<?php

/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ModuleManager;

use Ge;
use Ge\Helper\Url;
use Ge\Config\Config;
use Ge\Db\ActiveRecord;
use Ge\Stdlib\Collection;

/**
 * Базовый класс реестра установленных компонентов.
 * 
 * Каждый вид компонентов имеет свой реестр, среди них можно выделить:
 * - реестр установленных модулей;
 * - реестр установленных расширений (модулей);
 * - реестр установленных виджетов.
 * 
 * Реестр хранит только основные параметры компонентов, предназначенные для использования 
 * менеджером компонентов. Реестр может находится одновременно в базе данных и в файле реестра, 
 * что позволяет не использовать базу данных при ёё отсутствии.
 * 
 * Реестр в базе данных применяется только для установления прав доступа ролей пользователей 
 * к установленным компонентам и изменению их основных параметров.
 * Изменение параметров компонента в реестре (базы данных) приводит к синхронизации с 
 * файлом реестра компонентов.
 * 
 * Пример реестра установленных компонентов:
 * ```php
 * [
 *     'rg.foobar' => [
 *          'id'          => 'rg.foobar', // универсальный идентификатор компонента в приложении
 *          'rowId'       => '1', //  универсальный идентификатор компонента в базе данных
 *          'enabled'     => true, // доступность (обращение к компоненту через URL)
 *          'hasSettings' => false, // компонент имеет настройки
 *          'hasInfo'     => true, // компонент имеет информацию
 *          'route'       => 'foo/bar', // маршрут (для формирования URL-адреса вызова)
 *          'namespace'   => 'Rg\FooBar', // пространство имени
 *          'path'        => '/rg/rg.foobar', // каталог
 *          'name'        => 'Foo bar', // имя по умолчанию (если отсутствует необходимая локализация)
 *          'description' => 'Foo bar Sample', // описание по умолчанию (если отсутствует необходимая локализация),
 *          'version'     => '1.0.0' // версия компонента
 *     ],
 *     // ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class BaseRegistry extends Config
{
    /**
     * Менеджер компонентов.
     *
     * @var BaseManager|null
     */
    public ?BaseManager $manager = null;

    /**
     * {@inheritdoc}
     * 
     * @param null|BaseManager $manager Менеджер компонентов.
     */
    public function __construct(?string $filename = null, bool $useSerialize = false, ?BaseManager $manager = null)
    {
        parent::__construct($filename, $useSerialize);

        $this->manager = $manager;
    }

    /**
     * Реестр установленных компонентов был сериализован в файле конфигурации с расширением ".so.php".
     *
     * @return bool
     */
    public function hasUpdated(): bool
    {
        return $this->existsSerializer();
    }

    /**
     * Добавляет компонент в базу данных.
     * 
     * @param array $params Параметры компонента.
     * @param bool $updateAfter Если значение `true`, обновит {@see BaseRegistry::update()} 
     *     файлы конфигураций приложения (по умолчанию `false`).
     * 
     * @return bool Если возвращает значение `true`, то компонент установлен.
     */
    public function add(array $params, bool $updateAfter = false): bool
    {
        return false;
    }

    /**
     * Устанавливает компоненту параметры в реестре.
     * 
     * @param array|string $id Идентификатор компонента (например: 'rg.foobar') или его 
     *     параметры (если аргумент `params` имеет значение `null`).
     * @param array $params Параметры конфигурации (по умолчанию `null`).
     * @param bool $updateAfter Если значение `true`, изменения сохранены успешно
     *     и будет выполнено обновление {@see BaseRegistry::update()} (по умолчанию `false`).
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
     * Удаляет компонент из базы данных.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе 
     *     данных, например: 123, 'rg.foobar'.
     * @param bool $updateAfter Если значение `true`, компонент удалён и выполнит 
     *     обновление {@see BaseRegistry::update()} (по умолчанию `false`).
     * 
     * @return $this
     */
    public function remove(mixed $id, bool $updateAfter = false): static
    {
        /** @var ActiveRecord|null $component */
        $component = $this->manager->selectOne($id, false);
        if ($component) {
            $component->delete();
            if ($updateAfter) {
                $this->update();
            }
        }
        return $this;
    }

    /**
     * Возвращает значение параметра (параметров) указанного компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры компонента.
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
     * Возвращает значение параметра (параметров) указанного компонента.
     * 
     * Идентификатор компонента - идентификатор записи компонента в базе данных.
     * 
     * @param int $id Идентификатор записи компонента в базе данных.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры компонента.
     * @param mixed $default Значение, если имя параметра отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getAtMap(int $id, ?string $parameter = null, mixed $default = null): mixed
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
     * @see BaseRegistry::getMap()
     * 
     * @var array
     */
    protected array $map;

    /**
     * Возвращает карту идентификаторов компонентов в виде пар "идентификатор - конфигурация".
     *
     * @see BaseRegistry::createMap()
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
     * Создаёт карту идентификаторов (записей) компонентов.
     * 
     * Каждый идентификатор (записи) компонента выступает ключём для его параметров.
     * 
     * Результат: 
     * ```php
     * [
     *     1 => ['id' => 'rg.foobar',],
     *     // ...
     * ]
     * ```
     * вместо:
     * ```php
     * [
     *     'rg.foobar' => ['rowId' => 1,],
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
     * Возвращает номер версии компонента.
     * 
     * @param string $id Идентификатор компонента в реестре, например 'rg.foobar'.
     * @param mixed $default Значение, если версия компонента отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getVersion(string $id, mixed $default = null): mixed
    {
        return $this->container[$id]['version'] ?? $default;
    }

    /**
     * Возвращает параметры из файла конфигурации компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string $name Название файла конфигурации, например: 'module', 'install'.
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
     * Возвращает параметры из файла конфигурации версии компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации установки компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации настроек компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации самого расширения.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigExtension($id, bool $associative = true): Collection|array|null 
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, 'extension', $associative) : null;
    }

    /**
     * Возвращает путь к компоненту.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $basePath Если значение `true`, возвратит абсолютный путь к компоненту. 
     *     Иначе, локальный (по умолчанию `false`).
     * 
     * @return string|null Возвращает значение `null`, если компонент с указанным 
     *     идентификатор не установлен. Иначе, путь к компоненту.
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
     * Проверяет, существует ли каталог компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * 
     * @return bool Возвращает значение `true`, если каталог компонента, `false` в 
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
     * Проверяет, существует ли указанный файл конфигурации установленного компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
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
     * Проверяет существование указанного файла в каталоге компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.foobar', а имя файла 
     *    '/assets/css/foobar.css', то будет проверен файл '../rg/rg.foobar/assets/css/foobar.css'.
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
     * Проверяет существование указанного файла в каталоге "src" компонента.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string $name Короткое имя файла (без расширения '.php').  
     *     Например, если указан локальный путь '/rg/rg.foobar', а имя файла:
     *     - 'Controller/Sample', будет проверен файл '.../rg/rg.foobar/src/Controller/Sample.php';
     *     - 'Sample', будет проверен файл '.../rg/rg.foobar/src/Sample.php';
     * 
     * @return bool Возвращает значение `true`, если короткое имя файла существует, 
     *     иначе возвращает `false`.  
     */
    public function sourceExists(string|int $id, string $name): bool
    {
        $path = $this->getAt($id, 'path');
        if ($path) {
            return file_exists(Ge::$app->modulePath . $path . DS . 'src' . DS . $name . '.php');
        }
        return false;
    }

    /**
     * Проверяет, доступен ли компонент.
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * 
     * @return bool Возвращает значение `false`, если компонент не доступен или не установлен.
     */
    public function isEnabled(string|int $id): bool
    {
        $enabled = $this->getAt($id, 'enabled');
        return (bool) $enabled;
    }

    /**
     * Возвращает URL-адрес значка компонента.
     * 
     * @param string|int|array{path:string} $id Идентификатор компонента в реестре, 
     *     в базе данных или его параметры, например: 123, 'rg.foobar', 
     *     ['path' => '/rg/rg.foobar'].
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
    public function getIcon(array|string|int $id, ?string $type = null): string|array
    {
        $prefix = 'module';
        // URL-путь к значкам по умолчанию
        $iconNoneUrl = Url::theme() . '/widgets/images/module';
        // URL большого и маленького значка по умолчанию
        $iconNoneSmall = $iconNoneUrl . '/' . $prefix . '-none_small.svg';
        $iconNone      = $iconNoneUrl . '/' . $prefix . '-none.svg';

        // параметры конфигурации установленного модуля
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            if ($type === 'small') {
                return $iconNoneSmall;
            } else
            if ($type === 'icon' || $type === 'watermark') {
                return $iconNone;
            }
            return '';
        }
        return $this->manager->getIcon($params['path'], $type, 'module');
    }

    /**
     * Возвращает информацию о компоненте.
     * 
     * @see BaseManager::getInfo()
     * 
     * @param string|int|array{
     *     hasInfo: bool, 
     *     hasSettings: bool, 
     *     path: string, 
     *     route: string, 
     *     baseRoute: string
     * } $id Идентификатор компонента в реестре, в базе данных или его параметры, 
     *     например: 123, 'rg.foobar', `['path' => '/rg/rg.foobar', ...]`.
     * @param bool|array{
     *     version: bool, 
     *     config: bool,
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает компонент.
     *     Где ключи:
     *     - 'version', файл конфигурации версии компонента;
     *     - 'config', файл конфигурации компонента;
     *     - 'install', файл конфигурации установки компонента;
     *     - 'icon', значки компонента.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array|null
     */
    public function getInfo(array|string|int $id, bool|array $include = ['icon' => true]): ?array
    {
        /** @var array $params Параметры конфигурации установленного компонента */
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            return null;
        }
        return $this->manager->getInfo($params, $include);
    }

    /**
     * Возвращает имена и описание компонентов в текущей локализации.
     * 
     * @param bool $accessible Если значение `true`, то возвращается информация о 
     *     компонентах доступных для текущей роли пользователя (по умолчанию `false`).
     * 
     * @return array Возвращает массив с информацией о компонентах в текущей локализации.
     */
    public function getListNames(bool $accessible = false): array
    {
        return [];
    }

    /**
     * Возвращает информацию о компонентах.
     * 
     * @param bool $withNames Если значение `true`, то добавляется имя и описание 
     *     компонента в текущей локализации (по умолчанию `true`).
     * @param bool $accessible Если значение `true`, то возвращается информация о 
     *     компонентах доступных для текущей роли пользователя (по умолчанию `false`).
     * @param string $key Имя ключа возвращаемой информации:
     *     - 'rowId', идентификатор компонента в базе данных;
     *     - 'id', идентификатор компонента, например 'rg.foobar'.
     *     По умолчанию 'rowId'.
     * @param bool|array{
     *     version: bool, 
     *     config: bool,
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает компонент.
     *     Где ключи:
     *     - 'version', файл конфигурации версии компонента;
     *     - 'config', файл конфигурации компонента;
     *     - 'install', файл конфигурации установки компонента;
     *     - 'icon', значки компонента.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array Возвращает массив с информацией о установленных компонентах.
     */
    public function getListInfo(
        bool $withNames = true, 
        bool $accessible = false, 
        string $key = 'rowId', 
        bool|array $include = ['icon' => true]
    ): array
    {
        return [];
    }

    /**
     * Возвращает разрешения (права доступа) компонента для текущей или указанной локализации.
     * 
     * Пример:
     * ```php
     * [
     *     'any'  => ['Name', 'Description'],
     *     'read' => ['Name', 'Description'],
     *     // ...
     * ]
     * ```
     * 
     * @param string|int $id Идентификатор компонента в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string|null $locale Если необязательный аргумент `locale` имеет значение 
     *    `null`, то возвращается в текущей локализации, например: 'ru_RU', 'en_GB' 
     *    (по умолчанию `null`).
     * 
     * @return array<string, mixed>
     */
    public function getTranslatedPermissions(string|int $id, string $locale = null): array
    {
        return [];
    }

    /**
     * Выполняет локализацю указанных разрешений (права доступа).
     * 
     * @param string $permissions Разрешения (права доступа).
     * @param bool $toJson Если значение `true`, результатом будет JSON-формат (по 
     *     умолчанию `true`).
     * 
     * @return array|string
     */
    public function permissionsToTranslate(string $permissions, bool $toJson = true): array|string
    {
        $result = [];
        // исключить разрешения из локализации, т.к. локализация этих 
        // разрешений присутствует в пакете установленных языков
        $exclude = ['info', 'settings', 'recordRls', 'writeAudit', 'viewAudit'];

        $array = explode(',', $permissions);
        if ($array) {
            foreach ($array as $permission) {
                $permission = trim($permission);
                if (!in_array($permission, $exclude)) {
                    // имя и описание
                    $result[$permission] = [ucfirst($permission), ''];
                }
            }
        }
        if ($toJson)
            return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        else
            return $result;
    }

    /**
     * Выполняет синхронизацию реестра установленных компонентов в базе данных с файлом 
     * реестра.
     * 
     * @return void
     */
    public function update(): void
    {
    }
}
