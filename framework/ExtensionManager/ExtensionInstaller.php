<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ExtensionManager;

use Ge;
use Ge\Db\QueriesMap;
use Ge\Stdlib\Component;
use Ge\Stdlib\ErrorTrait;
use Ge\Stdlib\BaseObject;
use Ge\Filesystem\Filesystem;
use Ge\Exception\InvalidArgumentException;
 
/**
 * Класс установки расширения модуля.
 * 
 * Из-за того, что установщик расширения модуля использует backend, то для локализации 
 * сообщений класса приминается категория `BACKEND`, 
 * например: `Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}", ['.extension.php', 'id'])'`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class ExtensionInstaller extends Component
{
    use ErrorTrait;

    /**
     * Локальный путь к установке расширения.
     *
     * Пример: '\rg\rg.foobar'.
     * 
     * @see ExtensionInstaller::configure()
     * 
     * @var string
     */
    public string $path;

    /**
     * Идентификатор удаляемого расширения.
     * 
     * Пример: 'rg.foobar'.
     * 
     * @see ExtensionInstaller::configure()
     * 
     * @var string
     */
    public string $extensionId = '';

    /**
     * Идентификатор установки расширения.
     * 
     * Зашифрованная строка, которая включает локальный путь расширения и 
     * его пространство имён.
     * 
     * Имеет вид: 'path,namespace'.
     * 
     * Используется моделью представления для формирования интерфейса установки 
     * расширения и передачи его параметров установки установщику.
     * 
     * @see ExtensionInstaller::configure()
     * 
     * @var string
     */
    public string $installId = '';

    /**
     * Параметры конфигурации установленного (устанавливаемого) расширения.
     * 
     * @see ExtensionInstaller::getInstalledInfo()
     * @see ExtensionInstaller::getInfo()
     * 
     * @var array
     */
    public array $info;

    /**
     * Карта SQL-запросов устанавливаемого модуля.
     * 
     * @see ExtensionInstaller::getQueriesMap()
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
     * Возвращает информацию об установленном расширении.
     * 
     * @see \Ge\ExtensionManager\ExtensionRegistry::getInfo()
     * 
     * @return null|array
     */
    public function getInstalledInfo(): array
    {
        if (!isset($this->info)) {
            /** @var ExtensionRegistry $registry */
            $registry = Ge::$app->extensions->getRegistry();
            /** @var null|array Параметры конфигурации установленного модуля */
            $info = $registry->getInfo($this->extensionId);
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Возвращает информацию об устанавливаемом расширении.
     * 
     * @see \Ge\ExtensionManager\ExtensionManager::getInfo()
     * 
     * @return null|array
     */
    public function getExtensionInfo(): ?array
    {
        if (!isset($this->info)) {
            $info = Ge::$app->extensions->getInfo(['path' => $this->path], true);

            // попытка добавить локализацию расширения для определения имени и описания расширения
            if ($info && $info['config']['translator']) {
                try {
                    $extensionId = $info['install']['id'];
                    Ge::$app->translator->addCategory($extensionId, $info['config']['translator']);
                    $name = Ge::t($extensionId, '{name}');
                    // если есть перевод
                    if ($name !== '{name}') {
                        $info['name'] = $name;
                    }
                    $description = Ge::t($extensionId, '{description}');
                    // если есть перевод
                    if ($description !== '{description}') {
                        $info['description'] = $description;
                    }
                // если локализация не найдена
                } catch (\Exception $error) {
                }
            }

            if ($info) {
                // идентификатор установки расширения
                $info['installId'] = $this->installId;
            }
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

   /**
     * Выполняет удаление расширения.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления расширения.
     */
    public function uninstall(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные расширения из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // удаляет файлы расширения
        if (!$this->uninstallFiles()) {
            return false;
        }
        // отменяет регистрацию расширения
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUninstall();
        return true;
    }

    /**
     * Проверяет расширение перед удалением.
     * 
     * @return bool
     */
    public function validateUninstall(): bool
    {
        /** @var null|array $info Параметры конфигурации установленного расширения */
        $info = $this->getInstalledInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Extension'), $info['id']])
            );
            return false;
        }

        if ((bool) ($info['lock'] ?? false)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Extension'), $info['id']])
            );
            return false;
        }

        $rowId = $info['rowId'] ?? null;
        if (empty($rowId)) {
            $this->addError(
                Ge::t(BACKEND, 'The extension with the identifier "{0}" is missing in the database', [$info['id']])
            );
            return false;
        }
        return true;
    }

    /**
     * Удаляет данные расширения из базы данных.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления данных 
     *     расширения из базы данных.
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
     * @return bool Возвращает значение `false`, если была ошибка удаления файлов расширения.
     * 
     * @throws \Ge\Filesystem\Exception\RemoveDirectoryException
     * @throws \Ge\Filesystem\Exception\DeleteException
     */
    public function uninstallFiles(): bool
    {
        if (!Filesystem::deleteDirectory(Ge::$app->modulePath . $this->path)) {
            $this->addError(
                Ge::t(BACKEND, 'Could not perform directory deletion "{0}"', [Ge::$app->modulePath . $this->path])
            );
            return false;
        }
        return true;
    }

   /**
     * Отменяет регистрацию расширения.
     * 
     * Удаляет расширение из файлов конфигурации:
     * - расширения ".extensions.php" (.extensions.so.php),
     * и базы данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка отмены регистрации расширения.
     */
    public function unregister(): bool
    {
        // удаляет расширение из базы данных
        (new Model\Extension())->deleteByPk($this->info['rowId']);
        // удаляет локализацю расширения из базы данных
        (new Model\ExtensionLocale())->deleteByPk($this->info['rowId']);

        // обновить конфигурацию установленных расширений
        Ge::$app->extensions->update();
        // обновить конфигурацию установленных модулей
        Ge::$app->modules->update();
        return true;
    }

    /**
     * Событие возникающие после успешного удаления расширения.
     * 
     * @return void
     */
    public function afterUninstall(): void
    {
    }

    /**
     * Демонтирует расширение из системы.
     * 
     * В отличии от {@see ExtensionInstaller::uninstall()} не удаляет файлы расширения, что даёт 
     * возможность расширение переустановить.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка демонтирования расширения.
     */
    public function unmount(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные расширения из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // отменяет регистрацию расширения
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUnmount();
        return true;
    }

    /**
     * Событие возникающие после успешного демонтажа расширения.
     * 
     * @return void
     */
    public function afterUnmount(): void
    {
    }

    /**
     * Выполняет установку расширения.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в установке расширения.
     */
    public function install(): bool
    {
        if (!$this->validateInstall()) {
            return false;
        }
        // добавляет данные расширения в базу данных
        if (!$this->installDb()) {
            return false;
        }
        // устанавливает файлы расширения
        if (!$this->installFiles()) {
            return false;
        }
        // регистрирует расширение
        if (($params = $this->register()) === false) {
            return false;
        }
        $this->afterInstall($params);
        return true;
    }

    /**
     * Проверяет конфигурацию расширения.
     * 
     * Ошибки при проверке будут в {@see ExtensionInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateInstall(): bool
    {
        /** @var array Параметры конфигурации устанавливаемого расширения */
        $info = $this->getExtensionInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Extension'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки расширения */
        $installConfig = $this->info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['id', 'moduleId', 'name', 'description', 'namespace', 'path', 'route', 'locales', 'required'];
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

        // проверка принадлежности к модулю
        if (!Ge::$app->modules->getRegistry()->has($installConfig['moduleId'])) {
            $this->addError(
                Ge::t(BACKEND, 'To install this extension, you need to install the module with the identifier "{0}"', [$installConfig['moduleId']])
            );
            return false;
        }

        /** @var \Ge\ExtensionManager\ExtensionRegistry $registry */
        $registry = Ge::$app->extensions->getRegistry();
        // проверка расширения в конфигурации установленных расширений
        if ($registry->has($installConfig['id'])) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Extension'), $installConfig['id']])
            );
            return false;
        }

        // проверка расширения в базе данных установленных расширений
        $extension = Ge::$app->extensions->selectOne($installConfig['id']);
        if ($extension !== null) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Extension'), $installConfig['id']])
            );
            return false;
        }

        // проверка требований к расширению
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии расширения */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
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

        /** Конфигурация расширения */
        $extensionConfig = $info[$configName = 'config'] ?? null;
        if ($extensionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации расширения
        $requiredExtension = ['translator', 'accessRules'];
        foreach ($requiredExtension as $parameter) {
            if (!isset($extensionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['.extension.php', $parameter])
                );
                return false;
            }
        }

        // проверка параметров локализации расширения
        $translatorConfig = $extensionConfig['translator'];
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
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Extension'), $locale])
                );
                return false;
            }
        }

        /** Проверка параметров на уникальность среди установленных расширений */
        foreach ($registry->getMap() as $rowId => $registryConfig) {
            if ($installConfig['namespace'] === $registryConfig['namespace']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Extension'), 'namespace'])
                    
                );
                return false;
            }
            if ($installConfig['path'] === $registryConfig['path']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Extension'), 'path'])
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Добавляет данные в базу данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка добавления данных расширения  
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
     * Устанавливает файлы расширения.
     * 
     * @return bool Возвращает значение `false`, если ошибка установки файлов расширения.
     */
    public function installFiles(): bool
    {
        return true;
    }

    /**
     * Регистрирует расширение.
     * 
     * Добавляет данные расширения в файлы конфигурации:
     * - расширения ".extensions.php" (.extensions.so.php),
     * и базы данных.
     * 
     * @see \Ge\ExtensionManager\ExtensionRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      регистрации расширения. Иначе, параметры конфигурации установленного расширения.
     */
    public function register():  false|array
    {
        /** @var \Ge\ExtensionManager\ExtensionManager $extension Менеджер расширений */
        $extensions = Ge::$app->extensions;

        /** @var array|null Параметры конфигурации установки расширения */
        $installConfig = $extensions->getConfigInstall($this->path);
        /** @var array|null Параметры конфигурации расширения */
        $extensionConfig = $extensions->getConfigFile($this->path, 'extension');

        /** @var null|array $modules Менеджер модулей */
        $module = Ge::$app->modules->getRegistry()->get($installConfig['moduleId']);
 
        /** Добавление расширения в базу данных */
        /** @var \Ge\ExtensionManager\Model\Extension $extension Расширение */
        $extension = new Model\Extension();
        $extension->setAttributes([
            'moduleId'    => $module['rowId'],
            'extensionId' => $installConfig['id'],
            'name'        => $installConfig['name'],
            'description' => $installConfig['description'],
            'namespace'   => $installConfig['namespace'],
            'path'        => $installConfig['path'],
            'route'       => $installConfig['route'] ?? null,
            'enabled'     => 1,
            'hasInfo'     => (int) $extensions->controllerExists($installConfig['path'], 'Info'),
            'hasSettings' => (int) $extensions->controllerExists($installConfig['path'], 'Settings'),
            'permissions' => empty($installConfig['permissions']) ? null : implode(',', $installConfig['permissions']),
            'lock'        => (int) ($installConfig['lock'] ?? 0),
            'createdDate' => date('Y-m-d H:i:s'),
            'createdUser' => Ge::$app->user->getId()
        ]);
        $extensionId = $extension->save();
        if ($extensionId === false) {
            $this->addError(
                Ge::t(BACKEND, 'Error adding {0} to database', [Ge::t('app', 'Extension')])
            );
            return false;
        }

        /** Добавление локализаций в базу данных */
        // параметры переводчика из конфигурации расширения
        $translatorConfig = $extensionConfig['translator'];
        /** @var \Ge\ExtensionManager\Model\ExtensionLocale $extensionLocale Локализация расширения */
        $extensionLocale = new Model\ExtensionLocale();

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка расширения нет среди установленных
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
            $extensionLocale->extensionId    = $extensionId;
            $extensionLocale->languageId  = $language['code'];
            $extensionLocale->name        = $name;
            $extensionLocale->description = $description;
            $extensionLocale->permissions = $permissions;
            $extensionLocale->insert();
        }

        // Обновление конфигурации установленных расширений
        /** @var \Ge\ExtensionManager\ExtensionRegistry $installed */
        $installed = $extensions->getRegistry();
        $installed->update();
        $extensionConfig = $installed->get($installConfig['id']);

        // Обновление конфигурации установленных модулей
        Ge::$app->modules->update();

        return $extensionConfig;
    }

    /**
     * Событие возникающие после успешной установки расширения.
     * 
     * @param array $params Параметры конфигурации установленного расширения.
     * 
     * @return void
     */
    public function afterInstall(array $params): void
    {
    }

    /**
     * Выполняет обновление расширения.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в обновлении расширения.
     */
    public function update(): bool
    {
        if (!$this->validateUpdate()) {
            return false;
        }
        // добавляет данные расширения в базу данных
        if (!$this->updateDb()) {
            return false;
        }
        // обновляет файлы расширения
        if (!$this->updateFiles()) {
            return false;
        }
        // обновляет регистрацию расширения
        if (($params = $this->updateRegister()) === false) {
            return false;
        }
        $this->afterUpdate($params);
        return true;
    }

    /**
     * Проверяет конфигурацию расширения.
     * 
     * Ошибки при проверке будут в {@see ExtensionInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateUpdate(): bool
    {
        /** @var array $info Параметры конфигурации устанавливаемого расширения */
        $info = $this->getExtensionInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Extension'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки расширения */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации установки
        $requiredInstall = ['id', 'moduleId', 'name', 'description', 'namespace', 'path', 'route', 'locales', 'required'];
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

        // проверка принадлежности к модулю
        if (!Ge::$app->modules->getRegistry()->has($installConfig['moduleId'])) {
            $this->addError(
                Ge::t(BACKEND, 'To install this extension, you need to install the module with the identifier "{0}"', [$installConfig['moduleId']])
            );
            return false;
        }

        // проверка требований к расширению
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии расширения */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
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

        /** Конфигурация расширения */
        $extensionConfig = $info[$configName = 'config'] ?? null;
        if ($extensionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Extension'), ".$configName.php"])
            );
            return false;
        }

        // проверка параметров конфигурации расширения
        $requiredExtension = ['translator', 'accessRules'];
        foreach ($requiredExtension as $parameter) {
            if (!isset($extensionConfig[$parameter])) {
                $this->addError(
                    Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}"', ['.extension.php', $parameter])
                );
                return false;
            }
        }

        // проверка параметров локализации расширения
        $translatorConfig = $extensionConfig['translator'];
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
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Extension'), $locale])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Обновляет данные базы данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления данных расширения.
     */
    public function updateDb(): bool
    {
        return true;
    }

    /**
     * Обновляет файлы расширения.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления файлов модуля.
     */
    public function updateFiles(): bool
    {
        return true;
    }

    /**
     * Обновляет регистрацию расширения.
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
     *      обновлении расширения. Иначе, параметры конфигурации обновленного расширения.
     */
    public function updateRegister(): false|array
    {
        /** @var \Ge\ExtensionManager\ExtensionManager $extension Менеджер расширений */
        $extensions = Ge::$app->extensions;

        /** @var array|null $installConfig Параметры конфигурации установки расширения */
        $installConfig = $extensions->getConfigInstall($this->path);
        /** @var array|null $extensionConfig Параметры конфигурации расширения */
        $extensionConfig = $extensions->getConfigFile($this->path, 'extension');
        /** @var array|null $versionConfig Параметры конфигурации версии */
        $versionConfig = $extensions->getConfigVersion($this->path);

        /** @var array|null $extParams Параметры установленного модуля в реестре */
        $extParams = $extensions->getRegistry()->getAt($installConfig['id']);
        if ($extParams === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" is not in the registry', [Ge::t('app', 'Extension'), $installConfig['id']])
            );
            return false;
        }
        // идентификатор расщирения в базе данных
        $extensionRowId = $extParams['rowId'];

        /** @var null|array $module Модуль, которому пренадлежит расширение */
        $module = Ge::$app->modules->getRegistry()->get($installConfig['moduleId']);
 
        /** Обновление расширения в базе данных */
        /** @var \Ge\ExtensionManager\Model\Extension $extension Расширение */
        $extension = new Model\Extension();
        $extension = $extension->get($extensionRowId);
        if ($extension === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" not found at database', [Ge::t('app', 'Extension'), $extensionRowId])
            );
            return false;
        }

        // обновление всех полей расширения
        $extension->moduleId    = $module['rowId'];
        $extension->name        = $installConfig['name'];
        $extension->description = $installConfig['description'];
        $extension->namespace   = $installConfig['namespace'];
        $extension->path        = $installConfig['path'];
        $extension->route       = $installConfig['route'] ?? null;
        $extension->hasInfo     = (int) $extensions->controllerExists($installConfig['path'], 'Info');
        $extension->hasSettings = (int) $extensions->controllerExists($installConfig['path'], 'Settings');
        $extension->permissions = empty($installConfig['permissions']) ? null : implode(',', $installConfig['permissions']);
        $extension->version     = $versionConfig['version'] ?? '1.0';
        $extension->lock        = (int) ($installConfig['lock'] ?? 0);
        $extension->updatedDate = date('Y-m-d H:i:s');
        $extension->updatedUser = Ge::$app->user->getId();
        if (!$extension->save()) {
            $this->addError(
                Ge::t(BACKEND, 'Error saving extension to database')
            );
            return false;
        }

        /** Обновление локализаций в базу данных */
        // параметры переводчика из конфигурации расширения
        $translatorConfig = $extensionConfig['translator'];
        /** @var \Ge\ExtensionManager\Model\ExtensionLocale $extensionLocale Локализация расширения */
        $extensionLocale = new Model\ExtensionLocale();

        // удаление добавленных ранее локализаций расширений из базы данных
        $extensionLocale->deleteFromExtension($extensionRowId);

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка расширения нет среди установленных
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
            $extensionLocale->extensionId = $extensionRowId;
            $extensionLocale->languageId  = $language['code'];
            $extensionLocale->name        = $name;
            $extensionLocale->description = $description;
            $extensionLocale->permissions = $permissions;
            $extensionLocale->insert();
        }

        // Обновление конфигурации установленных расширений
        /** @var \Ge\ExtensionManager\ExtensionRegistry $installed */
        $installed = $extensions->getRegistry();
        $installed->update();
        $extensionConfig = $installed->get($installConfig['id']);

        // Обновление конфигурации установленных модулей
        Ge::$app->modules->update();

        return $extensionConfig;
    }

    /**
     * Событие возникающие после успешного обновления расширения.
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