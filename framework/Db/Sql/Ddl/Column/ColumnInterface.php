<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Column;

use Ge\Db\Sql\ExpressionInterface;

/**
 * Интерфейс ColumnInterface описывает протокол взаимодействия объектов Column.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
interface ColumnInterface extends ExpressionInterface
{
    /**
     * Возвращает имя столбца.
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает возможность столбца иметь значение NULL.
     * 
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Возвращает значение столбца по умолчанию.
     * 
     * @return mixed
     */
    public function getDefault(): mixed;

    /**
     * Возвращает параметры столбца.
     * 
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
