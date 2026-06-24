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
 * Класс CreateTable создаёт инструкцию SQL "CREATE TABLE" для создания таблицы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl
 * @since 2.0
 */
class CreateTable extends AbstractSql implements SqlInterface
{
    /**
     * @var string Ключ "columns" (столбцы таблицы) в спецификации.
     */
    public const COLUMNS  = 'columns';

    /**
     * @var string Ключ "constraints" (ограничения таблицы) в спецификации.
     */
    public const CONSTRAINTS = 'constraints';

    /**
     * @var string Ключ "table" (создание таблицы) в спецификации.
     */
    public const TABLE = 'table';

    /**
     * Столбцы таблицы.
     * 
     * @see CreateTable::addColumn()
     * 
     * @var array<int, ColumnInterface>
     */
    protected array $columns = [];

    /**
     * Ограничения.
     * 
     * @see CreateTable::addConstraint()
     * 
     * @var array<int, ConstraintInterface>
     */
    protected array $constraints = [];

    /**
     * Временная таблица.
     * 
     * @see CreateTable::setTemporary()
     * 
     * @var bool
     */
    protected bool $isTemporary = false;

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::TABLE    => 'CREATE %1$sTABLE %2$s (',
        self::COLUMNS  => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'combinedBy' => ",",
        self::CONSTRAINTS => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'statementEnd' => '%1$s',
    ];

    /**
     * Имя таблицы.
     * 
     * @see CreateTable::__construct()
     * 
     * @var string
     */
    protected string $table = '';

    /**
     * Конструктор класса.
     * 
     * @param string $table Имя таблицы (по умолчанию '').
     * @param bool $isTemporary Временная таблица (по умолчанию `false`).
     */
    public function __construct(string $table = '', bool $isTemporary = false)
    {
        $this->table = $table;
        $this->setTemporary($isTemporary);
    }

    /**
     * Указать, что таблица временная.
     * 
     * @param bool $temporary Временная таблица.
     * 
     * @return $this
     */
    public function setTemporary(bool $temporary): static
    {
        $this->isTemporary = $temporary;
        return $this;
    }

    /**
     * Проверяет, временная ли таблица.
     * 
     * @return bool
     */
    public function isTemporary(): bool
    {
        return $this->isTemporary;
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
     * Добавляет ограничение.
     * 
     * @param ConstraintInterface $constraint
     * 
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint): static
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * Возвращает необработанное состояние.
     *
     * @param string|null $key Ключ: 'columns', 'constraints', 'table'.
     * 
     * @return mixed
     */
    public function getRawState(?string $key = null): array
    {
        $rawState = [
            self::COLUMNS     => $this->columns,
            self::CONSTRAINTS => $this->constraints,
            self::TABLE       => $this->table,
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
        return [
            $this->isTemporary ? 'TEMPORARY ' : '',
            $platform->quoteIdentifier($this->table),
        ];
    }

    /**
     * Подготавливает столбцы для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array|null
     */
    protected function processColumns(PlatformInterface $platform): ?array
    {
        if (empty($this->columns)) return null;

        $sqls = [];
        foreach ($this->columns as $column) {
            $sqls[] = $this->processExpression($column, $platform);
        }
        return [$sqls];
    }

    /**
     * Подготавливает сочетание для спецификации.
     * 
     * @param PlatformInterface|null $platform Платформа адаптера.
     * 
     * @return array|string|null
     */
    protected function processCombinedby(?PlatformInterface $platform = null): array|string|null
    {
        if ($this->constraints && $this->columns) {
            return $this->specifications['combinedBy'];
        }
        return null;
    }

    /**
     * Подготавливает ограничения для спецификации.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array|null
     */
    protected function processConstraints(PlatformInterface $platform)
    {
        if (empty($this->constraints)) return null;

        $sqls = [];
        foreach ($this->constraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $platform);
        }
        return [$sqls];
    }

    /**
     * Подготавливает конца утверждения для спецификации.
     * 
     * @param PlatformInterface|null $platform Платформа адаптера.
     * 
     * @return array<int, string>
     */
    protected function processStatementEnd(?PlatformInterface $platform = null): array
    {
        return ["\n)"];
    }
}
