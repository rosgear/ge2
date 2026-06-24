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
 * Класс уникального ключа UNIQUE.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Db\Sql\Ddl\Constraint
 * @since 2.0
 */
class UniqueKey extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected string $specification = 'UNIQUE';
}
