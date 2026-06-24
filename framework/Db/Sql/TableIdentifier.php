<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

/**
 * Идентификатор таблицы базы данных в инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class TableIdentifier
{
    /**
     * Имя таблицы базы данных.
     * 
     * @var string
     */
    protected string $table;

    /**
     * Схема базы данных.
     * 
     * @var string|null
     */
    protected ?string $schema;

    /**
     * Конструктор класса.
     * 
     * @param mixed $table Имя таблицы базы данных.
     * @param null|string $schema Схема базы данных.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(mixed $table, ?string $schema = null)
    {
        if (! (is_string($table) || is_callable(array($table, '__toString')))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$table must be a valid table name, parameter of type %s given',
                is_object($table) ? get_class($table) : gettype($table)
            ));
        }

        $this->table = (string) $table;

        if ('' === $this->table) {
            throw new Exception\InvalidArgumentException('$table must be a valid table name, empty string given');
        }

        if (null === $schema) {
            $this->schema = null;
        } else {
            if (! (is_string($schema) || is_callable(array($schema, '__toString')))) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '$schema must be a valid schema name, parameter of type %s given',
                    is_object($schema) ? get_class($schema) : gettype($schema)
                ));
            }

            $this->schema = (string) $schema;

            if ('' === $this->schema) {
                throw new Exception\InvalidArgumentException(
                    '$schema must be a valid schema name or null, empty string given'
                );
            }
        }
    }

    /**
     * Устанавливает имя таблицы.
     * 
     * @param string $table
     *
     * @deprecated Используйте конструктор класса.
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * Возвращает имя таблицы.
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Проверяет, устанловлена ли схема базы данных.
     * 
     * @return bool
     */
    public function hasSchema(): bool
    {
        return ($this->schema !== null);
    }

    /**
     * Устанавливает схему базы данных.
     * 
     * @param $schema Схема базы данных.
     *
     * @deprecated Используйте конструктор класса.
     */
    public function setSchema(?string $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Возвращает схему базы данных.
     * 
     * @return null|string
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Возвращает таблицу и схему базы данных.
     * 
     * @return array
     */
    public function getTableAndSchema(): array
    {
        return [$this->table, $this->schema];
    }
}
