<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http;

use Ge;
use Ge\Stdlib\Service;
use Ge\Exception;
use Ge\Helper\Str;

/**
 * Класс веб-запроса представлен в виде HTTP-запроса.
 * 
 * Доступ к экземпляру класса можно получить через `Ge::$app->request`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class Request extends Service
{
    /**
     * Параметр, который передаёт запросом GET значение свойства 
     * hash интерфейса URL (идентификатор фрагмента URL после символа «#»).
     * 
     * Добавляется в запрос, только с помощью JavaScript. Если имя параметра 
     * не указано, добавление в запросе не будет.
     * 
     * Пример: "/request#my_hash" => "/request?_hash=my_hash#my_hash"
     * 
     * @var string
     */
    public string $hashParam = '_hash';

    /**
     * Значение свойства hash интерфейса URL (идентификатор фрагмента URL после 
     * символа «#»).
     * 
     * @see Request::$hashParam
     * 
     * @var string
     */
    protected $hash;

    /**
     * Яявляется ли текущий запрос AJAX (XMLHttpRequest).
     * 
     * @see Request::isAjax()
     * 
     * @var bool
     */
    protected bool $isAjax;

    /**
     * Яявляется ли текущий запрос PJAX.
     * 
     * @see Request::IsPjax()
     * 
     * @var bool
     */
    protected bool $IsPjax;

    /**
     * Яявляется ли текущий запрос GJAX.
     * 
     * @see Request::IsGjax()
     * 
     * @var bool
     */
    protected bool $IsGjax;

    /**
     * Яявляется ли текущий запрос Adobe Flash или Flex.
     * 
     * @see Request::IsFlash()
     * 
     * @var bool
     */
    protected bool $IsFlash;

    /**
     * Запрос сделан с помощью метода POST.
     * 
     * @var bool
     */
    public bool $isPost = false;

    /**
     * Запрос сделан с помощью метода GET.
     * 
     * @var bool
     */
    public bool $isGet = false;

    /**
     * Запрос сделан с помощью метода PUT.
     * 
     * @var bool
     */
    public bool $isPut = false;

    /**
     * Запрос сделан с помощью метода OPTIONS.
     * 
     * @var bool
     */
    public bool $isOptions = false;

    /**
     * Запрос сделан с помощью метода HEAD.
     * 
     * @var bool
     */
    public bool $isHead = false;

    /**
     * Запрос сделан с помощью метода DELETE.
     * 
     * @var bool
     */
    public bool $isDelete = false;

    /**
     * Запрос сделан с помощью метода PATCH.
     * 
     * @var bool
     */
    public bool $isPatch = false;

    /**
     * Абсолютный путь к исполняемому скрипту.
     * 
     * @see Request::setScriptFile()
     * @see Request::getScriptFile()
     * 
     * @var string
     */
    protected string $scriptFile;

    /**
     * Путь к текущему исполняемому скрипту.
     * 
     * @see Request::getScriptName()
     * 
     * @var string
     */
    protected string $scriptName;

    /**
     * Относительный URL-адрес исполняемого скрипта.
     * 
     * @see Request::getScriptUrl()
     * 
     * @var string
     */
    protected string $scriptUrl;

    /**
     * Относительный URL-адрес исполняемого скрипта и абсолютный путь к нему.
     * 
     * @see Request::getScript()
     * 
     * @var array
     */
    protected array $script;

    /**
     * Заголовок запроса, определяющий в каком формате должен быть ответ.
     * 
     * @see Request::getResponseFormatHeader()
     * 
     * @var string
     */
    public string $responseFormatHeader = 'X-Response-Format';

    /**
     * Коллекция заголовков.
     * 
     * @var Headers
     */
    protected Headers $headers;

    /**
     * Метод текущего запроса.
     * 
     * @see Request::getMethod()
     * 
     * @var string
     */
    protected string $method;

    /**
     * Безопасные методы запроса.
     * 
     * Эти методы предназначены только для получения информации и не должны 
     * изменять состояние сервера.
     * 
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-4.2
     * 
     * @var array
     */
    protected array $safeMethods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

    /**
     * Доступные методы запроса.
     * 
     * @var array
     */
    public array $allowedMethods = ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'TRACE', 'CONNECT'];

    /**
     * Включает проверку CSRF (подделка межсайтовых запросов).
     * 
     * Когда проверка CSRF включена, отправляемые запросы от клиента должны быть 
     * из этого же приложения. Если нет, будет исключение HTTP 400 .
     *
     * Для проверки CSRF необходимо, чтобы клиент принимал cookie.
     * Кроме того, чтобы использовать эту функцию, формы, отправленные с помощью 
     * метода POST, должны содержать скрытый ввод, имя которого указано в {@see Request::$csrfParamName}.
     * 
     * Для передачи HTTP-заголовков с CSRF токеном, необходимо добавить метатег CSRF 
     * на своей странице, используя:
     * ```php
     * Ge::$app->clientScript->meta->csrfTokenTag();
     * ```

     * @link https://ru.wikipedia.org/wiki/Межсайтовая_подделка_запроса
     * 
     * @var bool
     */
    public bool $enableCsrfValidation = true;

    /**
     * Параметры конфигурации для создания CSRF cookie.
     * 
     * Это свойство используется только тогда, когда {@see Request::$enableCsrfValidation}  
     * и {@see Request::enableCsrfCookie}} true. 

     * @var array
     */
    public array $csrfCookie = ['httpOnly' => true];

    /**
     * Время жизни CSRF cookie в минутах.
     * 
     * Если значение 0, значит cookie сессионные.
     * Это свойство устанавливается для {@see Request::$csrfCookie}.

     * @var int
     */
    public int $csrfCookieLifeTime = 0;

    /**
     * Имя токена cookie для хранения CSRF значения.
     * 
     * Если значение `null`, используется значение {@see Request::$csrfParam}.
     * Это свойство устанавливается для {@see Request::$csrfCookie}.

     * @var string
     */
    public string $csrfCookieName = 'xcsrf-token';

    /**
     * Имя токена сессии для хранения CSRF значения.
     * 
     * Если значение `null`, используется значение {@see Request::$csrfParam}.

     * @var string
     */
    public string $csrfSessionName = 'xcsrf-token';

    /**
     * Имя HTTP-заголовка для отправки CSRF токена.
     * 
     * @var string
     */
    public string $csrfHeaderName = 'csrf-token';

    /**
     * Имя токена формы для хранения CSRF значения.
     * 
     * @var string
     */
    public string $csrfParamName = '_csrf';

    /**
     * Значение токена CSRF, полученный из запроса пользователя или был 
     * сгенерирован.
     * 
     * @see Request::getCsrfToken()
     * 
     * @var string
     */
    protected string $csrfToken;

    /**
     * Имя токена cookie для хранения разметки значения.
     * 
     * @var string
     */
    public string $markupCookieName = 'markup-token';

    /**
     * Секретный ключ, используемый для проверки подлинности токена разметки.
     * 
     * @see Request::validateMarkupToken()
     * 
     * @var null|string
     */
    public string $markupValidationKey;

    /**
     * Использовать cookie для хранения токена CSRF.
     * 
     * Если значение true, токен CSRF будет храниться в cookie под именем 
     * {@see Request::$csrfCookieName} или {@see Request::$csrfParam}.
     * 
     * @var bool 
     */
    public bool $enableCsrfCookie = true;

    /**
     * Использовать сессию для хранения токена CSRF.
     * 
     * Если значение `true`, токен CSRF будет храниться в сессии под именем 
     * {@see Request::$csrfSessionName} или {@see Request::$csrfParam}.
     * Хранение токенов CSRF в сессии повышает безопасность, оно требует 
     * запуска сессии для каждой страницы, что снизит производительность.
     * 
     * @var bool 
     */
    public bool $enableCsrfSession = false;

    /**
     * Включает проверку подлинности cookie.
     * 
     * @see Request::loadCookies()
     * 
     * @var bool
     */
    public bool $enableCookieValidation = true;

    /**
     * Секретный ключ, используемый для проверки подлинности cookie.
     * 
     * @see Request::loadCookies()
     * 
     * @var bool
     */
    public bool $cookieValidationKey;

    /**
     * Имена cookie, которые необходимо проверять.
     * 
     * Имеет формат:
     *     - `*`, все cookie;
     *     - `['name1', 'name2'...]`, массив имён cookie.
     * 
     * @see Request::loadCookies()
     * 
     * @var string|array
     */
    public string|array $cookieValidation;

    /**
     * Для каждого запроса создавать новый CSRF токен.
     * 
     * @see Request::getCsrfToken()
     * 
     * @var bool
     */
    public bool $regenerateCsrfToken = false;

    /**
     * Имена методов для проверки значений параметров запроса.
     * 
     * @see Request::validateParam()
     * 
     * @var array
     */
    protected array $validatorNames = [
        'ip'     => 'isUserIp',
        'userIp' => 'isUserIp',
        'domain' => 'isHost',
        'host'   => 'isHost',
        'method' => 'isMethod',
        'port'   => 'isServerPort'
    ];

    /**
     * Коллекция cookie.
     * 
     * @see Request::loadCookies()
     * 
     * @var CookieCollection
     */
    protected CookieCollection $cookies;

     /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // определение метода запроса
        $this->defineMethod();
    }

    /**
     * Возвращает метод текущего запроса.
     * 
     * @return string Метод текущего запроса в верхнем регистре: GET, POST, HEAD, PUT, PATCH, DELETE.
     */
    public function getMethod(): string
    {
        if (!isset($this->method)) {
            if ($method = $this->getHeaders()->get('X-Http-Method-Override')) {
                $this->method = strtoupper($method);
            } else
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
            } else
                $this->method = 'GET';
        }
        return $this->method;
    }

    /**
     * Проверяет метод текущего запроса.
     * 
     * @param $method Имя метода.
     * 
     * @return bool Возвращает значение `true`, если метод $method совпал с методом 
     *     запроса.
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === $method;
    }

    /**
     * Проверяет, является ли текущий метод запроса безопасным.
     * 
     * @return bool Возвращает значение `true`, если метод запроса безопасный.
     */
    public function isSafeMethod(): bool
    {
        static $safe;

        if ($safe === null) {
            $safe = in_array($this->getMethod(), $this->safeMethods, true);
        }
        return $safe;
    }

    /**
     * Определение текущего метода запроса.
     * 
     * В зависимости от метода запроса, такие атрибуты как: {@see Request::$isPost}, 
     * {@see Request::$isOptions}, {@see Request::$isHead}, {@see Request::$isDelete}, 
     * {@see Request::$isPatch}, {@see Request::$isGet}, {@see Request::$isPut} будут 
     * иметь значение `true`.
     * 
     * @return $this
     */
    protected function defineMethod(): static
    {
        $isMethod = 'is' . ucfirst(strtolower($this->getMethod()));
        if (isset($this->$isMethod))
            $this->$isMethod = true;
        else
            $this->isGet = true;
        return $this;
    }

     /**
     * Установка значения POST параметра.
     * 
     * @param string $name Имя параметра.
     * @param mixed $value Значение параметра. Если значение не указано, параметр будет 
     *     удален (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setPost(string $name, mixed $value = null): static
    {
        if ($value === null)
            unset($_POST[$name]);
        else
            $_POST[$name] = $value;
        return $this;
    }

    /**
     * Возвращает значение POST параметра.
     * Если имя не указано, возвращает массив всех параметров POST.
     * 
     * @param array|string|null $name Имя или массив имен параметров POST.
     * @param mixed $default Значение по умолчанию, возвращаемое если параметр не существует.
     * @param null|string $type Тип возвращаемого значения (по умолчанию `null`). Если `null`, 
     *     приведение типа возвращаемого значения не будет. Допустимыми значениями:
     *     - 'boolean' или 'bool';
     *     - 'integer' или 'int';
     *     - 'float' или 'double';
     *     - 'string';
     *     - 'array';
     *     - 'object'.
     * 
     * @return mixed
     */
    public function getPost(array|string|null $name = null, mixed $default = null, ?string $type = null): mixed
    {
        if ($name === null) {
            return $_POST;
        }
        if (is_array($name)) {
            $result = [];
            foreach ($name as $key => $value) {
                // если нумерованный массив
                if (is_numeric($key)) {
                    $key = $value;
                }
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];
                    if ($type) {
                        settype($value, $type);
                    }
                    $result[$key] = $value;
                } else
                    $result[$key] = $default;
            }
            return $result;
        } else {
            if (isset($_POST[$name])) {
                $value = $_POST[$name];
                if ($type) {
                    settype($value, $type);
                }
                return $value;
            } else
                return $default;
        }
    }

    /**
     * Возвращает значение GET параметра.
     * Если имя не указано, возвращает массив всех параметров GET.
     * 
     * @param array|string|null $name Имя или массив имен параметров GET.
     * @param mixed $default Значение по умолчанию, возвращаемое если параметр не существует.
     * @param null|string $type Тип возвращаемого значения (по умолчанию `null`). Если `null`, 
     *     приведение типа возвращаемого значения не будет. Допустимыми значениями:
     *     - 'boolean' или 'bool';
     *     - 'integer' или 'int';
     *     - 'float' или 'double';
     *     - 'string';
     *     - 'array';
     *     - 'object'.
     * 
     * @return mixed
     */
    public function getQuery(array|string|null $name = null, mixed $default = null, ?string $type = null): mixed
    {
        if ($name === null) {
            return $_GET;
        }

        if (is_array($name)) {
            $result = [];
            foreach ($name as $key => $value) {
                // если нумерованный массив
                if (is_numeric($key)) {
                    $key = $value;
                }
                if (isset($_GET[$key])) {
                    $value = $_GET[$key];
                    if ($type) {
                        settype($value, $type);
                    }
                    $result[$key] = $value;
                } else
                    $result[$key] = $default;
            }
            return $result;
        } else {
            if (isset($_GET[$name])) {
                $value = $_GET[$name];
                if ($type) {
                    settype($value, $type);
                }
                return $value;
            }
            return $default;
        }
    }

    /**
     * Проверяет параметр в массиве GET-запросов.
     * 
     * @param string $name Имя параметра. Если имя не указано, проверяет пустой ли 
     *     массив GET (по умолчанию `null`).
     * 
     * @return bool
     */
    public function hasQuery(?string $name = null): bool
    {
        return $name === null ? !empty($_GET) : isset($_GET[$name]);
    }

    /**
     * Проверяет параметр в массиве POST-запросов.
     * 
     * @param null|string $name Имя параметра. Если имя не указано, проверяет пустой ли 
     *     массив POST (по умолчанию `null`).
     * 
     * @return bool
     */
    public function hasPost(?string $name = null): bool
    {
        return $name === null ? !empty($_POST) : isset($_POST[$name]);
    }

    /**
     * Устанавливает значение параметру в массиве GET-запросов.
     * 
     * @param string $name Имя параметра.
     * @param mixed $value Значение параметра. Если значение не указано, то параметр 
     *     будет удалён (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setQuery(string $name, mixed $value = null): static
    {
        if ($value === null)
            unset($_GET[$name]);
        else
            $_GET[$name] = $value;
        return $this;
    }

    /**
     * Возвращает значение параметра из массива GET-запросов.
     * 
     * @param string|null $name Имя параметра.Если имя не указано, то возвратит 
     *     массив GET-запросов в виде пар "ключ-значение".
     * @param mixed $default Значение по умолчанию, если параметр не существует 
     *     (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function get(?string $name, mixed $default = null): mixed
    {
        return $name ? (isset($_GET[$name]) ? $_GET[$name] : $default) : $_GET;
    }

    /**
     * Возвращает значение параметра из массива POST-запросов.
     * 
     * @param string|null $name Имя параметра.Если имя не указано, то возвратит 
     *     массив POST-запросов в виде пар "ключ-значение".
     * @param mixed $default Значение по умолчанию, если параметр не существует 
     *     (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function post(?string $name, mixed $default = null): mixed
    {
        return $name ? (isset($_POST[$name]) ? $_POST[$name] : $default) : $_POST;
    }

    /**
     * Возвращает значение параметра из массива REQUEST-запросов.
     * 
     * @param string|null $name Имя параметра.Если имя не указано, то возвратит 
     *     массив REQUEST-запросов в виде пар "ключ-значение".
     * @param mixed $default Значение по умолчанию, если параметр не существует 
     *     (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function param(?string $name, mixed $default = null): mixed
    {
        return $name ? (isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default) : $_REQUEST;
    }

    /**
     * Проверяет, является ли текущий запрос AJAX (XMLHttpRequest) запросом.
     * 
     * @return bool Возвращает значение `true`, если запрос является запросом 
     *     AJAX (XMLHttpRequest).
     */
    protected function _isAjax(): bool
    {
        if (!isset($this->isAjax)) {
            $this->isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        }
        return $this->isAjax;
    }

    /**
     * Проверяет, является ли текущий запрос AJAX (XMLHttpRequest) запросом.
     * 
     * @param null|string $headerName
     * 
     * @return bool Возвращает значение `true`, если запрос является запросом 
     *     AJAX (XMLHttpRequest).
     */
    public function isAjax(?string $headerName = null): bool
    {
        if ($headerName) {
            return $this->_isAjax() && $this->getHeaders()->has($headerName);
        }
        return $this->_isAjax();
    }

    /**
     * Проверяет, является ли текущий запрос PJAX запросом.
     * 
     * @return bool Возвращает значение `true`, если запрос является запросом PJAX.
     */
    public function IsPjax(): bool
    {
        if (!isset($this->IsPjax)) {
            $this->IsPjax = $this->isAjax('X-Pjax');
        }
        return $this->IsPjax;
    }

    /**
     * Проверяет, является ли текущий запрос GJAX запросом.
     * 
     * @return bool Возвращает значение `true`, если запрос является запросом GJAX.
     */
    public function IsGjax(): bool
    {
        if (!isset($this->IsGjax)) {
            $this->IsGjax = $this->isAjax('X-Gjax') || isset($_POST['X-Gjax']);
        }
        return $this->IsGjax;
    }

    /**
     * Проверяет, является ли запрос запросом Adobe Flash или Flex.
     * 
     * @return bool Возвращает значение `true`, если запрос является запросом 
     *     Adobe Flash или Adobe Flex.
     */
    public function IsFlash(): bool
    {
        if (!isset($this->IsFlash)) {
            $userAgent = $this->getHeaders()->get('User-Agent', '');
            $this->IsFlash = stripos($userAgent, 'Shockwave') !== false || stripos($userAgent, 'Flash') !== false;
        }
        return $this->IsFlash;
    }

    /**
     * Проверяет, выполняется ли текущий запрос через командную строку.
     * 
     * @return bool Значение, указывающее, выполняется ли текущий запрос через консоль.
     */
    public function isConsole(): bool
    {
        return IS_CONSOLE;
    }

   /**
     * Возвращает абсолютный путь к исполняемому скрипту.
     * 
     * Простая реализация вернёт "$_SERVER['SCRIPT_FILENAME']".
     * 
     * @return string Абсолютный путь к исполняемому скрипту.
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function getScriptFile(): string
    {
        if (!isset($this->scriptFile)) {
            if (!isset($_SERVER['SCRIPT_FILENAME'])) {
                throw new Exception\InvalidArgumentException(Ge::t('app', 'Unable to determine the entry script file path'));
            }
            $this->scriptFile = $_SERVER['SCRIPT_FILENAME'];
        }
        return $this->scriptFile;
    }

    /**
     * Устанавливает абсолютный путь к исполняемому скрипту.
     * 
     * @param string $filename Абсолютный путь к исполняемому скрипту.
     * 
     * @return void
     */
    public function setScriptFile(string $filename): void
    {
        $this->scriptFile = $filename;
    }

    /**
     * Возвращает путь к текущему исполняемому скрипту.
     * 
     * Простая реализация вернёт "$_SERVER['SCRIPT_NAME']".
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function getScriptName(): string
    {
        if (!isset($this->scriptName)) {
            if (!isset($_SERVER['SCRIPT_NAME'])) {
                throw new Exception\InvalidArgumentException(Ge::t('app', 'Unable to determine the entry script file path'));
            }
            $this->scriptName = basename($_SERVER['SCRIPT_NAME']);
        }
        return $this->scriptName;
    }

    /**
     * Возвращает относительный URL-адрес исполняемого скрипта.
     *
     * @return string
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function getScriptUrl(): string
    {
        if (!isset($this->scriptUrl)) {
            $scriptFile = $this->getScriptFile();
            $scriptName = basename($scriptFile);

            if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->scriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->scriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF']) && ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                $this->scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (!empty($_SERVER['BASE_PATH']) && strpos($scriptFile, $_SERVER['BASE_PATH']) === 0) {
                $this->scriptUrl = str_replace(array($_SERVER['BASE_PATH'], '\\'), array('', '/'), $scriptFile);
            } else {
                throw new Exception\InvalidArgumentException(
                    Ge::t('app', 'Unable to determine the entry script URL')
                );
            }
        }
        return $this->scriptUrl;
    }

    /**
     * Возвращает относительный URL-адрес и путь исполняемого скрипта.
     *
     * @return array
     */
    public function getScript(): array
    {
        if (!isset($this->script)) {
            $scriptUrl  = $this->getScriptUrl();
            $scriptFile = basename($this->getScriptFile());
            $this->script = [
                'filename' => $scriptFile,
                'url'      => $scriptUrl,
                'path'     => rtrim($scriptUrl, $scriptFile)
            ];
        }
        return $this->script;
    }

    /**
     * Возвращает заголовки сообщений.
     *
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        if (!isset($this->headers)) {
            $this->headers = new Headers();
            $this->headers->define();
        }
        return $this->headers;
    }

    /**
     * Возвращает значение параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * @param mixed $default Значение по умолчнаию (по умолчанию `null`).
     * @param bool $сouple Если значение `true`, возвращает строку вида "параметр: значение", 
     *     иначе значение параметра (по умолчанию `false`).
     * 
     * @return string|null
     */
    public function header(string $name, mixed $default = null, bool $сouple = false): ?string
    {
        return $this->getHeaders()->get($name, $default, $сouple);
    }

    /**
     * Возвращает IP-адрес на другом конце соединения.
     *
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return string|null
     */
    public function getRemoteIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Возвращает IP-адрес, с которого пользователь просматривает текущую страницу.
     *
     * @see Request::getRemoteIp()
     * 
     * @return string|null
     */
    public function getUserIp(): ?string
    {
        return $this->getRemoteIp();
    }

    /**
     * Проверяет IP-адрес, с которого пользователь просматривает текущую страницу.
     * 
     * @see Request::getRemoteIp()
     * 
     * @param string $ip Проверяемый IP-адес.
     * 
     * @return bool
     */
    public function isUserIp(string $ip): bool
    {
        return $this->getRemoteIp() === $ip;
    }

    /**
     * Возвращает удаленный хост, с которого пользователь просматривает текущую страницу.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return string|null
     */
    public function getRemoteHost(): ?string
    {
        return $_SERVER['REMOTE_HOST'] ?? null;
    }

    /**
     * Возвращает удаленный хост на другом конце соединения.
     * 
     * @see Request::getRemoteHost()
     * 
     * @return string|null
     */
    public function getUserHost(): ?string
    {
        return $this->getRemoteHost();
    }

    /**
     * Проверяет удаленный хост на другом конце соединения.
     *
     * @param string $host Удаленный хост, с которого пользователь просматривает 
     *     текущую страницу.
     * 
     * @see Request::getServerName()
     * 
     * @return bool
     */
    public function isHost(string $host): bool
    {
        return $this->getServerName() === $host;
    }

    /**
     * Возвращает User Agent.
     * 
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->getHeaders()->get('User-Agent');
    }

    /**
     * Возвращает номер порта сервера.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return int|null Возвращает значение `null`, если номер порта сервера недоступен.
     */
    public function getServerPort(): ?string
    {
        return $_SERVER['SERVER_PORT'] ?? null;
    }

    /**
     * Проверяет номер порта сервера.
     *
     * @param string $port Проверяемый номер порта сервера.
     * 
     * @see Request::getServerPort()
     * 
     * @return bool
     */
    public function isServerPort(string $port): bool
    {
        return $this->getServerPort() === $port;
    }

    /**
     * Возвращает имя сервера.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return string|null Возвращает значение `null`, имя сервера недоступно.
     */
    public function getServerName(): ?string
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }

    /**
     * Возвращает роль FCGI.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return string|null Возвращает значение `null`, если роль FCGI недоступна.
     */
    public function geServerFCGIRole(): ?string
    {
        return $_SERVER['FCGI_ROLE'] ?? null;
    }

    /**
     * Проверяет, использует ли сервер Fast CGI.
     * 
     * @link https://www.php.net/manual/ru/reserved.variables.server.php
     * 
     * @return bool
     */
    public function serverUseFCGI(): bool
    {
        return isset($_SERVER['FCGI_ROLE']);
    }

    /**
     * Возвращает URL источник запроса.
     * 
     * @return string|null Возвращает значение `null`, если URL источник запроса 
     *     недоступен.
     */
    public function getReferrer(): ?string
    {
        return $this->getHeaders()->get('Referer');
    }

    /**
     * Возвращает URL адрес загрузки (из заголовка запроса "Origin"), если запрос 
     * отправлен как CORS.
     *
     * Информация о заголовке запроса "Origin" здесь {@link https://developer.mozilla.org/ru/docs/Web/HTTP/Заголовки/Origin}.
     *
     * @return string|null Возвращает значение `null`, если заголовок "Origin" в 
     *     запросе отсутствует.
     */
    public function getOrigin(): ?string
    {
        return $this->getHeaders()->get('origin');
    }

    /**
     * Возвращает значение свойства hash интерфейса URL (идентификатор фрагмента 
     * URL после символа «#»).
     * 
     * @return string
     */
    public function getHash(): string
    {
        if ($this->hash === null) {
            $this->hash = $this->getQuery($this->hashParam, '');
        }
        return $this->hash;
    }

    /**
     * Возвращает токен, используемый для проверки CSRF.
     *
     * Этот токен создаётся для предотвращения breach атак {@link http://breachattack.com/}. 
     * Его можно передать через скрытое поле HTML-формы или значение заголовка HTTP для 
     * проверки CSRF.
     * 
     * Если токен ранее создан и получен запросом, то его возвратит {@see Request::readCsrfToken()}.
     * 
     * @param bool|null $regenerate Нужно ли регенерировать токен CSRF. Если true, то 
     *    каждый раз, когда вызывается этот метод, будет создаваться и сохраняться 
     *    новый токен CSRF (в сессии или в cookie).
     * 
     * @return string Токен, используемый для проверки CSRF.
     */
    public function getCsrfToken(?bool $regenerate = null): string
    {
        if ($regenerate === null) {
            $regenerate = $this->regenerateCsrfToken;
        }
        if (!isset($this->csrfToken) || $regenerate) {
            // cookie, сессия или null
            $token = $this->readCsrfToken();
            if ($regenerate || empty($token)) {
                $token = $this->generateCsrfToken();
            }
            $this->csrfToken = $token;
        }
        return $this->csrfToken;
    }

    /**
     * Загружает токен CSRF из cookie или сессии.
     * 
     * @return string|null Возвращает токен CSRF, загруженный из cookie или сессии. Если cookie 
     *     или сессия не имеют токен CSRF, возвращает `null`.
     */
    public function readCsrfToken(): ?string
    {
        // если использовать токен для cookie
        if ($this->enableCsrfCookie) {
            $token = $this->getCsrfTokenFromCookie();
            if ($token !== null) {
                try {
                    $token = Ge::$app->encrypter->decryptString($token);
                } catch(\Exception $e) {
                    $token = null;
                }
                return $token;
            }
        }
        // если использовать токен для сессии
        if ($this->enableCsrfSession) {
            return Ge::$app->session->getToken();
        }
        return null;
    }

    /**
     * Создаёт незашифрованный случайный токен, используемый для проверки CSRF.
     * 
     * @return string Возвращает cлучайный токен для проверки CSRF.
     */
    protected function generateCsrfToken(): string
    {
       return Str::random(40);
    }

    /**
     * Вовзарщает токен CSRF, отправленный браузером через заголовок.
     * 
     * @return string|null Если заголовок {@see Request::$csrfHeaderName} не отправлен, 
     *     возвращает `null`.
     */
    public function getCsrfTokenFromHeader(): ?string
    {
        return $this->getHeaders()->get($this->csrfHeaderName);
    }

    /**
     * Вовзарщает токен CSRF, отправленный браузером через cookie.
     * 
     * @return string|null Если cookie {@see Request::$csrfCookieName} не отправлен, 
     *     возвращает `null`.
     */
    public function getCsrfTokenFromCookie(): ?string
    {
        return $_COOKIE[$this->csrfCookieName] ?? null;
    }

    /**
     * Проверка CSRF.
     *
     * Проверяет CSRF токен пользователя, сравнивая его с токеном хранящимся в cookie 
     * или в сессии.
     * 
     * В зависимости от проверки, можно выделить следующие методы защиты:
     *     - "Synchronizer Token Pattern" (Statefull). CSRF токен пользователя из полей 
     *     формы или заголовка (header) запроса cравнивается с токеном хранящимся в сессии.
     *     Для этого:
     *     ```php
     *     $request->enableCsrfValidation = true;
     *     $request->enableCsrfCookie     = false;
     *     $request->enableCsrfSession    = true;
     *     ```
     *     - "Double Submit Cookie" (Stateless). CSRF токен пользователя из полей формы или 
     *     заголовка (header) запроса cравнивается с токеном хранящимся в cookie.
     *     Для этого:
     *     ```
     *     $request->enableCsrfValidation = true;
     *     $request->enableCsrfCookie     = true;
     *     $request->enableCsrfSession    = false;
     *     ```
     *
     * Обратите внимание, что метод НЕ будет выполнять проверку CSRF, если 
     * {@see Request::$enableCsrfValidation} false или HTTP метод является безопасным 
     * {@see Request::$safeMethods}.
     *
     * @param string|null $clientToken CSRF токен пользователя для проверки. Если null, токен будет извлечен 
     *     из поле {@see Request::$csrfParam} POST или HTTP-заголовока.
     * @return bool Действителен ли токен CSRF. Если {@see Request::$enableCsrfValidation} false, 
     *     то метод вернёт true.
     */
    public function validateCsrfToken(?string $clientToken = null): bool
    {
        // проверять только токен CSRF для небезопасных методов
        if (!$this->enableCsrfValidation || $this->isSafeMethod()) {
            return true;
        }
        $correctToken = $this->readCsrfToken();
        // если токен отсутсвует в сессии или в cookie
        if ($correctToken === null) {
            return false;
        }
        // если указан конкретный токен пользователя для проверки
        if ($clientToken !== null) {
            $token = $clientToken;
        } else {
            $token = $this->post($this->csrfParamName) ?: $this->getCsrfTokenFromHeader();
        }
        return is_string($token) && is_string($correctToken) && hash_equals($token, $correctToken);
    }

    /**
     * Создает cookie со случайно сгенерированным токеном CSRF.
     * 
     * Параметры конфигурации указанные в {@see Request::$csrfCookie}, будут 
     * применены к сгенерированному cookie.
     * Время жизни cookie в {@see Request::$csrfCookieLifeTime} в минутах. Если 0, 
     * является сессионным.
     * 
     * @see Request::$enableCsrfValidation
     * 
     * @param string $token Токен CSRF.
     * @param bool $object Возвратит объект Cookie, если значение `true` (по умолчанию `false`).
     * 
     * @return array|Cookie Если `$object = true`, cгенерированный cookie, иначе 
     *     параметры cookie.
     */
    public function createCsrfCookie(string $token, bool $object = false): array|Cookie
    {
        try {
            $token = Ge::$app->encrypter->encryptString($token);
        } catch (\Exception $e) {
            return [];
        }
        $cookie = array_merge(
            $this->csrfCookie, [
                'name'  => $this->csrfCookieName,
                'value' => $token
            ]
        );
        if ($this->csrfCookieLifeTime) {
            $cookie['expire'] = time() + 60 * $this->csrfCookieLifeTime;
        }
        if ($object)
            return $this->getCookies()->create($cookie);
        else
            return $cookie;
    }

    /**
     * @see Request::validateMarkupToken ()
     * 
     * @var null|false
     */
    protected ?bool $markupValidation = null;

    /**
     * Проверка разметки компонентов приложения.
     * 
     * Применяется для определения изменения параметров компонентов в визуальном 
     * редакторе с помощью разметки.
     * 
     * @return bool
     */
    public function validateBuildToken(): bool
    {
        if ($this->markupValidation === null) {
            if ($this->markupValidationKey) {
                /** @var null|string $key */
                $key = $this->cookie($this->markupCookieName);
                $this->markupValidation = $this->markupValidationKey === $key;
            } else
                $this->markupValidation = false;
        }
        return $this->markupValidation;
    }

    /**
     * Возвращает значение cookie или коллекцию `CookieCollection`.
     * 
     * @param string|null $name Имя cookie. Если значение `null`, возвратит коллекцию `CookieCollection`.
     * @param bool $object Возвратит объект Cookie, если значение `true` (по умолчанию `false`).
     * 
     * @return mixed Если `$name = null`, результат коллекция `CookieCollection`, иначе 
     *     значение или объект cookie.
     */
    public function cookie(?string $name = null, bool $object = false): mixed
    {
        if ($name === null) {
            return $this->getCookies();
        } else {
            $cookie = $this->getCookies()->get($name);
            return $object ? $cookie : ($cookie ? $cookie->getValue() : null);
        }
    }

    /**
     * Возвращает коллекцию `CookieCollection`.
     * 
     * @see Request::loadCookies()
     * 
     * @return CookieCollection
     */
    public function getCookies(): CookieCollection
    {
        if (!isset($this->cookies)) {
            $this->cookies = $this->loadCookies();
        }
        return $this->cookies;
    }

    /**
     * Преобразует `$ _COOKIE` в коллекцию `CookieCollection`.
     * 
     * @return CookieCollection Cookie полученные из запроса.
     * 
     * @throws Exception\InvalidConfigException Если {@see Request::$cookieValidationKey} 
     *     не установлен, когда  {@see Request::$enableCookieValidation} true.
     * @throws Exception\InvalidConfigException Если {@see Request::$cookieValidation} 
     *     не установлен, когда  {@see Request::$enableCookieValidation} true.
     */
    protected function loadCookies(): CookieCollection
    {
        $cookies = new CookieCollection($_COOKIE);
        // если необходимо проверять cookie
        if ($this->enableCookieValidation) {
            // если есть ключ проверки cookie
            if (empty($this->cookieValidationKey)) {
                throw new Exception\InvalidConfigException(
                    sprintf('%s::$cookieValidationKey must be configured with a secret key.', get_class($this))
                );
            }
            // если указаны какие cookie проверять
            if (empty($this->cookieValidation)) {
                throw new Exception\InvalidConfigException(
                    sprintf('%s::$cookieValidation must be configured with a cookie keys.', get_class($this))
                );
            }
            $cookies->decrypt($this->cookieValidation, $this->cookieValidationKey);
        }
        return $cookies;
    }

    /**
     * Выполняет проверку значения параметра запроса.
     * 
     * @see Request::$validatorNames
     * 
     * @param string $name Имя параметра. По имени параметра выберается метод 
     *     проверки из {@see Request::$validatorName}.
     * @param mixed $value Значение параметра.
     * @param bool $skip Если значение `true`, то игнорируется исключение вызванное 
     *     отсутствием метода проверка (по умоланию `false`).
     * 
     * @return bool Возвращает результат метода, проводивший проверку значения.
     * 
     * @throws Exception\InvalidArgumentException Неправильно указан метод проверки значения.
     */
    public function validateParam(string $name, mixed $value, bool $skip = false): bool
    {
        $validator = $this->validatorNames[$name] ?? null;
        if ($validator) {
            return $this->$validator($value);
        }
        if ($skip)
            return true;
        else
            throw new Exception\InvalidArgumentException(
                sprintf('The specified validator "%s" does not exist.', $name)
            ); 
    }

    /**
     * Выполняет проверку значений параметров запроса.
     * 
     * @see Request::validateParam()
     * 
     * @param array $params Параметры в виде пар "ключ-значение".
     * @param bool $skip Если значение `true`, то игнорируется исключение вызванное 
     *     отсутствием метода проверка (по умоланию `false`).
     * 
     * @return bool
     * 
     * @throws Exception\InvalidArgumentException Неправильно указан метод проверки значения.
     */
    public function validateParams(array $params, bool $skip = false): bool
    {
        foreach ($params as $name => $value) {
            if (!$this->validateParam($name, $value, $skip)) return false;
        }
        return true;
    }
}
