<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc;

use Ge;
use Throwable;
use DateTimeZone;
use Ge\Exception;
use Ge\View\View;
use Ge\Helper\Helper;
use Ge\Helper\Url;
use Ge\Theme\Theme;
use Ge\Router\Router;
use Ge\Config\Config;
use Ge\I18n\Formatter;
use Ge\View\LayoutView;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\Collection;
use Ge\Mvc\Module\BaseModule;
use Ge\Mvc\Controller\BaseController;
use Ge\ErrorHandler\WebErrorHandler;
use Ge\ServiceManager\ServiceManager;

/**
 * Приложение является базовым классом для всех классов-наследников приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc
 * @since 2.0
 */
class Application extends BaseObject
{
    /**
     * Менеджер служб.
     * 
     * @see Application::init()
     * 
     * @var ServiceManager
     */
    public ServiceManager $services;

    /**
     * Конфигуратор приложения.
     * 
     * Определяет настройки конфигурации для текущего приложения.
     * 
     * @see Application::getConfig()
     * 
     * @var Config
     */
    public Config $config;

    /**
     * Унифицированный конфигуратор приложения.
     * 
     * Этот конфигуратор используют все службы приложения.
     * 
     * @see Application::bootstrap()
     * 
     * @var Config
     */
    public Config $unifiedConfig;

    /**
     * Внутренняя кодировка приложения.
     * 
     * @see Application::initEncoding()
     * 
     * @var string
     */
    public string $charset;

    /**
     * Параметры приложения.
     * 
     * @see Application::initParameters()
     * 
     * @var Collection
     */
    public Collection $params;

    /**
     * Маршрутизатор запросов.
     *
     * @see Application::getRouter()
     * 
     * @var Router
     */
    public Router $router;

    /**
     * Обработчик ошибок.
     * 
     * @see Application::registerErrorHandler()
     * 
     * @var WebErrorHandler
     */
    public WebErrorHandler $errorHandler;

    /**
     * Абсолютный URL-адрес приложения.
     * 
     * @see Application::$url
     * @see Application::setHomeUrl()
     * 
     * @var string
     */
    public string $baseUrl;

    /**
     * Абсолютный URL-адрес приложения.
     * 
     * @see Application::$baseUrl
     * @see Application::setHomeUrl()
     * 
     * @var string
     */
    public string $url;

    /**
     * Абсолютный URL-адрес к модулям приложения.
     * 
     * Имеет вид: `<$url> <MODULE_BASE_URL>`.
     * 
     * @see Application::$baseUrl
     * @see Application::setModuleUrl()
     * 
     * @var string
     */
    public string $moduleUrl;

    /**
     * Абсолютный путь к приложению.
     * 
     * Имеет вид: `<BASE_PATH>`.
     * 
     * @see Application::$path
     * @see Application::setBasePath()
     * 
     * @var string
     */
    public string $basePath;

    /**
     * Абсолютный путь к временным файлам приложения.
     * 
     * Имеет вид: `<BASE_PATH> </runtime>`.
     * 
     * @see Application::setRuntimePath()
     * 
     * @var string
     */
    public string $runtimePath;

    /**
     * Абсолютный путь к часто используемым (системным) шаблонам приложения.
     * 
     * Имеет вид: `<BASE_PATH> </views>`.
     * 
     * @see Application::setViewPath()
     * 
     * @var string
     */
    public string $viewPath;

   /**
     * Абсолютный путь к макетам страниц приложения.
     * 
     * Имеет вид: `<BASE_PATH> </views> [/backend|/frontend] </layouts>`.
     * 
     * @see Application::setLayoutPath()
     * 
     * @var array
     */
    public array $layoutPath = [];

    /**
     * Абсолютный путь к приложению.
     * 
     * Имеет вид: `<BASE_PATH>`.
     * 
     * @see Application::$basePath
     * @see Application::setBasePath()
     * 
     * @var string
     */
    public string $path;

    /**
     * Абсолютный путь к модулям.
     * 
     * Имеет вид: `<BASE_PATH> <MODULE_PATH>`.
     * 
     * @see Application::setModulePath()
     * 
     * @var string
     */
    public string $modulePath;

    /**
     * Абсолютный путь к подключению внешних (vendor) библиотек.
     * 
     * Имеет вид: `<BASE_PATH> <VENDOR_PATH>`.
     * 
     * @see Application::setVendorPath()
     * 
     * @var string
     */
    public string $vendorPath;

    /**
     * Абсолютный путь к файлам конфигурации приложения.
     * 
     * Имеет вид: `<BASE_PATH> <CONFIG_PATH>`.
     * 
     * @see Application::setConfigPath()
     * 
     * @var string
     */
    public string $configPath;

    /**
     * Часовой пояс по умолчанию для всего приложения.
     * 
     * @see Application::setTimeZone()
     * 
     * @var DateTimeZone
     */
    public DateTimeZone $timeZone;

    /**
     * Часовой пояс хранилищ (баз данных, кэша и т.п.) данных.
     * 
     * Часовой пояс в котором будут хранится данные на сервере. Возвращаются 
     * данные уже в часовом поясе {@see Application::$timeZone} пользователя. 
     * Если данные хранятся и выводятся в одном и том же часовом поясе, тогда 
     * значение `null`.
     * 
     * @see Application::setDataTimeZone()
     * 
     * @var DateTimeZone
     */
    public DateTimeZone $dataTimeZone;

    /**
     * Текущая тема приложения.
     * 
     * @see Application::initTheme()
     * 
     * @var Theme
     */
    public Theme $theme;

    /**
     * Текущий модуль приложения.
     * 
     * @see \Ge\Mvc\Module\BaseModule::run()
     * 
     * @var BaseModule|null
     */
    public ?BaseModule $module = null;

    /**
     * Текущий контроллер приложения.
     * 
     * @see BaseController::run()
     * 
     * @var BaseController|null
     */
    public ?BaseController $controller = null;

    /**
     * Текущее действие контроллера приложения.
     * 
     * @see BaseController::run()
     * 
     * @var string|null
     */
    public ?string $action = null;

    /**
     * Имя макета или его файла.
     * 
     * Можно указать как для BACKEND, так и для FRONTEND или для обоих сразу.
     * 
     * Пример:
     * - `@app:layouts/main` (не учитывается тема) или `//main`;
     * - `BACKEND => '//main'` или  `FRONTEND => '//main'`;
     * 
     * @var string|array
     */
    public string|array $layout = '//main';

    /**
     * Параметры конфигурации, используемые при загрузке шаблонов представлений.
     * 
     * Можно указать как для `BACKEND`, так и для `FRONTEND` или для обоих сразу.
     * 
     * Пример:
     * ```php
     * [
     *     BACKEND  => ['useTheme' => true, 'useLocalize' => true],
     *     FRONTEND => ['useTheme' => true, 'useLocalize' => true]
     * ]
     * ```
     * или
     * `['useTheme' => true, 'useLocalize' => true]`.
     * 
     * @see Application::getViewConfig()
     * 
     * @var array
     */
    public array $viewConfig = [];

    /**
     * Слушатели событий - компоненты (модуль, расширение модуля, виджет).
     * 
     * @see Application::getEventListeners()
     *
     * @var EventListeners
     */
    public EventListeners $listeners;

    /**
     * @var string
     */
    public static $configFilename = 'config/.application.php';

    /**
     * Представление.
     * 
     * @see Application::getView()
     * 
     * @var View
     */
    protected View $view;

    /**
     * Представление макета страниц.
     * 
     * @see Application::getLayoutView()
     * 
     * @var LayoutView
     */
    protected LayoutView $layoutView;

    /**
     * Возвращает конфигуратор приложения.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        if (!isset($this->config)) {
            return $this->config = $this->services->createAs('config', [self::$configFilename]);
        }
        return $this->config;
    }

    /**
     * Если конфигуратор приложения не создан, то создаёт его и устанавливает ему 
     * параметры.
     * 
     * @param array<string, mixed> $params Параметры конфигуратора.
     * 
     * @return void
     */
    public function setConfigParams(array $params): void
    {
        if (!isset($this->config)) {
            $this->config = $this->services->createAs('config', []);
            $this->config->setAll($params);
        }
    }

    /**
     * Возвращает машрутизатор запросов.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        // если машрутизатор уже создан
        if (isset($this->router)) {
            return $this->router;
        }

        // маршрутизация запроса для backend
        if ($this->isBackend()) {
            $routerConfig = $this->config->factory($this->config->{BACKEND}['router']);
            // добавляем "секретный" маршрут к backend
            $prefixes = $routerConfig->get('prefixes');
            $prefixes[BACKEND] = $this->config->{BACKEND}['route'];
            $routerConfig->set('prefixes', $prefixes);
        // маршрутизация запроса для fronend
        } else {
            $routerConfig = $this->config->factory($this->config->{FRONTEND}['router']);
        }
        return $this->router = $this->services->getAs('router', [], ['config' => $routerConfig]);
    }

    /**
     * Возвращает абсолютный URL-адрес к модулям приложения.
     * 
     * @see Application::$moduleUrl
     * 
     * @return string Абсолютный URL-адрес к модулям приложения.
     */
    public function getModuleUrl(): string
    {
        if (!isset($this->moduleUrl)) {
            $this->setModuleUrl($this->url . MODULE_BASE_URL);
        }
        return $this->moduleUrl;
    }

    /**
     * Устанавливает абсолютный URL-адрес к модулям приложения.
     * 
     * @see Application::$moduleUrl
     * 
     * @param string $url URL-адрес. Пример 'https://domain/module'.
     * 
     * @return void
     */
    public function setModuleUrl(string $url): void
    {
        $this->moduleUrl = $url;
        Ge::setAlias('@module::', $url);
    }

    /**
     * Возвращает абсолютный URL-адрес приложения.
     * 
     * @see Application::$url
     * 
     * @return string Абсолютный URL-адрес приложения.
     */
    public function getHomeUrl(): string
    {
        if (!isset($this->url)) {
            $this->setHomeUrl(Url::home());
        }
        return $this->baseUrl;
    }

    /**
     * Устанавливает абсолютный URL-адрес приложения.
     * 
     * @see Application::$url
     * @see Application::$baseUrl
     * 
     * @param string $url Абсолютный URL-адрес. Пример 'https://domain'.
     * 
     * @return void
     */
    public function setHomeUrl(string $url): void
    {
        $this->url = $url;
        $this->baseUrl = $url;
        Ge::setAlias('@home::', $url);
        Ge::setAlias('@gm::', $url);
    }

    /**
     * Возвращает абсолютный путь к файлам конфигурации приложения.
     * 
     * @see Application::$configPath
     * 
     * @return string Абсолютный путь к файлам конфигурации приложения.
     */
    public function getConfigPath(): string
    {
        if (!isset($this->configPath)) {
            $this->setConfigPath(BASE_PATH . CONFIG_PATH);
        }
        return $this->configPath;
    }

    /**
     * Устанавливает абсолютный путь к файлам конфигурации приложения.
     * 
     * @see Application::$configPath
     * 
     * @param string $path Абсолютный путь. Пример '/home/host/public_html/config'.
     * 
     * @return void
     */
    public function setConfigPath(string $path): void
    {
        $this->configPath = $path;
        Ge::setAlias('@config', $path);
    }

    /**
     * Возвращает абсолютный путь к подключению внешних (vendor) библиотек.
     * 
     * @see Application::$vendorPath
     * 
     * @return string Абсолютный путь к подключению внешних (vendor) библиотек.
     */
    public function getVendorPath(): string
    {
        if (!isset($this->vendorPath)) {
            $this->setVendorPath(BASE_PATH . VENDOR_PATH);
        }
        return $this->vendorPath;
    }

    /**
     * Устанавливает абсолютный путь к подключению внешних (vendor) библиотек.
     * 
     * @see Application::$vendorPath
     * 
     * @param string $path Абсолютный путь. Пример '/home/host/public_html/vendor'.
     * 
     * @return void
     */
    public function setVendorPath(string $path): void
    {
        Ge::setAlias('@vendor', $this->vendorPath = $path);
    }

    /**
     * Возвращает абсолютный путь к модулям.
     * 
     * @see Application::$modulePath
     * 
     * @return string Абсолютный путь к модулям.
     */
    public function getModulePath(): string
    {
        if (!isset($this->modulePath)) {
            $this->setModulePath(BASE_PATH . MODULE_PATH);
        }
        return $this->modulePath;
    }

    /**
     * Устанавливает абсолютный путь к модулям.
     * 
     * @see Application::$modulePath
     * 
     * @param string $path Абсолютный путь. Пример '/home/host/public_html/module'.
     * 
     * @return void
     */
    public function setModulePath(string $path): void
    {
        $this->modulePath = $path;
        Ge::setAlias('@module', $path);
    }

    /**
     * Возвращает абсолютный путь к приложению.
     * 
     * @see Application::$basePath
     * 
     * @return string Абсолютный путь к приложению.
     */
    public function getBasePath(): string
    {
        if (!isset($this->basePath)) {
            $this->setBasePath(BASE_PATH);
        }
        return $this->basePath;
    }

    /**
     * Устанавливает абсолютный путь к приложению.
     * 
     * @see Application::$basePath
     * @see Application::$path
     * 
     * @param string $path Абсолютный путь к приложению. Пример '/home/host/public_html'.
     * 
     * @return void
     */
    public function setBasePath(string $path): void
    {
        $this->basePath = $path;
        $this->path     = $path;
        Ge::setAlias('@gm', $path);
        Ge::setAlias('@path', $path);
        Ge::setAlias('@home', $path);
    }

    /**
     * Возвращает абсолютный путь к временным файлам приложения.
     * 
     * @return string Абсолютный путь к временным файлам приложения.
     */
    public function getRuntimePath(): string
    {
        if (!isset($this->runtimePath)) {
            $this->setRuntimePath($this->getBasePath() . DS . 'runtime');
        }
        return $this->runtimePath;
    }

    /**
     * Устанавливает абсолютный путь к временным файлам приложения.
     * 
     * @see Application::$runtimePath
     * 
     * @param string $path Абсолютный путь, например '/home/host/public_html/runtime'.
     * 
     * @return void
     */
    public function setRuntimePath(string $path): void
    {
        Ge::setAlias('@runtime', $this->runtimePath = $path);
    }

    /**
     * Возвращает абсолютный путь приложения к файлам шаблонов представления.
     * 
     * @see Application::$viewPath
     * 
     * @return string
     */
    public function getViewPath(): string
    {
        if (!isset($this->viewPath)) {
            $this->setViewPath($this->getBasePath() . DS . 'views');
        }
        return $this->viewPath;
    }

    /**
     * Устанавливает абсолютный путь приложения к файлам шаблонов представления.
     * 
     * @see Application::$viewPath
     * 
     * @param string $path Абсолютный путь, например '/home/host/public_html/views'.
     * 
     * @return void
     */
    public function setViewPath(string $path): void
    {
        Ge::setAlias('@app:views', $this->viewPath = $path);
    }

    /**
     * Возвращает абсолютный путь приложения к файлам макетов страниц в зависимости от 
     * стороны интерфейса.
     * 
     * @param string|null $side Сторона интерфейса: FRONTEND, BACKEND, SIDE. Если значение 
     *     `null`, то будет использоваться SIDE (по умолчанию `null`).
     * 
     * @return string
     */
    public function getLayoutPath(?string $side = null): string
    {
        if ($side === null) {
            $side = SIDE;
        }
        if (!isset($this->layoutPath[$side])) {
            $this->setLayoutPath($this->getViewPath() . DS . $side . DS . 'layouts', $side);
        }
        return $this->layoutPath[$side];
    }

    /**
     * Устанавливает абсолютный путь приложения к файлам макетов страниц.
     * 
     * @param string $path Путь к файлам макетов страниц.
     * @param string|null $side Сторона интерфейса: FRONTEND, BACKEND, SIDE.
     * 
     * @return void
     */
    public function setLayoutPath(string $path, string $side): void
    {
        $this->layoutPath[$side] = $path;
        Ge::setAlias('@app:' . $side . ':layouts', $path);
        Ge::setAlias('@app:layouts', $path);
    }

    /**
     * Проверяет, относится ли запрос пользователя к frontend.
     * 
     * @return bool Если `true`, запрос пользователя к frontend.
     */
    public function isFrontend(): bool
    {
        return !$this->isBackend();
    }

    /**
     * Проверяет, относится ли запрос пользователя к backend.
     * 
     * @return bool Если `true`, запрос пользователя к backend.
     */
    public function isBackend(): bool
    {
        static $backend = null;

        if ($backend === null) {
            $backend = $this->urlManager->isBackendRoute();
        }
        return $backend;
    }

    /**
     * Проверяет, установлен ли режим разметки для представлений.
     * 
     * Режим разметки применяется для изменения параметров в 
     * визуальном редакторе.
     * 
     * @see \Ge\Http\Request::validateMarkupToken()
     * 
     * @return bool
     */
    public function isViewMarkup(): bool
    {
        return $this->request->validateBuildToken();
    }

    /**
     * Возвращает имя стороны к которой был адресован запрос.
     * 
     * @return string Значение: `BACKEND`, `FRONTEND`.
     */
    public function getSide(): string
    {
        static $side = null;

        if ($side === null) {
            $side = $this->isBackend() ? BACKEND : FRONTEND;
        }
        return $side;
    }

    /**
     * Возвращает параметры конфигурации, используемые при загрузке шаблона представления.
     * 
     * Параметры конфигурации могут относится к одной из сторон: `BACKEND`, `FRONTEND` или
     * к обоим сразу.
     * 
     * Параметры используются в {@see Application::getView()} и в {@see Application::getLayoutView()}.
     * 
     * @return array
     */
    public function getViewConfig(): array
    {
        return isset($this->viewConfig[SIDE]) ? $this->viewConfig[SIDE] : $this->viewConfig;
    }

    /**
     * Возвращает представление.
     * 
     * @param array $config Параметры конфигурации представления.
     * 
     * @return View
     */
    public function getView(array $config = []): View
    {
        if (!isset($this->view)) {
            $this->view = new View($config ?: $this->getViewConfig());
        }
        return $this->view;
    }

    /**
     * Возвращает представление макета страницы.
     * 
     * @param array $config Параметры конфигурации макета.
     * 
     * @return LayoutView
     */
    public function getLayoutView(array $config = []): LayoutView
    {
        if (!isset($this->layoutView)) {
            $this->layoutView = new LayoutView($config ?: $this->getViewConfig());
        }
        return $this->layoutView;
    }

    /**
     * Инициализация и загрузка приложения.
     *
     * @return $this
     */
    public static function init(): static
    {
        try {
            $serviceManager = new ServiceManager;
            /** @var \Ge\Mvc\Application $application */
            $application = $serviceManager->get(static::class);
            $application->services = $serviceManager;

            Ge::$services = $serviceManager;
            Ge::$app      = $application;

            $application->bootstrap();
        } catch (Exception\BootstrapException $e) {
            $e->render();
        }
        return $application;
    }

    /**
     * Инициализация и загрузка приложения с параметрами.
     * 
     * Применяется только для того, чтобы приложение при своём запуске приняло только 
     * указанные параметры конфигурации вместо параметров указанных в файле конфигурации
     * 'config/.application.php'.
     * 
     * @param array<string, mixed> $params Параметры конфигурации приложения {@see Application::$config}.
     *
     * @return $this
     */
    public static function initWith(array $params = []): static
    {
        try {
            $serviceManager = new ServiceManager;
            /** @var \Ge\Mvc\Application $application */
            $application = $serviceManager->get(static::class);
            $application->services = $serviceManager;

            Ge::$services = $serviceManager;
            Ge::$app      = $application;

            $application->setConfigParams($params);
            $application->bootstrap();
        } catch (Exception\BootstrapException $e) {
            $e->render();
        }
        return $application;
    }

    /**
     * Начальная загрузка приложения.
     *
     * Создаются объекты ответа и запроса, маршрутизатор, конфигуратор,
     * локализация языков. Определяются и запускаются основные настройки приложения.
     *
     * @return $this
     */
    public function bootstrap(): static
    {
        $this->initLoader();
        // инициализация помощника
        $this->initHelper();
        // обработчик ошибок
        $this->registerErrorHandler();
        // директории и ресурсы приложения
        $this->getBasePath();
        $this->getModulePath();
        $this->getVendorPath();
        $this->getConfigPath();
        $this->getRuntimePath();
        $this->getViewPath();
        $this->getHomeUrl();
        $this->getModuleUrl();
        // инициализация глобальных переменных приложения
        $this->initVariables();
        // конфигуратор приложения
        $this->config = $this->getConfig();
        // унифицированный конфигуратор приложения
        $this->unifiedConfig = $this->config->factory('unified');
        // менеджер служб
        $this->services->config = $this->config->factory('services');
        $this->services->init();
        // инициализация параметров
        $this->initParameters();
        // инициализация кодировки
        $this->initEncoding();
        // локализация языков
        $this->initLocalization();
        // определение стороны запроса
        $this->initSide();
        $this->getLayoutPath();
        // инициализация даты и времени
        $this->initDateTime();
        // маршрутизатор запросов
        $this->router = $this->getRouter();
        // инициализация событий в приложении
        $this->initEvents();
        // инициализация служб
        $this->initServices();
        // инициализация темы
        $this->initTheme();
        // инициализация защиты
        $this->initDefense();
        // Если включена отладка ошибок, создаем логгер для активации его 
        // писателей (с атрибутам "autoCreate" = true).
        if (GE_DEBUG) {
            $this->logger;
        }
        return $this;
    }

    /**
     * Регистрирует обработчик ошибок.
     * 
     * Обработчик будет создан в том случае, если `GE_ENABLE_ERROR_HANDLER = true` 
     * в `bootstrap` приложения.
     * 
     * @see Application::$errorHandler
     * 
     * @return void
     */
    protected function registerErrorHandler(): void
    {
        if (GE_ENABLE_ERROR_HANDLER) {
            $this->errorHandler = new WebErrorHandler();
            $this->errorHandler->register();
        }
    }

    /**
     * Возвращает указатель на службу "Темы" для frontend.
     * 
     * @return Theme
     */
    public function createFrontendTheme(): Theme
    {
        return $this->services->createAs('frontendTheme');
    }

    /**
     * Возвращает указатель на службу "Темы" для backend.
     * 
     * @return Theme
     */
    public function createBackendTheme(): Theme
    {
        return $this->services->createAs('backendTheme');
    }

    /**
     * @param string $side
     * 
     * @return Theme|null
     */
    public function createThemeBySide(string $side): ?Theme
    {
        if ($side === BACKEND)
            return $this->services->createAs('backendTheme');
        else
        if ($side === FRONTEND)
            return $this->services->createAs('frontendTheme');
        else
            return null;
    }

    /**
     * Инициализация помощника.
     * 
     * @return void
     */
    protected function initHelper(): void
    {
        Helper::setApplication($this);
    }

    /**
     * Инициализация службы "Тема" для backend или frontend.
     * 
     * Для backend используется {@see Application::$backendTheme}.
     * Для frontend используется {@see Application::$frontendTheme}.
     * 
     * Служба для работы с текущей темой вызывается через {@see Application::$theme}.
     * 
     * @return void
     */
    protected function initTheme(): void
    {
        if (isset($this->theme)) return;

        $this->theme = IS_BACKEND ? $this->backendTheme : $this->frontendTheme;
        // устанавливаем тему по умолчанию
        $this->theme->set();
    }

    /**
     * Инициализация кодировки приложения.
     * 
     * @see https://www.php.net/manual/ru/function.mb-internal-encoding
     * 
     * @return void
     * 
     * @throws Exception\BootstrapException Невозможно установить кодировку приложению.
     */
    protected function initEncoding(): void
    {
        $encoding = $this->config->encoding;
        if (isset($encoding['internal'])) {
            $internal = $encoding['internal'];
            if ($internal && mb_internal_encoding($internal) !== false)
                $this->charset = $internal;
            else
                throw new Exception\BootstrapException(sprintf('Could not set internal encoding "%s"', $internal));
        } else
            throw new Exception\BootstrapException('Could not set internal encoding, property "encoding[internal]" not found.');
    }

    /**
     * Определяет сторону запроса если это HTTP-запрос (backend, frontend) или console.
     * 
     * Находится после {@see Application::initLocalization}, т.к. URL-адрес может иметь слаг языка.
     * 
     * @return void
     */
    protected function initSide(): void
    {
        // если работает установщик приложения, он определяет сторону запроса
        if (defined('IS_INSTALL_MODE')) return;

        /** @var bool Указывает на то, что веб-приложение работает c консолью. */
        define('IS_CONSOLE', PHP_SAPI === 'cli');
        if (!IS_CONSOLE) {
            /** @var bool Указывает на то, что веб-приложение работает с frontend. */
            define('IS_FRONTEND', $this->isFrontend());
            /** @var bool Указывает на то, что веб-приложение работает с backend. */
            define('IS_BACKEND', !IS_FRONTEND);
            /** @var string Название стороны (FRONTEND, BACKEND) с которой работает веб-приложение. */
            define('SIDE', IS_FRONTEND ? FRONTEND : BACKEND);

            // инициализация служб для backend
            if (IS_BACKEND) {
                $services = $this->config->{BACKEND}['services'] ?? null;
            // IS_FRONTEND
            // инициализация служб для frontend
            } else {
                $services = $this->config->{FRONTEND}['services'] ?? null;
            }
        } else {
            /** @var bool Указывает на то, что веб-приложение работает с frontend. */
            define('IS_FRONTEND', false);
            /** @var bool Указывает на то, что веб-приложение работает с backend. */
            define('IS_BACKEND', false);
            /** @var string Название стороны (FRONTEND, BACKEND) с которой работает веб-приложение. */
            define('SIDE', false);

            // инициализация служб для console
            $services = $this->config->{CONSOLE}['services'] ?? null;
        }

        /**
         * т.к. initSide стоит после initLocalization в {@see Application::bootstrap()}, а
         * initLocalization не знает о стороне запроса, то доступность языка для одной из сторон 
         *  можно проверить только здесь.
         */
        if (IS_BACKEND) {
            // язык не доступен (отключен) для backend, то язык будет по умолчанию
            if ($this->language->{BACKEND} === false) {
                $this->language->set($this->language->default);
            }
        } else
        if (IS_FRONTEND) {
            // язык не доступен (отключен) для frontend, то язык будет по умолчанию
            if ($this->language->{FRONTEND} === false) {
                $this->language->set($this->language->default);
            }
        }

        if ($services) {
            $this->services->config->append($services['factory']['filename']);
            $this->services->refresh();
        }
    }

    /**
     * Инициализация параметров приложения.
     *
     * @see Application::$params
     * 
     * @return void
     */
    protected function initParameters(): void
    {
        $this->params = $this->services->createAs('collection');
        $this->params->setAll($this->config->params ?? []);
    }

    /**
     * Инициализация локализации приложения.
     *
     * @return void
     */
    protected function initLocalization(): void
    {
        // если язык по умолчанию не определен самой службой, 
        // то он, определяется конфигурацией приложения
        if (empty($this->language->default)) {
            // если в конфигурации приложения установлен язык по умолчанию
            if (!empty($this->config->language) && !$this->services->config->isLoaded)
                $this->language->default = $this->config->language;
        }
        // определение языка по запросу
        $this->language->set($this->language->define());
        // добавление категории с автозагрузкой шаблонов локализации из 
        // настроек службы языка: language/autoload[...]
        $this->translator
            ->addCategory('app')
                ->autoloadLocalePatterns();
    }

    /**
     * Инициализация часового пояса.
     * 
     * @see \Ge\I18n\Formatter::$timeZone
     * 
     * @return void
     * 
     * @throws Exception\BootstrapException Ошибка установки часового пояса.
     */
    protected function initDateTime(): void
    {
        $timeZone = $this->unifiedConfig->formatter['timeZone'] ?? null;
        // если не указан параметр в настройках форматировщика
        if (empty($timeZone)) {
            $timeZone = $this->config->timeZone ?: (ini_get('date.timezone') ?: 'UTC');
        }
        $this->setTimeZone($timeZone);

        $timeZone = $this->unifiedConfig->formatter['defaultTimeZone'] ?? null;
        // если не указан параметр в настройках форматировщика
        if (empty($timeZone)) {
            $timeZone = $this->config->dataTimeZone ?: (ini_get('date.timezone') ?: 'UTC');
        }
        $this->setDataTimeZone($timeZone);
    }

    /**
     * Устанавливает часовой пояс по умолчанию для всего приложения.
     * 
     * @see https://www.php.net/manual/ru/function.date-default-timezone-set.php
     * 
     * @param string $timezone Часовой пояс (например: "Europe/London", "Europe/Moscow" 
     *     или "Europe/Berlin").
     * 
     * @return $this
     * 
     * @throws Exception\BootstrapException Ошибка установки часового пояса.
     */
    public function setTimeZone(string $timezone): static
    {
        if (!date_default_timezone_set($timezone)) {
            throw new Exception\BootstrapException(sprintf('Failed to set timezone "%s"', $timezone));
        }
        $this->timeZone = new \DateTimeZone($timezone);
        return $this;
    }

    /**
     * Устанавливает часовой пояс хранилищ (баз данных, кэша и т.п.) данных.
     * 
     * @param string $timezone Часовой пояс (например: "Europe/London", "Europe/Moscow" 
     *     или "Europe/Berlin").
     * 
     * @return $this
     */
    public function setDataTimeZone(string $timezone): static
    {
        $this->dataTimeZone = new \DateTimeZone($timezone);
        return $this;
    }

    /**
     * Возвращает часовой пояс.
     *
     * @see https://www.php.net/manual/ru/function.date-default-timezone-get.php
     * 
     * @return string
     */
    public function getTimeZone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * @return void
     */
    protected function initLoader(): void
    {
        Ge::$loader = require BASE_PATH . DS . 'vendor/autoload.php';
        Ge::$loader->addPsr4('App\\', 'app');
    }

    /**
     * Инициализация служб приложения.
     * 
     * Инициализацию проходят только те службы, которые указаны в параметре "bootstrap" 
     * конфигурации или в унифицированном конфигуратое приложения.
     * 
     * Для каждой службы будет вызван метод `bootstrap()`.
     * 
     * @return void
     */
    protected function initServices(): void
    {
        $services = [];
        // инициализация служб через параметр "bootstrap" конфигурации
        if (isset($this->unifiedConfig->bootstrap)) {
            $services = array_merge($this->unifiedConfig->bootstrap, $this->config->bootstrap);
        } else {
            $services = $this->config->bootstrap;
        }
        if ($services) {
            foreach($services as $name => $destination) {
                $forBackend  = $destination[BACKEND] ?? false;
                $forFrontend = $destination[FRONTEND] ?? false;
                if ((IS_BACKEND && $forBackend) || (IS_FRONTEND && $forFrontend)) {
                    if (method_exists($this->{$name}, 'bootstrap'))
                        $this->{$name}->bootstrap($this);
                }
            }
        }
    }

    /**
     * Инициализация событий компонентов приложения.
     *
     * @return $this
     */
    protected function initEvents(): static
    {
        $this->getEventListeners();

        $self = $this;
        // событие, возникшее после сравнения маршрутов
        $this->router->on(Router::EVENT_AFTER_ROUTE_MATCH, function ($route) use ($self) {
            // если маршрут найден
            if ($route) {
                $self
                    ->modules
                        ->getByRoute($route)
                            ->run();
                $self
                    ->response
                        ->send();
            }
        });
        return $this;
    }

    /**
     * Инициализация глобальных переменных приложения.
     * 
     * @return void
     */
    protected function initVariables(): void
    {
        Ge::setAlias('@frontend:', FRONTEND_NAME);
        Ge::setAlias('@backend:', BACKEND_NAME);
        Ge::setAlias('@app', $this->getBasePath());
    }

    /**
     * Инициализация защиты приложения.
     * 
     * Проверка белого и черного списков IP-адресов, определяющий доступ клиенту к 
     * контенту на стороне сайта или панели управления.
     * 
     * Списки IP-адресов указываются в параметре "defense", унифицированного конфигуратора 
     * приложения.
     * 
     * @see \Ge\IpManager\IpManager
     * 
     * @return void
     * 
     * @throws Exception\IpAddressNotAllowedHttpException Если IP-адресу запрещено просматривать контент.
     */
    protected function initDefense(): void
    {
        // если есть настройки проактивной защиты
        if ($this->unifiedConfig->defense) {
            if (IS_BACKEND) {
                // проверять белый список IP-адресов
                $enableWhiteListIp = $this->unifiedConfig->defense['enableBackendWhiteListIp'] ?? false;
                if ($enableWhiteListIp) {
                    $ipAddress = $this->request->getUserIp();
                    // если Ваш IP-адрес не входит в диапазон
                    if (!$this->ip->list('white')->inRange($ipAddress, BACKEND, true)) {
                        $exception = new Exception\IpAddressNotAllowedHttpException($ipAddress);
                        $exception->viewFile = $this->unifiedConfig->defense['backendViewTemplate'] ?? '//pages/error';
                        throw $exception;
                    }
                } else {
                    // проверять черный список IP-адресов
                    $enableBlackListIp = $this->unifiedConfig->defense['enableBackendBlackListIp'] ?? false;
                    if ($enableBlackListIp) {
                        $ipAddress = $this->request->getUserIp();
                        // если Ваш IP-адрес входит в диапазон
                        if ($this->ip->list('black')->inRange($ipAddress, BACKEND, true)) {
                            $exception = new Exception\IpAddressNotAllowedHttpException($ipAddress);
                            $exception->viewFile = $this->unifiedConfig->defense['backendViewTemplate'] ?? '//pages/error';
                            throw $exception;
                        }
                    }
                }
            // IS_FRONTEND
            } else {
                // проверять белый список IP-адресов
                $enableWhiteListIp = $this->unifiedConfig->defense['enableFrontendWhiteListIp'] ?? false;
                if ($enableWhiteListIp) {
                    $ipAddress = $this->request->getUserIp();
                    // если Ваш IP-адрес не входит в диапазон
                    if (!$this->ip->list('white')->inRange($ipAddress, FRONTEND, true)) {
                        $exception = new Exception\IpAddressNotAllowedHttpException($ipAddress);
                        $exception->viewFile = $this->unifiedConfig->defense['frontendViewTemplate'] ?? '//pages/error';
                        throw $exception;
                    }
                } else {
                    // проверять черный список IP-адресов
                    $enableBlackListIp = $this->unifiedConfig->defense['enableFrontendBlackListIp'] ?? false;
                    if ($enableBlackListIp) {
                        $ipAddress = $this->request->getUserIp();
                        // если Ваш IP-адрес входит в диапазон
                        if ($this->ip->list('black')->inRange($ipAddress, FRONTEND, true)) {
                            $exception = new Exception\IpAddressNotAllowedHttpException($ipAddress);
                            $exception->viewFile = $this->unifiedConfig->defense['frontendViewTemplate'] ?? '//pages/error';
                            throw $exception;
                        }
                    }
                }
            }
        }
    }

    /**
     * Последнее исключение приложения.
     * 
     * Здесь будет поймано исключение если оно ранее нигде не было поймано.
     * 
     * @param Throwable $exception Исключение.
     * 
     * @return void
     * 
     * @throws \Exception Исключение если не указан обработчик ошибок {@see Application::$errorHandler}.
     */
    public function endException(Throwable  $exception): void
    {
        if (isset($this->errorHandler)) {
            $this->errorHandler->uncatchableException($exception);
        } else
            throw $exception;
    }

    /**
     * Если маршрутизатор не нашел модуль.
     *
     * @return void
     * 
     * @throws Exception\PageNotFoundException
     */
    public function routeNotFound(): void
    {
        throw new Exception\PageNotFoundException();
    }

    /**
     * Событие перед запуском приложения.
     *
     * @return void
     * 
     * @throws Exception\BaseException
     */
    protected function beforeRun(): void
    {
    }

    /**
     * Событие после запуска приложения.
     *
     * @return void
     */
    protected function afterRun(): void
    {
    }

    /**
     * Запуск приложения.
     *
     * @return $this
     * 
     * @throws Exception\BaseException
     */
    public function run(): static
    {
        try {
            $this->beforeRun();

            $this->router->run();
            // если ни один из модулей не найден маршрутизатором
            if ($this->module === null) {
                $this->routeNotFound();
            }
        } catch (Exception\BaseException $e) {
            $this->endException($e);
        }

        $this->afterRun();
        return $this;
    }

    /**
     * Выполняет действие контроллера модуля или расширения модуля по указанной 
     * сигнатуре (записи).
     * 
     * Сигнатура (записи) компонента указывается в виде строки {@see \Ge::signatureToArray()} 
     * или массива элементов.
     * 
     * @param string|array<string, string> $signature Cигнатура или элементы сигнатуры.
     * @param array $actionParams Параметры передаваемые в действие контроллера (по умолчанию `[]`).
     * @param array $params Параметры модуля или расширения, передаваемые в конструктор (по умолчанию `[]`).
     * 
     * @return void
     */
    public function runAs(string|array $signature, array $actionParams = [], array $params = []): void
    {
        if (is_string($signature)) {
            $signature = Ge::signatureToArray($signature, true);
        }
        // если указан модуль
        if ($signature['type'] === 'module') {
            $this->modules->run(
                $signature['id'], $signature['controller'], $signature['action'], $actionParams, $params
            );
            $this->response->send();
        } else
        // если указано расширение модуля
        if ($signature['type'] === 'extension') {
            $this->extensions->run(
                $signature['id'], $signature['controller'], $signature['action'], $actionParams, $params
            );
            $this->response->send();
        } else
            throw new Exception\InvalidArgumentException(
                'The component type in the signature is specified incorrectly.'
            );
    }

    /**
     * Возвращает форматтер.
     * 
     * Применяется для {@see Application::__get()}.
     * 
     * @return Formatter
     * 
     * @throws Exception\NotInstantiableException
     */
    protected function getFormatter(): Formatter
    {
        return $this->services->getAs('formatter', [], [
            'timeZone'      => $this->timeZone,
            'locale'        => $this->language->locale,
            'messageSource' => $this->translator->getCategory('app')
        ]);
    }

    /**
     * Возвращает объект (экземпляр класса службы) по указанному имени.
     * 
     * @see Application::$services
     * 
     * @param string $serviceName Имя службы.
     * 
     * @return mixed Возвращает значение `null`, если службу с указанным именем 
     *     невозможно создать.
     */
    public function &__get(string $serviceName)
    {
        $calledMethod = 'get' . $serviceName;
        if (method_exists($this, $calledMethod))
            $service = call_user_func([$this, $calledMethod]);
        else
            $service = $this->services->getAs($serviceName);

        // deprecated PHP 8.2 (creation of dynamic property)
        @$this->{$serviceName} = $service;
        return $service;
    }

    /**
     * Проверяет, создана ли служба с указанным именем.
     * 
     * @param string $serviceName Имя службы.
     * 
     * @return bool Возвращает значение `true`, если службв ранее создана.
     */
    public function has(string $serviceName): bool
    {
        return $this->services->has($serviceName);
    }

    /**
     * Возвращает слушателей событий - компоненты (модуль, расширение модуля, виджет).
     *
     * @return EventListeners
     */
    public function getEventListeners(): EventListeners
    {
        if (!isset($this->listeners)) {
            return $this->listeners = new EventListeners('config/.events.php', true);
        }
        return $this->listeners;
    }

    /**
     * Вызывает событие у слушателя - компонента (модуль, расширение модуля, виджет).
     * 
     * @param string $name Название события.
     * @param array $args Параметры передаваемые событием.
     * 
     * @return void
     */
    public function doEvent(string $name, array $args = []): void
    {
        $this->listeners->doEvent($name, $args);
    }
}
