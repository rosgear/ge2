<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Url;

use Ge;
use Ge\Stdlib\Service;
use Ge\Helper\Url;
use Ge\Helper\Str;
use Ge\Http\Request;
use Ge\Exception;

/**
 * URL Менеджер анализирует и обрабатывает HTTP-запросы и создаёт новые URL-адреса на 
 * основе установленных правил.
 * 
 * UrlManager - это служба приложения, доступ к которой можно получить через `Ge::$app->urlManager`.
 * 
 * Для формирования URL-адреса используются свойства класса:
 *    - $showScriptName = true, $enablePrettyUrl = false,
 *    пример: /index.php?r=news/post;
 *    - $showScriptName = false, $enablePrettyUrl = false,
 *    пример: /?r=news/post;
 *    - $showScriptName = false, $enablePrettyUrl = true, $enableTrailingSlash = false,
 *    пример: /news/post;
 *    - $showScriptName = false, $enablePrettyUrl = true, $enableTrailingSlash = true,
 *    пример: /news/post/;
*    - $showScriptName = true, $enablePrettyUrl = true, $enableTrailingSlash = false,
 *    пример: /index.php/news/post;
*    - $showScriptName = true, $enablePrettyUrl = true, $enableTrailingSlash = true,
 *    пример: /index.php/news/post/;
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Url
 * @since 2.0
 */
class UrlManager extends Service
{
    /**
     * {@inheritdoc}
     */
     protected bool $useUnifiedConfig = true;

    /**
     * Имя переменной сервера для получения маршрута.
     * 
     * Используется при включенном ЧПУ.
     * 
     * @var string
     */
    public string $requestParam = 'REQUEST_URI';

    /**
     * Параметр запроса URL-адреса для определения маршрута 
     * запроса (URL-пути).
     * 
     * Маршрут (URL-путь) будет получен из метода запроса GET.
     * 
     * Пример: "/?r=news/post".
     * 
     * @var string
     */
    public string $routeParam = 'r';

    /**
     * Добавление слеша в конец URL-адреса при его формировании.
     * 
     * Пример: "/news/post" => "/news/post/".
     * 
     * @var bool
     */
    public bool $enableTrailingSlash = false;

    /**
     * Включает ЧПУ для получения красивых URL-адресов.
     * 
     * Все параметры в строке запроса URL, будут преобразованы в составные 
     * части маршрута  URL-адреса.
     * 
     * Пример: "/?r=news/post" => "/news/post".
     * 
     * @var bool
     */
    public bool $enablePrettyUrl = true;

    /**
     * Подставляет имя файла главного сценария при формировании 
     * URL-адреса.
     * 
     * Пример: "/index.php/news/post" => "/news/post".
     * 
     * @var bool
     */
    public bool $showScriptName = false;

    /**
     * Добавление базового пути при формировании URL-адреса скрипта.
     * 
     * Если false, будет добавляться BASE_URL, иначе - вручную указывается 
     * путь (чтобы не было редиректа при подключении скрипта).
     * 
     * @var bool|string
     */
    public bool $scriptBaseUrl = false;

    /**
     * Замена директории файла главного сценария на указанную.
     * 
     * Замена необходима в том случаи, если главный сценарий (index.php) находится не 
     * в корне, а в указанной директории. Это необходимо при формировании URL-адреса в 
     * соответствии с правилами .htaccess (если в нём есть перенаправление).
     * 
     * Пример: "/public/index.php" => "/index.php".
     * 
     * @see UrlManager::getScript()
     * 
     * @var array
     */
    public array $scriptRedirect = [PUBLIC_BASE_URL . '/' => '/'];

    /**
     * Маршрут запроса (URL-путь).
     * 
     * Определяется из параметра запроса URL-адреса ($routeParam) или 
     * из переменной сервера ($requestParam).
     * 
     * Пример: /?r=news/post => news/post.
     * 
     * @var string
     */
    public string $route = '';

    /**
     * Маршрут запроса (URL-путь) к панели управления.
     * 
     * Определяется параметром "backend/route" конфигурации 
     * приложения (application.config.php).
     * 
     * @var string
     */
    public string $backendRoute = '';

    /**
     * URL-путь к вызываемому модулю, полученный из маршрута запроса.
     * 
     * @see UrlManager::getModuleRoute()
     * 
     * @var string
     */
    protected string $moduleRoute;

    /**
     * URI-запрос, полученный из разбора URL-пути.
     * 
     * @see UrlManager::parseRequest()
     * 
     * @var string
     */
    public string $requestUri = '';

    /**
     * Имя файла, полученное из URL-адреса.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Сегменты (части) маршрута полученные с помощью разделителя (слеша).
     * 
     * @see \Ge\Url\PathSegments
     * 
     * @var PathSegments
     */
    public PathSegments $segments;

    /**
     * URL-адрес является корневым.
     * 
     * @see UrlManager::parseRequest()
     * 
     * @var bool
     */
    public bool $isRootUrl = false;

    /**
     * Сохранить историю перехода между страницами в истории браузера.
     * 
     * Настройка на стороне клиента для Java Script (используется windows.history).
     * 
     * @var bool
     */
    public bool $browserHistory = false;

    /**
     * URL-адрес сценария (без имени файла), который обрабатывает запрос.
     * 
     * @see UrlManager::getScriptUrl()
     * 
     * @var string
     */
    private string $_scriptUrl;

    /**
     * URL-адрес сценария (с именем файла), который обрабатывает запрос.
     * 
     * @see UrlManager::getScript()
     * 
     * @var array{filename:string, url:string, path:string}
     */
    private array $_script;

    /**
     * Относится ли маршрут запроса к маршруту панели управления.
     * 
     * @see UrlManager::isBackendRoute()
     * 
     * @var bool
     */
    private bool $_isBackendRoute;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'urlManager';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // маршрут к панели управления из файла конфигурации приложения
        $backend = Ge::$app->config->get(BACKEND);
        $this->backendRoute = $backend['route'];
        // Параметр конфигурации scriptBaseUrl устанавливается в крайнем случаи (вручную, чтобы не использовать BASE_URL), 
        // но если он false, тогда определяется через BASE_URL
        if ($this->scriptBaseUrl === false && BASE_URL) {
            // экранирование символом "/", чтобы не было перехода с ошибкой 301 (из-за добавления браузером слеша в конец)
            $this->scriptBaseUrl = '/'. trim(BASE_URL, '/') . '/';
        }
        // разбор URL-пути
        $request = isset($_SERVER[$this->requestParam]) ? $_SERVER[$this->requestParam] : '';
        if ($request) {
            $request = $this->parseRequest($request);
            $this->filename = $request['filename'];
            $this->route    = $request['path'];
        }
        $this->segments = new PathSegments($this->route);
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        // маршрут без запроса (приставки) к панели управления
        Ge::setAlias('@routeOne', ltrim($this->route, $this->backendRoute . '/'));
        Ge::setAlias('@route',    $this->route);
        Ge::setAlias('@backend',  $this->backendRoute);
        Ge::setAlias('@filename', $this->filename);
        Ge::setAlias('@requestUri', $this->requestUri);
    }

    /**
     * @see UrlManager::explodeRoute()
     * 
     * @var array
     */
    private array $_explodeRoute;

    /**
     * Разбивает маршрут с помощью разделителя.
     * 
     * @return array
     */
    public function explodeRoute(): array
    {
        if (!isset($this->_explodeRoute)) {
            $this->_explodeRoute = $this->route ? explode('/', $this->route) : [];
        }
        return $this->_explodeRoute;
    }

    /**
     * Возвращает расширение файла.
     * 
     * @see UrlManager::getFileInfo()
     * 
     * @return string Если расширение файла отсутствует, то результатом будет ''.
     */
    public function getBasename(): string
    {
        $info = $this->getFileInfo();
        return $info['filename'] ?? '';
    }

    /**
     * Возвращает расширение файла.
     * 
     * @see UrlManager::getFileInfo()
     * 
     * @return string Если расширение файла отсутствует, то результатом будет ''.
     */
    public function getExtension(): string
    {
        $info = $this->getFileInfo();
        return $info['extension'] ?? '';
    }

    /**
     * @see UrlManager::getFileInfo()
     * 
     * @var array
     */
    private array $_fileinfo;

    /**
     * Возвращает информацию о пути к файлу.
     * 
     * @link https://www.php.net/manual/ru/function.pathinfo.php
     * 
     * @return array
     */
    public function getFileInfo(): array
    {
        if (!isset($this->_fileinfo)) {
            if ($this->filename)
                $this->_fileinfo = pathinfo($this->filename);
            else
                $this->_fileinfo = [];
        }
        return $this->_fileinfo;
    }

    /**
     * Добавляет слеш в конец или в начало адресной строки.
     * 
     * Если {@see UrlManager::$enableTrailingSlash} = true, добавляет слеш в конец строки, 
     * иначе используется аргумент $preffix.
     * 
     * @param string $str Адресная строка.
     * @param bool $preffix Если true, добавляет в начало строки, иначе в конец.
     * 
     * @return string
     */
    public function trailingSlash(string $str, bool $preffix = true): string
    {
        if (empty($str)) return '';

        if ($this->enableTrailingSlash)
            return $str .= '/';
        else
            if ($preffix)
                return '/' . $str;
            else
                return $str;
    }

    /**
     * Удаляет из начала строки базовый URL-путь (BASE_URL).
     * 
     * Базовый URL-путь (BASE_URL) соответсвует директории куда установлено приложение
     * (если приложение установлено не в корень, а в указанную директорию).
     * 
     * @param string $url URL-путь.
     * 
     * @return string
     */
    public function shiftBaseUrl(string $url): string
    {
        if (BASE_URL) {
            $baseUrl = ltrim(BASE_URL, '/');
            return substr($url, strlen($baseUrl) + 1);
        }
        return $url;
    }

    /**
     * Добавляет в начало строки базовый URL-путь (BASE_URL).
     * 
     * Базовый URL-путь (BASE_URL) соответсвует директории куда установлено приложение
     * (если приложение установлено не в корень, а в указанную директорию).
     * 
     * @param string $url URL-путь.
     * 
     * @return string
     */
    public function appendBaseUrl(string $url): string
    {
        if (BASE_URL) {
            return BASE_URL . '/' . $url;
        }
        return $url;
    }

    /**
     * Разбор URL-пути на путь и имя файла.
     * 
     * @param string $uri URL-путь.
     * 
     * @return array
     */
    public function parseRequest(string $uri): array
    {
        $path = $filename = '';

        if ($uri) {
            $script = $this->getScript();
            // определение, является ли URL-путь корнем сайта
            // $isRootUrl служит для проверки маршрута {@see \Ge\Router::run()}
            $this->isRootUrl = parse_url($uri, PHP_URL_PATH) === $script[$this->showScriptName ? 'url' : 'path'];
            // если необходимо убрать название скрипта (index.php) в запросе
            if ($this->showScriptName) {
                $uri = Str::ltrimWord($uri, $script['url']);
            }

            $uri = ltrim($uri, '/');
            // с ЧПУ
            if ($this->enablePrettyUrl) {
                // если приложение находится в указанном каталоге,
                // то удаляется из запроса префикс BASE_URL
                if (!$this->showScriptName)
                    $uri = $this->shiftBaseUrl($uri);
                $urlInfo = parse_url($uri);
                if (!isset($urlInfo['path']))
                    return ['path' => '', 'filename' => ''];
                $uri = $urlInfo['path'];
            // без ЧПУ
            } else {
                $uri = $_GET[$this->routeParam] ?? false;
                if ($uri === false)
                    return ['path' => '', 'filename' => ''];
            }

            $uri = ltrim($uri, '/');
            // определение названия файла в запросе
            if (strpos($uri, '.' ) !== false) {
                $filename = pathinfo($uri, PATHINFO_BASENAME);
                $path = pathinfo($uri, PATHINFO_DIRNAME);
                if ($path == '.')
                    $path = '';
                $this->requestUri = $uri;
            } else {
                $this->requestUri = $path = rtrim($uri, '/');
            }
        }
        return [
            'path'     => $path,
            'filename' => $filename
        ];
    }

    /**
     * Проверят, относится ли маршрут запроса к маршруту панели управления.
     * 
     * @return bool
     */
    public function isBackendRoute(): bool
    {
        if (isset($this->_isBackendRoute)) {
            return $this->_isBackendRoute;
        }
        return $this->_isBackendRoute = strpos($this->route, $this->backendRoute) === 0;
    }

    /**
     * Устанавливает маршрут запроса.
     * 
     * @param string $route Маршрут запроса.
     * 
     * @return $this
     */
    public function setRoute(string $route): static
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Возвращает URL-адрес сценария (без имени файла), который обрабатывает запрос.
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    public function getScriptUrl(): string
    {
        if (!isset($this->_scriptUrl)) {
            $request = Ge::$app->request;
            if ($request instanceof Request) {
                if ($this->scriptBaseUrl)
                    $this->_scriptUrl = $this->scriptBaseUrl . basename($request->getScriptFile());
                else
                    $this->_scriptUrl = $request->getScriptUrl();
            } else {
                throw new Exception\RuntimeException(
                    Ge::t('app', 'Please configure UrlManager::scriptUrl correctly as you are running a console application')
                );
            }
        }
        return $this->_scriptUrl;
    }

    /**
     * Возвращает URL-адрес, URL-путь и имя сценария обрабатывающий запрос.
     * 
     * @return array Возвращает:
     *    - url - URL-адрес сценария;
     *    - path - URL-путь сценария;
     *    - filename - имя сценария.
     */
    public function getScript(): array
    {
        if (!isset($this->_script)) {
            $request = Ge::$app->request;
            if ($request instanceof Request) {
                $this->_script = $request->getScript();
                if ($this->scriptBaseUrl) {
                    $this->_script['url']  = $this->scriptBaseUrl . $this->_script['filename'];
                    $this->_script['path'] = $this->scriptBaseUrl;
                } else {
                    if ($this->scriptRedirect) {
                        $path = $this->scriptRedirect[$this->_script['path']] ?? false;
                        if ($path) {
                            $this->_script['url']  = $path . $this->_script['filename'];
                            $this->_script['path'] = $path;
                        }
                    }
                }
            } else 
                throw new Exception\RuntimeException(
                    Ge::t('app', 'Please configure UrlManager::scriptUrl correctly as you are running a console application')
                );
        }
        return $this->_script;
    }

    /**
     * Возвращает URL-путь к вызываемому модулю, полученный из маршрута запроса.
     * 
     * @return string
     */
    public function getModuleRoute(): string
    {
        if (!isset($this->moduleRoute)) {
            $this->moduleRoute = str_replace($this->backendRoute . '/', '', $this->route);
        }
        return $this->moduleRoute;
    }

    /**
     * Создаёт URL-адрес с указанием маршрута и параметрами запроса.
     *
     * Можете указать маршрут в виде строки, например, 'foo/bar' или указать
     * параметры запроса. Массив должен иметь такой формат:
     *
     * ```php
     * echo Ge::$app->urlManager->createUrl(['foo/bar', '?' => ['param1' => 'value1', 'param2' => 'value2']]);
     * // результат: http://domain/index.php?r=foo%2Fbar&param1=value1&param2=value2
     * ```
     *
     * Если хотите создать URL-адрес с якорем, должны использовать параметр '#'.
     * Пример:
     *
     * ```php
     * echo Ge::$app->urlManager->createUrl(['foo/bar', '?' => ['param1' => 'value1'], '#' => 'name']);
     * // результат: http://domain/index.php?r=foo%2Fbar&param1=value1#name
     * ```
     *
     * Созданный URL-адрес является абсолютным. Если есть необходимость в создании относительного, 
     * используйте параметр 'local'.
     * Пример:
     *
     * ```php
     * echo Ge::$app->urlManager->createUrl(['foo/bar', '?' => ['param1' => 'value1'], 'local' => true]);
     * // результат: /index.php?r=foo%2Fbar&param1=value1
     * ```
     * 
     * @param string|array $params Использовать строку для представления маршрута (например 'foo/bar') 
     * или массив для представления маршрута с параметрами запроса (например "['foo/bar', 'param1' => 'value1']").
     * 
     * @return string Возвращает соданный URL-адрес.
     */
    public function createUrl(string|array $params): string
    {
        $params         = (array) $params;
        $params['path'] = $params[0];
        unset($params[0]);
        return $this->buildUrl($params);
    }

    /**
     * Возвращает URL-адрес, собранный из составных частей c 
     * указанными правилами.
     * 
     * Схема сборки URL-адреса (RFC 3986):
     *    - <схема>:[//[<логин>[:<пароль>]@] <хост> [:<порт>]] [/<URL-путь>] [?<параметры>] [#<якорь>]
     *    - <scheme>:[//[<user>[:<pass>]@] <host> [:<port>]] [/<path>] [?<query>] [#<fragment>]
     * 
     * @see \Ge\Helper\Url::build()
     * 
     * @param array $components Компоненты URL:
     *    - scheme: схема;
     *    - user: логин;
     *    - pass: пароль;
     *    - host: хост; 
     *    - port: порт;
     *    - path: URL-путь;
     *    - langSlug: слаг языка (ru-RU, en-GB);
     *    - basename: добавляет в конец path;
     *    - local: локальный адрес (true), иначе абсолютный (false);
     *    - ? или query: параметры запроса;
     *    - # или fragment: якорь.
     * @param array $options Правила формирования URL-адреса:
     *    - enableTrailingSlash - {@see UrlManager::$enableTrailingSlash};
     *    - enablePrettyUrl - {@see UrlManager::$nablePrettyUrl};
     *    - showScriptName - {@see UrlManager::$showScriptName}.
     * 
     * @return string
     */
    public function buildUrl(array $components = [], array $options = []): string
    {
        // если локальный адрес
        if ($components['local'] ?? false) {
            $components['scheme'] = false;
            $components['host']   = false;
        }

        // параметр локализации языка
        $langSlug = $components['langSlug'] ?? null;
        // [?<параметры>]
        $components['?'] = $components['?'] ?? ($components['query'] ?? []);
        $query = &$components['?'];
        // [/<URL-путь>]
        $path = &$components['path'] ?? '';
        if ($path === '*')
            $path = $this->requestUri ?: '';
        else 
        if ($path === null)
            $path = '';
        $basename = $components['basename'] ?? '';
        if ($path) {
            if ($basename) {
                $path .= '/' . $basename;
            }
        } else {
            if ($basename)
                $path = $basename;
        }
        $path  = $path ? trim($path, '/') : $path;
        $path_ = $path;

        // если нет настроек
        if (empty($options)) {
            $enableTrailingSlash = $this->enableTrailingSlash;
            $enablePrettyUrl     = $this->enablePrettyUrl;
            $showScriptName      = $this->showScriptName;
        } else {
            $enableTrailingSlash = $options['enableTrailingSlash'] ?? false;
            $enablePrettyUrl     = $options['enablePrettyUrl'] ?? false;
            $showScriptName      = $options['showScriptName'] ?? false;
        }
        $script     = $this->getScript();
        $scriptName = $script['filename'] ?? '';

        $path = $showScriptName ? '/' . $scriptName : '';
        if ($enablePrettyUrl) {
            // добавление локализации в запрос
            Ge::$app->language->toUrl($path_, $langSlug);

            if ($path_)
                $path .= '/' . $path_;
            if ($enableTrailingSlash) {
                if ($showScriptName) {
                    if ($path_ && strpos($path_, '.') === false)
                        $path .= '/';
                } else {
                    if ($path && strpos($path_, '.') === false)
                        $path .= '/';
                }
            }
            // если приложение находится не в корне, а в указанной директории
            if ($showScriptName)
                $path = $this->appendBaseUrl($path);
        } else {
            if ($path_) {
                $query[$this->routeParam] = $path_;
            }
            if ($enableTrailingSlash) {
                if (!$showScriptName)
                    $path .= '/';
            }
            // добавление локализации в запрос
            Ge::$app->language->toUrl($query, $langSlug);
        }
        $path = $this->scriptBaseUrl . ($this->scriptBaseUrl ? ltrim($path, '/') : $path);
        return Url::build($components, true);
    }

    /**
     * @see UrlManager::isHome()
     * 
     * @var bool
     */
    private bool $isHome;

    /**
     * Проверяет, является ли URL-адрес основным адресом сайта.
     * 
     * @return bool
     */
    public function isHome(): bool
    {
        if (!isset($this->isHome)) {
            $this->isHome = empty($this->route) && empty($this->filename);
        }
        return $this->isHome;
    }
}
