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
 * Сегментное сопоставление CRUD маршрута.
 * 
 * Применяется для модулей с CRUD {@link https://ru.wikipedia.org/wiki/CRUD} маршрутами.
 * 
 * Опции используемые для проверки и сопоставления маршрута:
 * - "restrictions", ограничения выбора контроллера {@see BaseMatcher::$restrictions};
 * - "constraints", имена ограничений (сегментов) маршрута {@see BaseMatcher::$constraints};
 * - "assign", назначенные параметры для определения значения сегментов (частей) маршрута 
 * {@see BaseMatcher::$assign};
 * - "redirect", правила перенаправления контроллера и действия {@see BaseMatcher::$redirect};
 * - "method", метод запроса {@see BaseMatcher::$method}.
 * 
 * Пример:
 * ```php
 * // для маршрута "https://domain.com/goods/books/list/view/17"
 * Ge::$app->router->match([
 *     'type'        => 'crudSegments',
 *     'module'      => 'site.goods',
 *     'route'       => 'goods',
 *     'childRoutes' => [
 *         'books' => [
 *             'route'       => 'books',
 *             'constraints' => ['categoryId'],
 *             'defaults'    => [
 *                 'action'     => 'view',
 *                 'controller' => ['list' => 'booklist', 'default' => 'booklist']
 *              ]
 *         ],
  *        'tea' => [
 *             'route'       => 'tea',
 *             'constraints' => ['categoryId'],
 *             'defaults'    => [
 *                 'action'     => 'view',
 *                 'controller' => ['list' => 'tealist', 'default' => 'tealist']
 *              ]
 *         ]
 *     ]
 * ]);
 * // результат: ['action' => 'view', 'controller' => 'booklist', 'categoryId' => 17, ...].
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class CrudSegments extends BaseMatcher
{
    /**
     * Дочернии маршрута.
     * 
     * @see CrudSegments::defineOptions()
     * 
     * @var array
     */
    protected array $childRoutes = [];

    /**
     * Cравнивать дочернии маршруты.
     * 
     * @see CrudSegments::defineOptions()
     * 
     * @var bool
     */
    protected bool $childEquality = true;

    /**
     * {@inheritdoc}
     */
    public function defineOptions(array $options): void
    {
        parent::defineOptions($options);

        $this->childRoutes   = $options['childRoutes'] ?? [];
        $this->childEquality = $options['childEquality'] ?? true;
    }

    /**
     * Определяет имя контроллера.
     * 
     * Имя контроллера определяется из 1-о сегмента маршрута с последующем его 
     * извлечением (количество сегментов уменьшится на 1-у). Если 1-й
     * сегмент маршрута не найден, то контроллер определяется по умолчанию.
     * 
     * @param array $segments Сегменты (части) маршрута.
     * 
     * @return string
     */
    protected function defineController(array &$segments): string
    {
        if (isset($segments[0])) {
            $controller = $segments[0];
            array_shift($segments);
            return $this->getDefaultController($controller);
        }
        return $this->getDefaultController();
    }

    /**
     * Определяет имя действия контроллера.
     * 
     * Имя действия определяется из 1-о сегмента маршрута с последующем его 
     * извлечением (количество сегментов уменьшится на 1-у). Если 1-й
     * сегмент маршрута не найден, то действие определяется по умолчанию.
     * 
     * @param array $segments Сегменты (части) маршрута.
     * 
     * @return string
     */
    protected function defineAction(array &$segments): string
    {
        if (isset($segments[0])) {
            $action = $segments[0];
            array_shift($segments);
            return $this->getDefaultAction($action);
        }
        return $this->getDefaultAction();
    }

    /**
     * Точное сравнение дочерних маршрутов.
     * 
     * @param array $options Опции сопоставления дочернего маршрута.
     * @param array $segments Сегменты (части) маршрута.
     * 
     * @return mixed
     */
    protected function childEqualityMatching(array $options, array $segments): mixed
    {
        // наследуем опции предка
        $options['namespace'] = $this->namespace;
        $options['module'] = $this->module;

        $routeMatch = (new Parts($options))->match($segments);
        if (is_array($routeMatch)) {
            $routeMatch['baseRoute'] = $this->route;
        }
        return $routeMatch;
    }

    /**
     * Сопоставление каждого дочернего маршрута.
     * 
     * @param array $segments Сегменты (части) маршрута.
     * 
     * @return mixed
     */
    protected function childEeachMatching(array $segments): mixed
    {
        foreach ($this->childRoutes as $name => $options) {
            // наследуем опции предка
            $options['namespace'] = $this->namespace;
            $options['module'] = $this->module;

            $routeMatch = (new Parts($options))->match($segments);
            if (is_array($routeMatch)) {
                $routeMatch['baseRoute'] = $this->route;
                return $routeMatch;
            }
        }
        return false;
    }

    /**
     * Возращает параметры дочернего маршрута.
     * 
     * @param string $name Имя дочернего маршрута.
     * 
     * @return false|array Если значение `false`, дочерний маршрут не найден.
     */
    public function child(string $name): false|array
    {
        return $this->childRoutes[$name] ?? false;
    }

    /**
     * {@inheritdoc}
     * 
     * @param string $route Cопоставляемый маршрут, например 'user/account' (по умолчанию `null`).
     */
    public function match(?string $route = null): mixed
    {
        if ($route === null) {
            $route = Ge::$app->urlManager->route;
        }

        // проверка принадлежности маршрута модулю
        if (strpos($route, $this->route) !== 0) return false;

        // проверка метода запроса если он указан
        if ($this->method) {
            if (!Ge::$app->request->isMethod($this->method)) return null;
        }

        // получение остаточного маршрута от сверяемого
        $childRoute = trim(substr($route, strlen($this->route)), '/');
        $segments = $childRoute ? explode('/', $childRoute) : [];

        // если есть префикс к указанному маршруту
        if ($segments) {
            // проверка дочерних маршрутов если указаны
            if ($this->childRoutes) {
                $result = false;
                // проверка дочернего маршрута на строгое соответствие если указано
                if ($this->childEquality) {
                    // опции сопоставления дочернего маршрута
                    $childOptions = $this->child($segments[0]);
                    if ($childOptions) {
                        $result = $this->childEqualityMatching($childOptions, $segments);
                    }
                // проверка всех дочерних маршрутов
                } else
                    $result = $this->childEeachMatching($segments);
                // is_array, null
                if ($result !== false) return $result;
            }

            $match = [
                'baseRoute'  => $this->route,
                'namespace'  => $this->namespace,
                'module'     => $this->module
            ];

            // проверка назначенных параметров если они указаны
            if ($this->assign) {
                return array_merge($match, $this->defineAssignment($segments));
            }

            $controller = $this->defineController($segments);
            $action     = $this->defineAction($segments);

            // проверка ограничений контроллера если они указаны
            if ($this->restrictions) {
                if (in_array($controller, $this->restrictions)) return null;
            }

            // проверка ограничений (сегментов) маршрута если они указаны
            if ($this->constraints) {
                $match = array_merge($match, $this->defineConstraints($segments));
            }
        
            // проверка правил перенаправления контроллера и действия если они указаны
            if ($this->redirect) {
                list($controller, $action) = $this->defineRedirect($controller, $action);
            }

            $match['controller'] = $controller;
            $match['action'] = $action;
            return $match;
        }

        // Если нет дочернего маршрута

        $controller = $this->getDefaultController();
        $action     = $this->getDefaultAction();

        // проверка правил перенаправления контроллера и действия если они указаны
        if ($this->redirect) {
            list($controller, $action) = $this->defineRedirect($controller, $action);
        }

        return [
            'baseRoute'  => $this->route,
            'namespace'  => $this->namespace,
            'module'     => $this->module,
            'controller' => $controller,
            'action'     => $action
        ];
    }
}
