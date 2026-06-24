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
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\ActiveRecord;

/**
 * Extension класс шаблона активной записи, предназначен для хранения данных объекта 
 * расширения модуля.
 * 
 * Активная запись хранит только те значения расширения, которые в дальнейшем при его 
 * обновлении не будут изменяться (исключения: 'name', 'description').
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class Extension extends ActiveRecord
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
        return '{{extension}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'           => 'id', // идентификатор в базе данных
            'extensionId'  => 'extension_id', // идентификатор (пример: 1, 2, 3...)
            'moduleId'     => 'module_id', // идентификатор (пример: 1, 2, 3...)
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // название пространство имён (например '\Frontend\Application')
            'path'         => 'path', // локальный путь или каталог (например '/Frontend/Application')
            'route'        => 'route', // маршрут (нпример 'app')
            'desk'         => 'desk',
            'menu'         => 'menu',
            'enabled'      => 'enabled', // доступен
            'hasInfo'      => 'has_info', // имеет контроллер информацию
            'hasSettings'  => 'has_settings', // имеет контроллер настройки
            'permissions'  => 'permissions', // разрешения (права доступа)
            'version'      => 'version', // версия расширения
            // системные
            'updatedDate' => '_updated_date',
            'updatedUser' => '_updated_user',
            'createdDate' => '_created_date',
            'createdUser' => '_created_user',
            'lock'        => '_lock'
        ];
    }

    /**
     * Возвращает маску атрибутов для конфигурации установленных модулей в файлах 
     * ".modules" (".modules.so").
     * 
     * Пример получения конфигурации установленных модулей с помощью запроса:
     * ```php
     * (new Module())->fetchAll('rowId', $this->maskedConfiguration());
     * ```
     * 
     * @return array
     */
    public function maskedConfiguration(): array
    {
        return [
            'id'           => 'module_id', // идентификатор (пример: 'extension.foobar')
            'rowId'        => 'id', // идентификатор в базе данных
            'use'          => 'module_use', // назначение (BACKEND, FRONTEND)
            'name'         => 'name', // имя по умолчанию (при отсутствии текущей локали)
            'description'  => 'description', // описание по умолчанию (при отсутствии текущей локали)
            'namespace'    => 'namespace', // пространство имени (пример: '\Frontend\Application')
            'path'         => 'path', // путь к модулю (пример: '/Frontend/Application')
            'route'        => 'route', // маршрут (пример: 'app')
            'routeAppend'  => 'route_append', // добавочный маршрут (пример: 'app/form')
            'enabled'      => 'enabled', // доступен
            'visible'      => 'visible', // видимый (только для панели управления)
            'append'       => 'append', // имеет добавочный маршрут
            'expandable'   => 'expandable', // имеет расширения (плагины, виджеты, т.д.)
            'hasInfo'      => 'has_info', // имеет (контроллер) информацию
            'hasSettings'  => 'has_settings', // имеет (контроллер) настройки
            'permissions'  => 'permissions', // разрешения (права доступа)
            'lock'         => '_lock' // модуль является системным
        ];
    }

    /**
     * Возвращает запись по указанному значению первичного ключа.
     * 
     * @see ActiveRecrod::selectByPk()
     * 
     * @param mixed $identifier Идентификатор записи.
     * 
     * @return null|Extension Активная запись при успешном запросе, иначе `null`.
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
        if ($order === null) {
            $order = ['name' => 'ASC'];
        }
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
        /*$command
            ->delete('{{extension_locale}}', ['extension_id' => $this->id])
            ->execute();*/
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
