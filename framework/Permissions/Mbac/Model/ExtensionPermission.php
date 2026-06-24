<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Mbac\Model;

use Ge;
use Ge\Db\ActiveRecord;

/**
 * Шаблон данных, реализующий доступ ролей пользователей к расширениям модулей с 
 * помощью разрешенией.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Mbac\Model
 * @since 2.0
 */
class ExtensionPermission extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'extension_id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{extension_permissions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'extensionId' => 'extension_id', // идентификатор расширения модуля
            'roleId'      => 'role_id', // идентификатор роли
            'permissions' => 'permissions', // разрешения через разделитель ','
        ];
    }

    /**
     * Возвращает только одну запись по указанному условию запроса.
     * 
     * @param int $extensionId Идентификатор расширения модуля.
     * @param int $roleId Идентификатор роли.
     * 
     * @return ActiveRecord|null Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $extensionId, int $roleId): ?static
    {
        return $this->selectOne([
            'extension_id' => $extensionId,
            'role_id'      => $roleId
        ]);
    }

    /**
     * Возвращает массив или строку идентификаторов расширений модулей, доступных 
     * указанным ролям.
     * 
     * @param array $rolesId Идентификаторы ролей.
     * @param bool $toString Если значение `true`, то результат будет строка, иначе, 
     *     массив (по умолчанию `false`).
     * 
     * @return string|array
     */
    public function getRolesExtensions(array $rolesId, bool $toString = false): string|array
    {
        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->select(['extension_id']);
        $select
            ->where(['role_id' => $rolesId])
            ->group('extension_id');
        $rows = $this->getDb()
            ->createCommand($select)
                ->queryColumn();
        return $toString ? ($rows ? implode(',', $rows) : $rows) : $rows;
    }

    /**
     * Возвращает разрешения для указанной роли или ролей пользователей.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     // разрешения расширений
     *     'permissions' => [
     *         '{extension_id}.{permission}' => true,
     *         // ...
     *     ],
     *     // идентификаторы расширений
     *     'ids' => 'extension_id1,extension_id2,...'
     * ]
     * ```
     * 
     * @param string|int|array $roleId Идентификатор(ы) ролей.
     * 
     * @return array<string, mixed>
     */
    public function getRolePermissions(string|int|array $roleId): array
    {
        if (empty($roleId)) return ['permissions' => [], 'ids' => ''];

        /** @var array $extensions Конфигурации установленных расширений */
        $extensions = Ge::$app->extensions->getRegistry()->getMap();
        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->select(['*']);
        $select->where(['role_id' => $roleId]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getDb()
            ->createCommand($select)
                ->query();

        $rows = $ids = [];
        if ($command->result) {
            while ($row = $command->fetch()) {
                $extensionId = $row['extension_id'];
                $permissions = $row['permissions'];

                if (!isset($extensions[$extensionId])) continue;
                if ($permissions) {
                    $extension = $extensions[$extensionId];
                    $permissions = explode(',', $permissions);
                    foreach ($permissions as $permission) {
                        $rows[$extension['id'] . '.' . $permission] = true;
                    }
                }
                $ids[] = $extensionId;
            }
        }
        return [
            'permissions' => $rows,
            'ids'         => $ids ? implode(',', $ids) : ''
        ];
    }
}
