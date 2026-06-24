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
 * Абстрактный класс точности значений столбца таблицы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
abstract class AbstractPrecisionColumn extends AbstractLengthColumn
{
    /**
     * Количество цифр.
     * 
     * @see AbstractPrecisionColumn::setDecimal()
     * 
     * @var int
     */
    protected int $decimal;

    /**
     * {@inheritdoc}
     *
     * @param int $decimal Количество знаков после запятой.
     * @param int $digits Количество цифр.
     */
    public function __construct(
        string $name = '', 
        int $digits = 0, 
        int $decimal = 0, 
        bool $nullable = false, 
        mixed $default = null, 
        array $options = []
    ) {
        $this->setDecimal($decimal);

        parent::__construct($name, $digits, $nullable, $default, $options);
    }

    /**
     * Устанавливает количество знаков после запятой.
     * 
     * @param int $digits Количество знаков после запятой.
     *
     * @return $this
     */
    public function setDigits(int $digits): static
    {
        return $this->setLength($digits);
    }

    /**
     * Возвращает количество знаков после запятой.
     * 
     * @return int
     */
    public function getDigits(): int
    {
        return $this->getLength();
    }

    /**
     * Устанавливает количество цифр.
     * 
     * @param int $decimal
     * 
     * @return $this
     */
    public function setDecimal(int $decimal): static
    {
        $this->decimal = $decimal;
        return $this;
    }

    /**
     * Возвращает количество цифр.
     * 
     * @return int
     */
    public function getDecimal(): int
    {
        return $this->decimal;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLengthExpression(): string
    {
        if ($this->decimal > 0) {
            return $this->length . ',' . $this->decimal;
        }
        return $this->length;
    }
}
