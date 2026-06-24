<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log;

use Ge\Stdlib\Service;
use Ge\Log\Writer\AbstractWriter;

/**
 * Логгер отправляет сообщения писателям (writer) для указанных целей.
 * 
 * Logger - это служба приложения, доступ к которой можно получить через `Ge::$app->logger`.
 * 
 * Для записи в журнал можно вызвать метод {@see log()}.
 * Для удобства предоставлен набор быстрых методов логирования сообщений с различным приоритетом,
 * чере класс {@see Ge}, это:
 * 
 * - {@see Ge::error()}
 * - {@see Ge::warning()}
 * - {@see Ge::info()}
 * - {@see Ge::notice()}
 * - {@see Ge::debug()}
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log
 * @since 2.0
 */
class Logger extends Service
{
    /**
     * @var string Цель (target) по умолчанию.
     */
    const DEFAULT_TARGET = 'application';

    /**
     * @var int Определяется из важности (severities) сообщений BSD Syslog.
     * @link http://tools.ietf.org/html/rfc3164
     */
    const
        EMERGENCY = 0, // Аварийная ситуация: система не работает
        ALERT     = 1, // Тревога: действие должно быть предпринято немедленно
        CRITICAL  = 2, // Критически: критические условия
        ERROR     = 3, // Ошибка: условия ошибки
        WARNING   = 4, // Предупреждение: условия предупреждения
        NOTICE    = 5, // уведомление: нормальное, но значительное состояние
        INFO      = 6, // Информация: информационные сообщения
        DEBUG     = 7, // Отладка: сообщения уровня отладки
        PROFILING = 8;

    const 
        /**
         * @var string Событие, возникшее перед вызовом закрытия писателя.
         */
        EVENT_BEFORE_CLOSE = 'beforeClose',
        /**
         * @var string Событие, возникшее при вызове закрытия писателя.
         */
        EVENT_WRITER_CLOSE = 'writerClose';

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Имена приоритетов.
     * 
     * Массив приоритетов в виде пар "код - название".
     *
     * @var array
     */
    public static array $priorityNames = [
        self::EMERGENCY  => 'emergency',
        self::ALERT      => 'alert',
        self::CRITICAL   => 'critical',
        self::ERROR      => 'error',
        self::WARNING    => 'warning',
        self::NOTICE     => 'notice',
        self::INFO       => 'informational',
        self::DEBUG      => 'debug',
        self::PROFILING  => 'profiling',
    ];

    /**
     * Базовые цели для которых отправляется сообщения писателями.
     *
     * @var array
     */
    public array $baseTargets = [
        self::DEFAULT_TARGET => [
            'writer'     => 'error',
            'enabled'    => true,
            'logFile'    => '@runtime/log/app.log',
            'severities' => '*'
        ]
    ];

    /**
     * Цели для которых отправляется сообщения писателями.
     * 
     * @see Logger::init()
     * 
     * @var array
     */
    public array $targets = [];

    /**
     * Профилирования производительности.
     *
     * @var bool
     */
    public bool $enableProfiling = false;

    /**
     * Профилирования запросов к базе данных.
     *
     * @var bool
     */
    public bool $enableProfilingDb = false;

    /**
     * Профилирования почты.
     *
     * @var bool
     */
    public bool $enableProfilingMail = false;

    /**
     * Уровень трассировки запроса в профилировании производительности.
     *
     * @var int
     */
    public int $traceLevel = 4;

    /**
     * Имена профилей с их параметрами, в виде пар "имя профиля - параметры".
     *
     * @var array
     */
    protected array $profiling = [];

    /**
     * Имена целей с их писателями, в виде пар "имя цели - писатель".
     * 
     * @var array
     */
    protected array $writers = [];

    /**
     * Менеджер служб Логгера.
     * 
     * @see Logger::getManager()
     * 
     * @var LoggerManager
     */
    protected LoggerManager $manager;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'logger';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->targets = array_merge($this->baseTargets, $this->targets);
        if ($this->enabled) {
            $this->autoCreateWriters();
        }
        $this->registerHandler();
    }

    /**
     * Автоматически создаёт писателей у каторых указан параметр "autoCreate".
     * 
     * @return $this
     */
    public function autoCreateWriters(): static
    {
        foreach($this->targets as $target => $options) {
            if (isset($options['autoCreate']) && $options['autoCreate'] == true) {
                $this->writers[$target] = $this->createWriter($options['writer'], $options);
            }
        }
        return $this;
    }

    /**
     * Создаёт писателя по указанному имени из менеджера служб Логгера.
     * 
     * @see LoggerManager::$invokableClasses
     * 
     * @param string $name Имя писателя.
     * @param array $options Настройки писателя.
     * 
     * @return AbstractWriter
     */
    public function createWriter(string $name, array $options = []): AbstractWriter
    {
        // убрать атрибут для определения класса писателя
        if (isset($options['writer'])) {
            unset($options['writer']);
        }
        return $this->getManager()->createAs($name, [], $options);
    }

    /**
     * Возвращает всех писателей.
     * 
     * @return array<string, AbstractWriter>
     */
    public function getWriters(): array
    {
        return $this->wrtiters;
    }

    /**
     * Добавляет писателя.
     * 
     * @param string $target Имя цели.
     * @param string $name Имя писателя.
     * @param array $options Настройки писателя.
     * 
     * @return $this
     */
    public function addWriter(string $target, string $name, array $options = []): static
    {
        if (isset($this->writers[$target])) {
            return $this;
        }
        if (is_object($name))
            $this->writers[$target] = $name;
        else
            $this->writers[$target] = $this->createWriter($name, $options);
        return $this;
    }

    /**
     * Проверяет, создан ли писатель с указанной цель.
     * 
     * @param string $target Имя цели.
     * 
     * @return bool
     */
    public function hasWriter(string $target): bool
    {
        return isset($this->writers[$target]);
    }

    /**
     * Возвращает писателя по указанной цели.
     * 
     * Если писатель не существует, создает его.
     * 
     * @param string $target Имя цели.
     * 
     * @return AbstractWriter|null Если значение `null`, то нет настроек для 
     *     создания писателя.
     */
    public function getWriter(string $target): ?AbstractWriter
    {
        if (!isset($this->writers[$target])) {
            $options = isset($this->targets[$target]) ? $this->targets[$target] : null;
            if ($options === null) {
                return null;
            }
            $this->writers[$target] = $this->createWriter($options['writer'], $options);
        }
        return $this->writers[$target];
    }

    /**
     * Проверяет, созданы ли писатели.
     * 
     * @return bool
     */
    public function hasWriters() :bool
    {
        return !empty($this->writers);
    }

    /**
     * Возвращает менеджер служб Логгера.
     * 
     * @return LoggerManager
     */
    public function getManager(): LoggerManager
    {
        if (!isset($this->manager)) {
            $this->manager = new LoggerManager();
        }
        return $this->manager;
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->_closed;
    }

    /**
     * @see Logger::close()
     * 
     * @var bool
     */
    protected bool $_closed = false;

    /**
     * Прекращает работу Логгера и всех его писателей.
     * 
     * @return void
     */
    public function close(): void
    {
        if ($this->_closed) return;

        defined('DEBUG_END_TIME') || define('DEBUG_END_TIME', time());

        $this->trigger(self::EVENT_BEFORE_CLOSE, ['logger' => $this]);
        foreach ($this->writers as $target => $writer) {
            try {
                if (!$writer->isClosed()) {
                    $this->trigger(self::EVENT_WRITER_CLOSE, ['logger' => $this, 'target' => $target, 'writer' => $writer]);
                    $writer->close();
                }
            } catch (\Exception $e) { }
        }
        $this->_closed = true;
    }

    /**
     * Прекращает работу Логгера и всех его писателей.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Запись сообщения.
     * 
     * @param string|array $message Сообщение.
     * @param int $priority Кода приоритета (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
     * @param array $extra Дополнительные параметры сообщения.
     * @param string $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function log(
        string|array $message, 
        int $priority, 
        array $extra = [], 
        ?string $category = null, 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        // если служба отключена
        if (!$this->enabled) {
            return $this;
        }

        // если нет целей и писателей
        if (empty($this->targets) && empty($this->writers)) {
            return $this;
        }

        // проверка приоритета
        if (!is_int($priority) || ($priority < 0) || ($priority >= count(self::$priorityNames))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$priority must be an integer >= 0 and < %d; received %s',
                count($this->priorities),
                var_export($priority, 1)
            ));
        }

        $writer = $this->getWriter($target);
        if ($writer == null) {
            throw new Exception\InvalidArgumentException(sprintf('Writer with target "%s" not exists.', $target));
        }

        // если доступен для записи
        if ($writer->enabled) {
            $writer->write([
                'category'     => $category,
                'timestamp'    => time(),
                'priority'     => (int) $priority,
                'priorityName' => self::$priorityNames[$priority],
                'message'      => $message,
                'extra'        => $extra,
            ]);
        }
        return $this;
    }

    /**
     * Запись сообщения с приоритетом "EMERGENCY" (аварийная ситуация).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function emergency(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::EMERGENCY, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "ALERT" (тревога).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function alert(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::ALERT, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "CRITICAL" (критически).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function critical(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::CRITICAL, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "ERROR" (ошибка).
     * 
     * @param string|array $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function error(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::ERROR, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "WARNING" (предупреждение).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function warning(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::WARNING, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "NOTICE" (уведомление).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function notice(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::NOTICE, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "INFO" (информация).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function info(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = self::DEFAULT_TARGET
    ): static
    {
        return $this->log($message, self::INFO, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "DEBUG" (отладка).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function debug(
        string|array $message, 
        array $extra = [], 
        ?string $category = 'application', 
        string $target = 'debug'
    ): static
    {
        return $this->log($message, self::DEBUG, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "DEBUG" (отладка) почты.
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function mailProfiling(
        string $message, 
        array $extra = [], 
        ?string $category = 'mail', 
        string $target = 'debug'
    ): static
    {
        return $this->log($message, self::DEBUG, $extra, $category, $target);
    }

    /**
     * Запись сообщения с приоритетом "PROFILING" (профилирование).
     * 
     * @param string $message Сообщение.
     * @param array $extra Дополнительные параметры сообщения.
     * @param string|null $category Имя категории сообщения.
     * @param string $target Имя цели.
     * 
     * @return $this
     */
    public function profiling(
        string $message, array $extra = [], 
        ?string $category = 'application', 
        string $target = 'debug'
    ): static
    {
        return $this->log($message, self::PROFILING, $extra, $category, $target);
    }

    /**
     * Регистрирует обработчик в register_shutdown_function.
     * 
     * @return void
     */
    public function registerHandler()
    {
        register_shutdown_function(function () {
            $this->close();
            // убедиться, что "close()" вызывается последним при наличии нескольких функций выключения
            register_shutdown_function([$this, 'close'], true);
        });
    }

    /**
     * Установка начальной точки профилирования запроса.
     * 
     * @param string $name Имя профиля.
     * @param string $category Имя категории к которой относится профилирование (по умолчанию "application").
     * 
     * @return array
     */
    public function beginProfile(string $name, string $category = 'application'): array
    {
        $trace = [];
        if ($this->traceLevel > 0) {
            $trace = debug_backtrace(2, $this->traceLevel + 1);
            array_shift($trace);
        }
        return $this->profiling[$name] = [
            'microtime'    => microtime(true),
            'category'     => $category,
            'amountMemory' => memory_get_usage(true),
            'peakMemory'   => memory_get_peak_usage(true),
            'trace'        => $trace
        ];
    }

    /**
     * Установка конечнной точки профилирования запроса.
     * 
     * @param string $name Имя профиля (операнда).
     * @param string $message Сообщение (например, значение операнда).
     * @param array $extra Дополнительные параметры (операнды или другая отладочная информация) сообщения.
     * 
     * @return mixed Возвращает параметры профиля.
     */
    public function endProfile(string $name, string $message = '', array $extra = [])
    {
        if (isset($this->profiling[$name])) {
            $profiling = $this->profiling[$name];
            $message   = $message ? htmlspecialchars($message) : $name;
            $microtime = round($profiling['microtime'], 3);
            $this->profiling($message, [
                'profiling' => [
                    'name'         => $name,
                    'duration'     => microtime(true) - $profiling['microtime'],
                    'time'         => explode('.', $microtime),
                    'amountMemory' => memory_get_usage(true) - $profiling['amountMemory'],
                    'peakMemory'   => memory_get_peak_usage(true) - $profiling['peakMemory'],
                    'trace'        => $profiling['trace']
                ],
                'extra' => $extra
            ], $profiling['category']);
            //
            unset($this->profiling[$name]);
        }
        return $profiling;
    }

    /**
     * Возвращает информацию о запросе по указанному имени профиля.
     * 
     * @param string|null $name Имя профиля (операнда). Если значение `null`, то возвратит 
     *     все профиля.
     * 
     * @return null|mixed Возвратит значение `null`, если указанный профиль отсутствует, 
     *     иначе информацию о профиле или профилях.
     */
    public function getProfiling(?string $name = null)
    {
        if ($name === null)
            return $this->profiling;
        else
            return isset($this->profiling[$name]) ? $this->profiling[$name] : null;
    }

    /**
     * Проверяет возможность профилирования производительности.
     * 
     * @return bool
     */
    public function isProfilingEnabled(): bool
    {
        return $this->enabled && $this->enableProfiling;
    }

    /**
     * Проверяет возможность профилирования запросов к базе данных.
     * 
     * @return bool
     */
    public function isProfilingDbEnabled(): bool
    {
        return $this->enabled && $this->enableProfilingDb;
    }
}
