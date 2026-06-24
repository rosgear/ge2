<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Rbac;

use RecursiveIterator;

/**
 * Абстрактный класс итератора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac
 * @since 2.0
 */
abstract class AbstractIterator implements RecursiveIterator
{
    /**
     * Порядковый номер.
     * 
     * @var int
     */
    protected int $index = 0;

    /**
     * Потомки.
     * 
     * @var array
     */
    protected array $children = [];

    /**
     * Возврат текущего элемента.
     * 
     * @link https://www.php.net/manual/ru/iterator.current.php
     * 
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->children[$this->index];
    }

    /**
     * Переход вперед к следующему элементу.
     * 
     * @link https://www.php.net/manual/ru/iterator.next.php
     * 
     * @return void
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Вернуть ключ текущего элемента.
     * 
     * @link http://php.net/manual/en/iterator.key.php
     * 
     * @return mixed
     */
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * Проверяет правильность текущей позиции.
     * 
     * @link https://www.php.net/manual/ru/iterator.valid.php
     * 
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->children[$this->index]);
    }

    /**
     * Перевести Iterator в первый элемент.
     * 
     * @link https://www.php.net/manual/ru/iterator.rewind.php
     * 
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Возвращает, если для текущей записи может быть создан итератор.
     * 
     * @link http://php.net/manual/en/recursiveiterator.haschildren.php
     * 
     * @return bool
     */
    public function hasChildren(): bool
    {
        if ($this->valid() && ($this->current() instanceof RecursiveIterator)) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает итератор для текущей записи.
     * 
     * @link https://www.php.net/manual/ru/recursiveiterator.getchildren.php
     * 
     * @return RecursiveIterator
     */
    public function getChildren(): ?RecursiveIterator
    {
        return $this->children[$this->index];
    }
}
