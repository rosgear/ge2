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
use Ge\Db\ActiveRecord;
use Ge\Mvc\Module\BaseModule;
use Ge\Router\Matcher\RouteMatch;

/**
 * Менеджер модулей предоставляет возможность создавать и обращаться к экземплярам 
 * классов модулей.
 * 
 * ModuleManager - это служба приложения, доступ к которой можно получить через 
 * `Ge::$app->modules`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class ModuleManager extends BaseManager
{
    /**
     * {@inheritdoc}
     */
    public string $callableClassName = 'Module';

    /**
     * {@inheritdoc}
     */
    public function getVersionPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'name'         => '', // название модуля
            'description'  => '', // описание модуля
            'version'      => '', // номер версии
            'versionDate'  => '', // дата версии
            'author'       => '', // имя или email автора
            'authorUrl'    => '', // URL-адрес страницы автора
            'email'        => '', // E-mail автора
            'url'          => '', // URL-адрес страницы модуля
            'license'      => '', // вид лицензии
            'licenseUrl'   => '' // URL-адрес текста лицензии
        ], $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallPattern(mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'use'          => '', // назначение модуля
            'id'           => '', // идентификатор модуля
            'name'         => '', // имя модуля
            'description'  => '', // описание модуля
            'namespace'    => '', // пространство имён модуля
            'path'         => '', // каталог модуля
            'expandable'   => false, // расширяемость модуля
            'route'        => '', // маршрут
            'routeAppend'  => '', // добавочный маршрут
            'routes'       => [], // правила маршрутизации
            'shortcodes'   => [], // подключаемые шорткоды
            'locales'      => [], // поддерживаемые локализации
            'permissions'  => [], // разрешения (права доступа)
            'required'     => []  // требования к версии модуля
        ], $params);
    }

    /**
     * Возвращает репозиторий модулей.
     *
     * @return ModuleRepository
     */
    public function getRepository(): ModuleRepository
    {
        if (!isset($this->repository)) {
            $this->repository = new ModuleRepository($this);
        }
        return $this->repository;
    }

    /**
     * Возвращает реестр установленных модулей.
     * 
     * @return ModuleRegistry
     */
    public function getRegistry(): ModuleRegistry
    {
        if (!isset($this->registry)) {
            $this->registry = new ModuleRegistry(Ge::alias('@config', DS . '.modules.php'), true, $this);
        }
        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function selectOne(string|int $id, bool $assoc = false): ActiveRecord|array|null
    {
        $module = new Model\Module();
        $module = $module->selectOne([(is_numeric($id) ? 'id' : 'module_id') => $id]);
        if ($module) {
            return $assoc ? $module->getAttributes() : $module;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function selectAll(?string $key = null, string|array $where = ''): array
    {
        $module = new Model\Module();
        return $module->fetchAll($key, $module->maskedAttributes(), $where ?: null);
    }

    /**
     * {@inheritdoc}
     */
    public function selectName(int $id): ?array
    {
        return (new Model\ModuleLocale())->fetchLocale($id);
    }

    /**
     * {@inheritdoc}
     */
    public function selectNames(?string $attribute = null, ?int $languageCode = null): ?array
    {
        return (new Model\ModuleLocale())->fetchNames($attribute, $languageCode);
    }

    /**
     * Вызывает триггер указанного модуля.
     * 
     * Если модуль доступен и имеет событие, то оно будет обработано им.
     * 
     * @param string|null $id Идентификатор установленного модуля, например, 'rg.be.foobar'.
     * @param string $event Название события.
     * @param array $args Параметры передаваемые событием.
     * 
     * @return void
     */
    public function doEvent(string $id, string $event, array $args = [])
    {
        /** @var array|null $moduleParams */
        $moduleParams = $this->getRegistry()->getAt($id);
        // если модуль доступен
        if ($moduleParams && $moduleParams['enabled']) {
            /** @var null|\Ge\Mvc\Module\BaseModule $module */
            $module = $this->get($id);
            if ($module) {
                $module->trigger($event, $args);
            }
        }
    }

    /**
     * Возвращает модуль, созданный в соответствии с маршрутом маршрутизатора.
     * 
     * @see ModuleManager::get()
     * 
     * @param RouteMatch $route Результата сопоставления маршрута модуля.
     * 
     * @return BaseModule
     * 
     * @throws Exception\InvalidArgumentException Отсутствует одно из свойств результата сопоставления.
     */
    public function getByRoute(RouteMatch $route): BaseModule
    {
        // идентификатор модуля
        if (empty($route->module)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Missing "{1}" in options to load {0}', [Ge::t('app', 'Module'), 'namespace, id'])
            );
        }

        // имя контроллера (должно быть обязательно)
        if (empty($route->controller)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Missing "{1}" in options to load {0}', [Ge::t('app', 'Module'), 'controller'])
            );
        }

        // действие контроллера (должно быть обязательно)
        if (empty($route->action)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Missing "{1}" in options to load {0}', [Ge::t('app', 'Module'), 'action'])
            );
        }

        /** @var \Ge\Mvc\Module\BaseModule $module Модуль */
        $module = $this->get($route->module);
        if ($route->extension !== null) {
            $extension = $module->extension($route->extension);
            if ($extension) {
                $extension
                    ->controller($route->controller)
                        ->action($route->action);
                return $module;
            }
        }

        $module
            ->controller($route->controller)
                ->action($route->action);
        return $module;
    }
}
