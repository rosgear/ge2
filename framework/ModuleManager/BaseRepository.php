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
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Ge\Filesystem\Filesystem;

/**
 * Базовый класс Репозитория объектов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class BaseRepository
{
    /**
     * @var string Модули репозитория.
     */
    const MODULE = 'Module';
    /**
     * @var string Расширение модулей репозитория.
     */
    const EXTENSION = 'Extension';
    /**
     * @var string Виджет репозитория.
     */
    const WIDGET = 'Widget';
    /**
     * @var string Плагины репозитория.
     */
    const PLUGIN = 'Plugin';

    /**
     * Менеджер компонентов.
     *
     * @var BaseManager|null
     */
    public $manager = null;

    /**
     * Конструктор класса.
     * 
     * @param BaseManager|null $manager Менеджер компонентов.
     * 
     * @return void
     */
    public function __construct(?BaseManager $manager = null)
    {
        $this->manager = $manager;
    }

    /**
     * Возвращает параметры из файла конфигурации установки компонента.
     * 
     * @param string $filename Имя файла конфигурации объекта с полным путём.
     * @param bool $usePattern Использовать шаблон параметров установки (по умолчанию `true`).
     * 
     * @return array|null Возвращает значение `null`, если файл конфигурации не существует.
     */
    public function getConfigInstall(string $filename, bool $usePattern = true): ?array
    {
        $params = [];
        if (file_exists($filename)) {
            // параметры конфигурации установки модуля
            $params = include($filename);
            if ($params && is_array($params)) {
                return $usePattern ? $this->manager->getInstallPattern($params) : $params;
            }
        }
        return null;
    }

    /**
     * Возвращает информацию о компоненте.
     * 
     * @see BaseManager::getInfo()
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
     * 
     * @return null|array
     */
    public function getInfo(array $params, bool|array $include = ['icon' => true]): ?array
    {
        if (is_array($include)) {
            /** @var bool $includeNames Получить имя компонента в текущей локализации */
            $includeName = $include['name'] ?? false;
            /** @var bool $includeNames Получить имена компонента на указанных им языках */
            $includeNames = $include['names'] ?? false;
            if ($includeName || $includeNames) {
                $include['config']  = true;
                $include['install'] = true;
            }
        } else {
            $includeName = $includeNames = $include;
        }

        /** @var null|array $info */ 
        $info = $this->manager->getInfo($params, $include);

        /** @var null|array $translator Параметры конфигурации переводчика компонента */
        $translator = $info['config']['translator'] ?? null;
        // такие компоненты, как виджеты, могут не иметь свой файл конфигурации, а значит и настроек перевода,
        // то мы их добавляем
        if ($translator === null) {
            $translator = [
                'locale'   => 'auto',
                'patterns' => [
                    'text' => [
                        'basePath' => BASE_PATH . MODULE_PATH . DS . $params['path'] . DS . 'lang',
                        'pattern'   => 'text-%s.php'
                    ]
                ],
                'autoload' => ['text']
            ];
        }

        // попытка добавить локализацию модуля для определения имени и описания
        if ($info && $translator) {
            try {
                if (empty($info['id']))
                    // если $include['install'] = true
                    $componentId = $info['install']['id'] ?? '';
                else
                    $componentId = $info['id'];
                // если нет идентификатора компонента
                if (empty($componentId)) return null;

                // имя и описание компонент по умолчанию
                $defName = $params['name'] ?? $info['install']['name'] ?? '';
                $defDesc = $params['description'] ?? $info['install']['description'] ?? '';

                // получаем имя и описание компонента
                if ($includeName) {
                    // добавляем файл локализации
                    Ge::$app->translator->addCategory($componentId, $translator);
                    $name = Ge::t($componentId, '{name}');
                    $desc = Ge::t($componentId, '{description}');
                    // если есть перевод
                    $info['name'] = $name === '{name}' ? $defName : $name;
                    $info['description'] = $desc === '{description}' ? $defDesc : $desc;
                }

                // получаем имена и описание компонента
                if ($includeNames) {
                    /** @var array $locales Локали компонента */
                    $locales = $info['install']['locales'] ?? [];
                    // имя и описание компонента в локалях
                    $info['names'] = [];
                    foreach ($locales as $locale) {
                        $translator['locale'] = $locale;
                        // добавляем файл локализации
                        Ge::$app->translator->addCategory($componentId . $locale, $translator);
                        $name = Ge::t($componentId . $locale, '{name}');
                        $desc = Ge::t($componentId . $locale, '{description}');
                        // если есть перевод
                        $info['names'][$locale] = [
                            'name'        => $name === '{name}' ? $defName : $name,
                            'description' => $desc === '{description}' ? $defDesc : $desc
                        ];
                    }
                }
            // если локализация не найдена
            } catch (\Exception $error) {
            }
        }
        return $info;
    }

    /**
     * Выполянет поиск компонентов в указанных репозиториях.
     * 
     * @see BaseRepository::getInfo()
     * 
     * @param array $path Репозитории компонентов (путь к каждому компоненту).
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
     * 
     * @return array<int|string, array>
     */
    public function findByPath(array $path, bool|array $include = ['name' => true]): array
    {
        $items = [];
        foreach ($path as $componentPath) {
            $info = $this->getInfo(['path' => $componentPath], $include);
            if ($info) {
                $id = $info['install']['id'] ?? '';
                if ($id)
                    $items[$id] = $info;
                else
                    $items[] = $info;
            }
        }
        return $items;
    }

    /**
     * Выполянет поиск компонентов в репозитории.
     *
     * @param string $name Имя компонента для которого осуществляется поиск 
     *     например: 'Module', 'Extension', 'Widget', 'Plugin'.
     * @param string $status Поиск компонента по статусу (по умолчанию 'all').
     *     Аргумент может иметь значения:
     *     - 'all', установленные и не установленные компоненты;
     *     - 'installed', только установленные компоненты;
     *     - 'notInstalled', только не установленные компоненты.
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
     * @param string $filename Имя файла конфигурации установки компонента (по умолчанию '.install.php').
     *     Относительно пути файла конфигурации установки выполняется поиск компонента  
     *     с именем $name.
     * @param array|null $paths Абсолютные пути найденных компонентов. Если значение 
     *     `null`, то будет применятся итератор для поиска путей (по умолчанию `null`).
     * 
     * @return array
     */
    public function find(
        string $name, 
        string $status = 'all', 
        array|bool $include = ['icon' => true, 'name' => true], 
        string $filename = '.install.php', 
        ?array $paths = null
    ): array
    {
        // применить итератор
        if ($paths === null) {
            /** @var \Symfony\Component\Finder\Finder $finder */
            $finder = Filesystem::finder();

            // имя файла компонента, относительно которого выполняется поиск
            $objectName = DS . '..' . DS . 'src' . DS . $name . '.php';
            $modulePath = Ge::$app ? Ge::$app->modulePath : BASE_PATH . MODULE_PATH;
            // поиск файлов конфигурации установки $filename
            $finder->files()->name($filename)->ignoreDotFiles(false)->in($modulePath);

            $paths = [];
            foreach ($finder as $file) {
                $path = $file->getPath();
                // если компонент отсутствует в каталоге
                if (file_exists($path . $objectName)) {
                    $paths[] = $path;
                }
            }
        }
        return $this->_find($status, $include, $paths);
    }

    /**
     * Выполянет поиск компонентов в репозитории.
     *
     * @param string $status Поиск компонента по статусу (по умолчанию 'all').
     *     Аргумент может иметь значения:
     *     - 'all', установленные и не установленные компоненты;
     *     - 'installed', только установленные компоненты;
     *     - 'notInstalled', только не установленные компоненты.
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
     * @param array|null $paths Абсолютные пути найденных компонентов. Если значение 
     *     `null`, то будет применятся итератор для поиска путей (по умолчанию `null`).
     * 
     * @return array
     */
    protected function _find(string $status, array|bool $include, array $paths): array
    {
        if ($status === 'installed' || $status === 'nonInstalled') {
            /** @var BaseRegistry $registry */
            $registry = $this->manager->getRegistry();
        }

        $items = [];
        foreach ($paths as $path) {
            if ($include === false) {
                $items[] = $path;
            } else {
                /** @var null|array $installConfig Параметры конфигурации установки компонента */
                $installConfig = $this->getConfigInstall($path . '/.install.php');
                if ($installConfig) {
                    // статус компонента
                    switch ($status) {
                        // установленные компоненты
                        case 'installed':
                            if ($registry->has($installConfig['id'])) {
                                $info = $this->getInfo($installConfig, $include);
                                if ($info) {
                                    $items[] = $info;
                                }
                            }
                            break;

                        // не установленные компоненты
                        case 'nonInstalled':
                            if (!$registry->has($installConfig['id'])) {
                                $info = $this->getInfo($installConfig, $include);
                                if ($info) {
                                    $items[] = $info;
                                }
                            }
                            break;

                        // установленные и не установленные компоненты
                        default:
                            $info = $this->getInfo($installConfig, $include);
                            if ($info) {
                                $items[] = $info;
                            }
                    }
                }
            }
        }
        return $items;
    }

    /**
     * Возвращает абсолютные пути всех найденных компонентов (модуля, расширения, 
     * виджеты, плагины).
     * 
     * Каждый возвращаемый путь - это путь к установочному файлу конфигурации 
     * компонента, например '/home/modules/rg/rg.be.foobar/config'.
     * 
     * Все пути будут сгруппированы по типу компонента, например:
     * ```php
     * return [
     *     'Module'    => [...],
     *     'Extension' => [...]
     *     'Widget'    => [...],
     *     'Plugin'    => [...]
     * ];
     * ```
     * 
     * @return array<string, array<int, string>>
     */
    public function findPaths(): array
    {
        $path = Ge::$app ? Ge::$app->modulePath : BASE_PATH . MODULE_PATH;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $modSrc = DS . '..' . DS . 'src' . DS . self::MODULE . '.php';
        $extSrc = DS . '..' . DS . 'src' . DS . self::EXTENSION . '.php';
        $widSrc = DS . '..' . DS . 'src' . DS . self::WIDGET . '.php';
        $plgSrc = DS . '..' . DS . 'src' . DS . self::PLUGIN . '.php';
        $items = [
            self::MODULE    => [],
            self::EXTENSION => [],
            self::WIDGET    => [],
            self::PLUGIN    => []
        ];
        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getFilename() === '.install.php') {

                $path = $item->getPath();
                // если репозиторий модуля
                if (file_exists($path . $modSrc)) {
                    $items[self::MODULE][] = $path;
                } else
                // если репозиторий расширения модуля
                if (file_exists($path . $extSrc)) {
                    $items[self::EXTENSION][] = $path;
                } else
                // если репозиторий виджета
                if (file_exists($path . $widSrc)) {
                    $items[self::WIDGET][] = $path;
                } else
                // если репозиторий плагина
                if (file_exists($path . $plgSrc)) {
                    $items[self::PLUGIN][] = $path;
                }
            }
        }
        return $items;
    }
}
