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
use Ge\Config\Config;
use Ge\Stdlib\Component;
use Ge\View\ViewManager;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Plugin\BasePlugin;
use Ge\I18n\Source\BaseSource;
use Ge\Exception\NotFoundException;
use Ge\Mvc\Extension\BaseExtension;
use Ge\Mvc\Controller\BaseController;
use Ge\ServiceManager\Exception\NotInstantiableException;

/**
 * Модуль является базовым классом для всех классов-наследников модуля.
 * 
 * Модуль реализует архитектуру MVC и может содержать такие ёё элементы, как модели, 
 * представления, контроллеры и т.д.
 * 
 * Доступ к контроллеру модуля можно получить через:
 * - `Ge::$app->controller`
 * - `Ge::$app->module->controller`
 * - `Ge::$app->module->controller()`
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Module
 * @since 2.0
 */
class BaseModule extends Component
{
    /**
     * Уникальный идентификатор модуля для всего приложения.
     * 
     * Устанавливается из конфигурации в конструкторе модуля.
     * 
     * @var string
     */
    public string $id = '';

    /**
     * Локальный путь.
     * 
     * Устанавливается из конфигурации в конструкторе модуля.
     * 
     * Пример: '/rg/rg.foobar'.
     * 
     * @var string
     */
    public string $path;

    /**
     * Абсолютный (полный) путь.
     * 
     * Устанавливает конструктор модуля.
     * 
     * Имеет вид: "</абсолютный путь к модулям> </локальный путь>".
     * Пример: '/home/host/public_html/modules/rg/rg.foobar'.
     * 
     * @var string
     */
    public string $basePath;

    /**
     * Абсолютный (полный) путь к объектам модуля (контроллерам, моделям данных и т.д.).
     * 
     * @see BaseModule::getSourcePath()
     * 
     * @var string
     */
    protected string $sourcePath;

    /**
     * Абсолютный (полный) путь к файлам моделей представлений.
     * 
     * @see BaseModule::getViewPath()
     * 
     * @var string
     */
    protected string $viewPath;

    /**
     * Абсолютный (полный) путь к файлам макетов.
     * 
     * @see BaseModule::getLayoutPath()
     * 
     * @var string
     */
    protected string $layoutPath;

    /**
     * Маршрут модуля.
     * 
     * @see BaseModule::getRoute()
     * 
     * @var string
     */
    public string $route;

    /**
     * Абсолютный (полный) маршрут модуля.
     * 
     * @see BaseModule::getBaseRoute()
     * 
     * @var string
     */
    protected string $baseRoute;

    /**
     * Название пространства имён модуля.
     * 
     * Устанавливается из конфигурации в конструкторе модуля.
     * 
     * @link https://www.php.net/manual/ru/reflectionclass.getnamespacename.php
     * 
     * @var string
     */
    public string $namespace;

    /**
     * Короткое имя класса контроллера по умолчанию.
     * 
     * @var string
     */
    public string $defaultController = 'IndexController';

    /**
     * Текущий контроллер модуля.
     * 
     * @see BaseModule::controller()
     * 
     * @var BaseController|null
     */
    public ?BaseController $controller = null;

    /**
     * Текущее расширение модуля.
     * 
     * @see BaseModule::extension()
     * 
     * @var BaseExtension|null
     */
    public ?BaseExtension $extension = null;

    /**
     * Имя (маршрут) расширения модуля по умолчанию.
     * 
     * Если расширение модуля указано с помощью метода {@see BaseModule::extension()} 
     * со значением '' аргумента (маршрута), то маршрут будет определён свойством, 
     * как маршрут расширения модуля по умолчанию. Полученный маршрут будет 
     * подставлен в карту маршрутов для определения расширения модуля.
     * 
     * @see BaseModule::getExtension()
     * 
     * @var string
     */
    public string $defaultExtension = '';

    /**
     * Имя макета, файла или параметры конфигурации макета.
     * 
     * Пример:
     * - `/layouts/main` или `layouts/main`;
     * - ['viewFile' => /layouts/main].
     * 
     * @var string|array{viewFile:string}
     */
    public string|array $layout = '';

    /**
     * Права доступа к модулю.
     * 
     * @see BaseModule::getPermission()
     * 
     * @var ModulePermission
     */
    protected ModulePermission $permission;

    /**
     * Все контроллеры созданные в модуле.
     * 
     * @see BaseModule::createController()
     * 
     * @var array<string, BaseController>
     */
    protected array $controllers = [];

    /**
     * Все расширения созданные в модуле.
     * 
     * @see BaseModule::createExtension()
     * 
     * @var array<string, BaseExtension>
     */
    protected array $extensions = [];

   /**
     * Менеджер представлений.
     * 
     * @see BaseModule::getViewManager()
     * 
     * @var ViewManager
     */
    protected ViewManager $viewManager;

    /**
     * Конфигуратор модуля.
     * 
     * @see BaseModule::getConfig()
     * 
     * @var Config
     */
    protected Config $config;

    /**
     * Настройки модуля.
     * 
     * @see BaseModule::getSettings()
     * 
     * @var Config
     */
    protected Config $settings;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        // название пространства имён модуля
        if (!isset($this->namespace)) {
            $this->namespace = $this->getReflection()->getNamespaceName();
        }
        // Абсолютный (полный) путь модуля
        if (!isset($this->basePath)) {
            $this->basePath = Ge::$app->modulePath . $this->path;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->initTranslations();
        $this->initCaching();

        parent::init();
    }

    /**
     * Возвращает маршрут модуля.
     * 
     * @return string
     */
    public function getRoute(): string
    {
        if (!isset($this->route)) {
            $this->route = '';
        }
        return $this->route;
    }

    /**
     * Возвращает маршрут модуля.
     * 
     * @return string
     */
    public function getBaseRoute(bool $short = false): ?string
    {
        if (!isset($this->baseRoute)) {
            $this->baseRoute = $this->getRoute();
            if ($this->extension) {
                $this->baseRoute .= '/' . $this->extension->getBaseRoute();
            } else {
                if ($this->controller) {
                    $this->baseRoute .= '/' . $this->controller->getName();
                    $actionName =  $this->controller->getActionName();
                    if ($actionName) {
                        $this->baseRoute .= '/' . $actionName;
                    }
                }
            }
            // TODO: controller->getName();
        }
        return $this->baseRoute;
    }

    /**
     * Возвращает Абсолютный (полный) путь к объектам модуля (контроллерам, моделям 
     * данных и т.д.).
     * 
     * Имеет вид: "</абсолютный путь> </src>".  
     * Пример: `'/home/host/public_html/module/Frontend/Application/src'`.
     * 
     * @return string
     */
    public function getSourcePath(): string
    {
        if (!isset($this->sourcePath)) {
            $this->sourcePath = $this->basePath . DS . 'src';
        }
        return $this->sourcePath;
    }

    /**
     * Возвращает абсолютный путь модуля к файлам шаблонов представления.
     * 
     * @see BaseModule::$viewPath
     * 
     * @return string
     */
    public function getViewPath(): string
    {
        if (!isset($this->viewPath)) {
            $this->viewPath = $this->basePath . DS . 'views';
        }
        return $this->viewPath;
    }

    /**
     * Возвращает абсолютный путь модуля к файлам макетов страниц.
     * 
     * @see BaseModule::$layoutPath
     * 
     * @return string
     */
    public function getLayoutPath(): string
    {
        if (!isset($this->layoutPath)) {
            $this->layoutPath = $this->getViewPath() . DS . 'layouts';
        }
        return $this->layoutPath;
    }

    /**
     * Возвращает локальный путь, используемый темой для определения файла шаблона.
     * 
     * Для модулей FRONTEND может не использоваться, т.к. все файлы шаблонов модулей 
     * должны находитmся в каталоге 'views' текущей темы.
     * 
     * Пример: 
     * - для BACKEND '<theme-path>/vews/<module-path>/';
     * - для FRONTEND '<theme-path>/vews/';
     * 
     * @return string
     */
    public function getThemePath(): string
    {
        return $this->path;
    }

    /**
     * Возвращает параметры версии модуля.
     * 
     * @return array|null Возвращает значение `null`, если невозможно получить параметры 
     *     версии модуля.
     */
    public function getVersion(): ?array
    {
        return [];
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
     * @return mixed
     */
    public function getStorage()
    {
    }

    /**
     * Добавляет значение переменной во временное хранилище (контейнер) данных модуля.
     * 
     * @see BaseModule::$storage
     * 
     * @param string $key Имя переменной.
     * @param mixed $value Значение переменной.
     * 
     * @return void
     */
    public function storageSet(string $key, mixed $value): void
    {
    }

    /**
     * Возвращает значение переменной из временного хранилища (контейнера) данных модуля.
     * 
     * @see BaseModule::$storage
     * 
     * @param string $key Имя переменной.
     * @param mixed $default Возвратит значение по умолчанию, если значение `null`.
     * 
     * @return mixed Если переменная не определена в хранилище, то возвратит `null`.
     */
    public function storageGet(string $key, mixed $default = null): mixed
    {
        return null;
    }

    /**
     * Удаляет значение переменной из временного хранилища (контейнера) данных модуля.
     * 
     * @see BaseModule::$storage
     * 
     * @param string $key Имя переменной.
     * 
     * @return void
     */
    public function storageRemove(string $key): void
    {
    }

    /**
     * Возвращает репозиторий перевода сообщений текущего модуля.
     * 
     * Репозиторий является источником сообщений, который хранит переводы сообщений в 
     * хранилище (файл, база данных и т.д.).
     * 
     * @return false|BaseSource Возвращает значение `false`, если категория сообщений 
     *     не существует.
     */
    public function getMessageSource(): false|BaseSource
    {
        return Ge::$app->translator->getCategory($this->id);
    }

    /**
     * Добавляет модулю шаблон перевода.
     * 
     * @see \Ge\I18n\Source\BaseSource::addPattern()
     * @see \Ge\I18n\Source\BaseSource::loadPattern()
     * 
     * @param string $name Имя названия шаблона перевода.
     *
     * @return void
     * 
     * @throws \Ge\I18n\Exception\PatternNotExistsException Невозможно подключить 
     *    шаблон перевода.
     */
    public function addTranslatePattern(string $name): void
    {
        /** @var \Ge\I18n\Source\BaseSource|bool $category */
        $category = Ge::$app->translator->getCategory($this->id);
        if ($category) {
            $category->addPattern($name);
        }
    }

    /**
     * Выполняет подготовку к переводу сообщений модуля.
     * 
     * В качестве перевода применяется транслятор (локализатор сообщений)
     * {@see \Ge\I18n\Translator}.
     * 
     * @return void
     */
    protected function initTranslations(): void
    {
    }

   /**
     * Выполняет подготовку к кэшированию данных модуля.
     * 
     * @return void
     */
    protected function initCaching(): void
    {
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function t(string|array $message, array $params = [], string $locale = ''): string|array
    {
        return Ge::$app->translator->translate($this->id, $message, $params, $locale);
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений c симолов "#" (translate hash).
     * 
     * Например: '#word' => 'слово', 'word' => 'word'.
     * 
     * Если сообщение имеет тип `string` и отсутствует первый симолов "#", то перевода не 
     * будет. 
     * 
     * Для массива значений можеть иметь вид, например:
     * ```php
     * [
     *     'id1'   => '#word 1',
     *     'id2'   => '#word 2',
     *     'id3'   => 'word 3',
     *     'items' => [
     *         'id1' => '#example 1',
     *         'id2' => 'example 2'
     *     ]
     * ]
     * ```
     * результат перевода:
     * ```php
     * [
     *     'id1'   => 'слово 1',
     *     'id2'   => 'слово 2',
     *     'id3'   => 'word 3',
     *     'items' => [
     *         'id1' => 'пример 1',
     *         'id2' => 'example 2'
     *     ]
     * ]
     * ```
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function tH(string|array $message, array $params = [], string $locale = ''): string|array
    {
        if (is_string($message)) {
            if (strncmp($message, '#', 1) === 0) {
                return Ge::$app->translator->translate(
                    $this->id, ltrim($message, '#'), $params, $locale
                );
            } else
                return $message;
        } else
            return Ge::$app->translator->translate($this->id, $message, $params, $locale);
    }

    /**
     * Создаёт (генерирует) идентификатор элемента для вывода его в моделе представления.
     * 
     * @param string $name Имя выводимого элемента для которого создаётся идентификатор, 
     *     например 'button'.
     * 
     * @return string
     */
    public function viewId(string $name): string
    {
        return 'g-' . uniqid() . '-' .  $name;
    }

    /**
     * Возвращает идентификатор с сигнатурой.
     * 
     * Возвращаемое значение сигнатуры зависит от класса и может иметь значение:
     * 'module', 'extension', 'widget'.
     * Если `$signature = true`, то возвратит следующие значения: 'module:rg.foobar', 
     * 'widget:rg.foobar', 'extension:rg.foobar'.
     * 
     * @param bool $signature Возвращать имя сигнатуры (по умолчанию `false`).
     * 
     * @return string
     */
    public function getId(bool $signature = false): string
    {
        if ($signature)
            return 'module:' . $this->id;
        else
            return $this->id;
    }

    /**
     * @param null|string $name
     * 
     * @return BaseModule|null
     */
    public function extension(?string $name = null): ?BaseModule
    {
        if ($name === null) {
            return $this->extension;
        }
        return $this->extension = $this->getExtension($name);
    }

    /**
     * Возвращает карту расширений модуля.
     * 
     * Сопоставление имени (маршрута) расширения с конфигурациями расширений. Каждая пара 
     * "имя - значение" определяет конфигурацию отдельного расширения. Конфигурация 
     * расширения может быть либо строкой, либо массивом. В первом случае строка должна 
     * быть идентификатором установленного расширения . В последнем случае массив должен 
     * содержать элемент, указывающий на: идентификатор расширения, название пространства 
     * имён расширения, полное имя класса расширения. Остальные пары "имя - значение" в 
     * массиве используются для инициализации соответствующих свойств расширения. Например:
     * 
     * ```php
     * [
     *     'toolbar' => 'debug.toolbar',
     *     // или
     *     'toolbar' => [
     *         'id' => 'debug.toolbar',
     *     ],
     *     // или
     *     'toolbar' => [
     *         'namespace' => '\Extension\Debug\Toolbar',
     *     ],
     *     // или
     *     'toolbar' => [
     *         'class' => '\Extension\Debug\Toolbar\Extension',
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @see BaseModule::createExtension()
     * 
     * @return array
     */
    public function extensionMap(): array
    {
        return [];
    }

    /**
     * Создаёт расширение модуля.
     * 
     * @param string $name Имя расширения модуля. Имя совпадает с частью маршрута 
     *     модуля, например, маршрут модуля 'module/extension/controller/action', то имя 
     *     будет 'extension'.
     * 
     * @return BaseExtension
     */
    public function createExtension(string $name): BaseExtension
    {
        /** @var null|BaseExtension $extension */
        $extension = null;
        /** @var \Ge\ExtensionManager\ExtensionManager $extensions */
        $extensions = Ge::$app->extensions;
        try {
            $map = $this->extensionMap();
            if (isset($map[$name])) {
                $params = $map[$name];
                // если идентификатор расширения
                if (is_string($params)) {
                    $extension = $extensions->create($params, ['parent' => $this, 'route' => $name]);
                } else
                // если конфигурация расширения
                if (is_array($params)) {
                    $params['parent'] = $this;
                    $params['route']  = $name;
                    $extension = $extensions->create($params['id'], ['parent' => $this, 'route' => $name]);
                }
            } else
                throw new Exception\ExtensionCreateException(
                    'It is impossible to create a module extension "' . $name . '", because extension map is empty.'
                );
        // невозможно создать контроллер, т.к. файл контроллера отсутствует
        } catch  (NotInstantiableException $e) {
        // ошибка внутри контроллера
        } catch  (\Exception $e) {
            // в процессе создания контроллера могут возникнуть ошибки, режим `GE_MODE_DEV`
            // позволяет их увидеть
            if (GE_MODE_DEV) {
                throw new Exception\ExtensionCreateException($e->getMessage());
            }
        }
        return $extension;
    }

    /**
     * Возвращает контроллер модуля по указанному имени класса контроллера.
     * 
     * Если контроллер ещё не создан, создаёт его.
     * 
     * @see BaseModule::createController()
     * 
     * @param string $name Короткое имя класса контроллера. Если имя имеет значения: '', 'index', 'Index', 
     *     то имя контроллера будет именем контроллера по умолчанию {@see BaseModule::$defaultController}.
     * 
     * @return BaseExtension|null 
     * 
     * @throws Exception\ControllerNotFoundException Если контроллер с указанным именем не существует.
     */
    public function getExtension(string $name): BaseExtension
    {
        if ($name === '') {
            $name = $this->defaultExtension;
        }

        // если расширение ранее создано
        if (isset($this->extensions[$name])) {
            return $this->extensions[$name];
        }
        return $this->createExtension($name);
    }

    /**
     * @see BaseModule::createPlugin()
     *
     * @var array<string, BasePlugin>
     */
    protected array $plugins = [];

    /**
     * Создаёт плагин модуля.
     * 
     * @param string $id Уникальный идентификатор плагина, например: 'rg.plg.foobar'.
     * @param array $params Параметры плагина (по умолчанию `[]`).

     * 
     * @return BasePlugin|null
     */
    public function createPlugin(string $id, array $params = []): ?BasePlugin
    {
        if (isset($this->plugins[$id])) {
            return $this->plugins[$id];
        }

        $plugin = null;
        try {
            $params['module'] = $this;

            /** @var null|BasePlugin $plugin */
            $plugin = Ge::$app->plugins->get($id, $params, true);
            $this->plugins[$id] = $plugin;
        // ошибка внутри плагина
        } catch  (\Exception $e) {
            // в процессе создания плагина могут возникнуть ошибки, режим `GE_MODE_DEV`
            // позволяет их увидеть
            if (GE_MODE_DEV) {
                throw new Exception\PluginCreateException($e->getMessage());
            }
        }
        return $plugin;
    }

    /**
     * Возвращает плагин модуля по указанному идентификатору.
     * 
     * Если плагин ещё не создан, создаёт его.
     * 
     * @see BaseModule::createPlugin()
     * 
     * @param string $id Уникальный идентификатор плагина, например: 'rg.plg.foobar'.
     * @param array $params Параметры плагина (по умолчанию `[]`).
     * 
     * @return BasePlugin|null 
     * 
     * @throws Exception\ControllerNotFoundException Если контроллер с указанным именем не существует.
     */
    public function getPlugin(string $id, array $params = []): ?BasePlugin
    {
        // если расширение ранее создано
        if (isset($this->plugins[$id])) {
            return $this->plugins[$id];
        }
        return $this->createPlugin($id);
    }

    /**
     * Возвращает информацию о плагинах модуля.
     * 
     * @param bool $withNames Если значение `true`, то добавляется имя и описание 
     *     плагина в текущей локализации (по умолчанию `true`).
     * @param string $key Имя ключа возвращаемой информации:
     *     - 'rowId', идентификатор плагина в базе данных;
     *     - 'id', идентификатор плагина, например 'rg.plg.foobar'.
     *     По умолчанию 'rowId'.
     * @param bool|array{
     *     version: bool, 
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает плагин.
     *     Где ключи:
     *     - 'version', файл конфигурации версии плагина;
     *     - 'install', файл конфигурации установки плагина;
     *     - 'icon', значки плагина.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `[]`.
     * 
     * @return array Возвращает массив с информацией о установленных плагинах.
     */
    public function getPluginsInfo(
        bool $withNames = true, 
        string $key = 'rowId', 
        bool|array $include = []
    ): array
    {
        $plugins = Ge::$services->getAs('plugins');
        return $plugins ? $plugins->getRegistry()->getListInfo($withNames, $this->id, $key, $include) : [];
    }

    /**
     * Возвращает текущий контроллер модуля или создаёт контроллер по указанному 
     * идентификатору или короткому имени его класса.
     * 
     * @see BaseModule::getController()
     * 
     * @param null|string $name Идентификатор контроллера или короткое имя его класса. 
     *     Если `null`, возвращает текущий контроллер {@see BaseModule::$controller}.
     * 
     * @return BaseController|null Если `null`, контроллер не создан.
     */
    public function controller(?string $name = null): ?BaseController
    {
        if ($name === null) {
            return $this->controller;
        }
        return $this->controller = $this->getController($name);
    }

    /**
     * Возвращает карту контроллеров модуля.
     * 
     * Сопоставление имени контроллера с конфигурациями контроллеров. Каждая пара 
     * "имя - значение" определяет конфигурацию отдельного контроллера. Конфигурация 
     * контроллера может быть либо строкой, либо массивом. В первом случае строка должна 
     * быть коротким именем класса контроллера (модуля, который его вызвал). В последнем 
     * случае массив должен содержать элемент класса, указывающий полное имя класса 
     * контроллера, а остальные пары "имя - значение" в массиве используются для инициализации 
     * соответствующих свойств контроллера. Например:
     * 
     * ```php
     * [
     *     'user' => 'UserController',
     *     'post' => [
     *         'class' => '\Ge\Frontend\Application\Controller\PostController',
     *         'title' => 'Good news',
     *     ],
     *     '*' => 'anyController', // будет принят, если 'user' и 'post' будут пропущены
     *     // ...
     * ]
     * ```
     * Если указан ключ '*', то он определит контроллер, который отсутствует в массиве.
     * 
     * @see BaseModule::createController()
     * 
     * @return array
     */
    public function controllerMap(): array
    {
        return [];
    }

    /**
     * Создаёт контроллер модуля.
     * 
     * Используется сопоставление {@see BaseModuel::controllerMap()} для преобразования 
     * идентификатора контроллера в короткое имя его класса.
     * 
     * @param string $name Идентификатор контроллера или короткое имя его класса.
     *     Пример: 
     *     - 'foobar', идентификатор контроллера;
     *     - 'FooBar', короткое имя класса.
     * 
     * @return BaseController|null Если `null`, контроллер не создан.
     * 
     * @throws Exception\ControllerCreateException Ошибка создания контроллера, только в режиме `GE_MODE_DEV`.
     */
    public function createController(string $name): ?BaseController
    {
        $controller = $params = null;
        try {
            if ($map = $this->controllerMap()) {
                if (isset($map[$name]))
                    $params = $map[$name];
                else
                if (isset($map['*']))
                    $params = $map['*'];
            }

            if ($params) {
                // если короткое имя класса
                if (is_string($params))
                    $params = ['class' => $this->namespace . NS . 'Controller' . NS . $params];
                else
                // если не конфигурация
                if (!is_array($params))
                    return null;
            } else {
                $params = ['class' => $this->namespace . NS . 'Controller' . NS . ucfirst($name)];
            }
            $params['name']      = $name;
            $params['construct'] = [$this, ''];
            $controller = Ge::createObject($params);
            /**
             * варианты создания контроллера:
             * $controller = Ge::createObject($this->namespace . NS . 'Controller' . NS . $name, $this, '', ['name' => $name]);
             * или
             * $controller = Ge::$services->get($this->namespace . NS . 'Controller' . NS . $name, $this, '', ['name' => $name]);
             */
        // невозможно создать контроллер, т.к. файл контроллера отсутствует
        } catch  (NotInstantiableException $e) {
        // ошибка внутри контроллера
        } catch  (\Exception $e) {
            // в процессе создания контроллера могут возникнуть ошибки, режим `GE_MODE_DEV`
            // позволяет их увидеть
            if (GE_MODE_DEV) {
                throw new Exception\ControllerCreateException($e->getMessage());
            }
        }
        return $controller;
    }

    /**
     * Выполняет поиск контроллера модуля из маршрута запроса.
     * 
     * Метод используется в том случае, если модуль ранее не был задействован 
     * маршрутизатом при определении маршрута.
     * 
     * Для определения контроллера и его действия, используются плагины сравнения 
     * маршрута, например:
     * ```php
     * [
     *     'type' => 'segments',
     *     'options' => [
     *         'module'   => 'foo.bar',
     *         'route'    => '[:controller[/:action]]',
     *         'defaults' => [
     *             'controller' => 'foobar',
     *             'action'     => 'index'
     *         ],
     *         'constraints' => [
     *             'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
     *             'action'     => '[a-zA-Z][a-zA-Z0-9_-]*'
     *          ]
     *      ]
     * ]
     * ```
     * Если параметр `module` не указан, будет применён идентификатор текущего модуля.
     * 
     * @see BaseModule::controller()
     * 
     * @param array $routeOptions Параметры сравнения маршрута. Если параметры не указаны,
     *     то будет использоваться плагин "segments" {@see \Ge\Router\Matcher\Http\Segments} 
     *     сравнения маршрута (по умолчанию `[]`).
     * 
     * @return BaseController|null Возвращает значение `null`, если контроллер модуля 
     *     не найден в маршруте запроса.
     */
    public function findController(array $routeOptions = []): ?BaseController
    {
        if (empty($routeOptions)) {
            $routeOptions = [
                'type'    => 'segments',
                'options' => [
                    'module'      => $this->id,
                    'route'       => '[:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ]
                ]
            ];
        } else {
            if (isset($routeOptions['options']['module'])) {
                $routeOptions['options']['module'] = $this->id;
            }
        }

        /** @var \Ge\Router\Matcher\RouteMatch $result Результат сравнения */
        $result = Ge::$app->router->matchRoutes([$routeOptions]);
        if ($result) {
            try {
                $controller = $this->controller($result->controller);
                $controller->action($result->action);
            } catch (NotFoundException $exception) {
            }
        }
        return $this->controller();
    }

    /**
     * Возвращает настройки модуля.
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
     * Возвращает параметры конфигурации модуля.
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        if (!isset($this->config)) {
            $this->config = new Config($this->basePath . DS . 'config' . DS . '.module.php', false);
        }
        return $this->config;
     }

    /**
     * Возвращает значение параметра конфигурации модуля.
     * 
     * @param null|string $name Имя параметра. Если значение `null`, то результатом будет 
     *     {@see BaseModule::$config()} (по умолчанию `null`).
     * @param mixed $default Значение по умолчанию если параметр не существует 
     *     (по умолчанию `[]`).
     * 
     * @return mixed
     */
    public function getConfigParam(?string $name = null, mixed $default = []): mixed
    {
        if (!isset($this->config)) {
            $this->config = $this->getConfig();
        }
        return $this->config->getValue($name, $default);
    }

    /**
     * Возвращает менеджер представлений.
     * 
     * @return ViewManager
     */
    public function getViewManager(): ViewManager
    {
        if (!isset($this->viewManager)) {
            $this->viewManager = new ViewManager($this->getConfigParam('viewManager'), $this);
        }
        return $this->viewManager;
    }

    /**
     * Возвращает контроллер модуля.
     * 
     * Если контроллер ещё не создан, создаёт по указанному идентификатору или короткому 
     * имени его класса.
     * 
     * @see BaseModule::createController()
     * 
     * @param string $name Идентификатор контроллера или короткое имя его класса.
     *     Если имеет значения: '', 'index', 'Index', то короткое имя его класса будет 
     *     именем по умолчанию {@see BaseModule::$defaultController}.
     * 
     * @return BaseController
     * 
     * @throws Exception\ControllerNotFoundException Если контроллер не существует.
     */
    public function getController(string $name): BaseController
    {
        if ($name === '' || strtolower($name) === 'index') {
            $name = $this->defaultController;
        }

        // если контроллер ранее создан
        if (isset($this->controllers[$name])) {
            return $this->controllers[$name];
        }

        $controller = $this->createController($name);
        if ($controller === null) {
            throw new Exception\ControllerNotFoundException(
                Ge::t('app', 'Controller with name "{0}" not exists', [$name]), $name
            );
        }
        return $controller;
    }

    /**
     * Возвращает имя текущего контроллера.
     * 
     * @return string|null Возвращает значение `null`, если контроллер не создан.
     */
    public function getControllerName(): ?string
    {
        return $this->controller ? $this->controller->getName() : null;
    }

    /**
     * Проверяет, является ли короткое имя класса текущего контроллера, именем контроллера 
     * по умолчанию.
     * 
     * @see BaseModule::$controller
     * 
     * @return bool Возвращает значение `true`, если имя контроллера, является именем 
     *     котроллера по умолчанию. Если контроллер не создан, возвратит `false`.
     */
    public function isDefaultController(): bool
    {
        if ($this->controller) {
            return $this->controller->getName() === $this->defaultController;
        }
        return false;
    }

    /**
     * Возвращает права доступа к модулю.
     * 
     * @see BaseModule::$permission
     * 
     * @return ModulePermission
     */
    public function getPermission(): ModulePermission
    {
        if (!isset($this->permission)) {
            $this->permission = new ModulePermission($this);
        }
        return $this->permission;
    }

    /**
     * Возвращает идентификатор текущего действия контроллера.
     * 
     * @return string|null Возвращает значение `null`, если действие контроллера 
     *     невозможно определить.
     */
    public function getActionName(): ?string
    {
        return $this->controller ? $this->controller->getActionName() : null;
    }

    /**
     * Возвращает модель данных модуля.
     * 
     * @param string $name Короткое имя класса, например: 'FooBar' => 'Model\FooBar'.
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getModel(string $name, array $config = []): ?BaseObject
    {
        return $this->getObject('Model\\' . $name, $config);
    }

    /**
     * Возвращает помощника модуля.
     * 
     * @see BaseModule::getObject()
     * 
     * @param string $name Короткое имя класса, например: 'FooBar' => 'Helper\FooBar'.
     * @param array $config Начальные значения свойств объекта в виде пар "ключ - значение"
     *     (по умолчанию: `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getHelper($name = 'Helper', array $config = []): ?BaseObject
    {
        return $this->getObject('Helper\\' . $name, $config);
    }

    /**
     * Возвращает помощника данных модуля.
     * 
     * Помощник обрабатывает только данные.
     * 
     * @see BaseModule::getObject()
     * 
     * @param array $config Начальные значения свойств объекта в виде пар "ключ - значение"
     *     (по умолчанию: `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getDataHelper(array $config = []): ?BaseObject
    {
        return $this->getObject('Helper\DataHelper', $config);
    }

    /**
     * Возвращает объект модуля.
     * 
     * Классы объектов должны находится в пространстве  имён модуля.
     * 
     * @param string $name Короткое имя класса, например: 'FooBar' => 'Ge\Backend\Sample\FooBar'.
     * @param array $config Начальные значения свойств объекта в виде пар "ключ - значение"
     *     (по умолчанию: `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getObject(string $name, array $config = []): ?BaseObject
    {
        if (!isset($config['module'])) {
            $config['module'] = $this;
        }
        return Ge::$services->get($this->namespace . NS . $name, $config);
    }

    /**
     * Возвращает параметр конфигурации установленного модуля.
     * 
     * @param string $name Имя параметра.
     * @param mixed $default Значение по умолчанию если параметр не существует 
     *     (по умолчанию `null`).

     * @return mixed
     */
    public function getInstalledParam(string $name, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Этот метод вызывается прямо перед запуском (выполнения действия контроллером) 
     * этого модуля.
     *
     * Метод вызовет событие {@see BaseModule::EVENT_BEFORE_RUN}. Возвращаемое значение 
     * метода определит, следует ли продолжать выполнение действия.
     *
     * В случае, если действие не должно выполняться, запрос должен обрабатываться внутри 
     * метода {@see BaseModule::beforeRun()}, либо путём предоставления необходимых выходных 
     * данных, либо путем перенаправления запроса. В противном случае ответ будет пустым.
     * 
     * Если вы переопределите этот метод, тогда код должен выглядеть следующим образом:
     * ```php
     * public function beforeRun(BaseController $controller, string $action)
     * {
     *     if (!parent::beforeRun($controller, $action)) {
     *         return false;
     *     }
     *
     *     // здесь ваш код
     *
     *     return true;
     * }
     * ```
     *
     * @param BaseController $controller Текущий контроллер к которому адресован запрос.
     * @param string $action Действие, которое нужно выполнить.
     * 
     * @return bool Если `true`, следует продолжать выполнение действия. Иначе, нет.
     */
    public function beforeRun(BaseController $controller, string $action): bool
    {
        /** @var bool $isValid если действие над контроллером верно */
        $isValid = true;
        $this->trigger(
            self::EVENT_BEFORE_RUN,
            [
                'controller' => $controller,
                'action'     => $action,
                'isValid'    => &$isValid
            ]
        );
        return $isValid;
    }

    /**
     * Этот метод вызывается сразу после выполнения контроллером действия в этом модуле.
     *
     * Метод вызовет событие {@see BaseModule::EVENT_AFTER_RUN}. Возвращаемое значение 
     * метода будет использоваться, как возвращаемое значение действия контроллера.
     *
     * Если вы переопределите этот метод, тогда код должен выглядеть следующим образом:
     *
     * ```php
     * public function afterRun(BaseController $controller, string $action, $result)
     * {
     *     $result = parent::afterRun($controller, $action, $result);
     * 
     *     // здесь ваш код
     * 
     *     return $result;
     * }
     * ```
     *
     * @param BaseController $controller Текущий контроллер к которому адресован запрос.
     * @param string $action Действие, которое выполнено.
     * @param mixed $result Результат выполненного действия.
     * 
     * @return mixed Результат выполненного действия.
     */
    public function afterRun(BaseController $controller, string $action, mixed $result): mixed
    {
        $this->trigger(
            self::EVENT_AFTER_RUN,
            [
                'controller' => $controller,
                'action'     => $action,
                'result'     => $result
            ]
        );
        return $result;
    }

    /**
     * Проверка доступа к модулю.
     * 
     * Этот метод выполняется перед вызовом события {@see BaseModule::EVENT_BEFORE_RUN}.
     * Для проверки доступа к модулю, вы можете переопределить этот метод.
     * 
     * @return bool Если `true`, модуль доступен.
     */
    public function onAccess(): bool
    {
        return true;
    }

    /**
     * Запуск модуля.
     * 
     * @return void
     */
    public function run(): void
    {
        // если создано расширение модуля
        if ($this->extension) {
            $this->extension->run();
        } else {
            // устанавливает модуль для приложения, который был задействован
            Ge::$app->module = $this;
            // проверяет доступ к модулю
            if ($this->onAccess()) {
                // определяет текущий контроллер для модуля
                $controller = $this->controller ?: $this->controller('')->action('');
                if ($this->beforeRun($controller, $controller->getActionName())) {
                    // выполняет действие контроллера
                    $result = $controller->run();
                    $this->afterRun($controller, $controller->getActionName(), $result);
                }
            }
        }
    }
}
