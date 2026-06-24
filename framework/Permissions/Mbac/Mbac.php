<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Mbac;

use Ge;
use Ge\Session\Container;

/**
 * Класс управления доступом ролей пользователей на основе разрешений модулей.
 * 
 * Module Base Access Control (MBAC).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Mbac
 * @since 2.0
 */
class Mbac
{
    /**
     * @var string Пространство имён хранилища дданых.
     */
    public const NAMESPACE_STORAGE = 'Ge_Mbac';

    /**
     * Хранилище данных.
     *
     * @var Container
     */
    protected Container $storage;

    /**
     * Последнии проверяемые разрешения.
     * 
     * @see Mbac::isGranted()
     *
     * @var array
     */
    protected array $grantedPermission = [];

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
     * Возвращает проверенные разрешения через разделитель ",".
     *
     * @return string
     */
    public function permission(): string
    {
        return implode(', ', $this->grantedPermission);
    }

    /**
     * Проверяет, имеет ли текущая роль (или роли) пользователя разрешения.
     *
     * @param string $permission Имя разрешения.
     * @param bool $extension Если значение `false`, проверяет разрешение модуля. 
     *     Иначе, расширение модуля (по умолчанию `false`).
     * 
     * @return bool
     */
    public function isGranted(string $permission, bool $extension = false): bool
    {
        $this->grantedPermission[] = $permission;
        if ($this->storage) {
            if ($extension)
                return isset($this->storage->extPermissions[$permission]);
            else
                return isset($this->storage->modPermissions[$permission]);
        }
        return false;
    }

    /**
     * Проверяет, имеет ли хранилище данные.
     * 
     * @return bool
     */
    public function hasStorage(): bool
    {
        return $this->storage ? $this->storage->empty() : false;
    }

    /**
     * Возвращает хранилище данных.
     * 
     * @return Container
     */
    public function getStorage(): Container
    {
        return $this->storage;
    }

    /**
     * Возвращает разрешения из хранилища данных.
     * 
     * @return array
     */
    public function getPermissions(bool $extension = false): array
    {
        if ($this->storage) {
            if ($extension)
                return $this->storage->extPermissions;
            else
                return $this->storage->modPermissions;
        }
        return [];
    }

    /**
     * Возвращает доступные идентификаторы модулей.
     * 
     * @param bool $toArray Если значение `false`, то результатом будут идентификаторы 
     *     через разделитель ",". Иначе, массив идентификаторов со значением `true` 
     *     (по умолчанию `false`).
     * @param null|string $permission Имя разрешения, которые имеют модули (по умолчанию `null`).
     * 
     * @return string|array
     */
    public function getModules(bool $toArray = false, ?string $permission = null): string|array
    {
        $moduleIds = '';
        if ($permission !== null) {
            $moduleIds = $this->getPermissionGroups($permission);
        } else {
            if ($this->storage !== null) {
                $moduleIds = $this->storage->modules;
            }
        }
        if ($toArray) {
            return $moduleIds ? array_fill_keys(explode(',', $moduleIds), true) : [];
        } else
            return $moduleIds;
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
        if ($this->storage) {
            $permAny  = $this->storage->modPermissionGroups['any'] ?? '';
            $permView = $this->storage->modPermissionGroups['view'] ?? '';
            if ($permAny)
                $moduleIds = $permView ? $permAny . ',' . $permView : $permAny;
            else
                $moduleIds = $permView ?: '';

            if ($moduleIds) {
                return $toArray ? array_fill_keys(explode(',', $moduleIds), true) : $moduleIds;
            }
        }
        return $toArray ? [] : '';
    }

    /**
     * Возвращает доступные идентификаторы расширений модулей.
     * 
     * @param bool $toArray Если значение `false`, то результатом будут идентификаторы 
     *     через разделитель ",". Иначе, массив идентификаторов со значением `true` 
     *     (по умолчанию `false`).
     * 
     * @return string|array
     */
    public function getExtensions(bool $toArray = false): string|array
    {
        if ($this->storage) {
            $ids = $this->storage->extensions;
            if ($ids) {
                return $toArray ? array_fill_keys(explode(',', $ids), true) : $ids;
            }
        }
        return $toArray ? [] : '';
    }

    /**
     * Возвращает доступные ролям пользователя идентификаторы модулей или расширений
     * через разделитель ",".
     * 
     * @param string $permission Имя разрешения, которому принадлежат идентификаторы.
     *     Например: 'any', 'read', 'write' и т.д.
     * @param bool $extension Если значение `false`, возвращает идентификаторы модулей. 
     *     Иначе, идентификаторы расширений модулей (по умолчанию `false`).
     * 
     * @return string Возвращает значение '', если идентификаторы отсутствуют.
     */
    public function getPermissionGroups(string $permission, bool $extension = false): string
    {
        if ($this->storage) {
            if ($extension)
                return $this->storage->extPermissionGroups[$permission] ?? '';
            else
                return $this->storage->modPermissionGroups[$permission] ?? '';
        }
        return '';
    }

    /**
     * Добавляет данные в хранилище.
     * 
     * @param mixed $data Данные.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function toStorage(array $data): static
    {
        if ($this->storage === null) {
            throw new Exception\InvalidArgumentException('Storage MBAC is null');
        }

        // роли
        $this->storage->roles = ['user' => [], 'parents' => []];
        if (isset($data['roles'])) {
            $this->storage->roles = [
                'user'    => $data['roles']['user'],
                'parents' => $data['roles']['parents']
            ];
        }

        // разрешения
        $this->storage->modules             = $data['modules'] ?? [];
        $this->storage->modPermissions      = $data['modPermissions'] ?? [];
        $this->storage->modPermissionGroups = $data['modPermissionGroups'] ?? [];
        $this->storage->extensions          = $data['extensions'] ?? '';
        $this->storage->extPermissions      = $data['extPermissions'] ?? [];
        $this->storage->extPermissionGroups = $data['extPermissionGroups'] ?? [];
        return $this;
    }

    /**
     * Создаёт хранилище разрешений.
     * 
     * @param array $userRoles Роли пользователей.
     * 
     * @return $this
     */
    public function createStorage(array $userRoles): static
    {
        /** @var \Ge\Permissions\Mbac\Model\RolePermission $rolePermission */
        $rolePermission = new Model\RolePermission();
        /** @var \Ge\Permissions\Mbac\Model\RoleHierarchy $roleHierarchy */
        $roleHierarchy = new Model\RoleHierarchy();
        /** @var \Ge\Permissions\Mbac\Model\ExtensionPermission $extPermission */
        $extPermission = new Model\ExtensionPermission();

        /**
         * Собирает предков указанных ролей.
         */
        $parentRoles = [];
        foreach ($userRoles as $role) {
            $parents = $roleHierarchy->getParents($role['id'], true);
            if ($parents)
                $parentRoles = $parentRoles + $parents;
        }
        // идентификаторы ролей пользователя
        $rolesId = array_keys($userRoles + $parentRoles);

        /**
         * Собирает разрешения для модулей доступных указанным ролям.
         */
        /** @var array $permissions Разрешения для указанных ролей пользователей */
        $permissions = $rolePermission->getRolePermissions($rolesId);
        $collect = [];
        foreach ($permissions as $roleId => $modules) {
            foreach($modules as $moduleId => $permission) {
                if (!isset($collect[$moduleId]))
                    $collect[$moduleId] = $permission;
                else {
                    $collect[$moduleId] = array_merge($collect[$moduleId], $permission);
                }
            }
        }

        /**
         * Формирует группы разрешений для каждого модуля доступного роли.
         * Результат: 
         * 1) `$modPermissionGroups => ['permission1' => 'module_id1,module_id2,...', ...]`;
         * 2) `$permissions => ['{module_id}.{permission}' => true, ...]`.
         */
        $mods = Ge::$app->modules->getRegistry()->getMap();
        $permissions = [];
        $modPermissionGroups = [];
        foreach ($collect as $moduleId => $permission) {
            if (isset($mods[$moduleId])) {
                $prefix = $mods[$moduleId]['id'] . '.';
                foreach ($permission as $name => $true) {
                    $permissions[$prefix . $name] = $true;
                    if (!isset($modPermissionGroups[$name])) {
                        $modPermissionGroups[$name] = [];
                    }
                    $modPermissionGroups[$name][] = $moduleId;
                }
            }
        }
        foreach ($modPermissionGroups as $permission => $modules) {
            $modPermissionGroups[$permission] = implode(',', $modules);
        }

        $extensions = $extPermission->getRolePermissions($rolesId);

        $this->toStorage([
            'modules'             => $rolePermission->getRolesModules($rolesId, true), // модули доступные роли
            'roles'               => ['user' => $userRoles, 'parents' => $parentRoles], // роли пользователя и роли предков
            'modPermissions'      => $permissions, // права доступа к модулям
            'modPermissionGroups' => $modPermissionGroups, // группы прав разрешений доступа к модулям
            'extensions'          => $extensions['ids'],
            'extPermissions'      => $extensions['permissions']
        ]);
        return $this;
    }
}
