<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

use Ge;
use Ge\Db\Sql\Where;
use Ge\Db\ActiveRecord;
use Ge\Permissions\Mbac\Mbac;
use Ge\Session\Storage\StorageInterface;

/**
 * Базовый класс предоставляющий информацию о идентификации пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
class UserIdentity extends ActiveRecord implements UserIdentityInterface, UserDataInterface
{
    /**
     * Имя параметра в хранилище для хранения аутентификации пользователя.
     *
     * @var string
     */
    protected string $storageMember = '';

    /**
     * Хранилище аутентификации пользователя.
     *
     * @var StorageInterface|null
     */
    protected StorageInterface|null $_storage;

    /**
     * Роли пользователя.
     *
     * @var mixed
     */
    protected $_roles;

    /**
     * Модульный контроль доступа.
     * 
     * Доступ на основе разрешений модуля с использованием ролей пользователя
     * Module Based Access Control (MBAC).
     * 
     * @see UserIdentity::getBac()
     * 
     * @var Mbac
     */
    protected Mbac $_bac;

    /**
     * Сторона авторизации пользователя.
     * 
     * Является unsafe атрибутом для {@see \Ge\Db\ActiveRecord} и может 
     * иметь значения: BACKEND_SIDE_INDEX, FRONTEND_SIDE_INDEX.
     * 
     * @var int
     * 
     * public $loginSide;
     */

    /**
     * Конструктор класса.
     *
     * @param null|StorageInterface $storage Хранилище аутентификации пользователя.
     * 
     * @return void
     */
    public function __construct(?StorageInterface $storage = null)
    {
        $this->configure([]);

        $this->_storage = $storage;
        // если `$storage = null`, то конструктор был вызван через {@see \Ge\User\User::createIdentity()}, 
        // иначе загружаем все атрибуты из хранилища аутентификации пользователя
        if ($storage !== null) {
            $this->read();
        }
    }

    /**
     * Возвращает модульный контроль доступом.
     * 
     * Здесь можно реализовать любую модель управления доступом, но для 
     * приложениям выбираем доступ на основе разрешений модуля с использованием 
     * ролей Module Based Access Control (MBAC) {@see \Ge\Permissions\Mbac\Mbac}.
     *
     * @return Mbac
     */
    public function getBac(): Mbac
    {
        if (!isset($this->_bac)) {
            $this->_bac = new Mbac();
        }
        return $this->_bac;
    }

    /**
     * Установка хранилища аутентификации пользователя.
     *
     * @param StorageInterface $storage Хранилище аутентификации пользователя.
     * 
     * @return void
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * Возвращает хранилище аутентификации пользователя.
     *
     * @return StorageInterface Хранилище аутентификации пользователя.
     */
    public function getStorage(): StorageInterface
    {
        if ($this->_storage === null) {
            $this->_storage = Ge::$services->getAs('user')->getStorage();
        }
        return $this->_storage;
    }

    /**
     * Проверяет, установлено ли хранилище аутентификации пользователя.
     *
     * @return bool Если значение `false`, хранилище аутентификации пользователя 
     *     отсутсвует.
     */
    public function hasStorage(): bool
    {
        return $this->_storage !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        /** @var object $storage */
        $storage = $this->getStorage();
        $attributes = $this->storageMember ? $storage->get($this->storageMember) : $storage->read();
        return $attributes ? $this->load($attributes) : $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad(): void
    {
        // т.к. свойство - сторона авторизации пользователя является unsafe атрибутом
        // и значение должно быть int
        $this->loginSide = $this->getAttribute('loginSide');
        if ($this->loginSide === null) {
            $this->loginSide = $this->getUnsafeAttribute('loginSide');
        }
    }

    /**
     * Возвращает имена атрибутов, значения которых необходимо отправить в хранилище.
     *
     * @return array
     */
    public function writableAttributes(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * Возвращает атрибуты в виде пар "ключ - значение", которые необходимо отправить в 
     * хранилище.
     * 
     * @see UserIdentity::writableAttributes()
     * 
     * @return array
     */
    public function getWritableAttributes(): array
    {
        $result = [];
        $names = $this->writableAttributes();
        foreach ($names as $name) {
            if (array_key_exists($name, $this->attributes)) {
                $result[$name] = $this->attributes[$name];
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     * 
     * @see UserIdentity::getWritableAttributes()
     */
    public function write()
    {
        /** @var array $attributes */
        $attributes = $this->getWritableAttributes();

        if ($attributes) {
            /** @var object $storage */
            $storage = $this->getStorage();
            if ($this->storageMember) {
                $storage->set($this->storageMember, $attributes);
            } else {
                $storage->write($attributes);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find()
    {
        $id = $this->id;
        return $id ? $this->selectOne([$this->primaryKey() => $id]) : null;
    }

    /**
     * {@inheritdoc}
     * 
     * @return UserIdentity|null Объект идентификации, соответствующий данному 
     *     идентификатору или условию поиска. Если значение `null`, то такой объект не 
     *     может быть найден.
     */
    public function findIdentity($id)
    {
        return $this->selectOne(is_numeric($id) ? [$this->primaryKey() => $id] : $id);
    }

    /**
     * Возвращает информацию о пользователе.
     * 
     * @param Where|\Closure|string|array $where Условие выполнения запроса.
     * 
     * @return array|null Информация о пользователе в виде пар "ключ - значение", иначе `null`.
     */
    public function findOne($where): ?array
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
    public function getId(): ?int
    {
        return (int) ($this->{$this->primaryKey()} ?: 0);
    }

    /**
     * Определяет принадлежность пользователя к стороне backend.
     * 
     * @see UserIdentity::getSide()
     * 
     * @return bool Если значение `true`, пользователь принадлежит backend (`BACKEND_SIDE_INDEX`). 
     */
    public function isBackend(): bool
    {
        return $this->getSide() === BACKEND_SIDE_INDEX;
    }

    /**
     * Определяет принадлежность пользователя к стороне frontend.
     * 
     * @see UserIdentity::getSide()
     * 
     * @return bool Если значение `true`, пользователь принадлежит frontend (`FRONTEND_SIDE_INDEX`). 
     */
    public function isFrontend(): bool
    {
        return $this->getSide() === FRONTEND_SIDE_INDEX;
    }

    /**
     * Определяет принадлежность пользователя к одной из сторон backend или frontend.
     * 
     * Принадлежность - авторизация пользователя на стороне backend (`BACKEND_SIDE_INDEX`)
     * или frontend (`FRONTEND_SIDE_INDEX`).
     * 
     * @return int Принадлежность пользователя к backend или frontend . Если принадлежность 
     *     неопределенна, тогда вернёт пустую строку.
     */
    public function getSide(): int
    {
        return $this->loginSide;
    }

    /**
     * Устанавливает принадлежность пользователя к одной из сторон backend или frontend.
     * 
     * @param  int $side Принадлежность пользователя к стороне backend (`BACKEND_SIDE_INDEX`)
     * или frontend (`FRONTEND_SIDE_INDEX`).
     * 
     * @return void
     */
    public function setSide(int $side)
    {
        $this->loginSide = $side;
    }

    /**
     * Возвращает имя пользователя.
     * 
     * @return string Если имя пользователя отсутствует или пользователь не прошел 
     *     идентификация, тогда вернёт пустую строку.
     */
    public function getUsername(): string
    {
        return '';
    }

    /**
     * Проверяет доступность пользователя.
     * 
     * @return bool Если значение `false`, пользователь не доступен.
     */
    public function isEnabled(): bool
    {
        return false;
    }

    /**
     * Определяет, предоставлять ли доступ, проверяя роль и дочерние роли для разрешения.
     * 
     * @param string $permission Имя разрешения.
     * @param bool $extension Если значение `false`, проверяет разрешение модуля. 
     *     Иначе, расширение модуля (по умолчанию `false`).
     * 
     * @return bool Если значение `true`, разрешение доступно, иначе нет.
     */
    public function isGranted(string $permission, bool $extension = false): bool
    {
        return false;
    }

    /**
     * Возвращает все доступные для роли пользователя разрешения модулей или расширений.
     * 
     * @param bool $extension Если значение `false`, возвращает разрешение модуля. 
     *     Иначе, расширение модуля (по умолчанию `false`).
     * 
     * @return array
     */
    public function getPermissions(bool $extension = false): array
    {
        return [];
    }

    /**
     * Возвращает пользователю доступные идентификаторы модулей.
     * 
     * @param bool $toArray Если значение `false`, результатом будут идентификаторы через 
     *     разделитель ",". Иначе, массив идентификаторов (по умолчанию `true`).
     * @param string|null $permission Имя разрешения, которые имеют модули (по умолчанию `null`).
     * 
     * @return string|array<string, mixed>
     */
    public function getModules(bool $toArray = false, string $permission = null): string|array
    {
        return $toArray ? [] : '';
    }

    /**
     * Возвращает идентификаторы модулей доступных (с разрешениями: "any", "view") для просмотра.
     * 
     * @param bool $toArray Если значение `false`, то результатом будут идентификаторы 
     *     через разделитель ",". Иначе, массив идентификаторов со значением `true` 
     *     (по умолчанию `false`).
     * 
     * @return string|array
     */
    public function getViewableModules(bool $toArray = false): string|array
    {
        return $toArray ? [] : '';
    }

    /**
     * Возвращает доступные идентификаторы расширений модулей.
     * 
     * @param bool $toArray Если значение `false`, то результатом будут идентификаторы 
     *     через разделитель ",". Иначе, массив идентификаторов со значением `true` 
     *     (по умолчанию `false`).
     * 
     * @return string|array<string, mixed>
     */
    public function getExtensions(bool $toArray = false): string|array
    {
        return $toArray ? [] : '';
    }

    /**
     * Возвращает роли пользователей.
     * 
     * @return mixed
     */
    public function getRoles()
    {
        return $this->_roles;
    }
}
