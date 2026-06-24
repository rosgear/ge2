<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data;

use Ge\Data\Model\BaseModel;

/**
 * Модель данных элементов списка смежностей (adjacency list).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data
 * @since 2.0
 */
class AdjacencyList extends BaseModel
{
    /**
     * Менеджер данных.
     * 
     * @var DataManager|null
     */
    protected ?DataManager $dataManager;

    /**
     * Все эелементы списка.
     * 
     * @var array
     */
    protected array $items;

    /**
     * Иерархия из эелементов списка.
     * 
     * @var array
     */
    protected array $hierarchy;

    /**
     * Корневые элементы списка.
     * 
     * @var array
     */
    protected array $root;

    /**
     * Дочернии элементы списка, где ключ - идентификатор родитель-о элемента.
     * 
     * @var null|array
     */
    protected ?array $parents = null;

    /**
     * Конструктор класса.
     * 
     * @param null|DataManager $dataManager
     * 
     * @return void
     */
    public function __construct(?DataManager $dataManager = null)
    {
        $this->dataManager = $dataManager;
    }

    /**
     * Устанавливает менеджер данных.
     * 
     * @param DataManager $dataManager
     * 
     * @return $this
     */
    public function setDataManager(DataManager $dataManager): static
    {
        $this->dataManager = $dataManager;
        return $this;
    }

    /**
     * Возвращает менеджер данных.
     * 
     * @return DataManager|null
     */
    public function getDataManager(): ?DataManager
    {
        return $this->dataManager;
    }

    /**
     * Обновляет элемент списка.
     * 
     * @param array $item Название полей с их значениями.
     * @param mixed $id Идентификатор элемента списка.
     * 
     * @return bool|int Если была ошибка - false, иначе количество обновленных элементов.
     */
    public function updateItem(array $item, int $id): false|int
    {
        return $this->updateRecord(
            $item,
            [$this->dataManager->primaryKey => $id],
            $this->dataManager->tableName
        );
    }

    /**
     * Удаляет элемент из списка.
     * 
     * @param mixed $id Идентификатор элемента списка.
     * @param bool $descendants Если true, удаляет вместе с потомками.
     * 
     * @return array
     */
    public function deleteItem(int $id, bool $descendants = false): array
    {
        if ($descendants) {
            $id = $this->getItemsById([$id]);
        }
        $result = $this->deleteRecord(
            [$this->dataManager->primaryKey => $id],
            $this->dataManager->tableName
        );
        return [$result, $id];
    }

    /**
     * Удаляет элементы из списка.
     * 
     * @param mixed $ids Идентификаторы элементов списка.
     * @param bool $descendants Если true, удаляет вместе с потомками.
     * 
     * @return array
     */
    public function deleteItems(mixed $id, bool $descendants = false): array
    {
        if ($descendants) {
            $id = $this->getItemsById($id);
        }
        $result = $this->deleteRecord(
            [$this->dataManager->primaryKey => $id],
            $this->dataManager->tableName
        );
        return [$result, $id];
    }

    /**
     * Добавляет элемент в список.
     * 
     * @param array $item Название полей с их значениями.
     * 
     * @return int Идентификатор добавленного элемента.
     */
    public function addItem(array $item): int
    {
        return $this->insertRecord($item, $this->dataManager->tableName);
    }

    /**
     * Добавляет элемент списка к родителю.
     * 
     * @param array $item Название полей с их значениями.
     * @param mixed $parentId Идентификатор элемента родителя.
     * 
     * @return int Идентификатор добавленного элемента.
     */
    public function addItemToParent(array $item, int $parentId): int
    {
        $item[$this->dataManager->parentKey] = $parentId;
        return $this->insertRecord($item, $this->dataManager->tableName);
    }

    /**
     * Отвязывает все элементы списка от родителя.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * 
     * @return void
     */
    public function unbindItems(int $parentId): void
    {
        $command = $this->getDb()->createCommand();
        $command->update(
            $this->dataManager->tableName,
            [$this->dataManager->parentKey => null],
            [$this->dataManager->parentKey => $parentId]
        );
        $command->execute();
    }

    /**
     * Привязывает указанные элементы списка к родителю.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * @param mixed $id Идентификатор элементов списка.
     * 
     * @return void
     */
    public function bindItems(int $parentId, mixed $id): void
    {
        if (is_string($id)) {
            $id = explode(',', $id);
        }
        $command = $this->getDb()->createCommand();
        $command->update(
            $this->dataManager->tableName,
            [$this->dataManager->parentKey  => $parentId],
            [$this->dataManager->primaryKey => $id]
        );
        $command->execute();
    }

    /**
     * Возвращает дочерние элементы родителя.
     * 
     * @param int $parentId Идентификатор элемента родителя.
     * @param string|int[] $allChilds Если true, все дочерние элементы на всех уровнях.
     * 
     * @return array|bool Если false запрос не удался.
     */
    public function getChildItems(int $parentId, bool $allChilds = false)
    {
        if ($allChilds) {
            return $this->getItemsById($parentId, true);
        } else {
            /** @var \Ge\Db\Adapter\Adapter $db */
            $db = $this->getDb();
    
            $select = $db->select($this->dataManager->tableName);
            $select->columns(['*']);
            $select->where([$this->dataManager->parentKey => $parentId]);
    
            $command = $db->createCommand($select);
            return $command->queryAll();
        }
    }

    /**
     * Возвращает все элементы списка.
     * 
     * @return array Ассоциативный массив, где ключ - идентификатор элемента списка.
     */
    public function getItems(): array
    {
        if (!isset($this->items)) {
            /** @var \Ge\Db\Adapter\Adapter $db */
            $db = $this->getDb();
    
            $select = $db->select($this->dataManager->tableName);
            $select->columns(['*']);
            $command = $db->createCommand($select);
            $this->items = $command->queryAll('id');
        }
        return $this->items;
    }

    /**
     * Создаёт иерархию из указанного списка элементов.
     * 
     * @param array $items Список элементов.
     * @param array $childs Ассоциативный массив дочерних элементов списка, 
     *     где ключ - идентификатор родитель-о элемента.
     * 
     * @return void
     */
    protected function collectItems(array &$items, array $childs): void
    {
        foreach ($items as $index => $item) {
            $id = $item['id'];
            if (isset($childs[$id])) {
               $items[$index]['items'] = &$childs[$id];
               $this->collectItems($items[$index]['items'], $childs);
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
    public function collectItemsId(array &$result, array $id, array $childs): void
    {
        foreach ($id as $itemId) {
            $result[] = $itemId;
            if (isset($childs[$itemId])) {
                $keys = array_keys($childs[$itemId]);
                $this->collectItemsId($result, $keys, $childs);
            }
        }
    }

    /**
     * Возвращает идентификаторы и информацию элементов (включая дочернии) списка.
     * 
     * @param mixed $id Идентификаторы элементов для которых необходимо найти дочерние элементы.
     * @param bool $info Если true, возвращает информацию по указанным элементам.
     * 
     * @return array
     */
    public function getItemsById(mixed $id = [], bool $info = false): array
    {
        if (empty($id)) return [];

        $id = (array) $id;
        $result = [];
        $this->collectItemsId($result, $id, $this->getParents());
        // убираем дублирующие id
        $result = array_unique($result, SORT_NUMERIC);
        if ($info) {
            $itemsInfo = [];
            foreach ($result as $id) {
                $itemsInfo[] = $this->items[$id];
            }
            return $itemsInfo;
        }
        return $result;
    }

    /**
     * Подготавливает элементы списка для дальнейшей обработки.
     * 
     * @return void
     */
    protected function prepareItems(): void
    {
        $items = $this->getItems();
        $childItems = [];
        $this->root = [];
        foreach($items as $id => $item) {
            $parentId = (int) ($item['parent_id'] ?? 0);
            $id       = (int) $id;
            if ($parentId) {
                // если ссылочная целостность не нарушена
                if (isset($items[$parentId])) {
                    if (!isset($childItems[$parentId])) {
                        $childItems[$parentId] = [];
                    }
                    $childItems[$parentId][$id] = [
                        'id'    => $id,
                        'count' => (int) $item['count']
                    ];
                }
            }
            if (!$parentId)
                $this->root[$id] = [
                    'id'    => $id,
                    'count' => (int) $item['count']
                ];
        }
        $this->parents = $childItems;
    }

    /**
     * Возвращает идентификаторы корневых (у которых нет родителя) элементов списка.
     * 
     * @return array
     */
    public function getRoot(): array
    {
        if (!isset($this->root)) {
            $this->prepareItems();
        }
        return $this->root;
    }

    /**
     * Возвращает всех родителей с дочерними элементами.
     * 
     * @return array
     */
    public function getParents(): array
    {
        if ($this->parents === null) {
            $this->prepareItems();
        }
        return $this->parents;
    }

    /**
     * Возвращает иерархию всех элементов списка.
     * 
     * @return array
     */
    public function getHierarchical(): array
    {
        if (!isset($this->hierarchy)) {
            $this->hierarchy = $this->getRoot();
            $this->collectItems($this->hierarchy, $this->parents);
        }
        return $this->hierarchy;
    }

    /**
     * Обновляет количество дочерних элементов для каждого родителя.
     * 
     * @return bool Если false, запрос не удался.
     */
    public function update(): bool
    {
        $command = $this->getDb()->createCommand();
        $command->update(
            $this->dataManager->tableName,
            [$this->dataManager->countKey => 0]
        );
        $command->execute();
        $command->bindValues([
            '@table'     => $this->dataManager->tableName,
            '@parentKey' => $this->dataManager->parentKey,
            '@countKey'  => $this->dataManager->countKey
        ]);
        $command->setSql(
            'UPDATE `:@table` `rows`, (SELECT COUNT(*) `total`, `:@parentKey` FROM `:@table` WHERE parent_id IS NOT NULL GROUP BY `:@parentKey`) `list` '
          . 'SET `rows`.`:@countKey`=`list`.`total` WHERE `rows`.`id`=`list`.`:@parentKey`'
        );
        $command->execute();
        return $command->getResult() === true;
    }
}
