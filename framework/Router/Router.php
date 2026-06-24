<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Router;

use Ge;
use Ge\Config\Config;
use Ge\Url\UrlManager;
use Ge\Stdlib\BaseObject;
use Ge\Router\Matcher\RouteMatch;
use Ge\Router\Matcher\Http\BaseMatcher;

/**
 * Маршрутизатор запросов.
 * 
 * Маршрутизатор выполняет поиск компонентов: модулей (modules), расширений (extensions),
 * методом сопоставления маршрутов компонентов с указанными маршрутами. Результатом
 * сопоставления будет {@see Router::$routeMatch}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router
 * @since 2.0
 */
class Router extends BaseObject
{
    /**
     * @var string Событие, возникшее перед сопоставлением маршрутов.
     */
    public const EVENT_BEFORE_ROUTE_MATCH = 'beforeRouteMatch';

    /**
     * @var string Событие, возникшее после сопоставления маршрутов.
     */
    public const EVENT_AFTER_ROUTE_MATCH = 'afterRouteMatch';

    /**
     * @var string Событие, возникшее при сопоставлении маршрутов.
     */
    public const EVENT_ON_ROUTE_MATCH = 'onRouteMatch';

    /**
     * Сопоставители (плагины) маршрутов компонентов.
     *
     * @var array
     */
    protected array $matcher = [
        'literal'      => 'Ge\Router\Matcher\Http\Literal',
        'segments'     => 'Ge\Router\Matcher\Http\Segments',
        'crudSegments' => 'Ge\Router\Matcher\Http\CrudSegments',
        'short'        => 'Ge\Router\Matcher\Http\Short',
        'parts'        => 'Ge\Router\Matcher\Http\Parts',
        'extensions'   => 'Ge\Router\Matcher\Http\Extensions'
    ];

    /**
     * Конфигуратор с параметрами маршрутизации компонентов.
     *
     * @var Config
     */
    public Config $config;

    /**
     * Результат сопоставления маршрута.
     * 
     * @var RouteMatch|false
     */
    public RouteMatch|false $routeMatch = false;

    /**
     * URL Менеджер.
     * 
     * @var UrlManager
     */
    public UrlManager $urlManager;

    /**
     * Название сопоставителя по умолчанию.
     * 
     * Используется в том случаи, если в параметрах маршрутизации компонента не указ 
     * тип (type) сопоставления (имя или плагин сопоставителя).
     * 
     * @see Router::$matcher
     * @see Router::match()
     * 
     * @var string
     */
    public string $matcherDefault = 'literal';

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!isset($this->config)) {
            $this->config = $this->createConfig();
        }
        if (!isset($this->urlManager)) {
            $this->urlManager = Ge::$services->getAs('urlManager');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'router';
    }

    /**
     * Возращает значение параметра последнего плагина, используемого для поиска модуля.
     * 
     * @param string $name Имя параметра.
     * @param mixed $default Если нет параметра, значение по умолчанию.
     * 
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if ($this->routeMatch === false) {
            return $default;
        }
        return $this->routeMatch->get($name, $default);
    }

    /**
     * Устанавливает конфигуратор маршрутизатору.
     * 
     * @return $this
     */
    public function setConfig(Config $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Возвращает конфигуратор маршрутизатора.
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        if (!isset($this->config)) {
            $this->config = $this->createConfig();
        }
        return $this->config;
    }

    /**
     * Создаёт конфигуратор маршрутизации c параметрами по умолчанию.
     * 
     * @return Config
     */
    public function createConfig(): Config
    {
        $config = new Config();
        $config->setAll(['prefixes' => [], 'routes' => []]);
        return $config;
    }

    /**
     * Устанавливает результат сопоставления маршрута.
     * 
     * @param RouteMatch|false $routeMatch Результат сопоставления маршрута.
     * 
     * @return void
     */
    public function setRouteMatch($routeMatch): void
    {
        $this->routeMatch = $routeMatch;
        Ge::setAlias('@hasMatch', $routeMatch ? true : false);
        if ($routeMatch) {
            $params = $routeMatch->getAll();
            Ge::setAlias('@match', $params['baseRoute']);
            Ge::setAlias('@match~', \Ge\Helper\Url::to($params['baseRoute']));
            Ge::setAlias('@match:id', $params['id'] ?? '');
            Ge::setAlias('@match:module', $params['module']);
            Ge::setAlias('@match:controller', $params['controller']);
            Ge::setAlias('@match:action', $params['action']);
        }
    }

    /**
     * Возвращает результат сопоставления маршрута.
     * 
     * @return RouteMatch|false Если значение `false`, то ниодного сопоставления 
     *     не найдено.
     */
    public function getRouteMatch(): RouteMatch|false
    {
        return $this->routeMatch;
    }

    /**
     * Создаёт результат сопоставления.
     * 
     * @param string $id Идентификатор компонента, чей маршрут был определён.
     * @param string $type Тип (имя сопостовителя) сопоставления маршрута.
     * @param array $options Параметры маршрута.
     * @param array $result Результат сопоставления.
     * 
     * @return RouteMatch
     */
    public function createRouteMatch(string $id, string $type, array $options = [], array $result = []): RouteMatch
    {
        return new RouteMatch($id, $type, $options, $result);
    }

    /**
     * Возвращает параметры маршрута из файла конфигурации маршрутизатора.
     * 
     * @param string $id Идентификатор компонента (модуль, расширение модуля), которому 
     *     принадлежит маршрут.
     * 
     * @return array{type:string, options:array}|null Если значение `null`, маршрут не найден.
     */
    public function getRoute(string $id): ?array
    {
        return $this->config->routes[$id] ?? null;
    }

    /**
     * Добавляет параметры маршрута в конфигурацию маршрутизатора.
     * 
     * @param string $id Идентификатор компонента (модуль, расширение модуля), которому 
     *     принадлежит маршрут.
     * @param string $type Вид маршрута, например: 'crudSegments', 'parts', 'literal'.
     * @param array $options Параметры маршрута.
     * 
     * @return $this Добавления не будет, если параметры уже существуют с указанным идентификатор компонента.
     */
    public function addRoute(string $id, string $type, array $options): static
    {
        if (isset($this->config->routes[$id])) return $this;

        $this->config->routes[$id] = [
            'type'    => $type,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Устанавливает параметры маршрута в конфигурацию маршрутизатора.
     * 
     * @param string $id Идентификатор компонента (модуль, расширение модуля), которому 
     *     принадлежит маршрут.
     * @param string $type Вид маршрута, например: 'crudSegments', 'parts', 'literal'.
     * @param array $options Параметры маршрута..
     * 
     * @return $this
     */
    public function setRoute(string $id, string $type, array $options): static
    {
        $this->config->routes[$id] = [
            'type'    => $type,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Удаляет параметры маршрута из конфигурации маршрутизатора.
     * 
     * @param string $id Идентификатор компонента (модуль, расширение модуля), которому 
     *     принадлежит маршрут.
     * 
     * @return $this
     */
    public function removeRoute(string $id): static
    {
        if (isset($this->config->routes[$id])) {
            unset($this->config->routes[$id]);
        }
        return $this;
    }

    /**
     * Возвращает название класса сопоставителя маршрута по его имени.
     * 
     * @param string $name Имя сопоставителя маршрута {@see Router::$matcher}.
     * 
     * @return null|string Если значение `null`, класс сопоставителя не найден.
     */
    public function getMatcher(string $name): ?string
    {
        return $this->matcher[$name] ?? null;
    }

    /**
     * Создаёт сопоставителя маршрута.
     * 
     * @param string $name Имя сопоставителя маршрута {@see Router::$matcher}.
     * @param array $options Параметры (маршрута) сопоставителя.
     * 
     * @return BaseMatcher|null  Если значение `null`, класс сопоставителя не найден. 
     */
    public function createMatcher(string $name, array $options): ?BaseMatcher
    {
        $matcherClass = $this->matcher[$name] ?? null;
        // если нет плагина соответствия
        if (!$matcherClass) {
            return null;
        }

        // префикс для определения маршрута в зависимости от backend или frontend
        if (isset($options['prefix']))
            $options['prefix'] = $this->config->prefixes[$options['prefix']] ?? '';
        else
            $options['prefix'] = '';
        return new $matcherClass($options);
    }

    /**
     * Создаёт сопоставителя и проверяет им указанные параметры маршрутизации.
     * 
     * @param array<string, mixed> $options Параметры маршрутизации компонента.
     * 
     * @return mixed Возвращает значение `false`, если сопоставление не 
     *     успешно. Иначе, результат сопоставления. Если результатом будет `null`,
     *     сопоставление частично успешно (нет смысла далее делать проверки, если это 
     *     выполняется перебором).
     */
    public function match(array $options): mixed
    {
        /** @var string $matcherName Имя сопоставителя */
        $matcherName  = $options['type'] ?? $this->matcherDefault;

        // быстрое стравнение, аналогичное типу "literal", за исключением того,
        // что вызвать можно в любой момент и без дополнительных параметров
        if ($matcherName === 'fast') {
            if ($options['route'] === $this->urlManager->requestUri)
                return $options;
            else
                return false;
        }

        /** @var string $matcherClass Класс сопоставителя */
        $matcherClass = $this->matcher[$matcherName] ?? false;
        if ($matcherClass === false) return false;

        // префикс для определения маршрута в зависимости от backend или frontend
        if (isset($options['prefix']))
            $options['prefix'] = $this->config->prefixes[$options['prefix']] ?? '';
        else
            $options['prefix'] = '';
        return $matcherClass::factory($options)->match();
    }

    /**
     * Выполняет поиск компонента, путём проверки указанных параметров маршрутизации.
     * 
     * @param array<string, array{type:string, options:array}> $routes Параметры 
     *     маршрутизации компонентов.
     * 
     * @return false|RouteMatch Возвращает значение `false`, если компонент не найден, 
     *     иначе результат сопоставления.
     */
    public function matchRoutes(array $routes): false|RouteMatch
    {
        $result = false;
        if (!$this->urlManager->isRootUrl) {
            foreach ($routes as $id => $route) {
                $route['options']['type'] = $route['type'];
                $result = $this->match($route['options']);
                if ($result !== false || $result === null) break;
            }
        }

        if ($result)
            $this->setRouteMatch(
                $this->createRouteMatch($id, $route['type'], $route['options'], $result)
            );
        else
            $this->setRouteMatch(false);
        return $this->routeMatch;
    }

    /**
     * Выполняет поиск компонента.
     * 
     * @return $this
     */
    public function run(): static
    {
        $this->trigger(self::EVENT_BEFORE_ROUTE_MATCH, ['routes' => $this->config->routes]);

        $result = false;
        Ge::beginProfile('routeMatch');
        // чтобы поиск маршрута был без учёта корня сайта (если ЧПУ не включено)
        // пример: "<host>/<baseroute>/<path>?r=route", должно быть "<host>/<baseroute>/?r=route".
        if (!$this->urlManager->enablePrettyUrl && !$this->urlManager->isRootUrl) {
            $result = false;
        } else {
            if ($this->urlManager->isRootUrl)
                $result = false;
            else {
                foreach ($this->config->routes as $id => $route) {
                    $route['options']['type'] = $route['type'];
                    $result = $this->match($route['options']);
                    if ($result !== false) break;
                }
            }
        }
        Ge::endProfile('routeMatch', 'Run router');

        if ($result) {
            $this->setRouteMatch(
                $this->createRouteMatch($id, $route['type'], $route['options'], $result)
            );
            $this->trigger(self::EVENT_ON_ROUTE_MATCH, ['routeMatch' => $this->routeMatch]);
        } else
            $this->setRouteMatch(false);

        $this->trigger(self::EVENT_AFTER_ROUTE_MATCH, ['routeMatch' => $this->routeMatch]);
        return $this;
    }
}
