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
use Closure;
use Ge\Db\Adapter\ParameterContainer;
use Ge\Db\Adapter\Driver\DriverInterface;
use Ge\Db\Adapter\Platform\PlatformInterface;
use Ge\Db\Sql\Predicate\PredicateInterface;
use Ge\Db\Sql\Expression;

/**
 * Оператор Select (выборки данных) инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Select extends AbstractSql
{
    /**#@+
     * Константы.
     * @const
     */
    public const SELECT = 'select';
    public const QUANTIFIER = 'quantifier';
    public const COLUMNS = 'columns';
    public const TABLE = 'table';
    public const JOINS = 'joins';
    public const WHERE = 'where';
    public const GROUP = 'group';
    public const HAVING = 'having';
    public const ORDER = 'order';
    public const LIMIT = 'limit';
    public const OFFSET = 'offset';
    public const QUANTIFIER_DISTINCT = 'DISTINCT';
    public const QUANTIFIER_ALL = 'ALL';
    public const JOIN_INNER = 'inner';
    public const JOIN_OUTER = 'outer';
    public const JOIN_LEFT = 'left';
    public const JOIN_RIGHT = 'right';
    public const JOIN_OUTER_RIGHT = 'outer right';
    public const JOIN_OUTER_LEFT  = 'outer left';
    public const SQL_STAR = '*';
    public const ORDER_ASCENDING = 'ASC';
    public const ORDER_DESCENDING = 'DESC';
    public const COMBINE = 'combine';
    public const COMBINE_UNION = 'union';
    public const COMBINE_EXCEPT = 'except';
    public const COMBINE_INTERSECT = 'intersect';
    /**#@-*/

    /**
     * Оператор Where.
     * 
     * @see Select::__construct()
     * 
     * @var Where
     */
    public Where $where;

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::SELECT => array(
            'SELECT %1$s FROM %2$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
            'SELECT %1$s %2$s FROM %3$s' => array(
                null,
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
            'SELECT %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
            ),
        ),
        self::JOINS  => array(
            '%1$s' => array(
                array(3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ')
            )
        ),
        self::WHERE  => 'WHERE %1$s',
        self::GROUP  => array(
            'GROUP BY %1$s' => array(
                array(1 => '%1$s', 'combinedby' => ', ')
            )
        ),
        self::ORDER  => array(
            'ORDER BY %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ')
            )
        ),
        self::LIMIT  => 'LIMIT %1$s',
        self::OFFSET => 'OFFSET %1$s'
    ];

    /**
     * Добавлять приставку имени таблицы к именам столбцов.
     * 
     * @see Select::columns()
     * 
     * @var bool
     */
    protected $prefixColumnsWithTable = true;

    /**
     * Имя или имена таблиц.
     * 
     * @see Select::from()
     * 
     * @var TableIdentifier|string|array|null
     */
    protected TableIdentifier|string|array|null $table = null;

    /**
     * Значение выражения JOIN инструкции SQL.
     * 
     * @see Select::joins()
     * 
     * @var array
     */
    protected array $joins = [];

    /**
     * Столбцы для выражения SELECT в инструкцию SQL.
     * 
     * @see Select::columns()
     * 
     * @var array
     */
    protected array $columns = [];

    /**
     * Значение выражения ORDER инструкции SQL.
     * 
     * @see Select::order()
     * 
     * @var array
     */
    protected array $order = [];

    /**
     * Значение выражения GROUP инструкции SQL.
     * 
     * @see Select::group()
     * 
     * @var array|null
     */
    protected ?array $group = null;

    /**
     * @var null|string|array
     */
    protected $having = null;

    /**
     * Значение выражения LIMIT инструкции SQL.
     * 
     * @see Select::limit()
     * 
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Значение выражения OFFSET инструкции SQL.
     * 
     * @see Select::offset()
     * 
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * @var bool
     */
    protected $tableReadOnly = false;

    /**
     * Квантификатор в инструкции SQL.
     * 
     * @see Select::quantifier()
     * 
     * @var Expression|string|null
     */
    protected Expression|string|null $quantifier = null;

    /**
     * Конструктор класса.
     *
     * @param TableIdentifier|string|array|null $table Имя или имена таблиц.
     */
    public function __construct(TableIdentifier|string|array|null $table = null)
    {
        if ($table) {
            $this->from($table);
            $this->tableReadOnly = true;
        }

        $this->where = new Where;
    }

    /**
     * Создаёт выражение FROM для инструкции SQL. 
     *
     * @param TableIdentifier|string|array $table Имя или имена таблиц.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function from(TableIdentifier|string|array $table): static
    {
        if ($this->tableReadOnly) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Since this object was created with a table and/or schema in the constructor, it is read only')
            );
        }

        if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', '$table must be a string, array, or an instance of TableIdentifier')
            );
        }

        if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'from() expects $table as an array is a single element associative array')
            );
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Добавляет квантификатор в инструкцию SQL.
     * 
     * @see Select::$quantifier
     * 
     * @param Expression|string $quantifier Квантификатор, например: 'DISTINCT', 'ALL'.
     * 
     * @return $this
     */
    public function quantifier(Expression|string $quantifier): static
    {
        $this->quantifier = $quantifier;
        return $this;
    }

    /**
     * Устанавливает столбцы для выражения SELECT в инструкцию SQL.
     *
     * Столбцы могут быть указаны, как:
     *   - `['*']`, все столбцы в результирующем запросе;
     *   - `['column', ...]`, значение может быть строкой или объектом Expression;
     *   - `['alias' => 'column', ...]`, ключом может быть псевдоним, а значение 
     * строка или объект Expression.
     *
     * @param array $columns Столбцы.
     * @param bool $prefixColumnsWithTable Добавить приставку имени таблицы к именам 
     *     столбцов (по умолчанию `true`).
     * 
     * @return $this
     */
    public function columns(array $columns, bool $prefixColumnsWithTable = true): static
    {
        $this->columns = $columns;
        $this->prefixColumnsWithTable = (bool) $prefixColumnsWithTable;
        return $this;
    }

    /**
     * Добавляет ORDER в инструкцию SQL.
     * 
     * @see Select::$order
     * 
     * @param string|array $order
     * 
     * @return $this
     */
    public function order(string|array $order): static
    {
        if ($order) {
            if (is_string($order)) {
                if (strpos($order, ',') !== false) {
                    $order = preg_split('#,\s+#', $order);
                } else {
                    $order = (array) $order;
                }
            } elseif (!is_array($order)) {
                $order = array($order);
            }

            foreach ($order as $k => $v) {
                if (is_string($k))
                    $this->order[$k] = $v;
                else
                    $this->order[] = $v;
            }
        }
        return $this;
    }

    /**
     * Добавляет LIMIT в инструкцию SQL.
     * 
     * @see Select::$limit
     * 
     * @param int|null $limit
     * 
     * @return $this
     */
    public function limit(?int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Добавляет OFFSET в инструкцию SQL. 
     * 
     * @see Select::$offset
     * 
     * @param int|null $offset
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function offset(?int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Создаёт выражение JOIN для инструкции SQL. 
     *
     * @param string|array $name Имя или мена таблиц для объединения.
     * @param Expression|string $on Имена столбцов для объединения.
     * @param string|array $columns Имена столбцов для SELECT.
     * @param string $type Одна из констант `JOIN_*`.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function join(
        string|array $name, 
        Expression|string $on, 
        string|array $columns = self::SQL_STAR, 
        string $type = self::JOIN_INNER
    ): static
    {
        if (is_array($name) && (!is_string(key($name)) || count($name) !== 1)) {
            throw new Exception\InvalidArgumentException(
                sprintf("join() expects '%s' as an array is a single element associative array", array_shift($name))
            );
        }
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->joins[] = [
            'name'    => $name,
            'on'      => $on,
            'columns' => $columns,
            'type'    => $type
        ];
        return $this;
    }

    /**
     * Создаёт выражение WHERE для инструкции SQL. 
     *
     * @param PredicateInterface|Closure|string|array $predicate Предикат.
     * @param string $combination Одна из констант `OP_*` для {@see Predicate\PredicateSet}.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function where(
        mixed $predicate, 
        string $combination = 'AND'
    ): static
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * Добавляет группировку в инструкцию SQL. 
     * 
     * @see Select::$group
     * 
     * @param mixed $group
     * 
     * @return $this
     */
    public function group(mixed $group): static
    {
        if (is_array($group)) {
            foreach ($group as $o) {
                $this->group[] = $o;
            }
        } else {
            $this->group[] = $group;
        }
        return $this;
    }

    /**
     * Возвращает часть выражения SELECT для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processSelect(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): array
    {
        $expr = 1;

        list($table, $fromTable) = $this->resolveTable($this->table, $platform, $driver, $parameterContainer);
        // формирование столбцов таблицы
        $columns = [];
        foreach ($this->columns as $columnIndexOrAs => $column) {
            if ($column === self::SQL_STAR) {
                $columns[] = [$fromTable . self::SQL_STAR];
                continue;
            }

            $columnName = $this->resolveColumnValue(
                array(
                    'column'       => $column,
                    'fromTable'    => $fromTable,
                    'isIdentifier' => true,
                ),
                $platform,
                $driver,
                $parameterContainer,
                (is_string($columnIndexOrAs) ? $columnIndexOrAs : 'column')
            );
            // формирование частей
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false) {
                $columnAs = (is_string($column)) ? $platform->quoteIdentifier($column) : 'Expression' . $expr++;
            }
            $columns[] = (isset($columnAs)) ? [$columnName, $columnAs] : [$columnName];
        }

        // формирование объединений
        foreach ($this->joins as $join) {
            $joinName = (is_array($join['name'])) ? key($join['name']) : $join['name'];
            $joinName = parent::resolveTable($joinName, $platform, $driver, $parameterContainer);

            foreach ($join['columns'] as $jKey => $jColumn) {
                $jColumns = array();
                $jFromTable = is_scalar($jColumn)
                            ? $joinName . $platform->getIdentifierSeparator()
                            : '';
                $jColumns[] = $this->resolveColumnValue(
                    [
                        'column'       => $jColumn,
                        'fromTable'    => $jFromTable,
                        'isIdentifier' => true,
                    ],
                    $platform,
                    $driver,
                    $parameterContainer,
                    (is_string($jKey) ? $jKey : 'column')
                );
                if (is_string($jKey)) {
                    $jColumns[] = $platform->quoteIdentifier($jKey);
                } elseif ($jColumn !== self::SQL_STAR) {
                    $jColumns[] = $platform->quoteIdentifier($jColumn);
                }
                $columns[] = $jColumns;
            }
        }

        if ($this->quantifier) {
            $quantifier = ($this->quantifier instanceof ExpressionInterface)
                    ? $this->processExpression($this->quantifier, $platform, $driver, $parameterContainer, 'quantifier')
                    : $this->quantifier;
        }

        if (!isset($table)) {
            return [$columns];
        }
        if (isset($quantifier)) {
            return [$quantifier, $columns, $table];
        }
        return [$columns, $table];
    }

    /**
     * Возвращает выражение ORDER для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processOrder(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if (empty($this->order)) return '';

        $orders = [];
        foreach ($this->order as $k => $v) {
            if (is_int($k)) {
                if (strpos($v, ' ') !== false) {
                    list($k, $v) = preg_split('# #', $v, 2);
                } else {
                    $k = $v;
                    $v = self::ORDER_ASCENDING;
                }
            }
            if (strtoupper($v) == self::ORDER_DESCENDING) {
                $orders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING];
            } else {
                $orders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING];
            }
        }
        return [$orders];
    }

    /**
     * Возвращает выражение LIMIT для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processLimit(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if ($this->limit === null) return '';

        if ($parameterContainer) {
            $parameterContainer->offsetSet('limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('limit')];
        }
        return [$this->limit];
    }

    /**
     * Возвращает выражение OFFSET для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processOffset(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if ($this->offset === null) return '';

        if ($parameterContainer) {
            $parameterContainer->offsetSet('offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('offset')];
        }
        return [$this->offset];
    }

    /**
     * Возвращает выражение JOIN для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    protected function processJoins(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if (!$this->joins) return '';

        // process joins
        $joinSpecArgArray = [];
        foreach ($this->joins as $j => $join) {
            $joinName = null;
            $joinAs = null;

            // имя таблицы
            if (is_array($join['name'])) {
                $joinName = current($join['name']);
                $joinAs = $platform->quoteIdentifier(key($join['name']));
            } else {
                $joinName = $join['name'];
            }

            if ($joinName instanceof Expression) {
                $joinName = $joinName->getExpression();
            } elseif ($joinName instanceof TableIdentifier) {
                $joinName = $joinName->getTableAndSchema();
                $joinName = ($joinName[1] ? $platform->quoteIdentifier($joinName[1]) . $platform->getIdentifierSeparator() : '') . $platform->quoteIdentifier($joinName[0]);
            } elseif ($joinName instanceof Select) {
                $joinName = '(' . $this->processSubSelect($joinName, $platform, $driver, $parameterContainer) . ')';
            } elseif (is_string($joinName) || (is_object($joinName) && is_callable(array($joinName, '__toString')))) {
                $joinName = $platform->quoteIdentifier($joinName);
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf('Join name expected to be Expression|TableIdentifier|Select|string, "%s" given', gettype($joinName))
                );
            }

            $joinSpecArgArray[$j] = [
                strtoupper($join['type']),
                $this->renderTable($joinName, $joinAs),
            ];

            // on expression
            // примечание: для объектов Expression передавать в ProcessExpression с префиксом, 
            // специфически для каждого соединения (используется для именованных параметров)
            $joinSpecArgArray[$j][] = ($join['on'] instanceof ExpressionInterface)
                ? $this->processExpression($join['on'], $platform, $driver, $parameterContainer, 'join' . ($j+1) . 'part')
                : $platform->quoteIdentifierInFragment($join['on'], array('=', 'AND', 'OR', '(', ')', 'BETWEEN', '<', '>')); // on
        }
        return [$joinSpecArgArray];
    }

    /**
     * Возвращает выражение WHERE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processWhere(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if ($this->where === null) return '';
        if ($this->where->count() == 0) return '';

        return [
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        ];
    }

    /**
     * Возвращает выражение GROUP для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param null|DriverInterface $driver Драйвера подключения (по умолчанию `null`).
     * @param null|ParameterContainer $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string|array
     * 
     * @throws Exception\RuntimeException
     */
    protected function processGroup(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string|array
    {
        if ($this->group === null) return '';

        // обработка столбцов таблицы
        $groups = [];
        foreach ($this->group as $column) {
            $groups[] = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'isIdentifier' => true,
                ],
                $platform,
                $driver,
                $parameterContainer,
                'group'
            );
        }
        // TODO: array($groups) => array(array($groups));
        return [[$groups]];
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveTable(
        TableIdentifier|Select|string|array $table,
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ): string|array 
    {
        $alias = null;

        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        }

        $table = parent::resolveTable($table, $platform, $driver, $parameterContainer);

        if ($alias) {
            $fromTable = $platform->quoteIdentifier($alias);
            $table = $this->renderTable($table, $fromTable);
        } else {
            $fromTable = $table;
        }

        if ($this->prefixColumnsWithTable && $fromTable) {
            $fromTable .= $platform->getIdentifierSeparator();
        } else {
            $fromTable = '';
        }

        return [$table, $fromTable];
    }

}
