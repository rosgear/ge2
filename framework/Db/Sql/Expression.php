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
 * Выражение инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql
 * @since 2.0
 */
class Expression extends AbstractExpression
{
    /**
     * @var string 
     */
    public const PLACEHOLDER = '?';

    /**
     * Выражение.
     * 
     * @see Expression::setExpression()
     * 
     * @var string
     */
    protected string $expression = '';

    /**
     * Параметры выражения.
     * 
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var array
     */
    protected array $types = [];

    /**
     * Конструктор класса.
     * 
     * @param string $expression Выражение.
     * @param mixed $parameters Параметры выражения (по умолчанию `null`).
     * @param array $types @deprecated будет упращено в версии 3.0 (по умолчанию `[]`).
     */
    public function __construct(string $expression = '', mixed $parameters = null, array $types = [])
    {
        if ($expression !== '') {
            $this->setExpression($expression);
        }

        if ($types) { // версия должна быть признана устаревшей и удалена в 3.0
            if (is_array($parameters)) {
                foreach ($parameters as $i => $parameter) {
                    $parameters[$i] = [
                        $parameter => isset($types[$i]) ? $types[$i] : self::TYPE_VALUE,
                    ];
                }
            } elseif (is_scalar($parameters)) {
                $parameters = [
                    $parameters => $types[0],
                ];
            }
        }

        if ($parameters) {
            $this->setParameters($parameters);
        }
    }

    /**
     * Устанавливает выражение.
     * 
     * @param $expression Выражение.
     * 
     * @return Expression
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function setExpression(string $expression)
    {
        if ($expression === '') {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Supplied expression must be a string')
            );
        }
        $this->expression = $expression;
        return $this;
    }

    /**
     * Возвращает выражение.
     * 
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Устанавдивает параметры.
     * 
     * @param $parameters Параметры.
     * 
     * @return $this
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Возвращает параметры.
     * 
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @deprecated
     * @param array $types
     * 
     * @return Expression
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @deprecated
     * 
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     * 
     * @throws Exception\RuntimeException
     */
    public function getExpressionData(): array
    {
        $parameters = (is_scalar($this->parameters)) ? [$this->parameters] : $this->parameters;
        $parametersCount = count($parameters);
        $expression = str_replace('%', '%%', $this->expression);

        if ($parametersCount == 0) {
            return [str_ireplace(self::PLACEHOLDER, '', $expression)];
        }

        // assign locally, escaping % signs
        $expression = str_replace(self::PLACEHOLDER, '%s', $expression, $count);
        if ($count !== $parametersCount) {
            throw new Exception\RuntimeException(
                Ge::t('app', 'The number of replacements in the expression does not match the number of parameters')
            );
        }
        foreach ($parameters as $parameter) {
            list($values[], $types[]) = $this->normalizeArgument($parameter, self::TYPE_VALUE);
        }
        return [
            [
                $expression,
                $values,
                $types
            ]
        ];
    }
}
