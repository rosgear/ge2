<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Router\Matcher\Http;

use Ge;

/**
 * Класс сопоставления маршрута "Extensions".
 * 
 * Cопоставляет маршруты, в шаблоне которых, присутствует расширение модуля.
 * 
 * Пример шаблона маршрута: 'reference[/:extension[/:controller[/:action[/:id]]]]'.
 * Такому маршруту соответствуют следующие URL-адреса:
 * - 'https:://domain.com/reference/books/form/view/1';
 * - 'https:://domain.com/reference/books/form/view';
 * - 'https:://domain.com/reference/books/form';
 * - 'https:://domain.com/reference/books';
 * - 'https:://domain.com/reference'.
 * Где, 'reference' - модуль, 'books' - расширение модуля, 'form' - контроллер, 
 * 'view' - действие, а '1' - идентификатор.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class Extensions extends Segments
{
    /**
     * Перенаправляет параметры запроса (имя контроллера, действия и расширения) по 
     * указанному правилу.
     * 
     * Правило определяется параметром {@see BaseMatcher::$redirect} и может иметь вид:
     * ```php
     * [
     *     // перенаправляет на расширение 'users'
     *     'user:account@*' => ['account', '*', 'users'],
     *     // перенаправляет на действие 'remove'
     *     'user:account@delete' => ['*', 'remove', '*'],
     *     // перенаправляет все действия контроллеров на расширение 'users'
     *     'user:*@*' => ['*', '*', 'users'],
    *      // перенаправляет все контроллеры с действием 'delete' на контроллер 'test' c действием 'remove'
     *     '*@delete' => ['test', 'remove'],
     * ]
     * ```
     * 
     * @param string $controller Имя контроллера.
     * @param string $action  Имя действия (по умолчанию '').
     * @param string $extension Маршрут расширения (по умолчанию '').
     * 
     * @return array Возвращает новые параметры сформированные согласно указанному 
     *     правилу и имеют вид `['controller', 'action', 'extension']`.
     * 
     */
    public function defineRedirect(string $controller, string $action = '', string $extension = ''): array
    {
        $name = $extension ? $extension . ':' : '';
        // правила перенаправления
        $rules = [
            $name . $controller . '@' . $action => true,
            $name . '*@*'                      => true,
            $name . '*@' .$action              => true,
            $name . $controller . '@*'         => true
        ];
        foreach ($this->redirect as $rule => $result) {
            if (isset($rules[$rule])) {
                if ($result[0] === '*') {
                    $result[0] = $controller;
                }
                if ($result[1] === '*') {
                    $result[1] = $action;
                }
                if ($result[2] === '*') {
                    $result[2] = $extension;
                }
                return $result;
            }
        }
        return [$controller, $action, $extension];
    }

    /**
     * {@inheritdoc}
     * 
     * @param null|string $route Маршрут сопоставления (полученный из URL-адреса, по умолчанию `null`).
     * @param null|int $pathOffset Cмещение относительно маршрута сопоставления, часть 
     *     (сегмент) которого будет сопоставляться (по умолчанию `null`).
     */
    public function match(?string $route = null, ?int $pathOffset = null): mixed
    {
        if ($route === null) {
            $route = Ge::$app->urlManager->route;
        }

        // если есть смещение относительно маршрута сопоставления
        if ($pathOffset !== null)
            $result = preg_match('(\G' . $this->regex . ')', $route, $matches, 0, $pathOffset);
        else
            $result = preg_match('(^' . $this->regex . '$)', $route, $matches);
        if (!$result) return false;

        // проверка метода запроса если он указан
        if ($this->method) {
            if (!Ge::$app->request->isMethod($this->method)) return null;
        }

        /**
         * Сопоставление групп регулярных выражений с именами параметров.
         * Результат имеет вид: `['extension' => 'name', 'controller' => 'name', 'action' => 'name', ...]`
         */
        $params = [];
        foreach ($this->paramMap as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $this->decode($matches[$index]);
            }
        }

        /**
         * Получение базового URL-пути (до модуля), который не проверяется регулярным выражением.
         * Необходим для формирования URL-адреса относительно модуля.
         */
        if ( ($pos = mb_strpos($this->regex, '(')) !== false)
            $baseRoute = mb_substr($this->regex, 0, $pos);
        else
            $baseRoute = $route;

        $controller = $this->getDefaultController($params['controller'] ?? '');
        $action     = $this->getDefaultAction($params['action'] ?? '');    
        $extension  = $params['extension'] ?? '';

        // проверка ограничений контроллера если они указаны
        if ($this->restrictions) {
            if (in_array($controller, $this->restrictions)) return null;
        }

        // проверка правил перенаправления контроллера, действия, расширения если они указаны
        if ($this->redirect) {
            list($controller, $action, $extension) = $this->defineRedirect($controller, $action, $extension);
        }

        return [
            'baseRoute'  => $baseRoute, // базовый маршрут
            'namespace'  => $this->namespace, // название пространства имён
            'module'     => $this->module, // идентификатор модуля
            'extension'  => $extension, // имя (маршрут) расширения
            'controller' => $controller, // имя контроллера
            'action'     => $action, // имя действия
            'params'     => $params // параметры запроса
        ];
    }
}
