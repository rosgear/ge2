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
use Ge\Exception;

/**
 * Сегментное сопоставление (частями) маршрута с применением регулярного выражения.
 * 
 * Опции используемые для проверки и сопоставления маршрута:
 * - "restrictions", ограничения выбора контроллера {@see BaseMatcher::$restrictions};
 * {@see BaseMatcher::$assign};
 * - "redirect", правила перенаправления контроллера и действия {@see BaseMatcher::$redirect};
 * - "method", метод запроса {@see BaseMatcher::$method}.
 * 
 * Пример:
 * ```php
 * // для маршрута "https://domain.com/user/account/view/17"
 *  Ge::$app->router->match([
 *     'type'        => 'segments',
 *     'module'      => 'site.frontend.user',
 *     'route'       => 'user/[:controller[/:action[/:id]]]',
 *     'constraints' => [
 *         'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
 *         'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
 *         'id'         => '[0-9_-]+'
 *     ]
 * ]);
 * // результат: ['action' => 'view', 'controller' => 'account', 'id' => 17, ...].
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher\Http
 * @since 2.0
 */
class Segments extends BaseMatcher
{
    /**
     * Кэш для вывода кодирования.
     * 
     * @see Segments::encode()
     * 
     * @var array
     */
    protected static array $cacheEncode = [];

    /**
     * Карта разрешенных специальных символов в сегментах пути.
     *
     * http://tools.ietf.org/html/rfc3986#appendix-A
     * segement      = *pchar
     * pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *               / "*" / "+" / "," / ";" / "="
     *
     * @var array
     */
    protected static array $urlencodeCorrectionMap = [
        '%21' => "!", // sub-delims
        '%24' => "$", // sub-delims
        '%26' => "&", // sub-delims
        '%27' => "'", // sub-delims
        '%28' => "(", // sub-delims
        '%29' => ")", // sub-delims
        '%2A' => "*", // sub-delims
        '%2B' => "+", // sub-delims
        '%2C' => ",", // sub-delims
        '%3A' => ":", // pchar
        '%3B' => ";", // sub-delims
        '%3D' => "=", // sub-delims
        '%40' => "@", // pchar
    ];

    /**
     * Части маршрута.
     * 
     * @see Segments::defineOptions()
     * @see Segments::compile()
     * 
     * @var array
     */
    protected array $parts = [];

    /**
     * Регулярное выражение, используемое для сопоставления маршрута.
     * 
     * @see Segments::defineOptions()
     * @see Segments::compile()
     * 
     * @var string
     */
    protected string $regex = '';

    /**
     * Сопоставление групп регулярных выражений с именами параметров.
     * 
     * @see Segments::defineOptions()
     * 
     * @var array
     */
    protected array $paramMap = [];

    /**
     * Ключи перевода, используемые в регулярном выражении.
     * 
     * @see Segments::buildRegex()
     * 
     * @var array
     */
    protected array $translationKeys = [];

    /**
     * Подготовленные (сформированные) опции для проверки соответствия.
     * 
     * Это те опции, которые занимают много машинного времени, такие опции 
     * заранее скомпилированы {@see \Ge\Router\Matcher\Http\BaseMatcher::compile()} и 
     * заложены в конфигурацию маршрута.
     *
     * @var array
     */
    protected array $compiled = [];

    /**
     * Разберает определение маршрута.
     *
     * @param string $def
     * @return array
     * 
     * @throws Exception\RuntimeException
     */
    protected function parseRouteDefinition(string $def): array
    {
        $currentPos = 0;
        $length     = strlen($def);
        $parts      = [];
        $levelParts = [&$parts];
        $level      = 0;

        while ($currentPos < $length) {
            preg_match('(\G(?P<literal>[^:{\[\]]*)(?P<token>[:{\[\]]|$))', $def, $matches, 0, $currentPos);

            $currentPos += strlen($matches[0]);

            if (!empty($matches['literal'])) {
                $levelParts[$level][] = array('literal', $matches['literal']);
            }

            if ($matches['token'] === ':') {
                if (!preg_match('(\G(?P<name>[^:/{\[\]]+)(?:{(?P<delimiters>[^}]+)})?:?)', $def, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Found empty parameter name');
                }

                $levelParts[$level][] = array('parameter', $matches['name'], isset($matches['delimiters']) ? $matches['delimiters'] : null);

                $currentPos += strlen($matches[0]);
            } elseif ($matches['token'] === '{') {
                if (!preg_match('(\G(?P<literal>[^}]+)\})', $def, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Translated literal missing closing bracket');
                }

                $currentPos += strlen($matches[0]);

                $levelParts[$level][] = array('translated-literal', $matches['literal']);
            } elseif ($matches['token'] === '[') {
                $levelParts[$level][] = array('optional', []);
                $levelParts[$level + 1] = &$levelParts[$level][count($levelParts[$level]) - 1][1];

                $level++;
            } elseif ($matches['token'] === ']') {
                unset($levelParts[$level]);
                $level--;

                if ($level < 0) {
                    throw new Exception\RuntimeException('Found closing bracket without matching opening bracket');
                }
            } else {
                break;
            }
        }
        if ($level > 0) {
            throw new Exception\RuntimeException('Found unbalanced brackets');
        }
        return $parts;
    }

    /**
     * Создаёт соответствующее регулярное выражение из частей.
     *
     * @param array $parts
     * @param array $constraints
     * @param int $groupIndex
     * 
     * @return string
     */
    protected function buildRegex(array $parts, array $constraints, &$groupIndex = 1): string
    {
        $regex = '';

        foreach ($parts as $part) {
            switch ($part[0]) {
                case 'literal':
                    $regex .= preg_quote($part[1]);
                    break;

                case 'parameter':
                    $groupName = '?P<param' . $groupIndex . '>';

                    if (isset($constraints[$part[1]])) {
                        $regex .= '(' . $groupName . $constraints[$part[1]] . ')';
                    } elseif ($part[2] === null) {
                        $regex .= '(' . $groupName . '[^/]+)';
                    } else {
                        $regex .= '(' . $groupName . '[^' . $part[2] . ']+)';
                    }

                    $this->paramMap['param' . $groupIndex++] = $part[1];
                    break;

                case 'optional':
                    $regex .= '(?:' . $this->buildRegex($part[1], $constraints, $groupIndex) . ')?';
                    break;

                case 'translated-literal':
                    $regex .= '#' . $part[1] . '#';
                    $this->translationKeys[] = $part[1];
                    break;
            }
        }
        return $regex;
    }

    /**
     * Кодирует сегмент пути.
     *
     * @param string $value
     * 
     * @return string
     */
    protected function encode(string $value): string
    {
        $key = $value;
        if (!isset(static::$cacheEncode[$key])) {
            static::$cacheEncode[$key] = rawurlencode($value);
            static::$cacheEncode[$key] = strtr(static::$cacheEncode[$key], static::$urlencodeCorrectionMap);
        }
        return static::$cacheEncode[$key];
    }

    /**
     * Декодирует сегмент пути.
     *
     * @param string $value
     * 
     * @return string
     */
    protected function decode(string $value): string
    {
        return rawurldecode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function defineOptions(array $options): void
    {
        parent::defineOptions($options);

        $this->compiled = $options['compiled'] ?? [];
        if ($this->compiled) {
            $this->regex = $this->compiled['regex'] ?? [];
            // добавляем префикс, т.к. компиляция была без него
            if ($this->prefix)
                $this->regex = $this->prefix . '/' . $this->regex;
            $this->paramMap = $this->compiled['paramMap'] ?? [];
        } else {
            $this->parts = $this->parseRouteDefinition($this->route);
            $this->regex = $this->buildRegex($this->parts, $this->constraints);
        }
    }

    /**
     * Компилирует.
     * 
     * @return array
     */
    public function compile(): array
    {
        // построение шаблона происходит без префикса маршрута, чтобы в дальнейшем 
        // поменяв префикс не использовать compile()
        $this->parts = $this->parseRouteDefinition($this->baseRoute);
        $this->regex = $this->buildRegex($this->parts, $this->constraints);
        return [
            'regex'    => $this->regex,
            'paramMap' => $this->paramMap
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * @param null|string $route Cопоставляемый маршрут, например 'user/account' (по умолчанию `null`).
     * @param null|int $pathOffset Cмещение относительно маршрута, часть которого будет 
     *     сопоставляться (по умолчанию `null`).
     */
    public function match(?string $route = null, ?int $pathOffset = null): mixed
    {
        if ($route === null) {
            $route = Ge::$app->urlManager->route;
        }

        // проверка принадлежности маршрута модулю
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
         * Результат имеет вид: `['controller' => 'name', 'action' => 'name', ...]`
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
        if (($pos = mb_strpos($this->regex, '(')) !== false)
            $baseRoute = mb_substr($this->regex, 0, $pos);
        else
            $baseRoute = $route;

        $controller = $this->getDefaultController($params['controller'] ?? '');
        $action     = $this->getDefaultAction($params['action'] ?? '');

        // проверка ограничений контроллера если они указаны
        if ($this->restrictions) {
            if (in_array($controller, $this->restrictions)) return null;
        }

        // проверка правил перенаправления контроллера и действия если они указаны
        if ($this->redirect) {
            list($controller, $action) = $this->defineRedirect($controller, $action);
        }

        return [
            'baseRoute'  => $baseRoute, // базовый маршрут
            'namespace'  => $this->namespace, // название пространства имён
            'module'     => $this->module, // идентификатор модуля
            'controller' => $controller, // имя контроллера
            'action'     => $action, // имя действия
            'params'     => $params // параметры запроса
        ];
    }
}
