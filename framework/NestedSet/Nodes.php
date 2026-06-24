<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\NestedSet;

use Ge\Db\ActionRecord;
use Ge\Db\Sql\Expression;
use Ge\Db\Sql\Predicate\Between;
use Ge\Db\Sql\Predicate\Operator;

/**
 * Класс предназначен для обработки иерархических данных в виде вложенного множества.
 * 
 * Имеет необходимый функционал для манипуляции с узлами дерева Nested Set (вложенного множества).
 * Цель состоит в том, чтобы упростить получение полного или частичного дерева из базы данных 
 * с одного запроса.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\NestedSet
 * @since 2.0
 */
class Nodes extends ActionRecord
{
    /**
     * Имя таблицы, используемой для выполнения запросов.
     *
     * @var string
     */
    public string $tableName = '';

    /**
     * Имя столбца определяющий идентификаторы узлов.
     *
     * @var string
     */
    public string $idColumn = 'id';

    /**
     * Имя столбца определяющий левую границу узлов.
     *
     * @var string
     */
    public string $leftColumn = 'ns_left';

    /**
     * Имя столбца определяющий правую границу узлов.
     *
     * @var string
     */
    public string $rightColumn = 'ns_right';

    /**
     * Имя столбца определяющий названия узлов.
     *
     * @var string
     */
    public string $nameColumn = 'name';

    /**
     * Имя столбца определяющий идентификатор родительских узлов.
     * Является не обязательным.
     *
     * @var string|null
     */
    public ?string $parentColumn = null;

    /**
     * Добавляет узел дерева.
     * 
     * @param array<string, mixed> $node Атрибуты добавляемого узла в виде пар "ключ - значение".
     * @param null|int|array{idColumn:int, leftColumn:int, rightColumn:int} $parent Идентификатор 
     *     родительского узла или его атрибуты. Если значение `null`, то он не будет добавлен в 
     *     дерево, а не родительскому узлу (по умолчанию `null`).
     *
     * @return mixed Если добавление успешно, возвращает идентификатор добавленного 
     *     узла, иначе значение `false`.
     */
    public function add(array $node, int|array|null $parent = null): mixed
    {
        if ($parent === null)
            return $this->append($node);
        else
            return $this->addChild($node, $parent);
    }

    /**
     * Возвращает максимальное количество узлов в дереве.
     * 
     * @return int
     */
    public function getMaxRightColumn(): int
    {
        /** @var array{max:int} $row */
        $row = $this->getRecord(
            $this->tableName, 
            [], 
            ['max' => new Expression("MAX({$this->rightColumn})")]
        );
        return (int) $row['max'];
    }

    /**
     * Добавляет узел дерева, как последний элемент.
     * 
     * @param array<string, mixed> $node Атрибуты добавляемого узла в виде пар "ключ - значение".
     * 
     * @return mixed Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе последний идентификатор добавленного узла.
     */
    public function append(array $node): mixed
    {
        /** @var int $max */
        $max = $this->getMaxRightColumn();

        // добавить новый элемент как последний узел
        $node[$this->leftColumn]  = $max + 1;
        $node[$this->rightColumn] = $max + 2;
        return $this->insertRecord($this->tableName, $node);
    }

    /**
     * Добавляет узел дерева.
     * 
     * Элемент становится его дочерним узлом.
     *
     * @param array $node Атрибуты добавляемого элемента (имя поля => значение).
     * @param null|int|array{idColumn:int, leftColumn:int, rightColumn:int} $parent Идентификатор 
     *     родительского узла или его атрибуты. Если значение `null`, то он не будет добавлен в 
     *     дерево, а не родительскому узлу (по умолчанию `null`).
     *
     * @return mixed Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе последний идентификатор добавленного узла.
     */
    public function addChild(array $node, int|array $parent): mixed
    {
        if (is_int($parent)) {
            /** @var array $parent */
            $parent = $this->getNode($parent);
            if (empty($parent)) return false;
        }
        $parentId = $parent[$this->idColumn];

        $right = $parent[$this->rightColumn];
        // переместите следующие элементы вправо, чтобы освободить место
        $this->updateRecord(
            $this->tableName,
            [$this->rightColumn => new Expression("{$this->rightColumn} + 2")],
            [new Operator($this->rightColumn, '>', $right)]
        );
        // переместить следующие элементы влево
        $this->updateRecord(
            $this->tableName,
            [$this->leftColumn => new Expression("{$this->leftColumn} + 2")],
            [new Operator($this->leftColumn, '>', $right)]
        );
        // освободить место в родительском элементе
        $this->updateRecord(
            $this->tableName,
            [$this->rightColumn => new Expression("{$this->rightColumn} + 2")],
            [$this->idColumn => $parentId]
        );

        // добавить новый узел
        $node[$this->leftColumn]  = $right;
        $node[$this->rightColumn] = $right + 1;
        if ($this->parentColumn !== null) {
            $node[$this->parentColumn] = $parentId;
        }
        return $this->insertRecord($this->tableName, $node);
    }

    /**
     * Удаляет узел дерева с рекурсивным удалением дочерних узлов.
     *
     * @param int|array{idColumn:int, leftColumn:int, rightColumn:int} $node Идентификатор 
     *     узла дерева или его атрибуты.
     * @param bool $recursive Если true, рекурсивное удаление дочерних узлов (по умолчанию true).
     *
     * @return false|int Если false, узел с указанным идентификатором не существует. Иначе, 
     *    количество удаленных дочерних узлов (включая предка).
     */
    public function delete(int|array $node, bool $recursive = true): false|int
    {
        if (is_int($node)) {
            /** @var array $node */
            $node = $this->getNode($node);
            if (empty($node)) return false;
        }

        // если рекурсивно
        if ($recursive)
            return $this->deleteRecursive($node);
        else
            return $this->deleteNonRecursive($node);
    }

    /**
     * Рекурсивное удаление дочерних узлов.
     *
     * @param array{leftColumn:int, rightColumn:int} $node Атрибуты удаляемого узла в 
     *     виде пар "ключ - значение".
     *
     * @return int Количество удаленных дочерних узлов. Если значение '0', узлы дерева
     *     не были удалены.
     */
    public function deleteRecursive(array $node): int
    {
        // интервал дочерних узлов
        $left  = (int) $node[$this->leftColumn];
        $right = (int) $node[$this->rightColumn];

        /** @var false|int $count */
        $count = $this->deleteRecord($this->tableName, new Between($this->leftColumn, $left, $right));
        // если узел не удален
        if ($count === false || $count === 0) return 0;

        // интервал обновления узлов
        $width = $right - $left + 1;
        // обновить правую границу
        $this->updateRecord(
            $this->tableName,
            [$this->rightColumn => new Expression("{$this->rightColumn} - $width")],
            [new Operator($this->rightColumn, '>', $right)]
        );
        // обновить левую границу
        $this->updateRecord(
            $this->tableName,
            [$this->leftColumn => new Expression("{$this->leftColumn} - $width")],
            [new Operator($this->leftColumn, '>', $right)]
        );
        return $count;
    }

    /**
     * Удаляет узел, но предварительно перемещает его дочернии узлы за пределы своего 
     * диапазона.
     *
     * @param array{idColumn:int, leftColumn:int, rightColumn:int} $node Атрибуты узла в 
     *     виде пар "ключ - значение".
     *
     * @return false|int Возвращает значение `false`, если ошибка удаления, иначе значение '1'.
     */
    public function deleteNonRecursive(array $node): false|int
    {
        $left  = (int) $node[$this->leftColumn];
        $right = (int) $node[$this->rightColumn];

        /** @var false|int $result */
        $result = $this->deleteRecord($this->tableName, [$this->idColumn => $node[$this->idColumn]]);
        if ($result === false) return false;

        // для остальных узлов
        $width = 2;
        // обновить правую границу для внутренних узлов
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getCommand();
        // если необходимо обновить родительский столбец
        if ($this->parentColumn) {
            $this->updateRecord(
                $this->tableName, 
                [$this->parentColumn => null], 
                [$this->leftColumn => $left + 1]
            );
        }
        $command->setSql(
            "UPDATE {$this->tableName}
               SET {$this->rightColumn} = {$this->rightColumn} - 1
             WHERE {$this->leftColumn} > $left AND {$this->rightColumn} < $right"
        )->execute();
        // обновить левую границу для внутренних узлов
        $command->setSql(
            "UPDATE {$this->tableName}
               SET {$this->leftColumn} = {$this->leftColumn} - 1
             WHERE {$this->leftColumn} > $left AND {$this->rightColumn} < $right"
        )->execute();
        // обновить правую границу для внешних узлов
        $command->setSql(
            "UPDATE {$this->tableName}
               SET {$this->rightColumn} = {$this->rightColumn} - $width
             WHERE {$this->rightColumn} > $right"
        )->execute();
        // обновить левую границу для внешних узлов
        $command->setSql(
            "UPDATE {$this->tableName}
               SET {$this->leftColumn} = {$this->leftColumn} - $width
             WHERE {$this->leftColumn} > $left AND {$this->rightColumn} >= $right"
        )->execute();
        return 1;
    }

    /**
     * Возвращает узел или узлы по указанному идентификатору.
     *
     * @param int|array<int, int> $nodeId Идентификатор узла или узлов.
     * @param string|array<string, string> $order Порядок сортировки, например: 'field, ASC', 
     *     `['field' => 'ASC', 'field1' => 'DESC']` (по умолчанию `null`).
     * 
     * @return null|array
     */
    public function getNode(int|array $nodeId, array|string $order = ''): ?array
    {
        if (is_array($nodeId)) {
            return $this->getRecords(
                $this->tableName, ['*'], [$this->idColumn => $nodeId], null, $order
            );
        }

        return $this->getRecord($this->tableName, [$this->idColumn => $nodeId], ['*']);
    }

    /**
     * Возвращает ширину границы указанного узла.
     *
     * Где шириниа определяется как: [правая граница] - [левая граница] + 1.
     * 
     * @param int|array{leftColumn:int, rightColumn:int} $node Идентификатор узла 
     *     или его атрибуты.
     * 
     * @return int
     */
    public function getNodeWidth(int|array $node): int
    {
        if (is_int($node)) {
            $node = $this->getNode($node);
            if (empty($node)) return 0;
        }
        return $node[$this->rightColumn] - $node[$this->leftColumn] + 1;
    }

    /**
     * Возвращает дочернии узлы родителя.
     *
     * @param int|array{leftColumn:int, rightColumn:int} $node Идентификатор узла родителя 
     *     или его атрибуты.
     * @param bool $includeParent Если true, первая запись будет - указанный узел.
     * @param string|array<string, string> $order Порядок сортировки, например: 'field, ASC', 
     *     `['field' => 'ASC', 'field1' => 'DESC']` (по умолчанию '').
     *
     * @return array
     */
    public function getChildNodes(int|array $node, bool $includeParent = false, array|string $order = ''): array
    {
        if (is_int($node)) {
            $node = $this->getNode($node);
            if (empty($node)) return [];
        }

        if (empty($order)) {
            $order = [$this->leftColumn => 'ASC'];
        }
        return $this->getRecords(
            $this->tableName, 
            ['*'],
            [new Between($this->leftColumn, $node[$this->leftColumn] + ($includeParent ? 0: 1), $node[$this->rightColumn])],
            null,
            $order
        );
    }

    /**
     * Возвращает количество дочерних узлов в указанном.
     *
     * @param int|array{leftColumn:int, rightColumn:int} $node Идентификатор узла родителя 
     *     или его атрибуты.
     *
     * @return int
     */
    public function getChildCount(int|array $node): int
    {
        if (is_int($node)) {
            $node = $this->getNode($node);
            if (empty($node)) return 0;
        }
        return ($node[$this->rightColumn] - $node[$this->leftColumn] - 1) / 2;
    }

    /**
     * Возвращает родительский узел.
     *
     * @param int|array{leftColumn:int, rightColumn:int} $child Идентификатор дочернего 
     *     узла или его атрибуты.
     * @param int|null $depth Глубина (уровень) поиска. Если значение `null`, то
     *     возвратит все родительские узлы относительно указанного без учёта 
     *     глубины (по умолчанию '1').
     *
     * @return array|null Возвращает значение `null`, если для указанного узла нет 
     *     родительского.
     */
    public function getParent(int|array $child, ?int $depth = 1): ?array
    {
        if (is_int($child)) {
            /** @var false|array $child */
            $child = $this->getNode($child);
            if (empty($child)) return null;
        }

        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->db->select($this->tableName)
            ->columns(['*'])
            ->order($this->leftColumn);
        if ($depth) {
            $select->limit($depth);
        }
        $select->where->lessThan($this->leftColumn, $child[$this->leftColumn]);
        $select->where->greaterThan($this->rightColumn, $child[$this->rightColumn]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getCommand();
        $command->setSql($select);
        return $command->queryAll();
    }

    /**
     * +Возвращает все узлы, которые не имеют дочерних узлов.
     * 
     * @param string|array<string, string> $order Порядок сортировки, например: 'field, ASC', 
     *     `['field' => 'ASC', 'field1' => 'DESC']` (по умолчанию '').
     * 
     * @return array
     */
    public function getLeafs(string|array $order = ''): array
    {
        return $this->getRecords(
            $this->tableName, 
            ['*'], 
            "{$this->rightColumn} = {$this->leftColumn} + 1",
            null,
            $order
        );
    }

    /**
     * Выполняет перемещение узла дерева.
     * 
     * @param int|array{idColumn:int, leftColumn:int, rightColumn:int} $fromNode 
     *     Идентификатор или атрибуты перемещаемого узла.
     * @param int|array{idColumn:int, leftColumn:int, rightColumn:int} $toNode 
     *     Идентификатор или атрибуты узла назначения.
     * @param string $position Положение перемещаемого узла: 'into', 'before', 'after' 
     *     (по умолчанию `null`).
     *
     * @return bool Возвращает значение `false`, если переместить невозможно.
     */
    public function move(int|array $fromNode, int|array $toNode, string $position = 'into'): bool
    {
        if (is_int($fromNode)) {
            $fromNode = $this->getNode((int) $fromNode);
        }
        if (is_int($toNode)) {
            $toNode = $this->getNode((int) $toNode);
        }

        // если один из узлов отсутствует
        if (empty($fromNode) || empty($toNode)) return false;

        switch ($position) {
            case 'into':

            default:
                return $this->moveRange($fromNode, $toNode);
        }
        return false;
    }

    /**
     * Проверяет, есть ли возможность перемещения узла дерева.
     * 
     * @param array{leftColumn:int, rightColumn:int} $fromNode Атрибуты перемещаемого узла.
     * @param array{leftColumn:int, rightColumn:int} $toNode Атрибуты узла назначения.
     *
     * @return bool Возвращает значение `false`, если переместить невозможно.
     */
    public function canMove(array $fromNode, array $toNode): bool
    {
        return !($toNode[$this->leftColumn] >= $fromNode[$this->leftColumn] && $toNode[$this->rightColumn] <= $fromNode[$this->rightColumn]);
    }

    /**
     * Выполняет перемещение узла дерева.
     * 
     * @param array{idColumn:int, leftColumn:int, rightColumn:int} $fromNode Атрибуты перемещаемого узла.
     * @param array{idColumn:int, leftColumn:int, rightColumn:int} $toNode Атрибуты узла назначения.
     *
     * @return bool Возвращает значение `false`, если переместить невозможно.
     */
    public function moveRange(array $fromNode, array $toNode): bool
    {
        // проверяем, можно ли переместить узел
        if (!$this->canMove($fromNode, $toNode)) return false;

        // определяем диапазон перемещения
        $fromNodeWidth = $this->getNodeWidth($fromNode[$this->idColumn]);

        // перемещаем правую границу
        $this->updateRecord(
            $this->tableName,
            [
                $this->rightColumn => new Expression("{$this->rightColumn} + $fromNodeWidth")
            ],
            [new Operator($this->rightColumn, '>=', $toNode[$this->rightColumn])]
        );

        // перемещаем левую границу
        $this->updateRecord(
            $this->tableName,
            [
                $this->leftColumn => new Expression("{$this->leftColumn} + $fromNodeWidth")
            ],
            [new Operator($this->leftColumn, '>', $toNode[$this->rightColumn])]
        );

        // перемещаем указанный узел с его потомками
        $fromNode = $this->getChildNodes($fromNode[$this->idColumn], true);
        $сhildrenId = [];
        foreach ($fromNode as $one) {
            array_push($сhildrenId, $one[$this->idColumn]);
        }

        $difference = $toNode[$this->rightColumn] - $fromNode[0][$this->leftColumn];
        $this->updateRecord(
            $this->tableName,
            [
                $this->leftColumn  => new Expression("{$this->leftColumn} + $difference"),
                $this->rightColumn => new Expression("{$this->rightColumn} + $difference")
            ],
            [$this->idColumn => $сhildrenId]
        );

        // переместить то, что было справа от узла
        $this->updateRecord(
            $this->tableName,
            [
                $this->leftColumn => new Expression("{$this->leftColumn} - $fromNodeWidth"),
            ],
            [new Operator($this->leftColumn, '>', $fromNode[0][$this->leftColumn])]
        );

        $this->updateRecord(
            $this->tableName,
            [
                $this->rightColumn => new Expression("{$this->rightColumn} - $fromNodeWidth"),
            ],
            [new Operator($this->rightColumn, '>', $fromNode[0][$this->rightColumn])]
        );
        return true;
    }

    /**
     * Обновляет значение столбца в дочерних узлах.
     * 
     * @param array{leftColumn:int, rightColumn:int} $parentNode Идентификатор или 
     *     атрибуты родительского узла.
     * @param string $column Название обновляемого столбца.
     * @param mixed $newValue Новое значение столбца.
     * @param mixed $oldValue Старое значение столбца.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество обновленных узлов.
     */
    public function replaceChildsColumn(array|int $parentNode, string $column, mixed $newValue, mixed $oldValue): false|int
    {
        if (is_int($parentNode)) {
            /** @var false|array $parentNode */
            $parentNode = $this->getNode($parentNode);
            if (empty($parentNode)) return false;
        }

        return $this->updateRecord(
            $this->tableName,
            [
                $column => new Expression("REPLACE($column, '$oldValue', '$newValue')")
            ],
            [
                new Operator($this->leftColumn, '>', $parentNode[$this->leftColumn]),
                new Operator($this->rightColumn, '<', $parentNode[$this->rightColumn])
            ]
        );
    }

    /**
     * Обновляет значения столбцов в дочерних узлах.
     * 
     * @param array{leftColumn:int, rightColumn:int} $parentNode Идентификатор или 
     *     атрибуты родительского узла.
     * @param array<string, mixed> $columns Имена столбцов с их значениями в виде 
     *     пар "ключ - значение".
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество обновленных узлов.
     */
    public function updateChilds(array|int $parentNode, array $columns): false|int
    {
        if (is_int($parentNode)) {
            /** @var false|array $parentNode */
            $parentNode = $this->getNode($parentNode);
            if (empty($parentNode)) return false;
        }

        return $this->updateRecord(
            $this->tableName,
            $columns,
            [
                new Operator($this->leftColumn, '>', $parentNode[$this->leftColumn]),
                new Operator($this->rightColumn, '<', $parentNode[$this->rightColumn])
            ]
        );
    }

    /**
     * Удаляет все узлы дерева.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество удалённых записей.
     */
    public function clean(): false|int
    {
        $result = $this->deleteRecord($this->tableName, []);
        if ($result) {
            $this->resetIncrement($this->tableName);
        }
        return $result;
    }
}
