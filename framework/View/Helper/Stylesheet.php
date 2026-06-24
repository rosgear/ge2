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
use Ge\Helper\Html;
use Ge\View\ClientScript;

/**
 * Вспомогательный класс формирования каскадных таблиц стилей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Stylesheet implements HelperInterface
{
    /**
     * Массив подключаемых CSS файлов.
     * 
     * @var array
     */
    protected array $files = [];

    /**
     * Массив подключаемых CSS файлов "ядра".
     * (vendors/)
     * 
     * @var array
     */
    protected array $vendorFiles = [];

    /**
     * Массив подключаемых CSS файлов "в последнюю очередь".
     * 
     * @var array
     */
    protected array $themeFiles = [];

    /**
     * Массив подключаемых стилей.
     * 
     * @var array
     */
    protected array $styles = [];

    /**
     * URL-путь подключения библиотек компонентов (для всех тем).
     * 
     * @var string
     */
    public string $vendorUrl = '/vendors';

    /**
     * URL ресурсов темы.
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
     * Разделитель стилевых инструкций при регистрации их с одним идентификатором.
     * 
     * @see Stylesheet::appendStyle()
     * 
     * @var string
     */
    public string $styleSeparator = PHP_EOL;

    /**
     * Комментарии.
     * 
     * @var array
     */
    public array $comments = [
        'vendorFiles'   => 'vendors CSS',
        'files'         => 'theme vendors CSS',
        'themeFiles'    => 'theme CSS',
        'resourceFiles' => 'resources CSS',
    ];

    /**
     * Конструктор класса.
     *
     * @return void
     */
    public function __construct()
    {
        $this->themeFiles = 
        $this->vendorFiles = 
        $this->files = 
        $this->styles = ClientScript::definePositions();
        $this->vendorUrl  = Ge::$app->clientScript->vendorUrl;
        $this->themeUrl = Ge::$app->theme->url;
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей текущей темы в HTML-документ.
     * 
     * Пример:
     * ```php
     * $css->registerFile('/assets/css/foobar.css', 'foobar.css', ClientScript::POS_HEAD);
     * ```
     * 
     * @param string $id Идентификатор файла каскадной таблицы стилей текущей темы 
     *     в очереди.
     * @param string $filename Имя файла каскадной таблицы стилей текущей темы. 
     *     Пример '/assets/css/foobar.css', результат:
     *     - для frontend 'https://domain/themes/имя_темы/assets/css/foobar.css';
     *     - для backend 'https://domain/themes/backend/имя_темы/assets/css/foobar.css'.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['href'] = ClientScript::defineSrc($this->themeUrl, $filename);
        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'stylesheet';
        }
        $this->files[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей поставщика (vendor) в HTML-документ.
     * 
     * Если указана позиция {@see ClientScript::POS_HEAD}, скрипт будет добавлен в 
     * метаданные HTML-документа.
     * 
     * Пример:
     * ```php
     * $css->registerVendorFile('/bootstrap/css/bootstrap.min.css', 'bootstrap', ClientScript::POS_HEAD);
     * ```
     * 
     * @param string $id Идентификатор файла каскадной таблицы стилей поставщика 
     *     (vendor) в очереди.
     * @param string $filename Имя файла каскадной таблицы стилей поставщика (vendor). 
     *     Пример '/bootstrap/css/bootstrap.min.css' (результат 'https://domain/vendors/bootstrap/css/bootstrap.min.css').
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerVendorFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['href'] = ClientScript::defineSrc($this->vendorUrl, $filename);
        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'stylesheet';
        }
        $this->vendorFiles[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей текущей темы в HTML-документ.
     * 
     * Синоним {@see Stylesheet::registerFile()}, за исключением добавления в 
     * {@see Stylesheet::themeFiles()}.
     * 
     * Пример:
     * ```php
     * $css->registerThemeFile('/assets/css/foobar.css', 'foobar.css', ClientScript::POS_HEAD);
     * ```
     * 
     * @param string $id Идентификатор файла каскадной таблицы стилей текущей темы 
     *     в очереди.
     * @param string $filename Имя файла каскадной таблицы стилей текущей темы. 
     *     Пример '/assets/css/error.css', результат:
     *     - для frontend 'https://domain/themes/имя_темы/assets/css/foobar.css';
     *     - для backend 'https://domain/themes/backend/имя_темы/assets/css/foobar.css'.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerThemeFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['href'] = ClientScript::defineSrc($this->themeUrl, $filename);
        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'stylesheet';
        }
        $this->themeFiles[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Отменяет регистрацию файла каскадной таблицы стилей.
     *
     * @param string $id Идентификатор файла скрипта в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор файла скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterFile(string $id, ?string $position = null): static
    {
        if ($position === null) {
            unset($this->files[ClientScript::POS_HEAD][$id], $this->files[ClientScript::POS_BEGIN][$id], $this->files[ClientScript::POS_END][$id], 
                $this->files[ClientScript::POS_READY][$id], $this->files[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->files[$position][$id]);
        return $this;
    }

    /**
     * Отменяет регистрацию файла каскадной таблицы стилей поставщика (vendor).
     *
     * @param string $id Идентификатор файла скрипта поставщика в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор файла скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterVendorFile(string $id, ?string $position): static
    {
        if ($position === null) {
            unset($this->vendorFiles[ClientScript::POS_HEAD][$id], $this->vendorFiles[ClientScript::POS_BEGIN][$id], 
                $this->vendorFiles[ClientScript::POS_END][$id], $this->vendorFiles[ClientScript::POS_READY][$id], 
                $this->vendorFiles[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->vendorFiles[$position][$id]);
        return $this;
    }

    /**
     * Отменяет регистрацию файла каскадной таблицы стилей темы (theme).
     *
     * @param string $id Идентификатор файла скрипта темы в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор файла скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterThemeFile(string $id, ?string $position): static
    {
        if ($position === null) {
            unset($this->themeFiles[ClientScript::POS_HEAD][$id], $this->themeFiles[ClientScript::POS_BEGIN][$id], 
                $this->themeFiles[ClientScript::POS_END][$id], $this->themeFiles[ClientScript::POS_READY][$id], 
                $this->themeFiles[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->themeFiles[$position][$id]);
        return $this;
    }

    /**
     * Регистрирует (добавляет) стилевые инструкции в HTML-документ.
     * 
     * Пример:
     * ```php
     * $script->registerStyle('main', 'a {color: green}', ClientScript::POS_HEAD);
     * ```
     * 
     * @param string $id Идентификатор скрипта (стилевых инструкций) в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     * @param string $style Стилевые инструкции.
     * @param string $position Позиция стилевых инструкций в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега style (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerStyle(string $id, string $style, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->styles[$position][$id] = ['content' => $style, 'attributes' => $attributes];
        return $this;
    }

    /**
     * Отменяет регистрацию стилевых инструкций.
     *
     * @param string $id Идентификатор скрипта (стилевых инструкций) в очереди.
     * @param string|null $position Позиция стилевых инструкций в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор стилевых инструкций
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterStyle(string $id, ?string $position = null): static
    {
        if ($position === null) {
            unset($this->styles[ClientScript::POS_HEAD][$id], $this->styles[ClientScript::POS_BEGIN][$id], 
                $this->styles[ClientScript::POS_END][$id], $this->styles[ClientScript::POS_READY][$id], 
                $this->styles[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->styles[$position][$id]);
        return $this;
    }

    /**
     * Добавляет стилевые инструкции уже к зарегистрированным.
     *
     * @param string $id Идентификатор стилевой инструкци в очереди.
     * @param string $style Стилевые инструкции.
     * @param string $position Позиция стилевых инструкций в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега style (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function appendStyle(string $id, string $style, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        if (isset($this->styles[$position][$id]))
            $this->styles[$position][$id]['content'] = $this->styles[$position][$id]['content'] . $this->styleSeparator . $style;
        else
            $this->styles[$position][$id] = ['content' => $style, 'attributes' => $attributes];
        return $this;
    }

    /**
     * Отменяет регистрацию стилевых инструкций или файлов каскадных таблиц стилей.
     *
     * @param string|array $id Идентификатор(ы) скрипта (стилевых инструкций) или файла(ов) в очереди.
     * @param string|null $position Позиция стилевых инструкций или файлов каскадных 
     *     таблиц стилей в HTML-документе {@see ClientScript} (по умолчанию `null`). 
     *     Если значение `null`, то идентификаторы будут удалены из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregister(string|array $id, ?string $position = null): static
    {
        $ids = (array) $id;
        foreach ($ids as $oneId) {
            if ($position === null) {
                // файл каскадной таблицы стилей поставщика (vendor)
                unset($this->vendorFiles[ClientScript::POS_HEAD][$oneId], $this->vendorFiles[ClientScript::POS_BEGIN][$oneId], 
                    $this->vendorFiles[ClientScript::POS_END][$oneId], $this->vendorFiles[ClientScript::POS_READY][$oneId], 
                    $this->vendorFiles[ClientScript::POS_LOAD][$oneId]);
                // файл каскадной таблицы стилей темы
                unset($this->themeFiles[ClientScript::POS_HEAD][$oneId], $this->themeFiles[ClientScript::POS_BEGIN][$oneId], 
                    $this->themeFiles[ClientScript::POS_END][$oneId], $this->themeFiles[ClientScript::POS_READY][$oneId], 
                    $this->themeFiles[ClientScript::POS_LOAD][$oneId]);
                // стилевые инструкции
                unset($this->styles[ClientScript::POS_HEAD][$oneId], $this->styles[ClientScript::POS_BEGIN][$oneId], 
                    $this->styles[ClientScript::POS_END][$oneId], $this->styles[ClientScript::POS_READY][$oneId], 
                    $this->styles[ClientScript::POS_LOAD][$oneId]);
            } else {
                // файл каскадной таблицы стилей поставщика (vendor)
                unset($this->vendorFiles[$position][$oneId]);
                // файл каскадной таблицы стилей темы
                unset($this->themeFiles[$position][$oneId]);
                // стилевые инструкции
                unset($this->styles[$position][$oneId]);
            }
        }
        return $this;
    }

    /**
     * Возвращает комментарий.
     * 
     * @param string $name Ключ {@see Script::$comments}.
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function comment(string $name, string $indent = ''): string
    {
        return '<!-- ' . $this->comments[$name] . ' -->' . PHP_EOL . $indent;
    }

    /**
     * Возвращает подключение зарегистрированных таблиц стилей по указанной позиции в 
     * HTML-документе.
     * 
     * @param array<string, array> $items Зарегистрированные таблицы стилей по позициям
     *     в HTML-документе.
     * @param string $position Позиция подключаемых таблиц стилей в HTML-документе 
     *     {@see ClientScript} (по умолчанию 'head').
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     *
     * @return string
     */
    protected function _renderFiles(array $items, string $position = ClientScript::POS_HEAD, string $indent = ''): string
    {
        if (empty($items[$position])) return '';

        $links = '';
        $files = $items[$position];
        foreach ($files as $id => $attributes) {
            $links .= '<link' . Html::renderTagAttributes($attributes) . '/>' . PHP_EOL . $indent;
        }
        return $links;
    }

    /**
     * Возвращает зарегистрированные стилевые инструкции по указанной позиции в HTML-документе.
     * 
     * @param array<string, array> $items Зарегистрированные стилевые инструкции по 
     *     позициям в HTML-документе.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript}
     *     (по умолчанию 'head').
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     *
     * @return string
     */
    protected function _renderStyles($items, string $position = ClientScript::POS_HEAD, string $indent = ''): string
    {
        if (empty($items[$position])) return '';

        $text = '';
        $styles = $items[$position];
        foreach ($styles as $id => $style) {
            $text .= '<style' . Html::renderTagAttributes($style['attributes']) . '>' . PHP_EOL 
                   . $indent . $style['content'] . PHP_EOL . $indent . '</style>' . PHP_EOL . $indent;
        }
        return $text;
    }

    /**
     * Возвращает текст зарегистрированных таблиц стилей по указанной позиции в HTML-документе.
     * 
     * @param string $position Позиция подключаемых таблиц стилей в HTML-документе 
     *     {@see ClientScript} (по умолчанию 'head').
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     *
     * @return string
     */
    public function renderFiles(string $position = ClientScript::POS_HEAD, string $indent = ''): string
    {
        $linkVendorFiles = $this->_renderFiles($this->vendorFiles, $position, $indent);
        $linkFiles       = $this->_renderFiles($this->files, $position, $indent);
        $linkThemeFiles  = $this->_renderFiles($this->themeFiles, $position, $indent);
        $linkStyles      = $this->_renderStyles($this->styles, $position, $indent);

        $links = '';
        if ($this->renderComments && $linkVendorFiles) {
            $links .= $this->comment('vendorFiles', $indent);
        }

        $links .= $linkVendorFiles;
        if ($this->renderComments && $linkFiles) {
            $links .= $this->comment('files', $indent);
        }

        $links .= $linkFiles;
        if ($this->renderComments && $linkThemeFiles) {
            $links .= $this->comment('themeFiles', $indent);
        }
        $links .= $linkThemeFiles;

        if ($linkStyles) {
            $links .= $linkStyles;
        }
        return $links;
    }
}
