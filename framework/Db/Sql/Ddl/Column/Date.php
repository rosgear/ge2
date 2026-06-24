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
 * Класс столбца с типом данных "DATE" (дата в формате "yyyy-mm-dd").
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
class Date extends Column
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'DATE';
}
