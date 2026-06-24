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
 * Роль пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac
 * @since 2.0
 */
class Role extends AbstractRole
{
    /**
     * Конструктор класса.
     * 
     * @param string $name Имя роли.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
