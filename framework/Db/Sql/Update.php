<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

use Closure;
use Ge\Stdlib\PriorityList;
use Ge\Db\Adapter\ParameterContainer;
use Ge\Db\Adapter\Driver\DriverInterface;
use Ge\Db\Adapter\Platform\PlatformInterface;
use Ge\Db\Sql\Predicate\PredicateInterface;

/**
 * Оператор Update (обновление данных) SQL инструкции.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Update extends AbstractSql
{
    /**@#++
     * @const Константы.
     */
    public const SPECIFICATION_UPDATE = 'update';
    public const SPECIFICATION_WHERE = 'where';
    public const VALUES_MERGE = 'merge';
    public const VALUES_SET   = 'set';
    /**@#-**/

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s SET %2$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];

    /**
     * Имя или идентификатор таблицы.
     * 
     * @see Update::table()
     * 
     * @var TableIdentifier|string
     */
    protected TableIdentifier|string $table = '';

    /**
     * @see Update::getRawState()
     * 
     * @var bool
     */
    protected bool $emptyWhereProtection = true;

    /**
     * Список приоритетности.
     * 
     * @see Update::__construct()
     * 
     * @var PriorityList
     */
    protected PriorityList $set;

    /**
     * Оператор Where.
     * 
     * @see Update::__construct()
     * 
     * @var Where|string
     */
    protected Where|string $where;

    /**
     * Конструктор класса.
     *
     * @param TableIdentifier|string|null $table Имя или идентификатор таблицы.
     */
    public function __construct(TableIdentifier|string|null $table = null)
    {
        if ($table) {
            $this->table($table);
        }
        $this->where = new Where();
        $this->set = new PriorityList();
        $this->set->isLIFO(false);
    }

    /**
     * Указывает таблицу.
     *
     * @param TableIdentifier|string $table Имя или идентификатор таблицы.
     * 
     * @return $this
     */
    public function table(TableIdentifier|string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Устанавливает пары "ключ - значение" для формирования выражения SET инструкции SQL.
     * 
     * @param array $values Значения в виде пар "ключ - значение".
     * @param string $flag Одна из констант `VALUES_*`.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function set(array $values, string $flag = self::VALUES_SET): static
    {
        if ($values === null) {
            throw new Exception\InvalidArgumentException('set() expects an array of values');
        }

        if ($flag == self::VALUES_SET) {
            $this->set->clear();
        }
        $priority = is_numeric($flag) ? $flag : 0;
        foreach ($values as $k => $v) {
            if (!is_string($k)) {
                throw new Exception\InvalidArgumentException('set() expects a string for the value key');
            }
            $this->set->insert($k, $v, $priority);
        }
        return $this;
    }

    /**
     * Создаёт выражение WHERE для инструкции SQL. 
     *
     * @param PredicateInterface|Closure|string|array $predicate Оператор WHERE.
     * @param string $combination Одна из констант `OP_*` {@see Predicate\PredicateSet}.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function where(
        PredicateInterface|Closure|string|array $predicate, 
        string $combination = Predicate\PredicateSet::OP_AND
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
     * Возвращает необработанное состояние.
     *
     * @param null|string $key Ключ: 'emptyWhereProtection', 'table', 'set', 'where'.
     * 
     * @return mixed
     */
    public function getRawState(?string $key = null): mixed
    {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set'   => $this->set->toArray(),
            'where' => $this->where
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Возвращает выражение UPDATE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    protected function processUpdate(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        $setSql = [];
        foreach ($this->set as $column => $value) {
            $prefix = $platform->quoteIdentifier($column) . ' = ';
            if (is_scalar($value) && $parameterContainer) {
                $setSql[] = $prefix . $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $setSql[] = $prefix . $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }

        return sprintf(
            $this->specifications[static::SPECIFICATION_UPDATE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            implode(', ', $setSql)
        );
    }

    /**
     * Возвращает выражение WHERE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    protected function processWhere(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        if ($this->where->count() == 0) return '';

        return sprintf(
            $this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    /**
     * Чтении значения из несуществуюшего свойства. 
     * 
     * Только для получения "where".
     *
     * @param string $name
     * 
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (strtolower($name) == 'where') {
            return $this->where;
        }
    }

    /**
     * Клонирование объекта.
     *
     * Сбрасывает оператор Where и Set при клонировании.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where = clone $this->where;
        $this->set = clone $this->set;
    }
}
