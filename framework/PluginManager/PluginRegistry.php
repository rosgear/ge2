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
use Ge\Config\Config;
use Ge\Stdlib\Collection;
 
/**
 * Класс PluginInstalled предоставляет возможность установки и удаление плагина, 
 * и его конфигурации из базы данных.
 * 
 * Параметры конфигурации установленных плагинов находятся в директории ("./config") 
 * приложения, файл ".plugins" (".plugins.so").
 * 
 * Конфигурации установленных плагинов имеют сводные параметры полученные при установке и 
 * дают возможность обращаться к плагинам без обращения к базе данных.
 * 
 * Пример параметров конфигурации установленного плагина:
 * ```php
 * [
 *     'rg.plg.foobar' => [
 *          'id'          => 'rg.plg.foobar', // уникальный идентификатор плагина в приложении
 *          'ownerId'     => 'rg.be.foobar', // владелиц плагина
 *          'rowId'       => '1', //  уникальный идентификатор плагина в базе данных
 *          'enabled'     => true, // доступность (визуализация)
 *          'hasSettings' => false, // плагин имеет контроллер настроек (возможность настроить плагин)
 *          'namespace'   => 'Rg\Plugin\FooBar', // пространство имени
 *          'path'        => '/rg/rg.plg.foobar', // директория плагина
 *          'name'        => 'Foo', // имя плагина по умолчанию (если отсутствует необходимая локализация)
 *          'description' => 'Foo bar', // описание плагина по умолчанию (если отсутствует необходимая локализация),
 *          'version'     => '1.0.0' // версия плагина
 *     ],
 *     // ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\PluginManager
 * @since 2.0
 */
class PluginRegistry extends Config
{
    /**
     * Менеджер плагинов.
     *
     * @var PluginManager|null
     */
    public ?PluginManager $manager = null;

    /**
     * {@inheritdoc}
     * 
     * @param null|PluginManager $manager Менеджер плагинов.
     */
    public function __construct(?string $filename = null, bool $useSerialize = false, ?PluginManager $manager = null)
    {
        parent::__construct($filename, $useSerialize);

        $this->manager = $manager;
    }

    /**
     * Все установленные видежты были сериализованы в файл конфигурации с расширением ".so.php".
     *
     * @return bool
     */
    public function hasUpdated(): bool
    {
        return $this->existsSerializer();
    }

    /**
     * Добавляет плагин в базу данных.
     * 
     * @param array $params Параметры плагина.
     * @param bool $updateAfter Если значение `true`, обновит {@see PluginRegistry::update()} 
     *     файлы конфигураций приложения (по умолчанию `false`).
     * 
     * @return bool Если возвращает значение `true`, то плагин установлен.
     */
    public function add(array $params, bool $updateAfter = false): bool
    {
        $plugin = new Model\Plugin($params);
        $plugin->createdDate = date('Y-m-d H:i:s');
        $plugin->createdUser = Ge::$app->user->getId();
        $result = (bool) $plugin->insert(false);
        if ($result && $updateAfter) {
            $this->update();
        }
        return $result;
    }

    /**
     * Устанавливает плагину параметры конфигурации.
     * 
     * @param array|string $id Идентификатор плагина (например: 'rg.plg.foobar') или его 
     *     параметры (если аргумент `params` имеет значение `null`).
     * @param array $params Параметры конфигурации (по умолчанию `null`).
     * @param bool $updateAfter Если значение `true`, изменения сохранены успешно
     *     и будет выполнено обновление {@see PluginRegistry::update()} (по умолчанию `false`).
     * 
     * @return $this
     */
    public function set(mixed $id, mixed $params = null, bool $updateAfter = false): static
    {
        if (is_array($id) && $params === null) {
            $this->container = $id;
        } else {
            $getParams = $this->get($id);
            if ($getParams === null) {
                return $this;
            }
            $this->container[$id] = array_merge($getParams, $params);
        }

        if ($updateAfter) {
            $this->save();
        }
        return $this;
    }

    /**
     * Удаляет плагин из базы данных.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $updateAfter Если значение `true`, компонент удалён и выполнит 
     *     обновление {@see PluginRegistry::update()} (по умолчанию `false`).
     * 
     * @return $this
     */
    public function remove(mixed $id, bool $updateAfter = false): static
    {
        /** @var Model\Plugin $plugin */
        $plugin = $this->manager->selectOne($id, false);
        if ($plugin) {
            $plugin->delete();
            if ($updateAfter) {
                $this->update();
            }
        }
        return $this;
    }

    /**
     * Возвращает значение параметра (параметров) указанного плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры плагина.
     * @param mixed $default Значение, если имя параметра отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getAt(string|int $id, ?string $parameter = null, mixed $default = null): mixed
    {
        if (is_numeric($id)) {
            return $this->getAtMap($id, $parameter, $default);
        }

        if ($parameter)
            return $this->container[$id][$parameter] ?? $default;
        else
            return $this->container[$id] ?? $default;
    }

    /**
     * Возвращает значение параметра (параметров) указанного плагина.
     * 
     * Идентификатор плагина - идентификатор записи плагина в базе данных.
     * 
     * @param int $id Идентификатор плагина в базе данных.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры плагина.
     * @param mixed $default Значение, если имя параметра отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getAtMap(int $id, ?string $parameter = null, mixed $default = null)
    {
        if (!isset($this->map)) {
            $this->createMap();
        }

        if ($parameter)
            return $this->map[$id][$parameter] ?? $default;
        else
            return $this->map[$id] ?? $default;
    }

    /**
     * Карта идентификаторов компонентов в виде пар "идентификатор - конфигурация".
     *
     * @see PluginRegistry::getMap()
     * 
     * @var array
     */
    protected array $map;

    /**
     * Возвращает карту идентификаторов плагинов в виде пар "идентификатор - конфигурация".
     *
     * @see PluginRegistry::createMap()
     * 
     * @var array
     */
    public function getMap(): array
    {
        if (!isset($this->map)) {
            $this->createMap();
        }
        return $this->map;
    }

    /**
     * Создаёт карту идентификаторов (записей) плагинов.
     * 
     * Каждый идентификатор (записи) плагина выступает ключём для параметров 
     * конфигурации установленного плагина.
     * 
     * Результат: 
     * ```php
     * [
     *     1 => ['id' => 'rg.plg.foobar',],
     *     // ...
     * ]
     * ```
     * вместо:
     * ```php
     * [
     *     'foobar' => ['rowId' => 1,],
     *     // ...
     * ]
     * ```
     * @return void
     */
    public function createMap(): void
    {
        $this->map = [];
        if ($this->container) {
            foreach ($this->container as $id => $options) {
                if (isset($options['rowId'])) {
                    $this->map[$options['rowId']] = $options;
                }
            }
        }
    }

    /**
     * Возвращает номер версии плагина.
     * 
     * @param string $id Идентификатор компонента в реестре, например 'rg.plg.foobar'.
     * @param mixed $default Значение, если версия плагина отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getVersion(string $id, mixed $default = null): mixed
    {
        return $this->container[$id]['version'] ?? $default;
    }

    /**
     * Возвращает параметры из файла конфигурации плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * @param string $name Название файла конфигурации, например: 'version', 'install'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigFile(string|int $id, string $name, bool $associative = true): Collection|array|null
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, $name, $associative) : null;
    }

    /**
     * Возвращает параметры из файла конфигурации версии плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * @param bool $usePattern Использовать шаблон параметров, только для ассоциативного 
     *     массива параметров (по умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigVersion(string|int $id, bool $associative = true, bool $usePattern = true): Collection|array|null 
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigVersion($path, $associative, $usePattern) : null;
    }

    /**
     * Возвращает параметры из файла конфигурации установки плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * @param bool $usePattern Использовать шаблон параметров, только для ассоциативного 
     *     массива параметров (по умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigInstall(string|int $id, bool $associative = true, bool $usePattern = true): Collection|array|null 
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigInstall($path, $associative, $usePattern) : null;
    }

    /**
     * Возвращает параметры из файла конфигурации настроек плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigSettings(string|int $id, bool $associative = true): Collection|array|null 
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, 'settings', $associative) : null;
    }

    /**
     * Возвращает параметры из файла конфигурации плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigPlugin(string|int $id, bool $associative = true)
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, 'plugin', $associative) : null;
    }

    /**
     * Возвращает путь к плагину.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * @param bool $basePath Если значение `true`, возвратит абсолютный путь к плагину. 
     *     Иначе, локальный (по умолчанию `false`).
     * 
     * @return string|null Возвращает значение `null`, если плагин с указанным 
     *     идентификатор не установлен. Иначе, путь к плагину.
     */
    public function getPath(string|int $id, bool $basePath = false): ?string
    {
        $path = $this->getAt($id, 'path');
        if ($path) {
            return $basePath ? Ge::$app->modulePath . $path : $path;
        }
        return null;
    }

    /**
     * Проверяет, существует ли каталог плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * 
     * @return bool Возвращает значение `true`, если каталог плагина, `false` в 
     *     противном случае.
     */
    public function pathExists(string|int $id): bool
    {
        $path = $this->getAt($id, 'path');
        if ($path) {
            return file_exists(Ge::$app->modulePath . $path);
        }
        return false;
    }

    /**
     * Проверяет, существует ли указанный файл конфигурации установленного плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * @param string $name Имя файла конфигурации, например: 'module', 'install'.
     * 
     * @return bool Возвращает значение `true`, если имя файла конфигурации существует, 
     *     `false` в противном случае.
     */
    public function configExists(string|int $id, string $name): bool
    {
        $path = $this->getAt($id, 'path');
        if ($path) {
            return file_exists(Ge::$app->modulePath . $path . DS . 'config' . DS . '.' . $name . '.php');
        }
        return false;
    }

    /**
     * Проверяет существование указанного файла в каталоге плагина.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.plg.foobar', а имя файла 
     *    '/assets/css/foobar.css', то будет проверен файл '../rg/rg.plg.foobar/assets/css/foobar.css'.
     * 
     * @return bool Возвращает значение `true`, если имя файл существует, иначе 
     *     возвращает `false`. 
     */
    public function fileExists(string|int $id, string $name): bool
    {
        $path = $this->getAt($id, 'path');
        if ($path) {
            return file_exists(Ge::$app->modulePath . $path . $name);
        }
        return false;
    }

    /**
     * Проверяет, доступен ли плагин.
     * 
     * @param string|int $id Идентификатор плагина в реестре или в базе данных, 
     *     например: 123, 'rg.plg.foobar'.
     * 
     * @return bool Возвращает значение `false`, если плагин не доступен или не установлен.
     */
    public function isEnabled(string|int $id): bool
    {
        $enabled = $this->getAt($id, 'enabled');
        return (bool) $enabled;
    }

    /**
     * Возвращает URL-адрес значка плагина.
     * 
     * @param string|int|array{path:string} $id Идентификатор плагина в реестре, 
     *     в базе данных или его параметры, например: 123, 'rg.foobar', 
     *     ['path' => '/rg/rg.plg.foobar'].
     * @param null|string $type Тип возвращаемого значка:  
     *     - 'small', минимальный размер 16x16 пкс.;
     *     - 'icon', минимальный размер 32x32 пкс.;
     *     - 'watermark', значок с прозрачностью и минимальным размером 32x32 пкс.
     *     Если значение `null`, результат будет иметь вид: 
     *     `['icon' => '...', 'small' => '...', 'watermark' => '...']`. 
     *     По умолчанию `null`.
     * 
     * @return string|array
     */
    public function getIcon($id, ?string $type = null): string|array
    {
        // параметры конфигурации установленного плагина
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            // URL-путь к значкам по умолчанию
            $iconNoneUrl = Url::theme() . '/widgets/images/module';
            // URL большого и маленького значка по умолчанию
            $iconNoneSmall = $iconNoneUrl . '/plugin-none_small.svg';
            $iconNone      = $iconNoneUrl . '/plugin-none.svg';

            if ($type === 'small') {
                return $iconNoneSmall;
            } else
            if ($type === 'icon') {
                return $iconNone;
            }
            return '';
        }
        return $this->manager->getIcon($params['path'], $type);
    }

    /**
     * Возвращает информацию о плагине.
     * 
     * @param string|int|array{path:string} $id Идентификатор плагина в реестре, в 
     *     базе данных или его параметры, например: 123, 'rg.plg.foobar', `['path' => '/rg/rg.plg.foobar', ...]`.
     * @param bool|array{
     *     version: bool, 
     *     config: bool,
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает компонент.
     *     Где ключи:
     *     - 'version', файл конфигурации версии компонента;
     *     - 'install', файл конфигурации установки компонента;
     *     - 'icon', значки плагина.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array|null
     */
    public function getInfo(string|int|array $id, array|bool $include = ['icon' => true]): ?array
    {
        /** @var array $params Параметры конфигурации установленного плагина */
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            return null;
        }
        return $this->manager->getInfo($params, $include);
    }

    /**
     * Возвращает имена и описание плагинов в текущей локализации.
     * 
     * @param bool $accessible Если значение `true`, то возвращается информация о 
     *     компонентах доступных для текущей роли пользователя (по умолчанию `false`).
     * 
     * @return array Возвращает массив с информацией о компонентах в текущей локализации.
     */
    public function getListNames(bool $accessible = false): array
    {
        $result = [];

        /**
         * @var array $names Имена плагинов с текущей локализацией. 
         * Имеют вид: `[id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
         */
        $names = $this->manager->selectNames();

        /**
         * @var array $names Параметры конфигурации установленных плагинов.
         * Имеют вид: `[id => [...], ...]`.
         */
        $map = $this->getMap();

        // выбираем отсортированные по имени плагин
        foreach ($names as $rowId => $localization) {
            // в том случаи если плагин удалён а его локализации нет
            if (!isset($map[$rowId])) continue;

            $result[$rowId] = [
                'name'        => $localization['name'],
                'description' => $localization['description']
            ];
        }
        return $result;
    }

    /**
     * Возвращает информацию о плагинах.
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
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array Возвращает массив с информацией о установленных плагинах.
     */
    public function getListInfo(
        bool $withNames = true, 
        ?string $ownerId = null,
        string $key = 'rowId', 
        bool|array $include = ['icon' => true]
    ): array
    {
        $result = [];

        // если с локализацией имён плагинов
        if ($withNames) {
            /**
             * @var array $names Имена плагинов с текущей локализацией. 
             * Имеют вид: `[id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
             */
            $names = $this->manager->selectNames();

            /**
             * @var array $names Параметры конфигурации установленных плагинов.
             * Имеют вид: `[id => [...], ...]`.
             */
            $map = $this->getMap();

            // выбираем отсортированные по имени плагины, 
            // где $rowId идентификатор плагина в базе данных (1, 2, 3, ...)
            foreach ($names as $rowId => $localization) {
                // в том случаи если плагин удалён а его локализация нет
                if (!isset($map[$rowId])) continue;
                if ($ownerId && $map[$rowId]['ownerId'] !== $ownerId) continue;

                $info = $this->getInfo($map[$rowId], $include);
                $info['name'] = $localization['name'];
                $info['description'] = $localization['description'];
                $result[$info[$key]] = $info;
            }
        // без локализации имён плагинов
        } else {
            // где $pluginId идентификатор плагина ('foobar'),
            // $rowId идентификатор плагина в базе данных (1, 2, 3, ...)
            foreach ($this->container as $pluginId => $configParams) {
                if ($ownerId && $configParams['ownerId'] !== $ownerId) continue;

                $info   = $this->getInfo($pluginId, $include);
                $rowId = $configParams['rowId'];
                $result[$configParams[$key]] = $info;
            }
        }
        return $result;
    }

    /**
     * Обновляет конфигурацию установленных плагинов.
     * 
     * Ообновляет файлы конфигурации приложения: 
     * - плагины ".plugins.php" (.plugins.so.php);
     * - события ".events.php" (.events.so.php).
     *
     * @return void
     */
    public function update(): void
    {
        // все установленные плагины из базы данных
        $plugins = $this->manager->selectAll('pluginId');

        $this->updateRegistry($plugins);
        $this->updateLocales($plugins);
    }

    /**
     * Обновляет реестр плагинов.
     *
     * @param array $plugins Параметры плагинов.
     * 
     * @return void
     */
    public function updateRegistry(array $plugins): void
    {
        // конфигурации плагинов ".plugins.php"
        $config = [];
        foreach ($plugins as $pluginId => $attributes) {
            $config[$pluginId] = [
                'lock'        => (bool) $attributes['lock'],
                'id'          => $attributes['pluginId'],
                'ownerId'     => $attributes['ownerId'],
                'rowId'       => (int) $attributes['id'],
                'enabled'     => (bool) $attributes['enabled'],
                'hasSettings' => (bool) $attributes['hasSettings'],
                'path'        => $attributes['path'],
                'namespace'   => $attributes['namespace'],
                'name'        => $attributes['name'],
                'description' => $attributes['description'],
                'version'     => $attributes['version']
            ];
        }
        // обновление файла конфигурации плагинов
        $this->set($config);
        $this->save();
    }

    /**
     * Обновляет локализацию установленных плагинов в базе данных.
     * 
     * Модель данных {@see Model\PluginLocale} обновляет локализацию.
     * Данные локализации (название, описание и права доступа) плагина находятся в файлах 
     * локализации, таких как: 'text-ru_RU.php', 'text-en_GB.php' и.т.
     * 
     * Внимание: перед обновлением все локализаци установленных плагинов в базе данных
     * будут удалены.
     * 
     * @param array $plugins Параметры установленных плагинов.
     * 
     * @return void
     */
    public function updateLocales(array $plugins): void
    {
        /** @var Model\PluginLocale $pluginLocale */
        $pluginLocale = new Model\PluginLocale();
        // очищаем таблицу
        $pluginLocale->deleteAll();
        /** @var array $languages Установленные языки */
        $languages = Ge::$app->language->available->getAll();
        foreach ($plugins as $pluginId => $attributes) {
            // шаблон параметров источника (категории) транслятора плагина
            $translator = PluginManager::getTranslatePattern($attributes['path']);
            foreach ($languages as $locale => $language) {
                try {
                    // указываем переводчику использование локали $locale
                    $translator['locale'] = $locale;
                    // имя категории сообщений переводчика (в данном случаи для каждой локали плагина своя категория)
                    $category = $attributes['pluginId'] . '.' . $locale;
                    Ge::$app->translator->addCategory($category, $translator);
                    $name = Ge::t($category, '{name}');
                    // если названия нет для переводчика, тогда по умолчанию
                    if ($name === '{name}') {
                        $name = $attributes['name'];
                    }
                    $description = Ge::t($category, '{description}');
                    // если описания нет для переводчика, тогда по умолчанию
                    if ($description === '{description}') {
                        $description = $attributes['description'];
                    }
                    // добавляем данные локализации плагина
                    $pluginLocale->pluginId    = $attributes['id'];
                    $pluginLocale->languageId  = $language['code'];
                    $pluginLocale->name        = $name;
                    $pluginLocale->description = $description;
                    $pluginLocale->insert();
                // если файл локализации не найден
                } catch (\Ge\I18n\Exception\PatternNotLoadException $error) {
                    // добавляем данные локализации плагина
                    $pluginLocale->pluginId    = $attributes['id'];
                    $pluginLocale->languageId  = $language['code'];
                    $pluginLocale->name        = $attributes['name'];
                    $pluginLocale->description = $attributes['description'];
                    $pluginLocale->insert();
                    continue;
                }
            }
        }
    }
}
