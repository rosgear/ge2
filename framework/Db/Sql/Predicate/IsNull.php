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
 * Предикат "IsNull", как условие для инструкции SQL "expression IS NULL".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class IsNull extends AbstractExpression implements PredicateInterface
{
    /**
     * Спецификация оператора.
     * 
     * @var string
     */
    protected string $specification = '%1$s IS NULL';

    /**
     * Идентификатор сравнения.
     * 
     * @var string|null
     */
    protected ?string $identifier = null;

    /**
     * Кострутор класса.
     *
     * @param string $identifier Идентификатор.
     */
    public function __construct(?string $identifier = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
    }

    /**
     * Установливает идентификатор для сравнения.
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
     * @return null|string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
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
        $identifier = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        return [
            [
                $this->getSpecification(),
                [$identifier[0]],
                [$identifier[1]],
            ]
        ];
    }
}
