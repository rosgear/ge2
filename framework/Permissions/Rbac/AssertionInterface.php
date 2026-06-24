<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Rbac;

/**
 * Интерфейс утверждения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac
 * @since 2.0
 */
interface AssertionInterface
{
    /**
     * Метод утверждения - должен возвращать логическое значение.
     *
     * @param Rbac $rbac
     * 
     * @return bool
     */
    public function assert(Rbac $rbac): bool;
}
