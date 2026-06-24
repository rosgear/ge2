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
use Ge\Db\Adapter\ParameterContainer;
use Ge\Db\Adapter\Driver\DriverInterface;
use Ge\Db\Adapter\Platform\PlatformInterface;
use Ge\Db\Sql\Predicate\PredicateInterface;

/**
 * Оператор Delete (удаления записей) инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Delete extends AbstractSql
{
    /**@#+
     * @const Константы.
     */
    const SPECIFICATION_DELETE = 'delete';
    const SPECIFICATION_USING = 'using';
    const SPECIFICATION_WHERE = 'where';
    /**@#-*/

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
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_USING => 'USING %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s',
    ];

    /**
     * Имя или идентификатор таблицы.
     * 
     * @see Delete::table()
     * 
     * @var TableIdentifier|array|string
     */
    protected TableIdentifier|array|string $table = '';

    /**
     * Имя или идентификатор таблицы для выражения USING.
     * 
     * @see Delete::using()
     * 
     * @var string|TableIdentifier
     */
    protected $usingTables = '';

    /**
     * @var bool
     */
    protected bool $emptyWhereProtection = true;

    /**
     * @var array
     */
    protected array $set = [];

    /**
     * Конструктор класса.
     *
     * @param TableIdentifier|string|null $table Имя или идентификатор таблицы.
     */
    public function __construct(TableIdentifier|string|array|null $table = null)
    {
        if ($table) {
            $this->from($table);
        }
        $this->where = new Where();
    }

    /**
     * Устанавливает таблицу для выражения FROM инструкции SQL.
     *
     * @param TableIdentifier|string $table Имя или идентификатор таблицы.
     * 
     * @return $this
     */
    public function from(TableIdentifier|array|string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Устанавливает таблицу для выражения USING инструкции SQL.
     *
     * @param TableIdentifier|string|array $table Имя или идентификатор таблицы.
     * 
     * @return $this
     */
    public function using(TableIdentifier|string|array $table): static
    {
        $this->usingTables = $table;
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
            'set'   => $this->set,
            'where' => $this->where
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Создаёт выражение WHERE для инструкции SQL. 
     *
     * @param Where|\Closure|string|array $predicate Оператор WHERE.
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
     * Возвращает выражение DELETE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     */
    protected function processDelete(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        $tables = [];
        if (is_array($this->table)) {
            foreach ($this->table as $alias => $table) {
                if (is_numeric($alias))
                    $resolve = $table;
                else
                    $resolve = [$alias => $table];
                $tables[] = $this->resolveTable($resolve, $platform, $driver, $parameterContainer);
            }
            $strTable = implode(',', $tables);
        } else
            $strTable = $this->resolveTable($this->table, $platform, $driver, $parameterContainer);

        return sprintf(
            $this->specifications[static::SPECIFICATION_DELETE],
            $strTable
        );
    }

    /**
     * Возвращает выражение USING для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     */
    protected function processUsing(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        if (empty($this->usingTables)) return '';

        $table = '';
        if (is_array($this->usingTables)) {
            $arr = array();
            foreach($this->usingTables as $name) {
                $arr[] = $this->resolveTable($name, $platform, $driver, $parameterContainer);
            }
            $table = implode(',', $arr);
        } else
            $table = $this->resolveTable($this->usingTables, $platform, $driver, $parameterContainer);

        return sprintf(
            $this->specifications[static::SPECIFICATION_USING],
            $table
        );
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
            $table = $this->renderTable($fromTable, $table);
        } else
            $fromTable = $table;

        if ($this->prefixColumnsWithTable && $fromTable)
            $fromTable .= $platform->getIdentifierSeparator();
        else
            $fromTable = '';
    
        return $table;
    }

    /**
     * Возвращает выражение WHERE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
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
        switch (strtolower($name)) {
            case 'where': return $this->where;
        }
        return null;
    }
}
