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
use Ge\Site\Page;
use Ge\View\BaseView;

/**
 * Класс представления в шаблоне MVC.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class View extends BaseView
{
    /**
     * {@inheritdoc}
     */
    public string $renderer = 'php';

    /**
     * Страница.
     * 
     * @var Page
     */
    public Page $page;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (!isset($this->page)) {
            $this->page = Ge::$services->getAs('page');
        }
    }

    /**
     * Устанавливает заголовок HTML-странице.
     * 
     * @return void
     */
    public function title(string $title): void
    {
        $this->script->title = $title;
    }
    
    /**
     * Регистрирует (добавляет) стилевые инструкции в HTML-документ.
     * 
     * @see \Ge\View\Helper\Stylesheet::appendStyle()
     * 
     * @param string $style Стилевые инструкции.
     * @param string $position Позиция стилевых инструкций в HTML-документе {@see \Ge\View\ClientScript} 
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
        $this->script->css->appendStyle($id ?? $position, $style, $position, $attributes);
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
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return void
     */
    public function registerCssFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): void
    {
        $this->script->css->registerFile($id ?? $filename, $filename, $position, $attributes);
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
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return void
     */
    public function registerVCssFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): void
    {
        $this->script->css->registerVendorFile($id ?? $filename, $filename, $position, $attributes);
    }

    /**
     * Регистрирует (добавляет) файл каскадной таблицы стилей модуля в HTML-документ.
     * 
     * @see \Ge\View\Helper\Stylesheet::registerFile()
     * 
     * @param string|null $id Идентификатор файла каскадной таблицы стилей модуля 
     *     в очереди. Идентификатор даёт возможность для последующего его 
     *     изменения. Если значение `null`, тогда он будет иметь значение имя 
     *     файла `$filename`.
     * @param string $filename Имя файла каскадной таблицы стилей модуля. 
     *     Пример '/css/bootstrap.min.css' (результат 'https://domain/modules/имя_модуля/assets/css/bootstrap.min.css').
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега link (по умолчанию `[]`).
     * 
     * @return void
     */
    public function registerMCssFile(string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): void
    {
        $this->script->css->registerFile($id ?? $filename,  $this->module->getAssetsUrl() . $filename, $position, $attributes);
    }

    /**
     * Регистрирует (добавляет) скрипт в HTML-документ.
     * 
     * @see \Ge\View\Helper\Script::appendScript()
     * 
     * @param string $script Текст скрипта.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
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
        $this->script->js->appendScript($id ?? $position, $script, $position, $attributes);
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
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerJsFile( string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->script->js->registerFile($id ?? $filename, $filename, $position, $attributes);
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
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerVJsFile( string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->script->js->registerVendorFile($id ?? $filename, $filename, $position, $attributes);
        return $this;
    }

    /**
     * Регистрирует (добавляет) файл скрипта модуля в HTML-документ.
     * 
     * @see \Ge\View\Helper\Script::registerFile()
     * 
     * @param string $filename Имя файла скрипта. 
     *     Пример '/js/foobar.js', результат: 'https://domain/modules/имя_модуля/assets/js/foobar.js'.
    * @param string|null $id Идентификатор файла скрипта модуля 
     *     в очереди. Идентификатор даёт возможность для последующего его изменения. 
     *     Если значение `null`, тогда он будет иметь значение имя файла `$filename`.
     * @param string $position Позиция подключаемого скрипта в HTML-документе {@see \Ge\View\ClientScript} 
     *     (по умолчанию 'head').
     * @param array $attributes Атрибуты тега script (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function registerMJsFile( string $filename, ?string $id = null, string $position = ClientScript::POS_HEAD, array $attributes = []): static
    {
        $this->script->js->registerFile($id ?? $filename, $this->module->getAssetsUrl() . $filename, $position, $attributes);
        return $this;
    }

    /**
     * Добавляет HTML-метатег в HTML-документ.
     * 
     * @param string $name Значение атрибута `name` (имя) HTML-метатега.
     * @param string $content Значение атрибута `content` (содержимое) для `http-equiv` или атрибута 
     *     `name`, в зависимости от контекста.
     * 
     * @see \Ge\View\Helper\Meta::setName()
     * 
     * @return $this
     */
    public function meta(string $name, string $content): static
    {
        $this->script->meta->setName($name, $content);
        return $this;
    }

    /**
     * Регистрация пакетов ресурсов.
     *
     * @param string|array $name Имя или имена пакетов ресурсов, которые необходимо 
     *     зарегистрировать.
     * 
     * @return $this
     * 
     * @throws \Ge\Exception\BadMethodCallException Ошибка вызова метода.
     */
    public function registerAsset($name): static
    {
        $this->theme->getAsset()->register($name);
        return $this;
    }
}
