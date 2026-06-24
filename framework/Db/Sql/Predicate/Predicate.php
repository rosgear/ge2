<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

use Ge\Db\Sql\Select;
use Ge\Db\Sql\Exception\RuntimeException;

/**
 * Класс предиката инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class Predicate extends PredicateSet
{
    /**
     * Предикат, который будет не вложенный.
     * 
     * @see Predicate::setUnnest()
     * 
     * @var Predicate|null
     */
    protected ?Predicate $unnest = null;

    /**
     * Комбинация ('OR', 'AND') для следующего вложения предиката.
     * 
     * @see Predicate::nest()
     * 
     * @var string|null
     */
    protected ?string $nextPredicateCombineOperator = null;

    /**
     * Начать вложение предикатов.
     *
     * @return $this
     */
    public function nest(): static
    {
        $predicateSet = new Predicate();
        $predicateSet->setUnnest($this);
        $this->addPredicate($predicateSet, ($this->nextPredicateCombineOperator) ?: $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;
        return $predicateSet;
    }

    /**
     * Указать, какой предикат будет невложенным.
     *
     * @param Predicate $predicate Предикат.
     * 
     * @return void
     */
    public function setUnnest(Predicate $predicate): void
    {
        $this->unnest = $predicate;
    }

    /**
     * Указать конец вложенного предиката.
     *
     * @return $this
     * 
     * @throws RuntimeException
     */
    public function unnest(): static
    {
        if ($this->unnest === null) {
            throw new RuntimeException('Not nested');
        }
        $unnest = $this->unnest;
        $this->unnest = null;
        return $unnest;
    }

    /**
     * Создаёт предикат "Эквивалент".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function equalTo(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Не эквивалент".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function notEqualTo(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_NOT_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Меньше чем".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function lessThan(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_LESS_THAN, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Больше чем".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function greaterThan(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_GREATER_THAN, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Меньше чем или равно".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function lessThanOrEqualTo(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Больше чем или равно".
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param string $leftType Тип параметра для левой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны предиката: `Predicate::TYPE_IDENTIFIER`, 
     *     `Predicate::TYPE_VALUE` {@see Predicate::$allowedTypes} (по умолчанию `Predicate::TYPE_VALUE`).
     * 
     * @return $this
     */
    public function greaterThanOrEqualTo(
        int|float|bool|string $left, 
        int|float|bool|string $right, 
        string $leftType = self::TYPE_IDENTIFIER, 
        string $rightType = self::TYPE_VALUE
    ): static
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Like".
     *
     * @param string $identifier Идентификатор сравнения.
     * @param string $like Значение для сравнения.
     * 
     * @return $this
     */
    public function like(string $identifier, string $like): static
    {
        $this->addPredicate(
            new Like($identifier, $like),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "notLike".
     *
     * @param string $identifier Идентификатор сравнения.
     * @param string $notLike Значение для сравнения.
     * 
     * @return $this
     */
    public function notLike(string $identifier, string $notLike): static
    {
        $this->addPredicate(
            new NotLike($identifier, $notLike),
            ($this->nextPredicateCombineOperator) ? : $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;
        return $this;
    }

    /**
     * Создаёт выражение с параметрами.
     *
     * @param string $expression Выражение.
     * @param mixed $parameters Параметры выражения.
     * 
     * @return $this
     */
    public function expression(string $expression, mixed $parameters): static
    {
        $this->addPredicate(
            new Expression($expression, $parameters),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "Literal".
     *
     * @param string $literal Значение для сравнения.
     * 
     * @return $this
     */
    public function literal(string $literal): static
    {
        // обрабатывать устаревшие параметры из предыдущих `literal($literal, $parameters = null)`
        if (func_num_args() >= 2) {
            $parameters = func_get_arg(1);
            $predicate = new Expression($literal, $parameters);
        }

        if (!isset($predicate)) {
            $predicate = new Literal($literal);
        }

        $this->addPredicate(
            $predicate,
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "IS NULL".
     *
     * @param string $identifier Идентификатор cравнения.
     * 
     * @return $this
     */
    public function isNull(string $identifier): static
    {
        $this->addPredicate(
            new IsNull($identifier),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "IS NOT NULL".
     *
     * @param string $identifier Идентификатор cравнения.
     * 
     * @return $this
     */
    public function isNotNull(string $identifier): static
    {
        $this->addPredicate(
            new IsNotNull($identifier),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "IN".
     *
     * @param string $identifier Идентификатор cравнения.
     * @param Select|array|null $valueSet Набор значений для сравнения (по умолчанию `null`).
     * 
     * @return $this
     */
    public function in(string $identifier, Select|array|null $valueSet = null): static
    {
        $this->addPredicate(
            new In($identifier, $valueSet),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Создаёт предикат "NOT IN".
     *
     * @param string $identifier Идентификатор cравнения.
     * @param Select|array|null $valueSet Набор значений для сравнения (по умолчанию `null`).
     * 
     * @return $this
     */
    public function notIn(string $identifier, Select|array|null $valueSet = null): static
    {
        $this->addPredicate(
            new NotIn($identifier, $valueSet),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     *  Создаёт предикат "BETWEEN".
     *
     * @param string $identifier Идентификатор cравнения.
     * @param int|float|string $minValue Максимальное значение для cравнения.
     * @param int|float|string $maxValue Минимальное значение для cравнения.
     * 
     * @return $this
     */
    public function between(string $identifier, int|float|string $minValue, int|float|string $maxValue): static
    {
        $this->addPredicate(
            new Between($identifier, $minValue, $maxValue),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;
        return $this;
    }

    /**
     * Использовать данный предикат напрямую
     *
     * В отличие от {@link Predicate::addPredicate()}, этот метод учитывает ранее установленный 
     * оператор комбинации AND / OR, что позволяет свободно использовать общие предикаты 
     * внутри цепочек, как и любой другой конкретный предикат.
     *
     * @param PredicateInterface $predicate Предикат.
     * 
     * @return $this
     */
    public function predicate(PredicateInterface $predicate): static
    {
        $this->addPredicate(
            $predicate,
            $this->nextPredicateCombineOperator ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;
        return $this;
    }

    /**
     * Чтении значения из несуществуюшего свойства. 
     *
     * Для свойств: "or", "and", "nest", "unnest".
     *
     * @param string $name
     * 
     * @return $this
     */
    public function __get(string $name): static
    {
        switch (strtolower($name)) {
            case 'or':
                $this->nextPredicateCombineOperator = self::OP_OR;
                break;

            case 'and':
                $this->nextPredicateCombineOperator = self::OP_AND;
                break;

            case 'nest': return $this->nest();

            case 'unnest': return $this->unnest();
        }
        return $this;
    }
}
