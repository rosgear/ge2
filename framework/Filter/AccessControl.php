<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filter;

use Ge;
use Closure;
use Ge\User\User;
use Ge\Stdlib\Behavior;
use Ge\Stdlib\Component;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Controller\BaseController;
use Ge\Mvc\Module\Exception\ForbiddenHttpException;

/**
 * AccessControl обеспечивает простой контроль доступа на основе набора правил.
 * 
 * AccessControl - это фильтр действий. Он проверит свои правила {@see AccessControl::$rules}, 
 * чтобы найти первое правило, которое соответствует текущим переменным контекста 
 * (таким как пользователь, разрешения (permission)). Правило сопоставления будет 
 * определять, разрешить или запретить доступ к запрошенному действию контроллера. 
 * Если ни одно правило не соответствует, в доступе будет отказано.
 * 
 * Чтобы использовать AccessControl, объявите его в методе `behavior()` вашего класса 
 * контроллера или модуля. Например, следующие объявления позволят аутентифицированным 
 * пользователям получить доступ к действиям: "создать", "обновить", "просмотреть" свой 
 * профиль c разрешением "read/write" и запретить всем другим пользователям доступ к этим 
 * действиям.
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'access' => [
 *             'class' => '\Ge\Filter\AccessControl',
 *             'rules' => [
 *                 // страница авторизации доступна для всех
 *                 [
 *                     'allow',
 *                     'controllers' => ['Index'],
 *                 ],
 *                 // для авторизованных пользователей 
 *                 [
 *                     'allow',
 *                     'controllers' => [
 *                         'Profile' => ['create', 'update', 'view']
 *                     ],
 *                     'permission'  => 'read/write',
 *                     'users'       => ['@frontend']
 *                 ],
 *                 //  для всех остальных, доступа нет
 *                 [
 *                      'deny',
 *                      'exception' => 404
 *                 ],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filter
 * @since 2.0
 */
class AccessControl extends Behavior
{
    /**
     * Проверяет доступ модуля к своему расширению.
     * 
     * Для доступа, необходимо, чтобы модуль имел разрешение "extension".
     * 
     * @see AccessControl::beforeAccess()
     * 
     * @var bool
     */
    public bool $checkParentAccess = false;

    /**
     * Класс проверяющий доступ на основе указанных правил.
     * 
     * @var string
     */
    public string $rulesClass = '\Ge\Filter\AccessRules';

    /**
     * Массив правил определяющих доступ пользователя к действиям 
     * контроллеров.
     * 
     * @var array
     */
    public array $rules = [];

    /**
     * Пользователь.
     * 
     * @var User
     */
    public User $user;

    /**
     * Обратный вызов, который будет вызываться, если в доступе будет отказано текущему 
     * пользователю.
     * 
     * Если не установлен или же результат вызова `true`, то будет вызван {@see AccessControl::denyAccess()}.
     * 
     * Сигнатура обратного вызова должна быть следующей:
    * ```php
     * function ($controller, $actionName)
     * ```
     * где, `$controller` - текущи объект контроллера, а `$actionName` - текущее действие.
     * 
     * @var Closure
     */
    public ?Closure $denyCallback = null;

    /**
     * Объект проверяющий доступ.
     * 
     * @see AccessControl::getAccessRules()
     * 
     * @var BaseObject|null
     */
    protected ?BaseObject $_accessRules = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (!isset($this->user)) {
            $this->user = Ge::$services->getAs('user');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Component $owner): void
    {
        $this->owner = $owner;
        $owner->on($owner::EVENT_BEFORE_RUN, [$this, 'beforeAccess']);
    }

    /**
     * {@inheritdoc}
     */
    public function detach(): void
    {
        if ($this->owner) {
            $this->off($this->owner::EVENT_BEFORE_RUN, [$this, 'beforeAccess']);
            $this->owner = null;
        }
    }

    /**
     * Этот метод вызывается прямо перед перед запуском владельца (модуля или 
     * контроллера).
     * 
     * @param BaseController $controller Текущий контроллер.
     * @param string $actionName Текущее действие контроллера.
     * @param bool $isAllowed Результат определяется в этом методе и вернётся для контроллера в 
     *    {@see \Ge\Mvc\Controller\BaseController::beforeRun()}, а для модуля в 
     *    {@see \Ge\Mvc\Module\BaseModule::beforeRun()} и станет результатом выполнения
     *    их методов.
     * 
     * @return void
     */
    public function beforeAccess(BaseController $controller, string $actionName, bool &$isAllowed): void
    {
        if ($this->checkParentAccess) {
            if (!$controller->module->parent->getPermission()->canExtension()) {
                $this->denyAccess();
            }
        }
        /** @var \Ge\Filter\AccessRules $access */
        $access = $this->getAccessRules($controller->getShortClass(), $actionName);
        $isAllowed = $access->match();
        if (!$isAllowed) {
            if ($this->denyCallback instanceof Closure) {
                if ($this->denyCallback->call($this, $controller, $actionName))
                    $this->denyAccess();
            } else {
                $this->denyAccess();
            }
        }
    }

    /**
     * Возвращает объект проверяющий доступ.
     * 
     * Для повторного вызова метода необходимо указать `$controllerName = null` и 
     * `$actionName = null`.
     * 
     * @see AccessControl::$_accessRules
     * 
     * @param null|string $controllerName Имя контроллера для которого выполняется 
     *     проверка доступа. 
     * @param null|string $actionName Имя действия для которого выполняется проверка 
     *     доступа.
     * 
     * @return BaseObject
     */
    public function getAccessRules(?string $controllerName = null, ?string $actionName = null)
    {
        if ($this->_accessRules === null) {
            if ($this->owner instanceof BaseController) {
                $module = $this->owner->getModule();
            } else {
                $module = $this->owner;
            }
            $this->_accessRules = Ge::createObject(
                $this->rulesClass, $this->rules, $module, $controllerName, $actionName
            );
        }
        return $this->_accessRules;
    }

    /**
     * Запрещает доступ пользователю.
     * 
     * По умолчанию перенаправит пользователя на страницу авторизации если он гость.
     * Если пользователь уже вошел в систему, будет выдано исключение 403 HTTP.
     * 
     * @return void
     * 
     * @throws ForbiddenHttpException Запрет на выполнения действия.
     */
    protected function denyAccess(): void
    {
        if ($this->user->isGuest()) {
            $this->user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Ge::t('app', 'You are not allowed to perform this action'));
        }
    }
}
