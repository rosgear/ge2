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
use Ge\Exception;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Module\BaseModule;

/**
 * Доступ пользователя к действию контроллера по указанным правилам.
 * 
 *  Правило доступа имеют вид:
 * ```php
 * return [
 *     [ // разрешение на запись
 *         'allow',
 *         'controllers' => [
 *              'Form' => ['create', 'read', 'someUpdate', 'someDelete'],
 *              'Grid' => ['data'],
 *          ],
 *          'permission'  => 'write',
 *          'users'       => ['@backend'],
 *     ],
  *     [ // разрешение на чтение
 *         'allow',
 *         'controllers' => [
 *              'Form' => ['read'],
 *              'Grid' => ['data'],
 *          ],
 *          'permission'  => 'read',
 *          'users'       => ['@backend'],
 *     ],
 *     [ // для всех остальных, доступа нет
 *          'deny'
 *     ]
 * ];
 * ```
 * где, параметры правила:
 * - `allow` (`'allow' => true`), правило необходимо проверить на доступ пользователю. 
 *   Иначе, `deny`, проверить на запрет доступа пользователю;
 * - `controllers`, проверяет соответствие текущего имени контроллера с указанными  
 *   {@see AccessRules::matchController()};
 * - `actions`, проверяет соответствие текущего действия контроллерами с указанными
 *   {@see AccessRules::matchAction()};
 * - `permission`, проверяет, соответствует ли указанное разрешение текущему пользователю
 *   {@see AccessRules::matchPermission()};
 * - `users`, проверяет, соответствует ли одна из указанных сторон авторизации, стороне 
 *   авторизации текущего пользователя {@see AccessRules::matchUser()};
 * - `exception`, параметры вызова исключения пот отказе досутпа {@see AccessRules::$defaultException}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filter
 * @since 2.0
 */
class AccessRules extends BaseObject
{
    /**
     * Текущее действие контроллера для которого выполняется проверка доступа.
     * 
     * @var string
     */
    protected string $actionName;

    /**
     * Текущее имя контроллер для которого выполняется проверка доступа.
     * 
     * @var string
     */
    protected string $controllerName;

    /**
     * Текущий модуль контроллера.
     * 
     * @var BaseModule
     */
    protected BaseModule $module;

    /**
     * Правила доступа.
     * 
     * @var array
     */
    protected array $rules = [];

    /**
     * Шаблон для вызова исключения с указанными параметрами при отсутствии доступа.
     * 
     * Обязательные параметры шаблона:
     * - `statusCode`, код HTTP-статуса;
     * - `message`, сообщение об ошибке.
     * 
     * @see AccessRules::callException
     * 
     * @var array
     */
    protected array $defaultException = [
        'statusCode' => 403,
        'message'    => 'You are not allowed to perform this action.'
    ];

    /**
     * Конструктор класса.
     * 
     * @param array $rules Правила доступа.
     * @param BaseModule $module Модуль.
     * @param string $controllerName Имя контроллера для которого выполняется проверка доступа. 
     * @param string $actionName Имя действия для которого выполняется проверка доступа.
     * 
     * @return void
     */
    public function __construct(array $rules, BaseModule $module, string $controllerName, string $actionName)
    {
        $this->rules = $rules;
        $this->module = $module;
        $this->controllerName = $this->normalizeControllerName($controllerName);
        $this->actionName     = $this->normalizeActionName($actionName);
    }

    /**
     * Нормализация имени контроллера.
     * 
     * В правилах, имена контроллеров должны иметь нотацию "CamelCase".
     * Пример: `foobar => FooBar`
     * 
     * @param string $name Имя контроллера.
     * 
     * @return string|null
     */
    protected function normalizeControllerName(string $name): ?string
    {
        if ($name === 'IndexController' || $name === '') {
            return 'Index';
        }
        return $name;
    }

    /**
     * Нормализация имени действия контроллера.
     * 
     * В правилах, имена действий контроллеров должны иметь нотацию "lowerCamelCase".
     * Пример: `foobar => fooBar`
     * 
     * @param string $name Имя действия контроллера.
     * 
     * @return string|null
     */
    protected function normalizeActionName(string $name): ?string
    {
        return $name;
    }

    /**
     * Проверяет соответствие текущего имени контроллера с указанными имена контроллеров.
     * 
     * @param array $controllers Массив имён контроллеров с их действиями.
     *     Массив может иметь вид:
     *     ```php
     *     ['Foo' => ['create', 'read', 'someUpdate', 'someDelete'], 'Bar' => ['create'], 'FooBar'];
     *     ```
     * @param bool $default Возвращаемое значение по умолчанию. Это значение будет 
     *     возвращено если массив `$controllers` будет пуст.
     * 
     * @return bool Если true, текущий контроллер {@see AccessRules::$controllerName} 
     *     находится в указанном массиве контроллеров `$controllers` и/или текущее действие 
     *     {@see AccessRules::$actionName} присутствует массиве действий контроллера.
     */
    public function matchController(array $controllers, bool $default): bool
    {
        if ($controllers) {
            foreach ($controllers as $index => $name) {
                // если массив действий контроллера, где `$name = ['foo', 'bar'...]`
                if (is_array($name)) {
                    if ($index === $this->controllerName) {
                        return $this->matchAction($name, $default);
                    }
                // если имя контроллера
                } else {
                    if ($name === $this->controllerName) {
                        return true;
                    }
                }
            }
            return false;
        }
        return $default;
    }

    /**
     * Проверяет соответствие текущего действия с указанными имена действий.
     * 
     * @param string[] $actions Массив действий контроллера.
     *     Массив имет вид:
     *     ```php
     *     ['create', 'read', 'someUpdate', 'someDelete'];
     *     ```
     * @param bool $default Возвращаемое значение по умолчанию. Это значение будет 
     *     возвращено если массив `$actions` будет пуст.
     * 
     * @return bool Если true, текущее действие {@see AccessRules::$actionName} присутствует 
     *     в указанном массиве действий `$actions`.
     */
    public function matchAction(array $actions, bool $default): bool
    {
        if ($actions) {
            return in_array($this->actionName, $actions);
        }
        return $default;
    }

    /**
     * Проверяет, соответствует ли указанное разрешение текущему пользователю.
     * 
     * @param array|string[] $permission Разрешение или массив разрешений.
     * @param bool $default Возвращаемое значение по умолчанию. Это значение будет 
     *     возвращено в том случае, если не указан модуль {@see AccessRules::$module}.
     * 
     * @return bool Если true, разрешение доступно для пользователя. Иначе, нет.
     */
    public function matchPermission($permission, bool $default): bool
    {
        if ($permission && $this->module !== null) {
            return $this->module->getPermission()->isAllow($permission);
        }
        return $default;
    }

    /**
     * Проверяет, соответствует ли одна из указанных сторон авторизации, стороне 
     * авторизации текущего пользователя.
     * 
     * @param string[] $users Массив пользователей, которые должны быть авторизованы 
     *     на одной из сторон приложения (frontend, backend). Имеет вид:
     *     ```php
     *     ['*', '@frontend', '@backend']
     *     ```
     *     где значения:
     *     - `*`, для всех пользователей, которые не авторизованы;
     *     - `@frontend`, если пользователь авторизован тольно на frontend стороне;
     *     - `@backend`, если пользователь авторизован тольно на backend стороне.
     * 
     * @param bool $default Возвращаемое значение по умолчанию. Это значение будет 
     *     возвращено в том случае, если массив `$users` пуст.
     * 
     * @return bool Если значение true`, сторона авторизации текущего пользователя соответствует 
     *     одной из указанных сторон.
     */
    public function matchUser(array $users, bool $default): bool
    {
        foreach($users as $user) {
            // для посетителей
            if ($user === '*') {
                return !Ge::hasUserIdentity();
            } else
            // для пользователей frontend
            if ($user === '@frontend') {
                return Ge::hasUserIdentity(FRONTEND_SIDE_INDEX);
            } else
            // для пользователей backend
            if ($user === '@backend') {
                return Ge::hasUserIdentity(BACKEND_SIDE_INDEX);
            }
        }
        return $default;
    }

    /**
     * Вызов исключения (об ограничении доступа) по указанным параметрам.
     * 
     * В том случае если правило имеет параметр "exception", то будет вызвано 
     * прерывание выполнения проверки остальных правил.
     * 
     * Если $exception имеет тип `int` или `string`, то будет вызвано исключение с 
     * параметрами по умолчанию {@see AccessRules::$defaultException}.
     * 
     * @param callable|mixed $exception Исключение может иметь тип:
     *     - `int`, код HTTP-статуса (пример: 403);
     *     - `string`, сообщение об ошибке (пример: "You are not allowed to perform this action");
     *     - `array`, параметры создания объекта {@see \Ge::createObject()}.  
     *       Пример: `['\Ge\Exception\ForbiddenHttpException', 'You are not allowed to perform this action']`;
     *     - `callable`, анонимная функция.  
     *       Пример: `fnction () { throw new \Ge\Exception\ForbiddenHttpException() }`.
     * 
     * @return void
     */
    public function callException($exception): void
    {
        if (is_callable($exception)) {
            $exception();
        }
        if (is_array($exception)) {
            throw Ge::createObject($exception);
        }

        $default = $this->defaultException;
        // если указан HTTP-статус
        if (is_int($exception)) {
            $default['statusCode'] = $exception;
        // если указано сообщение
        } else
        if (is_string($exception)) {
            $default['message'] = $exception;
        }
        
        if ($default['statusCode'] === 403) {
            throw new Exception\ForbiddenHttpException(Ge::t('app', $default['message']));
        } else {
            throw new Exception\HttpException($default['statusCode'], Ge::t('app', $default['message']));
        }
    }

    /**
     * Проверяет, имеет ли текущий пользователь доступ к действию контроллера, согласно 
     * указанному правилу.
     * 
     * Правило имеет вид:
     * ```php
     * return [
     *     'allow',
     *     'controllers' => [
     *          'Form' => ['create', 'update', 'someUpdate', 'someDelete'],
     *          'Grid' => ['data'],
     *      ],
     *      'permission'  => 'read/write',
     *      'users'       => ['@backend'],
     * ];
     * ```
     * где, параметры правила:
     * - `allow` (`'allow' => true`), правило необходимо проверить на доступ пользователю. 
     *   Иначе, `deny`, проверить на запрет доступа пользователю;
     * - `controllers`, проверяет соответствие текущего имени контроллера с указанными  
     *   {@see AccessRules::matchController()};
     * - `actions`, проверяет соответствие текущего действия контроллерами с указанными
     *   {@see AccessRules::matchAction()};
     * - `permission`, проверяет, соответствует ли указанное разрешение текущему пользователю
     *   {@see AccessRules::matchPermission()};
     * - `users`, проверяет, соответствует ли одна из указанных сторон авторизации, стороне 
     *   авторизации текущего пользователя {@see AccessRules::matchUser()};
     * - `exception`, параметры вызова исключения пот отказе досутпа {@see AccessRules::$defaultException}.
     * 
     * @param array $rule Правило для проверки доступа пользователю.
     * 
     * @return array Возвращает правило с результатом проверки доступности.
     *     ```php
     *     return ['allow' => true, 'access' => true];
     *     ```
     */
    public function matchRule(array $rule): array
    {
        // если есть параметр `allow`
        if (isset($rule[0])) {
            $allow = $rule[0] === 'allow';
        } else {
            $allow = $rule['allow'] ?? false;
        }
        // если есть параметр `actions`
        $actions = $rule['actions'] ?? [];
        // если есть параметр `controllers`
        $controllers = $rule['controllers'] ?? [];
        // если есть параметр `exception`
        $exception = $rule['exception'] ?? false;
        // если есть параметр `callback`
        $callback = $rule['callback'] ?? false;
        // имеет ли правило текущий контроллер или действие
        $hasController = $this->matchController($controllers, true);
        $hasAction     = $this->matchAction($actions, true);
        // доступен если правило имеет контроллер и действие
        $access = $hasController && $hasAction;
        if ($access) {
            // если есть параметр `users`
            $users = $rule['users'] ?? [];
            if ($users) {
                $hasUser = $this->matchUser($users, true);
                // если доступно пользователю
                $access = $access && $hasUser;
            }
        }
        if ($access) {
            // если есть параметр `permission`
            $permission = $rule['permission'] ?? false;
            if ($permission) {
                $hasPermission = $this->matchPermission($permission, true);
                // если пользователь имеет разрешение
                $access = $access && $hasPermission;
            }
        }
        // если есть параметры вызова исключения
        if ($access && $exception) {
            $this->callException($exception);
        }
        // если есть параметры обратного вызова
        if ($access && $callback) {
            if (is_string($callback))
                $this->module->{$callback}();
            else
                $callback();
        }
        return [
            'allow'  => $allow,
            'access' => $access
        ];
    }

    /**
     * Проверяет, имеет ли текущий пользователь доступ к действию контроллера, согласно 
     * правилам доступа.
     * 
     * @return bool Если значение `true`, пользователь имеет доступ к действию 
     *     контроллера, иначе нет.
     */
    public function match(): bool
    {
        foreach ($this->rules as $rule) {
            $match = $this->matchRule($rule);
            if ($match['allow'] && $match['access']) {
                return true;
            }
            if (!$match['allow'] && $match['access']) {
                return false;
            }
        }
        return false;
    }
}
