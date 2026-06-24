<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

/**
 * Интерфейс выражения в инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
interface ExpressionInterface
{
    public const TYPE_IDENTIFIER = 'identifier';
    public const TYPE_VALUE      = 'value';
    public const TYPE_LITERAL    = 'literal';
    public const TYPE_SELECT     = 'select';

    /**
     * Возвращает данные выражения.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     // строка в формате sprintf
     *     'specification',
     *     // значения для приведенной выше строки в формате sprintf
     *     ['foo' => 'bar', ...],
     *     // массив равной длины массиву значений с TYPE_IDENTIFIER или TYPE_VALUE для каждого значения
     *     [TYPE_IDENTIFIER, ...]
     * ]
     * ```
     * 
     * @return array<int, mixed>
     */
    public function getExpressionData(): array;
}
