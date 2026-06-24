<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter;

/**
 * Контейнер параметров.
 * 
 * Не используем \Iterator, т.к. класс ParameterContainer имеет устаревшие методы.
 * 
 * @author Zend Framework (http://framework.zend.com/)
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter
 * @since 2.0
 */
class ParameterContainer implements \ArrayAccess, \Countable
{
    public const TYPE_AUTO    = 'auto';
    public const TYPE_NULL    = 'null';
    public const TYPE_DOUBLE  = 'double';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BINARY  = 'binary';
    public const TYPE_STRING  = 'string';
    public const TYPE_LOB     = 'lob';

    /**
     * Элементы контейнера.
     *
     * @var array<string|int, mixed>
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $positions = [];

    /**
     * Элементы итератора.
     * 
     * @see ParameterContainer::offsetSetErrata()
     * 
     * @var array<string|int, mixed>
     */
    protected array $errata = [];

    /**
     * Элементы итератора максимальной длины.
     * 
     * @see ParameterContainer::offsetSetMaxLength()
     * 
     * @var array<string|int, mixed>
     */
    protected array $maxLength = [];

    /**
     * Конструктор класса.
     *
     * @param array<string|int, mixed> $data Элементы контейнера в виде пар "ключ - значение".
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->setFromArray($data);
        }
    }

    /**
     * Определяет, существует ли заданное смещение (ключ).
     *
     * @param mixed $offset Смещение (ключ) для проверки.
     * 
     * @return bool Возвращает `true` в случае успешного выполнения 
     *     или false в случае возникновения ошибки. 
     */
    public function offsetExists(mixed $offset): bool
    {
        return (isset($this->data[$offset]));
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
        return (isset($this->data[$offset])) ? $this->data[$offset] : null;
    }

    /**
     * Создаёт укузатель на элемент внутри контейнера.
     * 
     * @param $name
     * @param $from
     * 
     * @return void
     */
    public function offsetSetReference(string|int $name, string|int $from): void
    {
        $this->data[$name] =& $this->data[$from];
    }

    /**
     * Присваивает значение заданному смещению.
     *
     * @param mixed $name Смещение (ключ), которому будет присваиваться значение.
     * @param mixed $value Значение для присвоения.
     * @param mixed $errata Значения итератора.
     * @param mixed $maxLength Значения итератора максимальной длины.
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offsetSet(mixed $name, mixed $value, mixed $errata = null, mixed $maxLength = null): void
    {
        $position = false;

        if (is_int($name)) {
            if (isset($this->positions[$name])) {
                $position = $name;
                $name = $this->positions[$name];
            } else {
                $name = (string) $name;
            }
        } elseif (is_string($name)) {
            // is a string:
            $position = array_key_exists($name, $this->data);
        } elseif ($name === null) {
            $name = (string) count($this->data);
        } else {
            throw new Exception\InvalidArgumentException('Keys must be string, integer or null');
        }

        if ($position === false) {
            $this->positions[] = $name;
        }

        $this->data[$name] = $value;

        if ($errata) {
            $this->offsetSetErrata($name, $errata);
        }

        if ($maxLength) {
            $this->offsetSetMaxLength($name, $maxLength);
        }
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
        if (is_int($offset) && isset($this->positions[$offset])) {
            $offset = $this->positions[$offset];
        }
        unset($this->data[$offset]);
    }

    /**
     * Установить массивом.
     *
     * @param array<string|int, mixed> $data
     * 
     * @return $this
     */
    public function setFromArray(array $data): static
    {
        foreach ($data as $n => $v) {
            $this->offsetSet($n, $v);
        }
        return $this;
    }

    /**
     * Присваивает значение заданному смещению итератора максимальной длины.
     *
     * @param string|int $offset Смещение (ключ) для возврата.
     * @param mixed $value Значение для присвоения.
     * 
     * @return void
     */
    public function offsetSetMaxLength(string|int $offset, mixed $value): void
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        $this->maxLength[$offset] = $value;
    }

    /**
     * Возвращает заданное смещение (ключ) итератора максимальной длины.
     *
     * @param string|int $offset Смещение (ключ) для возврата.
     * 
     * @return mixed
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offsetGetMaxLength(string|int $offset): mixed
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        if (!array_key_exists($offset, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->maxLength[$offset];
    }

    /**
     * Проверяет возможность смещения итератора максимальной длины.
     *
     * @param string|int $offset Смещение (ключ) для проверки.
     * 
     * @return bool
     */
    public function offsetHasMaxLength(string|int $offset): bool
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        return (isset($this->maxLength[$offset]));
    }

    /**
     * Удаляет смещение итератора максимальной длины.
     *
     * @param string|int $offset Смещение (ключ) для удаления.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetMaxLength(string|int $offset): void
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        if (!array_key_exists($offset, $this->maxLength)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->maxLength[$offset] = null;
    }

    /**
     * Возвращает итератор максимальной длины.
     *
     * @return \ArrayIterator
     */
    public function getMaxLengthIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->maxLength);
    }

    /**
     * Присваивает значение заданному смещению итератора.
     *
     * @param string|int $offset Смещение (ключ), которому будет присваиваться значение.
     * @param mixed $value Значение для присвоения.
     * 
     * @return void
     */
    public function offsetSetErrata(string|int $offset, mixed $value): void
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        $this->errata[$offset] = $value;
    }

    /**
     * Возвращает заданное смещение (ключ) итератора.
     *
     * @param mixed $offset Смещение (ключ) для возврата.
     * 
     * @return mixed
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offsetGetErrata(string|int $offset): mixed
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        if (!array_key_exists($offset, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->errata[$offset];
    }

    /**
     * Проверяет возможность смещения.
     *
     * @param string|int $offset Смещение (ключ).
     * 
     * @return bool
     */
    public function offsetHasErrata(string|int $offset): bool
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        return (isset($this->errata[$offset]));
    }

    /**
     *  Удаляет смещение итератора.
     *
     * @param string|int $offset Смещение (ключ) для удаления.
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetErrata(string|int $offset): void
    {
        if (is_int($offset)) {
            $offset = $this->positions[$offset];
        }
        if (!array_key_exists($offset, $this->errata)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->errata[$offset] = null;
    }

    /**
     * Возвращает итератор.
     *
     * @return \ArrayIterator
     */
    public function getErrataIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->errata);
    }

    /**
     * Возвращает все элементы контейнера.
     *
     * @return array
     */
    public function getNamedArray(): array
    {
        return $this->data;
    }

    /**
     * Выбирает все значения элементов контейнера.
     * 
     * @return array Индексированный массив значений. 
     */
    public function getPositionalArray(): array
    {
        return array_values($this->data);
    }

    /**
     * Подсчитывает количество элементов контейнера.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Возвращает значение текущего элемент контейнера.
     *
     * @return mixed Возвращает значение элемента контейнера, на который указывает 
     *     его внутренний указатель. Не перемещает указатель куда бы то ни было. 
     *     Если внутренний указатель находится за пределами списка элементов или 
     *     коллекция пуста, возвращает `false`. 
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Перемещает указатель контейнера вперёд на один элемент.
     *
     * @return mixed Возвращает значение элемента контейнера, находящегося на позиции, 
     *     следующей за позицией внутренний указателя или `false`, если достигнут конец 
     *     коллекции. 
     */
    public function next(): mixed
    {
        return next($this->data);
    }

    /**
     * Возвращает первый ключ элемента контейнера.
     * 
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Выполняет проверку текущего элемента контейнера.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return (current($this->data) !== false);
    }

    /**
     * Устанавливает внутренний указатель контейнера на его первый элемент.
     *
     * @return mixed Возвращает значение первого элемента контейнера, или `false`, 
     *     если контейнер пуст. 
     */
    public function rewind(): mixed
    {
        return reset($this->data);
    }

    /**
     * Сливает массив или контейнер с текущем.
     * 
     * @param array|ParameterContainer $parameters
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function merge(array|ParameterContainer $parameters): static
    {
        if (!is_array($parameters) && !$parameters instanceof ParameterContainer) {
            throw new Exception\InvalidArgumentException('$parameters must be an array or an instance of ParameterContainer');
        }

        if (count($parameters) == 0) {
            return $this;
        }

        if ($parameters instanceof ParameterContainer) {
            $parameters = $parameters->getNamedArray();
        }

        foreach ($parameters as $key => $value) {
            if (is_int($key)) {
                $key = null;
            }
            $this->offsetSet($key, $value);
        }
        return $this;
    }
}
