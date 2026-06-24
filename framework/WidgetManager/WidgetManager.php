<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\WidgetManager;

use Ge;
use Ge\Helper\Url;
use Ge\Stdlib\Service;
use Ge\Db\ActiveRecord;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\Collection;
use Ge\I18n\Source\BaseSource;
use Ge\Exception\CreateObjectException;
use Ge\Exception\InvalidArgumentException;
use Ge\ServiceManager\Exception\NotInstantiableException;
 
/**
 * Менеджер виджетов предназначен для управления установленными виджетами.
 * 
 * WidgetManager - это служба приложения, доступ к которой можно получить через 
 * `Ge::$app->widgets`.
 * 
 * Внимание: если служба не находится в автозагрузке приложения, то не будет проверки 
 * событий и маршрутизации виджетов при первой загрузке 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager
 * @since 2.0
 */
class WidgetManager extends Service
{
    /**
     * Контейнер виджетов в виде пар "ключ - объект".
     *
     * В качестве ключа используется идентификатор виджета в реестре.
     * 
     * @see WidgetManager::add()
     * @see WidgetManager::set()
     * @see WidgetManager::has()
     * @see WidgetManager::remove()
     * 
     * @var array<string, BaseObject>
     */
    public array $container = [];

    /**
     * Реестр установленных виджетов.
     * 
     * @see WidgetManager::getRegistry()
     * 
     * @var WidgetRegistry
     */
    protected WidgetRegistry $registry;

    /**
     * Репозиторий виджетов.
     * 
     * @see WidgetManager::getRepository()
     * 
     * @var WidgetRepository
     */
    protected WidgetRepository $repository;

    /**
     * Короткое имя класса.
     * 
     * Основной сценарий с таким именем будет вызываться при обращении к виджету.
     * 
     * @see WidgetManager::create()
     * @see WidgetManager::get()
     * 
     * @var string
     */
    protected string $callableClassName = 'Widget';

    /**
     * Добавляет виджет в контейнер.
     * 
     * Если виджет был ранее добавлен, добавления не будет.
     * 
     * @param BaseObject $widget Виджет.
     * @param string $id Идентификатор виджета, например 'rg.wd.foobar'.
     * 
     * @return $this
     */
    public function add(BaseObject $widget, string $id): static
    {
        if (!isset($this->container[$id])) {
            $this->container[$id] = $widget;
        }
        return $this;
    }

    /**
     * Устанавливает виджет в контейнер.
     * 
     * @param BaseObject $widget Виджет.
     * @param string $id Идентификатор виджета, например 'rg.wd.foobar'.
     * 
     * @return $this
     */
    public function set(BaseObject $widget, string $id): static
    {
        $this->container[$id] = $widget;
        return $this;
    }

    /**
     * Проверяет, был ли добавлен виджет с указанным идентификатором.
     * 
     * @param string $id Идентификатор виджета, например 'rg.wd.foobar'.
     * 
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }

    /**
     * Удаляет виджет из контейнера.
     * 
     * @param string $id Идентификатор виджета, например 'rg.wd.foobar'.
     * 
     * @return $this
     */
    public function remove(string $id): static
    {
        if (isset($this->container[$id])) {
            unset($this->container[$id]);
        }
        return $this;
    }

    /**
     * Идентификаторы виджетов, которые вызывались ранее.
     * 
     * Пример: `['rg.widget1' => true, 'rg.widget2' => true, ...]`.
     * 
     * @see WidgetManager::create()
     * 
     * @var array<int|string, true>
     */
    protected array $created = [];

    /**
     * Создаёт виджет по указанному идентификатору.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных 
     *     например: 123, 'rg.wd.foobar'.
     *  @param array $params Параметры виджета (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать 
     *     виджет с указанным идентификатором.
     */
    public function create(string|int $id, array $params = []): ?BaseObject
    {
        /** @var WidgetRegistry $registry Реестр установленных виджетов */
        $registry = $this->getRegistry();
        if ($registry === null) {
            return null;
        }

        /** @var array $widgetParams Конфигурация установленного виджета */
        if (is_numeric($id))
            $widgetParams = $registry->getAt($id);
        else
            $widgetParams = $registry->get($id);    
        // если нет виджета с указанным идентификатором
        if ($widgetParams === null) {
            return null;
        }
        // если виджет не доступен
        if (!($widgetConfig['enabled'] ?? true)) {
            return null;
        }

        $namespace = $widgetParams['namespace'];
        $path      = $widgetParams['path'];
        // абсолютный путь к виджету
        $basePath = Ge::$app->modulePath . $path;
        if (!file_exists($basePath)) {
            throw new Exception\WidgetNotFoundException(
                Ge::t('app', 'File {0} "{0}" not exists', [Ge::t('app', 'Widget'), $basePath])
            );
        }

        // чтобы лишний раз не добавлять
        if (!isset($this->created[$id])) {
            $this->created[$id] = true;
            Ge::$loader->addPsr4($namespace . NS, $basePath . DS . 'src');
        }
        $params['path']      = $path;
        $params['namespace'] = $namespace;
        $params['registry']  = $widgetParams;
        $widget = Ge::createObject($namespace . NS . $this->callableClassName, $params);
        return $widget ?: null;
    }

    /**
     * @see WidgetManager::getWidgetId()
     * 
     * @var array<string, string> 'rg.wd.foobar:test' => 'rg.wd.foobar'
     */
    private array $_ids = [];

    /**
     * Возвращает идентификатор виджета из указанной строки.
     * 
     * Например: 'rg.wd.foobar:test' => 'rg.wd.foobar'.
     * 
     * @param string $str Уникальный идентификатор виджета.
     * 
     * @return string
     */
    public function getWidgetId(string $str): string
    {
        if (isset($this->_ids[$str])) {
            return $this->_ids[$str];
        }

        $pos = mb_strpos($str, ':');
        if ($pos !== false)
            $id = mb_substr($str, 0, $pos);
        else
            $id = $str;
        return $this->_ids[$str] = $id;
    }

    /**
     * Создаёт виджет по указанному идентификатору.
     * 
     * @see WidgetManager::create()
     * @see WidgetManager::add()
     * 
     * @param string $uniqueId Уникальный идентификатор виджета на странице, например: 
     *     'rg.wd.foobar' или 'rg.wd.foobar:top'.
     * @param array $params Параметры виджета (по умолчанию `[]`).
     * @param bool $throwException Если значение `true`, будет исключение при не 
     *     успешном создании виджета (по умолчанию `false`).
     * 
     * @return BaseObject|null Возвращаетзначение  `null`, если невозможно создать 
     *     виджет с указанным идентификатором.
     * 
     * @throws Exception\WidgetNotFoundException Виджет с указанным идентификатором не существует.
     */
    public function get(string $uniqueId, array $params = [], bool $throwException = false): ?BaseObject
    {
        // уникальный идентификатор виджета
        $id = $this->getWidgetId($uniqueId);

        // если указанный виджет ранее был создан
        if (isset($this->container[$uniqueId])) {
            $widget = $this->container[$uniqueId];
            if ($widget->useReconfigure) {
                $widget->configure($params);
            }
            return $widget;
        }

        // для передачи уникального идентификатора виджету
        $params['id'] = $params['id'] ?? $uniqueId;
        $widget = $this->create($id, $params);
        if ($widget === null) {
            if ($throwException)
                throw new Exception\WidgetNotFoundException(
                    Ge::t('app', '{0} with id "{1}" not exists', [Ge::t('app', 'Widget'), $id])
                );
            else
                return null;
        }
        $this->add($widget, $uniqueId);
        return $widget;
    }

    /**
     * Возвращает параметры из файла конфигурации виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $name Название файла конфигурации, например: 'widget', 'install'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Если значение `null`, файл конфигурации с указанным 
     *     именем не существует.
     */
    public function getConfigFile(string $path, string $name, bool $associative = true): Collection|array|null
    {
        $filename = Ge::$app->modulePath . $path . DS .'config' . DS . '.' . $name . '.php';
        if (!file_exists($filename)) {
            return null;
        }

        $fileConfig = include($filename);
        if ($associative) {
            return $fileConfig;
        }
        return Collection::createInstance($fileConfig);
    }

    /**
     * Возвращает параметры из файла конфигурации версии виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * @param bool $usePattern Использовать шаблон параметров, только для ассоциативного 
     *     массива параметров (по умолчанию `true`).
     * 
     * @return Collection|array|null Если значение `null`, файл конфигурации с указанным 
     *     именем не существует.
     */
    public function getConfigVersion(string $path, bool $associative = true, bool $usePattern = true): Collection|array|null
    {
        $params = $this->getConfigFile($path, 'version', $associative);
        if ($associative) {
            return $usePattern && is_array($params) ? $this->getVersionPattern($params) : $params;
        } else
            return $params;
    }

    /**
     * Возвращает параметры из файла конфигурации установки виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * @param bool $usePattern Использовать шаблон параметров, только для ассоциативного 
     *     массива параметров (по умолчанию `true`).
     * 
     * @return Collection|array|null Если значение `null`, файл конфигурации с указанным 
     *     именем не существует.
     */
    public function getConfigInstall(string $path, bool $associative = true, bool $usePattern = true): Collection|array|null
    {
        $params = $this->getConfigFile($path, 'install', $associative);
        if ($associative)
            return $usePattern && is_array($params) ? $this->getInstallPattern($params) : $params;
        else
            return $params;
    }

    /**
     * Возвращает параметры из файла конфигурации версии виджета.
     * 
     * @param string $id Идентификатор виджета в реестре, например 'rg.wd.foobar'.
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если виджет или файл конфигурации 
     *     виджета не существует.
     */
    public function getVersion(string $id): array|null
    {
        /** @var array|null $params Параметры установленного компонента */
        $params = $this->getRegistry()->get($id);
        return $params ? $this->getConfigVersion($params['path'], true, true) : null;
    }

    /**
     * Возвращает шаблон параметров версии виджета.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры версии виджета.
     * 
     * @return array
     */
    public function getVersionPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'name'         => '', // название виджета
            'description'  => '', // описание виджета
            'version'      => '', // номер версии
            'versionDate'  => '', // дата версии
            'author'       => '', // имя или email автора
            'authorUrl'    => '', // URL-адрес страницы автора
            'email'        => '', // E-mail автора
            'url'          => '', // URL-адрес страницы виджета
            'license'      => '', // вид лицензии
            'licenseUrl'   => '' // URL-адрес текста лицензии
        ], $params);
    }

    /**
     * Возвращает шаблон параметров конфигурации установки виджета.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры установки 
     *     виджета.
     * 
     * @return array
     */
    public function getInstallPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'id'           => '', // идентификатор виджета
            'name'         => '', // имя виджета
            'description'  => '', // описание виджета
            'namespace'    => '', // пространство имён виджета
            'path'         => '', // каталог виджета
            'shortcodes'   => [], // шорткоды
            'editor'       => [], // кнопки в редакторе
            'locales'      => [], // поддерживаемые локализации
            'required'     => []  // требования к версии виджета
        ], $params);
    }

    /**
     * Возвращает файл помощника указанного виджета.
     * 
     * @param string $id Идентификатор виджета, например 'rg.foo.bar'.
     * @param null|string $locale Имя локализации, например: 'ru-RU', 'en-GB'. Если 
     *     значение `null`, применяется текущая локализация (по умолчанию `null`).
     * 
     * @return string|null
     */
    public function getHelpFile(string $id, string $subject, ?string $locale = null): ?string
    {
        if ($locale === null) {
            $locale = Ge::$app->language->locale;
        }

        /** @var array|null $widget */
        $widget = $this->getRegistry()->get($id);
        if ($widget) {
            $path = Ge::$app->modulePath . $widget['path'] . DS . 'help' . DS;
            // с локализацией
            $filename = $path . $subject . '-' . $locale . '.phtml';
            if (file_exists($filename)) {
                return $filename;
            }
            // без локализации
            $filename = $path . $subject . '.phtml';
            if (file_exists($filename)) {
                return $filename;
            }
        }
        return null;
    }

    /**
     * Возвращает модель данных виджета.
     * 
     * @param string $name Короткое имя класса модели данных, например: 'FooBar', 'Foo\Bar'.
     * @param string $id Идентификатор компонента или название его пространства имён, 
     *     например: 'rg.wd.foobar' '\Rg\Widget\FoobBar'.
     * @param array $config Параметры компонента в виде пар "имя - значение", которые 
     *     будут использоваться для инициализации его свойств.
     * 
     * @return BaseObject|null Возвращает значение `null`, если ошибка создания модели 
     *     данных.
     * 
     * @throws CreateObjectException Ошибка создания модели данных.
     */
    public function getModel(string $name, string $id, array $config = []): ?BaseObject
    {
        return $this->getObject('Model' . NS . $name, $id, $config);
    }

    /**
     * Контейнер объектов виджета в виде пар "ключ - объект".
     * 
     * В качестве ключа используется значение идентификатора и имя объекта.
     * 
     * @see WidgetManager::getObject()
     * 
     * @var array<string, BaseObject>
     */
    protected array $objects = [];

    /**
     * Возвращает объект (модель данных, и т.п.) принадлежащих установленному компоненту.
     * 
     * @param string $name Короткое имя класса объекта, например: 'Model\FooBar', 
     *     'Controller\FooBar'.
     * @param string $id Идентификатор компонента или название его пространства имён, 
     *     например: 'rg.wd.foobar' '\Rg\Widget\FoobBar'.
     * @param array $config Параметры объекта (модель данных, и т.п.), которые будут 
     *     использоваться для инициализации его свойств.
     * 
     * @return BaseObject|null Возвращает значение `null`, если ошибка создания модели данных.
     * 
     * @throws CreateObjectException Ошибка создания модели данных.
     */
    public function getObject(string $name, string $id, array $config = []): ?BaseObject
    {
        $objectId = $name . $id;
        // если объект ранее создан
        if (isset($this->objects[$objectId])) {
            return $this->objects[$objectId];
        }

        $object = null;
        $params = $this->getRegistry()->getAt($id);
        if ($params) {
            // для доступа к пространству имён модуля
            Ge::$loader->addPsr4($params['namespace'] . NS, Ge::$app->modulePath . $params['path'] . DS . 'src');
            try {
                $object = Ge::createObject($params['namespace'] . NS . $name, $config);
            } catch  (NotInstantiableException $e) {
                // ошибка внутри контроллера
            } catch  (\Exception $e) {
                // в процессе создания контроллера могут возникнуть ошибки, режим `GE_MODE_DEV`
                // позволяет их увидеть
                if (GE_MODE_DEV) {
                    throw new CreateObjectException($e->getMessage());
                }
            }
        }
        return $object;
    }

    /**
     * Возвращает URL-адрес значка виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param null|string $type Тип возвращаемого значка:  
     *     - 'small', минимальный размер 16x16 пкс.;
     *     - 'icon', минимальный размер 32x32 пкс.;
     *     Если значение `null`, результат будет иметь вид: 
     *     `['icon' => '...', 'small' => '...']`. 
     *     По умолчанию `null`.
     * 
     * @return string|array{small:string, icon:string}
     */
    public function getIcon(string $path, ?string $type = null): string|array
    {
        // URL-путь к значкам по умолчанию
        $iconNoneUrl = Url::theme() . '/widgets/images/widget';
        // URL большого и маленького значка по умолчанию
        $iconNoneSmall = $iconNoneUrl . '/widget-none_small.svg';
        $iconNone      = $iconNoneUrl . '/widget-none.svg';

        // путь к значкам виджета
        $srcPath = '/assets/images';
        // абсолютный путь к виджету
        $modulePath = Ge::alias('@module') . $path;
        // URL-путь к ресурсам виджета
        $moduleUrl  = Ge::alias('@module::') . $path;

        if ($type === null) {
            $icon      = $srcPath . '/icon.svg';
            $smallIcon = $srcPath . '/icon_small.svg';

            // водный знак
            return [
                'small' => file_exists($modulePath . $smallIcon) ? $moduleUrl . $smallIcon : $iconNoneSmall,
                'icon'  => file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNone
            ];
        } else
            // значок маленький
            if ($type === 'small') {
                $icon = $srcPath . '/icon_small.svg';
                return file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNoneSmall;
            } else
                // значок большой
                if ($type === 'icon') {
                    $icon = $srcPath . '/icon.svg';
                    return file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNone;
                }
        return '';
    }

    /**
     * Возвращает информацию о виджете.
     * 
     * @param array $params Параметры виджета.
     * @param array|bool $include Информация, которую включает виджет. 
     *     Может иметь ключи:
     *     - 'version', файл конфигурации версии виджета;
     *     - 'install', файл конфигурации установки виджета;
     *     - 'icon', значки виджета.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     * @return null|array Возвращает полученную информацию о виджете, дополняя 
     *     аргумент `$params` следующими ключами:
     *     - 'version', параметры конфигурации версии виджета (если аргумент `$include` 
     *     имеет ключ 'version');
     *     - 'install', параметры конфигурации установки виджета (если аргумент `$include` 
     *     имеет ключ 'install');
     *     - 'smallIcon', 'icon' {@see BaseManager::getIcon()}.
     *     Если значение `null`, то невозможно получить информацию.
     */
    public function getInfo(array $params, array|bool $include): ?array
    {
        if (empty($params['path'])) {
            return null;
        }

        if (is_array($include)) {
            $incVersion = $include['version'] ?? false;
            $incInstall = $include['install'] ?? false;
            $incIcon    = $include['icon'] ?? false;
        } else {
            $incVersion = $incInstall = $incIcon = $include;
        }

        if ($incIcon) {
            $icon = $this->getIcon($params['path']);
            $params['smallIcon'] = $icon['small'];
            $params['icon']      = $icon['icon'];
        }
        if ($incVersion) {
            $params['version'] = $this->getConfigVersion($params['path'], true, true);
        }
        if ($incInstall) {
            $params['install'] = $this->getConfigInstall($params['path'], true, true);
        }
        return $params;
    }

    /**
     * Возвращает репозиторий виджетов.
     *
     * @return WidgetRepository
     */
    public function getRepository(): WidgetRepository
    {
        if (!isset($this->repository)) {
            $this->repository = new WidgetRepository($this);
        }
        return $this->repository;
    }

    /**
     * Возвращает реестр установленных виджетов.
     * 
     * @return WidgetRegistry
     */
    public function getRegistry()
    {
        if (!isset($this->registry)) {
            $this->registry = new WidgetRegistry(Ge::alias('@config', DS . '.widgets.php'), true, $this);
        }
        return $this->registry;
    }

    /**
     * Возвращает параметры конфигурации установленного виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных 
     *     например: 123, 'rg.wd.foobar'.
     * @param mixed $default Значение, если параметры конфигурации установленного виджета 
     *     не найдены (по умолчанию `null`).

     * @return mixed
     */
    public function getRegistryParams(string|int $id, mixed $default = null): mixed
    {
        return $this->getRegistry()->getAt($id, null, $default);
    }

   /**
     * Установщики виджетов.
     * 
     * @see WidgetManager::getInstaller()
     *
     * @var array<string, BaseObject>
     */
    protected array $installers = [];

    /**
     * Возвращает установщик виджета.
     * 
     * Конфигурация $config установщика должна обязательно иметь такие параметры
     * как:
     * - 'namespace', пространство имён устанавливаемого виджета, например 'Rg\Widget\FooBar';
     * - 'path', локальный путь к компоненту, например '/rg/g.wd.foobar'.
     * Эти параметры добавляются в загрузчик классов {@see \Ge\Ge::$loader}.
     * 
     * @param array{namespace:string, path:string} $config Параметры конфигурации 
     *     установщика в виде пар "ключ - значение", которые будут использоваться 
     *     для его инициализации.

     * @return BaseObject
     * 
     * @throws InvalidArgumentException Отсутствуют параметры конфигурации: namespace, path.
     */
    public function getInstaller(array $config)
    {
        /** @var BaseObject|null $installer Установщик виджета */
        $installer = null;

        if (empty($config['namespace']) || empty($config['path'])) {
            // Конфигурация должна иметь параметры "namespace", "path".
            throw new InvalidArgumentException('The configuration must have parameters "namespace", "path".');
        }

        // пространству имён виджета
        $namespace = $config['namespace'];
        // абсолютный путь к виджету
        $path = Ge::$app->modulePath . $config['path'];

        if (isset($this->installers[$namespace])) {
            return $this->installers[$namespace];
        }
        // для доступа к пространству имён объекта
        Ge::$loader->addPsr4($namespace . NS, $path . DS . 'src');

        try {
            $installer = Ge::createObject($namespace . NS . 'Installer\Installer', $config);
        } catch  (NotInstantiableException $e) {
            // ошибка внутри установщика
        } catch  (\Exception $e) {
            // в процессе создания установщика могут возникнуть ошибки, режим `GE_MODE_DEV`
            // позволяет их увидеть
            if (GE_MODE_DEV) {
                throw new CreateObjectException($e->getMessage());
            }
        }

        if ($installer) {
            $this->installers[$namespace] = $installer;
        }
        return $installer;
    }

    /**
     * Проверяет, существует ли путь к виджету.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * 
     * @return bool Возвращает значение `true`, если путь к виджету существует, 
     *     `false` в противном случае.
     */
    public function pathExists(string $path): bool
    {
        return file_exists(Ge::$app->modulePath . $path);
    }

    /**
     * Проверяет существование указанного файла в каталоге виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.wd.foobar' и имя файла 
     * :   '/assets/css/foobar.css', то будет проверен файл '../rg/rg.wd.foobar/assets/css/foobar.css'.
     * 
     * @return bool Возвращает значение `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`. 
     */
    public function fileExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . $name);
    }

    /**
     * Проверяет существование указанного файла в каталоге "src" виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $name Короткое имя файла (без расширения '.php').  
     *     Например, если указан локальный путь '/rg/rg.wd.foobar', то имя файла:
     *     - 'Model/FooBar', будет проверен файл '.../rg/rg.wd.foobar/src/Model/FooBar.php';
     *     - 'Widget', будет проверен файл '.../rg/rg.wd.foobar/src/Widget.php';
     * 
     * @return bool Возвращает значение `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`. 
     */
    public function sourceExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . DS . 'src' . DS . $name . '.php');
    }


    /**
     * Проверяет существование указанного файла контроллера в каталоге объекта.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $name Имя контроллера. Например, если указан локальный путь 
     *     '/rg/rg.wd.foobar', то имя файла:
     *     - 'FooBar', будет проверен файл '.../rg/rg.wd.foobar/src/Controller/FooBar.php';
     *     - 'Foo/Bar', будет проверен файл '.../rg/rg.wd.foobar/src/Controller/Foo/Bar.php';
     * 
     * @return bool Возвращает `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`.
     */
    public function controllerExists(string $path, string $name): bool
    {
        return $this->sourceExists($path, 'Controller' . DS . $name);
    }

    /**
     * Проверяет существование модели данных в каталоге виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $name Имя модуля данных. Например, если указан локальный путь 
     *     '/rg/rg.wd.foobar', то имя файла:
     *     - 'FooBar', будет проверен файл '.../rg/rg.wd.foobar/src/Model/FooBar.php';
     *     - 'Foo/Bar', будет проверен файл '.../rg/rg.wd.foobar/src/Model/Foo/Bar.php';
     * 
     * @return bool Возвращает `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`.
     */
    public function modelExists(string $path, string $name): bool
    {
        return $this->sourceExists($path, 'Model' . DS . $name);
    }

    /**
     * Проверяет существование файла установки в каталоге виджета.
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * 
     * @return bool Возвращает `true`, если файл установки, существует, 
     *     иначе возвращает `false`.
     */
    public function installerExists(string $path): bool
    {
        return $this->sourceExists($path, 'Installer' . DS . 'Installer');
    }

    /**
     * Создаёт установщик виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных 
     *     например: 123, 'rg.wd.foobar'.
     * @param array $params Параметры конфигурации установщика передаются ему 
     *     в конструктор класса (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает установщик, `null` в противном случае.
     */
    public function createInstaller(string|int $id, array $params = []): ?BaseObject
    {
        $widgetConfig = $this->getRegistry()->getAt($id, null);
        if ($widgetConfig === null) {
            return null;
        }

        $class = $widgetConfig['namespace'] . NS . 'Installer' . NS . 'Installer';
        if (!class_exists($class)) {
            return null;
        }
        return Ge::createObject($class, $params);
    }

    /**
     * Возвращает атрибуты виджета из базы данных по указанному идентификатору.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.wd.foobar'.
     * @param bool $assoc Если значение `true`, то возвратит ассоциативный массив 
     *     атрибутов. Иначе активная запись (по умолчанию `false`).
     * 
     * @return ActiveRecord|array<string, mixed>|null Если значение `null`, то запись 
     *     по указанному идентификатору отсутствует.
     */
    public function selectOne(string|int $id, bool $assoc = false): ActiveRecord|array|null
    {
        $widget = new Model\Widget();
        $widget = $widget->selectOne([(is_numeric($id) ? 'id' : 'widget_id') => $id]);
        if ($widget) {
            return $assoc ? $widget->getAttributes() : $widget;
        }
        return null;
    }

    /**
     * Возвращает атрибуты всех виджетов из базы данных.
     * 
     * @param null|string $key Имя ключа (атрибута) возвращаемой записи (по умолчанию `null`).
     * @param string|array<string, string> $where Условие выполнения запроса (по умолчанию `null`).
     * 
     * @return array
     */
    public function selectAll(?string $key = null, string|array $where = ''): array
    {
        $widget = new Model\Widget();
        return $widget->fetchAll($key, $widget->maskedAttributes(), $where ?: null);
    }

    /**
     * Возвращает атрибуты локализации виджета.
     * 
     * Результат имеет вид: 
     * ```php
     * [
     *     'name'        => 'Название', // название видежта
     *     'description' => 'Описание', // описание виджета
     * ]
     * ```
     * 
     * @param int $id Идентификатор виджета в базе данных.
     * 
     * @return null|array{name:string, description:string}
     */
    public function selectName(int $id): ?array
    {
        return (new Model\WidgetLocale())->fetchLocale($id);
    }

    /**
     * Возвращает атрибуты локализации виджетов.
     * 
     * Результат имеет вид: 
     * ```php
     * [
     *     'rowId1' => ['name' => 'Название1', 'description' => 'Описание1'],
     *     'rowId2' => ['name' => 'Название2', 'description' => 'Описание2'],
     *     // ...
     * ]
     * ```
     * @param null|string $attribute Название атрибута ('name', 'description') возвращаемого 
     *     для каждого идентификатора компонента. Если значение `null`, возвратит
     *     все атрибуты (по умолчанию `null`).
     * @param null|int $languageCode Идентификатор языка. Если значение `null`, то идентификатор 
     *     текущего языка (по умолчанию `null`).
     * 
     * @return array<int, array{name:string, description:string}>
     */
    public function selectNames(?string $attribute = null, ?int $languageCode = null): ?array
    {
        return (new Model\WidgetLocale())->fetchNames($attribute, $languageCode);
    }

    /**
     * Ообновляет реестр установленных виджетов.
     * 
     * @return void
     */
    public function update(): void
    {
        $this->getRegistry()->update();
    }

    /**
     * Расшифровует идентификатор установки виджета.
     * 
     * Идентификатор установки используется для идентификации виджета в процессе 
     * установки.
     * 
     * @see \Ge\Encryption\Encrypter::decryptString()
     * 
     * @param null|string $id Идентификатор установки. Зашифрованные параметры виджета:
     *     локальный путь и пространство имён.
     * 
     * @return string|array{path:string, namespace:string} Возвращает значение `string`, 
     *     если возникла ошибка при расшифровке параметра. Иначе, массив: 
     *     `['path' => '/rg/rg.wd.foobar', 'namespace' => 'Rg\Widget\FooBar']`.
     */
    public function decryptInstallId(?string $id): string|array
    {
        if (empty($id)) {
            return Ge::t('app', 'Invalid query parameter');
        }

        // попытка получить параметр установки (path,namespace)
        try {
            $decryptId = Ge::$app->encrypter->decryptString($id);
        } catch (\Exception $e) {
            return Ge::t('app', 'Invalid query parameter') . ': ' . $e->getMessage(); 
        }

        // получение: path и namespace модуля
        list($path, $namespace) = explode(',', $decryptId);
        if (empty($path) || empty($namespace)) {
            return Ge::t('app', 'Invalid query parameter');
        }
        // если вдруг лишние символы
        return [
            'path'      => str_replace(['\\', '//'], DS, $path),
            'namespace' => str_replace('\\', NS, $namespace)
        ];
    }

    /**
     * Шифрует идентификатор установки виджета.
     * 
     * Идентификатор установки используется для идентификации виджета 
     * в процессе установки.
     * 
     * @see \Ge\Encryption\Encrypter::encryptString()
     * 
     * @param string $path Локальный путь к виджету, например '/rg/rg.wd.foobar'.
     * @param string $namespace Пространство имён виджета, например 'Rg\Widget\FooBar'.
     * 
     * @return string
     */
    public function encryptInstallId(string $path, string $namespace): string
    {
        return Ge::$app->encrypter->encryptString($path . ',' . $namespace);
    }

    /**
     * Перевод (локализация) сообщения виджета.
     *
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.wd.foobar'.
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array $params Параметры перевода.
     * 
     * @return string
     * 
     * @throws \Ge\I18n\Exception\CategoryNotFoundException Сообщения виджета не найдены.
     */
    public function t(string|int $id, string|array $message, array $params = [])
    {
        /** @var \Ge\I18n\Translator $translator */
        $translator = Ge::$app->translator;

        // если сообщения виджета уже добавлены
        if ($translator->categoryExists($id)) {
            return $translator->translate($id, $message, $params);
        }
    
        /** @var string|null $path Локальный путь к виджету */
        $path = $this->getRegistry()->getAt($id, 'path');
        if ($path) {
            $this->addTranslateCategory($id, $path);
        }
        return $translator->translate($id, $message, $params);
    }

    /**
     * Источники (категории) локализации виджетов.
     * 
     * @see WidgetManager::addTranslateCategory()
     * 
     * @var array<string, BaseSource>
     */
    protected array $messageSources = [];

    /**
     * Добавляет источник (категорию) локализации виджета транслятору.
     * 
     * @see \Ge\I18n\Translator::addCategory()
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.wd.foobar'.
     * @param string $path Локальный путь виджета, например,'/rg/rg.wd.foobar'.
     *
     * @return BaseSource
     */
    public function addTranslateCategory(string|int $id, string $path): BaseSource
    {
        return $this->messageSources[$id] = Ge::$app->translator->addCategory(
            $id,
            [
                'locale'   => 'auto',
                'patterns' => [
                    'text' => [
                        'basePath' => Ge::$app->modulePath . $path . DS .'lang',
                        'pattern'  => 'text-%s.php'
                    ]
                ],
                'autoload' => ['text']
            ]
        );
    }

    /**
     * Возвращает имя установленного виджета.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.wd.foobar'.
     * 
     * @return null|string Возвращает значение `null` если виджет не найден.
     */
    public function getName(string|int $id): ?string
    {
        if (is_numeric($id)) {
            /** @var array|null $name Имя виджета в текущей локализации*/
            $name = (new Model\WidgetLocale())->fetchLocale($id);
            if ($name === null) {
                /** @var array $params|null Параметры виджета */
                $params = $this->getRegistry()->getAt($id);
                return $params ? $params['name'] : null;
            }
            return $name['name'];
        } else {
            /** @var array $params|null Параметры виджета */
            $params = $this->getRegistry()->getAt($id);
            if ($params) {
                /** @var array|null $name Имя виджета в текущей локализации*/
                $name = (new Model\WidgetLocale())->fetchLocale($params['rowId']);
                if ($name === null) {
                    return $params['name'];
                }
                return $name['name'];
            }
        }
        return null;
    }

    /**
     * Вызывает триггер указанного виджета.
     * 
     * Если виджет доступен и имеет событие, то оно будет обработано им.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.wd.foobar'.
     * @param string $event Название события.
     * @param array $args Параметры передаваемые событием.
     * 
     * @return void
     */
    public function doEvent(string|int $id, string $event, array $args = []): void
    {
        /** @var array|null $widgetParams */
        $widgetParams = $this->getRegistry()->getAt($id);
        // если виджет доступен
        if ($widgetParams && $widgetParams['enabled']) {
            /** @var null|\Ge\View\Widget $widget */
            $widget = $this->get($id);
            if ($widget) {
                $widget->trigger($event, $args);
            }
        }
    }

    /**
     * Возвращает параметры источника (категории) транслятора виджета.
     * 
     * Т.к. виджет не имеет своего файла конфигурации, то для создания источника 
     * (категории) {@see \Ge\I18n\Translator::addCategory()} транслятора виджета
     * применяется шаблон параметров.
     * 
     * @param string $path Локальный путь виджета, например '/rg/rg.wd.foobar'.
     *
     * @return array
     */
    public static function getTranslatePattern(string $path): array
    {
        return [
            'locale'   => 'auto',
            'patterns' => [
                'text' => [
                    'basePath' => Ge::$app->modulePath . $path . DS .'lang',
                    'pattern'  => 'text-%s.php'
                ]
            ],
            'autoload' => ['text']
        ];
    }
}
