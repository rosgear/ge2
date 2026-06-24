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
 * Интерфейс оптимизацит хранилища.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface OptimizableInterface
{
    /**
     * Оптимизация хранилища.
     *
     * @return bool true - если оптимизация успешна.
     */
    public function optimize();
}
