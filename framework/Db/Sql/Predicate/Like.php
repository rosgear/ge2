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
 * Предикат "Like", как оператор сравнения для инструкции SQL "expression LIKE pattern [ESCAPE 'escape_char']".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class Like extends AbstractExpression implements PredicateInterface
{
    /**
     * Спецификация оператора.
     * 
     * @var string
     */
    protected string $specification = '%1$s LIKE %2$s';

    /**
     * Идентификатор сравнения.
     * 
     * @var string
     */
    protected string $identifier = '';

    /**
     * Значение для сравнения.
     * 
     * @var string
     */
    protected string $like = '';

    /**
     * @param string $identifier Идентификатор сравнения.
     * @param string $like Значение для сравнения.
     */
    public function __construct(string $identifier = '', string $like = '')
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($like) {
            $this->setLike($like);
        }
    }

    /**
     * Устанавливает идентификатор для сравнения.
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
     * Возвращает идентификатор для сравнения.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Устанавливает значение для сравнения.
     *
     * @param string $like Значение для сравнения.
     * 
     * @return $this
     */
    public function setLike(string $like): static
    {
        $this->like = $like;
        return $this;
    }

    /**
     * Возвращает значение для сравнения.
     * 
     * @return string
     */
    public function getLike(): string
    {
        return $this->like;
    }

    /**
     * Устанавляет строку спецификации, которая будет использоваться при формировании 
     * предиката SQL.
     *
     * @param string $specification Строка спецификации.
     * 
     * @return $this
     */
    public function setSpecification(string $specification): static
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * Возвращает строку спецификации, которая будет использоваться при формировании 
     * предиката SQL.
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
        list($values[], $types[]) = $this->normalizeArgument($this->like, self::TYPE_VALUE);
        return [
            [$this->specification, $values, $types]
        ];
    }
}
