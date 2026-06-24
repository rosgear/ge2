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
 * Сегментное сопоставление (частями) маршрута.
 * 
 * Опции используемые для проверки и сопоставления маршрута:
 * - "size", максимальное количество сопоставляемых сегментов маршрута (например, 
 * для маршрута "user/account/view/17" - 4 сегмента);
 * - "restrictions", ограничения выбора контроллера {@see BaseMatcher::$restrictions};
 * - "constraints", имена ограничений (сегментов) маршрута {@see BaseMatcher::$constraints};
 * - "assign", назначенные параметры для определения значения сегментов (частей) маршрута 
 * {@see BaseMatcher::$assign};
 * - "redirect", правила перенаправления контроллера и действия {@see BaseMatcher::$redirect};
 * - "method", метод запроса {@see BaseMatcher::$method}.
 * 
 * Пример:
 * ```php
 * // для маршрута "https://domain.com/rss/my-feed"
 * Ge::$app->router->match([
 *     'type'   => 'parts',
 *     'module' => 'site.frontend.rss',
 *     'route'  => 'rss',
 *     'size'   => 2,
 *     'assign' => ['feedName' => 1]
 * ]);
 * // результат: ['action' => 'index', 'controller' => 'index', 'feedName' => 'my-feed', ...].
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class Parts extends BaseMatcher
{
    /**
     * Количество сопоставляемых сегментов.
     *
     * @var int
     */
    public int $size = 0;

    /**
     * {@inheritdoc}
     */
    public function defineOptions(array $options): void
    {
        parent::defineOptions($options);

        $this->size = (int) ($options['size'] ?? 0);
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
     * {@inheritdoc}
     * 
     * @param null|array $parts Cопоставляемые сегменты (части) маршрута, например 
     *     `['module', 'controller', 'action', 'id']` (по умолчанию `null`).
     */
    public function match(?array $parts = null): mixed
    {
        if ($parts === null) {
            $parts = Ge::$app->urlManager->explodeRoute();
        }

        // проверка принадлежности маршрута модулю
        if (!($parts && $parts[0] === $this->route)) return false;

        // проверка размера сегментов маршрута если он указан
       if ($this->size && $this->size < sizeof($parts)) return null;

        // проверка метода запроса если он указан
        if ($this->method) {
            if (!Ge::$app->request->isMethod($this->method)) return null;
        }

        $match = [
            'baseRoute'  => $this->route,
            'namespace'  => $this->namespace,
            'module'     => $this->module
        ];

        // проверка назначенных параметров если они указаны
        if ($this->assign) {
            return array_merge($match, $this->defineAssignment($parts));
        }

        // 'module/controller/action/id' => 'controller/action/id'
        array_shift($parts);

        $controller = $this->defineController($parts);
        $action     = $this->defineAction($parts);

        // проверка ограничений контроллера если они указаны
        if ($this->restrictions) {
            if (in_array($controller, $this->restrictions)) return null;
        }

        // проверка ограничений (сегментов) маршрута если они указаны
        if ($this->constraints) {
            $match = array_merge($match, $this->defineConstraints($parts));
        }
    
        // проверка правил перенаправления контроллера и действия если они указаны
        if ($this->redirect) {
            list($controller, $action) = $this->defineRedirect($controller, $action);
        }

        $match['controller'] = $controller;
        $match['action'] = $action;
        return $match;
    }
}
