<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @see https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Plugin;

use Ge;
use Ge\Config\Config;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Module\BaseModule;

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
class BasePlugin extends BaseObject
{
    public ?BaseModule $module = null;

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
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

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
    }

    /**
     * Возвращает Абсолютный (полный) путь к объектам модуля (контроллерам, моделям 
     * данных и т.д.).
     * 
     * Имеет вид: "</абсолютный путь> </src>".  
     * Пример: `'/home/host/public_html/module/FooBar/src'`.
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
        return $message;
    }

    /**
     * Создаёт (генерирует) идентификатор элемента для вывода его в моделе представления.
     * 
     * @param string $name Имя выводимого элемента для которого создаётся идентификатор, 
     *     например 'button'.
     * 
     * @return string
     */
    public function viewID(string $name): string
    {
        return $this->module ? $this->module->viewID($name) : 'g-' . uniqid() . '-' .  $name;
    }

    /**
     * Возвращает идентификатор с сигнатурой.
     * 
     * Если `$signature = true`, то возвратит следующие значения: 'plugin:rg.plg.foobar', 
     * иначе 'rg.plg.foobar'.
     * 
     * @param bool $signature Возвращать имя сигнатуры (по умолчанию `false`).
     * 
     * @return string
     */
    public function getId(bool $signature = false): string
    {
        if ($signature)
            return 'plugin:' . $this->id;
        else
            return $this->id;
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
            $this->config = new Config($this->basePath . DS . 'config' . DS . '.plugin.php', false);
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
     * Возвращает модель данных модуля.
     * 
     * @param string $name Короткое имя класса, например: 'FooBar' => 'Model\FooBar'.
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getModel(string $name, array $config = []): ?BaseObject
    {
       if (!isset($config['module'])) {
            $config['module'] = $this->module;
        }
        if (!isset($config['plugin'])) {
            $config['plugin'] = $this;
        }
        return $this->getObject('Model\\' . $name, $config);
    }

    /**
     * Возвращает модель данных модуля.
     * 
     * @param string $name Короткое имя класса, например: 'FooBar' => 'Model\FooBar'.
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать объект.
     */
    public function getWidget(string $name, array $config = []): ?BaseObject
    {
        $config['creator'] = $this;
        return $this->getObject('Widget\\' . $name, $config);
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
        /*if (!isset($config['plugin'])) {
            $config['module'] = $this->module;
        }*/
        return Ge::$services->get($this->namespace . NS . $name, $config);
    }
}
