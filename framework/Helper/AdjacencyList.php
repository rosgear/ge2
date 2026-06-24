<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

/**
 * Вспомогательный класс AdjacencyList списка смежностей (adjacency list) для получения 
 * дерева из элементов списка.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class AdjacencyList
{
    /**
     * Название параметра (ключа) элемента списка, указывающего на идентификатор родительского 
     * элемента.
     * 
     * @var string
     */
    protected static $parentKey = 'parent_id';

    /**
     * Название параметра (ключа) элемента списка, указывающего на идентификатор узла дерева.
     * 
     * @var string
     */
    protected static $idKey = 'id';
    
    /**
     * Название свойства (кюча) узла дерева, определяющие дочернии узлы дерева.
     * 
     * @var string
     */
    protected static $itemsKey = 'items';

    /**
     * Устанавливает параметры (ключи), которые указаны в элементах списка.
     * 
     * @param array<string, string> $keys Ключи в виде пар "ключ - значение".
     * 
     * @return void
     */
    public static function setKeys(array $keys): void
    {
        foreach ($keys as $key => $name) {
            $value = $key . 'Key';
            static::$$value = $name;
        }
    }

    /**
     * Устанавливает параметр (ключ), который указан в элементах списка.
     * 
     * @param string $key Название параметра: 'parent', 'id', 'items'.
     * @param string $name Название атрибута элемента списка.
     * 
     * @return void
     */
    public static function setKey(string $key, string $name): void
    {
        $value = $key . 'Key';
        static::$$value = $name;
    }

    /**
     * Фильтрует указанные параметры.
     * 
     * @param array $params Параметры.
     * @param array $filter Фильтр в виде пар "ключ - значение".
     * 
     * @return array
     */
    protected static function filterParams(array $params, array $filter): array
    {
        $result = [];
        foreach ($filter as $param => $newParam) {
            if (isset($params[$param])) {
                $result[$newParam] = $params[$param];
            }
        }
        return $result;
    }

    /**
     * Подготавливает и создаёт из элементов списка, корневые и дочернии узлы дерева.
     * 
     * @param array<string, mixed> $rows Элементы списка.
     * @param array<string, string> $paramsFilter Фильтр параметров элемента списка
     *     (по умолчанию `[]`).
     * 
     * @return array<int, array> Массив корневых и дочерних узлов дерева. 
     */
    protected static function prepareNodes(array $rows, array $paramsFilter = []): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[$row[static::$idKey]] = $row;
        }

        $childItems = [];
        $root = [];
        foreach ($items as $id => $item) {
            $parentId = $item[static::$parentKey];
            if ($parentId) {
                // если ссылочная целостность не нарушена
                if (isset($items[$parentId])) {
                    if (!isset($childItems[$parentId])) {
                        $childItems[$parentId] = [];
                    }
                    $childItems[$parentId][$id] = $paramsFilter ? 
                        static::filterParams($item, $paramsFilter) : 
                        $item;
                }
            }
            if (!$parentId)
                $root[$id] = $paramsFilter ? 
                    static::filterParams($item, $paramsFilter) : 
                    $item;
        }
        return [
            $root,
            $childItems
        ];
    }

    /**
     * Создаёт дерево из указанных элементов.
     * 
     * @param array $nodes Корневые элементы дерева.
     * @param array $childs Дочернии элементы дерева, где ключ - идентификатор родитель-о 
     *     элемента.
     * 
     * @return void
     */
    protected static function collectNodes(array &$nodes, array $childs): void
    {
        foreach ($nodes as $index => $node) {
            $id = $node[static::$idKey];
            if (isset($childs[$id])) {
                $nodes[$index][static::$itemsKey] = &$childs[$id];
                static::collectNodes($nodes[$index][static::$itemsKey], $childs);
            }
        }
    }

    /**
     * Убирает из узлов дерева ключи в виде идентификаторов.
     * 
     * Например, если `$nodes`:
     * ```php
     * [
     *     10 => [
     *         'id'    => 10,
     *         'text'  => 'Root',
     *         'items' => [
     *             '11' => ['id' => 11, 'text' => 'Node'],
     *             // ...
     *         ]
     *     ],
     *     // ...
     * ]
     * ```
     * то результат будет:
     * [
     *     [
     *         'id'    => 10,
     *         'text'  => 'Root',
     *         'items' => [
     *             ['id' => 11, 'text' => 'Node'],
     *             // ...
     *         ]
     *     ],
     *     // ...
     * ]
     * 
     * @param array $nodes Узлы дерева с ключами.
     * 
     * @return array
     */
    protected static function dropKeys(array $nodes): array
    {
        $new = [];
        foreach ($nodes as $id => $node) {
            if (isset($node[static::$itemsKey])) {
                $node[static::$itemsKey] = static::dropKeys($node[static::$itemsKey]);
            }
            $new[] = $node;
        }
        return $new;
    }

    /**
     * Возвращает дерево элементов.
     * 
     * @param array<int, array> $rows Массив записей в виде пар "ключ - значение".
     * @param bool $dropKeys Убрать из узлов дерева ключи в виде идентификаторов 
     *     (по умолчанию `true`).
     * @param array<string, string> $paramsFilter Фильтр параметров узлов дерева
     *     (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function getTree(array $rows, bool $dropKeys = true, array $paramsFilter = []): array
    {
        list($root, $parents) = static::prepareNodes($rows, $paramsFilter);
        static::collectNodes($root, $parents);
        if ($dropKeys) {
            $root = static::dropKeys($root);
        }
        return $root;
    }

    /**
     * Убирает из узлов дерева ключи в виде идентификаторов.
     * 
     * Например, если `$nodes`:
     * ```php
     * [
     *     10 => [
     *         'id'    => 10,
     *         'text'  => 'Root',
     *         'items' => [
     *             '11' => ['id' => 11, 'text' => 'Node'],
     *             // ...
     *         ]
     *     ],
     *     // ...
     * ]
     * ```
     * то результат будет:
     * [
     *     [
     *         'id'    => 10,
     *         'text'  => 'Root',
     *         'items' => [
     *             ['id' => 11, 'text' => 'Node'],
     *             // ...
     *         ]
     *     ],
     *     // ...
     * ]
     * 
     * @param array $nodes Узлы дерева с ключами.
     * 
     * @return void
     */
    protected static function treeNodesToRows(array &$rows, array $nodes, int $level = 1, $callback = null): void
    {
        foreach ($nodes as $id => $node) {
            $hasChildren = isset($node[static::$itemsKey]);
            if ($callback)
                $rows[] = $callback($node, $level);
            else
                $rows[] = $node;


            if ($hasChildren) {
                static::treeNodesToRows($rows, $node[static::$itemsKey], $level + 1, $callback);
            }

        }
    }

    /**
     * Возвращает массив упорядоченных записей дерева.
     * 
     * @param array<int, array> $rows Массив записей в виде пар "ключ - значение".
     * @param null|callable $callback 
     * 
     * @return array
     */
    public static function getTreeRows(array $rows, $callback = null): array
    {
        list($root, $parents) = static::prepareNodes($rows);
        static::collectNodes($root, $parents);
        $rows = [];
        static::treeNodesToRows($rows, $root, 1, $callback);
        return $rows;
    }
}
