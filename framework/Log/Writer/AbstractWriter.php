<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log\Writer;

use Ge;
use Ge\Log\Logger;

/**
 * Абстрактный класс писателя является базовым классом для всех классов-наследников.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
abstract class AbstractWriter
{
    /**
     * Сообщения.
     * 
     * @var array
     */
    protected array $messages = [];

    /**
     * Имеет значение true, если писатель завершил свою работу.
     * 
     * Устанавливается методом {@see AbstractWriter::close()}.
     * 
     * @var bool
     */
    protected bool $closed = true;

    /**
     * Имена приоритетов с кодами логирования сообщений.
     * 
     * Коды логирования сообщений указываются  {@see \Ge\Log\Logger::$priorityNames}.
     * 
     * @var int|array
     */
    protected int|array $prioritiesMap = [];

    /**
     * Коды приоритетов логирования сообщений.
     * 
     * Коды соответствую сообщениям {@see \Ge\Log\Logger::$priorityNames}.
     * 
     * Сообщения с перечисленными кодами приоритета будут добавлены в журнал.
     * Устанавливается в опциях ($options) конструктора класса {@see \Ge\Log\Writer\AbstractWriter}.
     * 
     * @var array|string|null
     */
    public array|string|null $priorities = null;

    /**
     * Доступность к записи сообщения.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@see \Ge\Log\Writer\AbstractWriter}.
     * 
     * @var bool
     */
    public bool $enabled = true;

    /**
     * Параметры писателя.
     * 
     * @var array
     */
    public array $options = [];

    /**
     * Маршруты запросов для которых отладка не доступна.
     * 
     * Пример: ["log", "debugtoolbar"], отладка не будет доступна для маршрутов
     * ["backend/log/...", "backend/debugtoolbar/..."].
     * 
     * @var array
     */
    public array $excludeRoutes = [];

    /**
     * IP-адреса для которых возможна отладка.
     * 
     * @var array
     */
    public array $allowedIPs = [];

    /**
     * Идентификаторы пользователей для которых возможна отладка.
     * 
     * @var array
     */
    public array $allowedUsers = [];

    /**
     * Идентификаторы ролей для которых возможна отладка.
     * 
     * @var array
     */
    public array $allowedRoles = [];

    /**
     * Идентификаторы ролей для которых возможна отладка.
     * 
     * @see AbstractWriter::isAllowed()
     * 
     * @var bool
     */
    protected bool $allowed;

    /**
     * Конструктор класса.
     * 
     * @param array $options Опции писателя.
     * 
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;

        Ge::configure($this, $options);
        $this->init();
    }

    /**
     * Проверяет маршрутов запросов для которых отладка не доступна.
     * 
     * @var bool
     */
    public function isExcludedRoutes() :bool
    {
        if ($this->excludeRoutes) {
            $route = Ge::$app->urlManager->getModuleRoute();
            foreach($this->excludeRoutes as $exclude) {
                if (strpos($route, $exclude) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Проверяет IP-адреса, для которых возможна отладка.
     * 
     * Если IP-адреса не указаны, отладка для всех.
     * 
     * @var bool
     */
    public function isAllowedIPs(): bool
    {
        if ($this->allowedIPs) {
            return in_array(Ge::$app->request->getUserIp(), $this->allowedIPs);
        }
        return true;
    }

    /**
     * Проверяет идентификаторы пользователей для которых возможна отладка.
     * 
     * Если идентификаторы пользователей {@see \Ge\Log\Writer\AbstractWriter::$allowedUsers} не указаны, отладка для всех.
     * 
     * @var bool
     */
    public function isAllowedUsers(): bool
    {
        if ($this->allowedUsers && Ge::hasUserIdentity()) {
            return in_array(Ge::$app->user->getId(), $this->allowedUsers);
        }
        return true;
    }

    /**
     * Проверяет идентификаторы ролей для которых возможна отладка.
     * 
     * Если идентификаторы ролей {@see \Ge\Log\Writer\AbstractWriter::$allowedRoles} не указаны, отладка для всех.
     * 
     * @var bool
     */
    public function isAllowedRoles(): bool
    {
        if ($this->allowedRoles && Ge::hasUserIdentity()) {
            return Ge::userIdentity()->getRoles()->has($this->allowedRoles);
        }
        return true;
    }

    /**
     * Проверяет условия при которых возможна отладка.
     * 
     * @var bool
     */
    public function isAllowed(): bool
    {
        if (!isset($this->allowed)) {
            $this->allowed = true;
            // маршруты запросов для которых отладка не доступна
            if ($this->isExcludedRoutes()) {
                return $this->allowed = false;
            }
            // ip-адреса для которых возможна отладка
            if (!$this->isAllowedIPs()) {
                return $this->allowed = false;
            }
            // идентификаторы пользователей  для которых возможна отладка
            if (!$this->isAllowedUsers()) {
                return $this->allowed = false;
            }
            // идентификаторы ролей для которых возможна отладка
            if (!$this->isAllowedRoles()) {
                return $this->allowed = false;
            }
        }
        return $this->allowed;
    }

    /**
     * Возвращает параметры писателя.
     * 
     * @param string $name Имя параметра.
     * @param null $default Значение по умолчпнию
     * 
     * @return mixed
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Инициализация приоритетов.
     * 
     * @see AbstractWriter::setPriorities()
     * 
     * @return $this
     */
    public function initPriorities(): static
    {
        if ($this->priorities) {
            $this->setPriorities($this->priorities);
        }
        return $this;
    }

    /**
     * Инициализация писателя.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->initPriorities();
        $this->isAllowed();
    }

    /**
     * Устанавливает приоритеты.
     * 
     * @see AbstractWriter::$prioritiesMap
     * 
     * @param array|string $priorities Приоритеты {@see sLogger::$priorityNames}.
     * 
     * @return $this
     */
    public function setPriorities(array|string $priorities): static
    {
        static $prioritiesMap;

        if ($prioritiesMap === null) {
            $prioritiesMap = array_flip(logger::$priorityNames);
        }

        if ($priorities === '*') {
            $this->prioritiesMap = 1;
        } else {
            foreach ($priorities as $name) {
                if (isset($prioritiesMap[$name])) {
                    $this->prioritiesMap[] = $prioritiesMap[$name];
                }
            }
        }
        return $this;
    }

    /**
     * Фильтрация сообщения.
     * 
     * @param array $message Сообщение.
     * 
     * @return array
     */
    public function filterMessage(array $message): array
    {
        return $message;
    }

    /**
     * Добавление сообщения в стек сообщений.
     * 
     * @param array $message Сообщение.
     * 
     * @return void
     */
    public function write(array $message): void
    {
        if ($this->enabled && $this->allowed) {
            $this->closed = false;
            if ($message = $this->filterMessage($message)) {
                $this->messages[] = $message;
            }
        }
    }

    /**
     * Запись стека сообщений в журнал.
     * 
     * @return void
     */
    public function writeAll(): void
    {
    }

    /**
     * Проверка, были ли записаны сообщения в журнал.
     * 
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Форматирование значения сообщения.
     * 
     * @param string $key Имя ключа.
     * @param mixed $value Значение ключа.
     * 
     * @return mixed
     */
    public function formatValue(string $key, mixed $value): mixed
    {
        if ($key === 'message') {
            if (is_array($value)) {
                return var_export($value, true);
            }
            return (string) $value;
        }
        if ($key === 'extra') {
            if (is_array($value)) {
                return print_r($value, true);
            }
            return (string) $value;
        }
        return $value;
    }

    /**
     * Форматирование сообщения (всех значений сообщения).
     * 
     * @param array $message сообщение.
     * 
     * @return array
     */
    public function formatMessage(array $message): string|array
    {
        foreach ($message as $key => $value) {
            $message[$key] = $this->formatValue($key, $value);
        }
        return $message;
    }

    /**
     * Запись стека сообщений в журнал.
     * 
     * @return void
     */
    public function close(): void
    {
        if (!$this->closed) {
            if ($this->enabled && $this->allowed) {
                $this->writeAll();
            }
            $this->closed = true;
        }
    }
}
