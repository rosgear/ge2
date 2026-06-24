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
use Ge\Helper\Url;
use Ge\Config\Config;
use Ge\Mvc\Module\BaseModule;

/**
 * Трейт расширяет класс виджета.
 * 
 * Так как виджет предназначен для формирования элементов интерфейса в представлении
 * и в большинстве случаев используется модулями их расширениями, и не является
 * самостоятельный компонент. Трейт наделяет виджет свойствами, позволяющие 
 * получать доступ к его ресурсам.
 * 
 * Свойства трейта (виджета имеющий этот трейт) устанавливает Менеджер виджетов 
 * {@see \Ge\WidgetManager\WidgetManager} при создании виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
trait WidgetResourceTrait
{
    /**
     * @var string Символ начала JavaSript в строке.
     */
    const SYMBOL_BEGIN_SCRIPT = ':';

    /**
     * Заголовок виджета.
     * 
     * Применяется для вывода описания виджета на странице в режиме разметки.
     * Устанавливается параметром "title" при указании виджета в шаблоне.
     * 
     * @var string|null
     */
    public ?string $title = null;

    /**
     * Параметры виджета из реестра виджетов.
     * 
     * В реестр заносятся только те виджеты, которые были установлены
     * с помощью Менеджера виджетов.
     * 
     * Устанавливается Менеджером виджетов при создании виджета.
     * Если виджет создаётся без помощи Менеджера, то значение `null`.
     * 
     * @see \Ge\WidgetManager\WidgetRegistry
     * 
     * @var array
     */
    public array $registry = [];

    /**
     * Название пространства имён виджета.
     * 
     * Устанавливается параметром конфигурации в конструкторе виджета.
     * Устанавливает Менеджер виджетов {@see \Ge\WidgetManager\WidgetManager::create()}.
     * 
     * @link https://www.php.net/manual/ru/reflectionclass.getnamespacename.php
     * 
     * @var string
     */
    public string $namespace = '';

    /**
     * Локальный путь виджета.
     * 
     * Устанавливается параметром конфигурации в конструкторе виджета.
     * Устанавливает Менеджер виджетов {@see \Ge\WidgetManager\WidgetManager::create()}.
     * 
     * Пример: '/rg/rg.wd.foobar'.
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Объединить настройки виджета полученные из параметра "settings" конструктора с 
     * параметрами настроек виджета в файле "settings.php".
     * 
     * Применяется в том случае, если настройки виджета вынесены в файл.
     * 
     * @see isMergeSettings()
     * 
     * @var bool
     * 
     * public bool $mergeSettings = false;
     */

    /**
     * Настройки виджета полученные из параметра "settings" конструктора.
     * 
     * Эти параметры применяются для объеденения с параметрами настроек в файле 
     * "settings.php".
     * 
     * @see configure()
     * @see getSettings()
     * 
     * @var array
     */
    protected array $configSettings = [];

    /**
     * Настройки виджета по умолчанию, которые не будут добавляться в JS скрипт виджета.
     * 
     * Названия (ключи) параметров таких настроек должны соответствовать параметрам 
     * в файле настроек виджета "settings.php".
     * 
     * @see getAllSettingWithoutDefaults()
     * 
     * @var array
     * 
     * protected array $defaultSettings = [];
     */

    /**
     * Абсолютный (полный) путь виджета.
     * 
     * @see getBasePath()
     * 
     * @var string
     */
    protected string $basePath;

    /**
     * Абсолютный (базовый) URL-адрес виджета.
     * 
     * @see getBaseUrl()
     * 
     * @var string
     */
    protected string $baseUrl;

    /**
     * Абсолютный (базовый) URL-адрес ресурса виджета.
     * 
     * @see getAssetsUrl()
     * 
     * @var string
     */
    protected string $assetsUrl;

    /**
     * URL-путь виджета.
     * 
     * @see getUrlPath()
     * 
     * @var string
     */
    protected string $urlPath;

    /**
     * Абсолютный (базовый) путь к ресурсам виджета.
     * 
     * @see getAssetsPath()
     * 
     * @var string
     */
    protected string $assetsPath;

    /**
     * URL-путь к подключению скриптов виджета.
     * 
     * @see getRequireUrl()
     * 
     * @var string
     */
    protected string $requireUrl;

    protected array $attributes = [];

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        if (isset($config['settings'])) {
            $this->configSettings = $config['settings'];
            unset($config['settings']);
        }
        if (isset($config['attributes'])) {
            $this->initAttributes($config['attributes']);
            unset($config['attributes']);
        }

        parent::configure($config);
    }

    /**
     * Выполняет инициализацию атрибутов тега шорткода виджета.
     * 
     * Все атрибуты передаваемые аргументом `$attr` определяют свойства класса виджета.
     * 
     * Например: '[foobar id="1" width="100" height="100"]', где атрибуты будут
     * `['id' => 1, width => '100', height => '100']`.
     * 
     * @param array<string, mixed> $attr Атрибуты тега шорткода виджета.
     * 
     * @return void
     */
    protected function initAttributes(array $attr): void
    {
    }

    /**
     * Проверяет, указано ли объединение настроек виджета, полученных из параметра 
     * "settings" конструктора с параметрами настроек в файле "settings.php".
     * 
     * Определяется свойством `$mergeSettings` в классе, если свойство отсутствует, 
     * то результатом будет значение `true`.
     * 
     * @return bool
     */
    public function isMergeSettings(): bool
    {
        return isset($this->mergeSettings) ? $this->mergeSettings : true;
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес ресурса виджета.
     * 
     * Имеет вид: "</абсолютный (базовый) URL-адрес> </assets>".  
     * Пример: 'http://domain/modules/rg/rg.wd.foobar/assets'.
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
     * Возвращает абсолютный (базовый) URL-адрес виджета.
     * 
     * Имеет вид: "<адрес хоста> </абсолютный URL-адрес модулей> </локальный путь>".  
     * Пример: 'http://domain/modules/rg/rg.wd.foobar'.
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
     * Возвращает URL-путь для подключения скриптов виджета.
     * 
     * Имеет вид: "</URL-путь корня хоста> </локальный URL-путь модулей> </локальный путь> </assets>".
     * Пример: '/modules/rg/rg.wd.foobar/assets'.
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
     * Возвращает URL-путь из локального пути виджета.
     *
     * Пример: '\rg\rg.wd.foobar' => '/rg/rg.wd.foobar'.
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
     * Возвращает абсолютный (базовый) путь к ресурсам виджета.
     * 
     * Имеет вид: "</абсолютный путь> </assets>".
     * Пример: '/home/host/public_html/modules/rg/rg.wd.foobar'.
     * 
     * @return string
     */
    public function getAssetsPath(): string
    {
        if (!isset($this->assetsPath)) {
            $this->assetsPath = $this->getBasePath() . DS . 'assets';
        }
        return $this->assetsPath;
    }

    /**
     * Возвращает абсолютный (полный) путь виджета.
     * 
     * Имеет вид: "</абсолютный путь к модулям> </локальный путь>".
     * Пример: '/home/host/public_html/modules/rg/rg.wd.foobar'.
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        if (!isset($this->basePath)) {
            $this->basePath = Ge::$app->modulePath . $this->path;
        }
        return $this->basePath;
    }

    /**
     * Возвращает полное имя файла шаблона (с путём).
     * 
     * @param string $renderFile Имя файла шаблона представления виджета.
     *     Пример: '/fo/bar.phtml', '/foobar.phtml'.
     * 
     * @return false|string Возвращает значение `false`, если невозможно получить имя 
     *     файла представления.
     */
    public function getWidgetRenderFile(string $renderFile): string
    {
        return $this->getBasePath() . '/views' . $renderFile;
    }

    /**
     * Возвращает уникальный идентификатор виджета.
     * 
     * Применяется для перегрузки метода {@see Widget::getId()}.
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Настройки виджета.
     * 
     * @see getSettings()
     * 
     * @var Config
     */
    protected Config $settings;

    /**
     * Возвращает настройки виджета.
     * 
     * @return Config
     */
    public function getSettings(): Config
    {
        if (!isset($this->settings)) {
            $this->settings = new Config($this->getBasePath() . '/config/.settings.php', true);
            if ($this->isMergeSettings() && $this->configSettings) {
                $this->settings->merge($this->configSettings);
            }
        }
        return $this->settings;
    }

    /**
     * Возвращает все параметры настроек виджета без настроек указанных по умолчанию. 
     * 
     * @return array<string, mixed>
     */
    public function getAllSettingWithoutDefaults() : array
    {
        $params = [];

        /** @var array $options Параметры настроек виджета  */
        $settings = $this->getSettings()->getAll();

        if (isset($this->defaultSettings) && sizeof($this->defaultSettings) > 0) {
            foreach ($settings as $name => $value) {
                if (isset($this->defaultSettings[$name])) {
                    if ($this->defaultSettings[$name] == $value) continue;
                }
                $params[$name] = $value;
            }
        } else
            $params = $settings;
        return $params;
    }

    /**
     * Выполняет подготовку к переводу сообщений виджета.
     * 
     * В качестве перевода применяется транслятор (локализатор сообщений)
     * {@see \Ge\I18n\Translator}.
     * 
     * @return void
     */
    protected function initTranslations()
    {
        Ge::$app->translator->addCategory(
            $this->id, 
            [
                'locale'   => 'auto',
                'patterns' => [
                    'text' => [
                        'basePath' => $this->getBasePath() . DS . 'lang',
                        'pattern'  => 'text-%s.php'
                    ]
                ],
                'autoload' => ['text']
            ]
        );
    }

    /**
     * Добавляет модулю или рашсирению шаблон перевода.
     * 
     * @see BaseModule::addTranslatePattern()
     * 
     * @param BaseModule $module
     * 
     * @return void
     */
    public function addTranslatePattern(BaseModule $module): void
    {
        $category = Ge::$app->translator->getCategory($module->id);
        $category->patterns[$this->id] = [
            'basePath' => $this->getBasePath() . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $module->addTranslatePattern($this->id);
    }

   /**
     * Перевод (локализация) сообщения.
     * 
     * @param string|string[] $message Текст сообщения (сообщений).
     * @param array $params Параметры перевода.
     * @param string $locale Код локали (на которую осуществляется перевод).
     * 
     * @return string|string[] Локализованные сообщения или сообщение.
     */
    public function t($message, array $params = [], string $locale = '')
    {
        return Ge::$app->translator->translate($this->id, $message, $params, $locale);
    }

    /**
     * Возвращает параметры передаваемые в функцию JavaScript в виде строки.
     * 
     * Например, `['width' => 10, 'height' => 20, 'options' => ['id' => 'foobar']]`,
     * результат 'width: 10, height: 20, options: {"id": "foobar"}'.
     * 
     * 
     * @param array<string, mixed> $params Параметры передаваемы в скрипт.
     * 
     * @return string
     */
    protected function formatScriptParams(array $params): string
    {
        $rows = [];
        foreach ($params as $name => $value) {
            $format = $this->formatScriptParam($name, $value);
            if ($format !== '') {
                $callable = 'format' . $name . 'Param';
                if (is_callable([$this, $callable], false))
                    $rows[] = $this->$callable($value);
                else
                    $rows[] = $format;
            }
        }
        return implode(', ', $rows);
    }

    /**
     * Форматирует значение указанного параметра для передачи в JavaScript.
     * 
     * Возвращает значение в виде: '<name>: <value>'.
     * 
     * @param string $name Имя параметра.
     * @param mixed $value Значение параметра.
     * 
     * @return string
     */
    protected function formatScriptParam(string $name, mixed $value): string
    {
        if (is_bool($value))
            $value = $value ? 'true' : 'false';
        elseif (is_string($value)) {
            if ($value) {
                if (strncmp($value, self::SYMBOL_BEGIN_SCRIPT, 1) === 0)
                    $value = mb_substr($value, 1);
                else
                    $value = "'$value'";
            } else
                return '';
        } elseif (is_array($value)) {
            if ($value) 
                $value = json_encode($value);
            else
                return '';
        }
        return $name . ': ' . $value;
    }
}
