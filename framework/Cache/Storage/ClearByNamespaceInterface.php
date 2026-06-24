<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage;

/**
 * Интерфейс удаления элементов заданного пространства имён.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface ClearByNamespaceInterface
{
    /**
     * Удаление элементов заданного пространства имён.
     *
     * @param string $namespace Пространство имён.
     * 
     * @return bool true - если удаление было успешно.
     */
    public function clearByNamespace($namespace);
}
