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
use Ge\Db\QueriesMap;
use Ge\Stdlib\Component;
use Ge\Stdlib\ErrorTrait;
use Ge\Stdlib\BaseObject;
use Ge\Filesystem\Filesystem;
use Ge\Exception\InvalidArgumentException;
 
/**
 * Установщик модуля.
 * 
 * Из-за того, что установщик модуля использует backend, то для локализации 
 * сообщений класса приминается категория `BACKEND`, 
 * например: `Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}", ['.module.php', 'id'])'`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class ModuleInstaller extends Component
{
    use ErrorTrait;

    /**
     * Локальный путь к установке модуля.
     *
     * Пример: '/rg/rg.foobar'.
     * 
     * @see ModuleInstaller::configure()
     * 
     * @var string
     */
    public string $path;

    /**
     * Идентификатор удаляемого модуля.
     * 
     * Пример: 'rg.foobar'.
     * 
     * @see ModuleInstaller::configure()
     * 
     * @var string
     */
    public string $moduleId = '';

    /**
     * Идентификатор установки модуля.
     * 
     * Зашифрованная строка, которая включает локальный путь модуля и 
     * его пространство имён.
     * 
     * Имеет вид: 'path,namespace'.
     * 
     * Используется моделью представления для формирования интерфейса установки 
     * модуля и передачи его параметров установки установщику.
     * 
     * @see ModuleInstaller::configure()
     * 
     * @var string
     */
    public string $installId = '';

    /**
     * Параметры конфигурации установленного (устанавливаемого) модуля.
     * 
     * @see ModuleRegistry::validateInstall()
     * @see ModuleRegistry::validateUninstall()
     * 
     * @var array
     */
    public array $info;

    /**
     * Карта SQL-запросов устанавливаемого модуля.
     * 
     * @see ModuleInstaller::getQueriesMap()
     * 
     * @var false|QueriesMap
     */
    protected false|QueriesMap $queriesMap;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if (!isset($this->path)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "%s" passed incorrectly.', 'path')
            );
        }
    }

    /**
     * Возвращает Карту SQL-запросов устанавливаемого модуля.
     * 
     * @return false|QueriesMap Возвращает значение `false`, если файл карты SQL-запросов 
     *     не существует.
     */
    public function getQueriesMap(): false|QueriesMap
    {
        if (!isset($this->queriesMap)) {
            $filename = Ge::$app->modulePath . $this->path . DS . 'src' . DS . 'Installer' . DS . 'Queries.php';
            if (file_exists($filename))
                $this->queriesMap = new QueriesMap([
                    'filename' => $filename,
                    'adapter'  => Ge::$app->db,
                    // эти параметры устанавливает так же установщик приложения (rg.setup)
                    'params'   => [
                        'isSetup'   => false, // выполняет работу установщик модуля, а не установщик приложения
                        'isRu'      => Ge::$app->language->isRu(), // если языка установки русский
                        'applyDemo' => false // применить демоданные
                    ]
                ]);
            else
                $this->queriesMap = false;
        }
        return $this->queriesMap;
    }

    /**
     * Возвращает информацию об установленном модуле.
     * 
     * @see \Ge\ModuleManager\ModuleRegistry::getInfo()
     * 
     * @return array
     */
    public function getInstalledInfo(): array
    {
        if (!isset($this->info)) {
            /** @var ModuleRegistry $registry */
            $registry = Ge::$app->modules->getRegistry();
            /** @var null|array Параметры конфигурации установленного модуля */
            $info = $registry->getInfo($this->moduleId);
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Возвращает информацию об устанавливаемом модуле.
     * 
     * @see \Ge\ModuleManager\BaseManager::getInfo()
     * 
     * @return array
     */
    public function getModuleInfo(): array
    {
        if (!isset($this->info)) {
            $info = Ge::$app->modules->getInfo(['path' => $this->path], true);

            // попытка добавить локализацию модуля для определения имени и описания модуля
            if ($info && $info['config']['translator']) {
                try {
                    $moduleId = $info['install']['id'];
                    Ge::$app->translator->addCategory($moduleId, $info['config']['translator']);
                    $name = Ge::t($moduleId, '{name}');
                    // если есть перевод
                    if ($name !== '{name}') {
                        $info['name'] = $name;
                    }
                    $description = Ge::t($moduleId, '{description}');
                    // если есть перевод
                    if ($description !== '{description}') {
                        $info['description'] = $description;
                    }
                // если локализация не найдена
                } catch (\Exception $error) {
                }
            }

            if ($info) {
                // идентификатор установки модуля
                $info['installId'] = $this->installId;
            }
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Выполняет удаление модуля.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления модуля.
     */
    public function uninstall(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные модуля из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // удаляет файлы модуля
        if (!$this->uninstallFiles()) {
            return false;
        }
        // отменяет регистрацию модуля
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUninstall();
        return true;
    }

    /**
     * Проверяет модуль перед удалением.
     * 
     * @return bool
     */
    public function validateUninstall(): bool
    {
        /** @var null|array $info Параметры конфигурации установленного модуля */
        $info = $this->getInstalledInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Module'), $info['id']])
            );
            return false;
        }

        if ((bool) ($info['lock'] ?? false)) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with the specified id "{1}" is a system', [Ge::t('app', 'Module'), $info['id']])
            );
            return false;
        }

        $rowId = $info['rowId'] ?? null;
        if (empty($rowId)) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with the identifier "{1}" is missing in the database', [Ge::t('app', 'Module'), $info['id']])
            );
            return false;
        }
        return true;
    }

    /**
     * Удаляет данные модуля из базы данных.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления данных 
     *     модуля из базы данных.
     */
    public function uninstallDb(): bool
    {
        /** @var false|QueriesMap $map */
        $map = $this->getQueriesMap();
        if ($map) {
            $map->load();
            $map->run('uninstall');
        }
        return true;
    }

    /**
     * Удаляет установленные файлы.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления файлов модуля.
     * 
     * @throws \Ge\Filesystem\Exception\RemoveDirectoryException
     * @throws \Ge\Filesystem\Exception\DeleteException
     */
    public function uninstallFiles(): bool
    {
        Filesystem::deleteDirectory(Ge::$app->modulePath . $this->path);
        return true;
    }

   /**
     * Отменяет регистрацию модуля.
     * 
     * Удаляет модуль из файлов конфигурации:
     * - шорткоды ".shortcodes.php" (.shortcodes.so.php),
     * - маршруты запросов ".router.php" (.router.so.php),
     * - модули ".modules.php" (.modules.so.php),
     * и базы данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка отмены регистрации модуля.
     */
    public function unregister(): bool
    {
        // удаляет модуль из базы данных
        (new Model\Module())->deleteByPk($this->info['rowId']);
        // удаляет локализацю модуля из базы данных
        (new Model\ModuleLocale())->deleteByPk($this->info['rowId']);

        // обновляет конфигурацию установленных модулей
        Ge::$app->modules->update();
        return true;
    }

    /**
     * Событие возникающие после успешного удаления модуля.
     * 
     * @return void
     */
    public function afterUninstall(): void
    {
    }

    /**
     * Демонтирует модуль из системы.
     * 
     * В отличии от {@see ModuleInstaller::uninstall()} не удаляет файлы модуля, что даёт 
     * возможность модуль переустановить.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка демонтирования модуля.
     */
    public function unmount(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные модуля из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // отменяет регистрацию модуля
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUnmount();
        return true;
    }

    /**
     * Событие возникающие после успешного демонтажа модуля.
     * 
     * @return void
     */
    public function afterUnmount(): void
    {
    }

    /**
     * Выполняет установку модуля.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в установке модуля.
     */
    public function install(): bool
    {
        if (!$this->validateInstall()) {
            return false;
        }
        // добавляет данные модуля в базу данных
        if (!$this->installDb()) {
            return false;
        }
        // устанавливает файлы модуля
        if (!$this->installFiles()) {
            return false;
        }
        // регистрирует модуль
        if (($params = $this->register()) === false) {
            return false;
        }
        $this->afterInstall($params);
        return true;
    }

    /**
     * Проверяет конфигурацию модуля.
     * 
     * Ошибки при проверке будут в {@see ModuleInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateInstall(): bool
    {
        /** @var array Параметры конфигурации устанавливаемого модуля */
        $info = $this->getModuleInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Module'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки модуля */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['use', 'id', 'name', 'description', 'namespace', 'path', 'locales', 'route', 'routes', 'required'];
        foreach ($requiredInstall as $parameter) {
            if (empty($installConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }
        if (!isset($installConfig['events'])) {
            $this->addError(
                Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", 'events'])
            );
            return false;
        }

        // проверка параметров: назначение
        if ($installConfig['use'] !== BACKEND && $installConfig['use'] !== FRONTEND) {
            $this->addError(
                Ge::t(BACKEND, 'File "{0}" configuration parameter "{1}" specified incorrectly', [".$configName.php", 'use'])
            );
            return false;
        }

        /** @var \Ge\ModuleManager\ModuleRegistry $registry */
        $registry = Ge::$app->modules->getRegistry();
        // проверка модуля в конфигурации установленных модулей
        if ($registry->has($installConfig['id'])) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Module'), $installConfig['id']])
            );
            return false;
        }

        // проверка модуля в базе данных установленных модулей
        $module = Ge::$app->modules->selectOne($installConfig['id']);
        if ($module !== null) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Module'), $installConfig['id']])
            );
            return false;
        }

        // проверка требований к модулю
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии модуля */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации версии
        $requiredVersion = ['name', 'description', 'version', 'versionDate', 'author'];
        foreach ($requiredVersion as $parameter) {
            if (!isset($versionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }

        /** Конфигурация модуля */
        $moduleConfig = $info[$configName = 'config'] ?? null;
        if ($moduleConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации модуля
        $requiredModule = ['translator', 'accessRules'];
        foreach ($requiredModule as $parameter) {
            if (!isset($moduleConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['.module.php', $parameter])
                );
                return false;
            }
        }

        // проверка параметров локализации модуля
        $translatorConfig = $moduleConfig['translator'];
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали модуля своя категория)
                $category = $installConfig['id'] . '.' . $locale;
                Ge::$app->translator->addCategory($category, $translatorConfig);

                // если нет названия ({name}) в локализации
                if (Ge::t($category, '{name}') === '{name}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{name}'])
                    );
                    return false;
                }

                // если нет описания ({description}) в локализации
                if (Ge::t($category, '{description}') === '{description}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{description}'])
                    );
                    return false;
                }

                // если нет разрешения ({permissions}) в локализации
                if (Ge::t($category, '{permissions}') === '{permissions}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{permissions}'])
                    );
                    return false;
                }
                // если файл локализации не найден
            } catch (\Exception $error) {
                $this->addError(
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Module'), $locale])
                );
                return false;
            }
        }

        /** Проверка параметров на уникальность среди установленных модулей */
        foreach ($registry->getMap() as $rowId => $registryConfig) {
            if ($installConfig['namespace'] === $registryConfig['namespace']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Module'), 'namespace'])
                );
                return false;
            }
            if ($installConfig['path'] === $registryConfig['path']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Module'), 'path'])
                );
                return false;
            }
            if ($installConfig['route'] && ($installConfig['route'] === $registryConfig['route'])) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Module'), 'route'])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Добавляет данные в базу данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка добавления данных модуля 
     *     в базу данных.
     */
    public function installDb(): bool
    {
        /** @var false|QueriesMap $map */
        $map = $this->getQueriesMap();
        if ($map) {
            $map->load();
            $map->run('install');
        }
        return true;
    }

    /**
     * Устанавливает файлы модуля.
     * 
     * @return bool Возвращает значение `false`, если ошибка установки файлов модуля.
     */
    public function installFiles(): bool
    {
        return true;
    }

    /**
     * Регистрирует модуль.
     * 
     * Добавляет данные модуля в файлы конфигурации:
     * - шорткоды ".shortcodes.php" (.shortcodes.so.php),
     * - маршруты запросов ".router.php" (.router.so.php),
     * - модули ".modules.php" (.modules.so.php),
     * и базы данных.
     * 
     * @see \Ge\ModuleManager\ModuleRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      регистрации модуля. Иначе, параметры конфигурации установленного модуля.
     */
    public function register(): false|array
    {
        /** @var \Ge\ModuleManager\ModuleManager $modules Менеджер модулей */
        $modules = Ge::$app->modules;

        /** @var array|null Параметры конфигурации установки модуля */
        $installConfig = $modules->getConfigInstall($this->path);
        /** @var array|null Параметры конфигурации модуля */
        $moduleConfig = $modules->getConfigFile($this->path, 'module');

        /** Добавление модуля в базу данных */
        /** @var \Ge\ModuleManager\Model\Module $module Модуль */
        $module = new Model\Module();
        $module->setAttributes([
            'moduleId'    => $installConfig['id'],
            'moduleUse'   => $installConfig['use'],
            'name'        => $installConfig['name'],
            'description' => $installConfig['description'],
            'namespace'   => $installConfig['namespace'],
            'path'        => $installConfig['path'],
            'route'       => $installConfig['route'] ?? null,
            'routeAppend' => $installConfig['routeAppend'] ?? null,
            'enabled'     => 1,
            'visible'     => $installConfig['use'] === BACKEND ? 1 : 0,
            'append'      => (int) ($installConfig['routeAppend'] ?? 0),
            'expandable'  => (int) ($installConfig['expandable'] ?? 0),
            'hasInfo'     => (int) $modules->controllerExists($installConfig['path'], 'Info'),
            'hasSettings' => (int) $modules->controllerExists($installConfig['path'], 'Settings'),
            'permissions' => empty($installConfig['permissions']) ? null : implode(',', $installConfig['permissions']),
            'lock'        => (int) ($installConfig['lock'] ?? 0),
            'createdDate' => date('Y-m-d H:i:s'),
            'createdUser' => Ge::$app->user->getId()
        ]);
        $moduleId = $module->save();
        if ($moduleId === false) {
            $this->addError(
                Ge::t(BACKEND, 'Error adding {0} to database', [Ge::t('app', 'Module')])
            );
            return false;
        }

        /** Добавление локализаций модуля в базу данных */
        // параметры переводчика из конфигурации модуля
        $translatorConfig = $moduleConfig['translator'];
        /** @var \Ge\ModuleManager\Model\ModuleLocale $moduleLocale Локализация модуля */
        $moduleLocale = new Model\ModuleLocale();

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка модуля нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали модуля своя категория)
                $category = $installConfig['id'] . '.' . $locale;
                Ge::$app->translator->addCategory($category, $translatorConfig);
                $name = Ge::t($category, '{name}');
                // если названия нет в локализации, тогда по умолчанию
                if ($name === '{name}') {
                    $name = $installConfig['name'];
                }
                $description = Ge::t($category, '{description}');
                // если описания нет в локализации, тогда по умолчанию
                if ($description === '{description}') {
                    $description = $installConfig['description'];
                }
                // если права доступа не указаны
                if (empty($installConfig['permissions'])) {
                    $permissions = null;
                } else {
                    $permissions = Ge::t($category, '{permissions}');
                    // если прав доступа нет для переводчика, тогда по умолчанию
                    if (empty($permissions) || $permissions === '{permissions}') {
                        $permissions = null;
                    } else {
                        $permissions = json_encode($permissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                    }
                }
            // если файл локализации не найден
            } catch (\Exception $error) {
                continue;
            }
            $moduleLocale->moduleId    = $moduleId;
            $moduleLocale->languageId  = $language['code'];
            $moduleLocale->name        = $name;
            $moduleLocale->description = $description;
            $moduleLocale->permissions = $permissions;
            $moduleLocale->insert();
        }

        // Обновление конфигурации установленных модулей
        /** @var \Ge\ModuleManager\ModuleRegistry $registry */
        $registry = $modules->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешной установки модуля.
     * 
     * @param array $params Параметры конфигурации установленного модуля.
     * 
     * @return void
     */
    public function afterInstall(array $params): void
    {
    }

    /**
     * Выполняет обновление модуля.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в обновлении модуля.
     */
    public function update(): bool
    {
        if (!$this->validateUpdate()) {
            return false;
        }
        // добавляет данные модуля в базу данных
        if (!$this->updateDb()) {
            return false;
        }
        // обновляет файлы модуля
        if (!$this->updateFiles()) {
            return false;
        }
        // обновляет регистрацию модуля
        if (($params = $this->updateRegister()) === false) {
            return false;
        }
        $this->afterUpdate($params);
        return true;
    }

    /**
     * Проверяет конфигурацию модуля.
     * 
     * Ошибки при проверке будут в {@see ModuleInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateUpdate(): bool
    {
        /** @var array $info Параметры конфигурации установленного модуля */
        $info = $this->getModuleInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Module'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки модуля */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['use', 'id', 'name', 'description', 'namespace', 'path', 'locales', 'routes', 'required'];
        if (isset($installConfig['use'])) {
            // параметр 'route' устанавливается только для BACKEND
            if ($installConfig['use'] === BACKEND) {
                $requiredInstall[] = 'route';
            }
        }
        foreach ($requiredInstall as $parameter) {
            if (empty($installConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }
        if (!isset($installConfig['events'])) {
            $this->addError(
                Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", 'events'])
            );
            return false;
        }

        // проверка параметров: назначение
        if ($installConfig['use'] !== BACKEND && $installConfig['use'] !== FRONTEND) {
            $this->addError(
                Ge::t(BACKEND, 'File "{0}" configuration parameter "{1}" specified incorrectly', [".$configName.php", 'use'])
            );
            return false;
        }

        // проверка требований к модулю
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии модуля */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации версии
        $requiredVersion = ['name', 'description', 'version', 'versionDate', 'author'];
        foreach ($requiredVersion as $parameter) {
            if (!isset($versionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }

        /** Конфигурация модуля */
        $moduleConfig = $info[$configName = 'config'] ?? null;
        if ($moduleConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации модуля
        $requiredModule = ['translator', 'accessRules'];
        foreach ($requiredModule as $parameter) {
            if (!isset($moduleConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['.module.php', $parameter])
                );
                return false;
            }
        }

        // проверка параметров локализации модуля
        $translatorConfig = $moduleConfig['translator'];
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали модуля своя категория)
                $category = $installConfig['id'] . '.' . $locale;
                Ge::$app->translator->addCategory($category, $translatorConfig);

                // если нет названия ({name}) в локализации
                if (Ge::t($category, '{name}') === '{name}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{name}'])
                    );
                    return false;
                }

                // если нет описания ({description}) в локализации
                if (Ge::t($category, '{description}') === '{description}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{description}'])
                    );
                    return false;
                }

                // если нет разрешения ({permissions}) в локализации
                if (Ge::t($category, '{permissions}') === '{permissions}') {
                    $this->addError(
                        Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['language: ' . $category, '{permissions}'])
                    );
                    return false;
                }
                // если файл локализации не найден
            } catch (\Exception $error) {
                $this->addError(
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Module'), $locale])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Обновляет данные базы данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления данных модуля.
     */
    public function updateDb(): bool
    {
        return true;
    }

    /**
     * Обновляет файлы модуля.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления файлов модуля.
     */
    public function updateFiles(): bool
    {
        return true;
    }

    /**
     * Обновляет регистрацию модуля.
     * 
     * Добавляет данные модуля в файлы конфигурации:
     * - шорткоды ".shortcodes.php" (.shortcodes.so.php),
     * - маршруты запросов ".router.php" (.router.so.php),
     * - модули ".modules.php" (.modules.so.php),
     * и базы данных.
     * 
     * @see \Ge\ModuleManager\ModuleRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      обновлении модуля. Иначе, параметры конфигурации установленного модуля.
     */
    public function updateRegister(): false|array
    {
        /** @var \Ge\ModuleManager\ModuleManager $modules Менеджер модулей */
        $modules = Ge::$app->modules;

        /** @var array|null $installConfig Параметры конфигурации установки модуля */
        $installConfig = $modules->getConfigInstall($this->path);
        /** @var array|null $moduleConfig Параметры конфигурации модуля */
        $moduleConfig = $modules->getConfigFile($this->path, 'module');
        /** @var array|null $versionConfig Параметры конфигурации версии */
        $versionConfig = $modules->getConfigVersion($this->path);

        /** @var array|null $moduleParams Параметры установленного модуля в реестре */
        $moduleParams = $modules->getRegistry()->getAt($installConfig['id']);
        if ($moduleParams === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" is not in the registry', [Ge::t('app', 'Module'), $installConfig['id']])
            );
            return false;
        }
        // идентификатор модуля в базе данных
        $moduleRowId = $moduleParams['rowId'];

        /** Обновление модуля в базе данных */
        /** @var \Ge\ModuleManager\Model\Module $module Активная запись модуля */
        $module = new Model\Module();
        $module = $module->get($moduleRowId);
        if ($module === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" not found at database', [Ge::t('app', 'Module'), $moduleRowId])
            );
            return false;
        }

        // обновление всех полей модуля
        $module->moduleUse   = $installConfig['use'];
        $module->name        = $installConfig['name'];
        $module->description = $installConfig['description'];
        $module->namespace   = $installConfig['namespace'];
        $module->path        = $installConfig['path'];
        $module->route       = $installConfig['route'] ?? null;
        $module->routeAppend = $installConfig['routeAppend'] ?? null;
        $module->enabled     = 1;
        $module->visible     = $installConfig['use'] === BACKEND ? 1 : 0;
        $module->append      = (int) ($installConfig['routeAppend'] ?? 0);
        $module->expandable  = (int) ($installConfig['expandable'] ?? 0);
        $module->hasInfo     = (int) $modules->controllerExists($installConfig['path'], 'Info');
        $module->hasSettings = (int) $modules->controllerExists($installConfig['path'], 'Settings');
        $module->permissions = empty($installConfig['permissions']) ? null : implode(',', $installConfig['permissions']);
        $module->version     = $versionConfig['version'] ?? '1.0';
        $module->lock        = (int) ($installConfig['lock'] ?? 0);
        $module->updatedDate = date('Y-m-d H:i:s');
        $module->updatedUser = Ge::$app->user->getId();
        if (!$module->save()) {
            $this->addError(
                Ge::t(BACKEND, 'Error saving {0} to database', [Ge::t('app', 'Module')])
            );
            return false;
        }

        /** Обновление локализаций модуля в базе данных */
        // параметры переводчика из конфигурации модуля
        $translatorConfig = $moduleConfig['translator'];
        /** @var \Ge\ModuleManager\Model\ModuleLocale $moduleLocale Локализация модуля */
        $moduleLocale = new Model\ModuleLocale();

        // удаление добавленных ранее локализаций модуля из базы данных
        $moduleLocale->deleteFromModule($moduleRowId);

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка модуля нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали модуля своя категория)
                $category = $installConfig['id'] . '.' . $locale;
                Ge::$app->translator->addCategory($category, $translatorConfig);
                $name = Ge::t($category, '{name}');
                // если названия нет в локализации, тогда по умолчанию
                if ($name === '{name}') {
                    $name = $installConfig['name'];
                }
                $description = Ge::t($category, '{description}');
                // если описания нет в локализации, тогда по умолчанию
                if ($description === '{description}') {
                    $description = $installConfig['description'];
                }
                // если права доступа не указаны
                if (empty($installConfig['permissions'])) {
                    $permissions = null;
                } else {
                    $permissions = Ge::t($category, '{permissions}');
                    // если прав доступа нет для переводчика, тогда по умолчанию
                    if (empty($permissions) || $permissions === '{permissions}') {
                        $permissions = null;
                    } else {
                        $permissions = json_encode($permissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                    }
                }
            // если файл локализации не найден
            } catch (\Exception $error) {
                continue;
            }
            $moduleLocale->moduleId    = $moduleRowId;
            $moduleLocale->languageId  = $language['code'];
            $moduleLocale->name        = $name;
            $moduleLocale->description = $description;
            $moduleLocale->permissions = $permissions;
            $moduleLocale->insert();
        }

        // Обновление конфигурации установленных модулей
        /** @var \Ge\ModuleManager\ModuleRegistry $registry */
        $registry = $modules->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешного обновления модуля.
     * 
     * @param array $params Параметры конфигурации обновляемого модуля.
     * 
     * @return void
     */
    public function afterUpdate(array $params): void
    {
    }

    /**
     * Возвращает виджет для формирования интерфейса установщика.
     * 
     * Если виджет производный от другого класса, то он должен иметь метод 
     * `run(): string` (результат формирования интерфейса).
     * 
     * @return null|BaseObject
     */
    public function getWidget(): ?BaseObject
    {
        return null;
    }
}