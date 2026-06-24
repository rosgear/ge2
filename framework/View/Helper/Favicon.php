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

/**
 * Вспомогательный класс формирования фавикон (favorite icon) HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Favicon implements HelperInterface
{
    /**
     * Массив настроек для мобильных устройств (для формирования тегов "meta").
     * 
     * @var array
     */
    protected array $devices = [
        'windows' => [
            'color' => ['name' => 'msapplication-TileColor', 'content' => ''],
            'image' => ['name' => 'msapplication-TileImage', 'content' => '/mstile-144x144.png'],
        ],
        'mobile' => [
            'color' => ['name' => 'theme-color', 'content' => '']
        ]
    ];

    /**
     * Массив размеров favicon.
     * 
     * @var array
     */
    protected array $sizes = [
        '16x16'          => ['size' => '16x16', 'rel' => 'icon'],
        '32x32'          => ['size' => '32x32', 'rel' => 'icon'],
        '96x96'          => ['size' => '96x96', 'rel' => 'icon'],
        '192x192'        => ['size' => '192x192', 'rel' => 'icon'],
        '194x194'        => ['size' => '194x194', 'rel' => 'icon'],
        'iPhoneClassic6' => ['size' => '57x57', 'rel' => 'apple-touch-icon'],
        'iPhoneClassic7' => ['size' => '60x60', 'rel' => 'apple-touch-icon'],
        'iPadClassic6'   => ['size' => '72x72', 'rel' => 'apple-touch-icon'],
        'iPadClassic7'   => ['size' => '76x76', 'rel' => 'apple-touch-icon'],
        'iPhoneRetina6'  => ['size' => '114x114', 'rel' => 'apple-touch-icon'],
        'iPhoneRetina7'  => ['size' => '120x120', 'rel' => 'apple-touch-icon'],
        'iPadRetina6'    => ['size' => '144x144', 'rel' => 'apple-touch-icon'],
        'iPadRetina7'    => ['size' => '152x152', 'rel' => 'apple-touch-icon'],
        'iPhone6Plus'    => ['size' => '180x180', 'rel' => 'apple-touch-icon']
    ];

    /**
     * Цвет темы браузера для мобильных устройств.
     * 
     * @var string
     */
    public string $themeColor = '#2b5797';

    /**
     * Вывод комментариев.
     * 
     * @var bool
     */
    public bool $renderComments = true;

    /**
     * Комментарии.
     * 
     * @var array
     */
    public array $comments = ['favicon'  => 'favorite icon'];

    /**
     * Url ресурса темы.
     * 
     * @var string
     */
    protected string $themeUrl = '';

    /**
     * Конструктор класса.
     *
     * @return void
     */
    public function __construct()
    {
        $this->themeUrl = Ge::$app->theme->url . '/assets/ico';
    }

    /**
     * Возвращение названия файла favicon.
     * 
     * @param string $rel 
     * @param string $size Размер значка, например "16x16".
     * 
     * @return string
     */
    protected function filename(string $rel, string $size): string
    {
        if ($size == '16x16')
            return $this->themeUrl . '/favicon.ico';
        else
            return $this->themeUrl . '/' . $rel . '-' . $size . '.png';
    }

    /**
     * Установка размеров favicon.
     * 
     * @param array $sizes Массив размеров.
     * 
     * @return $this
     */
    public function setSizes(array $sizes): static
    {
        $this->sizes = $sizes;
        return $this;
    }

    /**
     * Установка размера favicon.
     * 
     * @param string $name Название или размер.
     * @param array $options Настройки.
     * 
     * @return $this
     */
    public function setSize(string $name, array $options): static
    {
        $this->sizes[$name] = $options;
        return $this;
    }

    /**
     * Удаление размера favicon.
     * 
     * @param string $name Название или размер.
     * 
     * @return $this
     */
    public function unsetSize(string $name): static
    {
        if (isset($this->sizes[$name])) {
            unset($this->sizes[$name]);
        }
        return $this;
    }

    /**
     * Установка массива мобильных устройств.
     * 
     * @param array $devices Массив.
     * 
     * @return $this
     */
    public function setDevices(array $devices): static
    {
        $this->devices = $devices;
        return $this;
    }

    /**
     * Установка мобильного устройста.
     * 
     * @param string $name Название.
     * @param array $options Настройки.
     * 
     * @return $this
     */
    public function setDevice(string $name, array $options): static
    {
        $this->devices[$name] = $options;
        return $this;
    }

    /**
     * Удаление мобильного устройста.
     * 
     * @param string $name Название
     * 
     * @return $this
     */
    public function unsetDevice(string $name): static
    {
        if (isset($this->devices[$name])) {
            unset($this->devices[$name]);
        }
        return $this;
    }

    /**
     * Возвращение собранных тегов "meta" для мобильных устройств.
     * 
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function renderDevices(string $indent = ''): string
    {
        $str = '';
        foreach ($this->devices as $device => $tags) {
            foreach($tags as $name => $options) {
                $content = '';
                switch ($name) {
                    case 'color': $content = $this->themeColor; break;
                    case 'image': $content = $this->themeUrl . $options['content']; break;
                }
                $str .= '<meta name="' . $options['name'] .'" content="' . $content . '">' . PHP_EOL . $indent;
            }
        }
        return $str;
    }

    /**
     * Возвращение комментариев.
     * 
     * @param string $name Ключ комментария {@see Favicon::$comments}.
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function comment(string $name, string $indent = ''): string
    {
        return '<!-- ' . $this->comments[$name] . ' -->' . PHP_EOL . $indent;
    }

    /**
     * Возвращение собранных ссылок на favicon ввиде html.
     * 
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function render(string $indent = ''): string
    {
        $code = '';
        // если необходимо выводить комментарии
        if ($this->renderComments && $this->sizes)
            $code .= $this->comment('favicon', $indent);
        // все размеры
        foreach($this->sizes as $name => $item) {
            if (empty($item['size']) || empty($item['rel'])) continue;
            $code .= '<link type="image/png" href="' . $this->filename($item['rel'], $item['size']) . '" rel="' . $item['rel'] . '" sizes="' . $item['size'] . '">' . PHP_EOL . $indent;
        }
        // если есть устройства
        if ($this->devices)
            $code .= $this->renderDevices($indent);
        return $code;
    }
}
