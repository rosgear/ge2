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
 * Интерфейс удаления истекших по времени элементов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface ClearExpiredInterface
{
    /**
     * Удаление истекших по времени элементов.
     *
     * @return bool true - если удаление было успешно.
     */
    public function clearExpired();
}
