<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

use Ge;

/**
 * Behavior - это базовый класс для всех классов поведения.
 * 
 * Поведение можно использовать для расширения функциональности существующего 
 * компонента без изменения его кода. Он может реагировать на события запускаемые 
 * в компоненте и таким образом, перехватывать выполнение кода.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class Behavior extends BaseObject
{
    /**
     * Владелец поведения.
     * 
     * @var Component|null
     */
    public ?Component $owner = null;

    /**
     * Автоинициализация поведения.
     * 
     * Этот свойство является одним из параметров конфигурации объекта при его 
     * создании. Если значение `true`, то поведение будет автоматически создано 
     * при инициализации компонента с помощью {@see \Ge\Stdlib\Component::initBehaviors()}.
     * 
     * @var bool
     */
    public bool $autoInit = false;

    /**
     * Присоединённые обработчики событий.
     * 
     * Обработчики событий из {@see Behavior::events()}.
     * 
     * @var array
     */
    protected array $_attachedEvents = [];

    /**
     * Конструктор класса.
     * 
     * Реализация по умолчанию делает две вещи:
     * - инициализирует поведение согласно параметрам конфигурации `$config`;
     * - вызывает инициализацию поведения {@see Behavior::init()}.
     * 
     * Если этот метод переопределен в дочернем классе, рекомендуется:
     * - указать последний параметр конструктора - это массив параметров конфигурации, 
     * например, здесь `$config`;
     * - вызвать родительскую реализацию в конце конструктора.
     * 
     * @param array $config Параметры конфигурации поведения в виде пар имя-значение, 
     *     которые будут использоваться для инициализации свойств объекта.
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            Ge::configure($this, $config);
        }
        $this->init();
    }

    /**
     * Инициализация поведения.
     * 
     * Этот метод вызывается в конце конструктора после инициализации поведения  
     * заданной конфигурацией.
     * 
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Объявляет обработчики событий для вызова событий владельца поведения.
     *
     * Дочерние классы могут переопределить этот метод, чтобы указать, какие 
     * обратные вызовы PHP должны быть прикреплены к событиям компонента (владельца) 
     * {@see Behavior::$owner}.
     *
     * Обратные вызовы будут прикреплены к событиям владельца {@see Behavior::$owner}, 
     * когда поведение будет привязано к владельцу. И отсоединены они будут от событий, 
     * когда поведение будет отсоединено от самого компонента.
     *
     * Обратные вызовы могут соответствовать:
     * - метод в этом поведении: `'handleClick'`, эквивалентно `[$this, 'handleClick']`
     * - метод объекта: `[$object, 'handleClick']`
     * - статический метод: `['Some', 'handleClick']`
     * - анонимная функция: `function ($event) { ... }`
     *
     * Ниже приведен пример:
     * ```php
     * [
     *     Module::EVENT_BEFORE_RUN => 'myBeforeRun',
     *     Module::EVENT_AFTER_RUN  => 'myAfterRun',
     * ]
     * ```
     *
     * @return array events Обработчики событий в виде пар "событие владельца => событие поведения".
     */
    public function events(): array
    {
        return [];
    }

    /**
     * Присоединяет объект поведения к компоненту. 
     * 
     * @param Component $owner Компонент, к которому должно быть присоединено это поведение.
     * 
     * @return void
     */
    public function attach(Component $owner): void
    {
        $this->owner = $owner;
        foreach ($this->events() as $event => $handler) {
            $this->_attachedEvents[$event] = $handler;
            $owner->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
    }

    /**
     * Отсоединяет объект поведения от компонента.
     * 
     * По умолчанию сделает unset свойству владельца и отсоединит обработчики событий  
     * объявленные в {@see Behavior::events()}.
     * 
     * @return  void
     */
    public function detach(): void
    {
        if ($this->owner) {
            foreach ($this->_attachedEvents as $event => $handler) {
                $this->owner->off($event, is_string($handler) ? [$this, $handler] : $handler);
            }
            $this->_attachedEvents = [];
            $this->owner = null;
        }
    }
}
