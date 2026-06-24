<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

use Ge\Session\Storage\SessionStorage;

/**
 * Хранилище аутентификации пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
class UserSessionStorage extends SessionStorage
{
    /**
     * {@inheritdoc}
     */
    protected string $name = 'Ge_User';
}
