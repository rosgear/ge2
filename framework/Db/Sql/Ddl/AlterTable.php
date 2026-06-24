<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl;

use Ge\Db\Sql\AbstractSql;
use Ge\Db\Sql\Ddl\Column\ColumnInterface;
use Ge\Db\Adapter\Platform\PlatformInterface;
use Ge\Db\Sql\Ddl\Constraint\ConstraintInterface;

/**
 * Класс AlterTable создаёт инструкцию SQL "ALTER TABLE" для внесения изменений в 
 * таблицу и столбцы. 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl
 * @since 2.0
 */
class AlterTable extends AbstractSql implements SqlInterface
{
    /**
     * @var string Ключ "addColumns" (добавление столбцов) в спецификации.
     */
    public const ADD_COLUMNS = 'addColumns';

    /**
     * @var string Ключ "addConstraints" (добавление ограничений) в спецификации.
     */
    public const ADD_CONSTRAINTS = 'addConstraints';

    /**
     * @var string Ключ "changeColumns" (изменение столбцов) в спецификации.
     */
    public const CHANGE_COLUMNS = 'changeColumns';

    /**
     * @var string Ключ "dropColumns" (удаление столбцов) в спецификации.
     */
    public const DROP_COLUMNS = 'dropColumns';

    /**
     * @var string Ключ "dropConstraints" (удаление ограничений) в спецификации.
     */
    public const DROP_CONSTRAINTS = 'dropConstraints';

    /**
     * @var string Ключ "table" (изменение таблицы) в спецификации.
     */
    public const TABLE = 'table';

    /**
     * Столбцы.
     * 
     * @var array<int, ColumnInterface>
     */
    protected array $columns = [];

    /**
     * Добавление столбцов.
     * 
     * @see AlterTable::addColumn()
     * 
     * @var array<int, ColumnInterface>
     */
    protected array $addColumns = [];

    /**
     * Добавление ограничений.
     * 
     * @see AlterTable::addConstraints()
     * 
     * @var array<int, ConstraintInterface>
     */
    protected array $addConstraints = [];

    /**
     * Изменение столбцов.
     * 
     * @see AlterTable::changeColumns()
     * 
     * @var array<string, ColumnInterface>
     */
    protected array $changeColumns = [];

    /**
     * Удаление столбцов.
     * 
     * @see AlterTable::dropColumns()
     * 
     * @var array<int, string>
     */
    protected array $dropColumns = [];

    /**
     * Удаление ограничений.
     * 
     * @see AlterTable::dropConstraints()
     * 
     * @var array<int, string>
     */
    protected array $dropConstraints = [];

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::TABLE => "ALTER TABLE %1\$s\n",
        self::ADD_COLUMNS  => [
            "%1\$s" => [
                [1 => "ADD COLUMN %1\$s,\n", 'combinedby' => ""]
            ]
        ],
        self::CHANGE_COLUMNS  => [
            "%1\$s" => [
                [2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ""]
            ]
        ],
        self::DROP_COLUMNS  => [
            "%1\$s" => [
                [1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ""]
            ]
        ],
        self::ADD_CONSTRAINTS  => [
            "%1\$s" => [
                [1 => "ADD %1\$s,\n", 'combinedby' => ""]
            ]
        ],
        self::DROP_CONSTRAINTS  => [
            "%1\$s" => [
                [1 => "DROP CONSTRAINT %1\$s,\n", 'combinedby' => ""]
            ]
        ]
    ];

    /**
     * Имя таблицы.
     * 
     * @see AlterTable::setTable()
     * 
     * @var string
     */
    protected string $table = '';

    /**
     * Конструктор класса.
     * 
     * @param string $table Имя таблицы (по умолчанию '').
     */
    public function __construct(string $table = '')
    {
        $this->setTable($table);
    }

    /**
     * Устанавливает имя таблицы.
     * 
     * @param string $name Имя таблицы.
     * 
     * @return $this
     */
    public function setTable(string $name): static
    {
        $this->table = $name;
        return $this;
    }

    /**
     * Добавляет столбец таблицы.
     * 
     * @param ColumnInterface $column Столбец таблицы.
     * 
     * @return $this
     */
    public function addColumn(ColumnInterface $column): static
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * Указывает, какой столбец необходимо изменить.
     * 
     * @param string $name Имя изменяемого столбца.
     * @param ColumnInterface $column Новый столбец.
     * 
     * @return $this
     */
    public function changeColumn(string $name, ColumnInterface $column): static
    {
        $this->changeColumns[$name] = $column;
        return $this;
    }

    /**
     * Удаляет столбец.
     * 
     * @param string $name Имя удаляемого столбца.
     * 
     * @return $this
     */
    public function dropColumn(string $name): static
    {
        $this->dropColumns[] = $name;
        return $this;
    }

    /**
     * Удаляет ограничение таблицы.
     * 
     * @param string $name Имя ограничения.
     * 
     * @return $this
     */
    public function dropConstraint(string $name): static
    {
        $this->dropConstraints[] = $name;
        return $this;
    }

    /**
     * Добавляет ограничение таблицы.
     * 
     * @param ConstraintInterface $constraint Ограничение таблицы.
     * 
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint): static
    {
        $this->addConstraints[] = $constraint;
        return $this;
    }

    /**
     * Возвращает необработанное состояние.
     *
     * @param string|null $key Ключ: 'addColumns', 'dropColumns', 'changeColumns', 
     *     'addConstraints', 'dropConstraints'.
     * 
     * @return mixed
     */
    public function getRawState(?string $key = null): array
    {
        $rawState = [
            self::TABLE            => $this->table,
            self::ADD_COLUMNS      => $this->addColumns,
            self::DROP_COLUMNS     => $this->dropColumns,
            self::CHANGE_COLUMNS   => $this->changeColumns,
            self::ADD_CONSTRAINTS  => $this->addConstraints,
            self::DROP_CONSTRAINTS => $this->dropConstraints,
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Подготавливает имя таблицы для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processTable(PlatformInterface $platform): array
    {
        return [$platform->quoteIdentifier($this->table)];
    }

    /**
     * Подготавливает добавление столбцов для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processAddColumns(PlatformInterface $platform): array
    {
        $sqls = [];
        foreach ($this->addColumns as $column) {
            $sqls[] = $this->processExpression($column, $platform);
        }
        return [$sqls];
    }

    /**
     * Подготавливает изменение столбцов для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processChangeColumns(PlatformInterface $platform): array
    {
        $sqls = [];
        foreach ($this->changeColumns as $name => $column) {
            $sqls[] = [
                $platform->quoteIdentifier($name),
                $this->processExpression($column, $platform)
            ];
        }
        return [$sqls];
    }

    /**
     * Подготавливает удаление столбцов для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processDropColumns(PlatformInterface $platform): array
    {
        $sqls = [];
        foreach ($this->dropColumns as $column) {
            $sqls[] = $platform->quoteIdentifier($column);
        }
        return [$sqls];
    }

    /**
     * Подготавливает добавление ограничений для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processAddConstraints(PlatformInterface $platform): array
    {
        $sqls = [];
        foreach ($this->addConstraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $platform);
        }
        return [$sqls];
    }

    /**
     * Подготавливает удаление ограничений для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processDropConstraints(PlatformInterface $platform): array
    {
        $sqls = [];
        foreach ($this->dropConstraints as $constraint) {
            $sqls[] = $platform->quoteIdentifier($constraint);
        }
        return [$sqls];
    }
}
