<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\PluginManager\Model;

use Ge;
use Closure;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\ActiveRecord;

/**
 * Plugin класс шаблона активной записи, предназначен для хранения данных объекта плагина.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\PluginManager\Model
 * @since 2.0
 */
class Plugin extends ActiveRecord
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
        return '{{plugin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'           => 'id', // идентификатор в базе данных
            'pluginId'     => 'plugin_id', // идентификатор, например 'rg.plg.foobar'
            'ownerId'      => 'owner_id', // идентификатор владельца плагина, например 'rg.be.foobar'
            'category'     => 'category', // категория плагина
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // пространство имени (пример: '\Rg\Plugin\FooBar')
            'path'         => 'path', // путь к плагину (пример: '/Rg/Plugin/FooBar')
            'enabled'      => 'enabled', // доступен
            'hasSettings'  => 'has_settings', // имеет (контроллер) настройки
            'version'      => 'version', // версия плагина
            // системные
            'updatedDate' => '_updated_date',
            'updatedUser' => '_updated_user',
            'createdDate' => '_created_date',
            'createdUser' => '_created_user',
            'lock'        => '_lock'
        ];
    }

    /**
     * Возвращает маску атрибутов для конфигурации установленных виджетов в файлах 
     * ".plugins" (".plugins.so").
     * 
     * Пример получения конфигурации установленных виджетов с помощью запроса:
     * ```php
     * (new Module())->fetchAll('rowId', $this->maskedConfiguration());
     * ```
     * 
     * @return array
     */
    public function maskedConfiguration(): array
    {
        return [
            'rowId'        => 'id', // идентификатор в базе данных
            'id'           => 'plugin_id', // идентификатор, например 'rg.plg.foobar'
            'ownerId'      => 'owner_id', // идентификатор владельца плагина, например 'rg.be.foobar'
            'category'     => 'category', // категория плагина
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // пространство имени (пример: '\Rg\Plugin\FooBar')
            'path'         => 'path', // путь к плагину (пример: '/Rg/Plugin/FooBar')
            'enabled'      => 'enabled', // доступен
            'hasSettings'  => 'has_settings', // имеет (контроллер) настройки
            'version'      => 'version', // версия плагина
            'lock'         => '_lock' // виджет является системным
        ];
    }

    /**
     * Возвращает запись по указанному значению первичного ключа.
     * 
     * @see ActiveRecord::selectByPk()
     * 
     * @param mixed $identifier Идентификатор записи.
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function get(mixed $identifier): ?static
    {
        return $this->selectByPk($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(
        ?string $fetchKey = null, 
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null, 
        string|array|null $order = null
    ): array
    {
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
     * {@inheritdoc}
     */
    protected function deleteDependencies(mixed $condition): void
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command*/
        $command = $this->getDb()
            ->createCommand();
        // удаление локализаций модуля
        $command
            ->delete('{{plugin_locale}}', ['plugin_id' => $this->id])
            ->execute();
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
}
