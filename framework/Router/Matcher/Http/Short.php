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
 * Короткое сопоставление маршрута.
 * 
 * Опции используемые для проверки и сопоставления маршрута:
 * - "constraints", имена ограничений (сегментов) маршрута {@see BaseMatcher::$constraints};
 * - "assign", назначенные параметры для определения значения сегментов (частей) маршрута 
 * {@see BaseMatcher::$assign};
 * - "method", метод запроса {@see BaseMatcher::$method}.
 * 
 * Пример:
 * ```php
 * // для маршрута "https://domain.com/user/account/17"
 * Ge::$app->router->match([
 *     'type'   => 'short',
 *     'module' => 'site.frontend.user',
 *     'route'  => 'user',
 *     'assign' => ['controller' => 1, 'id' => 2]
 * ]);
 * // результат: ['action' => 'index', 'controller' => 'account', 'id' => 17, ...].
 * 
 * // для маршрута "https://domain.com/user"
 * // результат: ['action' => 'index', 'controller' => 'index', 'id' => null, ...].
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class Short extends BaseMatcher
{
    /**
     * {@inheritdoc}
     * 
     * @param string $route Cопоставляемый маршрут, например 'user/account'.
     */
    public function match(?string $route = null): mixed
    {
        if ($route === null) {
            $route = Ge::$app->urlManager->route;
        }

        // проверка принадлежности маршрута модулю
        if (strpos($route, $this->route) !== 0) return false;

        // получение остаточного маршрута от сверяемого
        $postfix = trim(substr($route, strlen($this->route)), '/');
        $segments = $postfix ? explode('/', $postfix) : [];

        $match = [
            'baseRoute'  => str_replace($postfix, '', $route),
            'namespace'  => $this->namespace,
            'module'     => $this->module,
            'controller' => $this->getDefaultController(),
            'action'     => $this->getDefaultAction()
        ];

        // проверка назначенных параметров если они указаны
        if ($this->assign) {
            return array_merge($match, $this->defineAssignment($segments));
        }

        // проверка ограничений (сегментов) маршрута если они указаны
        if ($this->constraints) {
            $match = array_merge($match, $this->defineConstraints($segments));
        }
        return $match;
    }
}
