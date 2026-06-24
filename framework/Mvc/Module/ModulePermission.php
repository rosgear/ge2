<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @see https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Module;

use Ge;
use Ge\User\User;
use Ge\Mvc\Module\BaseModule;

/**
 * Класс Разрешения, определяющий доступ к действию контроллера модуля.
 * 
 * Особые параметры разрешения, определяющие доступ к модулю, указываются в конфигурации
 * {@see ModulePermission::$config}. Параметры конфигурации:
 * - 'verify' (bool), выполнять проверку {@see ModulePermission::hasVerification()};
 * - 'excludeAction' (array<string, bool>), действия, которые не надо проверять;
 * - 'actionsAliases' (array<string, string>), имена действий с их псевдонимами.   
 * Такие параметры применяются только для проверки разрешения через {@see ModulePermission::checkAccess()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Module
 * @since 2.0
 */
class ModulePermission
{
    /**
     * @var string Разрешение пользователю полного доступа.
     */
    public const PERMISSION_ANY = 'any';

    /**
     * @var string Разрешение пользователю доступа к расширению модуля.
     */
    public const PERMISSION_EXTENSION = 'extension';

    /**
     * Особые параметры конфигурации проверки разрешения. 
     * 
     * Такие параметры указываются в файле конфигурации модуля (раздел "permission").
     * 
     * @var array
     */
    protected array $config;

    /**
     * Модуль.
     * 
     * @var BaseModule
     */
    protected BaseModule $module;

    /**
     * Пользователь.
     * 
     * @var User
     */
    protected User $user;

    /**
     * Доступные разрешения для текущего пользователя.
     * 
     * @see ModulePermission::isAllow()
     * 
     * @var array
     */
    protected array $_allowed = [];

    /**
     * Конструктор класса.
     * 
     * @param BaseModule $module Модуль.
     * 
     * @return void
     */
    public function __construct(BaseModule $module)
    {
        $this->module = $module;
        $this->config = $module->getConfigParam('permission', []);
        $this->user   = Ge::$services->getAs('user');
    }

    /**
     * Возвращает особые параметры конфигурации проверки разрешения.
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Возвращает значение особого параметра конфигурации проверки разрешения.
     * 
     * @param mixed $name Название параметра.
     * @param bool $default Значение по умолчанию если параметр отсутствует 
     *     (по умолчанию `false`).
     * 
     * @return mixed
     */
    public function get(string $name, mixed $default = false): mixed
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return $default;
    }

    /**
     * Проверяет, имеются ли параметры конфигурации для проверки разрешения.
     * 
     * @return bool
     */
    public function hasConfig() :bool
    {
        return !empty($this->config);
    }

    /**
     * Проверяет, необходимо ли использовать особые параметры конфигурации для проверки 
     * разрешения.
     * 
     * @return bool
     */
    public function hasVerification(): bool
    {
        return $this->get('verify');
    }

    /**
     * Возращает имя разрешения из указанного действия.
     * 
     * Результат: `<идентификатор модуля>.<действие>`.
     * 
     * @param string $action Название действия.
     * 
     * @return string
     */
    public function getPermissionName(string $action): string
    {
        return $this->module->id . '.' . $action;
    }

    /**
     * Выполняет проверку доступа к модулю по указанному разрешению.
     * 
     * Проверя выполняется относительно роли пользователя, которая получена из слияния 
     * всех разрешений всех ролей пользователей к которым имеет доступ пользователь.
     * 
     * @param string $permission Имя разрешения.
     * 
     * @return bool
     */
    public function isGranted(string $permission): bool
    {
        return $this->user->isGranted($permission);
    }

    /**
     * Выполняет проверку доступа к модулю по указанному действиям контроллера.
     * 
     * @param array<int, string> $actions Действия контроллера.
     * 
     * @return bool
     */
    public function isAllow(...$actions): bool
    {
        /** @var string $action Действие контроллера */
        foreach ($actions as $action) {
            // если проверка была ранее
            if (isset($this->_allowed[$action])) {
                if ($this->_allowed[$action]) {
                    return true;
                }
            } else {
                if ($this->_allowed[$action] = $this->isGranted($this->getPermissionName($action))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Проверяет, является ли действие с указанным именем исключением.
     * 
     * Для исключений не проверяются разрешения.
     * 
     * @param mixed $name Имя действия контроллера.
     * 
     * @return bool
     */
    public function isExcludeAction(string $name): bool
    {
        $exclude = $this->get('excludeAction');
        return isset($exclude[$name]) ? $exclude[$name] : false;
    }

    /**
     * Возвращает имя действия контроллера по его псевдониму.
     * 
     * @param string $name Псевдоним действия.
     * 
     * @return string
     */
    public function getActionAlias(string $name): string
    {
        $aliases = $this->get('actionsAliases', false);
        if ($aliases) {
            return isset($aliases[$name]) ? $aliases[$name] : $name;
        }
        return $name;
    }

    /**
     * Проверяет разрешение пользователя на выполнение действия относительно модуля 
     * с учётом его настроек.
     * 
     * Проверку разрешения выполняет контроллер {@see \Ge\Mvc\Controller\Controller::accessAction()}.
     * 
     * @param string $name Имя действия.
     * 
     * @return bool
     */
    public function checkAccess(string $name): bool
    {
        if ($this->hasConfig()) {
            // если необходимо проверять
            if ($this->hasVerification()) {
                // не проверять для него разрешения
                if ($this->isExcludeAction($name)) return true;
                // псевдоним действия если он есть
                $name = $this->getActionAlias($name);

                return $this->isAllow($name);
            }
        }
        return true;
    }

    /**
     * @var bool Разрешение пользователю на полный доступ.
     */
    protected $_prmAny;

    /**
     * Проверяет, есть ли у пользователя полный доступ.
     * 
     * @return bool Возвращает значение `true`, если пользователь имеет полный доступ.
     */
    public function canAny(): bool
    {
        if (!isset($this->_prmAny)) {
            $this->_prmAny = $this->user->isGranted($this->module->id . '.' . self::PERMISSION_ANY);
        }
        return $this->_prmAny;
    }

    /**
     * @var bool Разрешение пользователю на доступ расширению модуля.
     */
    protected bool $_prmExtension;

    /**
     * Проверяет, есть ли у пользователя доступ к расширениям модуля.
     * 
     * @return bool Возвращает значение `true`, если пользователь имеет доступ к 
     *     расширениям модуля.
     */
    public function canExtension(): bool
    {
        if (!isset($this->_prmExtension)) {
            $this->_prmExtension = $this->user->isGranted($this->module->id . '.' . self::PERMISSION_EXTENSION);
        }
        return $this->_prmExtension;
    }
}
