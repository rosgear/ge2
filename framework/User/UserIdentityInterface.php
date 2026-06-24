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
 * UserIdentityInterface - это интерфейс, который должен быть реализован классом, 
 * предоставляющий информацию о идентификации пользователя.
 * 
 * Этот интерфейс может быть реализован с помощью класса модели пользователя на основе 
 * Active Record {@see \Ge\Db\ActiveRecord} или классом идентификации пользователя 
 * {@see \Ge\User\UserIndentity}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
interface UserIdentityInterface
{
    /**
     * Найти пользователя по условию или заданному идентификатору.
     * 
     * @param string|int|array $id Идентификатор пользователя или условия для поиска 
     *     личности пользователя.
     * 
     * @return UserIdentityInterface|null Объект идентификации, соответствующий данному 
     *     идентификатору или условию поиска. Если значение `null`, то такой объект не 
     *     может быть найден.
     */
    public function findIdentity($id);

    /**
     * Возвращает идентификатор, который может идентифицировать пользователя.
     * 
     * @return int|null Идентификатор, который идентифицирует пользователя. 
     *     Если значение `null`, невозможно идентифицировать пользователя.
     */
    public function getId(): ?int;
}
