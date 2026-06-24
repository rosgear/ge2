<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

/**
 * Предикат "IsNotNull", как условие для инструкции SQL "expression IS NOT NULL".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class IsNotNull extends IsNull
{
    /**
     * {@inheritdoc}
     */
    protected $specification = '%1$s IS NOT NULL';
}
