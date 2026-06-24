<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ModuleManager;

use Ge;
use Ge\Helper\Url;
use Ge\Db\ActiveRecord;
use Ge\Stdlib\Service;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\Collection;
use Ge\Exception\CreateObjectException;
use Ge\Exception\InvalidArgumentException;
use Ge\Mvc\Controller\Exception\ActionNotFoundException;
use Ge\ServiceManager\Exception\NotInstantiableException;

/**
 * Базовый класс Менеджера компонентов приложения (модулей, расширений модулей, виджетов).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class BaseManager extends Service
{
    /**
     * Контейнер компонентов в виде пар "ключ - компонент".
     *
     * В качестве ключа используется название пространства имён компонента.
     * 
     * @see BaseManager::add()
     * @see BaseManager::set()
     * @see BaseManager::has()
     * @see BaseManager::remove()
     * 
     * @var array<string, BaseObject>
     */
    public array $container = [];

    /**
     * Реестр установленных компонентов.
     * 
     * @see BaseManager::getRegistry()
     * 
     * @var BaseRegistry
     */
    protected BaseRegistry $registry;

    /**
     * Репозиторий компонентов.
     * 
     * @see BaseManager::getRepository()
     * 
     * @var BaseRepository
     */
    protected BaseRepository $repository;

    /**
     * Короткое имя класса, например: 'Module', 'Extension'.
     * 
     * Основной сценарий с таким именем будет вызываться при обращении к компоненту.
     * 
     * @see BaseManager::create()
     * @see BaseManager::get()
     * 
     * @var string
     */
    public string $callableClassName = '';

    /**
     * Добавляет компонент в контейнер.
     * 
     * Если компонент ранее добавлен, то добавления не будет.
     * 
     * @param BaseObject $component Компонент.
     * @param string $namespace Пространство имён компонента, например '\Ge\FooBar'.
     * 
     * @return $this
     */
    public function add(BaseObject $component, string $namespace): static
    {
        if (!isset($this->container[$namespace])) {
            $this->container[$namespace] = $component;
        }
        return $this;
    }

    /**
     * Устанавливает объект в контейнер.
     * 
     * @param BaseObject $component Компонент.
     * @param string $namespace Пространство имён компонента, например '\Rg\FooBar'.
     * 
     * @return $this
     */
    public function set(BaseObject $component, string $namespace): static
    {
        $this->container[$namespace] = $component;
        return $this;
    }

    /**
     * Проверяет, был ли добавлен компонент с указанным названием пространства имён.
     * 
     * @param string $namespace Пространство имён компонента, например '\Rg\FooBar'.
     * 
     * @return bool
     */
    public function has(string $namespace): bool
    {
        return isset($this->container[$namespace]);
    }

    /**
     * Удаляет компонент из контейнера.
     * 
     * @param string $namespace Пространство имён компонента, например '\Rg\FooBar'.
     * 
     * @return $this
     */
    public function remove(string $namespace): static
    {
        if (isset($this->container[$namespace])) {
            unset($this->container[$namespace]);
        }
        return $this;
    }

    /**
     * Создаёт компонент по указанному идентификатору.
     * 
     * @param string $id Идентификатор компонента, например 'rg.foobar'.
     * @param array $params Параметры компонента (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать 
     *     компонент с указанным идентификатором.
     * 
     * @throws Exception\ModuleNotFoundException Компонент с указанным идентификатором 
     *     не существует.
     */
    public function create(string $id, array $params = []): ?BaseObject
    {
        /** @var array $moduleConfig Параметры установленного компонента */
        $moduleParams = $this->getRegistry()->get($id);
        // если нет объекта с указанным идентификатором
        if ($moduleParams === null) {
            return null;
        }
        // если объект не доступен
        if (!($moduleParams['enabled'] ?? true)) {
            return null;
        }
        $namespace = $moduleParams['namespace'];
        // если указанный объект ранее был создан
        if (isset($this->container[$namespace])) {
            return $this->container[$namespace];
        }
        $path = $moduleParams['path'];
        // абсолютный путь к объекту
        $modulePath = Ge::$app->modulePath . $path;
        if (!file_exists($modulePath)) {
            throw new Exception\ModuleNotFoundException(
                Ge::t('app', 'File {0} "{0}" not exists', [Ge::t('app', $this->callableClassName), $modulePath])
            );
        }
        Ge::$loader->addPsr4($namespace . NS, $modulePath . DS . 'src');
        $params['path']      = $path;
        $params['namespace'] = $namespace;
        $module = Ge::$services->get($namespace . NS . $this->callableClassName , $params);
        return $module ?: null;
    }

    /**
     * Создаёт компонент по указанному идентификатору.
     * 
     * @see BaseManager::create()
     * @see BaseManager::add()
     * 
     * @param string $id Идентификатор компонента, например, 'rg.foobar'.
     * @param array $params Параметры компонента (по умолчанию `[]`).
     * @param bool $throwException Если значение `true`, будет исключение при не 
     *     успешном создании компонента (по умолчанию `true`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно создать 
     *     компонент с указанным идентификатором.
     * 
     * @throws Exception\ModuleNotFoundException Компонент с указанным идентификатором 
     *     не существует.
     */
    public function get(string $id, array $params = [], bool $throwException = true): ?BaseObject
    {
        /** @var \Ge\Mvc\Module\BaseModule|null $module */
        $module = $this->create($id, $params);
        if ($module === null) {
            if ($throwException)
                throw new Exception\ModuleNotFoundException(
                    Ge::t('app', '{0} with id "{1}" not exists', [Ge::t('app', $this->callableClassName), $id])
                );
            else
                return null;
        }
        $this->add($module, $module->namespace);
        return $module;
    }

    /**
     * Возвращает параметры из файла конфигурации компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $name Название файла конфигурации, например: 'module', 'install'.
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
        if (!file_exists($filename)) return null;

        $fileConfig = include($filename);
        if ($associative) {
            return $fileConfig;
        }
        return Collection::createInstance($fileConfig);
    }

    /**
     * Возвращает параметры из файла конфигурации версии компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
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
     * Возвращает параметры из файла конфигурации установки компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
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
     * Возвращает параметры из файла конфигурации версии компонента.
     * 
     * @param string $id Идентификатор компонента в реестре, например 'rg.foobar'.
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если компонент или файл конфигурации 
     *     компонента не существует.
     */
    public function getVersion(string $id): array|null
    {
        /** @var array|null $params Параметры установленного компонента */
        $params = $this->getRegistry()->get($id);
        return $params ? $this->getConfigVersion($params['path'], true, true) : null;
    }

    /**
     * Возвращает шаблон параметров версии компонента.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры версии компонента.
     * 
     * @return array
     */
    public function getVersionPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return $params;
    }

    /**
     * Возвращает шаблон параметров конфигурации установки компонента.
     *
     * @param mixed $params Параметры, которые преобразуются в параметры конфигурации 
     *     установки компонента.
     * 
     * @return array
     */
    public function getInstallPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return $params;
    }

    /**
     * Возвращает файл помощника указанного компонента.
     * 
     * @param string $id Идентификатор компонента, например 'rg.foobar'.
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

        /** @var array|null $component */
        $component = $this->getRegistry()->get($id);
        if ($component) {
            $path = Ge::$app->modulePath . $component['path'] . DS . 'help' . DS;
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
     * Возвращает модель данных компонента.
     * 
     * @param string $name Короткое имя класса модели данных, например: 'FooBar', 'Foo\Bar'.
     * @param string $id Идентификатор компонента или название его пространства имён, 
     *     например: 'rg.foobar' '\Rg\FooBar'.
     * @param array $config Параметры компонента в виде пар "имя - значение", которые 
     *     будут использоваться для инициализации его свойств.
     * 
     * @return BaseObject|null Возвращает значение `null`, если ошибка создания модели данных.
     * 
     * @throws CreateObjectException Ошибка создания модели данных.
     */
    public function getModel(string $name, string $id, array $config = []): ?BaseObject
    {
        return $this->getObject('Model' . NS . $name, $id, $config);
    }

    /**
     * Контейнер объектов компонента в виде пар "ключ - объект".
     * 
     * В качестве ключа используется значение идентификатора и имя объекта.
     * 
     * @see BaseManager::getObject()
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
     *     например: 'rg.foobar' '\Ge\FooBar'.
     * @param array $config Параметры объекта (модель данных, и т.п.), которые будут 
     *     использоваться для инициализации его свойств.
     * 
     * @return BaseObject|null Возвращает значение `null`, если ошибка создания модели данных.
     * 
     * @throws CreateObjectException Ошибка создания модели данных.
     */
    public function getObject(string $name, string $id, array $config = []): ?BaseObject
    {
        $objectId = $id . $name;
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
     * Возвращает URL-адрес значка компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/foo-bar'.
     * @param null|string $type Тип возвращаемого значка:  
     *     - 'small', минимальный размер 16x16 пкс.;
     *     - 'icon', минимальный размер 32x32 пкс.;
     *     - 'watermark', значок с прозрачностью и минимальным размером 32x32 пкс.
     *     Если значение `null`, результат будет иметь вид: 
     *     `['icon' => '...', 'small' => '...', 'watermark' => '...']`. 
     *     По умолчанию `null`.
     * @param string $prefix Префикс имени файла значка: 'extension', 'module' (по умолчанию 'module').
     * 
     * @return string|array{small:string, icon:string, watermark:string}
     */
    public function getIcon(string $path, ?string $type = null, string $prefix = 'module'): string|array
    {
        // URL-путь к значкам по умолчанию
        $iconNoneUrl = Url::theme() . '/widgets/images/module';
        // URL большого и маленького значка по умолчанию
        $iconNoneSmall = $iconNoneUrl . '/' . $prefix . '-none_small.svg';
        $iconNone      = $iconNoneUrl . '/' . $prefix . '-none.svg';

        // путь к значкам компонента
        $srcPath = '/assets/images';
        // абсолютный путь к компоненту
        $modulePath = Ge::alias('@module') . $path;
        // URL-путь к ресурсам компонента
        $moduleUrl  = Ge::alias('@module::') . $path;

        if ($type === null) {
            $icon      = $srcPath . '/icon.svg';
            $smallIcon = $srcPath . '/icon_small.svg';
            $wmarkIcon = $srcPath . '/icon_fill.svg';

            // водный знак
            return [
                'small'     => file_exists($modulePath . $smallIcon) ? $moduleUrl . $smallIcon : $iconNoneSmall,
                'icon'      => file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNone,
                'watermark' => file_exists($modulePath . $wmarkIcon) ? $moduleUrl . $wmarkIcon : $iconNone
            ];
        } else {
            // значок маленький
            if ($type === 'small') {
                $icon = $srcPath . '/icon_small.svg';
                return file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNoneSmall;
            } else
                // значок большой
                if ($type === 'icon') {
                    $icon = $srcPath . '/icon.svg';
                    return file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNone;
                } else
                    // водный знак
                    if ($type === 'watermark') {
                        $icon = $srcPath . '/icon_fill.svg';
                        return file_exists($modulePath . $icon) ? $moduleUrl . $icon : $iconNone;
                    }
        }
        return '';
    }

    /**
     * Возвращает информацию о компоненте.
     * 
     * Для получения информации, необходимо, чтобы аргумент `$params` имел 
     * следующие ключи, например:
     * ```php
     * [
     *     'use'         => BACKEND, // назначение компонента: FRONTEND, BACKEND
     *     'route'       => 'foo/bar', // маршрут (для формирования URL-адреса вызова)
     *     'hasSettings' => false, // имеет контроллер настроек (настройка объекта)
     *     'hasInfo'     => true, // имеет контроллер информации (информация о компоненте)
     *     'path'        => '/rg/rg.foobar', // каталог
     * ]
     * ```
     * 
     * @param array{
     *     hasInfo: bool, 
     *     hasSettings: bool, 
     *     path: string, 
     *     route: string, 
     *     baseRoute: string
     * } $params Параметры компонента.
     * @param bool|array{
     *     version: bool, 
     *     config: bool,
     *     install: bool,
     *     icon: bool
     * } $include Информация, которую включает компонент. 
     *     Где ключи:
     *     - 'version', файл конфигурации версии компонента;
     *     - 'config', файл конфигурации компонента;
     *     - 'install', файл конфигурации установки компонента;
     *     - 'icon', значки компонента.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     * @param string $name Имя файла конфигурации: 'name', 'extension' (по умолчанию 'module').
     * 
     * @return null|array Возвращает полученную информацию о компоненте, дополняя 
     *     аргумент `$params` следующими ключами:
     *     - 'infoUrl', маршрут получения информации о компоненте (если 
     *     аргумент `$params` имеет ключ 'hasInfo');
     *     - 'settingsUrl', маршрут получения настроек компонента (если 
     *     аргумент `$params` имеет ключ 'hasSettings');
     *     - 'version', параметры конфигурации версии компонента (если 
     *     аргумент `$include` имеет ключ 'version');
     *     - 'config', параметры конфигурации компонента (если 
     *     аргумент `$include` имеет ключ 'config');
     *     - 'install', параметры конфигурации установки компонента (если 
     *     аргумент `$include` имеет ключ 'install');
     *     - 'smallIcon', 'icon', 'watermark' {@see BaseManager::getIcon()}.
     *     Если значение `null`, то невозможно получить информацию.
     */
    public function getInfo(array $params, bool|array $include, string $name = 'module'): ?array
    {
        if (empty($params['path'])) {
            return null;
        }

        if (is_array($include)) {
            $incVersion = $include['version'] ?? false;
            $incConfig  = $include['config'] ?? false;
            $incInstall = $include['install'] ?? false;
            $incIcon    = $include['icon'] ?? false;
        } else {
            $incVersion = $incConfig = $incInstall = $incIcon = $include;
        }

        if ($name === 'module')
            $route = $params['route'] ?? '';
        else
            // только расширение имеет параметр (module/extension)
            $route = $params['baseRoute'] ?? '';

        // есть ли есть контроллер информации о модуле
        if (isset($params['hasInfo']) && $params['hasInfo']) {
            $params['infoRoute'] = $route . '/info';
            $params['infoUrl']   = '@backend/' . $route . '/info';
        }
        // есть ли есть контроллер настроек модуля
        if (isset($params['hasSettings']) && $params['hasSettings']) {
            $params['settingsRoute'] = $route . '/settings/view';
            /*if ($params['use'] === FRONTEND)
                $params['settingsUrl'] = $route . '/settings/view';
            else*/
            $params['settingsUrl'] = '@backend/' . $route . '/settings/view';
        }
        if ($incIcon) {
            $icon = $this->getIcon($params['path']);
            $params['smallIcon'] = $icon['small'];
            $params['icon']      = $icon['icon'];
            $params['watermark'] = $icon['watermark'];
        }
        if ($incVersion) {
            $params['version'] = $this->getConfigVersion($params['path'], true, true);
        }
        if ($incConfig) {
            $params['config'] = $this->getConfigFile($params['path'], $name, true);
        }
        if ($incInstall) {
            $params['install'] = $this->getConfigInstall($params['path'], true, true);
        }
        return $params;
    }

    /**
     * Возвращает репозиторий компонентов.
     * 
     * @return BaseRepository
     */
    public function getRepository(): BaseRepository
    {
        return $this->repository;
    }

    /**
     * Возвращает реестр установленных компонентов.
     * 
     * @return BaseRegistry
     */
    public function getRegistry(): BaseRegistry
    {
        return $this->registry;
    }

    /**
     * Возвращает параметры установленного компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.foobar'.
     * @param mixed $default Значение по умолчанию если параметры установленного 
     *     компоннента не найдены (по умолчанию `null`).

     * @return mixed
     */
    public function getRegistryParams(string|int $id, mixed $default = null): mixed
    {
        return $this->getRegistry()->getAt($id, null, $default);
    }

    /**
     * Установщики компонентов.
     * 
     * @see BaseManager::getInstaller()
     *
     * @var array<string, BaseObject>
     */
    protected $installers = [];

    /**
     * Возвращает установщик компонентов.
     * 
     * Конфигурация $config установщика должна обязательно иметь такие параметры
     * как:
     * - 'namespace', пространство имён устанавливаемого компонента, например 'Rg\FooBar';
     * - 'path', локальный путь к компоненту, например '/rg/rg.foobar'.
     * Эти параметры добавляются в загрузчик классов {@see \Ge\Ge::$loader}.
     * 
     * @param array{namespace:string, path:string} $config Параметры конфигурации 
     *     установщика в виде пар "ключ - значение", которые будут использоваться 
     *     для его инициализации.

     * @return BaseObject
     * 
     * @throws InvalidArgumentException Отсутствуют параметры конфигурации: namespace, path.
     */
    public function getInstaller(array $config): BaseObject
    {
        /** @var BaseObject|null Установщик компонента */
        $installer = null;

        if (empty($config['namespace']) || empty($config['path'])) {
            // Конфигурация должна иметь параметры "namespace", "path".
            throw new InvalidArgumentException('The configuration must have parameters "namespace", "path".');
        }

        // пространству имён объекта
        $namespace = $config['namespace'];
        // абсолютный путь к объекту
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

        // если установщик не создан
        if ($installer === null) {
            throw new CreateObjectException(
                sprintf('Unable to create installer object "%s"', $namespace . NS . 'Installer\Installer')
            );
        }

        if ($installer) {
            $this->installers[$namespace] = $installer;
        }
        return $installer;
    }

    /**
     * Проверяет, существует ли путь к компоненту.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * 
     * @return bool Возвращает значение `true`, если путь к компоненту существует, 
     *     `false` в противном случае.
     */
    public function pathExists(string $path): bool
    {
        return file_exists(Ge::$app->modulePath . $path);
    }

    /**
     * Проверяет существование указанного файла в каталоге компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.foobar', а имя файла 
     *    '/assets/css/foobar.css', то будет проверен файл '../rg/rg.foobar/assets/css/foobar.css'.
     * 
     * @return bool Возвращает значение `true`, если указанный файл существует, 
     *     `false` в противном случае. 
     */
    public function fileExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . $name);
    }

    /**
     * Проверяет существование указанного файла в каталоге "src" компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $name Короткое имя файла (без расширения '.php').  
     *     Например, если указан локальный путь '/rg/foo-bar, а имя файла:
     *     - 'Controller/Sample', будет проверен файл '.../rg/rg.foobar/src/Controller/Sample.php';
     *     - 'Sample', будет проверен файл '.../rg/rg.foobar/src/Sample.php';
     * 
     * @return bool Возвращает значение `true`, если указанный файл существует, 
     *     `false` в противном случае. 
     */
    public function sourceExists(string $path, string $name): bool
    {
        return file_exists(Ge::$app->modulePath . $path . DS . 'src' . DS . $name . '.php');
    }

    /**
     * Проверяет существование указанного файла контроллера в каталоге компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $name Имя контроллера. Например, если указан локальный путь 
     *     '/rg/rg.foobar', то имя файла:
     *     - 'FooBar', будет проверен файл '.../rg/rg.foobar/src/Controller/FooBar.php';
     *     - 'Foo/Bar', будет проверен файл '.../rg/rg.foobar/src/Controller/Foo/Bar.php';
     * 
     * @return bool Возвращает значение `true`, если указанный файл существует, 
     *     `false` в противном случае. 
     */
    public function controllerExists(string $path, string $name): bool
    {
        return $this->sourceExists($path, 'Controller' . DS . $name);
    }

    /**
     * Проверяет существование указанного файла модели данных в каталоге компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $name Имя модели данных. Например, если указан локальный путь 
     *     '/rg/rg.foobar', то имя файла:
     *     - 'FooBar', будет проверен файл '.../rg/rg.foobar/src/Model/FooBar.php';
     *     - 'Foo/Bar', будет проверен файл '.../rg/rg.foobar/src/Model/Foo/Bar.php';
     * 
     * @return bool Возвращает значение `true`, если указанный файл существует, 
     *     `false` в противном случае. 
     */
    public function modelExists(string $path, string $name): bool
    {
        return $this->sourceExists($path, 'Model' . DS . $name);
    }

    /**
     * Проверяет существование файла установщика в каталоге компонента.
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * 
     * @return bool Возвращает значение `true`, если указанный файл существует, 
     *     `false` в противном случае. 
     */
    public function installerExists(string $path): bool
    {
        return $this->sourceExists($path, 'Installer' . DS . 'Installer');
    }

    /**
     * Создаёт установщик компонента.
     * 
     * @param string $namespace Пространство имён компонента, например '\Rg\FooBar'.
     * @param array $params Параметры конфигурации установщика передаются ему 
     *     в конструктор класса (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает установщик, `null` в противном случае.
     */
    public function createInstaller(string $namespace, array $params = []): ?BaseObject
    {
        $class = $namespace . NS . 'Installer' . NS . 'Installer';
        if (!class_exists($class)) {
            return null;
        }
        return Ge::createObject($class, $params);
    }

    /**
     * Возвращает атрибуты компонента из базы данных по указанному идентификатору.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.foobar'.
     * @param bool $assoc Если значение `true`, то возвратит ассоциативный массив 
     *     атрибутов. Иначе активная запись (по умолчанию `false`).
     * 
     * @return ActiveRecord|array<string, mixed>|null Если значение `null`, то запись 
     *     по указанному идентификатору отсутствует.
     */
    public function selectOne(string|int $id, bool $assoc = false): ActiveRecord|array|null
    {
        return null;
    }

    /**
     * Возвращает атрибуты всех компонентов из базы данных.
     * 
     * @param string|null $key Имя ключа (атрибута) возвращаемой записи (по умолчанию `null`).
     * @param string|array<string, string> $where Условие выполнения запроса (по умолчанию `null`).
     * 
     * @return array
     */
    public function selectAll(?string $key = null, string|array $where = ''): array
    {
        return [];
    }

    /**
     * Возвращает атрибуты локализации компонента.
     * 
     * Результат имеет вид: 
     * ```php
     * [
     *     'name'        => 'Имя', // имя компонента
     *     'description' => 'Описание', // описание компонента
     *     'permissions' => '{...}' // разрешения компонента
     * ]
     * ```
     * 
     * @param int $id Идентификатор компонента в базе данных.
     * 
     * @return null|array{name:string, description:string, permissions:string}
     */
    public function selectName(int $id): ?array
    {
        return null;
    }

    /**
     * Возвращает атрибуты локализации компонентов.
     * 
     * Результат имеет вид: 
     * ```php
     * [
     *     'rowId1' => ['name' => 'Name1', 'description' => 'Description1'],
     *     'rowId2' => ['name' => 'Name2', 'description' => 'Description2'],
     *     // ...
     * ]
     * ```
     * @param string $attribute Название атрибута ('name', 'description', 'permissions') 
     *     возвращаемого для каждого идентификатора компонента. Если значение `null`, возвратит
     *     все атрибуты (по умолчанию `null`).
     * @param int $languageCode Идентификатор языка. Если значение `null`, то идентификатор 
     *     текущего языка (по умолчанию `null`).
     * 
     * @return array<int, array{name:string, description:string}>
     */
    public function selectNames(string $attribute = null, int $languageCode = null): ?array
    {
        return null;
    }

    /**
     * Ообновляет реестр установленных компонентов.
     * 
     * @return void
     */
    public function update(): void
    {
        $this->getRegistry()->update();
    }

    /**
     * Расшифровует идентификатор установки компонента.
     * 
     * Идентификатор установки используется для идентификации компоннета в процессе 
     * установки.
     * 
     * @see \Ge\Encryption\Encrypter::decryptString()
     * 
     * @param null|string $id Идентификатор установки. Зашифрованные параметры компоннета:
     *     локальный путь и пространство имён.
     * 
     * @return string|array{path:string, namespace:string} Возвращает значение `string`, 
     *     если возникла ошибка при расшифровке параметра. Иначе, массив: 
     *     `['path' => '/rg/rg.foobar', 'namespace' => 'Ge\FooBar']`.
     */
    public function decryptInstallId($id)
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
     * Шифрует идентификатор установки компонента.
     * 
     * Идентификатор установки используется для идентификации компонента 
     * в процессе установки.
     * 
     * @see \Ge\Encryption\Encrypter::encryptString()
     * 
     * @param string $path Локальный путь к компоненту, например '/rg/rg.foobar'.
     * @param string $namespace Пространство имён компонента, например 'Ge\FooBar'.
     * 
     * @return string
     */
    public function encryptInstallId(string $path, string $namespace): string
    {
        return Ge::$app->encrypter->encryptString($path . ',' . $namespace);
    }

    /**
     * Выполняет запуск компонента (модуля, расширения модуля).
     * 
     * @see \Ge\Mvc\Module\BaseModule::run()
     * 
     * @param string $id Идентификатор компонента, например, 'rg.foobar'.
     * @param string $controller Имя контроллера {@see \Ge\Mvc\Module\BaseModule::controller()} (по умолчанию '').
     * @param string $action Действие контроллера {@see \Ge\Mvc\Controller\BaseController::action()} (по умолчанию '').
     * @param array $actionParams Параметры передаваемые в действие контроллера (по умолчанию `[]`).
     * @param array $params Параметры компонента передаваемые в его конструктор (по умолчанию `[]`).
     * 
     * @return void
     * 
     * @throws Exception\ModuleNotFoundException Компонент с указанным идентификатором не существует.
     * @throws ActionNotFoundException Действие контроллера не существует.
     */
    public function run(
        string $id, 
        string $controller = '', 
        string $action = '',  
        array $actionParams = [], 
        array $params = []): void
    {
        /** @var \Ge\Mvc\Module\BaseModule|null $module */
        $module = $this->get($id);
        if ($module) {
            $module
                ->controller($controller)
                    ->action($action, $actionParams);
            $module->run();
        }
    }
}
