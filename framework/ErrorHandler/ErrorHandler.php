<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ErrorHandler;

use Ge;
use Throwable;
use Ge\Exception\ErrorException;

/**
 * Базовый класс обработчика (необработанных) ошибок и исключений PHP.
 * 
 * Доступ к экземпляру класса можно получить через "Ge::$app-> errorHandler".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ErrorHandler
 * @since 2.0
 */
class ErrorHandler
{
    /**
     * Удалять содержимое буфера перед выводом ошибок.
     * 
     * @var bool
     */
    public bool $cleanExistingOutput = false;

    /**
     * Последнее исключение.
     * 
     * @var Throwable
     */
    protected ?Throwable $lastException = null;

    /**
     * Стек исключений.
     * 
     * @var array
     */
    protected array $exceptions = [];

    /**
     * Карта кодов серьезности ошибок с описанием.
     * 
     * E_STRICT - deprecated PHP 8.4
     * 
     * @var array
     */
    public static array $severityMap = [
        E_ERROR             => ['error', 'PHP Error'],
        E_WARNING           => ['warning', 'PHP Warning'],
        E_PARSE             => ['error', 'PHP Parsing error'],
        E_NOTICE            => ['notice', 'PHP Notice'],
        E_CORE_ERROR        => ['error', 'PHP Core error'],
        E_CORE_WARNING      => ['warning', 'PHP Core warning'],
        E_COMPILE_ERROR     => ['error', 'PHP Compile error'],
        E_COMPILE_WARNING   => ['warning', 'PHP Compile warning'],
        E_USER_ERROR        => ['error', 'PHP User error'],
        E_USER_WARNING      => ['warning', 'PHP User warning'],
        E_USER_NOTICE       => ['notice', 'PHP User notice'],
        E_RECOVERABLE_ERROR => ['error', 'PHP Catchable fatal error'],
        E_DEPRECATED        => ['notice', 'PHP Deprecated'],
        E_USER_DEPRECATED   => ['notice', 'PHP User deprecated'],
        // E_STRICT         => ['notice', 'PHP Runtime notice'],
    ];

    /**
     * Проверка существования сообщений в стеке.
     * 
     * @return bool
     */
    public function hasExceptions(): bool
    {
        return sizeof($this->exceptions) > 0;
    }

    /**
     * Регистрация обработчика ошибок.
     * 
     * @return void
     */
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        if (GE_ENABLE_EXCEPTION_HANDLER) {
            set_exception_handler([$this, 'handleException']);
        }
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Восстанавливает предыдущий обработчика ошибок и исключений.
     * 
     * @return void
     */
    public function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Исключение которое приводит к фатальной ошибке если оно не поймано обработчиком
     * ошибок {@Handler}.
     * 
     * Этот метод должен вызываться в последней ловушке приложения.
     * 
     * @param Throwable $exception
     * 
     * @return void
     */
    public function uncatchableException(Throwable $exception): void
    {
    }

    /**
     * Обработчик исключений.
     * 
     * Вызывается каждый раз, когда выбрасывается неперехватываемое исключение. Функция-обработчик 
     * принимает один аргумент - объект, представляющий выброшенное исключение.
     * 
     * @param Throwable $exception Исключение.
     * 
     * @return void
     */
    public function handleException(Throwable $exception)
    {
        $this->exceptions[] = $exception;
        $this->lastException = $exception;
        // если ответ сформирован, но не отправлен
        if (Ge::hasObject('response')) {
            Ge::$app->response->stopSend = true;
        }

        $this->unregister();
        $this->logException($exception);
        try {
            $this->renderException($exception);
        } catch (\Exception $e) {
            $this->handleRetreatException($e, $exception);
        // для совместимости с PHP 7
        } catch (\Throwable $e) {
            $this->handleRetreatException($e, $exception);
        }
        $this->lastException = null;
    }

    /**
     * Для исключений, которые были брошены но не обработаны в {@see handleException()}.
     * 
     * @param Throwable|mixed $exception Исключение.
     * @param Throwable|mixed $previousException Предыдущие Исключение.
     * 
     * @return void
     */
    protected function handleRetreatException($exception, $previousException)
    {
        if (method_exists($exception, 'render')) {
            $exception->render();
            return;
        }

        $message = sprintf(
            "An Error occurred while handling another error:\n %s\nPrevious exception:\n %s",
            (string) $exception,
            (string) $previousException
        );

        if (GE_DEBUG) {
            if (PHP_SAPI === 'cli') {
                echo "$message\n";
            } else {
                echo '<pre>' . htmlspecialchars($message, ENT_QUOTES, Ge::$app->charset === null ? 'utf-8' :  Ge::$app->charset) . '</pre>';
            }
        } else {
            echo 'An internal server error occurred.';
        }
        error_log($message);
        exit(1);
    }

    /**
     * Обработчик ошибок.
     * 
     * @param int $code Уровень ошибки.
     * @param string $message Сообщение об ошибке.
     * @param string $file Имя файла, в котором произошла ошибка.
     * @param int $line Номер строки, в которой произошла ошибка.
     * 
     * @return bool Если значение `false`, стандартный обработчик ошибок PHP не будет обрабатывать никакие типы ошибок.
     */
    public function handleError($code, $message, $file, $line): bool
    {
        if (error_reporting() & $code) {
            $exception = new ErrorException($message, $code, $code, $file, $line);
            // TODO: throw $exception;
            $this->handleException($exception);
        }
        return false;
    }

    /**
     * Обработчик завершении работы скрипта.
     * 
     * @return void
     */
    public function handleShutdown(): void
    {
        /** @var array|null $error */
        $error = error_get_last();

        if (ErrorException::isFatalError($error)) {
            $exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->lastException = $exception;
            // удалить содержимое буфера
            if ($this->cleanExistingOutput) {
                $this->cleanOutputBuffer();
            }
            $this->renderFatalException($exception);
            $this->logException($exception);
            exit(1);
        }

        // если нет ошибки
        $this->renderShutdownExceptions();
    }

    /**
     * Отображает пойманное исключение.
     * 
     * @param Throwable $exception Исключение.
     * 
     * @return void
     */
    public function renderException($exception): void
    {
    }

    /**
     * Отображает пойманное фатальное исключение.
     * 
     * @param Throwable $exception Исключение.
     * 
     * @return void
     */
    public function renderFatalException($exception): void
    {
    }

    /**
     * Отображает исключения при завершении работы скрипта.
     * 
     * @return void
     */
    public function renderShutdownExceptions(): void
    {
        if (Ge::hasObject('response')) {
            /** @var \Ge\Http\Response $response */
            $response = Ge::$app->response;
            // если ответ сформирован, но не отправлен
            if (!$response->isSent) {
                $response->send(true);
            }
        }
    }

    /**
     * Логирование исключения.
     * 
     * @param Throwable $exception Исключение.
     * 
     * @return void
     */
    public function logException($exception): void
    {
    }

    /**
     * Очистить содержимое всего буфера.
     * 
     * @return void
     */
    public function cleanOutputBuffer(): void
    {
        $level = ob_get_level();
        for ($level; $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }

    /**
     * Возвращает описание серьезности ошибки по ее коду.
     * 
     * @see ErrorHandler::$severityMap
     * 
     * @param int $code Код ошибки.
     * 
     * @return null|array Возвращает значение `null`, если описание не найдено.
     */
    public function getSeverity($code): ?array
    {
        return isset(self::$severityMap[$code]) ? self::$severityMap[$code] : null;
    }

    /**
     * Возвращает описание серьезности ошибки по ее коду.
     * 
     * @see ErrorHandler::$severityMap
     * 
     * @param int $code Код ошибки.
     * @param bool $fullname Если значение `true`, то полное название.
     * 
     * @return null|string Возвращает значение `null`, если описание не найдено.
     */
    public function getSeverityName($code, bool $fullname = false): ?string
    {
        if (isset(self::$severityMap[$code])) {
            return self::$severityMap[$code][$fullname ? 0 : 1];
        }
        return null;
    }
}