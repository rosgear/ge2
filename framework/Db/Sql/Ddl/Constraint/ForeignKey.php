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
 * Класс внешнего ключа FOREIGN KEY.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Db\Sql\Ddl\Constraint
 * @since 2.0
 */
class ForeignKey extends AbstractConstraint
{
    /**
     * Правило при удалении.
     * 
     * @see ForeignKey::setOnDeleteRule()
     * 
     * @var string
     */
    protected string $onDeleteRule = 'NO ACTION';

    /**
     * Правило при обновлении.
     * 
     * @see ForeignKey::setOnUpdateRule()
     * 
     * @var string
     */
    protected string $onUpdateRule = 'NO ACTION';

    /**
     * Ссылочные столбцы.
     * 
     * @see ForeignKey::setReferenceColumn()
     * 
     * @var array<int, string>
     */
    protected array $referenceColumn = [];

    /**
     * Ссылочная таблица.
     * 
     * @see ForeignKey::setReferenceTable()
     * 
     * @var string
     */
    protected string $referenceTable = '';

    /**
     * Спецификация REFERENCES.
     * 
     * @var array<int, string>
     */
    protected array $referenceSpecification = [
        'REFERENCES %s ',
        'ON DELETE %s ON UPDATE %s'
    ];

    /**
     * {@inheritdoc}
     */
    protected string $columnSpecification = 'FOREIGN KEY (%s) ';

    /**
     * Конструктор класса.
     * 
     * @param string|null $name Название.
     * @param string|array|null $columns Столбцы таблицы.
     * @param string $referenceTable Ссылочная таблица.
     * @param string|array|null $referenceColumn Ссылочные столбцы таблицы.
     * @param string|null $onDeleteRule Правило удаления.
     * @param string|null $onUpdateRule Правило обновления.
     */
    public function __construct(
        ?string $name, 
        string|array|null $columns, 
        string $referenceTable, 
        string|array|null $referenceColumn, 
        ?string $onDeleteRule = null, 
        ?string $onUpdateRule = null
    )
    {
        if ($name) $this->setName($name);
        if ($columns) $this->setColumns($columns);
        if ($referenceColumn) $this->setReferenceColumn($referenceColumn);

        $this->setReferenceTable($referenceTable);

        if ($onDeleteRule) $this->setOnDeleteRule($onDeleteRule);
        if ($onUpdateRule) $this->setOnUpdateRule($onUpdateRule);
    }

    /**
     * Устанавливает ссылочную таблицу.
     * 
     * @param string $referenceTable Ссылочная таблица.
     * 
     * @return $this
     */
    public function setReferenceTable(string $referenceTable): static
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * Возвращает ссылочную таблицу.
     * 
     * @return string
     */
    public function getReferenceTable(): string
    {
        return $this->referenceTable;
    }

    /**
     * Устанавливает ссылочный столбец.
     * 
     * @param string|array $referenceColumn Ссылочный столбец.
     * 
     * @return $this
     */
    public function setReferenceColumn(string|array $referenceColumn): static
    {
        $this->referenceColumn = (array) $referenceColumn;
        return $this;
    }

    /**
     * Возвращает ссылочный столбец.
     * 
     * @return array
     */
    public function getReferenceColumn(): array
    {
        return $this->referenceColumn;
    }

    /**
     * Устанавливает правило при удалении.
     * 
     * @param string $onDeleteRule Правило.
     * 
     * @return $this
     */
    public function setOnDeleteRule(string $onDeleteRule): static
    {
        $this->onDeleteRule = $onDeleteRule;
        return $this;
    }

    /**
     * Возвращает правило при удалении.
     * 
     * @return string
     */
    public function getOnDeleteRule(): string
    {
        return $this->onDeleteRule;
    }

    /**
     * Устанавливает правило при обновлении.
     * 
     * @param string $onUpdateRule Правило.
     * 
     * @return $this
     */
    public function setOnUpdateRule(string $onUpdateRule): static
    {
        $this->onUpdateRule = $onUpdateRule;
        return $this;
    }

    /**
     * Возвращает правило при обновлении.
     * 
     * @return string
     */
    public function getOnUpdateRule(): string
    {
        return $this->onUpdateRule;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $data         = parent::getExpressionData();
        $colCount     = count($this->referenceColumn);
        $newSpecTypes = [self::TYPE_IDENTIFIER];
        $values       = [$this->referenceTable];

        $data[0][0] .= $this->referenceSpecification[0];

        if ($colCount) {
            $values       = array_merge($values, $this->referenceColumn);
            $newSpecParts = array_fill(0, $colCount, '%s');
            $newSpecTypes = array_merge($newSpecTypes, array_fill(0, $colCount, self::TYPE_IDENTIFIER));

            $data[0][0] .= sprintf('(%s) ', implode(', ', $newSpecParts));
        }

        $data[0][0] .= $this->referenceSpecification[1];

        $values[]       = $this->onDeleteRule;
        $values[]       = $this->onUpdateRule;
        $newSpecTypes[] = self::TYPE_LITERAL;
        $newSpecTypes[] = self::TYPE_LITERAL;

        $data[0][1] = array_merge($data[0][1], $values);
        $data[0][2] = array_merge($data[0][2], $newSpecTypes);
        return $data;
    }
}
