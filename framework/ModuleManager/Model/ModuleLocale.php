<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ModuleManager\Model;

use Ge;
use Closure;
use Ge\Exception;
use Ge\Helper\Json;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\ActiveRecord;

/**
 * ModuleLocale класс шаблона активной записи, предназначен для хранения локализации 
 * объекта модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager\Model
 * @since 2.0
 */
class ModuleLocale extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'module_id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{module_locale}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'moduleId'    => 'module_id', // идентификатор модуля
            'languageId'  => 'language_id', // идентификатор языка
            'name'        => 'name', // название
            'description' => 'description', // описание
            'permissions' => 'permissions' // описание разрешений в формате JSON
        ];
    }

    /**
     * Возвращает запись по указанному идентификатору модуля и коду языка.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $moduleId Идентификатор модуля.
     * @param null|int $languageId Код языка. Если `null`, текуший код языка (по умолчанию `null`).
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $moduleId, int $languageId = null): ?ActiveRecord
    {
        return $this->selectOne([
            'module_id'   => $moduleId,
            'language_id' => $languageId === null ? Ge::$app->language->code : $languageId
        ]);
    }

    /**
     * {@inheritdoc}
     * 
     * Условие обновления записи если используется составной первичный ключ.
     */
    protected function updateProcessCondition(array &$where): void
    {
        $where['module_id'] = $this->moduleId;
        $where['language_id'] = $this->languageId;
    }

    /**
     * {@inheritdoc}
     * 
     * Условие удаления записи если используется составной первичный ключ.
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where['module_id']   = $this->moduleId;
        $where['language_id'] = $this->languageId;
    }

    /**
     * Удаляет все записи из таблицы.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteAll()
    {
        return $this->deleteRecord([]);
    }

    /**
     * Удаляет записи из таблицы по указаному модулю.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteFromModule(int $moduleId)
    {
        return $this->deleteRecord(['module_id' => $moduleId]);
    }

    /**
     * Возвращает все записи для текущего языка (если не указано условие запроса).
     * 
     * {@inheritdoc}
     */
    public function fetchAll(
        string $fetchKey = null, 
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null, 
        string|array|null $order = null
    ): array
    {
        if ($where === null) {
            $where = ['language_id' => Ge::$app->language->code];
        }
        /** @var Select $select */
        $select = $this->select($columns, $where);
        if ($order === null)
            $order = ['name' => 'ASC'];
        $select->order($order);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryAll($fetchKey);
    }

    /**
     * Возвращает имена модулей.
     * 
     * @param string $attribute Название атрибута ('name', 'description') возвращаемого 
     *     для каждого идентификатора. Если значение `null`, возвратит все атрибуты 
     *     (по умолчанию `null`).
     * @param int $languageCode Идентификатор языка. Если значение `null`, то идентификатор 
     *     текущего языка (по умолчанию `null`).
     * 
     * @return array<int, array{name:string, description:string}>
     */
    public function fetchNames(string $attribute = null, int $languageCode = null): array
    {
        $db = $this->getDb();
        $sql = 'SELECT IF(`l`.`name` IS NULL, `m`.`name`, `l`.`name`) `name`, `l`.`permissions`, '
             . 'IF(`l`.`description` IS NULL, `m`.`description`, `l`.`description`) `description`, `m`.`id` '
             . 'FROM `{{module}}` `m` LEFT JOIN `{{module_locale}}` `l` '
             . 'ON `m`.`id`=`l`.`module_id` AND `l`.`language_id`=:language';
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */ 
        $command = $db->createCommand($sql);
        $command->bindValues([
            ':language' => $languageCode ?: Ge::$app->language->code
        ]);
        if ($attribute)
            return $command->queryToColumn('id', $attribute);
        else
            return $command->queryAll('id');
    }

    /**
     * Возвращает атрибуты локализации модуля.
     * 
     * @param int $moduleId Идентификатор модуля.
     * 
     * @return array{name:string, description:string, permissions:string}|null
     */
    public function fetchLocale(int $moduleId)
    {
        /** @var Select $select */
        $select = $this->select(
            ['name', 'description', 'permissions'],
            [
                'language_id' => Ge::$app->language->code,
                'module_id'   => $moduleId
            ]
        );
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
    }

    /**
     * Возвращает набор всех строк (ассоциативные массивы) текущей таблицы.
     * 
     * Ключом каждой строки является значение первичного ключа {@see ActiveRecord::tableName()} 
     * текущей таблицы.
     * 
     * @param bool $caching Указывает на принудительное кэширование. Если служба кэширования 
     *     отключена, кэширование не будет выполнено (по умолчанию `true`).
     * 
     * @return array
     */
    public function getAll(bool $caching = true): ?array
    {
        if ($caching)
            return $this->cache(
                function () { return $this->fetchAll($this->primaryKey(), $this->maskedAttributes()); },
                null,
                true
            );
        else
            return $this->fetchAll($this->primaryKey(), $this->maskedAttributes());
    }


    /**
     * Декодирует разрешения модуля из JSON в ассоциативный массив.
     * 
     * @return array
     */
    public function permissionsToArray(): array
    {
        $permissions = [];
        if ($this->permissions) {
            if (is_string($this->permissions)) {
                $permissions = Json::decode($this->permissions, true);
                if ($error = Json::error()) {
                    throw new Exception\JsonFormatException(
                        Ge::t('app', 'Could not JSON decode: {0}', [$error]) . 'Module permission invalids.'
                    );
                }
            } else
            if (is_array($this->permipermissionsssion)) {
                $permissions = $this->permissions;
            }
        }
        return $permissions;
    }
}
