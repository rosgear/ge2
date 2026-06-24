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
 * Предикат "NotLike" для инструкции SQL "expression NOT LIKE pattern [ESCAPE 'escape_char']".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class NotLike extends Like
{
    /**
     * {@inheritdoc}
     */
    protected string $specification = '%1$s NOT LIKE %2$s';
}
