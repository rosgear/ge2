<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data;

/**
 * Трейт данных элементов списка смежностей (adjacency list).
 * 
 * Используется совместно с классами унаследованным от Активной записи {@see \Ge\Db\ActiveRecord}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data
 * @since 2.0
 */
trait AdjacencyListTrait
{
    /**
     * Все эелементы списка.
     * 
     * @see AdjacencyListTrait::getItems()
     * 
     * @var array|null
     */
    protected $items;

    /**
     * Дерево эеементов списка.
     * 
     * @see AdjacencyListTrait::getTree()
     * 
     * @var array|null
     */
    protected $tree;

    /**
     * Корневые эелементы списка.
     * 
     * @see AdjacencyListTrait::getRoot()
     * @see AdjacencyListTrait::getParents()
     * 
     * @var array|null
     */
    protected $root;

    /**
     * Дочернии элементы списка.
     * 
     * @see AdjacencyListTrait::getRoot()
     * @see AdjacencyListTrait::getParents()
     * 
     * @var array|null
     */
    protected $parents;

    /**
     * @var string|null
     */
    protected $callableItem = '';

    /**
     * Возвращает название столбца таблицы, значение которого определяет количество 
     * дочерних элементов у родителя.
     * 
     * @return string
     */
    public function countKey(): string
    {
        return 'count';
    }

    /**
     * Возвращает название столбца таблицы,  значение которого определяет принадлежность 
     * элемента к родителю.
     * 
     * @return string
     */
    public function parentKey(): string
    {
        return 'parent_id';
    }

    /**
     * Обновляет элемент списка.
     * 
     * @param array $item Атрибуты элемента с их значениями в виде пар "ключ - значение".
     * @param int $id Идентификатор обновляемого элемента списка.
     * 
     * @return bool|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество обновленных записей.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function updateItem(array $item, int $id)
    {
        return $this->updateRecord($item, [$this->primaryKey() => $id], $this->tableName());
    }

    /**
     * Удаляет элемент из списка.
     * 
     * @param int $id Идентификатор элемента списка.
     * @param bool $descendants Если значение `true`, удалить элемент с потомками 
     *     (по умолчанию `false`).
     * 
     * @return array Возвращает результат удаления элемента списка: 
     *     `[результат, массив удалённых идентификаторов]`.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function deleteItem(int $id, bool $descendants = false)
    {
        if ($descendants) {
            $id = $this->getItemsById([$id]);
        }
        $result = $this->deleteRecord([$this->primaryKey() => $id], $this->tableName());
        return [$result, $id];
    }

    /**
     * Удаляет элементы из списка.
     * 
     * @param int[]|int $ids Идентификаторы элементов списка.
     * @param bool $descendants Если значение `true`, удалять элементы с потомками 
     *     (по умолчанию `false`).
     * 
     * @return array Возвращает результат удаления элементов списка: 
     *     `[результат, массив удалённых идентификаторов]`.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function deleteItems($ids, bool $descendants = false): array
    {
        if ($descendants) {
            $ids = $this->getItemsById($ids);
        }
        $result = $this->deleteRecord([$this->$this->primaryKey() => $ids], $this->tableName());
        return [$result, $ids];
    }

    /**
     * Добавляет элемент в список.
     * 
     * @param array $item Имена полей с их значениями в виде пар "ключ - значение".
     * 
     * @return mixed Возвращает сгенерированный идентификатор добавленного элемента. 
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function addItem(array $item)
    {
        return $this->insertRecord($item, $this->tableName());
    }

    /**
     * Добавляет элемент в список к указанному родителю.
     * 
     * @param array $item Имена полей с их значениями в виде пар "ключ - значение".
     * @param int $parentId Идентификатор элемента родителя.
     * 
     * @return mixed Возвращает сгенерированный идентификатор добавленного элемента. 
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function addItemToParent(array $item, int $parentId)
    {
        $item[$this->parentKey()] = $parentId;
        return $this->insertRecord($item, $this->tableName());
    }

    /**
     * Отвязывает все элементы списка от родителя.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * 
     * @return mixed Возвращает значение `false`, если оишбка выполнения запроса.
     *     Иначе количетсво отвязанных элементов списка от родителя.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function unbindItems(int $parentId)
    {
        return $this->updateRecord(
            [$this->parentKey() => null],
            [$this->parentKey() => $parentId],
            $this->tableName()
        );
    }

    /**
     * Привязывает указанные элементы списка к родителю.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * @param int|string|array $id Идентификатор(ы) элементов списка. Имеют вид:
     *     - `1,2,3,4`;
     *     - `[1, 2, 3, 4]`;
     *     - `1`.
     * 
     * @return mixed Возвращает значение `false`, если оишбка выполнения запроса.
     *     Иначе количетсво привязанных элементов списка к родителю.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function bindItems(int $parentId, $id)
    {
        if (is_string($id) && !is_numeric($id)) {
            $id = explode(',', $id);
        }
        return $this->updateRecord(
            [$this->parentKey()  => $parentId],
            [$this->primaryKey() => $id],
            $this->tableName()
        );
    }

    /**
     * Возвращает дочерние элементы списка.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * @param bool $all Если значение `true`, все дочерние элементы указанного 
     *     родителя. Иначе, дочерние элементы на уровень ниже.
     * 
     * @return mixed Возвращает дочерние элементы родителя.
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function getChildItems(int $parentId, bool $all = false): array
    {
        if ($all)
            return $this->getItemsById($parentId, true);
        else
            return $this->fetchAll(null, ['*'], [$this->parentKey() => $parentId]);
    }

    /**
     * Возвращает все элементы списка.
     * 
     * @return array Возвращает элементы списка в виде пар "ключ - значение".
     * 
     * @throws \Ge\Db\Adapter\Exception\CommandException Ошибка выполнения инструкции SQL.
     */
    public function getAllItems(): array
    {
        if ($this->items === null) {
            $this->items = $this->fetchAll('id');
        }
        return $this->items;
    }

    /**
     * Создаёт дерево элементов из указанного списка.
     * 
     * @param array $items Список элементов.
     * @param array $childs Дочернии элементы списка, используемые в процессе итерации.
     * 
     * @return void
     */
    protected function createItemsTree(array &$items, array $childs)
    {
        foreach ($items as $itemId => $item) {
            if (isset($childs[$itemId])) {
               $items[$itemId]['items'] = &$childs[$itemId];
               $this->createItemsTree($items[$itemId]['items'], $childs);
            }
        }
    }

    /**
     * Возвращает массив идентификаторов (включая дочерние элементы) списка.
     * 
     * @param int[] $result Результат, массив идентификаторов.
     * @param int[] $id Идентификаторы элементов для которых необходимо найти дочерние элементы.
     * @param array $childs Ассоциативный массив дочерних элементов списка, 
     *     где ключ - идентификатор родитель-о элемента.
     * 
     * @return void
     */
    public function collectItemsId($id, $childs, &$result)
    {
        foreach ($id as $itemId) {
            $result[] = $itemId;
            if (isset($childs[$itemId])) {
                $keys = array_keys($childs[$itemId]);
                $this->collectItemsId($keys, $childs, $result);
            }
        }
    }

    /**
     * Возвращает идентификаторы и информацию элементов (включая дочернии) списка.
     * 
     * @param int[] $id Идентификаторы элементов для которых необходимо найти дочерние элементы.
     * @param array $info Если true, возвращает информацию по указанным элементам.
     * 
     * @return array
     */
    public function getItemsById($id = [], $info = false)
    {
        if (empty($id)) return [];

        $id = (array) $id;
        $result = [];
        $this->collectItemsId($id, $this->getParents(), $result);
        // убираем дублирующие id
        $result = array_unique($result, SORT_NUMERIC);
        if ($info) {
            $itemsInfo = [];
            foreach ($result as $idOne) {
                $itemsInfo[] = $this->items[$idOne];
            }
            return $itemsInfo;
        }
        return $result;
    }

    /**
     * Определяет параметры каждого элемента перед формированием дерева.
     * 
     * @see AdjacencyListTrait::splitItems()
     * 
     * @param array $row Атрибуты записей с их значениями, полученные из SQL-запроса.
     * @param bool $isChild Элемент является потомком.
     * 
     * @return array
     * 
     * protected function eachItem(array $row, bool $isChild): array
     * {}
     */

    /**
     * Разделяет элементы списка на родительские и корневые.
     * 
     * @param array $items Элементы списка.
     * 
     * @return array
     */
    protected function splitItems(array $items): array
    {
        $childItems = $root = [];

        // возможность переберать элементы
        $isCallableItem = is_callable([$this, 'eachItem']);
        foreach ($items as $id => $item) {
            $parentId = (int) ($item['parent_id'] ?? 0);
            $id       = (int) $id;
            if ($parentId) {
                // если ссылочная целостность не нарушена
                if (isset($items[$parentId])) {
                    if (!isset($childItems[$parentId])) {
                        $childItems[$parentId] = [];
                    }
                    $childItems[$parentId][$id] = $isCallableItem ?  $this->eachItem($item, true) : ['id' => $id];
                }
            }
            if (!$parentId)
                $root[$id] = $isCallableItem ? $this->eachItem($item, false) : ['id' => $id];
        }
        return [
            'root'    => $root,
            'parents' => $childItems
        ];
    }

    /**
     * Возвращает корневые элементов списка.
     * 
     * @return array
     */
    public function getRoot(): array
    {
        if ($this->root === null) {
            $items = $this->splitItems($this->getAllItems());
            $this->root    = $items['root'];
            $this->parents = $items['parents'];
        }
        return $this->root;
    }

    /**
     * Возвращает все родительские элементы списка с дочерними.
     * 
     * @return array
     */
    public function getParents(): array
    {
        if ($this->parents === null) {
            $items = $this->splitItems($this->getAllItems());
            $this->root    = $items['root'];
            $this->parents = $items['parents'];
        }
        return $this->parents;
    }

    /**
     * Возвращает дерево элементов списка.
     * 
     * @return array
     */
    public function getTree(): array
    {
        if ($this->tree === null) {
            $root    = $this->getRoot();
            $parents = $this->getParents();
            $this->createItemsTree($root, $parents);
            $this->tree = $root;
        }
        return $this->tree;
    }

    /**
     * Обновляет количество дочерних элементов для каждого родителя.
     * 
     * @return bool|int Возвращает значение `false`, если ошибка запроса. Иначе, 
     *     количество обновленных элементов.
     */
    public function updateItems()
    {
        $command = $this->getDb()->createCommand();
        $command->update(
            $this->tableName(),
            [$this->countKey() => 0]
        );
        $command->execute();
        $command->bindValues([
            '@table'     => $this->tableName(),
            '@parentKey' => $this->parentKey(),
            '@countKey'  => $this->countKey()
        ]);
        $command->setSql(
            'UPDATE `:@table` `rows`, (SELECT COUNT(*) `total`, `:@parentKey` FROM `:@table` WHERE parent_id IS NOT NULL GROUP BY `:@parentKey`) `list` '
          . 'SET `rows`.`:@countKey`=`list`.`total` WHERE `rows`.`id`=`list`.`:@parentKey`'
        );
        return $command->execute();
    }
}
