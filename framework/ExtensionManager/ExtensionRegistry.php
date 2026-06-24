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
use Ge\Helper\Url;
use Ge\ModuleManager\BaseRegistry;
 
/**
 * Класс реестра установленных расширений (модулей) приложения.
 * 
 * Реестр установленных модулей находятся в директории приложения ("./config"),
 * файл ".extensions" (".extensions.so").
 * 
 * Реестр хранит только основные параметры расширений, предназначенные для использования 
 * менеджером расширений. Реестр находится одновременно в базе данных и в файле реестра, 
 * что позволяет не использовать базу данных при ёё отсутствии.
 * 
 * Реестр в базе данных применяется только для установления прав доступа ролей пользователей 
 * к установленным расширениям и изменению их основных параметров.
 * Изменение параметров расширений в реестре (базы данных) приводит к синхронизации с 
 * файлом реестра расширений.
 * 
 * Пример реестра установленных расширений:
 * ```php
 * [
 *     'rg.references.countries' => [
 *          'lock'        => false, // системность
 *          'use'         => BACKEND, // назначение модуля: BACKEND, FRONTEND
 *          'id'          => 'rg.references.countries', // уникальный идентификатор расширения в приложении
 *          'moduleId'    => 'rg.crm.references', // уникальный идентификатор модуля расширения
 *          'rowId'       => '1', //  уникальный идентификатор записи в базе данных
 *          'enabled'     => true, // доступность (обращение к расширению через URL)
 *          'hasSettings' => false, // расширение имеет контроллер настроек (возможность настроить расширение)
 *          'hasInfo'     => true, // расширение имеет контроллер информации (возможность просмотра информации о расширении)
 *          'route'       => 'countries', // маршрут расширения
 *          'namespace'   => 'Rg\Extension\References\Countries', // пространство имён
 *          'path'        => '/rg/extension-references/countries', // каталог расширения
 *          'name'        => 'Countries', // имя расширения
 *          'description' => 'Reference of countries of the world', // описание расширения
 *     ],
 *     // ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class ExtensionRegistry extends BaseRegistry
{
    /**
     * {@inheritdoc}
     */
    public function add(array $params, bool $updateAfter = false): bool
    {
        $extension = new Model\Extension($params);
        $extension->createdDate = date('Y-m-d H:i:s');
        $extension->createdUser = Ge::$app->user->getId();
        $result = (bool) $extension->insert(false);
        if ($result && $updateAfter) {
            $this->update();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(array|string|int $id, ?string $type = null): string|array
    {
        // URL-путь к значкам по умолчанию
        $iconNoneUrl = Url::theme() . '/widgets/images/module';
        // URL большого и маленького значка по умолчанию
        $iconNoneSmall = $iconNoneUrl . '/extension-none_small.svg';
        $iconNone      = $iconNoneUrl . '/extension-none.svg';

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
        return $this->manager->getIcon($params['path'], $type, 'extension');
    }

    /**
     * {@inheritdoc}
     */
    public function getListNames(bool $accessible = false): array
    {
        $result = [];
        if ($accessible) {
            $accessIds = Ge::userIdentity()->getExtensions(true);
        }

        /**
         * @var array $names Имена расширений модуля с текущей локализацией. 
         * Имеют вид: `[extension_id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
         */
        $names = $this->manager->selectNames();

        /**
         * @var array $names Параметры конфигурации установленных расширений модуля.
         * Имеют вид: `[extension_id => [...], ...]`.
         */
        $map = $this->getMap();

        // выбираем отсортированные по имени расширения
        foreach ($names as $extensionId => $localization) {
            // в том случаи если расширение удалено а его локализация нет
            if (!isset($map[$extensionId])) continue;
            // только доступные для роли пользователя
            if ($accessible && !isset($accessIds[$extensionId])) {
                continue;
            }
            $result[$extensionId] = [
                'name'        => $localization['name'],
                'description' => $localization['description']
            ];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getListInfo(
        bool $withNames = true, 
        bool $accessible = false, 
        string $key = 'rowId', 
        bool|array $include = ['icon' => true]
    ): array
    {
        $result = [];
        if ($accessible) {
            /** 
             * @var array Доступные пользователю расширения. 
             * Имеют вид: `[extension_id1 => true, extension_id2 => true, ...]`.
             */
            $accessIds = Ge::userIdentity()->getExtensions(true);
        }

        // если с локализацией имён расширений
        if ($withNames) {
            /**
             * @var array $names Имена расширений модуля с текущей локализацией. 
             * Имеют вид: `[extension_id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
             */
            $names = $this->manager->selectNames();

            /**
             * @var array $names Параметры конфигурации установленных расширений модулей.
             * Имеют вид: `[extension_id => [...], ...]`.
             */
            $map = $this->getMap();

            // выбираем отсортированные по имени расширения, 
            // где $extensionId идентификатор расширения в базе данных (1, 2, 3, ...)
            foreach ($names as $extensionId => $localization) {
                // в том случаи если расширение удалено а его локализация нет
                if (!isset($map[$extensionId])) continue;
                // только доступные для роли пользователя
                if ($accessible && !isset($accessIds[$extensionId])) {
                    continue;
                }
                $info = $this->getInfo($map[$extensionId], $include);
                $info['name'] = $localization['name'];
                $info['description'] = $localization['description'];
                $result[$info[$key]] = $info;
            }
        // без локализации имён расширений
        } else {
            // где $extensionId идентификатор расширения ('extension.id'),
            // $rowId идентификатор расширения в базе данных (1, 2, 3, ...)
            foreach ($this->container as $extensionId => $configParams) {
                $info  = $this->getInfo($extensionId, $include);
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
     * {@inheritdoc}
     */
    public function getTranslatedPermissions(string|int $id, ?string $locale = null): array
    {
        // параметры конфигурации установленного модуля
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            return [];
        }
        // если нет у модуля разрешений
        if (empty($params['permissions'])) {
            return [];
        }
        // параметры конфигурации модуля
        $configParams = $this->manager->getConfigFile($params['path'], 'extension', true);
        if ($configParams === null) {
            return [];
        }
        // параметры конфигурации переводчика
        $translator = $configParams['translator'];
        if ($locale === null) {
            $locale = Ge::$app->language->locale;
        }
        $translator['locale'] = $locale;
        try {
             // категория сообщений переводчика (идент. модуля + имя локали)
            $category = $params['id'] . '.' . $locale;
            Ge::$app->translator->addCategory($category, $translator);
            // получаем разрешения в нужной локализации
            $permissions = Ge::t($category, '{permissions}');
            // если нет перевода
            if ($permissions === '{permissions}') {
                $permissions = [];
            }
        // если файл локализации не найден
        } catch (\Exception $error) {
            $permissions = [];
        }
        $extPermissions = explode(',', $params['permissions']);
        // альтернативный перевод, если разрешения пропущены в переводе
        foreach ($extPermissions as $permission) {
            if (!isset($permissions[$permission])) {
                // название и описание
                $permissions[$permission] = [ucfirst($permission), ''];
            }
        }
        // особые разрешения (info, settings, recordRls, writeAudit, viewAudit) менеджера данных для текущей локализации
        $specPermissions  = Ge::t('backend', '{dataManagerPermissions}');
        $permissions = $permissions ? array_merge($permissions, $specPermissions) : $specPermissions;
        return $permissions;
    }

    /**
     * Ообновляет конфигурацию установленных расширений.
     * 
     * Ообновляет файлы конфигурации приложения: 
     * - шорткоды ".shortcodes.php" (.shortcodes.so.php);
     * - расширения ".extensions.php (.extensions.so.php)".
     * - события ".events.php" (.events.so.php)".
     *
     * @return void
     */
    public function update(): void
    {
        // все установленные расширения из базы данных
        $extensions = $this->manager->selectAll('extensionId');

        $this->updateRegistry($extensions);
        $this->updateShortcodes($extensions);
        $this->updateLocales($extensions);
        $this->updateEvents($extensions);
    }

    /**
     * Обновляет реестр расширений.
     *
     * @param array $extensions Параметры установленных расширений.
     * 
     * @return void
     */
    public function updateRegistry(array $extensions): void
    {
        // карта идентификаторов модулей с их параметрами конфигурации
        $modules = Ge::$app->modules->getRegistry()->getMap();
        // конфигурации расширений ".extensions.php"
        $config = [];
        foreach ($extensions as $extensionId => $attributes) {
            /** @var array|null Параметры модуля которому принадлежит расширение */
            $module = $modules[$attributes['moduleId']] ?? null;
            if ($module === null) {
                continue;
            }

            $config[$extensionId] = [
                'use'         => $module['use'],
                'lock'        => (bool) $attributes['lock'],
                'id'          => $attributes['extensionId'],
                'rowId'       => (int) $attributes['id'],
                'moduleRowId' => (int) $attributes['moduleId'],
                'enabled'     => (bool) $attributes['enabled'],
                'hasSettings' => (bool) $attributes['hasSettings'],
                'hasInfo'     => (bool) $attributes['hasInfo'],
                'menu'        => (bool) $attributes['menu'],
                'baseRoute'   => $module['route'] . '/' . $attributes['route'],
                'route'       => $attributes['route'],
                'path'        => $attributes['path'],
                'namespace'   => $attributes['namespace'],
                'name'        => $attributes['name'],
                'description' => $attributes['description'],
                'permissions' => $attributes['permissions'],
                'version'     => $attributes['version']
            ];
        }
        // обновление файла конфигурации расширений
        $this->set($config);
        $this->save();
    }

    /**
     * Обновляет конфигурацию событий расширений.
     *
     * @param array $extensions Параметры установленных расширений.
     * 
     * @return void
     */
    public function updateEvents(array $extensions): void
    {
        // убираем все события расширений
        Ge::$app->listeners->removeListeners('extension');

        foreach ($extensions as $extensionId => $attributes) {
            $installParams = $this->manager->getInfo($attributes, ['install' => true]);
            $events = $installParams['install']['events'] ?? [];
            if ($events) {
                Ge::$app->listeners->addListener($events, $extensionId, 'extension');
            }
        }
        Ge::$app->listeners->save();
    }

    /**
     * Обновляет файл конфигурации шорткодов ".shortcodes.php".
     * 
     * @param array<string, array<string, mixed>> $extensions Атрибуты расширений.
     * 
     * @return void
     */
    public function updateShortcodes(array $extensions): void
    {
        $foundShortcodes = [];
        foreach ($extensions as $extensionId => $attributes) {
            // параметры файла конфигурации виджета ".install.php"
            $install = $this->manager->getConfigInstall($attributes['path'], false);
            if ($install === null) continue;

            if ($install->shortcodes) {
                foreach ($install->shortcodes as $index => $shortcode) {
                    // если указано как ['tag1', 'tag2'...]
                    if (is_numeric($index))
                        $foundShortcodes[$shortcode] = ['extension' => $install->id];
                    else
                        $foundShortcodes[$index] = $shortcode;
                }
            }
        }

        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->shortcodes->config;
        // загружаем с базовой конфигурацией ".shortcodes.php"
        $config->reload(false);

        // раздел имён шорткодов
        $shortcodes = $config->getValue('shortcodes', []);
        $shortcodes = array_merge($shortcodes, $foundShortcodes);
        $config->set('shortcodes', $shortcodes);
        $config->save();
    }

    /**
     * Обновляет локализацию установленных расширений в базе данных.
     * 
     * Модель данных {@see Model\ExtensionLocale} обновляет локализацию.
     * Данные локализации (название, описание и права доступа) расширения находятся в файлах 
     * локализации, таких как: 'text-ru_RU.php', 'text-gb_GB.php' и.т. Имя файла локализации 
     * определяется настройками переводчика (translator) расширения.
     * 
     * Внимание: перед обновлением все локализаци установленных расширений в базе данных
     * будут удалены.
     * 
     * @param array $extensions Параметры установленных расширений.
     * 
     * @return void
     */
    public function updateLocales(array $extensions): void
    {
        /** @var Model\ExtensionLocale $extensionLocale */
        $extensionLocale = new Model\ExtensionLocale();
        // очищаем таблицу
        $extensionLocale->deleteAll();
        /** @var array $languages Установленные языки */
        $languages = Ge::$app->language->available->getAll();
        foreach ($extensions as $extensionId => $attributes) {
            if (empty($attributes['path'])) {
                continue;
            }
            // параметры файла конфигурации модуля ".install.php"
            $installConfig = $this->manager->getConfigInstall($attributes['path'], false);
            if ($installConfig === null) {
                continue;
            }
            // параметры файла конфигурации модуля ".extension.php"
            $configParams = $this->manager->getConfigFile($attributes['path'], 'extension', false);
            // такого не должно быть, но проверим
            if ($configParams === null || !isset($configParams['translator'])) {
                continue;
            }
            // параметры переводчика из конфигурации модуля
            $translator = $configParams['translator'];
            foreach ($languages as $locale => $language) {
                try {
                    // указываем переводчику использование локали $locale
                    $translator['locale'] = $locale;
                    // имя категории сообщений переводчика (в данном случаи для каждой локали модуля своя категория)
                    $category = $attributes['extensionId'] . '.' . $locale;
                    Ge::$app->translator->addCategory($category, $translator);
                    $name = Ge::t($category, '{name}');
                    // если названия нет для переводчика, тогда по умолчанию
                    if ($name === '{name}') {
                        $name = $installConfig['name'];
                    }
                    $description = Ge::t($category, '{description}');
                    // если описания нет для переводчика, тогда по умолчанию
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
                    // добавляем данные локализации модуля
                    $extensionLocale->extensionId = $attributes['id'];
                    $extensionLocale->languageId  = $language['code'];
                    $extensionLocale->name        = $name;
                    $extensionLocale->description = $description;
                    $extensionLocale->permissions = $permissions;
                    $extensionLocale->insert();
                // если файл локализации не найден
                } catch (\Ge\I18n\Exception\PatternNotLoadException $error) {
                    // добавляем данные локализации модуля
                    $extensionLocale->extensionId = $attributes['id'];
                    $extensionLocale->languageId  = $language['code'];
                    $extensionLocale->name        = $attributes['name'];
                    $extensionLocale->description = $attributes['description'];
                    $extensionLocale->permissions = $this->permissionsToTranslate($attributes['permissions'] ?: '');
                    $extensionLocale->insert();
                    continue;
                }
            }
        }
    }
}