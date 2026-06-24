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
 * Класс коллекции предоставляет удобную обёртку для работы с массивами данных.
 * 
 * Для хранения элементов коллекции используется контейнер {@see Collection::$container}, 
 * где элементы представлены в виде пар "ключ - значение".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class IndexedCollection implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * Контейнер элементов коллекции в виде пары "ключ - значение", где ключ порядковый номер элемента.
     *
     * @var array
     */
    public array $container = [];

    /**
     * Создаёт экземпляр класса коллекции из указанного массива элементов.
     * 
     * @param array $container Контейнер элементов коллекции.
     * 
     * @return $this Коллекция элементов.
     */
    public static function createInstance(array $container = [])
    {
        $instance = new static();
        $instance->setAll($container);
        return $instance;
    }



    /**
     * Присваивает значение заданному смещению.
     *
     * @param int $offset Смещение (индекс), которому будет присваиваться значение.
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
     * Определяет, существует ли заданное смещение (идекс).
     * 
     * Данный метод выполняется при использовании `isset()` или `empty()` на объектах, 
     * реализующих интерфейс `ArrayAccess`. 
     *
     * @param int $offset Смещение (идекс) для проверки.
     * 
     * @return bool Возвращает `true` в случае успешного выполнения или `false` в случае 
     * возникновения ошибки. 
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->container);
    }

    /**
     * Удаляет смещение.
     *
     * @param int $offset Смещение (идекс) для удаления.
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
     * Данный метод выполняется, когда проверяется смещение (ключ) на пустоту с помощью 
     * функции `empty()`. 
     *
     * @param int $offset Смещение (индекс) для возврата.
     * 
     * @return int
     */
    public function offsetGet($offset): mixed
    {
        return array_key_exists($offset, $this->container) ? $this->container[$offset] : null;
    }

    /**
     * Проверяет, пустой ли элемент или вся коллекция элементов.
     * 
     * @param null|int $index Порядковый номер элемента. Если `null`, проверяет на пустоту 
     * всю коллекцию элементов.
     * 
     * @return bool Возвращает `false`, если элемент или вся коллекция элементов
     *     существует и содержит непустое ненулевое значение или строку `false`. 
     *     В противном случае возвращает `true`. 
     */
    public function empty(?int $index = null): bool
    {
        if ($index === null)
            return empty($this->container);
        else
            return empty($this->container[$index]);
    }

    /**
     * Определяет, было ли установлено значение элементу коллекции, включая `null`.
     * 
     * @param int $index Порядковый номер элемента.
     * 
     * @return bool Возвращает true, если элемент коллекции определен и его значение 
     *     отлично от `null`, и `false` в противном случае. 
     */
    public function has(int $index): bool
    {
        return array_key_exists($index, $this->container);
    }

    /**
     * Извлекает значение первого элемента коллекции и возвращает его, сокращая размер 
     * коллекцию на один элемент.
     * 
     * @return mixed
     */
    public function shift(): mixed
    {
        return array_shift($this->container);
    }

    /**
     * Извлекает значение последнего элемента коллекции и возвращает его, уменьшая размер 
     * коллекцию на один элемент.
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
     * @return mixed Массив, содержащий результаты применения callback-функции к 
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
     * @param callable $callback Callback-функция с аргументами ключа и значения элемента 
     *     коллекции.
     * 
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this->container as  $index => $value) {
            $value = $callback($index, $value);
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
     * @return int
     */
    public function getCount(): int
    {
        return sizeof($this->container);
    }

    /**
     * Подсчитывает количество элементов коллекции.
     * 
     * @see IndexCollection::getCount()
     * 
     * @return int
     */
    public function count(): int
    {
        return sizeof($this->container);
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
     * @return array Массива элементов в виде пар "ключ - значение", где ключ - порядковый 
     *     номер элемента.
     */
    public function &getContainer(): array
    {
        return $this->container;
    }

    /**
     * Возвращает все элементы коллекции.
     *
     * @return array Массива элементов в виде пар "ключ - значение", где ключ - порядковый 
     *     номер элемента.
     */
    public function getAll(): array
    {
        return $this->container;
    }

    /**
     * Возвращает все элементы коллекции.
     *
     * @return array Массива элементов в виде пар "ключ - значение", где ключ - порядковый 
     *     номер элемента.
     */
    public function all(): array
    {
        return $this->container;
    }

    /**
     * Возвращет все элементы коллекции в виде массива пар "ключ - значение", где 
     *     ключ - порядковый номер элемента.
     * 
     * @param bool $walk Если `true`, обход всех элементов коллекции и применение  
     *     `toArray` к элементу, значение которого объект `Collection`.
     * 
     * @return array Массива элементов.
     */
    public function toArray(bool $walk = false): array
    { 
        if ($walk) {
            $array = $this->container;
            array_walk_recursive($array, function (&$value, $key) {
                if ($value instanceof IndexedCollection) {
                    $value = $value->toArray(true);
                }
            });
            return $array;
        } else
            return $this->container;
    }

    /**
     * Возвращает в виде строки все или некоторое подмножество значений элементов коллекции.
     * 
     * @param string $separator Разделитель элементов.
     * 
     * @return string
     */
    public function toString(string $separator = ','): string
    {
        return implode($separator, $this->container);
    }

    /**
     * Устанавливает значение элементу подмножества коллекции.
     * 
     * ```php
     * $this->container[$key][$subKey] = $value
     * ```
     * 
     * @param int $index Порядковый номер элемента коллекции.
     * @param int $subIndex Ключ элемента подмножества.
     * @param mixed $value Значение элементу. Если `null`, элемент будет удалён.
     * 
     * @return $this
     */
    public function subSet(int $index, int $subIndex, mixed $value): static
    {
        if (isset($this->container[$index])) {
            if ($value === null)
                unset($this->container[$index][$subIndex]);
            else
                $this->container[$index][$subIndex] = $value;
        }
        return $this;
    }

    /**
     * Устанавливает значение элементу коллекции.
     * 
     * @param int $index Порядковый номер элемента коллекции.
     * @param mixed $value Значение ключа. Если `null`, элемент удаляется из коллекции.
     * 
     * @return $this
     */
    public function set(int $index, mixed $value): static
    {
        if ($value === null) {
            if (isset($this->container[$index]))
                unset($this->container[$index]);
        } else 
            $this->container[$index] = $value;
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
     * @param int $index Порядковый номер элемента коллекции.
     * 
     * @return mixed Если значение `null`, ключ элемента коллекции не существует.
     */
    public function &getFor(int $index): mixed
    {
        // чтобы не было: "Only variable references should be returned by reference"
        if (array_key_exists($index, $this->container))
            $_value = &$this->container[$index];
        else
            $_value = null;
        return $_value;
    }

    /**
     * Возвращает значение элемента коллекции.
     * 
     * @param int $index Порядковый номер элемента коллекции.
     * 
     * @return mixed Если значение `null`, ключ не существует.
     */
    public function get(int $index): mixed
    {
        return isset($this->container[$index]) ? $this->container[$index] : null;
    }

    /**
     * Сливает указанный массив элементов с элементами коллекции в один.
     * 
     * @param array $container Массив элементов.
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
     * @param array $indexes Порядковые номера элементов коллекции.
     * 
     * @return $this
     */
    public function removeMultiple(array $indexes): static
    {
        foreach ($indexes as $index) {
            $this->remove($index);
        }
        return $this;
    }

    /**
     * Удаляет элемент коллекции.
     * 
     * @param int $index Порядковы номер элемента коллекции.
     * 
     * @return $this
     */
    public function remove(int $index): static
    {
        unset($this->container[$index]);
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
     * Возвращает последний порядковый номер элемента коллекции.
     * 
     * @return int
     */
    public function lastIndex(): int
    {
        $count = sizeof($this->container);
        return $count > 0 ? $count - 1 : 0;
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
     * @return mixed Возвращает значение последнего элемента или `false` для пустой коллекции. 
     */
    public function end(): mixed
    {
        return end($this->container);
    }

    /**
     * Передвигает внутренний указатель коллекции на один элемент назад.
     *
     * @return mixed Возвращает значение элемента коллекции, на которое ранее указывал 
     *    внутренний указатель коллекции, или `false`, если больше элементов нет. 
     */
    public function prev(): mixed
    {
        return prev($this->container);
    }

    /**
     * Устанавливает внутренний указатель коллекции на ёё первый элемент.
     *
     * @return mixed Возвращает значение первого элемента коллекции, или `false`, 
     *     если коллекция пуста. 
     */
    public function reset(): mixed
    {
        return reset($this->container);
    }

    /**
     * Добавляет элемент коллекции.
     * 
     * @param mixed $value Элемент коллекции.
     * 
     * @return $this
     */
    public function add(mixed $value): static
    {
        $this->container[] = $value;
        return $this;
    }
}
