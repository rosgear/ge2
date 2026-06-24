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
use Closure;
use ReflectionClass;
use Ge\Event\EventManager;
use Ge\Exception\BadMethodCallException;

/**
 * Базовый объект наследования.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class BaseObject
{
    /**
     * @var string Событие, возникшее при вызове метода объекта.
     */
    public const EVENT_DOMETHOD = 'doMethod';

    /**
     * Информацию о классе.
     * 
     * @see BaseObject::reflection()
     * 
     * @var ReflectionClass
     */
    protected ReflectionClass $_reflection;

    /**
     * Менеджер событий.
     * 
     * @see BaseObject::getEvents()
     * 
     * @var EventManager
     */
    protected EventManager $_events;

    /**
     * Конструктор класса.
     * 
     * Инициализирует объект согласно параметрам конфигурации `$config`;
     * 
     * Если этот метод переопределен в дочернем классе, рекомендуется:
     * - указать последний параметр конструктора - это массив параметров конфигурации, 
     * например, здесь `$config`;
     * - вызвать родительскую реализацию в конце конструктора.
     * 
     * @param array<string, mixed> $config Параметры конфигурации объекта в виде пар "имя - значение", 
     *     которые будут использоваться для инициализации свойств объекта.
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
    }

    /**
     * Настраивает объект с начальными значениями свойств.
     *
     * @param array<string, mixed> $config Параметры конфигурации объекта в виде 
     *     пар "имя - значение", которые будут использоваться для инициализации свойств 
     *     объекта.
     * 
     * @return void
     */
    public function configure(array $config): void
    {
        if (!empty($config)) {
            Ge::configure($this, $config);
        }
    }

    /**
     * Возвращает информацию о классе.
     * 
     * @see BaseObject::$reflection
     * @see https://www.php.net/manual/ru/class.reflectionclass.php
     * 
     * @return ReflectionClass Информация о классе.
     */
    public function getReflection(): ReflectionClass
    {
        if (!isset($this->_reflection)) {
            $this->_reflection = new ReflectionClass($this);
        }
        return $this->_reflection;
    }

    /**
     * Уникальное имя объекта (службы).
     * 
     * Имя используется для получения параметров из унифицированного 
     * конфигуратора {@see Application::$unifiedConfig}, а также для обработки 
     * событий менджером событий {@see \Ge\Event\EventManager}.
     * Если значение `null`, имя определяется через `get_class()`.
     * 
     * @var string
     */
    public function getObjectName(): string
    {
        return @get_class($this);
    }

    /**
     * Возвращает имя класса, к которому принадлежит объект.
     * 
     * @see https://www.php.net/manual/ru/function.get-class.php
     * 
     * @return string Имя класса, к которому принадлежит экземпляр вызываемого объекта.
     */
    public function getClass(): string
    {
        return @get_class($this);
    }

    /**
     * Возвращает короткое имя класса, часть, которая не относится к названию пространства имён.
     * 
     * @see https://www.php.net/manual/ru/reflectionclass.getshortname.php
     * 
     * @return string Короткое имя класса. 
     */
    public function getShortClass(): string
    {
        return $this->getReflection()->getShortName();
    }

   /**
     * Возвращает имя класса, полученное с помощью позднего статического связывания.
     * 
     * @see https://www.php.net/manual/ru/function.get-called-class.php
     * 
     * @return string Имя класса. Возвращает `false`, если было вызвано вне класса.
     * @deprecated с версии PHP >=5.5, вместо этого используется "::class".
     */
    public static function getCalledClass() :string
    {
        return get_called_class();
    }

    /**
     * Проверяет, принадлежит ли объект к данному классу или является ли этот класс 
     * одним из его родителей.
     * 
     * @see https://www.php.net/manual/ru/function.is-a.php
     * 
     * @param string $class Имя класса.
     * 
     * @return bool Возвращает значение `true`, если объект принадлежит данному классу 
     *     или является ли этот класс одним из его родителей, иначе возвращается `false`. 
     */
    public function isA(string $class): bool
    {
        return is_a($this, $class, false);
    }

    /**
     * Возвращает Менеджер событий.
     * 
     * Если Менеджер событий не создан, создает его.
     * 
     * @see BaseObject::$events
     * @see \Ge\Event\EventManager
     * 
     * @return EventManager Менеджер событий.
     */
    public function getEvents(): EventManager
    {
        if (!isset($this->_events)) {
            $this->_events = Ge::$services->createAs('eventManager');
        }
        return $this->_events;
    }

    /**
     * Возвращает объект, созданный по указанному короткому имени классу.
     * 
     * Пример: если имя класса `\Foo\Bar`, то короткое имя `Bar`, а название 
     * пространства имён `\Foo`. Если вы укажите короткое имя класса `MyBar` в 
     * параметрах, то результирующие имя будет `\Foo\Bar\MyBar`.
     * 
     * @see \Ge\ServiceManager\ServiceManager::getAs()
     * 
     * @param array<string, mixed> $params Короткое имя класса c указанием аргументов 
     *     конструктора или конфигурация класса.
     * 
     * @return mixed
     */
    public function call(...$params): mixed
    {
        /**
         * @var string $invokeName имя класса
         * @var array<string, mixed> $construct аргументы конструктора 
         * @var array<string, mixed> $config конфигурация класса
         */
        list($invokeName, $construct, $config) = Ge::$services->normalizeParams($params);
        return Ge::$services->getAs(
            $this->getReflection()->getNamespaceName() . '\\' . $invokeName,
            $construct,
            $config
        );
    }

    /**
     * Выполняет метод объекта.
     * 
     * После вызова метода, будет вызов события {@see BaseObject::EVENT_DOMETHOD}.
     * 
     * @param array<string, mixed> $args Передаваемые в метод параметры в виде индексированного массива.
     * 
     * @return mixed Результат выполнения метода.
     * 
     * @throws BadMethodCallException Если вызываемый метод не существует.
     */
    public function do(...$args): mixed
    {
        $name = $args[0];
        if (!method_exists($this, $name)) {
            throw new BadMethodCallException('Bad method call: ' . __CLASS__ . '::' . $name . '.');
        }
        array_shift($args);
        $result = call_user_func_array([$this, $name], $args);
        $this->trigger(self::EVENT_DOMETHOD, ['name' => $this, 'args' => $args, 'result' => $result]);
        return $result;
    }

    /**
     * Присоединяет обработчик к Менеджеру событий.
     * 
     * @see EventManager::attach()
     * 
     * @param string $event Имя обработчика события.
     * @param Closure|array $callback Функция вызова события.
     * 
     * @return $this
     */
    public function on(string $event, Closure|array $callback = []): static
    {
        $this->getEvents()->attach($event, $callback);
        return $this;
    }

    /**
     * Отсоединяет обработчик от Менеджера событий.
     * 
     * @see EventManager::detach()
     * 
     * @param string $event Имя обработчика события.
     * 
     * @return $this
     */
    public function off(string $event): static
    {
        $this->getEvents()->detach($event);
        return $this;
    }

    /**
     * Добавляет триггер к Менеджеру событий.
     * 
     * @see EventManager::trigger()
     * 
     * @param string $event Имя обработчика события.
     * @param array<string, mixed> $args Параметры вызова триггера.
     * 
     * @return $this
     */
    public function trigger(string $event, array $args = []): static
    {
        $this->getEvents()->trigger($event, $args);
        return $this;
    }

    /**
     * Проверяет, одержит ли объект или класс указанный метод.
     *
     * @param string $method Имя метода.
     * 
     * @return bool Возвращает значение `true`, если метод method определён для объекта, 
     *     иначе возвращает `false`. 
     */
    public function hasMethod(string $method): bool
    {
        return method_exists($this, $method);
    }

    /**
     * Проверяет, содержит ли объект или класс указанное свойство.
     *
     * @param string $name Имя свойства.
     * 
     * @return bool Возвращает значение `true`, если свойство существует `false`, если 
     *     оно не существует, или `null` в случае возникновения ошибки. 
     */
    public function hasProperty(string $name): ?bool
    {
        return property_exists($this, $name);
    }

    /**
     * Рекурсивно проверяет, содержит ли объект указанный атрибут.
     * 
     * @param mixed $object Проверяемый объект.
     * @param string $property Имя свойства (может иметь вид: "property->property1->property2").
     * @param string $delimiter Разделитель в имени свойства объекта.
     * 
     * @return bool
     */
    public static function hasPropertyRecursive($object, string $property, string $delimiter = '->'): bool
    {
        $path   = explode($delimiter, $property);
        $sample = $object;
        foreach ($path as $part) {
            if (!property_exists($sample, $part)) {
                return false;
            }
            $sample = $sample->{$part};
        }
        return true;
    }
}
