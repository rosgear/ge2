<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Version;

use Ge;

/**
 * Версия приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Version
 * @since 2.0
 */
class AppVersion extends Version
{
    /**
     * Код версии приложение.
     * 
     * Сокращенное уникальное название приложения.
     * 
     * Например, если (международное) название приложения `$name = 'Company: Personnel management'`, 
     * то код может быть указан, как 'CMP PM'.
     * 
     * @var string
     */
    public string $code = '';

    /**
     * Оригинальное название версии приложения.
     * 
     * Это название применяется для страны разработчика.
     * 
     * Например, если (международное) название приложения `$name = 'Personnel management'`, 
     * то название в стране разработчика будет 'Управление персоналом' или на том языке, 
     * где зарегистрирован ваш продукт (приложение).
     * 
     * @var string
     */
    public string $originalName = '';

    /**
     * Возвращает версию редакции приложения.
     * 
     * @return Edition|null
     */
    public function getEdition(): ?Edition
    {
        // если не указаны базовые параметры свойства редакции
        if ($this->edition === null) {
            $this->edition = Ge::$app->config->edition ?? [];
        }

        if (is_array($this->edition)) {
            $this->edition = Ge::createObject('\Ge\Version\Edition', $this->edition);
        }
        return $this->edition;
    }
}
