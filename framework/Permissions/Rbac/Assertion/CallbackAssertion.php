<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Rbac\Assertion;

use Ge\Permissions\Rbac\Rbac;
use Ge\Permissions\Rbac\AssertionInterface;
use Ge\Permissions\Rbac\Exception\InvalidArgumentException;

/**
 * Класс обратного вызова утверждения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac\Assertion
 * @since 2.0
 */
class CallbackAssertion implements AssertionInterface
{
    /**
     * @var callable Обратный вызов.
     */
    protected $callback;

    /**
     * @param callable $callback
     * 
     * @throws InvalidArgumentException Не установлен обратный вызов.
     */
    public function __construct(callable $callback)
    {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided, not callable');
        }
        $this->callback = $callback;
    }

    /**
     * Метод утверждения - должен возвращать логическое значение.
     *
     * Возвращает результат выполненного обратного вызова.
     *
     * @param Rbac $rbac
     * 
     * @return bool
     */
    public function assert(Rbac $rbac): bool
    {
        return (bool) call_user_func($this->callback, $rbac);
    }
}
