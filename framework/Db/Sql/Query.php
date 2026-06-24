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
use Ge\Db\Adapter\ParameterContainer;
use Ge\Db\Adapter\Driver\DriverInterface;
use Ge\Db\Adapter\Platform\PlatformInterface;

/**
 * Оператор запроса для формирования SQL инструкции.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Query extends AbstractSql
{
    /**#@+
     * @const Константы.
     */
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
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::WHERE  => 'WHERE %1$s',
        self::ORDER  => [
            'ORDER BY %1$s' => [
                [1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ']
            ]
        ],
        self::LIMIT  => 'LIMIT %1$s',
        self::OFFSET => 'OFFSET %1$s'
    ];

    /**
     * Оператор Where.
     * 
     * @see Query::__construct()
     * 
     * @var Where
     */
    public Where $where;

    /**
     * Столбцы для выражения SELECT в инструкцию SQL.
     * 
     * @see Query::columns()
     * 
     * @var array
     */
    protected array $columns = [];

    /**
     * Значение выражения ORDER инструкции SQL.
     * 
     * @see Query::order()
     * 
     * @var array
     */
    protected array $order = [];

    /**
     * Значение выражения LIMIT инструкции SQL.
     * 
     * @see Query::limit()
     * 
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Значение выражения OFFSET инструкции SQL.
     * 
     * @see Query::offset()
     * 
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * Запрос.
     * 
     * @see Query::__construct()
     * 
     * @var string
     */
    protected $query = '';

    /**
     * Конструктор класса.
     *
     * @param string $query Запрос (по умолчанию '').
     */
    public function __construct(string $query = '')
    {
        $this->query = $query;
        $this->where = new Where;
    }

    /**
     * Формирует инструкцию SQL для выполнения фильтрации записей. 
     *
     * @param array<int, array> $data
     * @param array<string, mixed> $fieldConfigs Параметры полей (по умолчанию `[]`).
     * 
     * @return static
     */
    public function filter(array $data, array $fieldConfigs = []): static
    {
        if (empty($data)) return $this;

        // расчеты с датой для сервера в часовом поясе \Ge\I18n\Formatter::$defaultTimeZone (по умолчанию в UTC)
        $beforeTimeZone = Ge::$app->formatter->timeZone;
        Ge::$app->formatter->timeZone = Ge::$app->formatter->defaultTimeZone;

        foreach ($data as $filter)
        {
            $operator = $filter['operator'] ?? false;
            $value    = $filter['value'] ?? false;
            $property = $filter['property'] ?? false;

            $filterType = '';
            // если указаны настройки
            if ($fieldConfigs != null) {
                // если псевдоним существует
                if (isset($fieldConfigs[$property])) {
                    $fieldConfig = $fieldConfigs[$property];
                    // если псевдоним поля имеет настройки
                    if (is_array($fieldConfig)) {
                        // если указан вид фильтра в настройках
                        $filterType = isset($fieldConfig['filterType']) ? $fieldConfig['filterType'] : '';
                        // имя поля для фильтрации: "direct" (поле включает имя базы данных) или "field" 
                        $property = isset($fieldConfig['direct']) ? $fieldConfig['direct'] : $fieldConfig['field'];
                    } else
                        $property = $fieldConfig;
                } else
                    continue;
            }

            // исключение
            if ($operator == '=')
                if (empty($value)) $value = '0';
            if (!$operator || !$value || !$property) continue;

            // filter operator: "boolean", "date", "list", "number"
            switch ($operator) {
                case 'where':
                    $this->where(sprintf($filter['where'], $value));
                    break;

                case 'like':
                    $this->where->like($property, $value . '%');
                    break;

                // равенство
                case '=':
                    if ($value == 'true')
                        $value = '1';
                    else
                    if ($value == 'false')
                        $value = '0';
                    $this->where(array($property => $value));
                    break;

                // множество
                case 'in':
                    if (!is_array($value)) return $this;
                    $this->where->in($property, $value);
                    break;

                // до даты (Y-m-d) / меньше чем число
                case 'lt':
                    // если дата, а не числовое или строкове значение
                    if (!is_numeric($value)) {
                        $value = Ge::$app->formatter->toDate($value, 'php:Y-m-d');
                    }
                    $this->where->lessThan($property, $value);
                    break;

                // после даты (Y-m-d) / больше чем число
                case 'gt':
                    // если дата, а не числовое или строкове значение
                    if (!is_numeric($value)) {
                        $value = Ge::$app->formatter->toDate($value, 'php:Y-m-d');
                    }
                    $this->where->greaterThan($property, $value);
                    break;

                 // на дату (Y-m-d) / ровно числовому или строковому значению
                case 'eq':
                    // если числовое или строкове значение
                    if (is_numeric($value)) {
                        $this->where->equalTo($property, $value);
                    // если дата и/или время
                    } else {
                        if ($filterType == 'datetime') {
                            $this->where->Between(
                                $property,
                                Ge::$app->formatter->toDate($value, 'php:Y-m-d 00:00:00'),
                                Ge::$app->formatter->toDate($value, 'php:Y-m-d 23:59:59')
                            );
                        } else {
                            $value = Ge::$app->formatter->toDate($value, 'php:Y-m-d');
                            $this->where->equalTo($property, $value);
                        }
                    }
                    break;

                // диапазон дат (table.property BETWEEN {fromDate} AND {toDate})
                case 'dr':
                    $fromDate = '';
                    $toDate   = Ge::$app->formatter->toDateTime('now', 'php:Y-m-d H:i:s');
                    switch ($value) {
                        // за день
                        case 'lt-1d':
                            $fromDate = Ge::$app->formatter->toDate('now', 'php:Y-m-d 00:00:00');
                            break;

                        // за вчера
                        case 'lt-2d':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 00:00:00');
                            $toDate   = Ge::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 23:59:59');
                            break;

                        // за неделю
                        case 'lt-1w':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1W', 'Y-m-d 00:00:00');
                            break;

                        // за месяц
                        case 'lt-1m':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1M', 'Y-m-d 00:00:00');
                            break;

                        // за год
                        case 'lt-1y':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1Y', 'Y-m-d 00:00:00');
                            break;
                    }
                    if ($fromDate && $toDate) {
                        $this->where
                            ->nest()
                                ->Between($property, $fromDate, $toDate)
                            ->unnest();
                    }
                    break;

                // запись пользователя, если есть столбец аудита пользователя (table._updated_user={value} OR table._created_user={value})
                case 'lu':
                    $this->where
                        ->nest()
                            ->equalTo($property . '._updated_user', $value)
                            ->OR
                            ->equalTo($property . '._created_user', $value)
                        ->unnest();
                    break;

                // запись пользователя, если есть столбец аудита даты (table._updated_user={value} OR table._created_user={value})
                case 'ld':
                    $fromDate = '';
                    $toDate   = Ge::$app->formatter->toDateTime('now', 'php:Y-m-d H:i:s');
                    switch ($value) {
                        // за день
                        case 'lt-1d':
                            $fromDate = Ge::$app->formatter->toDate('now', 'php:Y-m-d 00:00:00');
                            break;

                        // за вчера
                        case 'lt-2d':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 00:00:00');
                            $toDate   = Ge::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 23:59:59');
                            break;

                        // за неделю
                        case 'lt-1w':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1W', 'Y-m-d 00:00:00');
                            break;

                        // за месяц
                        case 'lt-1m':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1M', 'Y-m-d 00:00:00');
                            break;

                        // за год
                        case 'lt-1y':
                            $fromDate = Ge::$app->formatter->toDateInterval('now', '-P1Y', 'Y-m-d 00:00:00');
                            break;
                    }
                    if ($fromDate && $toDate) {
                        $this->where
                            ->nest()
                                ->Between($property. '._created_date', $fromDate, $toDate)
                                ->OR
                                ->Between($property. '._updated_date', $fromDate, $toDate)
                            ->unnest();
                    }
                    break;
            } // end switch
        } // end foreach
        Ge::$app->formatter->timeZone = $beforeTimeZone;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlString(PlatformInterface $adapterPlatform): string
    {
        return $this->query . ' ' . parent::getSqlString($adapterPlatform);
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
                if (is_string($k)) {
                    $this->order[$k] = $v;
                } else {
                    $this->order[] = $v;
                }
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
     * Создаёт выражение WHERE для инструкции SQL. 
     *
     * @param Predicate\PredicateInterface|Where|\Closure|string|array $predicate Предикат.
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
}
