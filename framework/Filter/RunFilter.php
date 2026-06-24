<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filter;

use Ge\Stdlib\Behavior;
use Ge\Stdlib\Component;
use Ge\Mvc\Controller\BaseController;

/**
 * RunFilter это базовый класс для фильтрации запросов перед запуском модуля или контроллера.
 * 
 * Унаследованные фильтры от RunFilter необходимо объявлять в `behavior()` класса 
 * контроллера или модуля. 
 * 
 * Для модуля необходимо использовать следующие объявления с указанием параметров фильтрации:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'filter' => [
 *             'class'       => 'FilterClass',
 *             'controllers' => [
 *                 '' => [
 *                     // действия контроллера
 *                 ],
 *                 '*' => [
 *                     // действия контроллера
 *                 ],
 *                 'Controller' => [
 *                     '*'    => ['filterParams'], // параметры фильтра
 *                     ''     => ['filterParams'], // параметры фильтра
 *                     'view' => ['filterParams'], // параметры фильтра
 *                 ],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 * Для контроллера необходимо использовать следующие объявления с указанием параметров фильтрации:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'filter' => [
 *             'class'   => 'FilterClass',
 *             'actions' => [
 *                 '*'    => ['filterParams'], // параметры фильтра
 *                 ''     => ['filterParams'], // параметры фильтра
 *                 'view' => ['filterParams'], // параметры фильтра
 *             ],
 *         ],
 *     ];
 * }
 * ```
 * где параметр конфигурации `controllers` фильтра может иметь значения:
 * - `*`, имена контроллеров, которые не указаны в массиве;
 * - `''`, имя контроллера по умолчанию, определяется свойством модуля {@see \Ge\Mvc\Module\BaseModule::$defaultController}.
 * а параметр конфигурации `actions`:
 * - `*`, имена действий, которые не указаны в массиве;
 * - `''`, имя действия по умолчанию, определяется свойством контроллера {@see \Ge\Mvc\Controller\BaseController::$defaultAction}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filter
 * @since 2.0
 */
class RunFilter extends Behavior
{
    /**
     * Правила фильтрации для контроллеров с их действиями.
     * 
     * Правила здесь устанавливается в том случаи, если владелец {@see RunFilter::$owner}
     * модуль {@see \Ge\Mvc\Module\BaseModule}.
     * 
     * @var array
     */
    public array $controllers = [];

    /**
     * Правила фильтрации для действий.
     * 
     * Правила здесь устанавливается в том случаи, если владелец {@see RunFilter::$owner}
     * контроллер {@see \Ge\Mvc\Controller\BaseController}.
     * 
     * @var array
     */
    public array $actions = [];

    /**
     * Контроллер события.
     * 
     * @see RunFilter::beforeFilter()
     * 
     * @var BaseController
     */
    protected BaseController $controller;

    /**
     * Имя действия контроллера в событии.
     * 
     * @see RunFilter::beforeFilter()
     * 
     * @var string
     */
    protected string $actionName;

    /**
     * Фильтрация событии.
     * 
     * @param mixed $params Правила фильтрации.
     *     - если строка, метод запроса в верхнем регистре ('GET', 'POST'...);
     *     - если массив строк, методы запроса в верхнем регистре (['GET', 'POST'...]).
     *     Массив может иметь параметр `ajax` со значениями: true, false, 'AJAX', 'PJAX', 'GJAX'.
     * 
     * @return bool Если значение `true`, правила фильтрации успешно прошли проверку.
     */
    public function filtering(mixed $params): bool
    {
        return true;
    }

    /**
     * Присоединяет объект фильтра к компоненту.
     * 
     * @param Component $owner Компонент (контроллер, модуль) к которому должнен быть 
     *     присоединён фильтр.
     * 
     * @return void
     */
    public function attach(Component $owner): void
    {
        $this->owner = $owner;
        $owner->on($owner::EVENT_BEFORE_RUN, [$this, 'beforeFilter']);
    }

    /**
     * Отсоединяет объект фильтра от компонента.
     * 
     * @return void
     */
    public function detach(): void
    {
        if ($this->owner) {
            $this->off($this->owner::EVENT_BEFORE_RUN, [$this, 'beforeFilter']);
            $this->owner = null;
        }
    }

    /**
     * Проверяет правила фльтрации для контроллеров с их действиями.
     * 
     * @param BaseController $controller Контроллер события.
     * @param string $actionName Имя действия контроллера в событии.
     * 
     * @return bool Если значение `true`, то правила фильтрации успешно прошли проверку.
     */
    public function validateControllers(BaseController $controller, string $actionName): bool
    {
        $name = $controller->isDefault() ? '' : $controller->getName();

        if (isset($this->controllers[$name])) {
            if (isset($this->controllers[$name][$actionName])) {
                return $this->filtering($this->controllers[$name][$actionName]);
            } elseif (isset($this->controllers[$name]['*'])) {
                return $this->filtering($this->controllers[$name]['*']);
            }
        } elseif (isset($this->controllers['*'])) {
            return $this->filtering($this->controllers['*']);
        }
        return true;
    }

    /**
     * Проверяет правила фльтрации для действия контроллера.
     * 
     * @param string $actionName Имя действия контроллера в событии.
     * 
     * @return bool Если значение `true`, правила фльтрации успешно прошли проверку.
     */
    public function validateActions(string $actionName): bool
    {
        if (isset($this->actions[$actionName])) {
            return $this->filtering($this->actions[$actionName]);
        } elseif (isset($this->actions['*'])) {
            return $this->filtering($this->actions['*']);
        }
        return true;
    }

    /**
     * Событие выполняемое перед запуском владельца (модуля или контроллера).
     * 
     * @param BaseController $controller Контроллер события.
     * @param string $actionName Имя действия контроллера в событии.
     * 
     * @return bool Если значение `true`, правила фльтрации успешно прошли проверку.
     */
    public function beforeFilter(BaseController $controller, string $actionName): bool
    {
        $this->controller = $controller;
        $this->actionName = $actionName;
        if ($this->controllers) {
            return $this->validateControllers(
                $controller,
                $controller->isDefaultAction() ? '' : $actionName
            );
        } elseif ($this->actions) {
            return $this->validateActions(
                $controller->isDefaultAction() ? '' : $actionName
            );
        }
        return true;
    }
}
