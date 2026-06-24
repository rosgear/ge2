<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge\Exception;

/**
 * Класс Route выполняет проверку и сопоставление маршрутов. Применяется в качестве 
 * вспомогательного класса при определении маршрутов запроса.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Route extends Helper
{
    /**
     * Выполняет проверку запроса пользователя по указанному шаблону.
     * 
     * В качестве шаблона может быть строка URI (например, '/user/{id}') или параметры
     * запроса (например, `['uri' => '/user/{id}', 'domain' => 'domain.com', ...]`).
     * 
     * Пример простого шаблона:
     * ```php
     * Url::match('/user/account', 'rg.fe.account::Account@view');
     * ```
     * Пример с указанием параметров в шаблоне поиска:
     * ```php
     * Url::match(
     *     ['/user/{id}', ['id' => '[0..9]+']], 
     *     function (int $id = 0) { 
     *         return 'User ID: ' . $id;
     *     }
     * );
     * ```
     * Пример с указанием дополнительных параметров поиска:
     * ```php
     * Url::match(
     *     ['uri' => '/user/{id}', 'where' => ['id' => '[0..9]+'], 'domain' => 'domain.com'], 
     *     function (int $id = 0) { 
     *         return 'User ID: ' . $id;
     *     }
     * );
     * ```
     * 
     * @param string|array<string|int, mixed> $pattern  Шаблон в виде URI строки или 
     *     параметры запроса.
     * @param null|string|callable $callback Callback-функция с параметрами (если 
     *     они указаны в шаблоне). Результат передаётся match (по умолчанию `null`).
     * @param array<string, mixed> $options Дополнительные параметры (по умолчанию `[]`).
     * 
     * @return bool
     */
    public static function match(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
        ): bool
    {
        if (is_array($pattern)) {
            // если вид шаблона: `['/user/{id}', ['id' => '[0..9]+']]`
            if (array_is_list($pattern)) {
                $uri   = $pattern[0] ?? '';
                $where = $pattern[1] ?? [];
            // если вид шаблона: `['uri' => '/user/{id}', 'where' => ['id' => '[0..9]+'], ...]`
            } else {
                $uri   = $pattern['uri'] ?? '';
                $where = $pattern['where'] ?? [];
                if (!static::$app->request->validateParams($pattern, true)) {
                    return false;
                }
            }
        } else {
            $uri   = $pattern;
            $where = [];
        }

        if (empty($uri)) {
            throw new Exception\InvalidArgumentException('Incorrectly specified URI pattern.');
        }

        $uriCompare = '/' .  static::$app->urlManager->requestUri;
        $uriPattern = str_replace('/', '\/', $uri);

        // получить шаблон: '/user/{id}' => '\/user\/([^\/]+)'
        $pregPattern = preg_replace_callback('/{(.*?)}/', function ($match) use ($where) {
            $key = $match[1]; 
            return isset($where[$key]) ? '(' . $where[$key] . ')' : '([^\/]+)';
        }, $uriPattern);

        // если в шаблоне выражения '{...}'
        if ($pregPattern !== $uriPattern)
            $success = preg_match('/^' . $pregPattern . '$/', $uriCompare, $matches) === 1;
        else
            $success = $uriCompare === $uri;

        // если в шаблоне нет выражений и маршруты совпадают
        if ($success) {
            if (isset($matches))
                unset($matches[0]);
            else
                $matches = [];
            if (is_callable($callback)) {
                if ($matches)
                    $result = call_user_func_array($callback, $matches);
                else
                    $result = call_user_func($callback);
                if (is_bool($result))
                    return $result;
                else
                if (!is_null($result)) {
                    static::$app
                        ->response
                            ->setContent($result)
                                ->send();
                } 
                return true;
            } elseif (is_string($callback) || is_array($callback))
                static::$app->runAs($callback, $matches, $options['params'] ?? []);
            else
                return true;
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом GET.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isGet(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('GET')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом POST.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isPost(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('POST')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом PUT.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isPut(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('PUT')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом PATCH.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isPatch(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('PATCH')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом DELETE.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isDelete(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('DELETE')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута при запросе методом OPTIONS.
     * 
     * @see Route::match()
     * 
     * @param string|array<string, mixed> $pattern 
     * @param null|string|callable $callback
     * @param array<string, mixed> $options
     * 
     * @return bool
     */
    public static function isOptions(
        string|array $pattern, 
        null|string|callable $callback = null,
        array $options = []
    ): bool
    {
        if (static::$app->request->isMethod('OPTIONS')) {
            return static::match($pattern, $callback, $options);
        }
        return false;
    }

    /**
     * Выполняет проверку маршрута.
     * 
     * @see \Ge\Url\UrlManager::$requestUri
     * 
     * @param string $uri Строка URI, должна всегда начинаться с '/';
     * 
     * @return bool
     */
    public static function is(string $uri): bool
    {
        return '/' .  static::$app->urlManager->requestUri === $uri;
    }

    /**
     * Определяет маршруты, специфичные для поддомена приложения.
     * 
     * @param string $domain Имя домена.
     * @param callable $callback Callback-функция вызывается, если маршрут 
     *     соответствует указанному домену.
     * 
     * @return void
     */
    public static function isDomain(string $domain, callable $callback): void
    {
        if (static::$app->request->isHost($domain)) {
            call_user_func($callback);
        }
    }

    /**
     * Определяет маршруты по указанному префиксу.
     * 
     * @param string $prefix Префикс, например, 'admin'.
     * @param callable $callback Callback-функция вызывается, если часть маршрута 
     *     соответствует префиксу.
     * 
     * @return void
     */
    public static function isPrefix(string $prefix, callable $callback): void
    {
        if (static::$app->urlManager->segments->first() === $prefix) {
            call_user_func($callback);
        }
    }
}
