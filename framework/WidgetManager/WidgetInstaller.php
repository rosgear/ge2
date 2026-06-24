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
use Ge\Stdlib\Component;
use Ge\Stdlib\ErrorTrait;
use Ge\Stdlib\BaseObject;
use Ge\Filesystem\Filesystem;
use Ge\Exception\InvalidArgumentException;
 
/**
 * Установщик виджета.
 * 
 * Из-за того, что установщик виджета использует backend, то для локализации 
 * сообщений класса приминается категория `BACKEND`, 
 * например: `Ge::t(BACKEND, 'Missing file "{0}" configuration parameter "{1}", ['.widget.php', 'id'])'`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager
 * @since 2.0
 */
class WidgetInstaller extends Component
{
    use ErrorTrait;

    /**
     * Локальный путь к установке виджета.
     *
     * Пример: '/rg/rg.wd.foobar'.
     * 
     * @see WidgetInstaller::configure()
     * 
     * @var string
     */
    public string $path;

    /**
     * Идентификатор удаляемого виджета.
     * 
     * Пример: 'rg.wd.foobar'.
     * 
     * @see WidgetInstaller::configure()
     * 
     * @var string
     */
    public string $widgetId = '';

    /**
     * Идентификатор установки виджета.
     * 
     * Зашифрованная строка, которая включает локальный путь виджета и 
     * его пространство имён.
     * 
     * Имеет вид: 'path,namespace'.
     * 
     * Используется моделью представления для формирования интерфейса установки 
     * виджета и передачи его параметров установки установщику.
     * 
     * @see WidgetInstaller::configure()
     * 
     * @var string
     */
    public string $installId = '';

    /**
     * Параметры конфигурации установленного (устанавливаемого) виджета.
     * 
     * @see WidgetRegistry::validateInstall()
     * @see WidgetRegistry::validateUninstall()
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
     * Возвращает информацию об установленном виджете.
     * 
     * @see WidgetRegistry::getInfo()
     * 
     * @return array
     */
    public function getInstalledInfo(): array
    {
        if (!isset($this->info)) {
            /** @var WidgetRegistry $registry */
            $registry = Ge::$app->widgets->getRegistry();
            /** @var null|array Параметры конфигурации установленного виджета */
            $info = $registry->getInfo($this->widgetId);
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Возвращает информацию об устанавливаемом виджете.
     * 
     * @see WidgetManager::getInfo()
     * 
     * @return array
     */
    public function getWidgetInfo(): array
    {
        if (!isset($this->info)) {
            $info = Ge::$app->widgets->getInfo(['path' => $this->path], true);

            // попытка добавить локализацию виджета для определения имени и описания виджета
            if ($info) {
                try {
                    $widgetId = $info['install']['id'];
                    Ge::$app->widgets->addTranslateCategory($widgetId, $info['path']);
                    $name = Ge::t($widgetId, '{name}');
                    // если есть перевод
                    if ($name !== '{name}') {
                        $info['name'] = $name;
                    }
                    $description = Ge::t($widgetId, '{description}');
                    // если есть перевод
                    if ($description !== '{description}') {
                        $info['description'] = $description;
                    }
                // если локализация не найдена
                } catch (\Exception $error) {
                }
            }

            if ($info) {
                // идентификатор установки виджета
                $info['installId'] = $this->installId;
            }
            $this->info = $info === null ? [] : $info;
        }
        return $this->info;
    }

    /**
     * Выполняет удаление виджета.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления виджета.
     */
    public function uninstall(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные виджета из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // удаляет файлы виджета
        if (!$this->uninstallFiles()) {
            return false;
        }
        // отменяет регистрацию виджета
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUninstall();
        return true;
    }

    /**
     * Проверяет виджет перед удалением.
     * 
     * @return bool
     */
    public function validateUninstall(): bool
    {
        /** @var array $info Параметры конфигурации установленного виджета */
        $info = $this->getInstalledInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Widget'), $info['id']])
            );
            return false;
        }

        if ((bool) ($info['lock'] ?? false)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Widget'), $info['id']])
            );
            return false;
        }

        $rowId = $info['rowId'] ?? null;
        if (empty($rowId)) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with the identifier "{1}" is missing in the database', [Ge::t('app', 'Widget'), $info['id']])
            );
            return false;
        }
        return true;
    }

    /**
     * Удаляет данные виджета из базы данных.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления данных 
     *     виджета из базы данных.
     */
    public function uninstallDb(): bool
    {
        return true;
    }

    /**
     * Удаляет установленные файлы.
     * 
     * @return bool Возвращает значение `false`, если была ошибка удаления файлов виджета.
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
     * Отменяет регистрацию виджета.
     * 
     * Удаляет виджет из файлов конфигурации:
     * - виджеты ".widgets.php" (.widgets.so.php),
     * и базы данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка отмены регистрации виджета.
     */
    public function unregister(): bool
    {
        // удаляет виджет из базы данных
        (new Model\Widget())->deleteByPk($this->info['rowId']);
        // удаляет локализацю виджета из базы данных
        (new Model\WidgetLocale())->deleteByPk($this->info['rowId']);

        // обновляет конфигурацию установленных видежтов
        Ge::$app->widgets->update();
        return true;
    }

    /**
     * Событие возникающие после успешного удаления виджета.
     * 
     * @return void
     */
    public function afterUninstall(): void
    {
    }

    /**
     * Демонтирует виджет из системы.
     * 
     * В отличии от {@see WidgetInstaller::uninstall()} не удаляет файлы виджета, что даёт 
     * возможность виджет переустановить.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка демонтирования виджета.
     */
    public function unmount(): bool
    {
        if (!$this->validateUninstall()) {
            return false;
        }
        // удаляет данные виджета из базы данных
        if (!$this->uninstallDb()) {
            return false;
        }
        // отменяет регистрацию виджета
        if (!$this->unregister()) {
            return false;
        }
        $this->afterUnmount();
        return true;
    }

    /**
     * Событие возникающие после успешного демонтажа виджета.
     * 
     * @return void
     */
    public function afterUnmount(): void
    {
    }

    /**
     * Выполняет установку виджета.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в установке виджета.
     */
    public function install(): bool
    {
        if (!$this->validateInstall()) {
            return false;
        }
        // добавляет данные виджета в базу данных
        if (!$this->installDb()) {
            return false;
        }
        // устанавливает файлы виджета
        if (!$this->installFiles()) {
            return false;
        }
        // регистрирует виджет
        if (($params = $this->register()) === false) {
            return false;
        }
        $this->afterInstall($params);
        return true;
    }

    /**
     * Проверяет конфигурацию виджета.
     * 
     * Ошибки при проверке будут в {@see WidgetInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateInstall(): bool
    {
        /** @var array Параметры конфигурации устанавливаемого виджета */
        $info = $this->getWidgetInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Widget'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки виджета */
        $installConfig = $info[$configName = 'install'] ?? null;
        if ($installConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Widget'), ".$configName.php"])
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

        // проверка параметров: назначение
        if ($installConfig['use'] !== BACKEND && $installConfig['use'] !== FRONTEND) {
            $this->addError(
                Ge::t(BACKEND, 'File "{0}" configuration parameter "{1}" specified incorrectly', [".$configName.php", 'use'])
            );
            return false;
        }

        /** @var WidgetRegistry $registry */
        $registry = Ge::$app->widgets->getRegistry();
        // проверка виджета в конфигурации установленных виджетов
        if ($registry->has($installConfig['id'])) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Widget'), $installConfig['id']])
            );
            return false;
        }

        // проверка виджета в базе данных установленных виджетов
        $widget = Ge::$app->widgets->selectOne($installConfig['id']);
        if ($widget !== null) {
            $this->addError(
                Ge::t(BACKEND, '{0} with specified id "{1}" already installed', [Ge::t('app', 'Widget'), $installConfig['id']])
            );
            return false;
        }

        // проверка требований к виджету
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии виджета */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Widget'), ".$configName.php"])
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

        /** Конфигурация виджета */
        // проверка параметров локализации виджета
        $translatorConfig = WidgetManager::getTranslatePattern($installConfig['path']);
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали виджета своя категория)
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
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Widget'), $locale])
                );
                return false;
            }
        }

        /** Проверка параметров на уникальность среди установленных виджетов */
        foreach ($registry->getMap() as $rowId => $registryConfig) {
            if ($installConfig['namespace'] === $registryConfig['namespace']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Widget'), 'namespace'])
                );
                return false;
            }
            if ($installConfig['path'] === $registryConfig['path']) {
                $this->addError(
                    Ge::t(BACKEND, '{0} with specified parameter "{1}" already installed', [Ge::t('app', 'Widget'), 'path'])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Добавляет данные в базу данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка добавления данных виджета 
     *     в базу данных.
     */
    public function installDb(): bool
    {
        return true;
    }

    /**
     * Устанавливает файлы виджета.
     * 
     * @return bool Возвращает значение `false`, если ошибка установки файлов виджета.
     */
    public function installFiles(): bool
    {
        return true;
    }

    /**
     * Регистрирует виджет.
     * 
     * Добавляет данные виджета в файлы конфигурации:
     * - виджеты ".widgets.php" (.widgets.so.php),
     * и базы данных.
     * 
     * @see WidgetRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      регистрации виджета. Иначе, параметры конфигурации установленного виджета.
     */
    public function register(): false|array
    {
        /** @var WidgetManager $widgets Менеджер виджетов */
        $widgets = Ge::$app->widgets;

        /** @var array|null $installConfig Параметры конфигурации установки виджета */
        $installConfig = $widgets->getConfigInstall($this->path);

        /** Добавление виджета в базу данных */
        /** @var \Ge\WidgetManager\Model\Widget $widget */
        $widget = new Model\Widget();
        $widget->setAttributes([
            'widgetId'    => $installConfig['id'],
            'widgetUse'   => $installConfig['use'],
            'category'    => $installConfig['category'],
            'name'        => $installConfig['name'],
            'description' => $installConfig['description'],
            'namespace'   => $installConfig['namespace'],
            'path'        => $installConfig['path'],
            'enabled'     => 1,
            'hasSettings' => (int) $widgets->sourceExists($installConfig['path'], 'Settings' . DS . 'Settings'),
            'lock'        => (int) ($installConfig['lock'] ?? 0),
            'createdDate' => date('Y-m-d H:i:s'),
            'createdUser' => Ge::$app->user->getId()
        ]);
        $widgetId = $widget->save();
        if ($widgetId === false) {
            $this->addError(
                Ge::t(BACKEND, 'Error adding {0} to database', [Ge::t('app', 'Widget')])
            );
            return false;
        }

        /** Добавление локализаций виджета в базу данных */
        // шаблон параметров источника (категории) транслятора виджета
        $translatorConfig = WidgetManager::getTranslatePattern($installConfig['path']);
        /** @var \Ge\WidgetManager\Model\WidgetLocale $widgetLocale Локализация виджета */
        $widgetLocale = new Model\WidgetLocale();

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка виджета нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали виджета своя категория)
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
            $widgetLocale->widgetId    = $widgetId;
            $widgetLocale->languageId  = $language['code'];
            $widgetLocale->name        = $name;
            $widgetLocale->description = $description;
            $widgetLocale->insert();
        }

        // Обновление конфигурации установленных виджетов
        /** @var WidgetRegistry $registry */
        $registry = $widgets->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешной установки виджета.
     * 
     * @param array $params Параметры конфигурации установленного виджета.
     * 
     * @return void
     */
    public function afterInstall(array $params): void
    {
    }

    /**
     * Выполняет обновление виджета.
     * 
     * @return bool Возвращает значение `false`, если была ошибка в обновлении виджета.
     */
    public function update(): bool
    {
        if (!$this->validateUpdate()) {
            return false;
        }
        // добавляет данные виджета в базу данных
        if (!$this->updateDb()) {
            return false;
        }
        // обновляет файлы виджета
        if (!$this->updateFiles()) {
            return false;
        }
        // обновляет регистрацию виджета
        if (($params = $this->updateRegister()) === false) {
            return false;
        }
        $this->afterUpdate($params);
        return true;
    }

    /**
     * Проверяет конфигурацию виджета.
     * 
     * Ошибки при проверке будут в {@see WidgetInstaller::$errors}.
     * 
     * @return bool Возвращает значение `true`, если проверка прошла успешно. Иначе, ошибка.
     */
    public function validateUpdate(): bool
    {
        /** @var array $info Параметры конфигурации установленного виджета */
        $info = $this->getWidgetInfo();
        if (empty($info)) {
            $this->addError(
                Ge::t(BACKEND, 'There is no {0} with the specified id "{1}"', [Ge::t('app', 'Widget'), $info['id']])
            );
            return false;
        }

        /** Конфигурация установки виджета */
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

        // проверка требований к виджету
        $required = $installConfig['required'];
        if ($required) {
            /** @var \Ge\Version\Compare $compare */
            $compare = Ge::$app->version->getCompare();
            if (!$compare->with($required)) {
                $this->addError($compare->getMessage());
                return false;
            }
        }

        /** Конфигурация версии виджета */
        $versionConfig = $info[$configName = 'version'];
        if ($versionConfig === null) {
            $this->addError(
                Ge::t(BACKEND, 'Missing {0} configuration file "{1}"', [Ge::t('app', 'Widget'), ".$configName.php"])
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

        /** Конфигурация виджета */
        // проверка параметров локализации виджета
        $translatorConfig = WidgetManager::getTranslatePattern($installConfig['path']);
        foreach ($installConfig['locales'] as $locale) {
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали виджета своя категория)
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
                    Ge::t(BACKEND, 'The {0} does not have the "{1}" localization file specified in the installation options', [Ge::t('app', 'Widget'), $locale])
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Обновляет данные базы данных.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления данных виджета.
     */
    public function updateDb(): bool
    {
        return true;
    }

    /**
     * Обновляет файлы виджета.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления файлов виджета.
     */
    public function updateFiles(): bool
    {
        return true;
    }

    /**
     * Обновляет регистрацию виджета.
     * 
     * Добавляет данные виджета в файлы конфигурации:
     * - виджеты ".widgets.php" (.widgets.so.php),
     * и базы данных.
     * 
     * @see WidgetRegistry::update()
     * 
     * @return false|array Возвращает значение `false`, если получена ошибка при 
     *      обновлении виджета. Иначе, параметры конфигурации установленного виджета.
     */
    public function updateRegister(): false|array
    {
        /** @var WidgetManager $widgets Менеджер виджетов */
        $widgets = Ge::$app->widgets;

        /** @var array|null $installConfig Параметры конфигурации установки виджета */
        $installConfig = $widgets->getConfigInstall($this->path);
        /** @var array|null $versionConfig Параметры конфигурации версии */
        $versionConfig = $widgets->getConfigVersion($this->path);

        /** @var array|null $widgetParams Параметры установленного виджета в реестре */
        $widgetParams = $widgets->getRegistry()->getAt($installConfig['id']);
        if ($widgetParams === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" is not in the registry', [Ge::t('app', 'Widget'), $installConfig['id']])
            );
            return false;
        }
        // идентификатор виджета в базе данных
        $widgetRowId = $widgetParams['rowId'];

        /** Обновление виджета в базе данных */
        /** @var \Ge\WidgetManager\Model\Widget $widget Активная запись виджета */
        $widget = new Model\Widget();
        $widget = $widget->get($widgetRowId);
        if ($widget === null) {
            $this->addError(
                Ge::t(BACKEND, 'The {0} with ID "{1}" not found at database', [Ge::t('app', 'Widget'), $widgetRowId])
            );
            return false;
        }

        // обновление всех полей виджета
        $widget->widgetUse   = $installConfig['use'];
        $widget->category    = $installConfig['category'];
        $widget->name        = $installConfig['name'];
        $widget->description = $installConfig['description'];
        $widget->namespace   = $installConfig['namespace'];
        $widget->path        = $installConfig['path'];
        $widget->enabled     = 1;
        $widget->hasSettings = (int) $widgets->sourceExists($installConfig['path'], 'Settings' . DS . 'Settings');
        $widget->version     = $versionConfig['version'] ?? '1.0';
        $widget->lock        = (int) ($installConfig['lock'] ?? 0);
        $widget->updatedDate = date('Y-m-d H:i:s');
        $widget->updatedUser = Ge::$app->user->getId();
        if (!$widget->save()) {
            $this->addError(
                Ge::t(BACKEND, 'Error saving {0} to database', [Ge::t('app', 'Module')])
            );
            return false;
        }

        /** Обновление локализаций виджета в базе данных */
        // шаблон параметров источника (категории) транслятора виджета
        $translatorConfig = WidgetManager::getTranslatePattern($installConfig['path']);
        /** @var \Ge\WidgetManager\Model\WidgetLocale $widgetLocale Локализация виджета */
        $widgetLocale = new Model\WidgetLocale();

        // удаление добавленных ранее локализаций виджета из базы данных
        $widgetLocale->deleteFromWidget($widgetRowId);

        /** @var \Ge\Language\AvailableLanguage $languages Установленные языки */
        $languages = Ge::$app->language->available;
        foreach ($installConfig['locales'] as $locale) {
            $language = $languages->getBy($locale, 'locale');
            // если языка виджета нет среди установленных
            if ($language === null) continue;
            try {
                // указываем переводчику использование локали $locale
                $translatorConfig['locale'] = $locale;
                // имя категории сообщений переводчика (в данном случаи для каждой локали виджета своя категория)
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
            $widgetLocale->widgetId    = $widgetRowId;
            $widgetLocale->languageId  = $language['code'];
            $widgetLocale->name        = $name;
            $widgetLocale->description = $description;
            $widgetLocale->insert();
        }

        // Обновление конфигурации установленных виджетов
        /** @var WidgetRegistry $registry */
        $registry = $widgets->getRegistry();
        $registry->update();
        return $registry->get($installConfig['id']);
    }

    /**
     * Событие возникающие после успешного обновления виджета.
     * 
     * @param array $params Параметры конфигурации обновляемого виджета.
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