<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

use Ge\Db\Sql\Where;
use Ge\Db\ActiveRecord;

/**
 * Класс реализующий поиск и хранение данных в хранилище аутентификации пользователя.
 * 
 * Данные пользователя реализуемые классом, находятся в разделе {@see UserData::$storageName} 
 * хранилища аутентификации пользователя. Доступ к данным, можно получить используя 
 * методы: read и write интерфейса {@see UserDataInterface}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
class UserData extends ActiveRecord implements UserDataInterface
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
        $this->configure([]);

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
     * Поиск данных пользователя.
     * 
     * @param Where|\Closure|string|array $where Условие выполнения запроса.
     * 
     * @return null|ActiveRecord Информация о идентификации пользователя 
     *     при успешном запросе, иначе `null`.
     */
    public function findOne($where)
    {
        $select = $this->select(['*'], $where);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (empty($this->storageMember)) {
            return null;
        }

        $attributes = $this->_identity->getStorage()->get($this->storageMember);
        if ($attributes) {
            $this->load($attributes);
        }
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        if ($this->storageMember && $this->attributes) {
            $this->_identity->getStorage()->set($this->storageMember, $this->attributes);
        }
    }
}
