<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

use Ge\Stdlib\Collection;

/**
 * Класс реализующий хранение данных пользователя в виде коллекции в хранилище аутентификации.
 * 
 * Коллекция находится в разделе {@see UserData::$storageName} хранилища аутентификации 
 * пользователя. Доступ к ней, можно получить используя методы: read и write интерфейса 
 * {@see UserDataInterface}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
class UserStorage extends Collection implements UserDataInterface
{
    /**
     * Имя раздела в хранилище аутентификации пользователя.
     *
     * @var string
     */
    protected string $storageMember = '';

    /**
     * Объект идентификации пользователя.
     *
     * @var UserIdentity
     */
    protected UserIdentity $_identity;

    /**
     * Конструктор класса.
     *
     * @param UserIdentity $identity Объект идентификации пользователя.
     * 
     * @return void
     */
    public function __construct(UserIdentity $identity)
    {
        $this->_identity = $identity;
        // если объект идентификации пользователя имеет хранилище, 
        // то все атрибуты загружаются из этого хранилища
        if ($identity->hasStorage()) {
            $this->read();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (empty($this->storageMember)) {
            return null;
        }

        $container = $this->_identity->getStorage()->get($this->storageMember);
        if ($container) {
            $this->container = $container;
        }
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        if ($this->storageMember && $this->container) {
            $this->_identity->getStorage()->set($this->storageMember, $this->container);
        }
    }
}
