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
 * Класс столбца с типом данных "FLOAT" (дробные числа с плавающей точкой одинарной 
 * точности).
 *
 * Невозможно назвать класс "float", начиная с PHP 7, так как это зарезервированное 
 * ключевое слово.
 * Следовательно, "floating" с типом "FLOAT".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl
 * @since 2.0
 */
class Floating extends AbstractPrecisionColumn
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'FLOAT';
}
