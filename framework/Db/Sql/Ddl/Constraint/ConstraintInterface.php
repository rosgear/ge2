<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Constraint;

use Ge\Db\Sql\ExpressionInterface;

/**
 * Интерфейс ограничений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Constraint
 * @since 2.0
 */
interface ConstraintInterface extends ExpressionInterface
{
    /**
     * Возвращает столбцы таблицы.
     * 
     * @return array
     */
    public function getColumns(): array;
}
