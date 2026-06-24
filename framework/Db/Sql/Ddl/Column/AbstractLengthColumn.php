<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Column;

/**
 * Абстрактный класс размера столбца таблицы.
 * 
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
abstract class AbstractLengthColumn extends Column
{
    /**
     * Размера столбца.
     * 
     * @see AbstractLengthColumn::__construct()
     * 
     * @var int
     */
    protected int $length;

    /**
     * {@inheritdoc}
     *
     * @param int $length Размера столбца.
     */
    public function __construct(string $name = '', int $length = 0, bool $nullable = false, mixed $default = null, array $options = [])
    {
        $this->setLength($length);

        parent::__construct($name, $nullable, $default, $options);
    }

    /**
     * Устанавливает размер столбца.
     * 
     * @param int $length Размер столбца.
     *
     * @return $this
     */
    public function setLength(int $length): static
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Возвращает размер столбца.
     * 
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Возвращает размер столбца для выражения.
     * 
     * @return string
     */
    protected function getLengthExpression(): string
    {
        return (string) $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $data = parent::getExpressionData();

        if ($this->getLengthExpression()) {
            $data[0][1][1] .= '(' . $this->getLengthExpression() . ')';
        }
        return $data;
    }
}
