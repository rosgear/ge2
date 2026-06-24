<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme;

use Ge;
use Ge\View\ClientScript;
use Ge\Exception\BadMethodCallException;

/**
 * ThemeAsset класс регистрации пакетов ресурсов темы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme
 * @since 1.0
 */
class ThemeAsset
{
    /**
     * Cкрипты клиента в шаблонах.
     * 
     * @var ClientScript
     */
    protected ClientScript $script;

    /**
     * Конструктор класса.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->script = Ge::$services->getAs('clientScript');
    }

    /**
     * Регистрация пакетов ресурсов.
     * 
     * @param string|array $name Имя или имена пакетов ресурсов, которые необходимо 
     *     зарегистрировать.
     * 
     * @return void
     * 
     * @throws BadMethodCallException Ошибка вызова метода.
     */
    public function register(string|array $name): void
    {
        $name = (array) $name;
        foreach ($name as $method) {
            if (!method_exists($this, $method)) {
                throw new BadMethodCallException(sprintf('Method "%s" not found.', $method));
            }
            $this->$method();
        }
    }
}
