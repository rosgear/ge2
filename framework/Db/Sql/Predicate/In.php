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
use Ge\Db\Sql\AbstractExpression;

/**
 * Предикат "In", как оператор условия для SQL инструкции "expression IN (value,...)".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class In extends AbstractExpression implements PredicateInterface
{
    /**
     * Спецификация оператора.
     * 
     * @var string
     */
    protected string $specification = '%s IN %s';

    /**
     * Идентификатор условия.
     * 
     * @var string|array|null
     */
    protected string|array|null $identifier = null;

    /**
     * Значение условия.
     * 
     * @var Select|array|null
     */
    protected Select|array|null $valueSet = null;

    /**
     * Спецификация набора значений оператора.
     * 
     * @var string|null
     */
    protected ?string $valueSpecSpecification = '%%s IN (%s)';

    /**
     * Конструктор класса.
     *
     * @param string|array|null $identifier Идентификатор условия.
     * @param Select|array|null $valueSet Значение условия.
     */
    public function __construct(string|array|null $identifier = null, Select|array|null $valueSet = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($valueSet) {
            $this->setValueSet($valueSet);
        }
    }

    /**
     * Устанавливает идентификатор сравнения.
     *
     * @param string|array $identifier Идентификатор.
     * 
     * @return $this
     */
    public function setIdentifier(string|array $identifier): static
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Возвращает идентификатор сравнения.
     *
     * @return string|array|null
     */
    public function getIdentifier(): string|array|null
    {
        return $this->identifier;
    }

    /**
     * Установить набор значений для сравнения IN.
     *
     * @param Select|array $valueSet Набор значений для сравнения.
     * 
     * @return $this
     */
    public function setValueSet(Select|array $valueSet): static
    {
        $this->valueSet = $valueSet;
        return $this;
    }

    /**
     * Возвращает набор значений для сравнения IN.
     *
     * @return Select|array|null
     */
    public function getValueSet(): Select|array|null
    {
        return $this->valueSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $identifier = $this->getIdentifier();
        $values = $this->getValueSet();
        $replacements = [];

        if (is_array($identifier)) {
            $identifierSpecFragment = '(' . implode(', ', array_fill(0, sizeof($identifier), '%s')) . ')';
            $types = array_fill(0, sizeof($identifier), self::TYPE_IDENTIFIER);
            $replacements = $identifier;
        } else {
            $identifierSpecFragment = '%s';
            $replacements[] = $identifier;
            $types = [self::TYPE_IDENTIFIER];
        }

        if ($values instanceof Select) {
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '%s']
            );
            $replacements[] = $values;
            $types[] = self::TYPE_VALUE;
        } else {
            foreach ($values as $argument) {
                list($replacements[], $types[]) = $this->normalizeArgument($argument, self::TYPE_VALUE);
            }
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '(' . implode(', ', array_fill(0, count($values), '%s')) . ')']
            );
        }

        return [
            [$specification, $replacements, $types]
        ];
    }
}
