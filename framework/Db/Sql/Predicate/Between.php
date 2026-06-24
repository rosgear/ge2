<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

use Ge\Db\Sql\AbstractExpression;

/**
 * Предикат "Between", как оператор сравнения для инструкции SQL "expression BETWEEN min AND max".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class Between extends AbstractExpression implements PredicateInterface
{
    /**
     * Спецификация оператора.
     * 
     * @var string
     */
    protected string $specification = '%1$s BETWEEN %2$s AND %3$s';

    /**
     * Идентификатор сравнения.
     * 
     * @var string|null
     */
    protected ?string $identifier = null;

    /**
     * Минимальное значение сравнения.
     * 
     * @var int|float|string|null
     */
    protected int|float|string|null $minValue = null;

    /**
     * Максимальное значение сравнения.
     * 
     * @var int|float|string|null
     */
    protected int|float|string|null $maxValue = null;

    /**
     * Конструктор класса.
     *
     * @param string|null $identifier Идентификатор.
     * @param int|float|string|null $minValue Минимальное значение сравнения.
     * @param int|float|string|null $maxValue Максимальное значение сравнения.
     */
    public function __construct(
        ?string $identifier = null, 
        int|float|string|null $minValue = null, 
        int|float|string|null $maxValue = null
    )
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($minValue !== null) {
            $this->setMinValue($minValue);
        }
        if ($maxValue !== null) {
            $this->setMaxValue($maxValue);
        }
    }

    /**
     * Устанавливает идентификатор сравнения.
     *
     * @param string $identifier Идентификатор.
     * 
     * @return $this
     */
    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Возвращает идентификатор сравнения.
     *
     * @return null|string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Устанавливает минимальное значение для сравнения.
     *
     * @param int|float|string $minValue Минимальное значение для сравнения.
     * 
     * @return $this
     */
    public function setMinValue(int|float|string $minValue): static
    {
        $this->minValue = $minValue;
        return $this;
    }

    /**
     * Возвращает минимальное значение для сравнения.
     *
     * @return null|int|float|string
     */
    public function getMinValue(): null|int|float|string
    {
        return $this->minValue;
    }

    /**
     * Устанавливает максимальное значение для сравнения.
     *
     * @param int|float|string $maxValue Минимальное значение для сравнения.
     * 
     * @return $this
     */
    public function setMaxValue(int|float|string $maxValue): static
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    /**
     * Возвращает максимальнон значение для сравнения.
     *
     * @return null|int|float|string
     */
    public function getMaxValue(): null|int|float|string
    {
        return $this->maxValue;
    }

    /**
     * Устанавливает спецификацию, которая будет использоваться при формировании 
     * предиката SQL.
     *
     * @param string $specification Cпецификация.
     * 
     * @return $this
     */
    public function setSpecification(string $specification): static
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * Получить спецификацию для использования при формировании предиката SQL.
     *
     * @return string
     */
    public function getSpecification(): string
    {
        return $this->specification;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        list($values[], $types[]) = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        list($values[], $types[]) = $this->normalizeArgument($this->minValue,   self::TYPE_VALUE);
        list($values[], $types[]) = $this->normalizeArgument($this->maxValue,   self::TYPE_VALUE);
        return [
            [$this->getSpecification(), $values, $types]
        ];
    }
}
