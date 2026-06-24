<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge;
use Ge\Exception;
use Ge\Db\QueriesMap;
use Ge\Mvc\Application;
use Ge\Stdlib\BaseObject;
use Ge\Version\AppVersion;
use Ge\ServiceManager\ServiceManager;

/**
 * Класс установщика приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class Installer extends BaseObject
{
    /**
     * Используемый язык по умолчанию, если о не задан в файле конфигурации 
     * установщика.
     * 
     * Например: 'ru', 'en' и т.д.
     * 
     * @see Installer::getTranslator()
     * 
     * @var string
     */
    public string $language = 'en';

    /**
     * Имя файла или каталога, который блокирует работу установщика.
     * 
     * @var string
     */
    public string $lockName = '.install';

    /**
     * Маршрут к установщику.
     * 
     * @var string
     */
    public string $route = '';

    /**
     * Локальный путь к установщику.
     * 
     * @var string
     */
    public string $localPath = '';

    /**
     * Обработчик ошибок.
     * 
     * @see Application::registerErrorHandler()
     * 
     * @var InstallerErrorHandler
     */
    public InstallerErrorHandler $errorHandler;

     /**
     * Маршрутизатор запросов установщика.
     *
     * @see Installer::getRouter()
     * 
     * @var InstallerRouter
     */
    protected InstallerRouter $router;

    /**
     * Представление установщика.
     * 
     * @see Installer::getView()
     * 
     * @return InstallerView
     */
    protected InstallerView $view;

    /**
     * Абсолютный путь к установщику.
     * 
     * @see Installer::getPath()
     * 
     * @var string
     */
    protected string $path;

    /**
     * Доступность установщика к запуску.
     * 
     * @see Installer::accessibly()
     * 
     * @var bool
     */
    protected bool $accessibly = false;

    /**
     * Абсолютный URL-адрес установщика.
     * 
     * @see Installer::getUrl()
     * 
     * @var string
     */
    protected string $url;

    /**
     * Шаги установки.
     * 
     * @see Installer::getSteps()
     * 
     * @var array|InstallerSteps
     */
    protected array|InstallerSteps $steps;

    /**
     * Конфигуратор установщика.
     * 
     * @see Installer::getConfig()
     * 
     * @var InstallerConfig
     */
    protected InstallerConfig $config;

    /**
     * Переводчик установщика.
     * 
     * @see Installer::getTranslator()
     * 
     * @var InstallerTranslator
     */
    protected InstallerTranslator $translator;

    /**
     * Устанавливаемое приложение.
     * 
     * @see Installer::getApplication()
     * 
     * @var Application
     */
    protected Application $app;

    /**
     * Карта SQL-запросов.
     * 
     * @see Installer::getQueriesMap()
     * 
     * @var QueriesMap
     */
    protected QueriesMap $queriesMap;

    /**
     * Выполняет перевод указанного сообщения.
     * 
     * Пример:
     * ```php
     * t('Hi %s', ['Ivan']); // Hi Ivan
     * ```
     * 
     * @see Installer::getTranslator()
     * 
     * @param string $message Сообщение перевода.
     * @param array $args Параметры сообщения.
     * 
     * @return string
     */
    public function t(string $message, array $args = []): string
    {
        return $this->getTranslator()->translate($message, $args);
    }

    /**
     * Возвращает переводчик установщика.
     *
     * @return InstallerTranslator
     */
    public function getTranslator(): InstallerTranslator
    {
        if (!isset($this->translator)) {
            $this->translator = new InstallerTranslator([
                'path'     => $this->getPath() . DS . 'lang',
                'autoload' => true,
                'locale'   => $this->getLocale()
            ]);
        }
        return $this->translator;
    }

    /**
     * Возвращает текущий язык установки.
     * 
     * Например: 'ru-RU', 'en-GB', ...
     * 
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->config->language ?? $this->language;
    }

    /**
     * @see Installer::isRu()
     * 
     * @var bool
     */
    protected bool $_isRu;

    /**
     * Проверяет, является ли текущий язык установки русским.
     * 
     * @return bool
     */
    public function isRu(): bool
    {
        if (!isset($this->_isRu)) {
            $language = $this->getLanguage();
            $this->_isRu = $language === 'ru' || $language = 'ru-RU';
        }
        return $this->_isRu;
    }

    /**
     * Возвращает имя локализации.
     * 
     * Например: 'ru_RU', 'en_GB', ...
     * 
     * @return string
     */
    public function getLocale(): string
    {
        if (empty($this->config->language)) {
            return str_replace('-', '_', $this->language);
        }

        $language = $this->config->language ?? $this->language;
        if (isset($this->config->languages[$language]))
            return $this->config->languages[$language]['locale'];
        else
            return str_replace('-', '_', $language);
    }

    /**
     * Возвращает папки для поиска.
     * 
     * @return array
     */
    public function getSearchFolders(): array
    {
        return $this->config->searchFolders ?: [];
    }

    /**
     * Возвращает конфигуратор установщика.
     * 
     * @return InstallerConfig
     */
    public function getConfig(): InstallerConfig
    {
        if (!isset($this->config)) {
            $this->config = new InstallerConfig($this->getPath() . DS . 'config' . DS . '.setup.php', true);
        }
        return $this->config;
    }

    /**
     * Создаёт устанавливаемое приложение.
     * 
     * @param bool $withParams Если значение `true`, то вместо файла конфигурации 
     *     приложения '.application.php' будут указаны параметры. Это необходимо 
     *     чтобы приложение работало во время установки с минимальным количеством 
     *     параметров (по умолчанию `false`).
     * 
     * @return Application
     */
    public function createApp(bool $withParams = false): Application
    {
        return require_once BASE_PATH . DS . 'bootstrap/app.php';
    }

    /**
     * Возвращает устанавливаемое приложение.
     * 
     * @param bool $withParams Если значение `true`, то вместо файла конфигурации 
     *     приложения '.application.php' будут указаны параметры. Это необходимо 
     *     чтобы приложение работало во время установки с минимальным количеством 
     *     параметров (по умолчанию `false`).
     * 
     * @return Application
     */
    public function getApp(bool $withParams = false): Application
    {
        if (!isset($this->app)) {
            $this->app = $this->createApp($withParams);
        }
        return $this->app;
    }

    /**
     * Возвращает параметры файла конфигурацию приложения.
     * 
     * @param string $filename Имя файла конфигурации.
     * @param bool $includePath Если значение `true`, файл конфигурации включает 
     *     путь (по умолчанию `false`).
     * 
     * @return array|null Возвращает значение `null`, если ошибка чтения файла.
     */
    public function getAppConfig(string $filename, bool $includePath = false): ?array
    {
        if (!$includePath) {
            $filename = BASE_PATH . CONFIG_PATH . DS . $filename;
        }

        if (file_exists($filename)) {
            return include($filename);
        }
        return null;
    }

    /**
     * Версия приложения.
     * 
     * @see Installer::getAppVersion()
     * 
     * @var AppVersion
     */
    protected AppVersion $_appVersion;

    /**
     * Возвращает версию приложения.
     * 
     * @return AppVersion
     */
    public function getAppVersion(): AppVersion
    {
        if (!isset($this->_appVersion)) {
            require_once(BASE_PATH . DS . 'app/Version.php');
            $this->_appVersion = new \App\Version();
        }
        return $this->_appVersion;
    }

    /**
     * Возвращает версию редакции приложения.
     * 
     * @return array|null Возвращает значение `null`, если ошибка чтения файла 
     *     конфигурации приложения.
     */
    public function getAppEdition(): ?array
    {
        $params = $this->getAppConfig('.application.sample.php');
        if ($params) {
            return $params['edition'] ?? null;
        }
        return null;
    }

    /**
     * Возвращает лицензионный ключ приложения.
     * 
     * @return string|null
     */
    public function getAppLicenseKey(): ?string
    {
        $key = $this->getAppConfig('.license.php');
        return $key ?: null;
    }

    /**
     * Возвращает абсолютный путь к установщику.
     * 
     * @see Installer::$path
     * 
     * @return string Абсолютный путь к установщику.
     */
    public function getPath(): string
    {
        if (!isset($this->path)) {
            $this->path = BASE_PATH . MODULE_PATH . $this->localPath;
        }
        return $this->path;
    }

    /**
     *  Возвращает абсолютный путь к файлам представлений (шаблонам).
     * 
     * @return string
     */
    public function getViewPath(): string
    {
        return $this->getPath() . DS . 'views';
    }

    /**
     *  Возвращает абсолютный путь к файлам SQL-карт запроса.
     * 
     * @return string
     */
    public function getQueriesPath(): string
    {
        return $this->getPath() . DS . 'queries';
    }

    /**
     * Возвращает карту SQL-запросов.
     * 
     * @see Installer::$queriesMap
     * 
     * @param array $config Параметры конфигурации карты SQL-запросов.
     * 
     * @return QueriesMap
     */
    public function getQueriesMap(array $config = []): QueriesMap
    {
        if (!isset($this->queriesMap)) {
            $this->queriesMap = new QueriesMap($config);
        }
        return $this->queriesMap;
    }

    /**
     * Возвращает URL-адрес установщика.
     * 
     * @return string
     */
    public function getUrl(): string
    {
        if (!isset($this->url)) {
            $this->url = BASE_URL . MODULE_BASE_URL . $this->localPath;
        }
        return $this->url;
    }

    /**
     * Возврашает URL-адрес шага установки приложения.
     * 
     * @param string|null $stepName Название шага установки, например: 'foobar', 
     *     'choice:foobar'. Если значение `null`, название текущего шага (по умолчанию `null`).
     * 
     * @return string
     */
    public function makeUrl(?string $stepName = null): string
    {
        $url = '/' . $this->route;
        if ($stepName) {
            $url .= '/?' . $this->getSteps()->stepParam . '=' . $stepName;
        }
        return $url;
    }

    /**
     * Возврашает URL-адрес ресурсов установщика.
     * 
     * @return string
     */
    public function getAssetsUrl(): string
    {
        return $this->getUrl() . '/assets';
    }

    /**
     * Инициализация и загрузка установщика.
     *
     * @return Installer
     */
    public static function init(): Installer
    {
        try {
            $serviceManager = new ServiceManager;
            /** @var \Ge\Mvc\Application $application */
            $installer = $serviceManager->get(static::class);
            Ge::$services   = $serviceManager;
            $installer->bootstrap();
        } catch (Exception\BootstrapException $e) {
            $e->render();
        }
        return $installer;
    }

    /**
     * Начальная загрузка установщика.
     *
     * @return $this
     */
    public function bootstrap(): static
    {
        $this->initLoader();
        $this->initSide();
        $this->registerErrorHandler();
        $this->getPath();
        $this->getConfig();
        $this->getRouter();
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
            $this->errorHandler = new InstallerErrorHandler();
            $this->errorHandler->register();
        }
    }

    /**
     * Инициализация загрузчика.
     * 
     * @return void
     */
    protected function initLoader(): void
    {
        Ge::$loader = require BASE_PATH . DS . 'vendor/autoload.php';
    }

    /**
     * Определяет сторону запроса: backend, frontend или console.
     * 
     * @return void
     */
    protected function initSide(): void
    {
        /** @var bool Указывает на то, что установщик работает c консолью. */
        define('IS_CONSOLE', PHP_SAPI === 'cli');
        if (!IS_CONSOLE) {
            /** @var bool Указывает на то, что установщик работает с frontend. */
            define('IS_FRONTEND', true);
            /** @var bool Указывает на то, что установщик работает с backend. */
            define('IS_BACKEND', !IS_FRONTEND);
            /** @var string Название стороны (FRONTEND, BACKEND) с которой работает установщик. */
            define('SIDE', IS_FRONTEND ? FRONTEND : BACKEND);
        } else {
            /** @var bool Указывает на то, что установщик работает с frontend. */
            define('IS_FRONTEND', false);
            /** @var bool Указывает на то, что установщик работает с backend. */
            define('IS_BACKEND', false);
            /** @var string Название стороны (FRONTEND, BACKEND) с которой работает установщик. */
            define('SIDE', false);
        }
    }

    /**
     * Возвращает имя файла или каталога, который блокирует работу установщика.
     * 
     * @param bool $includePath Если значение `true`, имя включает путь (по умолчанию `true`).
     * 
     * @return string
     */
    public function getLockName(bool $includePath = true): string
    {
        return ($includePath ? $this->getPath() . DS : '') . $this->lockName;
    }

    /**
     * Проверяет, необходимо ли зупускать установщик.
     * 
     * Если результат `true`, установщик будет запущен.
     * 
     * @return bool
     */
    public function accessibly(): bool
    {
        return file_exists($this->getLockName());
    }

    /**
     * Делает недоступным установщик.
     * 
     * @return bool Возвращает значение `true` если установщик стал недоступен. Иначи, 
     *     значение `false`, если не получилось сделать установщик недоступным.
     */
    public function makeInaccessible(): bool
    {
        $name = $this->getLockName();
        if (file_exists($name)) {
            return is_dir($name) ? @rmdir($name) : @unlink($name);
        }
        return true;
    }

    /**
     * Возвращает шаги установки.
     * 
     * @return InstallerSteps
     */
    public function getSteps(): InstallerSteps
    {
        if (!isset($this->steps) || is_array($this->steps)) {
            $this->steps = new InstallerSteps([
                'steps'     => is_array($this->steps) ? $this->steps : [],
                'installer' => $this
            ]);
        }
        return $this->steps;
    }

    /**
     * Возвращает название шага.
     * 
     * @see InstallerSteps::getStepName()
     * 
     * @param bool $full Если значение `true`, добавляет название выбора (по умолчанию `false`).
     * 
     * @return string
     */
    public function getStepName(bool $full = false): string
    {
        return $this->getSteps()->getStepName($full);
    }

    /**
     * Возвращает маршрутизатор установщика.
     * 
     * @return InstallerRouter
     */
    public function getRouter(): InstallerRouter
    {
        if (!isset($this->router)) {
            $this->router = new InstallerRouter($this->route);
        }
        return $this->router;
    }

    /**
     * Возвращает представление установщика.
     * 
     * @return InstallerView
     */
    public function getView(): InstallerView
    {
        if (!isset($this->view)) {
            $installer = $this;

            $this->view = new InstallerView([
                'path'   => $this->getViewPath(),
                'params' => [
                    'assets' => $this->getAssetsUrl(),
                    't'      => function (string $message, array $args = []) use ($installer) {
                        return $installer->t($message, $args);
                    }
                ]
            ]);
        }
        return $this->view;
    }

    /**
     * Определяет, что указанный шаг установки завершен.
     * 
     * @param string|null $stepName Название шага установки, например: 'foobar', 'choice:foobar'.
     * 
     * @return bool
     */
    public function isCompleted(string $stepName): bool
    {
        return $this->config->completed[$stepName] ?? false;
    }

    /**
     * Поставить отметку о завершении шага установки.
     * 
     * @param string|null $stepName Название шага установки, например: 'foobar', 
     *     'choice:foobar'. Если значение `null`, название текущего шага (по умолчанию `null`).
     * @param bool $complete Если значение `true`, шаг установки завершен (по умолчанию `true`).
     * 
     * @return $this
     */
    public function complete(?string $stepName = null, bool $complete = true): static
    {
        if ($stepName === null) {
            $stepName = $this->getStepName(true);
        }

        $completed = $this->config->completed ?? [];
        $completed[$stepName] = $complete;
        $this->config->completed = $completed;
        return $this;
    }

    /**
     * Сбросить все отметки о завершении шагов установки.
     * 
     * @return $this
     */
    public function uncomlete(): static
    {
        $this->config->completed = [];
        return $this;
    }

    /**
     * Выполняет запуск установщика.
     * 
     * @return void
     */
    public function run(): void
    {
    }
}
