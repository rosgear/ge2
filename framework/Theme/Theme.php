<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme;

use Ge;
use Ge\Exception;
use Ge\Stdlib\Service;
use Ge\Helper\Url;
use Ge\Filesystem\Filesystem;
use Ge\Theme\Info\ViewsInfo;

/**
 * Тема предназначена для смены (установки) шаблонов в моделях представления.
 * 
 * Theme - это служба приложения, доступ к которой можно получить через `Ge::$app->theme`.
 * 
 * После создания экземпляра класса темы, необходимо задействовать (установить) тему 
 * {@see \Ge\Theme::set()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme
 * @since 2.0
 */
class Theme extends Service
{
    /**
     * @var string Файл описания шаблонов темы.
     */
    public const VIEW_DESC_FILE = 'views.json';

    /**
     * Название темы.
     *
     * @var string
     */
    public string $name = '';

    /**
     * Базовый (локальный) путь к темам.
     *
     * Указывается параметром "themesLocalPath" конфигурации сервиса "theme".
     * Пример: "/themes".
     * 
     * @var string
     */
    public string $themesLocalPath = '';

    /**
     * Абсолютный путь к темам.
     * 
     * Имеет вид: "</абсолютный общедоступный путь> </базовый (локальный) путь к темам>".
     * 
     * @var string
     */
    public string $themesPath = '';

    /**
     * Абсолютный URL-адрес тем.
     * 
     * Имеет вид: "<абсолютный общедоступный URL-адрес/> <базовый URL-путь к темам/>".
     * 
     * @var string
     */
    public string $themesUrl = '';

    /**
     * Локальный путь к теме относительно абсолютного пути к темам.
     * 
     * Указывается параметром "localPath" конфигурации темы.
     * Пример: "/my_theme".
     * 
     * @var string
     */
    public string $localPath = '';

    /**
     * Абсолютный путь к теме.
     * 
     * Имеет вид: "</абсолютный путь к темам> </локальный путь к теме>".
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Абсолютный путь к шаблонам моделей представления текущей темы.
     * 
     * Имеет вид: "</абсолютный путь к темам> </локальный путь к теме> </VIEW_PATH>".
     * 
     * @var string
     */
    public string $viewPath = '';

    /**
     * Абсолютный путь к шаблонам моделей представления текущей темы.
     * 
     * Имеет вид: "</абсолютный путь к темам> </локальный путь к теме> </VIEW_PATH>".
     * 
     * @var string
     */
    public string $layoutPath = '';

    /**
     * Базовый URL-путь темы.
     * 
     * Указывается параметром "baseUrl" конфигурации темы.
     * Пример: "/my_theme".
     * 
     * @var string
     */
    public string $baseUrl = '';

    /**
     * URL-путь темы.
     * 
     * Имеет вид: "<абсолютный URL-адрес тем/> <базовый URL-путь к теме/>".
     * 
     * @var string
     */
    public string $url = '';

    /**
     * Имя стороны, которой принадлежит тема.
     * 
     * Может иметь значение: FRONTEND, BACKEND.
     * 
     * @var string
     */
    public string $side = FRONTEND;

    /**
     * Доступные имена тем с их параметрами.
     * 
     * @var array
     */
    public array $available = [];

    /**
     * Имя (активной) темы по умолчанию.
     * 
     * Указывается из доступных тем {@see \Ge\Theme::$available}.
     * 
     * @var string
     */
    public string $default = '';

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Имя раздела унифицированного конфигуратора, где находятся параметры службы.
     * 
     * Применяется, т.к. служба может иметь несколько разделов с параметрами,
     * каждый из которых, может быть задействован при определенных условиях.
     * 
     * @var string
     */
    public string $unifiedName;

    /**
     * Описание шаблонов темы.
     * 
     * @see Theme::getViewsInfo()
     * 
     * @var ViewsInfo
     */
    protected ViewsInfo $viewsInfo;

    /**
     * {@inheritdoc}
     */
    public function __construct(?array $config = null)
    {
        $this->unifiedName = $config['unifiedName'] ?? null;

        Ge::configure($this, $config, $this->useUnifiedConfig);

        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        if ($this->unifiedName)
            return $this->unifiedName;
        else
            return $this->side === FRONTEND ? 'frontendTheme' : 'backendTheme';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // абсолютный путь к темам
        $this->themesPath = Ge::$app->clientScript->publishedPath . $this->themesLocalPath;
        // абсолютный URL-адрес тем
        $this->themesUrl = Ge::$app->clientScript->publishedUrl . $this->themesLocalPath;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app): void
    {
        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        Ge::setAlias('@theme', $this->path); // абсолютный путь темы
        Ge::setAlias('@theme:views', $this->viewPath);
        Ge::setAlias('@theme:layouts', $this->viewPath . DS . 'layouts');
        Ge::setAlias('@theme::',   $this->url); // URL-путь темы
        Ge::setAlias('@/theme',  Url::host() . $this->url); // абсолютный URL-адрес темы: <схема>://<хост> <URL-путь текущей темы>
    }

    /**
     * Установка темы.
     * 
     * @param null|string $theme Имя темы из доступных {@see Theme::$available} имен тем.
     * 
     * @return $this
     */
    public function set(?string $theme = null): static
    {
        if ($theme === null) {
            $theme = $this->default;
        }
        if (!isset($this->available[$theme])) {
            throw new Exception\InvalidConfigException(sprintf('No set configuration for theme "%s"', $theme));
        }
        // параметры выбранной темы
        $config = $this->available[$theme];
        // название темы
        $this->name = $theme;
        // локальный путь темы
        if (!isset($config['localPath'])) {
            throw new Exception\InvalidConfigException('Unable to set the component configuration parameter "localPath".');
        }
        $this->localPath = $config['localPath'];
        // абсолютный путь темы
        $this->path = $this->themesPath . $this->localPath;
        // абсолютный путь к шаблонам темы
        $this->viewPath = $this->path . '/views';
        // абсолютный путь к шаблонам темы
        $this->layoutPath = $this->viewPath . '/layouts';
        // базовый URL-адрес темы
        $this->baseUrl = $this->localPath;
        // абсолютный URL-адрес темы
        $this->url = $this->themesUrl . $this->baseUrl;
        if (!defined('THEME_BASE_URL')) {
            /** @var string Абсолютный URL-адрес темы. */
            define('THEME_BASE_URL', $this->url);
        }
        // функции темы
        $this->includeFunctions();
        // инициализация глобальных переменных сервиса
        $this->initVariables();
        return $this;
    }

    /**
     * Возвращает параметры из доступных {@see Theme::$available} тем.
     * 
     * @param string $themeName Если значение null, возвращает параметры текущей темы.
     * 
     * @return array
     */

    /**
     * Возвращает параметры доступной (установленной) темы.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, то 
     *     возвратит параметры текущей задействованной темы или параметры 
     *     темы по умолчанию (по умолчанию `null`).
     * 
     * @return array
     */
    public function get(?string $themeName = null): array
    {
        if ($themeName === null) {
            $themeName = $this->name ?: $this->default;
        }
        return $this->available[$themeName] ?? [];
    }

    /**
     * Регистратор пакетов ресурсов темы.
     * 
     * @var ThemeAsset
     */
    private ThemeAsset $_themeAsset;

    /**
     * Возвращает регистратор пакетов ресурсов темы
     * 
     * @return ThemeAsset
     */
    public function getAsset(): ThemeAsset
    {
        if (!isset($this->_themeAsset)) {
            require_once ($this->path . '/asset.php');

            $this->_themeAsset = new \Asset();
        }
        return $this->_themeAsset;
    }

    /**
     * Проверяет, относится ли тема к frontend стороне.
     * 
     * @return bool
     */
    public function isFrontend(): bool
    {
        return !$this->isBackend();
    }

    /**
     * Проверяет, относится ли тема к backend стороне.
     * 
     * @return bool
     */
    public function isBackend(): bool
    {
        static $backend = null;

        if ($backend === null) {
            $backend = $this->side === BACKEND;
        }
        return $backend;
    }

    /**
     * Подключает файл функций темы (если он есть).
     * 
     * @return void
     */
    protected function includeFunctions(): void
    {
        $filename = $this->path . '/functions.php';
        if (file_exists($filename)) {
            include($filename);
        }
    }

    /**
     * Проверят, доступна (установлена) ли указанная тема.
     * 
     * @param null|string $themeName Имя проверяемой темы. Если значение `null`, то 
     *     будет проверена текущая задействованная тема или тема по умолчанию  
     *     (по умолчанию `null`).
     * 
     * @return bool Если значение `false`, указанная тема не доступна (не установлена).
     */
    public function exists(?string $themeName = null): bool
    {
        if ($themeName === null) {
            $themeName = $this->name ?: $this->default;
        }
        return isset($this->available[$themeName]);
    }

    /**
     * Проверяет, существует ли файл шаблона.
     * 
     * @param string $filename Имя файла шаблона (может включать путь, начианется с "/").
     * @param null|string $themeName Имя темы.
     * 
     * @return bool Возвращает значение `false`, если файл шаблона не существует.
     */
    public function templateExists(string $filename, ?string $themeName = null): bool
    {
        return Filesystem::exists($this->getViewPath($themeName) . $filename);
    }

    /**
     * Возвращает названия файла шаблона темы с полным или локальным путём.
     * 
     * Где локальный путь {@see Theme::VIEW_PATH} - базовый путь ко всем шаблонам темы.
     * 
     * @param string $filename Имя файла шаблона темы (пример: '/path/template.phtml').
     * 
     * @return string Возвращает имя файла шаблона темы с полным или локальным путём. 
     *     Пример результата выполнения:
     *     - с полным путём для backend: '/home/host/public_html/themes/backend/theme-name/view/path/template.phtml';
     *     - с полным путём для frontend: '/home/host/public_html/themes/src/theme-name/view/path/template.phtml';
     *     - c локальным путём: '/view/path/template.phtml'.
     */
    public function getTemplateFile(string $filename, bool $absolute = true): string
    {
        if ($absolute)
            return $this->viewPath . $filename;
        else
            return '/views' . $filename;
    }

    /**
     * Возвращает названия файла шаблона темы с полным путем.
     * 
     * @param string $name Название шаблона (без расширения файла).
     * 
     * @return string
     */
    public function getTemplate(string $name): string
    {
        return $this->viewPath . DS . $name . '.phtml';
    }

    /**
     * Возвращает абсолютный URL-адрес ресурса.
     * 
     * @param string $src Локальный URL-путь ресурса.
     * 
     * @return string
     */
    public function getUrl(string $src): string
    {
        return $this->url . $src;
    }

    /**
     * Возвращает URL-адрес значка темы.
     *
     * @param string $themeName Название темы.
     * 
     * @return string Если значок отсутствует, возвратит ''.
     */
    public function getThumbUrl(string $themeName): string
    {
        /** @var array $themeParams */
        $themeParams = $this->get($themeName);
        if (isset($themeParams['localPath'])) 
            return $this->themesUrl . $themeParams['localPath'] . '/thumb.png';
        else
            return '';
    }

    /**
     * Возвращает URL-адрес скриншота темы.
     *
     * @param string $themeName Название темы.
     * 
     * @return string Если скриншот отсутствует, возвратит ''.
     */
    public function getScreenshotUrl(string $themeName): string
    {
        /** @var array $themeParams */
        $themeParams = $this->get($themeName);
        if (isset($themeParams['localPath'])) 
            return $this->themesUrl . $themeParams['localPath'] . '/screenshot.png';
        else
            return '';
    }

    /**
     * Возвращает абсолютный путь указанной темы.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, то текущая тема (по умолчанию `null`).
     * 
     * @return string
     */
    public function getPath(?string $themeName = null): ?string
    {
        if ($themeName === null) {
            return $this->path;
        }
        $params = $this->get($themeName);
        if (empty($params)) {
            return null;
        }
        return $this->themesPath . $params['localPath'];
    }

    /**
     * Проверяет, имеет ли указанная тема демонстрационные данные.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, то текущая тема (по умолчанию `null`).
     * 
     * @return bool
     */
    public function hasPreview(?string $themeName = null): bool
    {
        /** @var null|string $file */
        $file = $this->getPreviewFilename($themeName);
        return $file ? file_exists($file) : false;
    }

    /**
     * Возвращает абсолютный путь указанной темы к демонстрационным данным.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, то текущая тема (по умолчанию `null`).
     * 
     * @return null|string
     */
    public function getPreviewFilename(?string $themeName = null): ?string
    {
        if ($themeName === null) {
            return $this->path;
        }
        $params = $this->get($themeName);
        if (empty($params)) {
            return null;
        }
        return $this->themesPath . $params['localPath'] . DS . 'preview' . DS . 'package.xml';
    }

    /**
     * Возвращает абсолютный путь к шаблонам указанной темы.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, то текущая тема (по умолчанию `null`).
     * 
     * @return string|null
     */
    public function getViewPath(?string $themeName = null): ?string
    {
        if ($themeName === null) {
            return $this->viewPath;
        }
        $params = $this->get($themeName);
        if (empty($params)) {
            return null;
        }
        return $this->themesPath . $params['localPath'] . DS . 'views';
    }

    /**
     * Преобразование полного пути файла шаблона в локальный.
     * 
     * Например: '/public/themes/default/views/pages/blog.phtml' => '/views/pages/blog.phtml'.
     * 
     * @param string $viewFile Имя файла шаблон с полным указании пути.
     * 
     * @return string
     */
    public function viewFileToFilePath(string $viewFile): string
    {
        static $views = [];

        if (empty($viewFile)) return '';

        if (!isset($views[$viewFile])) {
            $viewFile = str_replace('\\', '/', $viewFile);
            $path     = str_replace('\\', '/', $this->path);
            return $views[$viewFile] = '/' .ltrim(str_replace($path, '', $viewFile), '/');
        }
        return $views[$viewFile];
    }

    /**
     * Возвращает пакет информации по указанной теме.
     * 
     * @param null|string $themeName Имя темы. Если значение `null`, имя темы по умолчанию (по умолчанию `null`).
     * 
     * @return ThemePackage
     */
    public function getPackage(?string $themeName = null): ?ThemePackage
    {
        if ($themeName === null) {
            $themeName = $this->default;
        }

        /** @var null|array $params */
        $params = $this->get($themeName);
        if (empty($params)) {
            return null;
        }
        return new ThemePackage($this->themesPath . $params['localPath']);
    }

    /**
     * Возвращает описание шаблонов темы.
     * 
     * @return ViewsInfo
     */
    public function getViewsInfo(): ViewsInfo
    {
        if (!isset($this->viewsInfo)) {
            $this->viewsInfo = new ViewsInfo($this);
        }
        return $this->viewsInfo;
    }

    /**
     * Копирует файлы шаблонов модулей в директорию указанной темы.
     * 
     * @param array $componentPaths Локальные пути к компонентам.
     *     Например: `['/rg/rg.module', '/rg/rg.widget', ...]`.
     * @param null|string $themeName Имя темы. Если значение null, имя текущей темы.
     * @param bool $replace Заменить файлы шаблонов, которые уже находятся в директории темы.
     * @param int $mode Разрешение на директорию (по умолчанию "0755").
     * 
     * @return int|string Если копирование выполнено успешно, возвращает количество созданных копий, иначе ошибку.
     */
    public function copyViewFiles(array $componentPaths, ?string $themeName = null, bool $replace = true, int $mode = 0755): int|string
    {
        Filesystem::$throwException = false; // запретить исключение

        /** @var string $viewPath Абсолютный путь к шаблонам указанной темы */
        $viewPath = $this->getViewPath($themeName);
        /** @var string $modulesPath Абсолютный путь к директории компонентов */
        $modulesPath = Ge::getAlias('@module');

        $count = 0;
        foreach ($componentPaths as $localPath) {
            $finder = Filesystem::finder();

            /** @var string $componentPath Абсолютный путь к шаблонам компонента */
            $componentPath = $modulesPath . $localPath . DS . 'views';
            // если нет файлов шаблонов компонента
            if (!file_exists($componentPath)) continue;

            $finder->files()->in($componentPath);
            foreach ($finder as $info) {
                /** @var string $relativePath Локальный путь к шаблонам компонента относително его директории */
                $relativePath = $info->getRelativePath();
                /** @var string $targetPath Абсолютный путь к шаблонам компонента в теме */
                $targetPath = $viewPath . $localPath . ($relativePath ? DS . $relativePath : '');
                if (!file_exists($targetPath)) {
                    if (!Filesystem::makeDirectory($targetPath, $mode, true)) {
                        return Ge::t('app', 'Unable to create directory "{0}"', [$targetPath]);
                    }
                }

                // создание копии файла шаблона
                $pathname = $info->getPathname(); // полный путь с именем файла
                $filename = $targetPath . DS . $info->getFilename();
                if (!$replace && file_exists($filename)) {
                    continue;
                }
                if (!Filesystem::copy($pathname, $filename)) {
                    return Ge::t('app', 'Unable to copy file "{0}" to directory "{1}"', [$pathname, $filename]);
                }
                $count++;
            }
        }
        return $count;
    }

    /**
     * Определяет из указанной строки, имя темы и какой стороне она принадлежит (backend, frontend).
     * 
     * @param string $name Имя темы.
     *    Имеет вид: "backend::Green", "frontend::My Theme".
     * @param string $separator Разделитель темы и стороны. По умолчанию "::".
     * @param bool $create Создать экземпляр класса темы из указанного имени в строке.
     * 
     * @return array|null Если null, неправильно указана тема в строке.
     *    Иначе результат:
     *       [
     *           (string) "side" сторона (backend, frontend), 
     *           (string) "name" имя темы,
     *           (\Ge\Theme\Theme) "theme" экземпляр класса темы
     *       ]
     */
    public function defineThemeFromStr(string $name, string $separator = '::', bool $create = false): ?array
    {
        if (empty($name)) {
            return null;
        }

        $both = explode($separator, $name);
        if (sizeof($both) !== 2) {
            return null;
        }

        $side = $both[0];
        if ($side !== FRONTEND && $side !== BACKEND) {
           return null;
        }
        return [
            'side'  => $side,
            'name'  => $both[1],
            'theme' => $create ? Ge::$app->services->createAs(strtolower($side) . 'Theme') : null
        ];
    }

    public function themeExists(string $localPath): bool
    {
        return file_exists($this->themesPath . DS . $localPath);
    }

    /**
     * Выполняет поиск тем и возвращает информация о них.
     * 
     * @param array $filter Фильтр в виде пар "ключ - значение" для поиска тем с 
     *     указанной информацией. Ключи для фильтрации: 'name', 'description', 'version', 
     *     'author', 'license', 'keywords'.
     *     Например: `['author' => 'author@mail.ru', 'name' => 'Theme name']`.
     * 
     * @return array
     */
    public function find(array $filter = []): array
    {
        $themes = [];

        /** @var \Symfony\Component\Finder\Finder $finder */
        $finder = Filesystem::finder();
        // поиск файлов конфигурации установки $filename
        $finder->files()->name(ThemePackage::PACKAGE_FILE)->ignoreDotFiles(false)->in($this->themesPath)->depth('== 1');
        foreach ($finder as $file) {
            $path = $file->getPath();
            $localPath = $file->getRelativePath();

            /** @var ThemePackage $package Пакет темы */
            $package = new ThemePackage($path);

            /** @var null|array $info Информация о пакете */
            $info = $package->getInfo($filter);
            if ($info) {
                // если есть файл привью
                if (file_exists($path . DS . 'thumb.png')) {
                    $info['thumb'] = $this->themesUrl . '/' . $localPath . '/thumb.png';
                }
                // если есть файл скриншота
                if (file_exists($path . DS . 'screenshot.png')) {
                    $info['screenshot'] = $this->themesUrl . '/' . $localPath . '/screenshot.png';
                }
                // если есть демоданные 
                $info['preview'] = file_exists($path . DS . 'preview' . DS . 'package.xml');

                $info['side'] = $this->side;
                $info['localPath'] = '/' . $localPath;
                $themes[] = $info;
            }
        }
        return $themes;
    }

    /**
     * Добавляет или делает указанную тему доступной для выбора.
     * 
     * @see Theme::$available
     * 
     * @param string $theme Имя темы
     * @param array<string, array> $params Параметры темы.
     * 
     * @return void
     */
    public function addAvailable(string $theme, array $params): void
    {
        $this->available[$theme] = [
            'name'      => $theme,
            'localPath' => $params['localPath'] ?? ''
        ];
    }
}
