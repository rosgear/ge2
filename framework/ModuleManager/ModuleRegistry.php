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
use Ge\Stdlib\Collection;
 
/**
 * Класс реестра установленных модулей приложения.
 * 
 * Реестр установленных модулей находятся в директории приложения ("./config"),
 * файл ".modules" (".modules.so").
 * 
 * Реестр хранит только основные параметры модулей, предназначенные для использования 
 * менеджером модулей. Реестр находится одновременно в базе данных и в файле реестра, 
 * что позволяет не использовать базу данных при ёё отсутствии.
 * 
 * Реестр в базе данных применяется только для установления прав доступа ролей пользователей 
 * к установленным модулям и изменению их основных параметров.
 * Изменение параметров модулей в реестре (базы данных) приводит к синхронизации с 
 * файлом реестра модулей.
 * 
 * Пример реестра установленных модулей:
 * ```php
 * [
 *     'rg.fe.api' => [
 *  *       'use'         => FRONTEND, // назначение модуля: BACKEND, FRONTEND
 *          'id'          => 'rg.fe.api', // уникальный идентификатор модуля в приложении
 *          'rowId'       => 1, //  уникальный идентификатор модуля в базе данных
 *          'enabled'     => true, // доступность (обращение к модулю через URL)
 *          'visible'     => true, // отображение интерфейса модуля (модели представления)
 *          'expandable'  => false, // расширяемость (модуль имеет плагины, виджеты и т.д.)
 *          'hasSettings' => false, // модуль имеет контроллер настроек (возможность настроить модуль)
 *          'hasInfo'     => true, // модуль имеет контроллер информации (возможность просмотра информации о модуле)
 *          'route'       => 'api', // маршрут (для формирования URL-адреса вызова модуля)
 *          'namespace'   => 'Rg\Frontend\Api', // пространство имени
 *          'path'        => '/rg/rg.fe.api', // директория модуля
 *          'name'        => 'API', // имя модуля по умолчанию (если отсутствует необходимая локализация)
 *          'description' => 'Application Programming Interface', // описание модуля по умолчанию (если отсутствует необходимая локализация),
 *          'version'     => '1.0.0' // версия модуля
 *     ],
 *     // ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class ModuleRegistry extends BaseRegistry
{
    /**
     * {@inheritdoc}
     */
    public function add(array $params, bool $updateAfter = false): bool
    {
        $module = new Model\Module($params);
        $module->createdDate = date('Y-m-d H:i:s');
        $module->createdUser = Ge::$app->user->getId();
        $result = (bool) $module->insert(false);
        if ($result && $updateAfter) {
            $this->update();
        }
        return $result;
    }

    /**
     * Возвращает параметры из файла конфигурации модуля.
     * 
     * @param string|int $id Идентификатор модуля в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * @param bool $associative Если значение `true`, возратит ассоциативный массив 
     *     параметров. Иначе коллекция параметров {@see \Ge\Stdlib\Collection} (по 
     *     умолчанию `true`).
     * 
     * @return Collection|array|null Возвращает значение `null`, если файл конфигурации с 
     *     указанным именем не существует.
     */
    public function getConfigModule(string|int $id, bool $associative = true)
    {
        $path = $this->getAt($id, 'path');
        return $path ? $this->manager->getConfigFile($path, 'module', $associative) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(array|string|int $id, ?string $type = null): string|array
    {
        // параметры конфигурации установленного модуля
        $params = is_array($id) ? $id : $this->getAt($id);
        if ($params === null) {
            // URL-путь к значкам по умолчанию
            $iconNoneUrl = Url::theme() . '/widgets/images/module';
            // URL большого и маленького значка по умолчанию
            $iconNoneSmall = $iconNoneUrl . '/module-none_small.svg';
            $iconNone      = $iconNoneUrl . '/module-none.svg';

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
     * Проверяет, видим ли (если модуль имеет интерфейс) модуль.
     * 
     * @param string|int $id Идентификатор модуля в реестре или в базе данных, 
     *     например: 123, 'rg.foobar'.
     * 
     * @return bool Если возвращает значение `false`, модуль не видим или не установлен.
     */
    public function isVisible(string|int $id): bool
    {
        $visible = $this->getAt($id, 'visible');
        return (bool) $visible;
    }

    /**
     * {@inheritdoc}
     */
    public function getListNames(bool $accessible = false): array
    {
        $result = [];
        if ($accessible) {
            $accessIds = Ge::userIdentity()->getModules(true);
        }

        /**
         * @var array $names Имена модулей с текущей локализацией. 
         * Имеют вид: `[module_id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
         */
        $names = $this->manager->selectNames();

        /**
         * @var array $names Параметры установленных модулей.
         * Имеют вид: `[module_id => [...], ...]`.
         */
        $map = $this->getMap();

        // выбираем отсортированные по имени модуль
        foreach ($names as $moduleId => $localization) {
            // в том случаи если модуль удалён а его локализации нет
            if (!isset($map[$moduleId])) continue;
            // только доступные для роли пользователя
            if ($accessible && !isset($accessIds[$moduleId])) {
                continue;
            }
            $result[$moduleId] = [
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
             * @var array Доступные пользователю модули. 
             * Имеют вид: `[module_id1 => true, module_id2 => true, ...]`.
             */
            $accessIds = Ge::userIdentity()->getModules(true);
        }

        // если с локализацией имён модулей
        if ($withNames) {
            /**
             * @var array $names Имена модулей с текущей локализацией. 
             * Имеют вид: `[module_id => ['name' => 'Имя', 'description' => 'Описание', 'permissions' => '{...}'], ...]`.
             */
            $names = $this->manager->selectNames();

            /**
             * @var array $names Параметры установленных модулей.
             * Имеют вид: `[module_id => [...], ...]`.
             */
            $map = $this->getMap();

            // выбираем отсортированные по имени модули, 
            // где $moduleId идентификатор модуля в базе данных (1, 2, 3, ...)
            foreach ($names as $moduleId => $localization) {
                // в том случаи если модуль удалён а его локализация нет
                if (!isset($map[$moduleId])) continue;

                // только доступные для роли пользователя
                if ($accessible && !isset($accessIds[$moduleId])) {
                    continue;
                }

                $info = $this->getInfo($map[$moduleId], $include);
                $info['name'] = $localization['name'];
                $info['description'] = $localization['description'];
                $result[$info[$key]] = $info;
            }
        // без локализации имён модулей
        } else {
            // где $moduleId идентификатор модуля ('ge.id'),
            // $rowId идентификатор модуля в базе данных (1, 2, 3, ...)
            foreach ($this->container as $moduleId => $configParams) {
                $info   = $this->getInfo($moduleId, $include);
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
        // параметры установленного модуля
        $params = $this->getAt($id);
        if ($params === null) {
            return [];
        }

        // если нет у модуля разрешений
        if (empty($params['permissions'])) {
            return [];
        }

        // параметры модуля
        $configParams = $this->manager->getConfigFile($params['path'], 'module', true);
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
        $modulePermissions = explode(',', $params['permissions']);
        // альтернативный перевод если разрешения пропущены в перевода
        foreach ($modulePermissions as $permission) {
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
     * Ообновляет конфигурацию установленных модулей.
     * 
     * Ообновляет файлы конфигурации приложения: 
     * - шорткоды ".shortcodes.php (.shortcodes.so.php)";
     * - маршруты запросов ".router.php (.router.so.php)";
     * - модули ".modules.php (.modules.so.php)";
     * - события ".events.php (.events.so.php)".
     *
     * @return void
     */
    public function update(): void
    {
        // все установленные модули из базы данных
        $modules = $this->manager->selectAll('moduleId');

        $this->updateRegistry($modules);
        $this->updateShortcodes($modules);
        $this->updateRoutes($modules);
        $this->updateEvents($modules);
        $this->updateLocales($modules);
    }

    /**
     * Обновляет конфигурацию событий модулей.
     *
     * @param array $modules Параметры установленных модулей.
     * 
     * @return void
     */
    public function updateEvents(array $modules): void
    {
        // убираем все события модулей
        Ge::$app->listeners->removeListeners('module');

        foreach ($modules as $moduleId => $attributes) {
            $installParams = $this->manager->getInfo(
                [
                    'use'         => $attributes['moduleUse'],
                    'path'        => $attributes['path'],
                    'route'       => $attributes['route'],
                    'hasSettings' => (int) $attributes['hasSettings'],
                    'hasInfo'     => (int) $attributes['hasInfo'],
                ],
                ['install' => true]
            );
            $events = $installParams['install']['events'] ?? [];
            if ($events) {
                Ge::$app->listeners->addListener($events, $moduleId, 'module');
            }
        }
        Ge::$app->listeners->save();
    }

    /**
     * Обновляет файл конфигурации шорткодов ".shortcodes.php".
     * 
     * @param array $modules
     * 
     * @return void
     */
    public function updateShortcodes(array $modules): void
    {
        $foundShortcodes = [];
        foreach ($modules as $moduleId => $attributes) {
            // параметры файла конфигурации модуля ".install.php"
            $install = $this->manager->getConfigInstall($attributes['path'], false);
            if ($install === null) continue;

            if ($install->shortcodes) {
                foreach ($install->shortcodes as $index => $shortcode) {
                    if (is_numeric($index))
                        $foundShortcodes[$shortcode] = $install->id;
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
        $shortcodes = $config->getValue('shortcodes', []);
        $shortcodes = array_merge($shortcodes, $foundShortcodes);
        $config->set('shortcodes', $shortcodes);
        $config->save();
    }

    /**
     * Обновляет файла конфигурации маршрутизации модулей ".router.php" (.router.so.php)
     *
     * @param array $modules Параметры установленных модулей.
     * 
     * @return void
     */
    public function updateRoutes(array $modules): void
    {
        $foundRoutes = [BACKEND => [], FRONTEND => []];

        foreach ($modules as $moduleId => $attributes) {
            // параметры файла конфигурации модуля ".install.php"
            $install = $this->manager->getConfigInstall($attributes['path'], false);
            if ($install === null) continue;

            if ($install->routes && $install->use) {
                foreach ($install->routes as $index => $route) {
                    // определяем назначение маршрута: BACKEND, FRONTEND
                    if (isset($route['use'])) {
                        $use = $route['use'];
                        unset($route['use']); // в файле конфигурации он не нужен
                    } else {
                        $use = $install->use;
                    }
    
                    if (is_numeric($index))
                        $name = $install->id;
                    else
                        // резделитель не имеет значение
                        $name = $install->id . '@' . $index;
                    // компиляция параметров для быстрой загрузки
                    $matcher = Ge::$app->router->createMatcher($route['type'], $route['options']);
                    if ($matcher) {
                        if ($compiled = $matcher->compile()) {
                            $route['options']['compiled'] = $compiled;
                        }
                    }
                    $foundRoutes[$use][$name] = $route;
                }
            }
        }

        // для backend
        $backendConfig = Ge::$app->config->get(BACKEND);
        $backendRouter = Ge::$app->config->factory($backendConfig['router']);
        // загружаем с базовой конфигурацией ".router.php"
        $backendRouter->reload(false);
        // раздел маршрутов переопределяем новыми маршрутами
        $routes = $backendRouter->getValue('routes', []);
        $routes = array_merge($routes, $foundRoutes[BACKEND]);
        $backendRouter->set('routes', $routes);
        $backendRouter->save();

        // для frontend
        $frontendConfig = Ge::$app->config->get(FRONTEND);
        $frontendRouter = Ge::$app->config->factory($frontendConfig['router']);
        // загружаем с базовой конфигурацией (".router.php") без сериализации (".router.so.php")
        $frontendRouter->reload(false);
        // раздел маршрутов переопределяем новыми маршрутами
        $routes = $frontendRouter->getValue('routes', []);
        $routes = array_merge($routes, $foundRoutes[FRONTEND]);
        $frontendRouter->set('routes', $routes);
        $frontendRouter->save();
    }

    /**
     * Обновляет реестр модулей.
     *
     * @param array $modules Параметры установленных модулей.
     * 
     * @return void
     */
    public function updateRegistry(array $modules): void
    {
        // конфигурации модулей ".modules.php"
        $config = [];
        // все установленные расширения модулей с их маршрутизацией, 
        // необходимы для быстрого обращения к расширению модулей
        $extensions = Ge::$app->extensions->collectRoutes();
        foreach ($modules as $moduleId => $attributes) {
            $config[$moduleId] = [
                'lock'        => (bool) $attributes['lock'],
                'id'          => $attributes['moduleId'],
                'use'         => $attributes['moduleUse'],
                'rowId'       => (int) $attributes['id'],
                'enabled'     => (bool) $attributes['enabled'],
                'visible'     => (bool) $attributes['visible'],
                'expandable'  => (bool) $attributes['expandable'],
                'hasSettings' => (bool) $attributes['hasSettings'],
                'hasInfo'     => (bool) $attributes['hasInfo'],
                'route'       => $attributes['route'],
                'routeAppend' => $attributes['routeAppend'],
                'path'        => $attributes['path'],
                'namespace'   => $attributes['namespace'],
                'name'        => $attributes['name'],
                'description' => $attributes['description'],
                'permissions' => $attributes['permissions'],
                'extensions'  => $extensions[$attributes['id']] ?? null,
                'version'     => $attributes['version']
            ];
        }
        // обновление файла конфигурации модулей
        $this->set($config);
        $this->save();
    }

    /**
     * Обновляет локализацию установленных модулей в базе данных.
     * 
     * Модель данных {@see Model\ModuleLocale} обновляет локализацию.
     * Данные локализации (название, описание и права доступа) модуля находятся в файлах 
     * локализации, таких как: 'text-ru_RU.php', 'text-en_GB.php' и.т. Имя файла локализации 
     * определяется настройками переводчика (translator) модуля.
     * 
     * Внимание: перед обновлением все локализаци установленных модулей в базе данных
     * будут удалены.
     * 
     * @param array $modules Параметры установленных модулей.
     * 
     * @return void
     */
    public function updateLocales(array $modules): void
    {
        /** @var Model\ModuleLocale $moduleLocale */
        $moduleLocale = new Model\ModuleLocale();
        // очищаем таблицу
        $moduleLocale->deleteAll();
        /** @var array $languages установленные языки */
        $languages = Ge::$app->language->available->getAll();
        foreach ($modules as $moduleId => $attributes) {
            // параметры файла конфигурации модуля ".install.php"
            $installConfig = $this->manager->getConfigInstall($attributes['path'], false);
            if ($installConfig === null) {
                continue;
            }
            // параметры файла конфигурации модуля ".module.php"
            $configParams = $this->manager->getConfigFile($attributes['path'], 'module', false);
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
                    $category = $attributes['moduleId'] . '.' . $locale;
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
                        if ($installConfig['id'] === 'rg.be.dashboard') {
                        }
                        // если прав доступа нет для переводчика, тогда по умолчанию
                        if (empty($permissions) || $permissions === '{permissions}') {
                            $permissions = null;
                        } else {
                            $permissions = json_encode($permissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        }
                    }
                    
                    // добавляем данные локализации модуля
                    $moduleLocale->moduleId    = $attributes['id'];
                    $moduleLocale->languageId  = $language['code'];
                    $moduleLocale->name        = $name;
                    $moduleLocale->description = $description;
                    $moduleLocale->permissions = $permissions;
                    $moduleLocale->insert();
                // если файл локализации не найден
                } catch (\Exception $error) {
                    // добавляем данные локализации модуля
                    $moduleLocale->moduleId    = $attributes['id'];
                    $moduleLocale->languageId  = $language['code'];
                    $moduleLocale->name        = $attributes['name'];
                    $moduleLocale->description = $attributes['description'];
                    $moduleLocale->permissions = $this->permissionsToTranslate($attributes['permissions'] ?: '');
                    $moduleLocale->insert();
                    continue;
                }
            }
        }
    }
}
