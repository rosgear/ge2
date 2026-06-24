<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge\Mvc\Application;

/**
 * Базовый вспомогательный класс.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Helper
{
    /**
     * Экземпляр приложения.
     * 
     * @see Helper::setApplication()
     * 
     * @var Application|null
     */
    protected static ?Application $app = null;

    /**
     * Возвращает экземпляр приложения.
     *
     * @return Application|null
     */
    public static function getApplication(): ?Application
    {
        return static::$app;
    }

    /**
     * Устанавливает экземпляр приложения.
     * 
     * @see \Ge\Mvc\Application::initHelper()
     * 
     * @param Application|null $app
     * 
     * @return void
     */
    public static function setApplication(?Application $app): void
    {
        static::$app = $app;
    }
}
