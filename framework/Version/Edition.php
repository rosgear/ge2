<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Version;

/**
 * Версия редакции приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Version
 * @since 2.0
 */
class Edition extends BaseVersion
{
    /**
     * Код версии редакции.
     * 
     * Сокращенное уникальное название редакции приложения.
     * 
     * Например, если (международное) название редакции `$name = 'Start'`, 
     * то код может быть указан, как 'STR'.
     * 
     * @var string
     */
    public string $code = '';

    /**
     * Оригинальное название версии редакции приложения.
     * 
     * Это название применяется для страны разработчика.
     * 
     * Например, если (международное) название редакции приложения `$name = 'Start'`, 
     * то название в стране разработчика будет 'Старт' или на том языке, 
     * где зарегистрирован ваш продукт (редакция приложения).
     * 
     * @var string
     */
    public string $originalName = '';
}
