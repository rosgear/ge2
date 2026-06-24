<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

use Ge;
use Ge\Helper\Url;
use Ge\Stdlib\Service;
use Ge\View\Helper\Favicon;

/**
 * Класс ClientScript управляет размещением скриптов клиента в шаблонах представления (view).
 * 
 * ClientScript - это служба приложения, доступ к которой можно получить через `Ge::$app->clientScript`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class ClientScript extends Service
{
    /**
     * @var string Символьный отступ.
     */
    public const CHAR_INDENT = '    ';

    /**
     * @var string Символ подстановки в строку названия хоста.
     */
    public const CHAR_HOST = '@';

    /**
     * @var string Символ подстановки в строку абсолютного URL-адреса.
     */
    public const CHAR_SRC = '~';

    /**
     * @var string Символ обозначение URL-адреса без изменений.
     * (полный URL-адрес начинается c "http...")
     */
    public const CHAR_URL = 'h';

    /**
     * @var string Позиция скриптов внутри тега "<head>".
     */
    public const POS_HEAD = 'head';

    /**
     * @var string Позиция скриптов в начале тега "<body>".
     */
    public const POS_BEGIN = 'begin';

    /**
     * @var string Позиция скриптов в конце тега "<body>".
     */
    public const POS_END  = 'end';

    /**
     * @var string Позиция стилей после успешной загрузки страницы.
     */
    public const POS_READY  = 'ready';

    /**
     * @var string Позиция стилей на загрузке страницы.
     */
    public const POS_LOAD  = 'load';

    /**
     * Связь атрибутов класса с помощниками.
     * 
     * @var array
     */
    protected array $binds = [
        'js'        => 'script',
        'css'       => 'stylesheet',
        'link'      => 'link',
        'html'      => 'html',
        'meta'      => 'meta',
        'openGraph' => 'openGraph',
    ];

    /**
     * Менеджер помощников модели представления.
     * 
     * @var HelperManager
     */
    public HelperManager $helper;

    /**
     * Помощник Favicon.
     * 
     * @see ClientScript::favIcon()
     * 
     * @var Favicon
     */
    protected Favicon $favicon;

    /**
     * Пакеты скриптов.
     * 
     * @see ClientScript::appendPackage()
     * 
     * @var array
     */
    public array $packages = [];

    /**
     * Позиция скриптов в html документе
     * (POS_HEAD, POS_BEGIN, POS_END, POS_READY, POS_LOAD).
     * 
     * @var array
     */
    public array $positions = [];

    /**
     * Зарегистрированные пакеты скриптов.
     * 
     * @var array
     */
    protected array $registerPackages = [];

    /**
     * Выводить адрес схемы хоста в URL-адресе ресурсов.
     * 
     * Указывается параметром "showSchemeUrl" конфигурации сервиса "clientScript".
     * Если значение имеет true, результат "<схема>:[//<хост>]][/<URL-путь>]",
     * иначе "[//<хост>]][/<URL-путь>]".
     * 
     * @var bool
     */
    public bool $showSchemeUrl = false;

    /**
     * Базовый (локальный) общедоступный путь.
     *
     * Указывается параметром "localPath" конфигурации сервиса "clientScript".
     * Пример: "/public".
     * 
     * @var string
     */
    public string $localPath = '';

    /**
     * Абсолютный общедоступный путь.
     * 
     * Имеет вид: "<абсолютный путь к приложению/> <базовый (локальный) общедоступный путь/>"
     * 
     * @var string
     */
    public string $publishedPath = '';

    /**
     * Базовый общедоступный URL-путь.
     * 
     * Указывается параметром "baseUrl" конфигурации сервиса "clientScript".
     * 
     * @var string
     */
    public string $baseUrl = '';

    /**
     * Абсолютный общедоступный URL-адрес.
     * 
     * Имеет вид: "<адрес хоста/> <базовый общедоступный URL-адрес/>"
     * 
     * @var string
     */
    public string $publishedUrl = '';

    /**
     * Общедоступный URL-путь для подключения библиотек JavaScript и таблицы стилей.
     * 
     * Указывается параметром "vendorUrl" конфигурации сервиса "clientScript" в виде 
     * суффикса в абсолютном общедоступном URL-адресе.
     * 
     * @var string
     */
    public string $vendorUrl = '';

    /**
     * @var string
     */
    public string $title = '';

    /**
     * @var string
     * 
     * @see ClientScript::init()
     */
    protected string $cacheCode = '';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->helper = Ge::$services->get('viewHelperManager');
        // позиция скриптов в html документе
        $this->positions = self::definePositions();
        // абсолютный общедоступный путь
        $this->publishedPath = Ge::$app->path . $this->localPath;
        // абсолютный общедоступный URL-адрес
        $this->publishedUrl = Url::home($this->showSchemeUrl). $this->baseUrl;
        // общедоступный URL-путь для подключения библиотек
        $this->vendorUrl = $this->publishedUrl . $this->vendorUrl;
        $this->cacheCode = md5(time());
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        Ge::setAlias('@published', $this->publishedPath);
        Ge::setAlias('@published::', $this->publishedUrl);
        Ge::setAlias('@vendor::', $this->vendorUrl);
    }

    /**
     * Возвращает значения по указанному ключу (магический метод).
     *
     * @param string $key Имя ключа.
     * 
     * @return mixed
     */
    public function __get(string $key)
    {
        if (!isset($this->$key)) {
            $bind = $this->getBind($key);
            if ($bind)
                // deprecated PHP 8.2 (creation of dynamic property)
                return @$this->$key = $this->helper->get($bind);
        }
        return null;
    }

    /**
     * Выполняет проверку существования файла скрипта.
     * 
     * Проверка выполняется относительно {@see ClientScript::$publishedPath}.
     * 
     * @param string $filename Имя файла скрипта или каталога.
     * 
     * @return bool
     */
    public function exists(string $filename): bool
    {
        return file_exists($this->publishedPath . $filename);
    }

    /**
     * Возвращает URL-адрес ресурса
     * 
     * @param string $baseUrl Базовый (локальный) URL-путь ресурса.
     * @param string $filename Название файла подключаемого ресурса.
     * 
     * @return string
     */
    public static function defineSrc(string $baseUrl, string $filename): string 
    {
        // если "h..."
        if ($filename[0] === self::CHAR_URL) {
            return $filename;
        } else
        // если "@..."
        if ($filename[0] === self::CHAR_HOST) {
            return Url::host() . mb_substr($filename, 1);
        } else
        // если нет "~..."
        if ($filename[0] !== self::CHAR_SRC) {
            // если "//..."
            if ($filename[0] === '/' && $filename[1] === '/')
                return $filename;
            // если "/..."
            else
                return $baseUrl . $filename;
        } else
            return mb_substr($filename, 1);
    }

    /**
     * Определение (инициализация) позиций скриптов.
     * 
     * @return array<string, array>
     */
    public static function definePositions(): array
    {
        return [
            self::POS_HEAD  => [],
            self::POS_BEGIN => [],
            self::POS_END   => [],
            self::POS_LOAD  => [],
            self::POS_READY => []
        ];
    }

    /**
     * Отступ слова c определенным количеством раз.
     * 
     * @param string $input Слово.
     * @param int $count Количество повторов слова.
     * 
     * @return string
     */
    public static function getWordIndent(string  $input, int $count = 1): string
    {
        return str_repeat($input, $count);
    }

    /**
     * Возвращает название помощника по его ключу.
     * 
     * @param string $key Имя ключа помощника.
     * 
     * @return false|string
     */
    protected function getBind(string $key): false|string
    {
        return isset($this->binds[$key]) ? $this->binds[$key] : false;
    }

    /**
     * Добавляет пакет скрипта.
     * 
     * @param string $name Имя пакета.
     * @param array<string, array> $options Параметры пакета.
     * 
     * @return $this
     */
    public function appendPackage(string $name, array $options): static
    {
        if (!isset($options['position'])) {
            $options['position'] = self::POS_HEAD;
        }
        if (!isset($options['vendor'])) {
            $options['vendor'] = false;
        }
        if (!isset($options['theme'])) {
            $options['theme'] = false;
        }
        if (!isset($options['baseUrl'])) {
            $options['baseUrl'] = '';
        }
        if (!isset($options['attributes'])) {
            $options['attributes'] = [];
        }

        $this->positions[$options['position']][$name] = true;
        $this->packages[$name] = $options;
        return $this;
    }

    /**
     * Добавляет пакеты скриптов.
     * 
     * @param array<string, array> $packages Пакеты скриптов в виде массива пар "имя пакета - параметры".
     * 
     * @return $this
     */
    public function appendPackages(array $packages): static
    {
        foreach ($packages as $name => $package) {
            $this->appendPackage($name, $package);
        }
        return $this;
    }

    /**
     * Проверяет, добавлен ли пакет с указанным именем.
     * 
     * @param string $name Имя пакета.
     * 
     * @return bool
     */
    public function hasPackage(string $name): bool
    {
        return isset($this->packages[$name]);
    }

    /**
     * Возвращает пакет скриптов.
     * 
     * @param string $name Имя пакета.
     * 
     * @return false|array<string, mixed>
     */
    public function getPackage(string $name): false|array
    {
        return $this->packages[$name] ?? false;
    }

    /**
     * Возвращает все пакеты.
     * 
     * @return array<string, array>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * Удаляет пакет скриптов.
     * 
     * @param string $name Имя пакета
     * 
     * @return $this
     */
    public function removePackage(string $name): static
    {
        if (!isset($this->packages[$name])) return $this;

        $position = $this->packages[$name]['position'];
        unset($this->positions[$position][$name]);
        unset($this->packages[$name]);
        return $this;
    }

    /**
     * Регистрирует пакеты скриптов.
     * 
     * @return $this
     */
    public function registerPackages(): static
    {
        $names = func_get_args();
        if ($names) {
            foreach($names as $index => $name) {
                $this->registerPackage($name);
            }
        }
        return $this;
    }

    /**
     * Регистрирует (добавляет) стилевые инструкции в HTML-документ.
     * 
     * @see \Ge\View\Helper\Stylesheet::appendStyle()
     * 
     * @param string $style Стилевые инструкции.
     * @param string $position Позиция стилевых инструкций в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param string $id Идентификатор скрипта (стилевых инструкций) в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     *     Если значение `null`, тогда он будет иметь значение позиции `$position`.
     * @param array $attributes Атрибуты тега style (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerCss(string $style, string $position = ClientScript::POS_HEAD, ?string $id = null, array $attributes = []): static
    {
        $this->css->appendStyle($id ?? $position, $style, $position, $attributes);
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей текущей темы в 
     * HTML-документ.
     * 
     * @see \Ge\View\Helper\Stylesheet::registerFile()
     * 
     * @param string $filename Имя файла каскадной таблицы стилей текущей темы. 
     *     Пример '/assets/css/foobar.css', результат:
     *     - для frontend 'https://domain/themes/имя_темы/assets/css/foobar.css';
     *     - для backend 'https://domain/themes/backend/имя_темы/assets/css/foobar.css'.
    * @param string|null $id Идентификатор файла каскадной таблицы стилей текущей темы 
     *     в очереди. Идентификатор даёт возможность для последующего его изменения. 
     *     Если значение `null`, тогда он будет иметь значение имя файла `$filename`.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return void
     */
    public function registerCssFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): void
    {
        $this->css->registerFile($id ?? $filename, $filename, $position, $attributes);
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей поставщика (vendor) в 
     * HTML-документ.
     * 
     * @see \Ge\View\Helper\Stylesheet::registerVendorFile()
     * 
     * @param string|null $id Идентификатор файла каскадной таблицы стилей поставщика 
     *     (vendor) в очереди. Идентификатор даёт возможность для последующего его 
     *     изменения. Если значение `null`, тогда он будет иметь значение имя 
     *     файла `$filename`.
     * @param string $filename Имя файла каскадной таблицы стилей поставщика (vendor). 
     *     Пример '/bootstrap/css/bootstrap.min.css' (результат 'https://domain/vendors/bootstrap/css/bootstrap.min.css').
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return void
     */
    public function registerVCssFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): void
    {
        $this->css->registerVendorFile($id ?? $filename, $filename, $position, $attributes);
    }

    /**
     * Регистрирует (добавляет) скрипт в HTML-документ.
     * 
     * @see \Ge\View\Helper\Script::appendScript()
     * 
     * @param string $script Текст скрипта.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param string $id Идентификатор скрипта в очереди. Идентификатор даёт возможность 
     *     для последующего его изменения. Если значение `null`, тогда он будет иметь 
     * значение позиции `$position`.
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerJs(string $script, string $position = ClientScript::POS_HEAD, ?string $id = null, array $attributes = []): static
    {
        $this->js->appendScript($id ?? $position, $script, $position, $attributes);
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл скрипта в HTML-документ.
     * 
     * @see \Ge\View\Helper\Script::registerFile()
     * 
     * @param string $filename Имя файла скрипта. 
     *     Пример '/assets/js/foobar.js', результат:
     *     - для frontend 'https://domain/themes/имя_темы/assets/js/foobar.js';
     *     - для backend 'https://domain/themes/backend/имя_темы/assets/js/foobar.js'.
    * @param string|null $id Идентификатор файла скрипта текущей темы 
     *     в очереди. Идентификатор даёт возможность для последующего его изменения. 
     *     Если значение `null`, тогда он будет иметь значение имя файла `$filename`.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerJsFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->js->registerFile($id ?? $filename, $filename, $position, $attributes);
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл скрипта поставщика (vendor) в HTML-документ.
     * 
     * @see \Ge\View\Helper\Script::registerVendorFile()
     * 
     * @param string $filename Имя файла из библиотеки (vendor) скриптов. 
     *     Пример '/bootstrap/js/bootstrap.min.js' (результат 'https://domain/vendors/bootstrap/js/bootstrap.min.js').
    * @param string|null $id Идентификатор файла скрипта текущей темы 
     *     в очереди. Идентификатор даёт возможность для последующего его изменения. 
     *     Если значение `null`, тогда он будет иметь значение имя файла `$filename`.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerVJsFile( string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->js->registerVendorFile($id ?? $filename, $filename, $position, $attributes);
        return $this;
    }

    /**
     * Возвращает зарегистрированные пакеты скриптов.
     * 
     * @return array<string, array>
     */
    public function getRegisterPackages(): array
    {
        return $this->registerPackages;
    }

    /**
     * Регистрирует пакет скриптов.
     * 
     * @param string $name Имя пакета.
     * 
     * @return $this
     */
    public function registerPackage(string $name): static
    {
        if (isset($this->registerPackages[$name]) || !$this->hasPackage($name)) {
            return $this;
        }

        $package  = $this->getPackage($name);
        $css      = $this->css;
        $js       = $this->js;
        $baseUrl  = $package['baseUrl'];;
        $position = $package['position'];
        $isVendor = $package['vendor'];
        $isTheme  = $package['theme'];
        $noCache  = $package['noCache'] ?? false;
        $attributes = $package['attributes'];
        if (!empty($package['css'])) {
            /** @var array<int, string> $options => [src, position, attributes] */
            foreach ($package['css'] as $id => $options) {
                $src        = $options[0];
                $position   = $options[1] ?? $position; 
                $attributes = $options[2] ?? $attributes;

                if ($baseUrl)
                    $src = $baseUrl . $src;
                if ($noCache)
                    $src .= '?' . $this->cacheCode;
                if ($isVendor)
                    $css->registerVendorFile($id, $src, $position, $attributes);
                else
                if ($isTheme)
                    $css->registerThemeFile($id, $src, $position, $attributes);
                else
                    $css->registerFile($id, $src, $position, $attributes);
            }
        }

        $position = $package['position'];
        if (!empty($package['js'])) {
            /** @var array<int, string> $options => [src, position, attributes] */
            foreach ($package['js'] as $id => $options) {
                $src        = $options[0];
                $position   = $options[1] ?? $position; 
                $attributes = $options[2] ?? $attributes;

                if ($baseUrl)
                    $src = $baseUrl . $src;
                if ($noCache)
                    $src .= '?' . $this->cacheCode;
                if ($isVendor)
                    $js->registerVendorFile($id, $src, $position, $attributes);
                else
                if ($isTheme)
                    $js->registerThemeFile($id, $src, $position, $attributes);
                else
                    $js->registerFile($id, $src, $position, $attributes);
            }
        }
        return $this;
    }

    /**
     * Возвращает помощника "Favicon".
     * 
     * @return Favicon
     */
    public function favIcon(): Favicon
    {
        if (!isset($this->favicon)) {
            $this->favicon = $this->helper->get('favicon');
        }
        return $this->favicon;
    }

    /**
     * Добавляет метаданные.
     * 
     * @param array $attributes Атрибуты метаданных.
     * @param array $meta Метаданные.
     * 
     * @return $this
     */
    public function registerMeta(array $attributes, array $meta): static
    {
        foreach ($meta as $index => $name) {
            if (is_array($name)) {
                $this->$index->setCommon($attributes);
                $metaObj = $this->$index;
                foreach ($name as $subindex => $subname) {
                    $metaObj->$subname->setCommon($attributes);
                }
            } else
                $this->$name->setCommon($attributes);
        }
        return $this;
    }

    /**
     * Возвращает HTML тег "<title>".
     * 
     * @param null|string $title Заголовок (по умолчанию `null`).
     * @param string $pattern Шаблон заголовка к (по умолчанию '').
     * 
     * @return string
     */
    public function renderTitle(?string $title = null, string $pattern = ''): string
    {
        $title = $title === null ? $this->title : $title;
        if ($pattern) {
            $title = sprintf($pattern, $title);
        }
        return '<title>' . $title . '</title>';
    }

    /**
     * Возвращает метаданные HTML.
     * 
     * @param int $indent Количество отступов текста слева в символах (по умолчанию '1').
     * 
     * @return string
     */
    public function renderMeta(int $indent = 1): string
    {
        if ($indent) {
            if (is_numeric($indent))
                $indent = self::getWordIndent(self::CHAR_INDENT, $indent);
        }

        $html = '';
        if (isset($this->meta)) {
            $html .= $this->meta->render($indent);
            $this->html->appendAttributes('html', $this->meta->getHtmlAttributes());
        }

        if (isset($this->openGraph)) {
            $html .= $this->openGraph->render($indent);
            $this->html->appendAttributes('html', $this->openGraph->getHtmlAttributes());
        }
        return $html;
    }

    /**
     * Возвращает все скрипты и метаданные HTML.
     * 
     * @param string $position Позиция скрипта в html {@see ClientScript::$positions}.
     * @param int $indent Количество отступов текста слева в символах (по умолчанию '1').
     * 
     * @return string
     */
    public function render(string $position, int $indent = 1): string
    {
        $indent = $indent ? self::getWordIndent(self::CHAR_INDENT, $indent) : '';

        $html = '';
        if ($position == self::POS_HEAD) {
            if ($this->favicon !== null)
                $html .= $this->favicon->render($indent);
        }

        $html .= $this->css->renderFiles($position, $indent);
        $html .= $this->js->renderFiles($position, $indent);
        return $html;
    }
}
