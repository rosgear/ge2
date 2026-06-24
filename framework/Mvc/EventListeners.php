<?php
namespace Ge\Mvc;

use Ge;
use Ge\Config\Config;

/**
 * Класс слушателей событий.
 * 
 * Слушатели выступают в роли компонента приложения (модуля, расширение модуля, виджета).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc
 * @since 2.0
 */
class EventListeners extends Config
{
    /**
     * @var string Вид слушателя событий - модуль.
     */
    public const TYPE_MODULE = 'module';

    /**
     * @var string Вид слушателя событий - расширение модуля.
     */
    public const TYPE_EXTENSION = 'extension';

    /**
     * @var string Вид слушателя событий - виджет.
     */
    public const TYPE_WIDGET = 'widget';

    /**
     * Вызывает событие слушателя.
     * 
     * Триггер слушателя будет вызван в том случаи, если слушатель доступен и имеет
     * указанное событие.
     * 
     * @param string $name Название события.
     * @param array $args Параметры передаваемые событием.
     * 
     * @return void
     */
    public function doEvent(string $name, array $args = []): void
    {
        if ($components = $this->get($name)) {
            foreach ($components as $component) {
                // для виджета
                if ($component[1] === self::TYPE_WIDGET)
                    Ge::$app->widgets->doEvent($component[0], $name, $args);
                else
                // для модуля
                if ($component[1] === self::TYPE_MODULE)
                    Ge::$app->modules->doEvent($component[0], $name, $args);
                else
                // для расширений
                if ($component[1] === self::TYPE_EXTENSION)
                    Ge::$app->extensions->doEvent($component[0], $name, $args);
    
            }
        }
    }

    /**
     * Удаляет указанный вид слушателей из событий.
     *
     * @param string $type Вид слушателя: 'module', 'extension', 'widget'.
     * 
     * @return void
     */
    public function removeListeners(string $type): void
    {
        $parameters = [];
        foreach ($this->container as $event => $listeners) {
            if (isset($parameters[$event])) {
                $parameters[$event] = [];
            }
            if ($listeners) {
                foreach ($listeners as $listener) {
                    if ($listener[1] === $type) continue;
                    $parameters[$event][] = $listener;
                }
            }
        }
        $this->container = $parameters;
    }

    /**
     * Удаляет слушателя событий с указанным идентификатором.
     *
     * @param string $listenerId Идентификатор слушателя событий.
     * 
     * @return void
     */
    public function removeListener(string $listenerId): void
    {
        $parameters = [];
        foreach ($this->container as $event => $listeners) {
            if (isset($parameters[$event])) {
                $parameters[$event] = [];
            }
            if ($listeners) {
                foreach ($listeners as $listener) {
                    if ($listener[0] === $listenerId) continue;
                    $parameters[$event][] = $listener;
                }
            }
        }
        $this->container = $parameters;
    }

    /**
     * Добавляет слушателя событий.
     *
     * @param string|array $events Событие или события.
     * @param string $listenerId Идентификатор слушателя событий.
     * @param string $type Вид слушателя: 'module', 'extension', 'widget'.
     * 
     * @return $this
     */
    public function addListener(string|array $events, string $listenerId, string $type): static
    {
        $events = (array) $events;
        foreach ($events as $event) {
            if (!isset($this->container[$event])) {
                $this->container[$event] = [];
            }
            $this->container[$event][] = [$listenerId, $type];
        }
        return $this;
    }
}