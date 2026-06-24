<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Rbac;

use RecursiveIteratorIterator;
use Ge\Session\Container;

/**
 * Управление доступом на основе ролей пользователей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac
 * @since 2.0
 */
class Rbac extends AbstractIterator
{
    /**
     * @var string Пространство имени в сессии по умолчанию.
     */
    public const NAMESPACE_STORAGE = 'Ge_Rbac';

    /**
     * flag: создавать или не создавать роли автоматически, если они не существуют.
     *
     * @var bool
     */
    protected bool $createMissingRoles = false;

    /**
     * Контейнер для сессии.
     *
     * @var Container
     */
    protected Container $storage;

    /**
     * Конструктор класса.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->storage = new Container(self::NAMESPACE_STORAGE);
    }

    /**
     * Установить флаг создания роли автоматически.
     * 
     * @param bool $createMissingRoles
     * 
     * @return $this
     */
    public function setCreateMissingRoles($createMissingRoles): static
    {
        $this->createMissingRoles = $createMissingRoles;
        return $this;
    }


    /**
     * Возвращает флаг создания роли автоматически.
     * 
     * @return bool
     */
    public function getCreateMissingRoles(): bool
    {
        return $this->createMissingRoles;
    }

    /**
     * Добавляет роль.
     * 
     * @param RoleInterface|string $child Дочерняя роль.
     * @param null|array<int, RoleInterface|string> $parents Родительские роли.
     * 
     * @return RoleInterface
     * 
     * @throws Exception\InvalidArgumentException Дочеряя роль должна быть строкой или интерфейсом.
     */
    public function addRole(RoleInterface|string $child, ?array $parents = null): RoleInterface
    {
        if (is_string($child)) {
            $child = new Role($child);
        }

        if (!$child instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Child must be a string or implement Ge\Permissions\Rbac\RoleInterface'
            );
        }

        if ($parents) {
            if (!is_array($parents)) {
                $parents = [$parents];
            }
            foreach ($parents as $parent) {
                if ($this->createMissingRoles && !$this->hasRole($parent)) {
                    $this->addRole($parent);
                }
                $this->getRole($parent)->addChild($child);
            }
        }

        $this->children[] = $child;
        return $child;
    }

    /**
     * Проверяет, добавлена ли роль.
     *
     * @param RoleInterface|string $role Роль или её имя.
     * 
     * @return bool
     */
    public function hasRole(RoleInterface|string $role): bool
    {
        return $this->getRole($role) !== null;
    }

    /**
     * Возвращает все роли.
     * 
     * @return array<int, array{name:string, parent:string}>
     */
    public function getRoles(): array
    {
        $roles = [];
        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            if ($leaf->getParent() != null)
                $parent = $leaf->getParent()->getName();
            else
                $parent = null;
            $roles[] = [
                'name'   => $leaf->getName(),
                'parent' => $parent
            ];
        }
        return $roles;
    }

    /**
     * Возвращает роль.
     *
     * @param RoleInterface|string $role Роль или её имя.
     * 
     * @return RoleInterface|null Возвращает значение `null`, если роль не найдена.
     * 
     * @throws Exception\InvalidArgumentException Роль должна быть строкой или интерфейсом.
     */
    public function getRole($role): ?RoleInterface
    {
        if (!is_string($role) && !$role instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Expected string or implement Ge\Permissions\Rbac\RoleInterface'
            );
        }

        $requiredRole = is_object($role) ? $role->getName() : $role;

        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            /** @var RoleInterface $leaf */
            if ($leaf->getName() == $requiredRole) {
                return $leaf;
            }
        }
        return null;
    }

    /**
     * Определяет, предоставляется ли доступ, проверяя роль и дочерние роли для разрешения
     *
     * @param RoleInterface|string $role Роль или её имя.
     * @param string $permission Разрешение.
     * @param AssertionInterface|callable|null $assert Утверждение.
     * 
     * @return bool
     * 
     * @throws Exception\InvalidArgumentException Неправильно указано утверждение.
     */
    public function isGranted(RoleInterface|string $role, string $permission, AssertionInterface|Callable|null $assert = null): bool
    {
        if ($assert) {
            if ($assert instanceof AssertionInterface) {
                return (bool) $assert->assert($this);
            }

            if (is_callable($assert)) {
                return (bool) $assert($this);
            }

            throw new Exception\InvalidArgumentException(
                'Assertions must be a Callable or an instance of Ge\Permissions\Rbac\AssertionInterface'
            );
        }

        $role = $this->getRole($role);
        if ($role == null) {
            return false;
        }
        return $role->hasPermission($permission);
    }

    /**
     * Имеет ли хранилищие данные о ролях
     * 
     * @return bool
     */
    public function hasStorage(): bool
    {
        return !$this->storage->empty();
    }

    /**
     * Возвращает хранилище.
     * 
     * @return Container
     */
    public function getStorage(): Container
    {
        return $this->storage;
    }

    /**
     * Добавление данных в хранилище.
     * 
     * @param array<string, array> $data Данные.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException Хранилище не установлено.
     */
    public function toStorage(array $data): static
    {
        if ($this->storage === null) {
            throw new Exception\InvalidArgumentException('Storage RBAC is null');
        }

        $this->storage->roles = isset($data['roles']) ? $data['roles'] : [];
        $this->storage->permissions = isset($data['permissions']) ? $data['permissions'] : [];
        $this->storage->granted = [];
        return $this;
    }
}
