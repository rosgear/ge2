<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db;

use Ge;
use Closure;
use Ge\Db\Sql\Select;
use Ge\Db\Sql\Predicate\PredicateInterface;
use Ge\Stdlib\BaseObject;
use Ge\Db\Adapter\Adapter;
use Ge\Db\Adapter\Driver\AbstractCommand;
use Ge\Db\Adapter\Exception\CommandException;

/**
 * ActionRecord базовый наследуемый класс, применяемый для выполнения CRUD запросов над записями.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db
 * @since 2.0
 */
class ActionRecord extends BaseObject
{
    /**
     * Адаптер подключения к базе данных.
     * 
     * @see \Ge\Db\Adapter\Adapter
     * 
     * @var Adapter
     */
    public Adapter $db;

    /**
     * Команда выполнения SQL инструкций.
     * 
     * @var AbstractCommand
     */
    protected AbstractCommand $command;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!isset($this->db)) {
            $this->db = Ge::$services->getAs('db');
        }
    }

    /**
     * Возвращает адаптер подключения к базе данных.
     * 
     * @return Adapter
     */
    public function getDb(): Adapter
    {
        if (!isset($this->db)) {
            $this->db = Ge::$services->getAs('db');
        }
        return $this->db;
    }

    /**
     * Возвращает команду выполнения SQL инструкций.
     * 
     * @return AbstractCommand
     */
    public function getCommand(): AbstractCommand
    {
        if (!isset($this->command)) {
            $this->command = $this->db->createCommand();
        }
        return $this->command;
    }

    /**
     * Устанавливает режим выборки записей.
     * 
     * @see AbstractCommand::$fetchMode
     * 
     * @param int $mode Режим выборки записей.
     * 
     * @return $this
     */
    public function setFetchMode(int $mode): static
    {
        $this->getCommand()->setFetchMode($mode);
        return $this;
    }

    /**
     * Удаляет записи.
     * 
     * @see AbstractCommand::delete()
     * 
     * @param PredicateInterface|Closure|string|array<string, string> $where Условие 
     *     выполнения запроса.
     * @param string $tableName Название таблицы в базе данных.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество удалённых записей.
     * 
     * @throws CommandException Ошибка выполнения запроса.
     */
    public function deleteRecord(
        string $tableName, 
        PredicateInterface|Closure|string|array $where
    ): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->getCommand();
        $command
            ->delete($tableName, $where)
            ->execute();
        return $command->getResult() === true ? (int) $command->getAffectedRows() : false;
    }

    /**
     * Обновляет записи.
     * 
     * @see AbstractCommand::update()
     * 
     * @param array $columns Имена полей с их значениями в виде пар "ключ - значение".
     * @param PredicateInterface|Closure|string|array<string, string> $where Условие 
     *     выполнения запроса (по умолчанию `null`).
     * @param string $tableName Название таблицы в базе данных.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе количество обновленных записей.
     * 
     * @throws CommandException Ошибка выполнения запроса.
     */
    public function updateRecord(
        string $tableName, 
        array $columns, 
        PredicateInterface|Closure|string|array $where
    ): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->getCommand();
        $command
            ->update($tableName, $columns, $where)
            ->execute();
        return $command->getResult() === true ? (int) $command->getAffectedRows() : false;
    }

    /**
     * Добавляет запись.
     * 
     * @see AbstractCommand::insert()
     * 
     * @param array $columns Имена полей с их значениями в виде пар "ключ - значение".
     * @param string $tableName Название таблицы в базе данных.
     * 
     * @return mixed Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе последний идентификатор добавленной записи.
     */
    public function insertRecord(string $tableName, array $columns): mixed
    {
        /** @var AbstractCommand $command */
        $command = $this->getCommand();
        $command
            ->insert($tableName, $columns)
            ->execute();
        return $command->getResult() === true ? $this->db->getConnection()->getLastGeneratedValue() : false;
    }

    /**
     * Возвращает запись таблицы.
     * 
     * Результирующий набор определяется методом {@see ActionRecord::setFetchMode()}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @param string $tableName Название таблицы в базе данных.
     * @param PredicateInterface|Closure|string|array<string, string> $where Условие 
     *     выполнения запроса.
     * @param array $columns Столбцы выборки текущей таблицы. Если вы не укажете столбцы 
     *     для выборки, то по умолчанию будет значение `['*']` (все столбцы).
     * 
     * @return mixed
     */
    public function getRecord(
        string $tableName, 
        PredicateInterface|Closure|string|array $where, 
        array $columns = ['*']
    ): mixed
    {
        /** @var Select $select */
        $select = $this->db->select($tableName);
        $select->columns($columns);

        if ($where) {
            $select->where($where);
        }        
        return $this->getCommand()
            ->setSql($select)
                ->queryOne();
    }

    /**
     * Возвращает записи таблицы.
     * 
     * Результирующий набор определяется методом {@see ActionRecord::setFetchMode()}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @param string $tableName Название таблицы в базе данных.
     * @param array $columns Столбцы выборки текущей таблицы. Если вы не укажете столбцы 
     *     для выборки, то по умолчанию будет значение `['*']` (все столбцы).
     * @param PredicateInterface|Closure|string|array<string, string>|null $where Условие 
     *     выполнения запроса (по умолчанию `null`).
     * @param null|string $fetchKey Ключ возвращаемого ассоциативного массива записей. 
     *     Если `null`, результатом будет индексированный массив записей (по умолчачнию `null`).
     * @param string|array<string, string> $order Порядок сортировки, например: 'field, ASC', 
     *     `['field' => 'ASC', 'field1' => 'DESC']` (по умолчанию '').
     * @param null|array<int, int> $limit Количество записей выводимых в запросе со смещением, например `[10, 10]`.
     * 
     * @return array
     */
    public function getRecords(
        string $tableName, 
        array $columns = ['*'],  
        PredicateInterface|Closure|string|array|null $where = null, 
        ?string $fetchKey = null, 
        array|string $order = '',  
        ?array $limit = null
    ): array {
        /** @var Select $select */
        $select = $this->db->select($tableName);
        $select->columns($columns);
        if ($where) {
            $select->where($where);
        }
        
        if ($order) {
            $select->order($order);
        }

        if ($limit) {
            $select->limit($limit[0]);
            if (isset($limit[1])) {
                $select->offset($limit[1]);
            }
        }
        return $this->getCommand()
            ->setSql($select)
            ->queryAll($fetchKey);
    }

    /**
     * Сбрасывает значение автоинкремента таблицы.
     * 
     * @param string $tableName Имя таблицы.
     * @param mixed $increment Значение автоинкремента.
     * 
     * @return void
     */
    public function resetIncrement(string $tableName, mixed $increment = 1): void
    {
        $this->getCommand()
            ->resetIncrement($tableName, $increment)
            ->execute();
    }
}