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
 * Интерфейс удаления элементов соответствующие заданному префиксу.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface ClearByPrefixInterface
{
    /**
     * Удаление элементов, соответствующие заданному префиксу.
     *
     * @param string $prefix Имя префикса.
     * 
     * @return bool true - если удаление было успешно.
     */
    public function clearByPrefix($prefix);
}
