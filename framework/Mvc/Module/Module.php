<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @see https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Module;

use Ge;
use Ge\Helper\Url;
use Ge\Stdlib\Collection;
use Ge\ServiceManager\PluginManager;
use Ge\Session\Container as SessionContainer;

/**
 * Веб-модуль имеет репозиторий и предоставляет доступ к своим веб-ресурсам (CSS, JS, и т.д.).
 * 
 * В модуль добавлен репозиторий 'assets', который доступен через URL-адрес {@see Module::getAssetsUrl()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Module
 * @since 2.0
 */
class Module extends BaseModule
{
    /**
     * Параметры конфигурации установленного модуля.
     * 
     * @see Module::getInstalledParam()
     * @see Module::getInstalledParams()
     * 
     * @var array
     */
    protected array $installedParams;

    /**
     * Абсолютный (базовый) URL-адрес.
     * 
     * @see Module::getBaseUrl()
     * 
     * @var string
     */
    protected string $baseUrl;

    /**
     * URL-путь к подключению скриптов модуля.
     * 
     * @see Module::getRequireUrl()
     * 
     * @var string
     */
    protected string $requireUrl;

    /**
     * Абсолютный (базовый) URL-адрес ресурса модуля.
     * 
     * @see Module::getAssetsUrl()
     * 
     * @var string
     */
    protected string $assetsUrl;

    /**
     * URL-путь модуля.
     * 
     * @see Module::getUrlPath()
     * 
     * @var string
     */
    protected string $urlPath;

    /**
     * Абсолютный (базовый) путь к ресурсам модуля.
     * 
     * @see Module::getAssetsPath()
     * 
     * @var string
     */
    protected string $assetsPath;

    /**
     * Менеджер плагинов модуля.
     * 
     * @var PluginManager
     */
    protected PluginManager $pluginManager;

    /**
     * Временное хранилище (контейнер) данных модуля.
     * 
     * @see Module::getStorage()
     * 
     * @var SessionContainer
     */
    protected SessionContainer $storage;

    /**
     * {@inheritdoc}
     */
    protected function initTranslations(): void
    {
        Ge::$app->translator
            ->addCategory($this->id, $this->getConfigParam('translator'));
    }

    /**
     * {@inheritdoc}
     */
    public function extensionMap(): array
    {
        return $this->getInstalledParam('extensions', []);
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес ресурса модуля.
     * 
     * Имеет вид: "</абсолютный (базовый) URL-адрес> </assets>".  
     * Пример: 'http://domain/modules/rg/rg.foobar/assets'.
     * 
     * @see Module::getBaseUrl()
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
     * Возвращает относительный URL-адрес ресурса модуля.
     * 
     * Имеет вид: "</относительный URL-адрес> </assets>".  
     * Пример: `'/modules/rg/rg.foobar/assets'`.
     * 
     * @see Module::getUrlPath()
     * 
     * @return string
     */
    public function getRelativeAssetsUrl(): string
    {
        return MODULE_BASE_URL . $this->getUrlPath() . '/assets';
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес модуля.
     * 
     * Имеет вид: "<адрес хоста> </абсолютный URL-адрес модулей> </локальный путь>".  
     * Пример: 'http://domain/modules/rg.foobar'.
     * 
     * @see Module::getUrlPath()
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
     * Возвращает URL-путь из локального пути модуля.
     *
     * Пример: 'Ge\FooBar' => 'Ge/FooBar'.
     * 
     * @see Module::$urlPath
     * @see Module::$path
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
     * Возвращает URL-путь для подключения скриптов модуля.
     * 
     * Имеет вид: "</URL-путь корня хоста> </локальный URL-путь модулей> </локальный путь> </assets>".
     * Пример: '/modules/rg.foobar/assets'.
     * 
     * @see Module::getUrlPath()
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
     * Возвращает абсолютный (базовый) путь к ресурсам модуля.
     * 
     * Имеет вид: "</абсолютный путь> </assets>".
     * Пример: '/home/host/public_html/modules/rg.foobar/assets'.
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
     * @return SessionContainer Временное хранилище данных модуля.
     */
    public function getStorage(): SessionContainer
    {
        if (!isset($this->storage)) {
            $this->storage = new SessionContainer($this->makeStorageId());
        }
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function storageSet(string $key, mixed $value): void
    {
        /** @var SessionContainer $storage */
        $storage = $this->getStorage();
        $storage->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function storageGet(string $key, mixed $default = null): mixed
    {
        /** @var SessionContainer $storage */
        $storage = $this->getStorage();
        $value = $storage->get($key);
        return $value === null ? $default : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function storageRemove(string $key): void
    {
        /** @var SessionContainer $storage */
        $storage = $this->getStorage();
        $storage->remove($key);
    }

    /**
     * Создаёт (генерирует) идентификатор элемента для вывода его в представлении.
     * 
     * Такой идентификатор является уникальным для элемента HTML и создаётся на основе
     * шаблона.   
     * Пример: 'g-element-{name}', где {name} - имя выводимого элемента.
     * 
     * Шаблон идентификатора определяется параметром "id" в файле конфигурации модуля ".module.php".
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
     * @param string $name Имя выводимого элемента для которого создаётся идентификатор, 
     *     например 'button'.
     * 
     * @return string
     */
    public function viewId(string $name): string
    {
        $viewManager = $this->getConfigParam('viewManager');
        if (empty($viewManager['id'])) {
            Ge::warning(Ge::t('app', 'Module could not make id for view model'));
            return uniqid();
        }
        return strtr($viewManager['id'], ['{name}' => $name]);
    }

    /**
     * Возвращает менеджер плагинов модуля.
     * 
     * @see BaseModule::$pluginManager
     * 
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        if (!isset($this->pluginManager)) {
            $this->pluginManager = Ge::$services->get(
                'pluginManager', $this->getConfigParam('pluginManager')
            );
        }
        return $this->pluginManager;
    }

    /**
     * Возвращает маршрут модуля.
     * 
     * Маршрут модуля указывается в конфигурации установки модуля ".install.php" в 
     * параметре "route" или в свойстве класса.
     * 
     * @see BaseModule::$route
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
     * Возвращает параметры конфигурации установленного модуля.
     * 
     * Такие параметры находятся в файле конфигурации приложения ".modules.php" (".modules.so.php").
     * 
     * @return array<string, mixed>
     */
    public function getInstalledParams(): array
    {
        if (!isset($this->installedParams)) {
            $this->installedParams = Ge::$app->modules->getInstalledParams($this->id, []);
        }
        return $this->installedParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstalledParam(string $name, mixed $default = null): mixed
    {
        if (!isset($this->installedParams)) {
            $this->installedParams = Ge::$app->modules->getRegistryParams($this->id, []);
        }

        if ($this->installedParams) {
            return $this->installedParams[$name] ?? $default;
        }
        return $default;
    }

    /**
     * Возвращает идентификатор записи модуля в базе данных.
     * 
     * @see Module::getInstalledParam()
     * 
     * @return int|null
     */
    public function getRowId(): ?int
    {
        return $this->getInstalledParam('rowId');
    }

    /**
     * Возвращает версию модуля.
     * 
     * @see \Ge\ModuleManager\ModuleManager::getConfigVersion()
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
        return Ge::$app->modules->getConfigVersion($this->path, $associative, $usePattern);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): ?array
    {
        return $this->getConfigVersion(true, true);
    }
}
