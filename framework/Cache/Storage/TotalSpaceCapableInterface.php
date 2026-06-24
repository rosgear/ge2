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
 * Интерфейс общего пространство в байтах.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface TotalSpaceCapableInterface
{
    /**
     * Получение общего пространство в байтах.
     *
     * @return int|float Пространство в байтах.
     */
    public function getTotalSpace();
}
