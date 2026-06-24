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
 * WidgetLocale класс шаблона активной записи, предназначен для хранения локализации 
 * объекта виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager\Model
 * @since 2.0
 */
class WidgetLocale extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'widget_id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{widget_locale}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'widgetId'    => 'widget_id', // идентификатор виджета
            'languageId'  => 'language_id', // идентификатор языка
            'name'        => 'name', // название
            'description' => 'description' // описание
        ];
    }

    /**
     * Возвращает запись по указанному идентификатору виджета и коду языка.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $widgetId Идентификатор виджета.
     * @param null|int $languageId Код языка. Если `null`, текуший код языка (по умолчанию `null`).
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $widgetId, ?int $languageId = null): ?ActiveRecord
    {
        return $this->selectOne([
            'widget_id'   => $widgetId,
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
        $where['widget_id']   = $this->widgetId;
        $where['language_id'] = $this->languageId;
    }

    /**
     * {@inheritdoc}
     * 
     * Условие удаления записи если используется составной первичный ключ.
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where['widget_id']   = $this->widgetId;
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
     * Удаляет записи из таблицы по указаному виджету.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteFromWidget(int $widgetId)
    {
        return $this->deleteRecord(['widget_id' => $widgetId]);
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
     * Возвращает имена виджетов.
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
        $db = $this->getDb();
        $sql = 'SELECT IF(`l`.`name` IS NULL, `m`.`name`, `l`.`name`) `name`,  '
             . 'IF(`l`.`description` IS NULL, `m`.`description`, `l`.`description`) `description`, `m`.`id` '
             . 'FROM `{{widget}}` `m` LEFT JOIN `{{widget_locale}}` `l` '
             . 'ON `m`.`id`=`l`.`widget_id` AND `l`.`language_id`=:language';
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
     * Возвращает атрибуты локализации виджета.
     * 
     * @param integer $widgetId Идентификатор виджета.
     * 
     * @return array{name:string, description:string}|null
     */
    public function fetchLocale(int $widgetId): ?array
    {
        /** @var Select $select */
        $select = $this->select(
            ['name', 'description'],
            [
                'language_id' => Ge::$app->language->code,
                'widget_id'   => $widgetId
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
}
