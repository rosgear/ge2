<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\PluginManager;

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
 * Менеджер плагинов предназначен для управления установленными плагинами.
 * 
 * PluginManager - это служба приложения, доступ к которой можно получить через 
 * `Ge::$app->plugins`.
 * 
 * Внимание: если служба не находится в автозагрузке приложения, то не будет проверки 
 * событий и маршрутизации плагинов при первой загрузке 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\PluginManager
 * @since 2.0
 */
class PluginManager extends Service
{
    /**
     * Контейнер плагинов в виде пар "ключ - объект".
     *
     * В качестве ключа используется идентификатор плагина в реестре.
     * 
     * @see PluginManager::add()
     * @see PluginManager::set()
     * @see PluginManager::has()
     * @see PluginManager::remove()
     * 
     * @var array<string, BaseObject>
     */
    public array $container = [];

    /**
     * Реестр установленных плагинов.
     * 
     * @see PluginManager::getRegistry()
     * 
     * @var PluginRegistry
     */
    protected PluginRegistry $registry;

    /**
     * Репозиторий плагинов.
     * 
     * @see PluginManager::getRepository()
     * 
     * @var PluginRepository
     */
    protected PluginRepository $repository;

    /**
     * Короткое имя класса.
     * 
     * Основной сценарий с таким именем будет вызываться при обращении к плагину.
     * 
     * @see PluginManager::create()
     * @see PluginManager::get()
     * 
     * @var string
     */
    protected string $callableClassName = 'Plugin';

    /**
     * Добавляет плагин в контейнер.
     * 
     * Если плагин был ранее добавлен, добавления не будет.
     * 
     * @param BaseObject $plugin Плагин.
     * @param string $id Идентификатор плагина, например 'rg.plg.foobar'.
     * 
     * @return $this
     */
    public function add(BaseObject $plugin, string $id): static
    {
        if (!isset($this->container[$id])) {
            $this->container[$id] = $plugin;
        }
        return $this;
    }

    /**
     * Устанавливает плагин в контейнер.
     * 
     * @param BaseObject $plugin Плагин.
     * @param string $id Идентификатор плагина, например 'rg.plg.foobar'.
     * 
     * @return $this
     */
    public function set(BaseObject $plugin, string $id): static
    {
        $this->container[$id] = $plugin;
        return $this;
    }

    /**
     * Проверяет, был ли добавлен плагин с указанным идентификатором.
     * 
     * @param string $id Идентификатор плагина, например 'rg.plg.foobar'.
     * 
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }

    /**
     * Удаляет плагин из контейнера.
     * 
     * @param string $id Идентификатор плагина, например 'rg.plg.foobar'.
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
     * Идентификаторы плагинов, которые вызывались ранее.
     * 
     * Пример: `['rg.plugin1' => true, 'rg.plugin2' => true, ...]`.
     * 
     * @see PluginManager::create()
     * 
     * @var array<int|string, true>
     */
    protected array $created = [];

    /**
     * Создаёт плагин по указанному идентификатору.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных 
     *     например: 123, 'rg.plg.foobar'.
     *  @param array $params Параметры плагина (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать 
     *     плагин с указанным идентификатором.
     */
    public function create(string|int $id, array $params = []): ?BaseObject
    {
        /** @var PluginRegistry $registry Реестр установленных плагинов */
        $registry = $this->getRegistry();
        if ($registry === null) {
            return null;
        }

        /** @var array $pluginParams Конфигурация установленного плагина */
        if (is_numeric($id))
            $pluginParams = $registry->getAt($id);
        else
            $pluginParams = $registry->get($id);
        // если нет плагина с указанным идентификатором
        if ($pluginParams === null) {
            return null;
        }
        // если плагин не доступен
        if (!($pluginConfig['enabled'] ?? true)) {
            return null;
        }

        $namespace = $pluginParams['namespace'];
        $path      = $pluginParams['path'];
        // абсолютный путь к плагину
        $basePath = Ge::$app->modulePath . $path;
        if (!file_exists($basePath)) {
            throw new Exception\PluginNotFoundException(
                Ge::t('app', 'File {0} "{0}" not exists', [Ge::t('app', 'Plugin'), $basePath])
            );
        }

        // чтобы лишний раз не добавлять
        if (!isset($this->created[$id])) {
            $this->created[$id] = true;
            Ge::$loader->addPsr4($namespace . NS, $basePath . DS . 'src');
        }
        $params['path']      = $path;
        $params['namespace'] = $namespace;
        $params['registry']  = $pluginParams;
        $plugin = Ge::createObject($namespace . NS . $this->callableClassName, $params);
        return $plugin ?: null;
    }

    /**
     * Создаёт плагин по указанному идентификатору.
     * 
     * @see PluginManager::create()
     * @see PluginManager::add()
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных 
     *     например: 123, 'rg.plg.foobar'.
     * @param array $params Параметры плагина (по умолчанию `[]`).
     * @param bool $throwException Если значение `true`, будет исключение при не 
     *     успешном создании плагина (по умолчанию `false`).
     * 
     * @return BaseObject|null Возвращаетзначение  `null`, если невозможно создать 
     *     плагин с указанным идентификатором.
     * 
     * @throws Exception\PluginNotFoundException Плагин с указанным идентификатором не существует.
     */
    public function get(string|int $id, array $params = [], bool $throwException = false): ?BaseObject
    {
        // если указанный плагин ранее был создан
        if (isset($this->container[$id])) {
            return $this->container[$id];
        }

        $plugin = $this->create($id, $params);
        if ($plugin === null) {
            if ($throwException)
                throw new Exception\PluginNotFoundException(
                    Ge::t('app', '{0} with id "{1}" not exists', [Ge::t('app', 'Plugin'), $id])
                );
            else
                return null;
        }
        $this->add($plugin, $id);
        return $plugin;
    }

    /**
     * Возвращает параметры из файла конфигурации плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * @param string $name Название файла конфигурации, например: 'version', 'install'.
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
     * Возвращает параметры из файла конфигурации версии плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
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
     * Возвращает параметры из файла конфигурации установки плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
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
     * Возвращает параметры из файла конфигурации версии плагина.
     * 
     * @param string $id Идентификатор плагина в реестре, например 'rg.plg.foobar'.
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если плагин или файл конфигурации 
     *     плагина не существует.
     */
    public function getVersion(string $id): array|null
    {
        /** @var array|null $params Параметры установленного компонента */
        $params = $this->getRegistry()->get($id);
        return $params ? $this->getConfigVersion($params['path'], true, true) : null;
    }

    /**
     * Возвращает шаблон параметров версии плагина.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры версии плагина.
     * 
     * @return array
     */
    public function getVersionPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'name'         => '', // название плагина
            'description'  => '', // описание плагина
            'version'      => '', // номер версии
            'versionDate'  => '', // дата версии
            'author'       => '', // имя или email автора
            'authorUrl'    => '', // URL-адрес страницы автора
            'email'        => '', // E-mail автора
            'url'          => '', // URL-адрес страницы плагина
            'license'      => '', // вид лицензии
            'licenseUrl'   => '' // URL-адрес текста лицензии
        ], $params);
    }

    /**
     * Возвращает шаблон параметров конфигурации установки плагина.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры установки 
     *     плагина.
     * 
     * @return array
     */
    public function getInstallPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'id'           => '', // идентификатор плагина
            'ownerId'      => '', // идентификатор владельца плагина
            'name'         => '', // имя плагина
            'description'  => '', // описание плагина
            'namespace'    => '', // пространство имён плагина
            'path'         => '', // каталог плагина
            'locales'      => [], // поддерживаемые локализации
            'required'     => []  // требования к версии плагина
        ], $params);
    }

    /**
     * Возвращает файл помощника указанного плагина.
     * 
     * @param string $id Идентификатор плагина, например 'rg.plg.foobar'.
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

        /** @var array|null $plugin */
        $plugin = $this->getRegistry()->get($id);
        if ($plugin) {
            $path = Ge::$app->modulePath . $plugin['path'] . DS . 'help' . DS;
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
     * Возвращает модель данных плагина.
     * 
     * @param string $name Короткое имя класса модели данных, например: 'FooBar', 'Foo\Bar'.
     * @param string $id Идентификатор компонента или название его пространства имён, 
     *     например: 'rg.plg.foobar' '\Rg\Plugin\FoobBar'.
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
     * Возвращает виджет плагина.
     * 
     * @param string $name Короткое имя класса виджета, например: 'FooBar', 'Foo\Bar'.
     * @param string $id Идентификатор плагина или название его пространства имён, 
     *     например: 'rg.plg.foobar' '\Rg\Plugin\FoobBar'.
     * @param array $config Параметры виджета в виде пар "имя - значение", которые 
     *     будут использоваться для инициализации его свойств.
     * 
     * @return BaseObject|null Возвращает значение `null`, если ошибка создания виджета.
     * 
     * @throws CreateObjectException Ошибка создания виджета.
     */
    public function getWidget(string $name, string $id, array $config = []): ?BaseObject
    {
        return $this->getObject('Widget' . NS . $name, $id, $config);
    }

    /**
     * Контейнер объектов плагина в виде пар "ключ - объект".
     * 
     * В качестве ключа используется значение идентификатора и имя объекта.
     * 
     * @see PluginManager::getObject()
     * 
     * @var array<string, BaseObject>
     */
    protected array $objects = [];

    /**
     * Возвращает объект (модель данных, и т.п.) принадлежащих установленному компоненту.
     * 
     * @param string $name Короткое имя класса объекта, например: 'Model\FooBar', 
     *     'Controller\FooBar'.
     * @param string $id Идентификатор плагина или название его пространства имён, 
     *     например: 'rg.plg.foobar' '\Rg\Plugin\FoobBar'.
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
     * Возвращает URL-адрес значка плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
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
        $iconNoneUrl = Url::theme() . '/widgets/images/module';
        // URL большого и маленького значка по умолчанию
        $iconNoneSmall = $iconNoneUrl . '/plugin-none_small.svg';
        $iconNone      = $iconNoneUrl . '/plugin-none.svg';

        // путь к значкам плагина
        $srcPath = '/assets/images';
        // абсолютный путь к плагину
        $modulePath = Ge::alias('@module') . $path;
        // URL-путь к ресурсам плагина
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
     * Возвращает информацию о плагине.
     * 
     * @param array $params Параметры плагина.
     * @param array|bool $include Информация, которую включает плагин. 
     *     Может иметь ключи:
     *     - 'version', файл конфигурации версии плагина;
     *     - 'install', файл конфигурации установки плагина;
     *     - 'icon', значки плагина.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     * @return null|array Возвращает полученную информацию о плагине, дополняя 
     *     аргумент `$params` следующими ключами:
     *     - 'version', параметры конфигурации версии плагина (если аргумент `$include` 
     *     имеет ключ 'version');
     *     - 'install', параметры конфигурации установки плагина (если аргумент `$include` 
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
     * Возвращает репозиторий плагинов.
     *
     * @return PluginRepository
     */
    public function getRepository(): PluginRepository
    {
        if (!isset($this->repository)) {
            $this->repository = new PluginRepository($this);
        }
        return $this->repository;
    }

    /**
     * Возвращает реестр установленных плагинов.
     * 
     * @return PluginRegistry
     */
    public function getRegistry()
    {
        if (!isset($this->registry)) {
            $this->registry = new PluginRegistry(Ge::alias('@config', DS . '.plugins.php'), true, $this);
        }
        return $this->registry;
    }

    /**
     * Возвращает параметры конфигурации установленного плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных 
     *     например: 123, 'rg.plg.foobar'.
     * @param mixed $default Значение, если параметры конфигурации установленного плагина 
     *     не найдены (по умолчанию `null`).

     * @return mixed
     */
    public function getRegistryParams(string|int $id, mixed $default = null): mixed
    {
        return $this->getRegistry()->getAt($id, null, $default);
    }

   /**
     * Установщики плагинов.
     * 
     * @see PluginManager::getInstaller()
     *
     * @var array<string, BaseObject>
     */
    protected array $installers = [];

    /**
     * Возвращает установщик плагина.
     * 
     * Конфигурация $config установщика должна обязательно иметь такие параметры
     * как:
     * - 'namespace', пространство имён устанавливаемого плагина, например 'Rg\Plugin\FooBar';
     * - 'path', локальный путь к компоненту, например '/rg/rg.plg.foobar'.
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
        /** @var BaseObject|null $installer Установщик плагина */
        $installer = null;

        if (empty($config['namespace']) || empty($config['path'])) {
            // Конфигурация должна иметь параметры "namespace", "path".
            throw new InvalidArgumentException('The configuration must have parameters "namespace", "path".');
        }

        // пространству имён плагина
        $namespace = $config['namespace'];
        // абсолютный путь к плагину
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
     * Проверяет, существует ли путь к плагину.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * 
     * @return bool Возвращает значение `true`, если путь к плагину существует, 
     *     `false` в противном случае.
     */
    public function pathExists(string $path): bool
    {
        return file_exists(Ge::$app->modulePath . $path);
    }

    /**
     * Проверяет существование указанного файла в каталоге плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.plg.foobar' и имя файла 
     * :   '/assets/css/foobar.css', то будет проверен файл '../rg/rg.plg.foobar/assets/css/foobar.css'.
     * 
     * @return bool Возвращает значение `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`. 
     */
    public function fileExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . $name);
    }

    /**
     * Проверяет существование указанного файла в каталоге "src" плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * @param string $name Короткое имя файла (без расширения '.php').  
     *     Например, если указан локальный путь '/rg/rg.plg.foobar', то имя файла:
     *     - 'Model/FooBar', будет проверен файл '.../rg/rg.plg.foobar/src/Model/FooBar.php';
     *     - 'Plugin', будет проверен файл '.../rg/rg.plg.foobar/src/Plugin.php';
     * 
     * @return bool Возвращает значение `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`. 
     */
    public function sourceExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . DS . 'src' . DS . $name . '.php');
    }

    /**
     * Проверяет существование модели данных в каталоге плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * @param string $name Имя модуля данных. Например, если указан локальный путь 
     *     '/rg/rg.plg.foobar', то имя файла:
     *     - 'FooBar', будет проверен файл '.../rg/rg.plg.foobar/src/Model/FooBar.php';
     *     - 'Foo/Bar', будет проверен файл '.../rg/rg.plg.foobar/src/Model/Foo/Bar.php';
     * 
     * @return bool Возвращает `true`, если файл, указанный параметром $name, существует, 
     *     иначе возвращает `false`.
     */
    public function modelExists(string $path, string $name): bool
    {
        return $this->sourceExists($path, 'Model' . DS . $name);
    }

    /**
     * Проверяет существование файла установки в каталоге плагина.
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * 
     * @return bool Возвращает `true`, если файл установки, существует, 
     *     иначе возвращает `false`.
     */
    public function installerExists(string $path): bool
    {
        return $this->sourceExists($path, 'Installer' . DS . 'Installer');
    }

    /**
     * Создаёт установщик плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных 
     *     например: 123, 'rg.plg.foobar'.
     * @param array $params Параметры конфигурации установщика передаются ему 
     *     в конструктор класса (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает установщик, `null` в противном случае.
     */
    public function createInstaller(string|int $id, array $params = []): ?BaseObject
    {
        $pluginConfig = $this->getRegistry()->getAt($id, null);
        if ($pluginConfig === null) {
            return null;
        }

        $class = $pluginConfig['namespace'] . NS . 'Installer' . NS . 'Installer';
        if (!class_exists($class)) {
            return null;
        }
        return Ge::createObject($class, $params);
    }

    /**
     * Возвращает атрибуты плагина из базы данных по указанному идентификатору.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.plg.foobar'.
     * @param bool $assoc Если значение `true`, то возвратит ассоциативный массив 
     *     атрибутов. Иначе активная запись (по умолчанию `false`).
     * 
     * @return ActiveRecord|array<string, mixed>|null Если значение `null`, то запись 
     *     по указанному идентификатору отсутствует.
     */
    public function selectOne(string|int $id, bool $assoc = false): ActiveRecord|array|null
    {
        $plugin = new Model\Plugin();
        $plugin = $plugin->selectOne([(is_numeric($id) ? 'id' : 'plugin_id') => $id]);
        if ($plugin) {
            return $assoc ? $plugin->getAttributes() : $plugin;
        }
        return null;
    }

    /**
     * Возвращает атрибуты всех плагинов из базы данных.
     * 
     * @param null|string $key Имя ключа (атрибута) возвращаемой записи (по умолчанию `null`).
     * @param string|array<string, string> $where Условие выполнения запроса (по умолчанию `null`).
     * 
     * @return array
     */
    public function selectAll(?string $key = null, string|array $where = ''): array
    {
        $plugin = new Model\Plugin();
        return $plugin->fetchAll($key, $plugin->maskedAttributes(), $where ?: null);
    }

    /**
     * Возвращает атрибуты локализации плагина.
     * 
     * Результат имеет вид: 
     * ```php
     * [
     *     'name'        => 'Название', // название видежта
     *     'description' => 'Описание', // описание плагина
     * ]
     * ```
     * 
     * @param int $id Идентификатор плагина в базе данных.
     * 
     * @return null|array{name:string, description:string}
     */
    public function selectName(int $id): ?array
    {
        return (new Model\PluginLocale())->fetchLocale($id);
    }

    /**
     * Возвращает атрибуты локализации плагинов.
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
        return (new Model\PluginLocale())->fetchNames($attribute, $languageCode);
    }

    /**
     * Ообновляет реестр установленных плагинов.
     * 
     * @return void
     */
    public function update(): void
    {
        $this->getRegistry()->update();
    }

    /**
     * Расшифровует идентификатор установки плагина.
     * 
     * Идентификатор установки используется для идентификации плагина в процессе 
     * установки.
     * 
     * @see \Ge\Encryption\Encrypter::decryptString()
     * 
     * @param null|string $id Идентификатор установки. Зашифрованные параметры плагина:
     *     локальный путь и пространство имён.
     * 
     * @return string|array{path:string, namespace:string} Возвращает значение `string`, 
     *     если возникла ошибка при расшифровке параметра. Иначе, массив: 
     *     `['path' => '/rg/rg.plg.foobar', 'namespace' => 'Rg\Plugin\FooBar']`.
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
     * Шифрует идентификатор установки плагина.
     * 
     * Идентификатор установки используется для идентификации плагина 
     * в процессе установки.
     * 
     * @see \Ge\Encryption\Encrypter::encryptString()
     * 
     * @param string $path Локальный путь к плагину, например '/rg/rg.plg.foobar'.
     * @param string $namespace Пространство имён плагина, например 'Ge\Plugin\FooBar'.
     * 
     * @return string
     */
    public function encryptInstallId(string $path, string $namespace): string
    {
        return Ge::$app->encrypter->encryptString($path . ',' . $namespace);
    }

    /**
     * Перевод (локализация) сообщения плагина.
     *
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.plg.foobar'.
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array $params Параметры перевода.
     * 
     * @return string
     * 
     * @throws \Ge\I18n\Exception\CategoryNotFoundException Сообщения плагина не найдены.
     */
    public function t(string|int $id, string|array $message, array $params = [])
    {
        /** @var \Ge\I18n\Translator $translator */
        $translator = Ge::$app->translator;

        // если сообщения плагина уже добавлены
        if ($translator->categoryExists($id)) {
            return $translator->translate($id, $message, $params);
        }
    
        /** @var string|null $path Локальный путь к плагину */
        $path = $this->getRegistry()->getAt($id, 'path');
        if ($path) {
            $this->addTranslateCategory($id, $path);
        }
        return $translator->translate($id, $message, $params);
    }

    /**
     * Источники (категории) локализации плагинов.
     * 
     * @see PluginManager::addTranslateCategory()
     * 
     * @var array<string, BaseSource>
     */
    protected array $messageSources = [];

    /**
     * Добавляет источник (категорию) локализации плагина транслятору.
     * 
     * @see \Ge\I18n\Translator::addCategory()
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.plg.foobar'.
     * @param string $path Локальный путь плагина, например,'/rg/rg.plg.foobar'.
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
     * Возвращает имя установленного плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе 
     *     данных, например: 123, 'rg.plg.foobar'.
     * 
     * @return null|string Возвращает значение `null` если плагин не найден.
     */
    public function getName(string|int $id): ?string
    {
        if (is_numeric($id)) {
            /** @var array|null $name Имя плагина в текущей локализации*/
            $name = (new Model\PluginLocale())->fetchLocale($id);
            if ($name === null) {
                /** @var array $params|null Параметры плагина */
                $params = $this->getRegistry()->getAt($id);
                return $params ? $params['name'] : null;
            }
            return $name['name'];
        } else {
            /** @var array $params|null Параметры плагина */
            $params = $this->getRegistry()->getAt($id);
            if ($params) {
                /** @var array|null $name Имя плагина в текущей локализации*/
                $name = (new Model\PluginLocale())->fetchLocale($params['rowId']);
                if ($name === null) {
                    return $params['name'];
                }
                return $name['name'];
            }
        }
        return null;
    }

    /**
     * Возвращает параметры источника (категории) транслятора плагина.
     * 
     * Т.к. плагин не имеет своего файла конфигурации, то для создания источника 
     * (категории) {@see \Ge\I18n\Translator::addCategory()} транслятора плагина
     * применяется шаблон параметров.
     * 
     * @param string $path Локальный путь плагина, например '/rg/rg.plg.foobar'.
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
