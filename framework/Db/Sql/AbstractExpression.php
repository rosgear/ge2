<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

use Ge;

/**
 * Абстрактный класс выражений инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Db\Sql
 * @since 2.0
 */
abstract class AbstractExpression implements ExpressionInterface
{
    /**
     * Допустимые типы выражений
     * 
     * @var array<int, string>
     */
    protected $allowedTypes = [
        self::TYPE_IDENTIFIER,
        self::TYPE_LITERAL,
        self::TYPE_SELECT,
        self::TYPE_VALUE
    ];

    /**
     * Нормализует аргументы.
     *
     * @param mixed $argument Аргумент.
     * @param string $defaultType Тип выражения (по умолчани `TYPE_VALUE`).
     * 
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeArgument(mixed $argument, string $defaultType = self::TYPE_VALUE): array
    {
        if ($argument instanceof ExpressionInterface || $argument instanceof SqlInterface) {
            return $this->buildNormalizedArgument($argument, self::TYPE_VALUE);
        }

        if (is_scalar($argument) || $argument === null) {
            return $this->buildNormalizedArgument($argument, $defaultType);
        }

        if (is_array($argument)) {
            $value = current($argument);

            if ($value instanceof ExpressionInterface || $value instanceof SqlInterface) {
                return $this->buildNormalizedArgument($value, self::TYPE_VALUE);
            }

            $key = key($argument);

            if (is_integer($key) && ! in_array($value, $this->allowedTypes)) {
                return $this->buildNormalizedArgument($value, $defaultType);
            }

            return $this->buildNormalizedArgument($key, $value);
        }

        throw new Exception\InvalidArgumentException(
            Ge::t('app',
                '$argument should be {0} or {1} or {2} or {3} or {4}, "{5}" given',
                ['null', 'scalar', 'array', 'Ge\Db\Sql\ExpressionInterface', 'Ge\Db\Sql\SqlInterface', 
                is_object($argument) ? get_class($argument) : gettype($argument)]
            )
        );
    }

    /**
     * Выполняет нормализацию аргумента.
     * 
     * @param mixed $argument Аргумент.
     * @param string $argumentType Тип аргумента.
     * 
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    private function buildNormalizedArgument(mixed $argument, string $argumentType): array
    {
        if (!in_array($argumentType, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Argument type should be in array({0})', [implode(',', $this->allowedTypes)])
            );
        }
        return [$argument, $argumentType];
    }
}
