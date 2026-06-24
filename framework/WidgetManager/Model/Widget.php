<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\WidgetManager\Model;

use Ge;
use Closure;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\ActiveRecord;

/**
 * Widget класс шаблона активной записи, предназначен для хранения данных объекта виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager\Model
 * @since 2.0
 */
class Widget extends ActiveRecord
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
        return '{{widget}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'           => 'id', // идентификатор в базе данных
            'widgetId'     => 'widget_id', // идентификатор (пример: 'rg.wd.foobar')
            'widgetUse'    => 'widget_use', // назначение (BACKEND, FRONTEND)
            'category'     => 'category', // категория
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // пространство имени (пример: '\Ge\Widget\FooBar')
            'path'         => 'path', // путь к модулю (пример: '/Ge/Widget/FooBar')
            'enabled'      => 'enabled', // доступен
            'hasSettings'  => 'has_settings', // имеет (контроллер) настройки
            'version'      => 'version', // версия виджета
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
     * ".widgets" (".widgets.so").
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
            'id'           => 'widget_id', // идентификатор (пример: 'rg.wd.foobar')
            'use'          => 'widget_use', // назначение (BACKEND, FRONTEND)
            'category'     => 'category', // категория
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // пространство имени (пример: '\Ge\Widget\FooBar')
            'path'         => 'path', // путь к модулю (пример: '/Ge/Widget/FooBar')
            'enabled'      => 'enabled', // доступен
            'hasSettings'  => 'has_settings', // имеет (контроллер) настройки
            'version'      => 'version', // версия виджета
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
            ->delete('{{widget_locale}}', ['widget_id' => $this->id])
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
