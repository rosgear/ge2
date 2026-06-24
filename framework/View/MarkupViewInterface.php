<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

/**
 * Интерфейс разметки компонентов представления в шаблоне.
 * 
 * Используется для изменения параметров компонентов с помощью визуального редактора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
interface MarkupViewInterface
{
    /**
     * Возвращает настройки разметки компонента в шаблоне.
     * 
     * Разметка применяется для управления компонентом с помощью визуального редактора.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     'uniqueId'   => 'rg.wd.menu:top', // уникальный идентификатор компонента в шаблоне
     *     'dataId'     => 1, // уникальный идентификатор компонента в базе данных
     *     'registryId' => 'rg.wd.menu', // идентификатор в менеджере (модулей, расширений, виджетов)
     *     'title'      => 'Top menu' // заголовок (описание) компонента,
     *     'menu'       => [
     *         [
     *             'title' => 'Edit menu',
     *             'route' => 'menu/edit/{id}',
     *         ],
     *         [
     *             'title' => 'Add menu item',
     *             'route' => 'menu/item/add',
     *         ],
     *     ]
     * ]
     * ```
     * 
     * @param array $options Настройки разметки, которые изменят значения по умолчанию.
     * 
     * @return array
     */
    public function getMarkupOptions(array $options = []): array;
}
