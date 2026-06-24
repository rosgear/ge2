<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Predicate;

use Ge\Db\Sql\Expression as BaseExpression;

/**
 * Предикат "Expression" для SQL инструкции.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Predicate
 * @since 2.0
 */
class Expression extends BaseExpression implements PredicateInterface
{
    /**
     * Конструктор класса.
     *
     * @param null|string $expression Выражение.
     * @param int|float|bool|string|array|null $valueParameter Параметры выражения.
     */
    public function __construct(
        ?string $expression = null, 
        mixed $valueParameter = null /*[, $valueParameter, ... ]*/)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }
}
