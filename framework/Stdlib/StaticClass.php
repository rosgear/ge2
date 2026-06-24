<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

/**
 * Вспомогательный статический класс.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class StaticClass
{
    /**
     * Массив анонимных методов класса.
     * 
     * Имя метода соответствует анонимной функции (Closure), добавляемой с помощью 
     * {@see StaticClass::addMethod()}.
     * 
     * @var array
     */
    protected static array $methods = [];

    /**
     * Запускается при вызове недоступных методов в статическом контексте. 
     * 
     * @param string $name Имя вызываемого метода
     * @param array $arguments Нумерованный массив, содержащий параметры, переданные 
     *    в вызываемый метод $name
     * 
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (isset(static::$methods[$name])) {
             return forward_static_call_array(static::$methods[$name], $arguments);
        }
        return null;
    }

    /**
     * Добавляет динамический метод классу.
     * 
     * @param mixed $name Имя метода.
     * @param callable $func Callback-функция.
     * 
     * @return void
     */
    public static function addMethod(string $name, callable $func): void
    {
        static::$methods[$name] = $func;
    }

    /**
     * Проверяет, существует ли динамический метод.
     * 
     * @param string $name Имя метода.
     * 
     * @return bool
     */
    public static function hasMethod(string $name): bool
    {
        return isset(static::$methods[$name]);
    }

    /**
     * Удаляет динамический метод.
     * 
     * @param string $name Имя метода.
     * 
     * @return bool Если значение `true`, метод успешно удалён.
     */
    public static function removeMethod(string $name): bool
    {
        if (isset(static::$methods[$name])) {
            unset(static::$methods[$name]);
            return true;
        }
        return false;
    }
}
