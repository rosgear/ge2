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
 * Класс столбца с типом данных "BOOLEAN" (псевдонимом для типа TINYINT(1)).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
class Boolean extends Column
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'BOOLEAN';

    /**
     * {@inheritdoc}
     */
    public function setNullable(bool $nullable): static
    {
        return parent::setNullable(false);
    }
}
