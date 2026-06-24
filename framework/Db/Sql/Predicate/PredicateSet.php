<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

use Closure;
use Countable;
use Ge\Db\Sql\Exception;

/**
 * Набор (комбинация) предикатов SQL инструкции.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class PredicateSet implements PredicateInterface, Countable
{
    public const COMBINED_BY_AND = 'AND';
    public const OP_AND          = 'AND';
    public const COMBINED_BY_OR  = 'OR';
    public const OP_OR           = 'OR';

    /**
     * Комбинация по умолчанию.
     * 
     * @var string
     */
    protected string $defaultCombination = self::COMBINED_BY_AND;

    /**
     * Предикаты.
     * 
     * @see PredicateSet::addPredicate()
     * 
     * @var array<int, PredicateInterface>
     */
    protected array $predicates = [];

    /**
     * Конструктор класса.
     *
     * @param array|null $predicates Предикаты (по умолчанию `null`).
     * @param string $defaultCombination Комбинация по умолчанию.
     */
    public function __construct(?array $predicates = null, string $defaultCombination = self::COMBINED_BY_AND)
    {
        $this->defaultCombination = $defaultCombination;
        if ($predicates) {
            foreach ($predicates as $predicate) {
                $this->addPredicate($predicate);
            }
        }
    }

    /**
     * Добавляет предикат.
     *
     * @param PredicateInterface $predicate Предикат.
     * @param string|null $combination Комбинация: 'OR', 'AND' (по умолчанию `null`).
     * 
     * @return $this
     */
    public function addPredicate(PredicateInterface $predicate, ?string $combination = null): static
    {
        if ($combination === null || !in_array($combination, array(self::OP_AND, self::OP_OR))) {
            $combination = $this->defaultCombination;
        }

        if ($combination == self::OP_OR) {
            $this->orPredicate($predicate);
            return $this;
        }

        $this->andPredicate($predicate);
        return $this;
    }

    /**
     * Добавляет предикаты.
     *
     * @param PredicateInterface|Closure|string|array<int, PredicateInterface> $predicates Предикаты.
     * @param string $combination
     * 
     * @return $this
     */
    public function addPredicates(
        PredicateInterface|Closure|string|array $predicates, 
        string $combination = self::OP_AND
    ): static
    {
        if ($predicates === null) {
            throw new Exception\InvalidArgumentException('Predicate cannot be null');
        }
        if ($predicates instanceof PredicateInterface) {
            $this->addPredicate($predicates, $combination);
            return $this;
        }
        if ($predicates instanceof \Closure) {
            $predicates($this);
            return $this;
        }
        if (is_string($predicates)) {
            // строка $predicate должна быть передана как выражение
            $predicates = (strpos($predicates, Expression::PLACEHOLDER) !== false)
                ? new Expression($predicates) : new Literal($predicates);
            $this->addPredicate($predicates, $combination);
            return $this;
        }
        if (is_array($predicates)) {
            foreach ($predicates as $pkey => $pvalue) {
                // перебираем предикаты
                if (is_string($pkey)) {
                    if (strpos($pkey, '?') !== false) {
                        $predicates = new Expression($pkey, $pvalue);
                    } elseif ($pvalue === null) {
                        $predicates = new IsNull($pkey);
                    } elseif (is_array($pvalue)) {
                        $predicates = new In($pkey, $pvalue);
                    } elseif ($pvalue instanceof PredicateInterface) {
                        throw new Exception\InvalidArgumentException(
                            'Using Predicate must not use string keys'
                        );
                    } else {
                        // в противном случае предположим, что array('foo' => 'bar') это "foo" = 'bar'
                        $predicates = new Operator($pkey, Operator::OP_EQ, $pvalue);
                    }
                } elseif ($pvalue instanceof PredicateInterface) {
                    $predicates = $pvalue;
                } else {
                    // должен быть массивом выражений (с массивом, индексированным int)
                    $predicates = (strpos($pvalue, Expression::PLACEHOLDER) !== false)
                        ? new Expression($pvalue) : new Literal($pvalue);
                }
                $this->addPredicate($predicates, $combination);
            }
        }
        return $this;
    }

    /**
     * Возвращает предикаты.
     *
     * @return array<int, PredicateInterface>
     */
    public function getPredicates(): array
    {
        return $this->predicates;
    }

    /**
     * Добавляет предикат использующий оператор OR.
     * 
     * @param PredicateInterface $predicate Предикат.
     * 
     * @return $this
     */
    public function orPredicate(PredicateInterface $predicate): static
    {
        $this->predicates[] = array(self::OP_OR, $predicate);
        return $this;
    }

    /**
     * Добавляет предикат использующий оператор AND.
     * 
     * @param PredicateInterface $predicate Предикат.
     * 
     * @return $this
     */
    public function andPredicate(PredicateInterface $predicate): static
    {
        $this->predicates[] = array(self::OP_AND, $predicate);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $parts = array();
        for ($i = 0, $count = count($this->predicates); $i < $count; $i++) {
            /** @var $predicate PredicateInterface */
            $predicate = $this->predicates[$i][1];

            if ($predicate instanceof PredicateSet) {
                $parts[] = '(';
            }

            $parts = array_merge($parts, $predicate->getExpressionData());

            if ($predicate instanceof PredicateSet) {
                $parts[] = ')';
            }

            if (isset($this->predicates[$i+1])) {
                $parts[] = sprintf(' %s ', $this->predicates[$i+1][0]);
            }
        }
        return $parts;
    }

    /**
     * Возвращает количество добавленных предикатов.
     *
     * @return int
     */
    public function count(): int
    {
        return sizeof($this->predicates);
    }
}
