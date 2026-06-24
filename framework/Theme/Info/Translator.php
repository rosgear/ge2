<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme\Info;

use Ge;

/**
 * Транслятор (переводчик) выполняет локализацию описания шаблонов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme\Info
 * @since 2.0
 */
class Translator 
{
    /**
     * Имя локали.
     * 
     * Например: 'ru_RU', 'en_GB.
     * 
     * @var string
     */
    protected string $locale;

    /**
     * Сообщения локализации.
     * 
     * @see Translator::loadPattern()
     * 
     * @var array
     */
    protected array $messages = [];

    /**
     * Конструктор класса.
     * 
     * @param string|null $locale Имя локали, например: 'ru_RU', 'en_GB.
     * 
     * @return void
     */
    public function __construct(?string $locale = null)
    {
        if ($locale === null) {
            $this->locale = Ge::$services->getAs('language')->get('locale', '');
        }
        $this->loadPattern();
    }

    /**
     * Загружает сообщения для описания шаблонов в указанной локали.
     * 
     * @param null|string $locale Имя локали, например: 'ru_RU', 'en_GB.
     * 
     * @return $this
     */
    public function loadPattern(?string $locale = null): static
    {
        if ($locale === null) {
            $locale = $this->locale;
        }
        $this->messages = include('patterns' . DS . $locale . '.php');
        return $this;
    }

    /**
     * Выполняет перевод (локализацию) сообщения.
     * 
     * @param string $message Текст сообщения.
     * 
     * @return string|array
     */
    public function translate(string $message): string|array
    {
        if (isset($this->messages[$message]))
            return $this->messages[$message];
        else
            return $message;
    }

    /**
     * Перевод (локализация) сообщения из источника.
     * 
     * @param string $source Источник, ключ в массиве сообщений.
     * @param string $message Текст сообщения.
     * 
     * @return string|array
     */
    public function translateFrom(string $source, string $message): string|array
    {
        return $this->messages[$source][$message] ?? $message;
    }
}
