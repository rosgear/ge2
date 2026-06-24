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
 * Класс столбца с типом данных "BIGINT" (целые числа от -9 223 372 036 854 775 808 
 * до 9 223 372 036 854 775 807).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
class BigInteger extends Integer
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'BIGINT';
}
