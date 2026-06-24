<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Extension;

use Ge;
use Ge\Helper\Url;
use Ge\Config\Config;
use Ge\View\ViewManager;
use Ge\Stdlib\Collection;
use Ge\Session\Container as SessionContainer;

/**
 * Веб-модуль имеет репозиторий и предоставляет доступ к своим веб-ресурсам (CSS, JS, и т.д.).
 * 
 * В модуль добавлен репозиторий 'assets', который доступен через URL-адрес {@see Module::getAssetsUrl()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Extension
 * @since 2.0
 */
class Extension extends BaseExtension
{
    /**
     * Параметры конфигурации установленного расширения.
     * 
     * @see Extension::getInstalledParam()
     * @see Extension::getInstalledParams()
     * 
     * @var array
     */
    protected array $installedParams;

    /**
     * Абсолютный (базовый) URL-адрес.
     * 
     * @see Extension::getBaseUrl()
     * 
     * @var string
     */
    protected string $baseUrl;

    /**
     * URL-путь к подключению скриптов расширения.
     * 
     * @see Extension::getRequireUrl()
     * 
     * @var string
     */
    protected string $requireUrl;

    /**
     * Абсолютный (базовый) URL-адрес ресурса расширения.
     * 
     * @see Extension::getAssetsUrl()
     * 
     * @var string
     */
    protected string $assetsUrl;

    /**
     * URL-путь расширения.
     * 
     * @see Extension::getUrlPath()
     * 
     * @var string
     */
    protected string $urlPath;

    /**
     * Абсолютный (базовый) путь к ресурсам расширения.
     * 
     * @see Extension::getAssetsPath()
     * 
     * @var string
     */
    protected string $assetsPath;

   /**
     * Менеджер представлений.
     * 
     * @see Extension::getViewManager()
     * 
     * @var ViewManager
     */
    protected ViewManager $viewManager;

    /**
     * Временное хранилище (контейнер) данных расширения.
     * 
     * @see Module::getStorage()
     * 
     * @var SessionContainer
     */
    protected SessionContainer $storage;

    /**
     * Конфигуратор расширения.
     * 
     * @see Module::getConfig()
     * 
     * @var Config
     */
    protected Config $config;

    /**
     * Настройки расширения.
     * 
     * @var Config
     */
    protected Config $settings;

    /**
     * {@inheritdoc}
     */
    protected function initTranslations(): void
    {
        Ge::$app->translator
            ->addCategory($this->id, $this->getConfigParam('translator'));
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес ресурса расширения.
     * 
     * Имеет вид: "</абсолютный (базовый) URL-адрес> </assets>".  
     * Пример: `'http://domain/modules/rg/rg.foobar/assets'`.
     * 
     * @return string
     */
    public function getAssetsUrl(): string
    {
        if (!isset($this->assetsUrl)) {
            $this->assetsUrl = $this->getBaseUrl() . '/assets';
        }
        return $this->assetsUrl;
    }

    /**
     * Возвращает относительный URL-адрес ресурса расширения.
     * 
     * Имеет вид: "</относительный URL-адрес> </assets>".  
     * Пример: `'/modules/rg/rg.foobar/assets'`.
     * 
     * @see Extension::getUrlPath()
     * 
     * @return string
     */
    public function getRelativeAssetsUrl(): string
    {
        return MODULE_BASE_URL . $this->getUrlPath() . '/assets';
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес расширения.
     * 
     * Имеет вид: "<адрес хоста> </абсолютный URL-адрес расширений> </локальный путь>".  
     * Пример: `'http://domain/modules/rg.foobar'`.
     * 
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (!isset($this->baseUrl)) {
            $this->baseUrl = Ge::$app->moduleUrl . $this->getUrlPath();
        }
        return $this->baseUrl;
    }
    /**
     * Возвращает URL-путь из локального пути расширения.
     *
     * Пример: `'Ge\FooBar' => 'Ge/FooBar'`.
     * 
     * @return string
     */
    public function getUrlPath(): string
    {
        if (!isset($this->urlPath)) {
            $this->urlPath = OS_WINDOWS ? str_replace(DS, '/', $this->path) : $this->path;
        }
        return $this->urlPath;
    }

    /**
     * Возвращает URL-путь для подключения скриптов расширения.
     * 
     * Имеет вид: "</URL-путь корня хоста> </локальный URL-путь расширений> </локальный путь> </assets>".
     * Пример: `'/modules/rg.foobar/assets'`.
     * 
     * @return string
     */
    public function getRequireUrl(): string
    {
        if (!isset($this->requireUrl)) {
            $this->requireUrl = Url::home(false) . MODULE_BASE_URL . $this->getUrlPath() . '/assets';
        }
        return $this->requireUrl;
    }

    /**
     * Возвращает абсолютный (базовый) путь к ресурсам расширения.
     * 
     * Имеет вид: "</абсолютный путь> </assets>".
     * Пример: `'/home/host/public_html/module/Extension/Foo/Bar/assets'`.
     * 
     * @return string
     */
    public function getAssetsPath(): string
    {
        if (!isset($this->assetsPath)) {
            $this->assetsPath = $this->basePath . DS . 'assets';
        }
        return $this->assetsPath;
    }

    /**
     * Создает идентификатор хранилища (контейнера) модуля.
     * 
     * Использует контейнер {@see BaseModule::$storage}.
     * 
     * @return string
     */
    public function makeStorageId(): string
    {
        return str_replace('.', '_', $this->id);
    }

    /**
     * Возвращает временное хранилище (контейнер) данных модуля.
     * 
     * Контейнер может хранить значения переменных модуля, которые могут использоваться 
     * в процессе его работы.
     * Пример:
     * ```php
     * $module->storage->fooBar = 'foobar';
     * ```
     * 
     * @see BaseModule::$storage
     * 
     * @return SessionContainer Временное хранилище данных расширения.
     */
    public function getStorage(): SessionContainer
    {
        if (!isset($this->storage)) {
            $this->storage = new SessionContainer($this->makeStorageId());
        }
        return $this->storage;
    }

    /**
     * Создаёт (генерирует) идентификатор элемента для вывода его в моделе представления.
     * 
     * Такой идентификатор является уникальным для элемента HTML и создаётся на основе
     * шаблона.   
     * Пример: 'g-element-{name}', где {name} - имя выводимого элемента.
     * 
     * Шаблон идентификатора определяется параметром "id" в файле конфигурации расширения ".extension.php".
     * Пример:
     * ```php
     * return [
     *     'viewManager' => [
     *         'id' => 'g-element-{name}',
     *         ...
     *     ],
     *     ...
     * ];
     * ```
     * 
     * @param string $name Имя выводимого элемента (пример: 'button').
     * 
     * @return string
     */
    public function viewId(string $name): string
    {
        $viewManager = $this->getConfigParam('viewManager');
        if (empty($viewManager['id'])) {
            Ge::warning(Ge::t('app', 'Module could not make id for view model'));
            return $name . '-' .uniqid();
        }
        return strtr($viewManager['id'], ['{name}' => $name]);
    }

    /**
     * Возвращает права доступа к модулю.
     * 
     * @see BaseModule::$permission
     * 
     * @return ExtensionPermission
     */
    public function getPermission(): ExtensionPermission
    {
        if (!isset($this->permission)) {
            $this->permission = new ExtensionPermission($this);
        }
        return $this->permission;
    }

    /**
     * Возвращает маршрут модуля.
     * 
     * Маршрут модуля указывается в конфигурации установки модуля ".install.php" в 
     * параметре "route" или в свойстве класса.
     * 
     * @see Module::getInstalledParam()
     *
     * @return string
     */
    public function getRoute(): string
    {
        if (!isset($this->route)) {
            $this->route = $this->getInstalledParam('route', '');
        }
        return $this->route;
    }

    /**
     * Возвращает маршрут расширения модуля, который включает маршрут сопоставления.
     * 
     * Маршрут сопоставления - это маршрут, полученный маршрутизатором при поиске 
     * модуля, путём сопоставления маршрута модуля с маршрутом в URL-адресе.
     * 
     * Пример запроса: `https:://domain.com/admin/user/account/panel/view`, где:
     * - 'admin/user', маршрут сопоставления;
     * - 'user', маршрут модуля;
     * - 'acount', маршрут расширения;
     * - 'panel', контроллер расширения;
     * - 'view', действие контроллера расширения.  
     * Результат выполнения метода: 'admin/user/account'.
     * 
     * @see Extension::getRoute()
     * 
     * @param string $route Маршрут добавляемый к результату выполнения метода (по умолчанию '').
     *
     * @return string
     */
    public function route(string $route = '', bool $onlyParent = false): string
    {
        if ($onlyParent)
            return $this->parent->getRoute() . '/' . $this->getRoute() . $route;
        else
            return Ge::alias('@match', '/' . $this->getRoute() . $route);
    }

    /**
     * Возвращает параметры конфигурации установленного расширения.
     * 
     * Такие параметры находятся в файле конфигурации приложения ".extensions.php" (".extensions.so.php").
     * 
     * @return array
     */
    public function getInstalledParams(): array
    {
        if (!isset($this->installedParams)) {
            $this->installedParams = Ge::$app->extensions->getRegistryParams($this->id, []);
        }
        return $this->installedParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstalledParam(string $name, mixed $default = null): mixed
    {
        if (!isset($this->installedParams)) {
            $this->installedParams = Ge::$app->extensions->getRegistryParams($this->id, []);
        }
        if ($this->installedParams) {
            return $this->installedParams[$name] ?? $default;
        }
        return $default;
    }

    /**
     * Возвращает настройки расширения.
     * 
     * @see Extension::$settings
     * 
     * @return Config
     */
    public function getSettings(): Config
    {
        if (!isset($this->settings)) {
            $this->settings = new Config($this->basePath . DS . 'config' . DS . '.settings.php', true);
        }
        return $this->settings;
    }

    /**
     * Возвращает параметры конфигурации расширения.
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        if (!isset($this->config)) {
            $this->config = new Config($this->basePath . DS . 'config' . DS . '.extension.php', false);
        }
        return $this->config;
     }

    /**
     * Возвращает версию расширения.
     * 
     * @see \Ge\ExtensionManager\ExtensionManager::getConfigVersion()
     * 
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров версии. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * @param bool $usePattern Использовать шаблон параметров версии, только для ассоциативного 
     *     массива параметров (по умолчанию `true`).
     * 
     * @return Collection|array<string, mixed>|null Если значение `null`, то невозможно получить 
     *     информацию о модуле.
     */
    public function getConfigVersion(bool $associative = true, bool $usePattern = true): Collection|array|null
    {
        return Ge::$app->extensions->getConfigVersion($this->path, $associative, $usePattern);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): ?array
    {
        return $this->getConfigVersion(true, true);
    }
}
