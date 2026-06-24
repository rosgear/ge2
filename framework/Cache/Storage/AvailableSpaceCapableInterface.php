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
 * Интерфейс доступного пространства для хранения данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface AvailableSpaceCapableInterface
{
    /**
     * Возвращает доступное пространство в байтах.
     * 
     * @return int|float Доступное пространство в байтах.
     */
    public function getAvailableSpace();
}
