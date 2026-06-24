<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Router\Matcher\Http;

/**
 * Базовый класс сопоставления маршрута.
 * 
 * Для сопоставления маршрута, используются базовые опции:
 *     [
 *         "route"     => "foo/bar",
 *         "namespace" => "Backend\MyModule",
 *         "module"    => "site.module",
 *         "method"    => ["POST", "GET"],
 *         "defaults"  => [
 *             "controller" => "Grid", "action" => "view"
 *         ],
 *         "redirect" => [
 *             "Form"      => ["MyForm", "view"],
 *             "Grid@view" => ["MyGrid", "view"]
 *         ],
 *         "constraints" => [
 *             "action" => "[a-zA-Z][a-zA-Z0-9_-]*",
 *             "id"     => "[0-9]+"
 *         ],
 *         "restrictions" => ["grid", "form"]
 *     ]
 * 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class BaseMatcher implements RouteInterface
{
    /**
     * @var string Название "основного" действия контроллера (если другие действия контроллера не определены).
     */
    public const BASE_ACTION = 'index';
    /**
     * @var string Название "основного" контроллера (если другие контроллеры не определены).
     */
    public const BASE_CONTROLLER = 'index';

    /**
     * Маршрут сопоставления (шаблон).
     * 
     * Опция "route" в конфигурации сопоставления.
     * В {@see \Ge\Router\Matcher\Http\BaseMatcher::defineOptions()} дополняется 
     * префиксом (часть маршрута например для backend) маршрутизации (указывается в конфигурации маршрутизатора) для 
     * сопоставления с полным машрутом.
     * 
     * @var string
     */
    protected string $route = '';

    /**
     * Маршрут сопоставления (шаблон) без изменений.
     * 
     * В отличии от {@see \Ge\Router\Matcher\Http\BaseMatcher::$route} не имеет префикса.
     * 
     * @var string
     */
    protected string $baseRoute = '';

    /**
     * Префикс для определения маршрута в зависимости от конфигурации для 
     * backend или frontend стороны.
     * 
     * В опцию "prefix" будет уже передан часть маршрута из {@see \Ge\Router::createMatcher()} или из 
     * .{@see \Ge\Router::match()}.
     * 
     * @var string
     */
    protected string $prefix = '';

    /**
     * Namespace модуля.
     * 
     * Опция "namespace" в конфигурации маршрута. Если сопоставление будет удачным, 
     * создаст модуль с указанным именем класса.
     * 
     * Является не обязательным если указана опция "module" {@see \Ge\Router\Matcher\Http\BaseMatcher::$module}.
     * 
     * @var string
     */
    protected string $namespace = '';

    /**
     * Идентификатор модуля.
     * 
     * Опция "module" в конфигурации маршрута. Если сопоставление будет удачным, 
     * создаст модуль с указанным идентификатор.
     * 
     * @var string
     */
    protected string $module = '';

    /**
     * Параметры по умолчанию (имя контроллер и его действие).
     * 
     * Опция "defaults" в конфигурации маршрута. Если сопоставление удачно, 
     * но не определено имя контроллера и его дейтсвие, то используются из 
     * параметров по умолчанию. 
     *
     * @var array
     */
    protected array $defaults = [
        'action'     => '', // string|array Имя действия ("action" => "view" или "action" => ["default" => "view", "view" => "myview"]).
        'controller' => ''  // string|array Имя контроллера ("controller" => "Grid" или "controller" => ["default" => "Grid", "Grid" => "MyGrid"]).
    ];

    /**
     * Жесткие ограничения выбора контроллера.
     * 
     * Опция "restrictions" в конфигурации маршрута. Если результатом сопоставления
     * получено имя контроллер, то оно будет сравниваться с контроллерами в ограничении.
     * 
     * Все указанные здесь имена контроллеров доступня для вызова, остальные нет.
     *
     * @var null|array
     */
    protected ?array $restrictions = null;

    /**
     * Метод запроса.
     * 
     * Опция "method" в конфигурации маршрута. Может иметь значение: "POST" или ["POST", "GET"].
     *
     * @var string[]|string
     */
    protected string|array $method = '';

    /**
     * Правила перенаправления контроллера и действия.
     * 
     * Правила указыватся в виде пар 'правило - [контроллер, действие]'.
     * Допустимые правила:
     * ```php
     * [
     *     'controller@action' => ['controller', 'action'],
     *     'controller@*'      => ['controller', '*'],
     *     '*@action'          => ['*', 'action'],
     *     '*@*'               => ['*', '*']
     * ]
     * ```
     *
     * @see BaseMatcher::defineRedirect()
     * 
     * @var null|array
     */
    protected $redirect;

    /**
     * Имена ограничений (сегментов) маршрута.
     * 
     * Применяются для получения значений сегментов маршрута после его разбора.
     *
     * @see BaseMatcher::defineConstraints()
     * 
     * @var null|array
     */
    protected $constraints;

    /**
     * Назначенные параметры для определения значения сегментов (частей) маршрута.
     * 
     * Параметры в виде пар 'ключ - индекс', где индекс - порядковый номер сегмента
     * маршрута, например: `['id' => 3, 'controller' => 2, 'action' => 1]`.
     * 
     * @see BaseMatcher::defineAssignment()
     *
     * @var null|array
     */
    protected $assign;

    /**
     * Параметры сопоставления маршрута.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Указатель на объект BaseMatch
     *
     * @var null|BaseMatcher
     */
    protected static $instance;

    /**
     * Конструктор класса.
     * 
     * @param array $options Параметры сопоставления маршрута.
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        $this->defineOptions($options);
    }

    /**
     * Создаёт новое сопоставление маршрута с указанными параметрами.
     * 
     * @param array $options Параметры сопоставления маршрута.
     * 
     * @return $this
     */
    public static function factory(array $options): static
    {
        return new static($options);
    }

    /**
     * Определяет параметры сопоставления маршрута.
     * 
     * @param array $options Параметры сопоставления маршрута.
     * 
     * @return void
     */
    public function defineOptions(array $options): void
    {
        $this->options = $options;

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Error of defining options: missing "route" in options array.');
        }
        $this->route = $options['route'];

        if (!isset($options['namespace']) && !isset($options['module'])) {
            throw new Exception\InvalidArgumentException('Error of defining options: missing "namespace" and "module" in options array.');
        }
        
        $this->namespace    = $options['namespace'] ?? '';
        $this->module       = $options['module'] ?? '';
        $this->restrictions = $options['restrictions'] ?? [];
        $this->assign       = $options['assign'] ?? [];
        $this->defaults     = $options['defaults'] ?? [];
        $this->constraints  = $options['constraints'] ?? [];
        $this->redirect     = $options['redirect'] ?? [];
        $this->method       = $options['method'] ?? '';
        $this->prefix       = $options['prefix'] ?? '';
        // определение названий по умолчанию для контроллера и действия
        if (empty($this->defaults['controller']))
            $this->defaults['controller'] = self::BASE_CONTROLLER;
        else {
            if (is_array($this->defaults['controller']))
                if (empty($this->defaults['controller']['default']))
                    $this->defaults['controller']['default'] = self::BASE_CONTROLLER;
        }
        if (empty($this->defaults['action']))
            $this->defaults['action'] = self::BASE_ACTION;
        else {
            if (is_array($this->defaults['action']))
                if (empty($this->defaults['action']['default']))
                    $this->defaults['action']['default'] = self::BASE_ACTION;
        }
        // формирование полного маршрута для сопоставления в зависимости для кого 
        // он предназначен (backend или frontend)
        $this->baseRoute = $this->route;
        if ($this->prefix) {
            if ($this->route == '/')
                $this->route = $this->prefix;
            else
                $this->route = $this->prefix . '/'  .$this->route;
        }
    }

    /**
     * Определяет ограничения (сегменты) маршрута, которые остались после его разбора.
     * 
     * Например, если маршрут имеет вид "user/account/view/17", а имена ограничений 
     * `['id', 'param']`, то результатом будет `[id' => 17, 'param' => null]`.
     * Если значение для ограничения отсутствует в сегменте маршрута, то оно
     * будет иметь значение `null`.
     * 
     * @param array $segments Сегменты маршрута полученные при его разборе. Передаётся 
     *     только те части сегментов к которым не относится: модуль, контроллер, действие.
     *     Например, если изначально `$segments` имеет значение `['user', 'account', 'view', 17]`,
     *     то в качестве значения аргумента будет указано `[17]`.
     * 
     * @return array
     */
    protected function defineConstraints(array $segments): array
    {
        $result = [];
        foreach ($this->constraints as $index => $name) {
            $result[$name] = $segments[$index] ?? null;
        }
        return $result;
    }

    /**
     * Определяет значения сегментов (частей) маршрута назначенным параметрам, как 
     * результат сопоставления.
     * 
     * Например, если маршрут имеет вид "user/account/view/17", а назначенные параметры 
     * `['id' => 3, 'param' => 4]`, то результатом будет `[id' => 17, 'param' => null]`.
     * 
     * Если параметр "action" или "controller" не указан, то их значения будут определяться 
     * из {@see BaseMatcher::getDefaultController()} и {@see BaseMatcher::getDefaultAction()}.
     * Если указанный порядковый номер отсутствует в сегменте маршрута, то назначенный параметр
     * будет иметь значение `null`.
     * 
     * @see BaseMatcher::$assign
     * 
     * @param array $segments Сегменты маршрута полученные при его разборе, например: 
     *     `['user', 'account', 'view', 17]`.
     * 
     * @return array
     */
    protected function defineAssignment(array $segments): array
    {
        $result = [];
        foreach ($this->assign as $name => $index) {
            $result[$name] = $segments[$index] ?? null;
        }
    
        if (!isset($result['controller'])) {
            $result['controller'] = $this->getDefaultController();
        }
        if (!isset($result['action'])) {
            $result['action'] = $this->getDefaultAction();
        }
        return $result;
    }

    /**
     * Возвращает контроллер и действие согласно правилу перенаправления.
     * 
     * Правило перенаправления определяется свойством {@see BaseMatcher::$redirect},
     * например:
     * ```php
     * [
     *     'account@view' => ['user', 'view'], // контроллер 'account' => 'user'
     *     'profile@*'    => ['user', '*'], // контроллер 'profile' => 'user'
     * ]
     * ```
     * 
     * @see BaseMatcher::$redirect
     * 
     * @param string $controller Имя текущего контроллера, например 'account'.
     * @param string $action Имя текущего действия, например 'view'.
     * 
     * @return array Имя нового конроллера и действия (`['account', 'view']`).
     */
    protected function defineRedirect(string $controller, string $action = ''): array
    {
        // правила перенаправления
        $rules = [
            $controller . '@' .$action => true,
            '*@*'                      => true,
            '*@' .$action              => true,
            $controller . '@*'         => true
        ];
        foreach ($this->redirect as $rule => $result) {
            if (isset($rules[$rule])) {
                if ($result[0] === '*')
                    $result[0] = $controller;
                if ($result[1] === '*')
                    $result[1] = $action;
                return $result;
            }
        }
        return [$controller, $action];
    }

    /**
     * Возвращает имя контроллера, определенного из параметров маршрута модуля.
     * 
     * @param string $name Имя контроллера.
     *    Если не указано, значение будет из параметра "default" контроллера маршрута модуля.
     * 
     * @return string
     */
    public function getDefaultController(string $name = ''): string
    {
        $defaults = $this->defaults['controller'];
        if (is_array($defaults))
            return $name ? ($defaults[$name] ?? $name) : $defaults['default'];
        else
            return $name ?: $defaults;
    }

    /**
     * Возвращает имя действия контроллера, определенного из параметров маршрута модуля.
     * 
     * @param string $name Имя действия. Если не указано, значение будет из параметра 
     *     "default" действия маршрута модуля
     * 
     * @return string
     */
    public function getDefaultAction(string $name = ''): string
    {
        $defaults = $this->defaults['action'];
        if (is_array($defaults))
            return $name ? ($defaults[$name] ?? $name) : $defaults['default'];
        else
            return $name ?: $defaults;
    }

    /**
     * Компилирует правила сопоставления маршрута.
     * 
     * @return array
     */
    public function compile(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function match(): mixed
    {
        return false;
    }
}
