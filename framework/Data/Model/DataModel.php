<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data\Model;

use Closure;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Db\Adapter\Adapter;

/**
 * Модель данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
class DataModel extends BaseModel
{
    use DataModelTrait;

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Where|Closure|string|array $where, ?string $tableName = null): false|int
    {
        return parent::deleteRecord($where, $tableName ?: $this->dataManager->tableName);
    }

    /**
     * Удаляет записи по значению первичного ключа таблицы.
     * 
     * @param mixed $value Значение.
     * @param null|string $primaryKey Первичный ключ.
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе, 
     *     количество удалённых записей.
     */
    public function deleteByPk(mixed $value, ?string $primaryKey = null, ?string $tableName = null): false|int
    {
        $primaryKey = $primaryKey ?: $this->dataManager->primaryKey;
        return $this->deleteRecord(
            [$primaryKey => $value], 
            $tableName ?: $this->dataManager->tableName
        );
    }

    /**
     * Удаляет записи с условием.
     * 
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе, 
     *     количество удалённых записей
     */
    public function deleteAll(?string $tableName = null): false|int
    {
        return $this->deleteRecord([], $tableName);
    }

    /**
     * Удаляет записи в зависимых таблицах.
     * 
     * @param array $dependencies Зависимые записи.
     * @param array $condition Условие удаление записей в зависимых таблицах.
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     *
     * @return void
     */
    public function deleteDependencies(array $dependencies, array $condition = [], ?string $tableName = null): void
    {
        if (empty($dependencies)) return;

        if ($tableName === null)
            $tableName = $this->dataManager->tableName;

        $db = $this->getDb();

        $masterTableName = $db->rawExpression($tableName);
        // на каждую таблицу зависимости создается конструктор
        foreach ($dependencies as $slaveTableName => $slaveCondition) {
            // Элемент массива - это название зависимой таблицы, где
            // $slaveCondition - название таблицы. Массив имеет вид: array("{{tableName}}"...).
            if (is_numeric($slaveTableName)) {
                /** @var \Ge\Db\Sql\QueryBuilder $builder */
                $builder = $db->getQueryBuilder();
                $delete = $builder->delete(array($db->rawExpression($slaveCondition)));

                $delete->where($condition);

                $sql = $builder->getSqlString();
            // в остальных случаях ассоц. элемент массива
            } else {
                $slaveTableName = $db->rawExpression($slaveTableName);
                // если условие - SQL запрос
                if (is_string($slaveCondition)) {
                    $sql = $slaveCondition;
                } else {
                    /** @var \Ge\Db\Sql\QueryBuilder $builder */
                    $builder = $db->getQueryBuilder();
                    $delete = $builder
                        ->delete(array($slaveTableName))
                        ->using(array($slaveTableName, $masterTableName));
    
                    // если условие - массив полей таблиц $slaveTableName и $masterTableName
                    if (is_array($slaveCondition)) {
                        foreach ($slaveCondition as $field1 => $field2) {
                            $delete->where->equalTo(
                                $slaveTableName . '.' . $field1,
                                $masterTableName . '.' . $field2,
                                \Ge\Db\Sql\ExpressionInterface::TYPE_IDENTIFIER,
                                \Ge\Db\Sql\ExpressionInterface::TYPE_IDENTIFIER
                            );
                        }
                    // если условие - SQL оператор
                    } else
                    if (is_object($slaveCondition)) {
                        $slaveCondition($delete->where, $slaveTableName, $masterTableName);
                    }
                    $delete->where($condition);
                    $sql = $builder->getSqlString();
                }
            }
            // удаляем зависимые записи по запросу
            $db->createCommand($sql)->execute();
        }
    }

    /**
     * Удаляет все записи в зависимых таблицах.
     * 
     * @param array $dependencies Зависимые записи.
     * @param bool $resetIncrement Сбрасывать автоинкримент {@see \Ge\Db\Adapter\Driver\AbstractCommand::resetIncrement()}.
     * 
     * @return void
     */
    public function dropDependencies(array $dependencies, bool $resetIncrement = true): void
    {
        if (empty($dependencies)) return;

        $command = $this->getDb()->createCommand();
        foreach ($dependencies as $tableName) {
            $command->delete($tableName)->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetIncrement(int|string $increment = 1, ?string $tableName = null): void
    {
        parent::resetIncrement($increment, $tableName ?: $this->dataManager->tableName);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRecord(
        array $columns, 
        Where|Closure|string|array|null $where = null, 
        ?string $tableName = null
    ): false|int
    {
        return parent::updateRecord($columns, $where, $tableName ?: $this->dataManager->tableName);
    }

    /**
     * Обновляет записи по указанным значениям первичного ключа.
     * 
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * @param mixed $value Значениям первичного ключа.
     * @param null|string $primaryKey Первичный ключ.
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе количество 
     *     обновленных записей.
     */
    public function updateByPk(
        array $columns, 
        mixed $value, 
        ?string $primaryKey = null, 
        ?string $tableName = null
    ): false|int
    {
        $primaryKey = $primaryKey ?: $this->dataManager->primaryKey;
        return $this->updateRecord(
            $columns, 
            [$primaryKey => $value], 
            $tableName ?: $this->dataManager->tableName
        );
    }

    /**
     * Обновляет все записи.
     * 
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе количество 
     *     обновленных записей.
     */
    public function updateAll(array $columns, ?string $tableName = null): false|int
    {
        return $this->updateRecord($columns, [], $tableName ?: $this->dataManager->tableName);
    }

    /**
     * {@inheritdoc}
     */
    public function insertRecord(array $columns, ?string $tableName = null): int|string
    {
        return parent::insertRecord($columns, $tableName ?: $this->dataManager->tableName);
    }

    /**
     * Возвращает записи выбранные по условию.
     * 
     * @see \Ge\Db\Adapter\Driver\AbstractCommand::fetchAll()
     * 
     * @param Where|Closure|string|array $condition Условие выполнения запроса.
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * @param array $columns Столбцы таблицы выборки. Если столбцы выборки не указаны, 
     *     то по умолчанию будет значение `['*']` (означающее "все столбцы"). 
     * 
     * @return array<int|string, array|object>
     */
    public function selectByCondition(
        Where|Closure|string|array $condition, 
        ?string $tableName = null, 
        array $columns = ['*']
    )
    {
        /** @var Adapter $db */
        $db = $this->getDb();
        /** @var Select $select */
        $select = new Select($tableName ?: $this->dataManager->tableName);
        $select->columns($columns);
        if ($condition) {
            $select->where($condition);
        }
        return $db->createCommand($select)->queryAll();
    }

    /**
     * Возвращает одну запись выбранную по условию.
     * 
     * @see \Ge\Db\Adapter\Driver\AbstractCommand::fetchAll()
     * 
     * @param Where|Closure|string|array $condition Условие выполнения запроса.
     * @param null|string $tableName Имя таблицы. Если значение `null`, тогда имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * @param array $columns Столбцы таблицы выборки. Если столбцы выборки не указаны, 
     *     то по умолчанию будет значение `['*']` (означающее "все столбцы"). 
     * 
     * @return array<int|string, array|object>
     */
    public function selectOneByCondition(
        Where|Closure|string|array $condition, 
        ?string $tableName = null, 
        array $columns = ['*']
    )
    {
        $rows = $this->selectByCondition($condition, $tableName, $columns);
        return sizeof($rows) ? $rows[0] : [];
    }

    /**
     * Возвращает запись по указанному значению первичного ключа.
     * 
     * @see DataModel::selectByCondition()
     * 
     * @param mixed $value Значение первичного ключа.
     * @param null|string $primaryKey Первичный ключ.
     * @param null|string $tableName Имя таблицы. Если значение `null`, то имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return array<int|string, array|object>
     */
    public function selectByPk(mixed $value, ?string $primaryKey = null, ?string $tableName = null)
    {
        if ($primaryKey === null) {
            $primaryKey = $this->dataManager->primaryKey;
        }
        return $this->selectByCondition([$primaryKey => $value], $tableName);
    }

    /**
     * Возвращает все записи таблицы.
     * 
     * @see DataModel::selectByCondition()
     * 
     * @param null|string $tableName Имя таблицы. Если значение `null`, то имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return array<int|string, array|object>
     */
    public function selectAll(?string $tableName = null): array
    {
        return $this->selectByCondition([], $tableName);
    }

    /**
     * Возвращает количество записей в таблице.
     * 
     * @param null|string $tableName Имя таблицы. Если значение `null`, то имя таблицы должно 
     *     быть указано в менеджере данных (по умолчанию `null`).
     * 
     * @return int
     */
    public function selectCount(?string $tableName = null): int
    {
        /** @var Adapter $db  */
        $db = $this->getDb();
        /** @var Select $select  */
        $select = $db->select()
            ->from($tableName ?: $this->dataManager->tableName)
            ->columns(
                ['total' => new \Ge\Db\Sql\Expression('COUNT(*)')]
            );
        $row = $db
            ->createCommand($select)
                ->queryOne();
        return isset($row['total']) ? (int) $row['total'] : 0;
    }
}
