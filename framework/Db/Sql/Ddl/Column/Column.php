<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Column;

use Ge\Db\Sql\Ddl\Constraint\ConstraintInterface;

/**
 * Класс столбца таблицы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
class Column implements ColumnInterface
{
    /**
     * Значение по умолчанию столбца.
     * 
     * @see Column::setDefault()
     * 
     * @var mixed
     */
    protected mixed $default = null;

    /**
     * Столбец может иметь значение NULL.
     * 
     * @see Column::setNullable()
     * 
     * @var bool
     */
    protected bool $isNullable = false;

    /**
     * Название столбца.
     * 
     * @see Column::setName()
     * 
     * @var string
     */
    protected string $name = '';

    /**
     * Параметры столбца.
     * 
     * @see Column::setOptions()
     * 
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * Ограничения.
     * 
     * @see Column::addConstraint()
     * 
     * @var array<int, ConstraintInterface>
     */
    protected array $constraints = [];

    /**
     * Спецификация.
     * 
     * @var string
     */
    protected string $specification = '%s %s';

    /**
     * Тип столбца.
     * 
     * @var string
     */
    protected string $type = 'INTEGER';

    /**
     * Конструктор класса.
     * 
     * @param string $name Имя столбца.
     * @param bool $nullable  (по умолчанию `[]`)
     * @param mixed $default Значение по умолчанию (по умолчанию '')
     * @param array<int, mixed> $options Параметры столбца (по умолчанию `[]`).
     */
    public function __construct(string $name = '', bool $nullable = false, mixed $default = null, array $options = [])
    {
        $this->setName($name);
        $this->setNullable($nullable);
        $this->setDefault($default);
        $this->setOptions($options);
    }

    /**
     * Устанавливает имя столбца.
     * 
     * @param string $name Имя столбца.
     * 
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Возвращает имя столбца.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает возможность столбца иметь значение NULL.
     * 
     * @param bool $nullable Возможность столбца иметь значение NULL.
     * 
     * @return $this
     */
    public function setNullable(bool $nullable): static
    {
        $this->isNullable = $nullable;
        return $this;
    }

    /**
     * Возвращает возможность столбца иметь значение NULL.
     * 
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * Устанавливает значение столбца по умолчанию.
     * 
     * @param mixed
     * 
     * @return $this
     */
    public function setDefault(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Возвращает значение столбца по умолчанию.
     * 
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Устанавливает параметры столбца.
     * 
     * @param array $options Параметры столбца.
     * 
     * @return $this
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Устанавливает параметр столбца.
     * 
     * @param string $name Параметр.
     * @param mixed $value Значение параметра.
     * 
     * @return $this
     */
    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Возвращает параметры столбца.
     * 
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Добавляет ограничение.
     * 
     * @param ConstraintInterface $constraint Ограничение.
     *
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint): static
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $spec   = $this->specification;
        $params = [$this->name, $this->type];
        $types  = [self::TYPE_IDENTIFIER, self::TYPE_LITERAL];

        if (!$this->isNullable) {
            $spec .= ' NOT NULL';
        }

        if ($this->default !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = $this->default;
            $types[]  = self::TYPE_VALUE;
        }

        $data = [[$spec, $params, $types]];

        foreach ($this->constraints as $constraint) {
            $data[] = ' ';
            $data = array_merge($data, $constraint->getExpressionData());
        }
        return $data;
    }
}
