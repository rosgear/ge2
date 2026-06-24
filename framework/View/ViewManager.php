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
use Ge\View\View;
use Ge\View\Exception;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Module\Module;
use Ge\Mvc\Extension\Extension;

/**
 * Менеджер представлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class ViewManager extends BaseObject
{
    /**
     * Модуль или расширение, которому принадлежит представление.
     * 
     * Если значение не установлено, то будет применяться текущий модуль или расширение 
     * приложения.
     * 
     * @var Module|Extension
     */
    public Module|Extension $module;

    /**
     * Идентификатор или шаблон атрибута идентификатора (виджета, плагина и т.д.), 
     * который должен быть уникальным для всего HTML-документа.
     * 
     * Указывается исключительно для рендера: виджетов, плагинов и т.д.
     * Пример: 'g-mymodule-{name}' или 'g-mymodule', где {name} - имя 
     * расширения ('g-mymodule-grid', 'g-mymodule-form'...).
     * 
     * @var string
     */
    public string $id = '';

    /**
     * Автозагрузка шаблонов из директории тем.
     * Если - false, шаблоны загружаются из директории модуля.
     * 
     * @var bool
     */
    public bool $useTheme = false;

    /**
     * Использовать локализацию в определении файла шаблона.
     * 
     * Определении файла шаблона происходит если {@see Ge\View\ViewManager::$useTheme} true
     * и файл шаблона с локализацией находится в директории темы.
     * 
     * @var bool
     */
    public bool $useLocalize = false;

    /**
     * Использовать шорткоды в шаблоне представления.
     * 
     * @var bool
     */
    public bool $useShortcodes = false;

    /**
     * Карта имён или параметров конфигурации представлений.
     * 
     * Пример:
     * ```php
     * [
     *     'index'    => '/index.phtml',
     *     'settings' => '/settings.json',
     *     'header'   => [
     *         'viewFile'    => '/partials/header.phtml',
     *         'useTheme'    => true,
     *         'useLocalize' => true
     *     ],
     * ]
     * ```
     * 
     * @var array
     */
    public array $viewMap = [];

    /**
     * Представления в виде пар "имя - объект".
     * 
     * @see ViewManager::getView()
     * 
     * @var array
     */
    protected array $views = [];

    /**
     * {@inheritdoc}
     * 
     * @param \Ge\Mvc\Module\BaseModule|null $module Модуль, которому принадлежит 
     *     представление. (по умолчанию `null`).
     */
    public function __construct(array $config = [], Module|Extension|null $module = null)
    {
        parent::__construct($config);

        $this->module = $module ?: Ge::module();
    }

    /**
     * Возвращает параметры конфигурации унаследованные от менеджера представлений.
     * 
     * @param array $config Параметры конфигурации представления (по умолчанию `[]`).
     * 
     * @return array
     */
    public function inheritConfig(array $config = []): array
    {
        $parent = [
            'useLocalize'   => $this->useLocalize,
            'useTheme'      => $this->useTheme,
            'useShortcodes' => $this->useShortcodes
        ];
        return $config ? array_merge($parent, $config) : $config;
    }

    /**
     * Возвращает параметры конфигурации представления (включая имя представления или 
     * файла).
     * 
     * Параметры конфигурации представления наследуются от менеджера представлений 
     * в {@see ViewManager::inheritConfig()}.
     * 
     * @param string $name Имя представления в карте имён.
     * 
     * @return string|null Возвращает значение `null`, если карта имён не имеет имя.
     */
    public function getViewConfig(string $name): ?array
    {
        if (isset($this->viewMap[$name])) {
            $config = $this->viewMap[$name];
            return $this->inheritConfig(is_string($config) ? ['viewFile' => $config] : $config);
        }
        return null;
    }

    /**
     * Возвращает имя представления или файла.
     * 
     * @param string $name Имя представления в карте имён.
     * 
     * @return string|null Возвращает значение `null`, если карта имён не имеет имя.
     */
    public function getViewFile(string $name): ?string
    {
        $config = $this->viewMap[$name] ?? null;
        if (is_array($config)) {
            return $config['viewFile'] ?? null;
        }
        return $config;
    }

    /**
     * Возвращает представление по указанному имени.
     * 
     * Возвращаемое представление будет уже иметь имя файла {@see View::$viewFile}.
     * 
     * @param string $name Имя представления в карте имён.
     * 
     * @return View
     * 
     * @throws Exception\TemplateNotFoundException Невозможно получить имя из 
     *     карты имён представлений.
     */
    public function getView(string $name): View
    {
        if (!isset($this->views[$name])) {
            /** @var array|null Параметры конфигурации представления */
            $viewConfig = $this->getViewConfig($name);
            if ($viewConfig === null || empty($viewConfig['viewFile'])) {
                throw new Exception\TemplateNotFoundException( // Невозможно получить имя из карты имен представлений
                    Ge::t('app', 'Cannot get name "{0}" from view map', [$name]), $name
                );
            }
            return $this->views[$name] = new View($viewConfig);
        }
        return $this->views[$name];
    }

    /**
     * Возвращает визуализацию представления и применяет макет, если он доступен.
     *
     * - например '@app:views/site/index';
     * - абсолютный путь в приложении, например '//site/index'. 
     * Здесь имя представления начинается с двойной косой черты. Файл представления 
     * будет иметь аболютный путь {@see \Ge\Mvc\Application::$viewPath} приложения.
     * - абсолютный путь внутри модуля, например '/site/index'.
     * Здесь имя представления начинается с одной косой черты. Файл представления будет 
     * иметь аболютный путь {@see \Ge\Mvc\Module\BaseModule::$viewPath} модуля.
     * - относительный путь, например 'index'. 
     * Файл представления будет иметь аболютный путь {@see BaseController::$viewPath}.
     * 
     * Для визуализации представления макета используется метод
     * {@see \Ge\Mvc\Controller\BaseController::renderContent()}.
     *
     * @see ViewManager::getView()
     * 
     * @param string $name Имя представления в карте имён.
     * @param array $params Параметры в виде пары "имя-значение", которые будут переданы 
     *     в представление. Эти параметры не будут доступны в макете (по умолчанию `[]`).
     * 
     * @return string Результат визуализации представления.
     * 
     * @throws Exception\TemplateNotFoundException Невозможно получить имя из 
     *     карты имён представлений.
     */
    public function render(string $name, array $params = []): string
    {
        $view = $this->getView($name);
        $content = $view->render($view->viewFile, $params, $this->module);
        return $this->module->controller->renderContent($content);
    }

    /**
     * Возвращает визуализацию представления без применения макета.
     * 
     * @see ViewManager::getView()
     * 
     * @param string $name Имя представления в карте имён.
     * @param array $params Параметры в виде пары "имя-значение", которые будут переданы 
     *     в представление (по умолчанию `[]`).
     * 
     * @return string Результат визуализации представления.
     * 
     * @throws Exception\TemplateNotFoundException Невозможно получить имя из 
     *     карты имён представлений.
     */
    public function renderPartial(string $name, array $params = []): string
    {
        $view = $this->getView($name);
        return $view->render($view->viewFile, $params, $this->module);
    }
}
