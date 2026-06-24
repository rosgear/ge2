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
 * Литеральное выражаение в инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Literal implements ExpressionInterface
{
    /**
     * Литерал.
     * 
     * @var string
     */
    protected string $literal = '';

    /**
     * Конструктор класса.
     * 
     * @param $literal Литерал.
     */
    public function __construct(string $literal = '')
    {
        $this->literal = $literal;
    }

    /**
     * Устанавливает литерал.
     * 
     * @param string $literal Литерал.
     * 
     * @return $this
     */
    public function setLiteral(string $literal): static
    {
        $this->literal = $literal;
        return $this;
    }

    /**
     * Возвращает литерал.
     * 
     * @return string
     */
    public function getLiteral(): string
    {
        return $this->literal;
    }

    /**
     * Возвращает выражение литерала.
     * 
     * @return array
     */
    public function getExpressionData(): array
    {
        return [
            [
                str_replace('%', '%%', $this->literal),
                [],
                []
            ]
        ];
    }
}
