<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

/**
 * Класс списка приоритетов.
 * 
 * Применяется для установки приоритетности элементов в массиве.
 * Приминение {@see \Ge\Db\Sql\Update::$set}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class PriorityList implements \Iterator, \Countable
{
    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    /**
     * Внутренний список всех элементов.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Серийный номер, присвоенный элементам, подлежащим сохранению LIFO.
     *
     * @var int
     */
    protected $serial = 0;

    /**
     * Режим сортировки элементов по серийному номеру.
     * 
     * @var int
     */
    protected $isLIFO = 1;

    /**
     * Внутренний счетчик, чтобы избежать использования count().
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Был ли список уже отсортирован.
     *
     * @var bool
     */
    protected $sorted = false;

    /**
     * Вставляет новый элемент.
     *
     * @param string $name Название элемента.
     * @param mixed $value Значение элемента.
     * @param int $priority Приоритет.
     *
     * @return void
     */
    public function insert(string $name, $value, int $priority = 0)
    {
        if (!isset($this->items[$name])) {
            $this->count++;
        }

        $this->sorted = false;

        $this->items[$name] = array(
            'data'     => $value,
            'priority' => (int) $priority,
            'serial'   => $this->serial++,
        );
    }

    /**
     * Устанавливает приоритет элементу.
     * 
     * @param string $name Название элемента.
     * @param int $priority Приоритет.
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setPriority(string $name, int $priority)
    {
        if (!isset($this->items[$name])) {
            throw new \Exception("item $name not found");
        }

        $this->items[$name]['priority'] = (int) $priority;
        $this->sorted                   = false;

        return $this;
    }

    /**
     * Удаляет элемент.
     *
     * @param string $name Название элемента.
     * 
     * @return void
     */
    public function remove(string $name)
    {
        if (isset($this->items[$name])) {
            $this->count--;
        }

        unset($this->items[$name]);
    }

    /**
     * Удаляет все элементы.
     *
     * @return void
     */
    public function clear()
    {
        $this->items  = [];
        $this->serial = 0;
        $this->count  = 0;
        $this->sorted = false;
    }

    /**
     * Возвращает элемент.
     *
     * @param string $name Название элемента.
     * 
     * @return mixed
     */
    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            return;
        }

        return $this->items[$name]['data'];
    }

    /**
     * Сортирует все элементы.
     *
     * @return void
     */
    protected function sort()
    {
        if (!$this->sorted) {
            uasort($this->items, array($this, 'compare'));
            $this->sorted = true;
        }
    }

    /**
     * Сравните приоритет двух элементов.
     *
     * @param array $item1 Элемент 1.
     * @param array $item2 Элемент 2.
     * 
     * @return int
     */
    protected function compare(array $item1, array $item2)
    {
        return ($item1['priority'] === $item2['priority'])
            ? ($item1['serial']   > $item2['serial']   ? -1 : 1) * $this->isLIFO
            : ($item1['priority'] > $item2['priority'] ? -1 : 1);
    }

    /**
     * Возвращает или устанавливает режим сортировки элементов.
     * 
     * @param bool|null $flag
     *
     * @return bool
     */
    public function isLIFO($flag = null)
    {
        if ($flag !== null) {
            $isLifo = $flag === true ? 1 : -1;

            if ($isLifo !== $this->isLIFO) {
                $this->isLIFO = $isLifo;
                $this->sorted = false;
            }
        }

        return 1 === $this->isLIFO;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->sort();
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function current(): mixed
    {
        $this->sorted || $this->sort();
        $node = current($this->items);

        return $node ? $node['data'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): mixed
    {
        $this->sorted || $this->sort();
        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return current($this->items) !== false;
    }

    /**
     * @return $this
     */
    public function getIterator()
    {
        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Возвращает весь список элементов.
     *
     * @param int $flag `EXTR_DATA`, `EXTR_DATA`, `EXTR_BOTH` (по умолчанию `EXTR_DATA`).
     *
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA): array
    {
        $this->sort();

        if ($flag == self::EXTR_BOTH) {
            return $this->items;
        }

        return array_map(
            function ($item) use ($flag) {
                return ($flag == PriorityList::EXTR_PRIORITY) ? $item['priority'] : $item['data'];
            },
            $this->items
        );
    }
}
