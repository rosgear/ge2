<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge;

/**
 * Вспомогательный класс Url, представлен в виде набора статических методов для 
 * управления URL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Url extends Helper
{
    /**
     * Создаёт URL-адрес с указанием параметров (компонентов URL-адреса).
     * 
     * @see \Ge\Url\UrlManager::createUrl()
     * 
     * @param string|array $params Параметры (компоненты) {@see \Ge\Url\UrlManager::buildUrl()} 
     *    используются при создании URL-адреса.
     * @param null|bool|string $ruleName Если значение `false`, правило не будет 
     *    применяться к компонентам URL. Если значение `null`, то используется правило 
     *    по умолчанию, иначе указывается имя правила (по умолчанию `null`).
     * 
     * @return string
     */
    public static function to(string|array $params, null|bool|string $ruleName = null): string
    {
        // подготовка компонентов URL согласно правилу $ruleName
        if ($ruleName !== false) {
            $params = (array) $params;
            static::$app->urlRules->prepareUrlComponents($params, $ruleName);
        }
        return static::$app->urlManager->createUrl($params);
    }

    /**
     * Создаёт URL-адрес для панели управления с указанием параметров (компонентов 
     * URL-адреса).
     * 
     * @see \Ge\Url\UrlManager::createUrl()
     * 
     * @param string|array $params Параметры (компоненты) {@see \Ge\Url\UrlManager::buildUrl()} 
     * используются при создании URL-адреса (по умолчанию `[]`).
     * 
     * @return string
     */
    public static function toBackend(string|array $params = []): string
    {
        static $backend;

        if ($backend === null)
            $backend = Ge::alias('@backend');
        $params = (array) $params;
        if (empty($params[0]))
            $params[0] = $backend;
        else
            $params[0] = $backend . '/' . $params[0];
        return static::$app->urlManager->createUrl($params);
    }

    /**
     * Добавляет маршрут (полученный из запроса) к создаваемому URL-адресу с указанием 
     * параметров (компонентов URL-адреса).
     * 
     * @see \Ge\Url\UrlManager::createUrl()
     * 
     * @param string|array $params Параметры (компоненты) {@see \Ge\Url\UrlManager::buildUrl()} 
     * используются при создании URL-адреса (по умолчанию `[]`).
     * 
     * @return string
     */
    public static function toRoute(string|array $params = []): string
    {
        static $route;

        if ($route === null)
            $route = Ge::alias('@route');

        $params = (array) $params;
        if (empty($params[0]))
            $params[0] = $route;
        else
            $params[0] = $route . '/' . $params[0];
        return static::$app->urlManager->createUrl($params);
    }

    /**
     * Добавляет маршрут (найденного модуля маршрутизатором) к создаваемому URL-адресу 
     * с указанием параметров (компонентов URL-адреса).
     * 
     * @see \Ge\Url\UrlManager::createUrl()
     * 
     * @param string|array $params Параметры (компоненты) {@see \Ge\Url\UrlManager::buildUrl()} 
     * используются при создании URL-адреса (по умолчанию `[]`).
     * 
     * @return string
     */
    public static function toMatch(string|array $params = []): string
    {
        static $match;

        if ($match === null) {
            $match = static::$app->router->get('baseRoute', '');
        }

        $params = (array) $params;
        if (empty($params[0]))
            $params[0] = $match;
        else
            $params[0] = $match . '/' . $params[0];
        return static::$app->urlManager->createUrl($params);
    }

    /**
     * Возвращает URL-адрес загруженного ресурса.
     * 
     * @see \Ge\Upload\Upload::$url

     * @return string
     */
    public static function uploads(): string
    {
        return static::$app->uploader->url;
    }

    /**
     * Добавляет указанный URL-путь к URL-адресу загруженного ресурса.
     * 
     * @see Url::uploads()
     * 
     * @param string $url URL-путь, например, '/images/sample.jpg'.
     * 
     * @return string
     */
    public static function toUploads(string $url): string
    {
        return static::$app->uploader->url . $url;
    }

    /**
     * Возвращает URL-адрес ресурса модулей.
     * 
     * URL-адрес имеет вид: "[<схема>://]<хост> </BASE_URL> </MODULE_BASE_URL>".
     * 
     * @param bool|null $scheme Если значение `true`, добавляет схему к имени хоста 
     *     (по умолчанию `null`).

     * @return string
     */
    public static function module(?bool $scheme = null): string
    {
        static $module;

        if ($scheme !== null)
            return self::home($scheme) . MODULE_BASE_URL;
        if ($module === null) {
            $module = BASE_URL . MODULE_BASE_URL;
        }
        return $module;
    }

    /**
     * Добавляет указанный URL-путь к URL-адресу ресурса модулей.
     * 
     * Имеет вид: "[<схема>://]<хост> </BASE_URL> </MODULE_BASE_URL> </URL-путь>".
     * 
     * @param string $url URL-путь, например, '/foo/bar'.
     * @param bool|null $scheme Если значение `true`, добавляет схему к имени хоста
     *     (по умолчанию `null`).
     * @return string
     */
    public static function toModule(string $url, ?bool $scheme = null): string
    {
        return self::module($scheme) . $url;
    }

    /**
     * Возвращает URL-адрес ресурса для публичного доступа.
     * 
     * URL-адрес имеет вид: "[<схема>://]<хост> </public>".
     * 
     * @param bool|null $scheme Если значение `true`, добавляет схему к имени хоста 
     *     (по умолчанию `null`).
     * 
     * @return string
     */
    public static function published(?bool $scheme = null): string
    {
        static $published;

        if ($scheme !== null) {
            return self::home($scheme) . static::$app->clientScript->baseUrl;
        }
        if ($published === null) {
            $published = static::$app->clientScript->publishedUrl;
        }
        return $published;
    }

    /**
     * Добавляет указанный URL-путь к URL-адресу ресурса для публичного доступа.
     * 
     * Имеет вид:: "[<схема>://]<хост> </public> </URL-путь>".
     * 
     * @see Url::published()
     * 
     * @param string $url URL-путь, например, '/foo/bar'.
     * @param bool $scheme Если значение `true`, добавляет схему к имени хоста
     *      (по умолчанию `null`).
     * 
     * @return string
     */
    public static function toPublished(string $url, ?bool $scheme = null): string
    {
        return self::published($scheme) . $url;
    }

    /**
     * Возвращает абсолютный URL-адрес ресурса текущей темы.
     * 
     * URL-адрес имеет вид: "</URL-путь к темам> </имя темы>".
     * 
     * @see \Ge\Theme\Theme::$url
     * 
     * @return string
     */
    public static function theme(): string
    {
        static $theme;

        if ($theme !== null) {
            return $theme;
        }
        return $theme = static::$app->theme->url;
    }

    /**
     * Добавляет указанный URL-путь к URL-адресу ресурса текущей темы.
     * 
     * Имеет вид:: "[<схема>://]<хост> </public> </URL-путь>".
     * 
     * @param string $url URL-путь, например, '/foo/bar'.
     * @param bool $scheme Если значение `true`, добавляет схему к имени хоста
     *     (по умолчанию `null`).
     * 
     * @return string
     */
    public static function toTheme(string $url, ?bool $scheme = null): string
    {
        static $theme;

        if ($scheme !== null) {
            return self::home($scheme) . static::$app->theme->url;
        }
        if ($theme === null) {
            $theme = static::$app->theme->url;
        }
        return $theme;
    }

    /**
     * Возвращает абсолютный URL-адрес ресурсов тем.
     * 
     * URL-адрес имеет вид: "<схема>://<хост> </URL-путь к темам>".
     * 
     * @see \Ge\Theme\Theme::$themesUrl
     * 
     * @return string
     */
    public static function themes(): string
    {
        static $themes;

        if ($themes !== null) {
            return $themes;
        }
        return $themes = static::$app->theme->themesUrl;
    }

    /**
     * Возвращает информацию о хосте (с базовым URL-путём).
     * 
     * Имеет вид: 
     * - "[<схема>://]<хост> <BASE_URL>" если `$scheme = true`;
     * - "<BASE_URL>" если `$scheme = false`;
     * - "<хост> <BASE_URL> если `$scheme = ''`".
     * 
     * Если приложение установлено не в "корень", а в указанную директорию. Базовый 
     * URL-путь включает эту директорию.
     * 
     * @param string|bool $scheme Если значение `true`, добавляет схему к имени хоста
     *    (по умолчанию `true`).
     * 
     * @return string
     */
    public static function home(string|bool $scheme = true): string
    {
        static $home = [];

        if (isset($home[$scheme])) {
            return $home[$scheme];
        }
        if ($scheme === true) {
            return $home[$scheme] = self::scheme() . $_SERVER['SERVER_NAME'] . BASE_URL;
        }
        if ($scheme === false) {
            return $home[$scheme] = BASE_URL;
        }
        if ($scheme === '') {
            return $home[$scheme] = '//' . $_SERVER['SERVER_NAME'] . BASE_URL;
        }
        return $home = self::scheme() . $_SERVER['SERVER_NAME'] . BASE_URL;
    }

    /**
     * Возвращает информацию о хосте.
     * 
     * Имеет вид: "[<схема>://]<хост>".
     * 
     * @param bool $scheme Если значение `true`, добавляет схему к имени хоста
     *     (по умолчанию `true`).
     * 
     * @return string
     */
    public static function host(bool $scheme = true): string
    {
        static $host = [];

        if (isset($host[$scheme]))
            return $host[$scheme];
        if ($scheme === true)
            return $host[$scheme] = self::scheme() . $_SERVER['SERVER_NAME'];
        if ($scheme === false)
            return $host[$scheme] = BASE_URL;
        if ($scheme === '')
            return $host[$scheme] = '//' . $_SERVER['SERVER_NAME'];
        return $host = self::scheme() . $_SERVER['SERVER_NAME'];
    }

    /**
     * Возвращает имя хоста.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return string
     */
    public static function hostInfo(): string
    {
        return $_SERVER['SERVER_NAME'] ?? '';
    }

    /**
     * Возвращает имя бренда из имени хоста.
     * 
     * @return string
     */
    public static function brandName(): string
    {
        $name = $_SERVER['SERVER_NAME'] ?? '';
        $parts = explode('.', $name);
        if (isset($parts[0])) {
            $name = $parts[0];
        }
        return ucfirst($name);
    }

    /**
     * Возвращает схему URL-адреса.
     * 
     * @see Url::isSSL()
     * 
     * @return string Возвращает "https://" ил "http://".
     */
    public static function scheme(): string
    {
        static $scheme;

        if ($scheme === null) {
            $scheme = self::isSSL() ? 'https://' : 'http://';
        }
        return $scheme;
    }

    /**
     * Проверяет, является ли URL-адрес основным адресом сайта.
     * 
     * @see \Ge\Url\UrlManager::isHome()
     * 
     * @return bool
     */
    public static function isHome(): bool
    {
        static $home;

        if ($home === null) {
            $home = static::$app->urlManager->isHome();
        }
        return $home;
    }

    /**
     * Проверка в запросе SSL сертификата.
     * 
     * @return bool
     */
    public static function isSSL(): bool
    {
        static $isSSL;

        if ($isSSL !== null) {
            return $isSSL;
        }
        if (isset($_SERVER['HTTPS'])) {
            return $isSSL = !empty($_SERVER['HTTPS']) && stristr($_SERVER['HTTPS'], 'off') === false;
        } else {
            if (isset($_SERVER['SERVER_PORT']))
                return $isSSL = '443' == $_SERVER['SERVER_PORT'];
            else
                return $isSSL = false;
        }
    }

    /**
     * Возвращает URL-адрес сценария (без имени файла), который обрабатывает запрос.
     * 
     * @see \Ge\Url\UrlManager::getScriptUrl()
     * 
     * @return string
     */
    public static function scriptUrl(): string
    {
        static $scriptUrl;

        if ($scriptUrl === null) {
            $scriptUrl = static::$app->urlManager->getScriptUrl();
        }
        return $scriptUrl;
    }

    /**
     * Возвращает имя сценария обрабатывающий запрос.
     * 
     * @see \Ge\Url\UrlManager::getScript()
     * 
     * @return string
     */
    public static function scriptName(): string
    {
        static $scriptName;

        if ($scriptName === null) {
            $script     = static::$app->urlManager->getScript();
            $scriptName = $script['filename'] ?? '';
        }
        return $scriptName;
    }

    /**
     * Возвращает URL-путь к сценарию обрабатывающий запрос.
     * 
     * @see \Ge\Url\UrlManager::getScript()
     * 
     * @return string
     */
    public static function scriptPath(): string
    {
        static $scriptPath;

        if ($scriptPath === null) {
            $script     = static::$app->urlManager->getScript();
            $scriptPath = $script['path'] ?? '';
        }
        return $scriptPath;
    }

    /**
     * Возвращает URL-адрес, собранный из составных частей.
     * 
     * Схема сборки URL-адреса (RFC 3986):
     *    - <схема>:[//[<логин>[:<пароль>]@] <хост> [:<порт>]] [/<URL-путь>] [?<параметры>] [#<якорь>]
     *    - <scheme>:[//[<user>[:<pass>]@] <host> [:<port>]] [/<path>] [?<query>] [#<fragment>]
     * 
     * @link https://tools.ietf.org/html/rfc3986
     * 
     * @param array $components Компоненты URL:
     *    - scheme: схема;
     *    - user: логин;
     *    - pass: пароль;
     *    - host: хост; 
     *    - port: порт;
     *    - path: URL-путь;
     *    - ? или query: параметры запроса;
     *    - # или fragment: якорь.
     * @param bool $define Если значение `true`, такие компоненты как: scheme, host 
     *    определяются из паременных сервера.
     * 
     * @return string
     */
    public static function build(array $components, bool $define = true): string
    {
        // http://username:password@hostname:9090/path?arg=value#anchor
        $url = '';
        $scheme = $components['scheme'] ?? ($define ? (self::isSSL() ? 'https' : 'http') : '');
        if ($scheme) {
            if ($scheme != '//')
                $url .= $scheme . '://';
        }
        // [<user>[:<pass>]@]
        $user = $components['user'] ?? '';
        if ($user)
            $url .= $user;
        $pass = $components['pass'] ?? '';
        if ($pass)
            $url .= ':' . $pass;
        if ($user)
            $url .= '@';
        // <хост>
        $url .= ($components['host'] ?? ($define ? self::hostInfo() : ''));
        // [:<порт>]]
        $port = $components['port'] ?? '';
        if ($port)
            $url .= ':' . $port;
        // [/<URL-путь>]
        $path = $components['path'] ?? '';
        // [?<параметры>]
        $query = $components['?'] ?? ($components['query'] ?? []);
        // [#<fragment>]
        $fragment = $components['#'] ?? ($components['fragment'] ?? '');

        // правая часть адреса
        if ($path || $query || $fragment) {
            $url .= '/';
        }
        if ($path)
            $url .= ltrim($path, '/');

        // если URL-адрес локальный
        $local = $components['local'] ?? '';
        if ($local && $url === '/')
            $url = '';

        if ($query)
            $url .= '?' . urldecode(http_build_query($query));
        if ($fragment)
            $url .= '#' . $fragment;
        return $url;
    }
}
