<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Helper;

use Ge;
use Ge\View\ClientScript;

/**
 * Вспомогательный класс формирования URL-адресов ресурсов HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Link implements HelperInterface
{
    /**
     * Массив линков.
     * 
     * @var array
     */
    protected array $links = [];

    /**
     * Помощник формирования favorite icon.
     * 
     * @see Link::appendFavicon()
     * 
     * @var Favicon
     */
    protected Favicon $favicon;

    /**
     * @var array<string, string>
     */
    public array $files = [];

    /**
     * @var array<string, string>
     */
    public array $comments = [];

    /**
     * Базовый (абсолютный) URL к темам
     * имеет вид: "<абсолютный URL приложения/> <локальный путь к темам/>".
     * 
     * @see Ge\Theme\Theme::$baseUrl
     * 
     * @var string
     */
    public string $themeUrl = '';

    /**
     * Вывод комментариев.
     * 
     * @var bool
     */
    public bool $renderComments = true;

    /**
     * Конструктор класса.
     *
     * @return void
     */
    public function __construct()
    {
        $this->themeUrl = Ge::$app->theme->url;
    }

    /**
     * Добавление линка.
     *
     * @param string $id Идентификатор линка.
     * @param string $filename Название файла.
     * @param string $position Позиция на странице (по умолчанию 'head').
     * 
     * @return $this
     */
    public function setFile(string $id, string $filename, string $position = ClientScript::POS_HEAD): static
    {
        $this->files[$position] = array($id => ClientScript::defineSrc($this->themeUrl, $filename));
        return $this;
    }

    /**
     * Добавление favorite icon.
     *
     * @return $this
     */
    public function appendFavicon(): static
    {
        if (!isset($this->favicon)) {
            $this->favicon = new Favicon();
        }
        return $this;
    }

    /**
     * Возвращение собранных линков ввиде HTML.
     * 
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function render(string $indent = ''): string
    {
        $links = '';
        if ($this->renderComments && $this->favicon) {
            $links .= '<!-- ' . $this->comments['favicon'] . ' -->' . PHP_EOL . $indent;
            $links .= $this->favicon->render();
        }
        return $links;
    }
}
