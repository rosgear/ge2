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
use Ge\Db\Adapter\Platform\PlatformInterface;
use Ge\Db\Adapter\Driver\DriverInterface;
use Ge\Db\Adapter\ParameterContainer;

/**
 * Абстрактный класс инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
abstract class AbstractSql
{
    /**
     * Спецификации для генерации строк SQL инструкции.
     *
     * @var array<string, string>
     */
    protected array $specifications = [];

    /**
     * Информация для формирования имён.
     * 
     * @var array
     */
    protected array $processInfo = ['paramPrefix' => '', 'subselectCount' => 0];

    /**
     * Счётчик использования параметров контейнера с приставкой.
     * 
     * @see AbstractSql::processExpression()
     * 
     * @var array<string, int>
     */
    protected array $instanceParameterIndex = [];

    /**
     * Возвращение имя таблицы с псевдонимом.
     * 
     * @param string $table Имя таблицы.
     * @param string $alias Псевдоним (по умолчанию '').
     * 
     * @return string
     */
    protected function renderTable(string $table, string $alias = ''): string
    {
        return $table . ' ' . $alias;
    }

    /**
     * Возвращает инструкцию SQL.
     * 
     * @see AbstractSql::buildSqlString()
     * 
     * @param null|PlatformInterface $platform Платформа адаптера.
     * 
     * @return string
     */
    public function getSqlString(?PlatformInterface $platform = null): string
    {
        return $this->buildSqlString($platform);
    }

    /**
     * Создаёт инструкцию SQL.
     * 
     * @param PlatformInterface $platform Платформы адаптера.
     * @param DriverInterface|null $driver Драйвера подключения.
     * @param ParameterContainer|null $parameterContainer Контейнер параметров.
     * 
     * @return string
     */
    protected function buildSqlString(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ): string 
    {
        $sqls       = [];
        $parameters = [];
        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}(
                $platform,
                $driver,
                $parameterContainer,
                $sqls,
                $parameters
            );

            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);

                continue;
            }

            if (is_string($parameters[$name])) {
                $sqls[$name] = $parameters[$name];
            }
        }
        return rtrim(implode(' ', $sqls), "\n ,");
    }

    /**
     * Возвращает инструкции SQL для подзапроса оператора Select.
     * 
     * @param Select $subselect Оператор Select (выборки данных) инструкции SQL.
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     */
    protected function processSubSelect(
        Select $subselect,
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ): string 
    {
        $decorator = $subselect;
        return $decorator->buildSqlString($platform, $driver, $parameterContainer);
    }

    /**
     * Возвращает выражение инструкции SQL.
     * 
     * @param ExpressionInterface $expression Выражение.
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * @param string|null $namedParameterPrefix Приставка к именам параметров контейнера
     *     (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    protected function processExpression(
        ExpressionInterface $expression,
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null,
        ?string $namedParameterPrefix = null
    ): string {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;
        // статический счетчик для количества раз, когда этот метод был вызван во время выполнения PHP
        static $runtimeExpressionPrefix = 0;

        if ($parameterContainer && ((!is_string($namedParameterPrefix) || $namedParameterPrefix == ''))) {
            $namedParameterPrefix = sprintf('expr%04dParam', ++$runtimeExpressionPrefix);
        } else {
            if ($namedParameterPrefix) {
                $namedParameterPrefix = preg_replace('/\s/', '__', $namedParameterPrefix);
            }
        }

        $sql = '';

        // инициализировать переменные
        $parts = $expression->getExpressionData();
        //
        if (!isset($this->instanceParameterIndex)) {
            $this->instanceParameterIndex = [];
        }
        if (!isset($this->instanceParameterIndex[$namedParameterPrefix])) {
            $this->instanceParameterIndex[$namedParameterPrefix] = 1;
        }

        $expressionParamIndex = &$this->instanceParameterIndex[$namedParameterPrefix];

        foreach ($parts as $part) {
            // #7407: use $expression->getExpression() to get the unescaped
            // Версия выражения
            if (is_string($part) && $expression instanceof Expression) {
                $sql .= $expression->getExpression();
                continue;
            }

            // Если это строка, просто привяжите ее к возвращаемому sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $part;
                continue;
            }

            if (! is_array($part)) {
                throw new Exception\RuntimeException(
                    Ge::t('app', 'Elements returned from getExpressionData() array must be a string or array')
                );
            }

            // Значения и типы процесса (средняя и последняя позиция данных выражения)
            $values = $part[1];
            $types = isset($part[2]) ? $part[2] : array();
            foreach ($values as $vIndex => $value) {
                if (!isset($types[$vIndex])) {
                    continue;
                }
                $type = $types[$vIndex];
                if ($value instanceof Select) {
                    // процес sub-select
                    $values[$vIndex] = '('
                        . $this->processSubSelect($value, $platform, $driver, $parameterContainer)
                        . ')';
                } elseif ($value instanceof ExpressionInterface) {
                    // рекурсивный вызов для удовлетворения вложенных выражений
                    $values[$vIndex] = $this->processExpression(
                        $value,
                        $platform,
                        $driver,
                        $parameterContainer,
                        $namedParameterPrefix . $vIndex . 'subpart'
                    );
                } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
                    $values[$vIndex] = $platform->quoteIdentifierInFragment($value);
                } elseif ($type == ExpressionInterface::TYPE_VALUE) {
                    // если параметр prepareType установлен, это означает, что это конкретное значение должно
                    // вернуться к заявлению в том случаи если оно возжмно используется к placeholder
                    if ($parameterContainer) {
                        $name = $namedParameterPrefix . $expressionParamIndex++;
                        $parameterContainer->offsetSet($name, $value);
                        $values[$vIndex] = $driver->formatParameterName($name);
                        continue;
                    }

                    // если не подготовленное заявление, просто указываем значение и переходим
                    $values[$vIndex] = $platform->quoteValue($value);
                } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
                    $values[$vIndex] = $value;
                }
            }

            // После циклизации значений интерполируем их в строку sql
            // (они могут быть placeholders или значениями)
            $sql .= vsprintf($part[0], $values);
        }
        return $sql;
    }

    /**
     * Создаёт инструкцию SQL из спецификации и указанных параметров.
     * 
     * @param string|array $specifications Спецификация.
     * @param string|array $parameters Параметры.
     * 
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters(string|array $specifications, array $parameters): string
    {
        if (is_string($specifications)) {
            if (empty($parameters)) return '';

            return vsprintf($specifications, $parameters);
        }

        $parametersCount = sizeof($parameters);

        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == sizeof($paramSpecs)) {
                break;
            }

            unset($specificationString, $paramSpecs);
        }

        if (!isset($specificationString)) {
            throw new Exception\RuntimeException(Ge::t('app', 
                'A number of parameters was found that is not supported by this specification'
            ));
        }

        $topParameters = [];
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = [];

                foreach ($paramsForPosition as $multiParamsForPosition) {
                    
                    $ppCount = count($multiParamsForPosition);
                    if (!isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException(Ge::t('app',
                            "A number of parameters ({0}) was found that is not supported by this specification", [$ppCount]
                        ));
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (!isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException(Ge::t('app',
                        'A number of parameters ({0}) was found that is not supported by this specification', [$ppCount]
                    ));
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }
        return vsprintf($specificationString, $topParameters);
    }

   /**
     * Получает значение столбца.
     * 
     * @param ExpressionInterface|Select|array|int|string|null $column Столбец.
     * @param PlatformInterface $platform Платформы адаптера.
     * @param null|DriverInterface $driver Драйвера подключения (по умолчанию `null`).
     * @param null|ParameterContainer $parameterContainer (по умолчанию `null`).
     * @param null|string $namedParameterPrefix (по умолчанию `null`).
     * 
     * @return string
     */
    protected function resolveColumnValue(
        mixed $column,
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null,
        ?string $namedParameterPrefix = null
    ): string 
    {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;
        $isIdentifier = false;
        $fromTable = '';
        if (is_array($column)) {
            if (isset($column['isIdentifier'])) {
                $isIdentifier = (bool) $column['isIdentifier'];
            }
            if (isset($column['fromTable']) && $column['fromTable'] !== null) {
                $fromTable = $column['fromTable'];
            }
            if (isset($column['column'])) {
                $column = $column['column'];
            }            
        }

        if ($column instanceof ExpressionInterface) {
            return $this->processExpression($column, $platform, $driver, $parameterContainer, $namedParameterPrefix);
        }
        if ($column instanceof Select) {
            return '(' . $this->processSubSelect($column, $platform, $driver, $parameterContainer) . ')';
        }
        if ($column === null) {
            return 'NULL';
        }
        return $isIdentifier
                ? $fromTable . $platform->quoteIdentifierInFragment($column)
                : $platform->quoteValue($column);
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
        $schema = null;
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        if ($table instanceof Select) {
            $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
        } elseif ($table) {
            $table = $platform->quoteIdentifier($table);
        }

        if ($schema && $table) {
            $table = $platform->quoteIdentifier($schema) . $platform->getIdentifierSeparator() . $table;
        }
        return $table;
    }
}
