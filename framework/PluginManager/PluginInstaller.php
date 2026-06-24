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
use Ge\Stdlib\Component;
use Ge\Stdlib\ErrorTrait;
use Ge\Stdlib\BaseObject;
use Ge\Filesystem\Filesystem;
use Ge\Exception\InvalidArgumentException;
 
/**
 * Установщик плагина.
 * 
 * Из-за того, что установщик плагина использует backend, то для локализации 
 * сообщений класса приминается категория `BACKEND`, 
 * например: `Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}", ['.plugin.php', 'id'])'`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\PluginManager
 * @since 2.0
 */
class PluginInstaller extends Component
{
    use ErrorTrait;

    /**
     * Локальный путь к установке плагина.
     *
     * Пример: '/rg/rg.plg.foobar'.
     * 
     * @see PluginInstaller::configure()
     * 
     * @var string
     */
    public string $path;

    /**
     * Идентификатор удаляемого плагина.
     * 
     * Пример: 'rg.plg.foobar'.
     * 
     * @see PluginInstaller::configure()
     * 
     * @var string
     */
    public string $pluginId = '';

    /**
     * Идентификатор установки плагина.
     * 
     * Зашифрованная строка, которая включает локальный путь плагина и 
     * его пространство имён.
     * 
     * Имеет вид: 'path,namespace'.
     * 
     * Используется моделью представления для формирования интерфейса установки 
     * плагина и передачи его параметров установки установщику.
     * 
     * @see PluginInstaller::configure()
     * 
     * @var string
     */
    public string $installId = '';

    /**
     * Параметры конфигурации установленного (устанавливаемого) плагина.
     * 
     * @see PluginRegistry::validateInstall()
     * @see PluginRegistry::validateUninstall()
     * 
     * @var array
     */
    public array $info;

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
     * Возвращает информацию об установленном плагине.
     * 
     * @see PluginRegistry::getInfo()
     * 
     * @return array
     */
    public function getInstalledInfo(): array
    {
        if (!isset($this->info)) {
            /** @var PluginRegistry $registry */
            $registry = Ge::$app->plugins->getRegistry();
            /** @var null|array Параметры конфигурации установленного плагина */
            $info = $registry->getInfo($this->pluginId);
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Возвращает информацию об устанавливаемом плагине.
     * 
     * @see PluginManager::getInfo()
     * 
     * @return array
     */
    public function getPluginInfo(): array
    {
        if (!isset($this->info)) {
            $info = Ge::$app->plugins->getInfo(['path' => $this->path], true);

            // попытка добавить локализацию плагина для определения имени и описания плагина
            if ($info) {
                try {
                    $pluginId = $info['install']['id'];
                    Ge::$app->plugins->addTranslateCategory($pluginId, $info['path']);
                    $name = Ge::t($pluginId, '{name}');
                    // если есть перевод
                    if ($name !== '{name}') {
                        $info['name'] = $name;
                    }
                    $description = Ge::t($pluginId, '{description}');
                    // если есть перевод
                    if ($description !== '{description}') {
                        $info['description'] = $description;
                    }
                // если локализация не найдена
                } catch (\Exception $error) {
                }
            }

            if ($info) {
                // идентификатор установки плагина
                $info['installId'] = $this->installId;
            }
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Выполняет удаление плагина.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления плагина.
     */
    public function uninstall(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные плагина из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // удаляет файлы плагина
        if (!$this->uninstallFiles()) {
            return false;
        }
        // отменяет регистрацию плагина
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUninstall();
        return true;
    }

    /**
     * Проверяет плагин перед удалением.
     * 
     * @return bool
     */
    public function validateUninstall(): bool
    {
        /** @var array $info Параметры конфигурации установленного плагина */
        $info = $this->getInstalledInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Plugin'), $info['id']])
            );
            return false;
        }

        if ((bool) ($info['lock'] ?? false)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Plugin'), $info['id']])
            );
            return false;
        }

        $rowId = $info['rowId'] ?? null;
        if (empty($rowId)) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with the identifier "{1}" is missing in the database', [Ge::t('app', 'Plugin'), $info['id']])
            );
            return false;
        }
        return true;
    }

    /**
     * Удаляет данные плагина из базы данных.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления данных 
     *     плагина из базы данных.
     */
    public function uninstallDb(): bool
    {
        return true;
    }

    /**
     * Удаляет установленные файлы.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления файлов плагина.
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
     * Отменяет регистрацию плагина.
     * 
     * Удаляет плагин из файлов конфигурации:
     * - плагины ".plugins.php" (.plugins.so.php),
     * и базы данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка отмены регистрации плагина.
     */
    public function unregister(): bool
    {
        // удаляет плагин из базы данных
        (new Model\Plugin())->deleteByPk($this->info['rowId']);
        // удаляет локализацю плагина из базы данных
        (new Model\PluginLocale())->deleteByPk($this->info['rowId']);

        // обновляет конфигурацию установленных видежтов
        Ge::$app->plugins->update();
        return true;
    }

    /**
     * Событие возникающие после успешного удаления плагина.
     * 
     * @return void
     */
    public function afterUninstall(): void
    {
    }

    /**
     * Демонтирует плагин из системы.
     * 
     * В отличии от {@see PluginInstaller::uninstall()} не удаляет файлы плагина, что даёт 
     * возможность плагин переустановить.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка демонтирования плагина.
     */
    public function unmount(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные плагина из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // отменяет регистрацию плагина
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUnmount();
        return true;
    }

    /**
     * Событие возникающие после успешного демонтажа плагина.
     * 
     * @return void
     */
    public function afterUnmount(): void
    {
    }

    /**
     * Выполняет установку плагина.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в установке плагина.
     */
    public function install(): bool
    {
        if (!$this->validateInstall()) {
            return false;
        }
        // добавляет данные плагина в базу данных
        if (!$this->installDb()) {
            return false;
        }
        // устанавливает файлы плагина
        if (!$this->installFiles()) {
            return false;
        }
        // регистрирует плагин
        if (($params = $this->register()) === false) {
            return false;
        }
        $this->afterInstall($params);
        return true;
    }

    /**
     * Проверяет конфигурацию плагина.
     * 
     * Ошибки при проверке будут в {@see PluginInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateInstall(): bool
    {
        /** @var array Параметры конфигурации устанавливаемого плагина */
        $info = $this->getPluginInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Plugin'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки плагина */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Plugin'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['id', 'ownerId', 'name', 'description', 'namespace', 'path', 'locales', 'required'];
        foreach ($requiredInstall as $parameter) {
            if (empty($installConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }

        /** @var PluginRegistry $registry */
        $registry = Ge::$app->plugins->getRegistry();
        // проверка плагина в конфигурации установленных плагинов
        if ($registry->has($installConfig['id'])) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Plugin'), $installConfig['id']])
            );
            return false;
        }

        // проверка плагина в базе данных установленных плагинов
        $plugin = Ge::$app->plugins->selectOne($installConfig['id']);
        if ($plugin !== null) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Plugin'), $installConfig['id']])
            );
            return false;
        }

        // проверка требований к плагину
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии плагина */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Plugin'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации версии
        $requiredVersion = ['name', 'description', 'version', 'versionDate', 'author', 'license'];
        foreach ($requiredVersion as $parameter) {
            if (!isset($versionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }

        /** Конфигурация плагина */
        // проверка параметров локализации плагина
        $translatorConfig = PluginManager::getTranslatePattern($installConfig['path']);
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали плагина своя категория)
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
                // если файл локализации не найден
            } catch (\Exception $error) {
                $this->addError(
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Plugin'), $locale])
                );
                return false;
            }
        }

        /** Проверка параметров на уникальность среди установленных плагинов */
        foreach ($registry->getMap() as $rowId => $registryConfig) {
            if ($installConfig['namespace'] === $registryConfig['namespace']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Plugin'), 'namespace'])
                );
                return false;
            }
            if ($installConfig['path'] === $registryConfig['path']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Plugin'), 'path'])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Добавляет данные в базу данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка добавления данных плагина 
     *     в базу данных.
     */
    public function installDb(): bool
    {
        return true;
    }

    /**
     * Устанавливает файлы плагина.
     * 
     * @return bool Возвращает значение `false`, если ошибка установки файлов плагина.
     */
    public function installFiles(): bool
    {
        return true;
    }

    /**
     * Регистрирует плагин.
     * 
     * Добавляет данные плагина в файлы конфигурации:
     * - плагины ".plugins.php" (.plugins.so.php), и базы данных.
     * 
     * @see PluginRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      регистрации плагина. Иначе, параметры конфигурации установленного плагина.
     */
    public function register(): false|array
    {
        /** @var PluginManager $plugins Менеджер плагинов */
        $plugins = Ge::$app->plugins;

        /** @var array|null $installConfig Параметры конфигурации установки плагина */
        $installConfig = $plugins->getConfigInstall($this->path);

        /** Добавление плагина в базу данных */
        /** @var \Ge\PluginManager\Model\Plugin $plugin */
        $plugin = new Model\Plugin();
        $plugin->setAttributes([
            'pluginId'    => $installConfig['id'],
            'ownerId'     => $installConfig['ownerId'],
            'category'    => $installConfig['category'],
            'name'        => $installConfig['name'],
            'description' => $installConfig['description'],
            'namespace'   => $installConfig['namespace'],
            'path'        => $installConfig['path'],
            'enabled'     => 1,
            'hasSettings' => (int) $plugins->sourceExists($installConfig['path'], 'Settings' . DS . 'Settings'),
            'lock'        => (int) ($installConfig['lock'] ?? 0),
            'createdDate' => date('Y-m-d H:i:s'),
            'createdUser' => Ge::$app->user->getId()
        ]);
        $pluginId = $plugin->save();
        if ($pluginId === false) {
            $this->addError(
                Ge::t(BACKEND, 'Error adding {0} to database', [Ge::t('app', 'Plugin')])
            );
            return false;
        }

        /** Добавление локализаций плагина в базу данных */
        // шаблон параметров источника (категории) транслятора плагина
        $translatorConfig = PluginManager::getTranslatePattern($installConfig['path']);
        /** @var \Ge\PluginManager\Model\PluginLocale $pluginLocale Локализация плагина */
        $pluginLocale = new Model\PluginLocale();

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка плагина нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали плагина своя категория)
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
            // если файл локализации не найден
            } catch (\Exception $error) {
                continue;
            }
            $pluginLocale->pluginId    = $pluginId;
            $pluginLocale->languageId  = $language['code'];
            $pluginLocale->name        = $name;
            $pluginLocale->description = $description;
            $pluginLocale->insert();
        }

        // Обновление конфигурации установленных плагинов
        /** @var PluginRegistry $registry */
        $registry = $plugins->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешной установки плагина.
     * 
     * @param array $params Параметры конфигурации установленного плагина.
     * 
     * @return void
     */
    public function afterInstall(array $params): void
    {
    }

    /**
     * Выполняет обновление плагина.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в обновлении плагина.
     */
    public function update(): bool
    {
        if (!$this->validateUpdate()) {
            return false;
        }
        // добавляет данные плагина в базу данных
        if (!$this->updateDb()) {
            return false;
        }
        // обновляет файлы плагина
        if (!$this->updateFiles()) {
            return false;
        }
        // обновляет регистрацию плагина
        if (($params = $this->updateRegister()) === false) {
            return false;
        }
        $this->afterUpdate($params);
        return true;
    }

    /**
     * Проверяет конфигурацию плагина.
     * 
     * Ошибки при проверке будут в {@see PluginInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateUpdate(): bool
    {
        /** @var array $info Параметры конфигурации установленного плагина */
        $info = $this->getPluginInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Plugin'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки плагина */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Module'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['use', 'id', 'category', 'name', 'description', 'namespace', 'path', 'locales', 'required'];
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

        // проверка требований к плагину
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии плагина */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Plugin'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации версии
        $requiredVersion = ['name', 'description', 'version', 'versionDate', 'author', 'license'];
        foreach ($requiredVersion as $parameter) {
            if (!isset($versionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', [".$configName.php", $parameter])
                );
                return false;
            }
        }

        /** Конфигурация плагина */
        // проверка параметров локализации плагина
        $translatorConfig = PluginManager::getTranslatePattern($installConfig['path']);
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали плагина своя категория)
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
                // если файл локализации не найден
            } catch (\Exception $error) {
                $this->addError(
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Plugin'), $locale])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Обновляет данные базы данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления данных плагина.
     */
    public function updateDb(): bool
    {
        return true;
    }

    /**
     * Обновляет файлы плагина.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления файлов плагина.
     */
    public function updateFiles(): bool
    {
        return true;
    }

    /**
     * Обновляет регистрацию плагина.
     * 
     * Добавляет данные плагина в файлы конфигурации:
     * - плагины ".plugins.php" (.plugins.so.php),
     * и базы данных.
     * 
     * @see PluginRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      обновлении плагина. Иначе, параметры конфигурации установленного плагина.
     */
    public function updateRegister(): false|array
    {
        /** @var PluginManager $plugins Менеджер плагинов */
        $plugins = Ge::$app->plugins;

        /** @var array|null $installConfig Параметры конфигурации установки плагина */
        $installConfig = $plugins->getConfigInstall($this->path);
        /** @var array|null $versionConfig Параметры конфигурации версии */
        $versionConfig = $plugins->getConfigVersion($this->path);

        /** @var array|null $pluginParams Параметры установленного плагина в реестре */
        $pluginParams = $plugins->getRegistry()->getAt($installConfig['id']);
        if ($pluginParams === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" is not in the registry', [Ge::t('app', 'Plugin'), $installConfig['id']])
            );
            return false;
        }
        // идентификатор плагина в базе данных
        $pluginRowId = $pluginParams['rowId'];

        /** Обновление плагина в базе данных */
        /** @var \Ge\PluginManager\Model\Plugin $plugin Активная запись плагина */
        $plugin = new Model\Plugin();
        $plugin = $plugin->get($pluginRowId);
        if ($plugin === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" not found at database', [Ge::t('app', 'Plugin'), $pluginRowId])
            );
            return false;
        }

        // обновление всех полей плагина
        $plugin->ownerId     = $installConfig['ownerId'];
        $plugin->category    = $installConfig['category'];
        $plugin->name        = $installConfig['name'];
        $plugin->description = $installConfig['description'];
        $plugin->namespace   = $installConfig['namespace'];
        $plugin->path        = $installConfig['path'];
        $plugin->enabled     = 1;
        $plugin->hasSettings = (int) $plugins->sourceExists($installConfig['path'], 'Settings' . DS . 'Settings');
        $plugin->version     = $versionConfig['version'] ?? '1.0';
        $plugin->lock        = (int) ($installConfig['lock'] ?? 0);
        $plugin->updatedDate = date('Y-m-d H:i:s');
        $plugin->updatedUser = Ge::$app->user->getId();
        if (!$plugin->save()) {
            $this->addError(
                Ge::t(BACKEND, 'Error saving {0} to database', [Ge::t('app', 'Module')])
            );
            return false;
        }

        /** Обновление локализаций плагина в базе данных */
        // шаблон параметров источника (категории) транслятора плагина
        $translatorConfig = PluginManager::getTranslatePattern($installConfig['path']);
        /** @var \Ge\PluginManager\Model\PluginLocale $pluginLocale Локализация плагина */
        $pluginLocale = new Model\PluginLocale();

        // удаление добавленных ранее локализаций плагина из базы данных
        $pluginLocale->deleteFromPlugin($pluginRowId);

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка плагина нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали плагина своя категория)
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
            // если файл локализации не найден
            } catch (\Exception $error) {
                continue;
            }
            $pluginLocale->pluginId    = $pluginRowId;
            $pluginLocale->languageId  = $language['code'];
            $pluginLocale->name        = $name;
            $pluginLocale->description = $description;
            $pluginLocale->insert();
        }

        // Обновление конфигурации установленных плагинов
        /** @var PluginRegistry $registry */
        $registry = $plugins->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешного обновления плагина.
     * 
     * @param array $params Параметры конфигурации обновляемого плагина.
     * 
     * @return void
     */
    public function afterUpdate(array $params): void
    {
    }

    /**
     * Возвращает плагин для формирования интерфейса установщика.
     * 
     * Если плагин производный от другого класса, то он должен иметь метод 
     * `run(): string` (результат формирования интерфейса).
     * 
     * @return null|BaseObject
     */
    public function getWidget(): ?BaseObject
    {
        return null;
    }
}