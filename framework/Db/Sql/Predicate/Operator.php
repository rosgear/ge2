<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

use Ge\Db\Sql\Exception;
use Ge\Db\Sql\AbstractExpression;

/**
 * Предикат "Operator" (оператор) инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class Operator extends AbstractExpression implements PredicateInterface
{
    public const OPERATOR_EQUAL_TO                  = '=';
    public const OP_EQ                              = '=';

    public const OPERATOR_NOT_EQUAL_TO              = '!=';
    public const OP_NE                              = '!=';

    public const OPERATOR_LESS_THAN                 = '<';
    public const OP_LT                              = '<';

    public const OPERATOR_LESS_THAN_OR_EQUAL_TO     = '<=';
    public const OP_LTE                             = '<=';

    public const OPERATOR_GREATER_THAN              = '>';
    public const OP_GT                              = '>';

    public const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = '>=';
    public const OP_GTE                             = '>=';

    /**
     * {@inheritdoc}
     */
    protected $allowedTypes  = [
        self::TYPE_IDENTIFIER,
        self::TYPE_VALUE,
    ];

    /**
     * Левая сторона оператора.
     * 
     * @see Operator::setLeft()
     * 
     * @var int|float|bool|string|null
     */
    protected int|float|bool|string|null $left = null;

    /**
     * Правая сторона оператора.
     * 
     * @see Operator::setRight()
     * 
     * @var int|float|bool|string|null
     */
    protected int|float|bool|string|null $right = null;

    /**
     * Тип параметра для левой стороны оператора.
     * 
     * @see Operator::setLeftType()
     * 
     * @var string
     */
    protected string $leftType = self::TYPE_IDENTIFIER;

    /**
     * Тип параметра для правой стороны оператора.
     * 
     * @see Operator::setRightType()
     * 
     * @var string
     */
    protected string $rightType = self::TYPE_VALUE;

    /**
     * Оператор.
     * 
     * @see Operator::setOperator()
     * 
     * @var string
     */
    protected string $operator = self::OPERATOR_EQUAL_TO;

    /**
     * Конструктор класса.
     *
     * @param mixed $left Левая сторона оператора.
     * @param string $operator Оператор, одна из констант `OP_*` (по умолчанию `Operator::OPERATOR_EQUAL_TO`).
     * @param mixed $right Правая сторона оператора.
     * @param string $leftType Тип параметра для левой стороны оператора: `Operator::TYPE_IDENTIFIER`, 
     *     `Operator::TYPE_VALUE` {@see Operator::$allowedTypes} (по умолчанию `Operator::TYPE_IDENTIFIER`).
     * @param string $rightType Тип параметра для правой стороны оператора: `Operator::TYPE_IDENTIFIER`, 
     *     `Operator::TYPE_VALUE` {@see Operator::$allowedTypes} (по умолчанию `Operator::TYPE_VALUE`).
     */
    public function __construct(
        mixed $left = null,
        string $operator = self::OPERATOR_EQUAL_TO,
        mixed $right = null,
        string $leftType = self::TYPE_IDENTIFIER,
        string $rightType = self::TYPE_VALUE
    ) {
        if ($left !== null) {
            $this->setLeft($left);
        }

        if ($operator !== self::OPERATOR_EQUAL_TO) {
            $this->setOperator($operator);
        }

        if ($right !== null) {
            $this->setRight($right);
        }

        if ($leftType !== self::TYPE_IDENTIFIER) {
            $this->setLeftType($leftType);
        }

        if ($rightType !== self::TYPE_VALUE) {
            $this->setRightType($rightType);
        }
    }

    /**
     * Устанавливает левую сторону оператора.
     *
     * @param mixed $left Левая сторону оператора.
     *
     * @return $this
     */
    public function setLeft(mixed $left): static
    {
        $this->left = $left;

        if (is_array($left) || is_object($left)) {
            $left = $this->normalizeArgument($left, $this->leftType);
            $this->leftType = $left[1];
        }
        return $this;
    }

    /**
     * Возвращает левую сторону оператора.
     *
     * @return int|float|bool|string|null
     */
    public function getLeft(): int|float|bool|string|null
    {
        return $this->left;
    }

    /**
     * Устанавливает тип параметра для левой стороны оператора.
     *
     * @param string $type Тип параметра: `TYPE_IDENTIFIER`, `TYPE_VALUE` {@see Operator::$allowedTypes}.
     *
     * @return $this
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setLeftType(string $type): static
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->leftType = $type;
        return $this;
    }

    /**
     * Возвращает тип параметра для левой стороны оператора.
     *
     * @return string
     */
    public function getLeftType(): string
    {
        return $this->leftType;
    }

    /**
     * Устанавливает оператора.
     *
     * @param string $operator Оператор.
     * 
     * @return $this
     */
    public function setOperator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Возвращает оператора.
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Устанавливает правую сторону оператора.
     *
     * @param mixed $right Правая сторону оператора.
     *
     * @return $this
     */
    public function setRight(mixed $right): static
    {
        $this->right = $right;
        if (is_array($right) || is_object($right)) {
            $right = $this->normalizeArgument($right, $this->rightType);
            $this->rightType = $right[1];
        }
        return $this;
    }

    /**
     * Возвращает правую сторону оператора.
     *
     * @return int|float|bool|string|null
     */
    public function getRight(): int|float|bool|string|null
    {
        return $this->right;
    }

    /**
     * Устанавливает тип параметра для правой стороны оператора.
     *
     * @param  string $type Тип параметра: `TYPE_IDENTIFIER`, `TYPE_VALUE` {@see Operator::$allowedTypes}.
     *
     * @return $this
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setRightType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->rightType = $type;
        return $this;
    }

    /**
     * Возвращает тип параметра для правой стороны оператора.
     *
     * @return string
     */
    public function getRightType(): string
    {
        return $this->rightType;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        list($values[], $types[]) = $this->normalizeArgument($this->left, $this->leftType);
        list($values[], $types[]) = $this->normalizeArgument($this->right, $this->rightType);
        return [
            [
                '%s ' . $this->operator . ' %s',
                $values,
                $types
            ]
        ];
    }
}
