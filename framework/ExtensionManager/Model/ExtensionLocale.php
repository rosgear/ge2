<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ExtensionManager\Model;

use Ge;
use Closure;
use Ge\Exception;
use Ge\Helper\Json;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\ActiveRecord;

/**
 * ExtensionLocale класс шаблона активной записи, предназначен для хранения локализации 
 * объекта расширения модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class ExtensionLocale extends ActiveRecord
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
        return '{{extension_locale}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'extensionId' => 'extension_id', // идентификатор расширения модуля
            'languageId'  => 'language_id', // идентификатор языка
            'name'        => 'name', // название
            'description' => 'description', // описание
            'permissions' => 'permissions' // описание разрешений в формате JSON
        ];
    }

    /**
     * Возвращает запись по указанному идентификатору расширения и коду языка.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $extensionId Идентификатор расширения.
     * @param null|int $languageId Код языка. Если `null`, текуший код языка (по умолчанию `null`).
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $extensionId, ?int $languageId = null): ?ActiveRecord
    {
        return $this->selectOne([
            'extension_id' => $extensionId,
            'language_id'  => $languageId === null ? Ge::$app->language->code : $languageId
        ]);
    }

    /**
     * {@inheritdoc}
     * 
     * Условие обновления записи если используется составной первичный ключ.
     */
    protected function updateProcessCondition(array &$where): void
    {
        $where['extension_id'] = $this->extensionId;
        $where['language_id']  = $this->languageId;
    }

    /**
     * {@inheritdoc}
     * 
     * Условие удаления записи если используется составной первичный ключ.
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where['extension_id'] = $this->extensionId;
        $where['language_id']  = $this->languageId;
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
     * Удаляет записи из таблицы по указаному расширению.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteFromExtension(int $extensionId)
    {
        return $this->deleteRecord(['extension_id' => $extensionId]);
    }

    /**
     * Возвращает все записи для текущего языка (если не указано условие запроса).
     * 
     * {@inheritdoc}
     */
    public function fetchAll(
        ?string $fetchKey = null, 
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
     * Возвращает все записи локализации.
     * 
     * @param null|string $attribute Название атрибута ('name', 'description') возвращаемого 
     *     для каждого идентификатора. Если значение `null`, возвратит все атрибуты 
     *     (по умолчанию `null`).
     * @param null|int $languageCode Идентификатор языка. Если значение `null`, то идентификатор 
     *     текущего языка (по умолчанию `null`).
     * 
     * @return array<int, array{name:string, description:string}>
     */
    public function fetchNames(?string $attribute = null, ?int $languageCode = null): array
    {
        /** @var Select $select */
        $select = $this->select(['*'], ['language_id' => $languageCode ?: Ge::$app->language->code]);
        $select->order(['name' => 'ASC']);
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getDb()->createCommand($select);
        if ($attribute)
            return $command->queryToColumn('extension_id', $attribute);
        else
            return $command->queryAll('extension_id');
    }

    /**
     * Возвращает атрибуты локализации расширения модуля.
     * 
     * @param integer $extensionId Идентификатор расширения модуля.
     * 
     * @return array{name:string, description:string, permissions:string}|null
     */
    public function fetchLocale(int $extensionId): ?array
    {
        /** @var Select $select */
        $select = $this->select(
            ['name', 'description', 'permissions'],
            [
                'language_id'  => Ge::$app->language->code,
                'extension_id' => $extensionId
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
