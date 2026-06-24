<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Mbac\Model;

use Ge\Db\ActiveRecord;

/**
 * Шаблон данных, реализующий доступ ролей пользователей к модулям с помощью 
 * разрешенией.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Mbac\Model
 * @since 2.0
 */
class RolePermission extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{module_permissions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'moduleId'    => 'module_id', // идентификатор модуля
            'roleId'      => 'role_id', // идентификатор роли
            'permissions' => 'permissions', // разрешения через разделитель ','
        ];
    }

    /**
     * Возвращает только одну запись по указанному условию запроса.
     * 
     * @param int $moduleId Идентификатор модуля.
     * @param int $roleId Идентификатор роли.
     * 
     * @return ActiveRecord|null Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $moduleId, int $roleId): ?static
    {
        return $this->selectOne([
            'module_id' => $moduleId,
            'role_id'   => $roleId
        ]);
    }

    /**
     * Возвращает массив или строку идентификаторов модулей, доступных указанным 
     * ролям.
     * 
     * @param array $rolesId Идентификаторы ролей.
     * @param bool $toString Если значение `true`, то результат будет строка, иначе, 
     *     массив (по умолчанию `false`).
     * 
     * @return string|array
     */
    public function getRolesModules(array $rolesId, bool $toString = false): string|array
    {
        if (empty($rolesId)) return $toString ? '' : [];

        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->select(['module_id']);
        $select
            ->where(['role_id' => $rolesId])
            ->group('module_id');

        $rows = $this->getDb()
            ->createCommand($select)
                ->queryColumn();
        return $toString ? ($rows ? implode(',', $rows) : $rows) : $rows;
    }

    /**
     * Возвращает разрешения для указанных ролей пользователей.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     'role_id1' => [
     *         'module_id1' => ['permission_name1' => true, 'permission_name2' => true,],
     *         'module_id2' => ['permission_name1' => true, 'permission_name2' => true,],
     *         // ...
     *     ],
     *     'role_id2' => [
     *         // ...
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @param array $rolesId Идентификаторы ролей.
     * 
     * @return array
     */
    public function getRolePermissions(array $rolesId): array
    {
        if (empty($rolesId)) return [];

        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->select(['*']);
        $select->where(['role_id' => $rolesId]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getDb()
            ->createCommand($select)
                ->query();

        $rows = [];
        if ($command->result) {
            while ($row = $command->fetch()) {
                $permissions = $row['permissions'];
                if ($permissions) {
                    $permissions = explode(',', $permissions);
                    if ($permissions) {
                        $permissions = array_fill_keys($permissions, true);
                    }
                } else 
                $permissions = [];
                $rows[ $row['role_id'] ][ $row['module_id'] ] = $permissions;
            }
        }
        return $rows;
    }
}
