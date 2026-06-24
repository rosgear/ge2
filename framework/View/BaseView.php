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
use Ge\Helper\Str;
use Ge\Helper\Html;
use Ge\Theme\Theme;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\Collection;
use Ge\Mvc\Module\Module;
use Ge\Mvc\Extension\Extension;
use Ge\Exception\NotDefinedException;
use Ge\View\ClientScript;
use Ge\View\Renderer\AbstractRenderer;

/**
 * Базовый класс представления в шаблоне MVC.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class BaseView extends BaseObject
{
    /**
     * Модуль, в представление которого, будет выводиться содержимое виджета.
     * 
     * Если значение не установлено, то будет использоваться текущий модуль приложения
     * или его расширение.
     * 
     * @var Module|Extension|null
     */
    public Module|Extension|null $module;

    /**
     * Список визуализаторов представлений.
     * 
     * Если используется файл шаблона с расширением ".php" или ".phtml" будет применяться 
     * вывод шаблона с помощью {@see BaseView::renderPhpFile()}. Иначе, используется 
     * визуализаторов из списка, соответствующий расширению файла.
     * 
     * Пример списка:
     * ```php
     * [
     *     'json' => ['class' => '\Ge\View\Renderer\JsonRenderer'],
     *     // ...
     * ]
     * ```
     * 
     * @var array
     */
    public array $renderers = [];

    /**
     * Имя визуализатора представления.
     * 
     * @see BaseView::getRenderer()
     * 
     * @var string
     */
    public string $renderer = '';

    /**
     * Выполнять поиск файла шаблона представления в каталоге темы.
     * 
     * Если значение `false`, поиск файла шаблона представления в каталоге модуля.
     * 
     * @var bool
     */
    public bool $useTheme = false;

    /**
     * Выполнять поиск файла шаблона представления с локализацией.
     * 
     * @var bool
     */
    public bool $useLocalize = false;

    /**
     * Принудительное (строгое) изменение имени шаблона.
     * 
     * В имя шаблона будет подставлен префикс локализации, независимо от того,
     * является ли выбранный язык языком по умолчанию или нет.
     * 
     * Если значение `false`, то для выбранного языка (если он язык по умолчанию) 
     * изменение в имени шаблона не выполняется.
     * 
     * Применяется только при `$useLocalize = true`.
     * 
     * @var bool
     */
    public bool $forceLocalize = false;

    /**
     * Использовать шорткоды в шаблоне представления.
     * 
     * @var bool
     */
    public bool $useShortcodes = false;

    /**
     * Тема используемая в шаблоне представления.
     * 
     * Если значение `null`, будет использоваться текущая тема.
     * 
     * @var Theme
     */
    public Theme $theme;

    /**
     * Расширение файла шаблона представления.
     * 
     * @var string
     */
    public string $defaultExtension = 'phtml';

    /**
     * Обработчик содержимого в визуализаторе представления.
     * 
     * @see BaseView::renderFile()
     * 
     * @var mixed
     */
    public mixed $contentHandler = null;

    /**
     * Скрипты клиента.
     * 
     * Если значение не установлено, то будет использоваться текущий скрипт клиента.
     * 
     * @var ClientScript
     */
    public ClientScript $script;

    /**
     * Текущий язык приложения, является языком по умолчанию.
     * 
     * @see \Ge\Language\Language::isDefault()
     * 
     * @var bool
     */
    public bool $isDefaultLanguage = false;

    /**
     * Переменные приложения используемые представлением.
     * 
     * @see \Ge\Mvc\Application::$params
     * 
     * @var Collection
     */
    public Collection $params;

    /**
     * Выполнять разметку компонентов представления.
     * 
     * @var bool
     */
    public bool $markupEnabled;

    /**
     * Класс CSS разметки блока.
     * 
     * @var string
     */
    public string $markupBlockCls = 'gm-markup-block';

    /**
     * Класс CSS разметки компонента.
     * 
     * @var string
     */
    public string $markupComponentCls = 'gm-markup-cmp';

    /**
     * Приставка к идентификатору разметки блока или компонента.
     * 
     * @var string
     */
    public string $markupPrefixId = 'gm-markup-';

    /**
     * Идентификаторы блоков, используемые для получения содержимого при начале записи 
     * блока и его окончания.
     * 
     * @see BaseView::beginBlock()
     * @see BaseView::endBlock()
     * 
     * @var array
     */
    public static array $blocks = [];

    /**
     * Содержимое с соответствующими ему идентификаторами.
     * 
     * Используется виджетами или самим представлением при получении шаблона.
     * 
     * @see BaseView::getBlock()
     * 
     * @var array
     */
    public static array $content = [];

    /**
     * Карта имён представлений и их шаблонов, полученные в процессе визуализации 
     * содержимого представлений.
     * 
     * Используется для определения имён файлов шаблонов при каскадной визуализации 
     * (вызов метода `render` внутри шаблона) представлений.
     * 
     * Имеет вид: `[['viewFile' => '...', 'filename' => '...', 'path' => '...'], ...]`.
     * 
     * @see BaseView::renderFrom()
     * 
     * @var array
     */
    protected array $mapFiles = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);

        if (!isset($this->module)) {
            $this->module = Ge::module();
        }
        if (!isset($this->theme)) {
            $this->theme = Ge::theme();
        }
        if (!isset($this->markupEnabled)) {
            $this->markupEnabled = Ge::$app->isViewMarkup();
        }
        if (!isset($this->script)) {
            $this->script = Ge::$services->getAs('clientScript');
        }
        if (!isset($this->params)) {
            $this->params = Ge::$app->params;
        }
        $this->isDefaultLanguage = Ge::$app->language->isDefault();
    }

    /**
     * Переконфигурировать настройки представления.
     * 
     * @param array $config Новый параметры конфигурации представления.
     * 
     * @return void
     */
    public function reconfigure(array $config = []): void
    {
        foreach ($config as $name => $value) {
            // deprecated PHP 8.2 (creation of dynamic property)
            @$this->$name = $value;
        }
    }

    /**
     * Начинает разметку блока.
     *
     * @param string $blockId Уникальный идентификатор блока разметки.
     * @param string $title Заголовок.
     * @param string $viewFile Файл шаблона.
     * 
     * @return string
     */
    public function beginMarkupBlock(string $blockId, string $title = '', string $viewFile = ''): string 
    {
        $markupId = $this->markupPrefixId . $blockId;
        return sprintf(
            '<script>Ge.Markup.addBlock(\'%s\', {"callId":"%s","title":"%s","calledFrom":"%s"});</script>' . PHP_EOL . '<div%s>',
            $markupId,
            $blockId,
            $title ?: ucfirst($blockId),
            $viewFile,
            Html::renderTagAttributes([
                'id'    => $markupId, 
                'class' => $this->markupBlockCls
            ])
        );
    }

    /**
     * Начинает разметку компонента (виджета, модуля, расширения модуля).
     *
     * @param MarkupViewInterface $component Компонент с интерфейсом разметки.
     * 
     * @return string
     */
    public function beginMarkupComponent(MarkupViewInterface $component): string 
    {
        $options = $component->getMarkupOptions();
        // параметры разметки
        $markupParams = [
            'type'       => $options['type'] ?? '', // вид компонента: widget, module, extension
            'dataId'     => $options['dataId'] ?? '', // идент. в базе данных записи выводимой в шаблоне
            'registryId' => $options['registryId'] ?? '', // идент. компонента в реестре 
            'control'    => $options['control'] ?? null, // управление записью компонента
            'menu'       => $options['menu'] ?? [], // меню управления компонентом
        ];
        // уникальный идентификатор компонента
        $uniqueId = $options['uniqueId'] ?? '';
        return sprintf(
            "<script>Ge.Markup.addCmp('%s', '%s');</script>" . PHP_EOL . '<div%s>',
            $uniqueId,
            json_encode(
                $markupParams, 
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
            ),
            Html::renderTagAttributes([
                'id'    => $uniqueId, 
                'class' => $this->markupComponentCls, 
                'title' => $options['title'] ?? ''
            ])
        );
    }

    /**
     * Начинает разметку блока или компонента (виджета, модуля, расширения модуля).
     *
     * @param MarkupViewInterface|string $element Уникальный идентификатор блока 
     *     разметки или компонент.
     * @param string $title Заголовок разметки.
     * 
     * @return string
     */
    public function beginMarkup(MarkupViewInterface|string $element, string $title = ''): string
    {
        if (!$this->markupEnabled) return '';

        // если идентификатор
        if (is_string($element)) {
            $trace = debug_backtrace();
            return $this->beginMarkupBlock($element, $title, $this->theme->viewFileToFilePath($trace[0]['file']  ?? ''));
        } else
        // если компонент
        if ($element instanceof MarkupViewInterface)
            return $this->beginMarkupComponent($element);
        else
            return '';
    }

    /**
     * Заканчивает разметку блока или компонента (виджета, модуля, расширения модуля).
     * 
     * @see BaseView::beginMarkup()
     * 
     * @return string
     */
    public function endMarkup(): string
    {
        return $this->markupEnabled ? '</div>' : '';
    }

    /**
     * Выводит содержимое шорткода с помощью менеджера шорткодов.
     * 
     * @see \Ge\Shortcode\ShortcodeManager::process()
     * 
     * @return string Шорткод.
     */
    public function shortcode(string $shortcode): string
    {
        return Ge::$app->shortcodes->process($shortcode);
    }

    /**
     * Выводит содержимое установленного виджета с помощью менеджера виджетов.
     * 
     * @see \Ge\WidgetManager\WidgetManager::get()
     * 
     * @param string $uniqueId Уникальный идентификатор виджета. 
     *     Например: 'rg.menu', 'rg.menu:top'.
     * @param array $params Параметры виджета (по умолчанию `[]`).
     * 
     * @return string Возвращает содержимое установленного виджета.
     */
    public function widget(string $uniqueId, array $params = [], bool $markup = true): string
    {
        if ($this->markupEnabled) {
            $trace = debug_backtrace();
            // устанавливаем виджету шаблон откуда он был вызван
            $params['calledFromViewFile'] = $this->theme->viewFileToFilePath($trace[0]['file']  ?? '');
        }

        /** @var \Ge\WidgetManager\WidgetManager $manager */
        $manager = Ge::$app->widgets;

        // если выполняется разметка, то всегда отображать виджет
        if ($this->markupEnabled)
            $enabled = true;
        else {
            $widgetId = $manager->getWidgetId($uniqueId);
            $enabled = $manager->getRegistry()->isEnabled($widgetId);
        }

        if ($enabled) {
            /** @var \Ge\View\BaseWidget|null $widget  */
            $widget = $manager->get($uniqueId, $params);
            /** @var string $content */
            $content = $widget ? $widget->renderMe() : '';
            // если виджет поддерживает разметку
            if ($widget instanceof MarkupViewInterface) {
                if ($this->markupEnabled && $markup) {
                    return $this->beginMarkup($widget) . $content . $this->endMarkup();
                }
            }
            return $content;
        }
        return '';
    }

    /**
     * Возвращает указатель на установленный виджет с помощью менеджера виджетов.
     * 
     * Если виджет не создан, создаст его.
     * 
     * @param string $uniqueId Уникальный идентификатор виджета. 
     *     Например: 'rg.menu', 'rg.menu:top'.
     * @param array $params Параметры виджета (по умолчанию `[]`).
     * 
     * @return mixed
     */
    public function getWidget(string $uniqueId, array $params = []): mixed
    {
        return Ge::$app->widgets->get($uniqueId, $params);
    }

    /**
     * Возвращает визуализатор.
     * 
     * @see BaseView::$renderer
     * 
     * @param null|string $name Имя визуализатора. Если значение `null`, будет 
     *     использоваться имя {@see BaseView::$renderer} (по умолчанию `null`).
     * 
     * @return AbstractRenderer
     * 
     * @throws Exception\RenderNotFoundException Визуализатор отсутствует.
     */
    public function getRenderer(?string $name = null): AbstractRenderer
    {
        if ($name === null) {
            $name = $this->renderer;
        }

        $renderer = $this->renderers[$name] ?? null;
        if ($renderer) {
            if (is_string($renderer)) {
                return $this->renderers[$name] = Ge::createObject($renderer);
            }
            return $this->renderers[$name];
        }

        throw new Exception\RenderNotFoundException(
            Ge::t('app', 'Could not render, render with name "{0}" not found', [$name]),
            $name
        );
    }

    /**
     * Возвращает имя файла представления (с путём) из по указанному имени.
     * 
     * Если представление имеет параметр:
     * - "useLocalize" со значением `true`, то результатом будет 
     * имя файла с локализацией (если файл существует). Иначе, имя 
     * файла без локализации. Пример: `view.phtml` и `view-ru_RU.phtml`.
     *  - "useTheme" со значением `true`, то результатом будет 
     * имя файла, cодержащий путь к теме. 
     * 
     * Приоритет получения имени файла представления зависит от параметров "useLocalize",
     * "useTheme", всегда выполняется очерёдность:
     * 1. Получение имени файла представления расположенного в теме;
     * 2. Получение имени файла представления из локализации.
     * 
     * @param string $viewFile Имя шаблона или файл шаблона представления.
     *     Пример: '@app/views/backend/module-info.phtml'.
     * 
     * @return false|string Возвращает значение `false`, если невозможно получить имя 
     *     файла представления.
     */
    public function getViewFile(string $viewFile): false|string
    {
        $extension = pathinfo($viewFile, PATHINFO_EXTENSION);
        if ($extension === '') {
            $viewFile = $viewFile . '.' . $this->defaultExtension;
        } else {
            $extension = $this->defaultExtension;
        }

        $moduleThemePath = $this->module ? $this->module->getThemePath() : '';

        /**
         * Получение имени файла шаблона из псевдонима "@".
         * 
         * Например, если указано "@app:views/foobar":
         * 1) если useLocalize, то "<path>/views/<side>/foobar-<locale>.phtml"
         * 2) "<path>/views/<side>/foobar.phtml"
         */
        if (strncmp($viewFile, '@', 1) === 0) {
            $filename = Ge::getAlias($viewFile);
            if ($filename === false) {
                return false;
            }
            // 1) Получение с локализацией
            if ($this->useLocalize) {
                // если язык не по умолчанию или принудительное изменение имени шаблона
                if (!$this->isDefaultLanguage || $this->forceLocalize) {
                    $filenameLoc = Str::localizeFilename($filename, null, $extension);
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
            }
            // 2) Получение без локализации
            return $filename;
        }

        /**
         * Получение имени файла шаблона из каталога приложения "//".
         * 
         * Например, если указано "//foobar":
         * 1) если useLocalize, то "<app-path>/views/<side>/foobar-<locale>.phtml"
         * 2) если useTheme, то:
         * - если useLocalize, то "<theme-path>/views/foobar-<locale>.phtml"
         * - "<theme-path>/views/foobar.phtml"
         * 3) "<app-path>/views/<side>/foobar.phtml"
         */
        if (strncmp($viewFile, '//', 2) === 0) {
            $viewFile =  ltrim($viewFile, '/');
            // 1) Каталог приложения
            // получение с каталогом приложения
            $filename = Ge::$app->getViewPath() . DS . $viewFile;
            // получение с локализацией
            if ($this->useLocalize) {
                // если язык не по умолчанию или принудительное изменение имени шаблона
                if (!$this->isDefaultLanguage || $this->forceLocalize) {
                    $filenameLoc = Str::localizeFilename($filename);
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
            }
            // 2) Каталог темы
            if ($this->useTheme) {
                // получение с каталогом темы
                $filenameTh =  $this->theme->viewPath . DS . $viewFile;
                // получение с локализацией
                if ($this->useLocalize) {
                    // если язык не по умолчанию или принудительное изменение имени шаблона
                    if (!$this->isDefaultLanguage || $this->forceLocalize) {
                        $filenameLoc = Str::localizeFilename($filenameTh);
                        if (file_exists($filenameLoc)) return $filenameLoc;
                    }
                }
                // без локализации
                if (file_exists($filenameTh)) return $filenameTh;
            }
            // 3) Без локализации
            return $filename;
        }

        // например 'foobar' => '/foobar'
        if (strncmp($viewFile, '/', 1) !== 0) {
            $viewFile = '/' . $viewFile;
        }

        /**
         * Получение имени файла шаблона из каталого модуля "/".
         * 
         * Например, если указано "/foobar":
         * 1) если useLocalize, то:
         * -  если useTheme, то "<theme-path>/views/foobar-<locale>.phtml"
         * - "<module-path>/views/foobar-<locale>.phtml"
         * 2) если useTheme, то "<theme-path>/views/foobar.phtml"
         * 3) "<module-path>/views/foobar.phtml"
         */
        // 1) Получение с локализацией (если язык не по умолчанию)
        if ($this->useLocalize) {
            // если указан язык отличный от языка по умолчанию или принудительное изменение имени шаблона
            if (!$this->isDefaultLanguage || $this->forceLocalize) {
                $templateLoc = Str::localizeFilename($viewFile, null, $extension);
                if ($this->useTheme) {
                    // получение с темой и локализацией
                    $filenameLoc = $this->theme->viewPath . $moduleThemePath . $templateLoc;
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
                // получение без темы но с локализацией
                $filenameLoc = $this->module->getViewPath() . $templateLoc;
                if (file_exists($filenameLoc)) return $filenameLoc;
            }
        }
        // 2) Получение без локализации (с темой)
        // получение с темой
        if ($this->useTheme) {
            $filename = $this->theme->viewPath . $moduleThemePath . $viewFile;
            if (file_exists($filename)) return $filename;
        }
        // 3) Получение без темы и без локализации
        return $this->module->getViewPath() . $viewFile;
    }

    /**
     * Добавляет параметр в шаблон представления, используемый для локализации 
     * сообщений в шаблоне.
     * 
     * @see \Ge\I18n\Source\MessageSource::createFuncTranslate()
     * 
     * @param array $params Параметры передаваемые в шаблон представления.
     * 
     * @return void
     */
    public function addMessages(array &$params): void
    {
        if ($this->module) {
            /** @var \Ge\I18n\Source\MessageSource $source */
            $source = $this->module->getMessageSource();
            if ($source) {
                $source->createFuncTranslate($params);
            }
        }
    }

    /**
     * Метод вызываемый перед визуализацией представления.
     * 
     * @return bool
     */
    public function beforeRender(string $filename, array $params): bool
    {
        return true;
    }

    /**
     * Метод вызываемый после визуализации представления.
     * 
     * @param mixed $content Содержимое, полученное визуализатором.
     * 
     * @return mixed
     */
    public function afterRender(string $filename, array $params, mixed $content = ''): mixed
    {
        return $content;
    }

    /**
     * Возвращает содержимое файла шаблона.
     * 
     * @param string $viewFile Имя шаблона или файла.
     * @param Module|Extension|null $module Модуль или расширение к которому относится шаблон. 
     *     Применяется для получения файла шаблона. Если значение `null`, 
     *     тогда применяется текущий модуль {@see \Ge\Mvc\Application::$module} (по 
     *     умолчанию `null`).
     * 
     * @return string
     */
    public function loadFile(string $viewFile, Module|Extension|null $module = null): string
    {
        if ($module) {
            $this->module = $module;
        }

        $filename = $this->getViewFile($viewFile);
        if ($filename === false) {
            throw new Exception\TemplateNotFoundException(
                Ge::t('app', 'Cannot resolve view file for "{0}"', [$viewFile ?: 'unknow']),
                $filename
            );
        }

        $content = file_get_contents($filename, true);
        if ($content === false) {
            throw new Exception\TemplateNotFoundException(
                Ge::t('app', 'Could not load template, file is not accessible "{0}"', [$filename]),
                $filename
            );
        }
        return $content;
    }

    /**
     * Возвращает содержимое шаблона PHP-файла.
     *
     * @param string $filename Имя файла (включая путь).
     * @param array $params Параметры передаваемые в шаблон в виде пар "имя - значение".
     * 
     * @return string
     */
    public function renderPhpFile(string $filename, array $params = []): string
    {
        $obInitialLevel = ob_get_level();

        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        try {
            require $filename;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /**
     * Возвращает содержимое файла шаблона.
     * 
     * @param string $filename Имя файл (включает путь) шаблона.
     * @param array|null $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления. Если значение `null`, будет использоваться 
     *     {@see BaseView::$params} (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function renderFile(string $filename, array $params = []): mixed
    {
        $content = '';
        // событие перед визуализацией
        if ($this->beforeRender($filename, $params)) {
            // если не указано имя визуализатора
            if ($this->renderer === null) {
                $this->renderer = pathinfo($filename, PATHINFO_EXTENSION);
            }
            // добавить параметр локализации сообщений в шаблон
            $this->addMessages($params);

            // если использовать внутренний визуализатор
            if ($this->renderer === 'php' || $this->renderer === 'phtml') {
                $content = $this->renderPhpFile($filename, $params);
            } else {
                /** @var object $renderer Визуализатор */
                $renderer = $this->getRenderer();
                $content = $renderer->render($params, $filename);
            }

            // использовать в визуализаторе шорткоды
            if ($this->useShortcodes) {
                $this->contentHandler = Ge::$app->shortcodes;
            }
            // использовать в визуализаторе обработчик контента
            if ($this->contentHandler && method_exists($this->contentHandler, 'process')) {
                $content = $this->contentHandler->process($content);
            }

            // событие после визуализации
            $content = $this->afterRender($filename, $params, $content);
        }
        return $content;
    }

    /**
     * Возвращает визуализацию содержимого представления.
     * 
     * @param string $viewFile Имя шаблона или файл шаблона представления.
     * @param array $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления (по умолчанию `[]`).
     * @param Module|Extension|null $module Модуль к которому относится представление. 
     *     Применяется для получения файла шаблона. Если значение `null`, 
     *     тогда применяется текущий модуль {@see \Ge\Mvc\Application::$module} (по 
     *     умолчанию `null`).
     * 
     * @return mixed
     * 
     * @throws Exception\TemplateNotFoundException Невозможно получить имя файла 
     *     шаблона представления.
     */
    public function render(string $viewFile, array $params = [], Module|Extension|null $module = null): mixed
    {
        if ($module) {
            $this->module = $module;
        }

        $filename = $this->getViewFile($viewFile);
        if ($filename === false) {
            throw new Exception\TemplateNotFoundException(
                Ge::t('app', 'Cannot resolve view file for "{0}"', [$viewFile ?: 'unknow']),
                $filename
            );
        }

        // добавляем, чтобы последующие шаблоны знали о существовании этого шаблона
        $this->mapFiles[] = [
            'viewFile' => $viewFile,
            'filename' => $filename
        ];

        $content = $this->renderFile($filename, $params);

        // удаляем, чтобы все последующие шаблоны о нём забыли
        array_pop($this->mapFiles);
        return $content;
    }

    /**
     * Возвращает визуализацию содержимого представления.
     * 
     * Используется в каскадной визуализации представлений. Где в выводе содержимого 
     * текущего шаблона, выводится содержимое последующего шаблона, что создаёт каскад 
     * (рекурсию) вызовов метода `renderFrom`.
     * 
     * Для быстрого определения имени файла шаблона, используется уже полученный путь к 
     * предыдущиму (родительскому) шаблону.
     * 
     * @param string $viewFile Имя шаблона, например: 'foobar', 'foo/bar'. К полученному имени, 
     *     добавляется расширение файла шаблона по умолчанию {@see BaseView::$defaultExtension}.
     * @param array $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления (по умолчанию `[]`).
     * 
     * @return mixed
     */
    public function renderFrom(string $viewFile, array $params = []): mixed
    {
        // определяем предыдуший шаблон
        $index = sizeof($this->mapFiles) - 1;
        if (isset($this->mapFiles[$index])) {
            $parent = &$this->mapFiles[$index];
            // путь относительно предыдущего шаблона, если он не указан,
            // определяем и устан-м его шаблону, чтобы в дальнейшем 
            // последующие шаблоны не определяли его
            if (isset($parent['path'])) {
                $path = $parent['path'];
            } else {
                $path = dirname($parent['filename']);
                $parent['path'] = $path;
            }
        // если нет предыдушего шаблона
        } else
            return '';

        $filename = $path . DS . $viewFile . '.' . $this->defaultExtension;

        // добавляем, чтобы последующие шаблоны знали о существовании этого шаблона
        $this->mapFiles[] = [
            'viewFile' => $viewFile,
            'filename' => $filename
        ];

        $content = $this->renderFile($filename, $params);

        // удаляем, чтобы все последующие шаблоны о нём забыли
        array_pop($this->mapFiles);
        return $content;
    }

   /**
     * Начинает запись блока.
     * 
     * @see BaseView::$blocks
     * 
     * @param string $id Идентификатор блока.
     * 
     * @return void
     */
    public function beginBlock(string $id): void
    {
        ob_start();
        // выключаем неявный сброс
        ob_implicit_flush(false);

        static::$blocks[] = $id;
    }

    /**
     * Заканчивает запись блока.
     * 
     * Получает содержимое текущего буфера и удаляет его. Содержимое блока передаётся в 
     * {@see BaseView::$content()}.
     * 
     * @see BaseView::beginView
     * 
     * @return string Возвращает содержимое блока.
     * 
     * @throws NotDefinedException Начало блока не найдено.
     */
    public function endBlock(): string
    {
        if (static::$blocks) {
            // последний блок
            $blockId = array_pop(static::$blocks);
            // получает содержимое текущего буфера и удаляет его
            $content = ob_get_clean();
            static::$content[$blockId] = $content;
            return $content;
        }
        throw new NotDefinedException('Not defined "beginBlock()"');
    }

    /**
     * Возвращает содержимое блока по указанному идентификатору.
     * 
     * Для получения содержимого блока необходимо использовать {@see BaseView::beginBlock()} 
     * и {@see BaseView::endBlock()}.
     * 
     * @see BaseView::$content
     * 
     * @param string $id Идентификатор блока.
     * @param string $default Значение по умолчанию, если идентификатор не найден.
     * 
     * @return string
     */
    public function getBlock(string $id, string $default = ''): string
    {
        return static::$content[$id] ?? $default;
    }
}
