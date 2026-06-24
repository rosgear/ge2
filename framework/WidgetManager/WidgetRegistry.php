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
use Ge\Config\Config;
use Ge\Stdlib\Collection;
 
/**
 * Класс WidgetInstalled предоставляет возможность установки и удаление виджета, 
 * и его конфигурации из базы данных.
 * 
 * Параметры конфигурации установленных виджетов находятся в директории ("./config") 
 * приложения, файл ".widgets" (".widgets.so").
 * 
 * Конфигурации установленных виджетов имеют сводные параметры полученные при установке и 
 * дают возможность обращаться к виджетам без обращения к базе данных.
 * 
 * Пример параметров конфигурации установленного виджета:
 * ```php
 * [
 *     'rg.wd.menu' => [
 *          'id'          => 'rg.wd.menu', // уникальный идентификатор виджета в приложении
 *          'rowId'       => '1', //  уникальный идентификатор виджета в базе данных
 *          'enabled'     => true, // доступность (визуализация)
 *          'hasSettings' => false, // виджет имеет контроллер настроек (возможность настроить виджет)
 *          'namespace'   => 'Rg\Widget\Menu', // пространство имени
 *          'path'        => '/rg/rg.wd.menu', // директория виджета
 *          'name'        => 'Menu', // имя виджета по умолчанию (если отсутствует необходимая локализация)
 *          'description' => 'Site menu', // описание виджета по умолчанию (если отсутствует необходимая локализация),
 *          'version'     => '1.0.0' // версия виджета
 *     ],
 *     // ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager
 * @since 2.0
 */
class WidgetRegistry extends Config
{
    /**
     * Менеджер виджетов.
     *
     * @var WidgetManager|null
     */
    public ?WidgetManager $manager = null;

    /**
     * {@inheritdoc}
     * 
     * @param null|WidgetManager $manager Менеджер виджетов.
     */
    public function __construct(?string $filename = null, bool $useSerialize = false, ?WidgetManager $manager = null)
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
     * Добавляет виджет в базу данных.
     * 
     * @param array $params Параметры виджета.
     * @param bool $updateAfter Если значение `true`, обновит {@see WidgetRegistry::update()} 
     *     файлы конфигураций приложения (по умолчанию `false`).
     * 
     * @return bool Если возвращает значение `true`, то виджет установлен.
     */
    public function add(array $params, bool $updateAfter = false): bool
    {
        $widget = new Model\Widget($params);
        $widget->createdDate = date('Y-m-d H:i:s');
        $widget->createdUser = Ge::$app->user->getId();
        $result = (bool) $widget->insert(false);
        if ($result && $updateAfter) {
            $this->update();
        }
        return $result;
    }

    /**
     * Устанавливает виджету параметры конфигурации.
     * 
     * @param array|string $id Идентификатор виджета (например: 'rg.foobar') или его 
     *     параметры (если аргумент `params` имеет значение `null`).
     * @param array $params Параметры конфигурации (по умолчанию `null`).
     * @param bool $updateAfter Если значение `true`, изменения сохранены успешно
     *     и будет выполнено обновление {@see WidgetRegistry::update()} (по умолчанию `false`).
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
     * Удаляет виджет из базы данных.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $updateAfter Если значение `true`, компонент удалён и выполнит 
     *     обновление {@see WidgetRegistry::update()} (по умолчанию `false`).
     * 
     * @return $this
     */
    public function remove(mixed $id, bool $updateAfter = false): static
    {
        /** @var Model\Widget $widget */
        $widget = $this->manager->selectOne($id, false);
        if ($widget) {
            $widget->delete();
            if ($updateAfter) {
                $this->update();
            }
        }
        return $this;
    }

    /**
     * Возвращает значение параметра (параметров) указанного виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры виджета.
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
     * Возвращает значение параметра (параметров) указанного виджета.
     * 
     * Идентификатор виджета - идентификатор записи виджета в базе данных.
     * 
     * @param int $id Идентификатор виджета в базе данных.
     * @param null|string $parameter Имя параметра. Если значение `null`, результатом 
     *     будут все параметры виджета.
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
     * @see WidgetRegistry::getMap()
     * 
     * @var array
     */
    protected array $map;

    /**
     * Возвращает карту идентификаторов виджетов в виде пар "идентификатор - конфигурация".
     *
     * @see WidgetRegistry::createMap()
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
     * Создаёт карту идентификаторов (записей) виджетов.
     * 
     * Каждый идентификатор (записи) виджета выступает ключём для параметров 
     * конфигурации установленного виджета.
     * 
     * Результат: 
     * ```php
     * [
     *     1 => ['id' => 'rg.wd.foobar',],
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
     * Возвращает номер версии виджета.
     * 
     * @param string $id Идентификатор компонента в реестре, например 'rg.wd.foobar'.
     * @param mixed $default Значение, если версия виджета отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getVersion(string $id, mixed $default = null): mixed
    {
        return $this->container[$id]['version'] ?? $default;
    }

    /**
     * Возвращает параметры из файла конфигурации виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.wd.foobar'.
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
     * Возвращает параметры из файла конфигурации версии виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации установки виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации настроек виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
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
     * Возвращает параметры из файла конфигурации виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.wd.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigWidget(string|int $id, bool $associative = true)
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, 'widget', $associative) : null;
    }

    /**
     * Возвращает путь к виджету.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $basePath Если значение `true`, возвратит абсолютный путь к виджету. 
     *     Иначе, локальный (по умолчанию `false`).
     * 
     * @return string|null Возвращает значение `null`, если виджет с указанным 
     *     идентификатор не установлен. Иначе, путь к виджету.
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
     * Проверяет, существует ли каталог виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.wd.foobar'.
     * 
     * @return bool Возвращает значение `true`, если каталог виджета, `false` в 
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
     * Проверяет, существует ли указанный файл конфигурации установленного виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
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
     * Проверяет существование указанного файла в каталоге виджета.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param string $name Имя файла (может включать путь).  
     *     Например, если указан локальный путь '/rg/rg.wd.foobar', а имя файла 
     *    '/assets/css/foobar.css', то будет проверен файл '../rg/rg.wd.foobar/assets/css/foobar.css'.
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
     * Проверяет, доступен ли виджет.
     * 
     * @param string|int $id Идентификатор виджета в реестре или в базе данных, 
     *     например: 123, 'rg.wd.foobar'.
     * 
     * @return bool Возвращает значение `false`, если виджет не доступен или не установлен.
     */
    public function isEnabled(string|int $id): bool
    {
        $enabled = $this->getAt($id, 'enabled');
        return (bool) $enabled;
    }

    /**
     * Возвращает URL-адрес значка виджета.
     * 
     * @param string|int|array{path:string} $id Идентификатор виджета в реестре, 
     *     в базе данных или его параметры, например: 123, 'rg.foobar', 
     *     ['path' => '/rg/rg.wd.foobar'].
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
        // параметры конфигурации установленного виджета
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            // URL-путь к значкам по умолчанию
            $iconNoneUrl = Url::theme() . '/widgets/images/module';
            // URL большого и маленького значка по умолчанию
            $iconNoneSmall = $iconNoneUrl . '/widget-none_small.svg';
            $iconNone      = $iconNoneUrl . '/widget-none.svg';

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
     * Возвращает информацию о виджете.
     * 
     * @param string|int|array{path:string} $id Идентификатор виджета в реестре, в 
     *     базе данных или его параметры, например: 123, 'rg.foobar', `['path' => '/rg/rg.foobar', ...]`.
     * @param bool|array{
     *     version: bool, 
     *     config: bool,
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает компонент.
     *     Где ключи:
     *     - 'version', файл конфигурации версии компонента;
     *     - 'install', файл конфигурации установки компонента;
     *     - 'icon', значки виджета.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array|null
     */
    public function getInfo(string|int|array $id, array|bool $include = ['icon' => true]): ?array
    {
        /** @var array $params Параметры конфигурации установленного виджета */
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            return null;
        }
        return $this->manager->getInfo($params, $include);
    }

    /**
     * Возвращает имена и описание виджетов в текущей локализации.
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
         * @var array $names Имена виджетов с текущей локализацией. 
         * Имеют вид: `[id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
         */
        $names = $this->manager->selectNames();

        /**
         * @var array $names Параметры конфигурации установленных виджетов.
         * Имеют вид: `[id => [...], ...]`.
         */
        $map = $this->getMap();

        // выбираем отсортированные по имени виджет
        foreach ($names as $rowId => $localization) {
            // в том случаи если виджет удалён а его локализации нет
            if (!isset($map[$rowId])) continue;

            $result[$rowId] = [
                'name'        => $localization['name'],
                'description' => $localization['description']
            ];
        }
        return $result;
    }

    /**
     * Возвращает информацию о виджетах.
     * 
     * @param bool $withNames Если значение `true`, то добавляется имя и описание 
     *     виджета в текущей локализации (по умолчанию `true`).
     * @param bool $accessible Если значение `true`, то возвращается информация о 
     *     виджетах доступных для текущей роли пользователя (по умолчанию `false`).
     * @param string $key Имя ключа возвращаемой информации:
     *     - 'rowId', идентификатор виджета в базе данных;
     *     - 'id', идентификатор виджета, например 'rg.wd.foobar'.
     *     По умолчанию 'rowId'.
     * @param bool|array{
     *     version: bool, 
     *     install: bool,
     *     icon: bool
     * } $include Дополнительная информация, которую включает виджет.
     *     Где ключи:
     *     - 'version', файл конфигурации версии виджета;
     *     - 'install', файл конфигурации установки виджета;
     *     - 'icon', значки виджета.
     *     Если значение `true`, включает всё выше. Иначе, всё выше исключает.
     *     По умолчанию `['icon' => true]`.
     * 
     * @return array Возвращает массив с информацией о установленных виджетах.
     */
    public function getListInfo(
        bool $withNames = true, 
        bool $accessible = false, 
        string $key = 'rowId', 
        bool|array $include = ['icon' => true]
    ): array
    {
        $result = [];

        // если с локализацией имён виджетов
        if ($withNames) {
            /**
             * @var array $names Имена виджетов с текущей локализацией. 
             * Имеют вид: `[id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
             */
            $names = $this->manager->selectNames();

            /**
             * @var array $names Параметры конфигурации установленных виджетов.
             * Имеют вид: `[id => [...], ...]`.
             */
            $map = $this->getMap();

            // выбираем отсортированные по имени виджеты, 
            // где $rowId идентификатор виджета в базе данных (1, 2, 3, ...)
            foreach ($names as $rowId => $localization) {
                // в том случаи если виджет удалён а его локализация нет
                if (!isset($map[$rowId])) continue;

                $info = $this->getInfo($map[$rowId], $include);
                $info['name'] = $localization['name'];
                $info['description'] = $localization['description'];
                $result[$info[$key]] = $info;
            }
        // без локализации имён виджетов
        } else {
            // где $widgetId идентификатор виджета ('foobar'),
            // $rowId идентификатор виджета в базе данных (1, 2, 3, ...)
            foreach ($this->container as $widgetId => $configParams) {
                $info   = $this->getInfo($widgetId, $include);
                $rowId = $configParams['rowId'];
                // только доступные для роли пользователя
                if ($accessible && !isset($accessIds[$rowId])) {
                    continue;
                }
                $result[$configParams[$key]] = $info;
            }
        }
        return $result;
    }

    /**
     * Обновляет конфигурацию установленных виджетов.
     * 
     * Ообновляет файлы конфигурации приложения: 
     * - шорткоды ".shortcodes.php" (.shortcodes.so.php);
     * - виджеты ".widgets.php" (.widgets.so.php);
     * - события ".events.php" (.events.so.php).
     *
     * @return void
     */
    public function update(): void
    {
        // все установленные виджеты из базы данных
        $widgets = $this->manager->selectAll('widgetId');

        $this->updateRegistry($widgets);
        $this->updateShortcodes($widgets);
        $this->updateLocales($widgets);
        $this->updateEvents($widgets);
    }

    /**
     * Обновляет реестр виджетов.
     *
     * @param array $widgets Параметры виджетов.
     * 
     * @return void
     */
    public function updateRegistry(array $widgets): void
    {
        // конфигурации виджетов ".widgets.php"
        $config = [];
        foreach ($widgets as $widgetId => $attributes) {
            $config[$widgetId] = [
                'lock'        => (bool) $attributes['lock'],
                'id'          => $attributes['widgetId'],
                'use'         => $attributes['widgetUse'],
                'rowId'       => (int) $attributes['id'],
                'enabled'     => (bool) $attributes['enabled'],
                'hasSettings' => (bool) $attributes['hasSettings'],
                'path'        => $attributes['path'],
                'namespace'   => $attributes['namespace'],
                'category'    => $attributes['category'],
                'name'        => $attributes['name'],
                'description' => $attributes['description'],
                'version'     => $attributes['version']
            ];
        }
        // обновление файла конфигурации виджетов
        $this->set($config);
        $this->save();
    }

    /**
     * Обновляет конфигурацию событий виджетов.
     *
     * @param array $widgets Параметры виджетов.
     * 
     * @return void
     */
    public function updateEvents(array $widgets): void
    {
        // убираем все события виджетов
        Ge::$app->listeners->removeListeners('widget');

        foreach ($widgets as $widgetId => $attributes) {
            $installParams = $this->manager->getInfo($attributes, ['install' => true]);
            $events = $installParams['install']['events'] ?? [];
            if ($events) {
                Ge::$app->listeners->addListener($events, $widgetId, 'widget');
            }
        }
        Ge::$app->listeners->save();
    }

    /**
     * Обновляет файл конфигурации шорткодов ".shortcodes.php".
     * 
     * @param array<string, array<string, mixed>> $widgets Атрибуты виджетов.
     * 
     * @return void
     */
    public function updateShortcodes(array $widgets): void
    {
        $foundShortcodes = [];
        foreach ($widgets as $widgetId => $attributes) {
            // параметры файла конфигурации виджета ".install.php"
            $install = $this->manager->getConfigInstall($attributes['path'], false);
            if ($install === null) continue;

            if ($install->shortcodes) {
                foreach ($install->shortcodes as $index => $shortcode) {
                    // если указано как ['tag1', 'tag2'...]
                    if (is_numeric($index))
                        $foundShortcodes[$shortcode] = ['widget' => $install->id];
                    else {
                        $foundShortcodes[$index] = $shortcode;
                    }
                }
            }
        }

        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->shortcodes->config;
        // загружаем с базовой конфигурацией ".shortcodes.php"
        $config->reload(false);

        // раздел имён шорткодов
        $shortcodes = $config->get('shortcodes', []);
        $shortcodes = array_merge($shortcodes, $foundShortcodes);
        $config->set('shortcodes', $shortcodes);
        $config->save();
    }

    /**
     * Обновляет локализацию установленных виджетов в базе данных.
     * 
     * Модель данных {@see Model\WidgetLocale} обновляет локализацию.
     * Данные локализации (название, описание и права доступа) виджета находятся в файлах 
     * локализации, таких как: 'text-ru_RU.php', 'text-en_GB.php' и.т.
     * 
     * Внимание: перед обновлением все локализаци установленных виджетов в базе данных
     * будут удалены.
     * 
     * @param array $widgets Параметры установленных виджетов.
     * 
     * @return void
     */
    public function updateLocales(array $widgets): void
    {
        /** @var Model\WidgetLocale $widgetLocale */
        $widgetLocale = new Model\WidgetLocale();
        // очищаем таблицу
        $widgetLocale->deleteAll();
        /** @var array $languages Установленные языки */
        $languages = Ge::$app->language->available->getAll();
        foreach ($widgets as $widgetId => $attributes) {
            // шаблон параметров источника (категории) транслятора виджета
            $translator = WidgetManager::getTranslatePattern($attributes['path']);
            foreach ($languages as $locale => $language) {
                try {
                    // указываем переводчику использование локали $locale
                    $translator['locale'] = $locale;
                    // имя категории сообщений переводчика (в данном случаи для каждой локали виджета своя категория)
                    $category = $attributes['widgetId'] . '.' . $locale;
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
                    // добавляем данные локализации виджета
                    $widgetLocale->widgetId    = $attributes['id'];
                    $widgetLocale->languageId  = $language['code'];
                    $widgetLocale->name        = $name;
                    $widgetLocale->description = $description;
                    $widgetLocale->insert();
                // если файл локализации не найден
                } catch (\Ge\I18n\Exception\PatternNotLoadException $error) {
                    // добавляем данные локализации виджета
                    $widgetLocale->widgetId    = $attributes['id'];
                    $widgetLocale->languageId  = $language['code'];
                    $widgetLocale->name        = $attributes['name'];
                    $widgetLocale->description = $attributes['description'];
                    $widgetLocale->insert();
                    continue;
                }
            }
        }
    }
}
