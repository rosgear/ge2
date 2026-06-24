<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

/**
 * UserDataInterface - это интерфейс, который должен быть реализован классом, 
 * предоставляющий данные аутентификации пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
interface UserDataInterface
{
    /**
     * Поиска данных аутентификации пользователя.
     * 
     * Выполнение запроса к базе данных используя Active Record {@see \Ge\Db\ActiveRecord}.
     * 
     * @return array|\Ge\Db\ActiveRecord|null
     */
    public function find();

    /**
     * Читает данные аутентификации пользователя из хранилища.
     * 
     * Метод используется только для хранилища аутентификации пользователя.
     * 
     * @return null|array Если null, данные аутентификации пользователя отсутствуют в 
     *     хранилище.
     */
    public function read();

    /**
     * Записывает данные аутентификации пользователя в хранилище.
     * 
     * Метод используется только для хранилища аутентификации пользователя.
     * 
     * @return void
     */
    public function write();
}
