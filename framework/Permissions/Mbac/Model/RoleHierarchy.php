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
 * Шаблон данных, реализующий иерархию ролей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Mbac\Model
 * @since 2.0
 */
class RoleHierarchy extends ActiveRecord
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
        return '{{role_hierarchy}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'roleId'   => 'role_id', // идентификатор
            'parentId' => 'parent_id', // идентификатор предка
        ];
    }

    /**
     * Возвращает только одну запись по указанному условию запроса.
     * 
     * @param int $roleId Идентификатор роли.
     * @param int $parentId Идентификатор предка роли.
     * 
     * @return ActiveRecord|null Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $roleId, int $parentId): ?static
    {
        return $this->selectOne([
            'role_id'   => $roleId,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Возвращает иерархию предков.
     * 
     * @param int $roleId Идентификатор роли.
     * @param array $hierarchy Иерархия ролей.
     * @param array $parents Результат иерархии предков.
     * 
     * @return void
     */
    public function deepHierarchy(int $roleId, array $hierarchy, array &$parents): void
    {
        $parents[$roleId] = true;
        foreach ($hierarchy as $row) {
            if ($row['role_id'] == $roleId)
                $this->deepHierarchy($row['parent_id'], $hierarchy, $parents);
        }
    }

    /**
     * @var null|array Все записи иерархии.
     */
    private $_hierarchy;

    /**
     * Возвращает все записи иерархии.
     *
     * @return array
     */
    public function getHierarchy(): array
    {
        if ($this->_hierarchy === null) {
            /** @var \Ge\Db\Sql\Select $select */
            $select = $this->select(['role_id', 'parent_id']);
            $this->_hierarchy = $this->getDb()
                ->createCommand($select)
                    ->queryAll();
        }
        return $this->_hierarchy;
    }

    /**
     * Возвращает все роли пользователей.
     *
     * @return array
     */
    public function getRoles(): array
    {
        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        /** @var \Ge\Db\Sql\Select $select */
        $select = $db
            ->select('{{role}}')
                ->columns(['*']);
        return $db
                ->createCommand($select)
                    ->queryAll('id');
    }

    /**
     * Возвращает предка (или предков) указанной роли.
     * 
     * @param int $roleId Идентификатор роли.
     * @param bool $deep Если значение `true`, то возвратит всех предков указанной роли.
     * 
     * @return array
     */
    public function getParents(int $roleId, bool $deep = false): array
    {
        $result = [];
        if ($deep) {
            $hierarchy = $this->getHierarchy();
            // поиск всех родителей
            $parents = array();
            foreach ($hierarchy as $row) {
                if ($row['role_id'] == $roleId)
                    // проверка на дубликат роли
                    if (!isset($parents[ $row['parent_id'] ]))
                        $this->deepHierarchy($row['parent_id'], $hierarchy, $parents);
                
            }
            // все роли, вид: `['id1' => [...], 'id2' => [...], ...]`
            $roles = $this->getRoles();
            foreach ($parents as $id => $true) {
                if (isset($roles[$id])) {
                    $result[$id] = $roles[$id];
                }
            }
        } else {
            /** @var \Ge\Db\Adapter\Adapter $db */
            $db = $this->getDb();
            $sql = 'SELECT `role`.`id`, `role`.`name` '
                 . 'FROM `{{role_hierarchy}}` `hierarchy` '
                 . 'JOIN `{{role}}` `role` ON `role`.`id`=`hierarchy`.`parent_id` AND `hierarchy`.`role_id`=' . $roleId;
            $result = $db
                ->createCommand($sql)
                    ->queryAll();
        }
        return $result;
    }
}
