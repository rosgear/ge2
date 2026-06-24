<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Event;

use Ge;
use Closure;

/**
 * Менеджер событий.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Event
 * @since 2.0
 */
class EventManager
{
    /**
     * События.
     * 
     * @var array
     */
    protected array $events = [];

    /**
     * Последнее вызываемое событие (имя и/или параметры).
     * 
     * Моежт иметь значение:
     * - `true`, параметры события;
     * - `false`, имя события;
     * - `null`, массив `[name, parameters]`.
     * 
     * @see EventManager::trigger()
     * @see EventManager::getLastEvent()
     * 
     * @var string|array|null
     */
    protected string|array|null $lastEvent = null;

    /**
     * Присоединяет событие.
     * 
     * @param string|string[] $event Имя или имена событий.
     * @param Closure|array $callback Замыкание вызова события.
     * 
     * @return $this
     */
    public function attach(string|array $event, Closure|array $callback): static
    {
        if (is_string($event)) {
            if (!array_key_exists($event, $this->events)) {
                $this->events[$event] = [];
            }
            $this->events[$event][] = [$callback];
        } else
        if (is_array($event)) {
            foreach ($event as $name) {
                $this->attach($name, $callback);
            }
        }
        return $this;
    }

    /**
     * Отсоединяет событие.
     * 
     * @param string $event Имя события.
     * 
     * @return $this
     */
    public function detach(string $event): static
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
        return $this;
    }

    /**
     * Выполняет вызов события.
     * 
     * @param Closure|array $callback Замыкание вызова события.
     * @param array $args Массив аргументов передаваемых событием.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Вызываемое событие не существует.
     */
    protected function doFunction(Closure|array $callback, array $args = []): void
    {
        // проверка возможности вызова анонимной функции
        if (!is_callable($callback)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', '{0}: сallback function "{1}" not called', [__METHOD__, $callback])
            );
        }
        // FIX php8: "Unknown named parameter" array_values()
        call_user_func_array($callback, array_values($args));
    }

    /**
     * Вызывает триггер события.
     * 
     * @see EventManager::doFunction()
     * 
     * @param string $event Имя события.
     * @param array $args Массив аргументов передаваемых событием.
     * 
     * @return $this
     */
    public function trigger(string $event, array $args = []): static
    {
        $this->lastEvent = [$event, $args];
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $index => $func) {
               $this->doFunction($func[0], $args);
            }
        }
        return $this;
    }

    /**
     * Возвращает последнее вызываемое событие (имя и/или параметры).
     * 
     * В зависимости от указанного значения $parameters, будет результат:
     * - если `true`, параметры события;
     * - если `false`, имя события;
     * - если `null`, массив `[name, parameters]`.
     * 
     * @param null|bool $parameters Значение указывает на возвращаемый результат.
     * 
     * @return string|array|null Последнее вызываемое событие (имя и/или параметры).
     */
    public function getLastEvent(?bool $parameters = null): string|array|null
    {
        if ($parameters === true)
            return $this->lastEvent[1];
        elseif ($parameters === false)
            return $this->lastEvent[0];
        else
            return $this->lastEvent;
    }

    /**
     * Возвращает все события.
     * 
     * @return array События.
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Возвращает имена всех событий.
     * 
     * @return array События.
     */
    public function getEventNames(): array
    {
        return array_keys($this->events);
    }

    /**
     * Удаляет все события без отсоединения.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->events = [];
    }
}
