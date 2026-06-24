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
 * Класс столбца с типом данных "INT" (целые числа от -2147483648 до 2147483647).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl
 * @since 2.0
 */
class Integer extends Column
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'INT';

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $data    = parent::getExpressionData();
        $options = $this->getOptions();

        if (isset($options['length'])) {
            $data[0][1][1] .= '(' . $options['length'] . ')';
        }

        return $data;
    }
}
