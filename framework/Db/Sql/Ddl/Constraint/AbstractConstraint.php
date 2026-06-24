<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Constraint;

/**
 * Абстрактный класс ограничений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Constraint
 * @since 2.0
 */
abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * Спецификация столбца.
     * 
     * @var string
     */
    protected string $columnSpecification = ' (%s)';

    /**
     * Спецификация имени.
     * 
     * @var string
     */
    protected string $namedSpecification = 'CONSTRAINT %s ';

    /**
     * Спецификация.
     * 
     * @var string
     */
    protected string $specification = '';

    /**
     * Название.
     * 
     * @see AbstractConstraint::setName()
     * 
     * @var string
     */
    protected string $name = '';

    /**
     * Столбцы таблицы.
     * 
     * @see AbstractConstraint::setColumns()
     * 
     * @var array
     */
    protected array $columns = [];

    /**
     * Конструктор класса.
     * 
     * @param string|array $columns Столбцы таблицы.
     * @param null|string $name Название.
     */
    public function __construct(string|array $columns = [], string $name = '')
    {
        if ($columns) {
            $this->setColumns($columns);
        }
        $this->setName($name);
    }

    /**
     * Устанавливает название.
     * 
     * @param string $name Название.
     * 
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Возвращает название.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает столбцы.
     * 
     * @param string|array|null $columns Столбцы таблицы.
     * 
     * @return $this
     */
    public function setColumns(string|array $columns): static
    {
        $this->columns = (array) $columns;
        return $this;
    }

    /**
     * Добавляет столбец.
     * 
     * @param string $column Название столбца.
     * 
     * @return $this
     */
    public function addColumn(string $column): static
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * Возвращает столбцы таблицы.
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $colCount = sizeof($this->columns);
        $newSpecTypes = [];
        $values = [];
        $newSpec = '';

        if ($this->name) {
            $newSpec .= $this->namedSpecification;
            $values[] = $this->name;
            $newSpecTypes[] = self::TYPE_IDENTIFIER;
        }

        $newSpec .= $this->specification;

        if ($colCount) {
            $values = array_merge($values, $this->columns);
            $newSpecParts = array_fill(0, $colCount, '%s');
            $newSpecTypes = array_merge($newSpecTypes, array_fill(0, $colCount, self::TYPE_IDENTIFIER));
            $newSpec .= sprintf($this->columnSpecification, implode(', ', $newSpecParts));
        }

        return [
            [$newSpec, $values, $newSpecTypes]
        ];
    }
}
