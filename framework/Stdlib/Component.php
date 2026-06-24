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
 * Компонент - это базовый класс, реализующий свойства, события и поведения.
 * 
 * Свойства, события и поведения компонента реализованы его родительским классом 
 * {@see BaseObject}.
 * 
 * Событие - это один из способов "внедрить" свой код в существующий код в определенных 
 * местах. Например, объект статьи сайта может запускать событие "добавить", когда 
 * пользователь добавляет статью. Мы можем написать собственный код и прикрепить его 
 * к этому событию, чтобы, когда событие выполнится (т.е. будет добавлена статья), наш 
 * код будет выполнен.
 * 
 * Событие определяются именем, которое должно быть уникальным в пределах класса, в котором 
 * оно определено. Имена событий чувствительны к регистру.
 * 
 * К событию можно привязать один или несколько обратных вызовов PHP, называемых обработчиками 
 * событий. Вы можете вызвать {@see Component::trigger()}, чтобы вызвать событие. При возникновении 
 * события, обработчики событий будут вызываться автоматически в том порядке, в котором они были 
 * добавлены.
 * 
 * Чтобы прикрепить обработчик события к событию, вызовите {@see Component::on()}:
 * ```php
 * $article->on('delete', function ($param1, $param2...) {
 *     // отправить уведомление
 * });
 * ```
 * 
 * В приведенном выше описании к событию "delete" сообщения прикреплена анонимная функция. Вы можете 
 * присоединить следующие типы обработчиков событий:
 * - анонимная функция: `function ($param1, $param2...) { ... }`
 * - метод объекта: `[$object, 'handleDelete']`
 * - статический метод объекта: `['Article', 'handleDelete']`
 * - глобальные функции: `'handleDelete'`
 * 
 * Сигнатура обработчика события должна быть следующей:
 * ```php
 * function foobar($param1, $param2...)
 * ```
 * где `$param1, $param2...` это параметры связанные с событием.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class Component extends BaseObject
{
    /**
     * @var string Событие определяется наследниками компонента и возникает до его запуска.
     */
    public const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @var string Событие определяется наследниками компонента и возникает после его запуска.
     */
    public const EVENT_AFTER_RUN = 'afterRun';

   /**
    * Прикрепленные поведения в виде пар (имя поведения => поведение) если не 
    * инициализирован, то `null`.
    * 
    * @var array<string, Behavior>
    */
   protected array $_behaviors = [];

    /**
     * Конструктор класса.
     * 
     * Реализация по умолчанию делает две вещи:
     * - инициализирует объект согласно параметрам конфигурации `$config`;
     * - вызывает инициализацию компонента {@see Component::init()}.
     * 
     * Если этот метод переопределен в дочернем классе, рекомендуется:
     * - указать последний параметр конструктора - это массив параметров конфигурации, 
     * например, здесь `$config`;
     * - вызвать родительскую реализацию в конце конструктора.
     * 
     * @param array<string, mixed> $config Параметры конфигурации компонента в виде пар 
     *     "имя - значение", которые будут использоваться для инициализации свойств объекта.
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * Инициализация компонента.
     * 
     * Этот метод вызывается в конце конструктора после инициализации компонента 
     * заданной конфигурацией.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->initBehaviors();
    }

    /**
     * Возвращает список поведений, которые компонент присоединит.
     *
     * Дочерние классы могут переопределить этот метод, чтобы указать поведения, 
     * которое они хотят присоединить.
     *
     * Возвращаемое значение этого метода должно быть массивом объектов поведения 
     * или конфигураций. Конфигурация поведений может быть либо строкой, определяющей 
     * класс поведения, либо массивом следующей структуры:
     * ```php
     * 'behaviorName' => [
     *     'class'     => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ```
     *
     * Класс поведения должен быть расширен от {@see Behavior}. Поведение можно прикрепить 
     * только с помощью имени. Т.к. имя поведения используется в качестве ключа массива, 
     * то поведение можно позже получить с помощью {@see Component::getBehavior()} или 
     * отсоединить с помощью {@see Component::detachBehavior()}.
     *
     * Поведение, объявленное в этом методе, будет прикреплено к компоненту автоматически 
     * (по запросу) или при инициализации компонента с помощью {@see Component::initBehaviors()} 
     * (в том случаи если конфигурация поведения имеет параметр `autoInit => true`).
     *
     * @return array Конфигурации поведений.
     */
    public function behaviors(): array
    {
        return [];
    }

    /**
     * Возвращает объект поведения по указанному имени.
     *
     * Не вызывайте этот метод напрямую, так как это магический метод PHP, который будет 
     * неявно вызываться при выполнении `$value =  $this->getBehavior($name);`.
     * 
     * @param string $name Имя поведения.
     * 
     * @return Behavior|null Объект поведения или `null`, если поведение не существует.
     */
    public function __get(string $name)
    {
        return $this->getBehavior($name);
    }

    /**
     * Отсоединяет поведение от компонента.
     *
     * Не вызывайте этот метод напрямую, так как это магический метод PHP, который 
     * будет неявно вызываться при выполнении `$this->detachBehavior($name)`.
     * 
     * @param string $name Имя поведения.
     */
    public function __unset(string $name)
    {
        $this->detachBehavior($name);
    }

    /**
     * Проверяет, существует (присоединено к компоненту) ли поведение, когда к элементу 
     * обращаются как к свойству объекта. 
     * 
     * @see Component::hasBehavior()
     * 
     * @param string $name Имя поведения.
     * 
     * @return bool Если значение `true`, поведение существует.
     */
    public function __isset(string $name)
    {
        return $this->hasBehavior($name);
    }

    /**
     * Выполняет клонирование объекта.
     * 
     * Удаляет все поведения, потому что они привязаны к старому объекту.
     * 
     * @return void
     */
    public function __clone()
    {
        $this->_behaviors = [];
        if (isset($this->_events)) {
            $this->_events->clear();
        }
    }

    /**
     * Проверяет, присоединено ли поведение к компоненту.
     * 
     * @param string $name Имя поведения.
     * 
     * @return bool Если значение `true`, поведение присоединено к компоненту.
     */
    public function hasBehavior(string $name): bool
    {
        return isset($this->_behaviors[$name]);
    }

    /**
     * Инициализация поведений.
     * 
     * Инициализация поведений выполняется при инициализации компонента для все 
     * поведений у которых указан параметр конфигурации `autoInit`
     * 
     * @return void
     */
    public function initBehaviors(): void
    {
        foreach ($this->behaviors() as $name => $behavior) {
            $autoInit= isset($behavior['autoInit']) ? (bool) $behavior['autoInit'] : false; 
            if ($autoInit) {
                $this->attachBehavior($name, $behavior);
            }
        }
    }

    /**
     * Возвращает объект поведения по указанному имени.
     * 
     * @see Component::attachBehavior()
     * 
     * @param string $name Имя поведения.
     * 
     * @return Behavior|null Объект поведения или `null`, если поведение не существует.
     */
    public function getBehavior(string $name): ?Behavior
    {
        $behavior = isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
        if ($behavior === null) {
            $behaviors = $this->behaviors();
            if (isset($behaviors[$name])) {
                $behavior = $this->attachBehavior($name, $behaviors[$name]);
            }
        }
        return $behavior;
    }

    /**
     * Возвращает все поведения присоединённые к этому компоненту.
     * 
     * @see Component::attachBehavior()
     * 
     * @return Behavior[]|null Список поведений присоединённых к этому компоненту.
     */
    public function getBehaviors(): ?array
    {
        foreach ($this->behaviors() as $name => $behavior) {
            $_behavior = isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null; 
            if ($_behavior === null) {
                $this->attachBehavior($name, $behavior);
            }
        }
        return $this->_behaviors;
    }

    /**
     * Присоединяет поведение к этому компоненту.
     * 
     * @param string $name Имя поведения.
     * @param string|array|Behavior $behavior Прикрепляемое поведение.
     * 
     * @return Behavior Присоединённое поведение.
     */
    protected function attachBehavior(string $name, mixed $behavior): Behavior
    {
        if (!($behavior instanceof Behavior)) {
            $behavior = Ge::createObject($behavior);
        }
        if (isset($this->_behaviors[$name])) {
            $this->_behaviors[$name]->detach();
        }
        $behavior->attach($this);
        $this->_behaviors[$name] = $behavior;
        return $behavior;
    }

    /**
     * Присоединяет список поведений к компоненту.
     * 
     * Каждое поведение индексируется по имени и должно быть объектом {@see Behavior}, 
     * строкой, определяющей класс поведения, или массивом конфигурации для создания поведения.
     * 
     * @see Component::attachBehavior()
     * 
     * @param array $behaviors Список поведений, которые будут присоеденины к компоненту.
     * 
     * @return void
     */
    public function attachBehaviors(array $behaviors): void
    {
        foreach ($behaviors as $name => $behavior) {
            $this->attachBehavior($name, $behavior);
        }
    }

    /**
     * Отсоединяет поведение от компонента.
     * 
     * Будет вызван метод поведения {@see Behavior::detach()}.
     * 
     * @param string $name Имя поведения.
     * 
     * @return Behavior|null Отсоединённое поведение. Если `null`, поведение не 
     *    существует.
     */
    public function detachBehavior(string $name): ?Behavior
    {
        if (isset($this->_behaviors[$name])) {
            $behavior = $this->_behaviors[$name];
            unset($this->_behaviors[$name]);
            $behavior->detach();
            return $behavior;
        }
        return null;
    }

    /**
     * Отсоединяет все поведения от компонента.
     * 
     * @return void
     */
    public function detachBehaviors(): void
    {
        foreach ($this->_behaviors as $name => $behavior) {
            $this->detachBehavior($name);
        }
    }
}
