<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Crypt\Password;

/**
 * Интерфейс создания хеш пароля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Crypt\Password
 * @since 2.0
 */
interface PasswordInterface
{
    /**
     * Создаёт хеш пароля для заданного значения.
     *
     * @param string $password Пароль.
     * 
     * @return false|string Хеш пароля.
     */
    public function create(string $password): false|string;

    /**
     * Проверяет хеш пароля с заданным значением.
     *
     * @param string $password Пароль.
     * @param string $hash Проверяемый хеш.
     * 
     * @return bool
     */
    public function verify(string $password, string $hash): bool;
}
