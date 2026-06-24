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
 * Вспомогательный класс формирования JavaScript для HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Script implements HelperInterface
{
    /**
     * Массив подключаемых файлов скрипта.
     * 
     * @var array
     */
    protected array $files = [];

    /**
     * Массив подключаемых JS файлов "ядра" (themes/assets/vendors/).
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
     * Массив подключаемых скриптов.
     * 
     * @var array
     */
    protected array $scripts = [];

    /**
     * Базовый (абсолютный) URL к компонентам библиотек.
     * 
     * Имеет вид: "<абсолютный путь к приложению/> <локальный путь к темам/> <VENDORS_PATH/>".
     * 
     * @var string
     */
    public string $vendorUrl = '';

    /**
     * Базовый (абсолютный) URL к темам.
     * 
     * Имеет вид: "<абсолютный URL приложения/> <локальный путь к темам/>".
     * 
     * @var string
     * @see Ge\Theme\Theme::$baseUrl
     */
    public string $themeUrl = '';

    /**
     * Выводить комментарии.
     * 
     * @var bool
     */
    public bool $renderComments = true;

    /**
     * Комментарии.
     * 
     * @var array
     */
    public array $comments = [
        'vendorFiles' => 'vendors JS',
        'files'       => 'theme vendors JS',
        'themeFiles'  => 'theme JS'
    ];

    /**
     * Разделитель скрипта при регистрации их с одним идентификатором.
     * 
     * @see Script::appendScript()
     * 
     * @var string
     */
    public string $scriptSeparator = PHP_EOL;

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
        $this->scripts = ClientScript::definePositions();
        $this->vendorUrl = Ge::$app->clientScript->vendorUrl;
        $this->themeUrl = Ge::$app->theme->url;
    }

    /**
     * Регистрирует (добавляет) файл скрипта в HTML-документ.
     * 
     * Если указана позиция {@see ClientScript::POS_HEAD}, скрипт будет добавлен в 
     * метаданные HTML-документа.
     * 
     * Пример:
     * ```php
     * $script->registerFile('/assets/js/foobar.js', 'foobar.js', ClientScript::POS_HEAD, ['defer' => '']);
     * ```
     * 
     * @param string $id Идентификатор файла скрипта в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     * @param string $filename Имя файла скрипта. 
     *     Пример '/assets/js/foobar.js', результат:
     *     - для frontend 'https://domain/themes/имя_темы/assets/js/foobar.js';
     *     - для backend 'https://domain/themes/backend/имя_темы/assets/js/foobar.js'.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['src'] = ClientScript::defineSrc($this->themeUrl, $filename);
        $this->files[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл скрипта поставщика (vendor) в HTML-документ.
     * 
     * Если указана позиция {@see ClientScript::POS_HEAD}, скрипт будет добавлен в 
     * метаданные HTML-документа.
     * 
     * Пример:
     * ```php
     * $script->registerVendorFile('/bootstrap/js/bootstrap.min.js', 'bootstrap.min.js', ClientScript::POS_HEAD, ['defer' => '']);
     * ```
     * 
     * @param string $id Идентификатор файла скрипта поставщика (vendor) в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     * @param string $filename Имя файла из библиотеки (vendor) скриптов. 
     *     Пример '/bootstrap/js/bootstrap.min.js' (результат 'https://domain/vendors/bootstrap/js/bootstrap.min.js').
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerVendorFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['src'] = ClientScript::defineSrc($this->vendorUrl, $filename);
        $this->vendorFiles[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл скрипта текущей темы (theme) в HTML-документ.
     * 
     * Если указана позиция {@see ClientScript::POS_HEAD}, скрипт будет добавлен в 
     * метаданные HTML-документа.
     * 
     * Пример:
     * ```php
     * $script->registerThemeFile('/assets/js/main.min.js', 'main.min.js', ClientScript::POS_HEAD, ['defer' => '']);
     * ```
     * 
     * @param string $id Идентификатор файла скрипта темы в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     * @param string $filename Имя файла скрипта текущей темы. 
     *     Пример '/assets/js/main.min.js' (результат 'https://domain/themes/имя_темы/assets/js/main.min.js').
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerThemeFile(string $id, string $filename, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $attributes['src'] = ClientScript::defineSrc($this->themeUrl, $filename);
        $this->themeFiles[$position][$id] = $attributes;
        return $this;
    }

    /**
     * Отменяет регистрацию файл скрипта.
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
     * Отменяет регистрацию файл скрипта поставщика (vendor).
     *
     * @param string $id Идентификатор файла скрипта поставщика в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор файла скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterVendorFile(string $id, ?string $position = null): static
    {
        if ($position === null) {
            unset($this->vendorFiles[ClientScript::POS_HEAD][$id], $this->vendorFiles[ClientScript::POS_BEGIN][$id], $this->vendorFiles[ClientScript::POS_END][$id], 
                $this->vendorFiles[ClientScript::POS_READY][$id], $this->vendorFiles[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->vendorFiles[$position][$id]);
        return $this;
    }

    /**
     * Отменяет регистрацию файл скрипта темы (theme).
     *
     * @param string $id Идентификатор файла скрипта темы в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор файла скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterThemeFile(string $id, ?string $position = null): static
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
     * Регистрирует (добавляет) скрипт в HTML-документ.
     * 
     * Пример:
     * ```php
     * $script->registerScript('documentReady', '$(document).ready(function() { ... }', ClientScript::POS_END);
     * ```
     * 
     * @param string $id Идентификатор скрипта в очереди. 
     *     Идентификатор даёт возможность для последующего его изменения.
     * @param string $script Текст скрипта.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerScript(string $id, string $script, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->scripts[$position][$id] = ['content' => $script, 'attributes' => $attributes];
        return $this;
    }

    /**
     * Отменяет регистрацию скрипта.
     *
     * @param string $id Идентификатор скрипта в очереди.
     * @param string|null $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию `null`). Если значение `null`, то идентификатор скрипта 
     *     будет удалён из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregisterScript(string $id, ?string $position = null): static
    {
        if ($position === null) {
            unset($this->scripts[ClientScript::POS_HEAD][$id], $this->scripts[ClientScript::POS_BEGIN][$id], 
                $this->scripts[ClientScript::POS_END][$id], $this->scripts[ClientScript::POS_READY][$id], 
                $this->scripts[ClientScript::POS_LOAD][$id]);
        } else
            unset($this->scripts[$position][$id]);
        return $this;
    }

    /**
     * Добавляет текста скрипта уже к зарегистрированному скрипту.
     *
     * @param string $id Идентификатор скрипта в очереди.
     * @param string $script Текста скрипта.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript} 
     *     (по умолчанию 'head').
     * 
     * @return $this
     */
    public function appendScript(string $id, string $script, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        if (isset($this->scripts[$position][$id]))
            $this->scripts[$position][$id]['content'] = $this->scripts[$position][$id]['content'] . $this->scriptSeparator . $script;
        else
            $this->scripts[$position][$id] = ['content' => $script, 'attributes' => $attributes];
        return $this;
    }

    /**
     * Отменяет регистрацию текста или файлов скрипта.
     *
     * @param string|array $id Идентификатор(ы) текста или файла(ов) скрипта в очереди.
     * @param string|null $position Позиция текста или файлов скрипта в HTML-документе 
     *     {@see ClientScript} (по умолчанию `null`). Если значение `null`, то 
     *     идентификаторы будут удалены из всех позиций очередей.
     * 
     * @return $this
     */
    public function unregister(string|array $id, ?string $position = null): static
    {
        $ids = (array) $id;
        foreach ($ids as $oneId) {
            if ($position === null) {
                // файл скрипта поставщика (vendor)
                unset($this->vendorFiles[ClientScript::POS_HEAD][$oneId], $this->vendorFiles[ClientScript::POS_BEGIN][$oneId], 
                    $this->vendorFiles[ClientScript::POS_END][$oneId], $this->vendorFiles[ClientScript::POS_READY][$oneId], 
                    $this->vendorFiles[ClientScript::POS_LOAD][$oneId]);
                // файла скрипта темы
                unset($this->themeFiles[ClientScript::POS_HEAD][$oneId], $this->themeFiles[ClientScript::POS_BEGIN][$oneId], 
                    $this->themeFiles[ClientScript::POS_END][$oneId], $this->themeFiles[ClientScript::POS_READY][$oneId], 
                    $this->themeFiles[ClientScript::POS_LOAD][$oneId]);
                // текст скрипта
                unset($this->styles[ClientScript::POS_HEAD][$oneId], $this->styles[ClientScript::POS_BEGIN][$oneId], 
                    $this->styles[ClientScript::POS_END][$oneId], $this->styles[ClientScript::POS_READY][$oneId], 
                    $this->styles[ClientScript::POS_LOAD][$oneId]);
            } else {
                // файл скрипта поставщика (vendor)
                unset($this->vendorFiles[$position][$oneId]);
                // файла скрипта темы
                unset($this->themeFiles[$position][$oneId]);
                // текст скрипта
                unset($this->styles[$position][$oneId]);
            }
        }
        return $this;
    }

    /**
     * Выводит сообщение в консоль браузера.
     * 
     * @param string $type Тип сообщения, например: 'log', 'error', 'warn', 'table', 'dir'.
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public function console(string $type, string $message, array $vars): static
    {
        $args = [];
        if ($message)
            $args[] = "'$message'";

        foreach ($vars as $var) {
            if (is_array($var))
                $args[] = json_encode($var);
            else
            if (is_null($var))
                $args[] = 'null';
            else
            if (is_bool($var))
                $args[] = $var ? 'true' : 'false';
            else
            if (is_string($var))
                $args[] = "'$var'";
        }

        $script = 'console.' . $type .'(' . implode(', ', $args) . ')';
        $this->scripts[ClientScript::POS_END][md5($script)] = ['content' => $script, 'attributes' => []];
        return $this;
    }

    /**
     * Выводит сообщение в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/log_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public function consoleLog(string $message, mixed ...$vars): static
    {
        return $this->console('log', $message, $vars);
    }

    /**
     * Выводит сообщения, содержащие некоторую информацию, в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/info_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public function consoleInfo(string $message, mixed ...$vars): static
    {
        return $this->console('info', $message, $vars);
    }

    /**
     * Выводит ошибку в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/error_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public function consoleError(string $message, mixed ...$vars): static
    {
        return $this->console('error', $message, $vars);
    }

    /**
     * Выводит предупреждение в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/warn_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public function consoleWarn(string $message, mixed ...$vars): static
    {
        return $this->console('warn', $message, $vars);
    }

    /**
     * Выводит в консоли браузера все свойства JavaScript объекта.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/dir_static
     * 
     * @param mixed $object JavaScript-объект, свойства которого нужно вывести.
     * @param array<string, mixed> $options Настройки вывода.
     * 
     * @return $this
     */
    public function consoleDir(mixed $object, array $options = []): static
    {
        $args = [$object];
        if ($options) {
            $args[] = $options;
        }
        return $this->console('dir', '', $args);
    }

    /**
     * Выводит в консоль браузера набор данных в виде таблицы.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/table_static
     * 
     * @param array<int, array<string, mixed>> $rows Набор данных.
     * 
     * @return $this
     */
    public function consoleTable(array $rows): static
    {
        return $this->console('table', '', [$rows]);
    }

    /**
     * Возвращает комментарий в виде HTML.
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
     * Возвращает подключение зарегистрированных скриптов по указанной позиции в HTML-документе.
     * 
     * @param array<string, array> $items Зарегистрированные скрипты по позициям в HTML-документе.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript}
     *     (по умолчанию 'head').
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
            $links .= '<script' . Html::renderTagAttributes($attributes) . '></script>' . PHP_EOL . $indent;
        }
        return $links;
    }

    /**
     * Возвращает текст зарегистрированных скриптов по указанной позиции в HTML-документе.
     * 
     * @param array<string, array> $items Зарегистрированные скрипты по позициям в HTML-документе.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript}
     *     (по умолчанию 'head').
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     *
     * @return string
     */
    protected function _renderScripts($items, string $position = ClientScript::POS_HEAD, string $indent = ''): string
    {
        if (empty($items[$position])) return '';

        $text = '';
        $scripts = $items[$position];
        foreach ($scripts as $id => $script) {
            $text .= '<script' . Html::renderTagAttributes($script['attributes']) . '>' . PHP_EOL 
                   . $indent . $script['content'] . PHP_EOL . $indent . '</script>' . PHP_EOL . $indent;
        }
        return $text;
    }

    /**
     * Возвращает подключение всех зарегистрированных скриптов по указанной позиции в HTML-документе.
     * 
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see ClientScript}
     *     (по умолчанию 'head').
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     *
     * @return string
     */
    public function renderFiles(string $position = ClientScript::POS_HEAD, string $indent = ''): string
    {
        $linkVendorFiles = $this->_renderFiles($this->vendorFiles, $position, $indent);
        $linkFiles       = $this->_renderFiles($this->files, $position, $indent);
        $linkThemeFiles  = $this->_renderFiles($this->themeFiles, $position, $indent);
        $linkScripts     = $this->_renderScripts($this->scripts, $position, $indent);

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

        if ($linkScripts) {
            $links .= $linkScripts;
        }
        return $links;
    }
}
