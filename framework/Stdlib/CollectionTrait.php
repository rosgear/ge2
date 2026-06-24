<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

use ArrayIterator;

/**
 * Трейт коллекции предоставляет удобную обёртку для работы с массивами данных.
 * 
 * Для использования трейта коллекции в классе, необходимо классу указать 
 * соответствующие интерфейсы: `\IteratorAggregate`, `\ArrayAccess`, `\Countable`.
 * 
 * Такой класс может иметь вид:
 * ```php
 * class MyService extends Service implements \IteratorAggregate, \ArrayAccess, \Countable
 * {
 *     use CollectionTrait;
 * }
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
Trait CollectionTrait
{
    /**
     * Контейнер элементов коллекции в виде пары "ключ - значение".
     *
     * @var array
     */
    protected array $container = [];

    /**
     * Создаёт экземпляр класса коллекции из указанного массива элементов.
     * 
     * @param array $container Контейнер элементов коллекции.
     * 
     * @return $this
     */
    public static function createInstance(array $container = []): static
    {
        $instance = new static();
        $instance->setAll($container);
        return $instance;
    }

    /**
     * Создаёт новую коллекцию из значения элемента коллекции или 
     * указанного массив элементов.
     * 
     * @param mixed $key Ключ элемента коллекции или массив элементов коллекции.
     * 
     * @return null|static Если значение `null`, ключ элемента коллекции не существует, 
     *     иначе новая коллекция.
     */
    public function factory(mixed $key): static
    {
        if (is_string($key)) {
            $container = $this->get($key);
            return $container ? static::createInstance((array) $container) : null;
        } else
            return static::createInstance($key);
    }

    /**
     * Проверяет, существует ли элемент коллекции, когда к элементу обращаются как 
     * к свойству объекта. 
     * 
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return bool Если true, элемент коллекции существует.
     */
    public function __isset(mixed $key)
    {
        return isset($this->container[$key]);
    }

    /**
     * Устанавливает значение элементу коллекции, когда к элементу 
     * обращаются как к свойству объекта. 
     *
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $value Значение элемента.
     * 
     * @return void
     */
    public function __set(mixed $key, mixed $value)
    {
        $this->container[$key] = $value;
    }

    /**
     * Удаляет элемент из коллекции, когда к элементу обращаются как 
     * к свойству объекта. 
     *
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return void
     */
    public function __unset(mixed $key)
    {
        unset($this->container[$key]);
    }

    /**
     * Возращает значение по указанному ключу элемента коллекции.
     *
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return mixed Если значение `null`, ключ элемента коллекции не существует.
     */
    public function &__get(mixed $key): mixed
    {
        // чтобы не было: "Only variable references should be returned by reference"
        if (array_key_exists($key, $this->container))
            $_value = &$this->container[$key];
        else
            $_value = null;
        return $_value;
    }

    /**
     * Присваивает значение заданному смещению.
     *
     * @param mixed $offset Смещение (ключ), которому будет присваиваться значение.
     * @param mixed $value Значение для присвоения.
     * 
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Определяет, существует ли заданное смещение (ключ).
     * 
     * Данный метод выполняется при использовании `isset()` или 
     * `empty()` на объектах, реализующих интерфейс `ArrayAccess`. 
     *
     * @param mixed $offset Смещение (ключ) для проверки.
     * 
     * @return bool Возвращает true в случае успешного выполнения 
     *     или false в случае возникновения ошибки. 
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->container);
    }

    /**
     * Удаляет смещение.
     *
     * @param mixed $offset Смещение (ключ) для удаления.
     * 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Возвращает заданное смещение (ключ).
     * 
     * Данный метод выполняется, когда проверяется смещение (ключ) 
     * на пустоту с помощью функции `empty()`. 
     *
     * @param mixed $offset Смещение (ключ) для возврата.
     * 
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return array_key_exists($offset, $this->container) ? $this->container[$offset] : null;
    }

    /**
     * Проверяет, пустой ли элемент или вся коллекция элементов.
     * 
     * @param mixed $key Ключ элемента. Если `null`, проверяет на пустоту всю коллекцию 
     *     элементов.
     * 
     * @return bool Возвращает `false`, если элемент или вся коллекция элементов
     *     существует и содержит непустое ненулевое значение или строку false. 
     *     В противном случае возвращает true. 
     */
    public function empty(mixed $key = null): bool
    {
        if ($key === null)
            return empty($this->container);
        else
            return empty($this->container[$key]);
    }

    /**
     * Определяет, было ли установлено значение элементу коллекции, включая null.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return bool Возвращает `true`, если элемент коллекции определен и его значение 
     *     отлично от `null`, и `false` в противном случае. 
     */
    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->container);
    }

    /**
     * Извлекает значение первого элемента коллекции и возвращает его, 
     * сокращая размер коллекцию на один элемент.
     * 
     * @return mixed
     */
    public function shift(): mixed
    {
        return array_shift($this->container);
    }

    /**
     * Извлекает значение последнего элемента коллекции и возвращает его, 
     * уменьшая размер коллекцию на один элемент.
     * 
     * @return mixed
     */
    public function pop(): mixed
    {
        return array_pop($this->container);
    }

    /**
     * Возвращает массив (array), содержащий результаты применения callback-функции к 
     * соответствующему индексу элемента коллекции, используемого в качестве 
     * аргумента callback-функции. 
     * 
     * @return array Массив, содержащий результаты применения callback-функции к 
     *    соответствующему индексу элемента коллекции, используемого в качестве 
     *    аргумента для callback-функции. 
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->container);
    }

    /**
     * Проход через все элементы коллекции с применением callback-функции.
     * 
     * @param callable $callback Callback-функция с аргументами ключа и 
     *     значения элемента коллекции.
     * 
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this->container as  $key => $value) {
            $value = $callback($key, $value);
        }
        return $this;
    }

    /**
     * Удаляет все элементы коллекции.
     * 
     * @see Collection::removeAll()
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->removeAll();
    }

    /**
     * Подсчитывает количество элементов коллекции.
     * 
     * @see Collection::getCount()
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->getCount();
    }

    /**
     * Возвращает итератор для просмотра элементов коллекции.
     * 
     * Этот метод требуется для интерфейса SP `\IteratorAggregate`.
     * Он будет неявно вызываться, когда используетcя `foreach` для обхода коллекции.
     * 
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->container);
    }

    /**
     * Возвращает указатель на контейнер элементов коллекции.
     *
     * @return array Массива элементов в виде пар "ключ - значение".
     */
    public function &getContainer(): array
    {
        return $this->container;
    }

    /**
     * Возвращает все элементы коллекции.
     *
     * @return array Массива элементов в виде пар "ключ - значение".
     */
    public function getAll(): array
    {
        return $this->container;
    }

    /**
     * Возвращает все элементы коллекции.
     *
     * @return array Массива элементов в виде пар "ключ - значение".
     */
    public function all(): array
    {
        return $this->getAll();
    }

    /**
     * Возвращет все элементы коллекции в виде массива пар "ключ - значение".
     * 
     * @param bool $walk Если true, обход всех элементов коллекции и применение  
     *     `toArray` к элементу, значение которого объект `Collection`.
     * 
     * @return array Массива элементов в виде пар "ключ - значение".
     */
    public function toArray(bool $walk = false): array
    { 
        if ($walk) {
            $array = [];
            foreach($this->container as $name => $value) {
                if (is_object($value)) {
                    if ($value instanceof Collection) {
                        $array[$name] = $value->toArray(true);
                    }
                } else
                    $array[$name] = $value;
            }
            return $array;
        } else
            return $this->container;
    }

    /**
     * Выбирает все значения элементов коллекции.
     * 
     * @return array Индексированный массив значений. 
     */
    public function getValues(): array
    {
        return array_values($this->container);
    }

    /**
     * Возвращает в виде строки все или некоторое подмножество значений элементов коллекции.
     * 
     * @param string $separator Разделитель элементов.
     * 
     * @return string
     */
    public function valuesToString(string $separator = ','): string
    {
        return implode($separator, array_values($this->container));
    }

    /**
     * Возвращает все или некоторое подмножество ключей элементов коллекции.
     * 
     * @return array Массив со всеми ключами элементов коллекции. 
     */
    public function getKeys(): array
    {
        return array_keys($this->container);
    }

    /**
     * Возвращает в виде строки все или некоторое подмножество ключей элементов коллекции.
     * 
     * @param string $separator Разделитель элементов.
     * 
     * @return string
     */
    public function keysToString(string $separator = ','): string
    {
        return implode($separator, array_keys($this->container));
    }

    /**
     * Подсчитывает количество элементов коллекции.
     * 
     * @return int
     */
    public function getCount(): int
    {
        return sizeof(array_keys($this->container));
    }

    /**
     * Устанавливает значение элементу подмножества коллекции.
     * 
     * ```php
     * $this->container[$key][$subKey] = $value
     * ```
     * 
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $subKey Ключ элемента подмножества.
     * @param mixed $value Значение элементу. Если null, элемент будет удалён.
     * 
     * @return $this
     */
    public function subSet(mixed $key, mixed $subKey, mixed $value): static
    {
        if (isset($this->container[$key])) {
            if ($value === null)
                unset($this->container[$key][$subKey]);
            else
                $this->container[$key][$subKey] = $value;
        }
        return $this;
    }

    /**
     * Поиск элемента коллекции в подмножестве.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $search Значие ключа для поиска.
     * 
     * @return mixed Если `null`, значение не найдено, иначе массив элементов 
     *     (подмножества) которому принадлежит ключ (`['key' => '...', 'value' => '...']`).
     */
    public function subSearch(mixed $key, mixed $search): ?array
    {
        foreach ($this->container as $index => $variable) {
            if (is_array($variable) && isset($variable[$key])) {
                if ($variable[$key] == $search) {
                    return ['key' => $index, 'value' => $variable];
                }
            }
        }
        return null;
    }

    /**
     * Устанавливает значение элементу коллекции.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $value Значение ключа. Если `null`, элемент удаляется из коллекции.
     * 
     * @return $this
     */
    public function set(mixed $key, mixed $value): static
    {
        if ($value === null) {
            if (isset($this->container[$key]))
                unset($this->container[$key]);
        } else 
            $this->container[$key] = $value;
        return $this;
    }

    /**
     * Устанавливает значение параметра подмножества.
     * 
     * Пример: $container[$name][$subname] = $value.
     * 
     * @param string $name Имя параметра множества.
     * @param string $subname Имя параметра подмножества.
     *    Имеет вид: $container[$name][$subname].
     * @param mixed $value Значение параметра. Если `null`, параметр будет удалён.
     * 
     * @return $this
     */
    public function setSubset(mixed $name, mixed $subname, mixed $value = null): static
    {
        if (isset($this->container[$name])) {
            if ($value === null)
                unset($this->container[$name][$subname]);
            else
                $this->container[$name][$subname] = $value;
        }
        return $this;
    }

    /**
     * Устанавливает контейнеру коллекции новый массив элементов.
     * 
     * @param array $container Новый массив элементов коллекции.
     * 
     * @return $this
     */
    public function setAll(array $container): static
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Возвращает указатель на элемент коллекции.
     *
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return mixed Если `null`, ключ элемента коллекции не существует.
     */
    public function &getFor($key): mixed
    {
        // чтобы не было: "Only variable references should be returned by reference"
        if (array_key_exists($key, $this->container))
            $_value = &$this->container[$key];
        else
            $_value = null;
        return $_value;
    }

    /**
     * Возвращает значение элемента коллекции.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * 
     * @return mixed Если `null`, ключ не существует.
     */
    public function get(mixed $key): mixed
    {
        return isset($this->container[$key]) ? $this->container[$key] : null;
    }

    /**
     * Возвращает значение элемента коллекции.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $default Значение по умолчанию.
     * 
     * @return mixed Если ключ не существует, значение по умолчанию.
     */
    public function getValue(mixed $key, mixed $default = null): mixed
    {
        return isset($this->container[$key]) ? $this->container[$key] : $default;
    }

    /**
     * Сливает указанный массив элементов с элементами коллекции в один.
     * 
     * @param array $container Массив элементов в виде пар "ключ - значение".
     * 
     * @return $this
     */
    public function merge(array $container): static
    {
        $this->container = array_merge($this->container, $container);
        return $this;
    }

    /**
     * Удаляет несколько элементов коллекции.
     * 
     * @param array $keys Ключи элементов коллекции.
     * 
     * @return $this
     */
    public function removeMultiple(array $keys): static
    {
        foreach ($keys as $key) {
            $this->remove($key);
        }
        return $this;
    }

    /**
     * Удаляет элемент или элементы коллекции.
     * 
     * @param mixed $key Ключ или ключи элементов коллекции.
     * 
     * @return $this
     */
    public function remove(mixed $key): static
    {
        if (isset($this->container[$key]))
            unset($this->container[$key]);
        return $this;
    }

    /**
     * Удаляет все элементы коллекции.
     * 
     * @return $this
     */
    public function removeAll(): static
    {
        $this->container = [];
        return $this;
    }

    /**
     * Возвращает первый ключ элемента коллекции.
     * 
     * @return mixed
     */
    public function firstKey(): mixed
    {
        return array_key_first($this->container);
    }

    /**
     * Возвращает последний ключ элемента коллекции.
     * 
     * @return mixed
     */
    public function lastKey(): mixed
    {
        return array_key_last($this->container);
    }

    /**
     * Возвращает значение текущего элемент коллекции.
     *
     * @return mixed Возвращает значение элемента коллекции, на который указывает 
     *     его внутренний указатель. Не перемещает указатель куда бы то ни было. 
     *     Если внутренний указатель находится за пределами списка элементов или 
     *     коллекция пуста, возвращает `false`. 
     */
    public function current(): mixed
    {
        return current($this->container);
    }

    /**
     * Перемещает указатель коллекции вперёд на один элемент.
     *
     * @return mixed Возвращает значение элемента коллекции, находящегося на позиции, 
     *     следующей за позицией внутренний указателя или `false`, если достигнут конец 
     *     коллекции. 
     */
    public function next(): mixed
    {
        return next($this->container);
    }

    /**
     * Устанавливает внутренний указатель коллекции на его последний элемент.
     *
     * @return mixed Возвращает значение последнего элемента или false для пустой коллекции. 
     */
    public function end(): mixed
    {
        return end($this->container);
    }

    /**
     * Передвигает внутренний указатель коллекции на один элемент назад.
     *
     * @return mixed Возвращает значение элемента коллекции, на которое ранее указывал 
     *    внутренний указатель коллекции, или false, если больше элементов нет. 
     */
    public function prev(): mixed
    {
        return prev($this->container);
    }

    /**
     * Устанавливает внутренний указатель коллекции на ёё первый элемент.
     *
     * @return mixed Возвращает значение первого элемента коллекции, или false, 
     *     если коллекция пуста. 
     */
    public function reset(): mixed
    {
        return reset($this->container);
    }

    /**
     * Возвращает строку, содержащую JSON-представление коллекции.
     * 
     * @param int $flags Битовая маска.
     * @param int $depth Устанавливает максимальную глубину. Должен быть больше нуля.
     * 
     * @return string|false Возвращает строку (string), закодированную JSON или false в 
     *     случае возникновения ошибки.
     */
    public function toJson(int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($this->container, $flags, $depth);
    }
}
