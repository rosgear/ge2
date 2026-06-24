<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

use Ge;
use Ge\Db\Adapter\Adapter;
use Ge\Db\Adapter\Platform\PlatformInterface;

/**
 * Конструктор SQL инструкции.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class QueryBuilder
{
    /**
     * Имя или идентификатор таблицы.
     * 
     * @see QueryBuilder::setTable()
     * 
     * @var TableIdentifier|string
     */
    protected TableIdentifier|string $table = '';

    /**
     * Адаптер подключения к базе данных.
     * 
     * @see QueryBuilder::__construct()
     * 
     * @var Adapter
     */
    protected Adapter $adapter;

    /**
     * Платформа адаптера.
     * 
     * @see QueryBuilder::__construct()
     * 
     * @var PlatformInterface|null
     */
    public ?PlatformInterface $sqlPlatform = null;

    /**
     * Последний вызываемый оператора.
     * 
     * @see QueryBuilder::select()
     * @see QueryBuilder::insert()
     * @see QueryBuilder::replace()
     * @see QueryBuilder::delete()
     * 
     * @var AbstractSql|null
     */
    protected ?AbstractSql $result = null;

    /**
     * Конструктор класса.
     * 
     * @param Adapter $adapter Адаптер подключения к базе данных.
     * @param PlatformInterface $platform Платформа адаптера.
     * @param TableIdentifier|string|null $table  Имя или идентификатор таблицы.
     */
    public function __construct(
        Adapter $adapter, 
        ?PlatformInterface $platform = null, 
        TableIdentifier|string|null $table = null)
    {
        $this->adapter = $adapter;
        $this->sqlPlatform = $platform ?: $this->adapter->getPlatform();
        if ($table) {
            $this->setTable($table);
        }
    }

    /**
     * Проверяет, указана ли таблица.
     * 
     * @return bool
     */
    public function hasTable(): bool
    {
        return isset($this->table);
    }

    /**
     * Указывает таблицу.
     *
     * @param TableIdentifier|string $table Имя или идентификатор таблицы.
     * 
     * @return $this
     */
    public function setTable(TableIdentifier|string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Возвращает таблицу.
     * 
     * @return TableIdentifier|string|null
     */
    public function getTable(): TableIdentifier|string|null
    {
        return isset($this->table) ? $this->table : null;
    }

    /**
     * Возвращает инструкцию SQL полученную последним оператором.
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function getSqlString(): string
    {
        if ($this->result === null) {
            throw new Exception\InvalidArgumentException(Ge::t('app', 'Can\'t get Sql string from query builder, unknow result'));
        }
        return $this->result->getSqlString($this->sqlPlatform);
    }

    /**
     * Создаёт оператор SELECT для инструкции SQL.
     * 
     * @param TableIdentifier|string|null|null $table
     * 
     * @return Select
     */
    public function select(TableIdentifier|string|null $table = null): Select
    {
        if (!isset($this->table) && $table !== null) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app',
                    'This Sql object is intended to work with only the table "{0}" provided at construction time', 
                    [is_object($table) ? $table->getTable() : $table]
                )
            );
        }
        return $this->result = new Select($table ?: $this->table);
    }

    /**
     * Создаёт оператор INSERT для инструкции SQL.
     * 
     * @param TableIdentifier|string|null|null $table
     * 
     * @return Insert
     */
    public function insert(TableIdentifier|string|null $table = null): Insert
    {
        if (!isset($this->table) && $table !== null) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app',
                    'This Sql object is intended to work with only the table "{0}" provided at construction time', 
                    [is_object($table) ? $table->getTable() : $table]
                )
            );
        }
        return $this->result = new Insert($table ?: $this->table);
    }

    /**
     * Создаёт оператор REPLACE для инструкции SQL.
     * 
     * @param TableIdentifier|string|null|null $table
     * 
     * @return Replace
     */
    public function replace(TableIdentifier|string|null $table = null): Replace
    {
        if (!isset($this->table) && $table !== null) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app',
                    'This Sql object is intended to work with only the table "{0}" provided at construction time', 
                    [is_object($table) ? $table->getTable() : $table]
                )
            );
        }
        return $this->result = new Replace($table ?: $this->table);
    }

    /**
     * Создаёт оператор DELETE для инструкции SQL.
     * 
     * @param TableIdentifier|string|array|null $table
     * 
     * @return Delete
     */
    public function delete(TableIdentifier|string|array|null $table = null): Delete
    {
        if (!isset($this->table) && $table !== null) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app',
                    'This Sql object is intended to work with only the table "{0}" provided at construction time', 
                    [is_object($table) ? $table->getTable() : $table]
                )
            );
        }
        return $this->result = new Delete($table ?: $this->table);
    }

    /**
     * Создаёт оператор UPDATE для инструкции SQL.
     * 
     * @param TableIdentifier|string|null|null $table
     * 
     * @return Update
     */
    public function update(TableIdentifier|string|null $table = null): Update
    {
        if (!isset($this->table) && $table !== null) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app',
                    'This Sql object is intended to work with only the table "{0}" provided at construction time', 
                    [is_object($table) ? $table->getTable() : $table]
                )
            );
        }
        return $this->result = new Update($table ?: $this->table);
    }

    /**
     * Создаёт оператор для формирования инструкции SQL.
     * 
     * @param string $sql Запрос SQL.
     * 
     * @return Query
     */
    public function sql(string $sql = ''): Query
    {
        return $this->result = new Query($sql);
    }

    /**
     * Возвращает адаптер подключения к базе данных.
     * 
     * @return Adapter|null
     */
    public function getAdapter(): ?Adapter
    {
        return $this->adapter;
    }
}
