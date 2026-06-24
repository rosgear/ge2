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
use Ge\Exception\BadRequestHttpException;
use Ge\Exception\MethodNotAllowedHttpException;

/**
 * VerbFilter - это фильтр действий, который фильтрует методы HTTP-запроса.
 * 
 * Он позволяет определять разрешенные методы HTTP-запроса для каждого действия и 
 * выдает ошибку HTTP 405, если метод запрещен или ошибку HTTP 400, если отсутсвует 
 * заголовок запроса XMLHttpRequest.
 * 
 * Чтобы использовать VerbFilter, его необходимо объявить в `behavior()` класса 
 * контроллера или модуля. Например, следующие объявления будут определять типичный 
 * набор разрешенных методов запроса для действий REST CRUD.
 * Для контроллера:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class'   => '\Ge\Filter\VerbFilter',
 *             'actions' => [
 *                 '*'      => 'GET'
 *                 ''       => ['GET'],
 *                 'view'   => ['GET'],
 *                 'create' => ['GET', 'POST'],
 *                 'update' => ['GET', 'PUT', 'POST'],
 *                 'delete' => ['POST', 'DELETE', 'ajax' => true]
 *             ]
 *         ]
 *     ];
 * }
 * ```
 * где параметр конфигурации `actions` фильтра может иметь значения:
 * - `*`, имена действий, которые не указаны в массиве;
 * - `''`, имя действия по умолчанию, определяется свойством контроллера {@see \Ge\Mvc\Controller\BaseController::$defaultAction}.

 * Для модуля:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class'       => '\Ge\Filter\VerbFilter',
 *             'controllers' => [
  *                 '*' => [
 *                     // действия контроллера
 *                 ],
   *               '' => [
 *                     // действия контроллера
 *                 ],
 *                 'Controller' => [
 *                     '*'      => 'GET'
 *                     ''       => ['GET'],
 *                     'view'   => ['GET'],
 *                     'create' => ['GET', 'POST'],
 *                     'update' => ['GET', 'PUT', 'POST'],
 *                     'delete' => ['POST', 'DELETE', 'ajax' => true]
 *                 ],
 *             ]
 *         ]
 *     ];
 * }
 * ```
 * где параметр конфигурации `controllers` фильтра может иметь значения:
 * - `*`, имена контроллеров, которые не указаны в массиве;
 * - `''`, имя контроллера по умолчанию, определяется свойством модуля {@see \Ge\Mvc\Module\BaseModule::$defaultController}.
 * Параметры фильтра представлены в виде строки с указанным метода или массива методов.
 * Параметры фильтра могут иметь ключ `ajax`, указывающий на обязательное использование AJAX, где 
 * значение ключа:
 * - `true`, `AJAX`, обязательное использование AJAX для действия контроллера;
 * - `false`, AJAX не используется для действия контроллера;
 * - `PJAX`, обязательное использование PJAX для действия контроллера;
 * - `GAXJ`, обязательное использование GAXJ для действия контроллера.
 * 
 * Все значения параметров фильтра указываются в верхнем регистре.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filter
 * @since 2.0
 */
class VerbFilter extends RunFilter
{
    /**
     * Фильтрация метода запроса.
     * 
     * @param mixed $params Параметры фильтрации.
     * 
     * @return bool Если true, метод запроса соответствует указанному фильтру.
     * 
     * @throws MethodNotAllowedHttpException Метод не разрешен.
     */
    public function methodFiltering(mixed $params): bool
    {
        $method = Ge::$app->request->getMethod();
        if (is_array($params)) {
            unset($params['ajax']);
            if (in_array($method, $params)) {
                return true;
            }
        } else
        if (is_string($params)) {
            if ($params === $method) {
                return true;
            }
        }
        throw new MethodNotAllowedHttpException($method, $params);
    }

    /**
     * Фильтрация AJAX запроса.
     * 
     * @param mixed $params Параметры фильтрации.
     * 
     * @return bool Если true, AJAX запрос соответствует указанному фильтру.
     * 
     * @throws BadRequestHttpException Запрос должен быть с заголовком XMLHttpRequest.
     */
    public function ajaxFiltering(mixed $params): bool
    {
        $ajax = $params['ajax'] ?? false;
        if ($ajax === false) {
            return true;
        } elseif ($ajax === true) {
            if (Ge::$app->request->isAjax()) {
                return true;
            }
        } elseif ($ajax === 'PJAX') {
            if (Ge::$app->request->IsPjax()) {
                return true;
            }
        }
        elseif ($ajax === 'GJAX') {
            if (Ge::$app->request->IsGjax()) {
                return true;
            }
        }
        throw new BadRequestHttpException(
            sprintf('Request must be XMLHttpRequest%s.', is_string($ajax) ? ' "' . $ajax . '"' : '')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filtering(mixed $params): bool
    {
        if (!$this->ajaxFiltering($params)) {
            return false;
        }
        if (!$this->methodFiltering($params)) {
            return false;
        }
        return true;
    }
}
