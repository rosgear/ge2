<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http;

use Ge;
use Ge\Helper\Url;
use Ge\Http\Response\AbstractResponseFormatter;

/**
 * Класс представляет собой HTTP-ответ.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class Response extends AbstractMessage
{
    /**
     * @var int Коды состояния.
     */
    public const STATUS_CODE_CUSTOM = 0;
    public const STATUS_CODE_100 = 100;
    public const STATUS_CODE_101 = 101;
    public const STATUS_CODE_102 = 102;
    public const STATUS_CODE_200 = 200;
    public const STATUS_CODE_201 = 201;
    public const STATUS_CODE_202 = 202;
    public const STATUS_CODE_203 = 203;
    public const STATUS_CODE_204 = 204;
    public const STATUS_CODE_205 = 205;
    public const STATUS_CODE_206 = 206;
    public const STATUS_CODE_207 = 207;
    public const STATUS_CODE_208 = 208;
    public const STATUS_CODE_300 = 300;
    public const STATUS_CODE_301 = 301;
    public const STATUS_CODE_302 = 302;
    public const STATUS_CODE_303 = 303;
    public const STATUS_CODE_304 = 304;
    public const STATUS_CODE_305 = 305;
    public const STATUS_CODE_306 = 306;
    public const STATUS_CODE_307 = 307;
    public const STATUS_CODE_400 = 400;
    public const STATUS_CODE_401 = 401;
    public const STATUS_CODE_402 = 402;
    public const STATUS_CODE_403 = 403;
    public const STATUS_CODE_404 = 404;
    public const STATUS_CODE_405 = 405;
    public const STATUS_CODE_406 = 406;
    public const STATUS_CODE_407 = 407;
    public const STATUS_CODE_408 = 408;
    public const STATUS_CODE_409 = 409;
    public const STATUS_CODE_410 = 410;
    public const STATUS_CODE_411 = 411;
    public const STATUS_CODE_412 = 412;
    public const STATUS_CODE_413 = 413;
    public const STATUS_CODE_414 = 414;
    public const STATUS_CODE_415 = 415;
    public const STATUS_CODE_416 = 416;
    public const STATUS_CODE_417 = 417;
    public const STATUS_CODE_418 = 418;
    public const STATUS_CODE_422 = 422;
    public const STATUS_CODE_423 = 423;
    public const STATUS_CODE_424 = 424;
    public const STATUS_CODE_425 = 425;
    public const STATUS_CODE_426 = 426;
    public const STATUS_CODE_428 = 428;
    public const STATUS_CODE_429 = 429;
    public const STATUS_CODE_431 = 431;
    public const STATUS_CODE_500 = 500;
    public const STATUS_CODE_501 = 501;
    public const STATUS_CODE_502 = 502;
    public const STATUS_CODE_503 = 503;
    public const STATUS_CODE_504 = 504;
    public const STATUS_CODE_505 = 505;
    public const STATUS_CODE_506 = 506;
    public const STATUS_CODE_507 = 507;
    public const STATUS_CODE_508 = 508;
    public const STATUS_CODE_511 = 511;

    /**
     * Фразы соответствующие кодам состояния.
     * 
     * @var array
     */
    public static array $recommendedReasonPhrases = [
        // Информационные коды
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // Коды успеха
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // Коды перенаправлений
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // Ошибки клиента
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // Ошибки сервера
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var string Формат ответа.
     */
    public const
        FORMAT_RAW   = 'raw',
        FORMAT_HTML  = 'html',
        FORMAT_JSON  = 'json',
        FORMAT_JSONP = 'jsonp',
        FORMAT_XML   = 'xml';

    /**
     * @var string Событие, возникшее перед отправкой контента.
     */
    public const EVENT_BEFORE_SEND = 'beforeSend';

    /**
     * @var string Событие, возникшее после отправки контента.
     */
    public const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @var string Событие, возникшее при обработке контента исключения.
     */
    public const EVENT_SET_EXCEPTION = 'setException';

    /**
     * @var string Название дополнительного загаловка для отображения автора.
     */
    public const HEADER_POWERED = 'X-Powered-By';

    /**
     * Формат ответа.
     * 
     * Определяет, как преобразовать содержимое ответа {@see Response::$content} для вывода.
     * Значение этого свойства должно быть одним из ключей, объявленных в массиве {@see Response::$formatters}.
     * По умолчанию поддерживаются следующие форматы:
     * - {@see Response::FORMAT_RAW}: данные будут обрабатываться как содержимое ответа 
     *   без какого-либо преобразования. Дополнительный HTTP-заголовок добавляться не будет.
     * - {@see Response::FORMAT_HTML}: данные будут рассматриваться как содержимое ответа без какого-либо преобразования.
     *   Заголовок "Content-Type" бет иметь значение "text/html".
     * - {@see Response::FORMAT_JSON}: данные будут преобразованы в формат JSON, а заголовок "Content-Type"
     *   будет иметь значение "application/json".
     * - {@see Response::FORMAT_JSONP}: данные будут преобразованы в формат JSONP, а заголовок "Content-Type"
     *   будет иметь значение "text/javascript".
     * - {@see Response::FORMAT_XML}: данные будут преобразованы в формат XML.
     *
     * @see Response::$formatters
     */
    public string $format = self::FORMAT_RAW;

    /**
     * Форматеры для конвертирования данных в содержимое ответа.
     * 
     * Указыватся в виде массива пар "ключ - значение". Где, ключ - имя форматера, 
     * а значение - имя класс форматера. После создания форматера, ключ будет иметь
     * указатель на объект форматера.

     * @see Response::format
     * @see Response::$defaultFormatters
     * 
     * @var array<string, string|AbstractResponseFormatter>
     */
    public array $formatters = [];

    /**
     * Создать и отправить в ответе токен для проверки CSRF (подделка межсайтовых запросов).
     * 
     * Токен буде создан и отправлен если {@see Request::$enableCsrfValidation} будет иметь 
     * значение `true` (запрос ключает проверку CSRF).
     * 
     * @see Response::sendToken()
     * 
     * @var bool
     */
    public bool $sendCsrfToken = true;

    /**
     * Доступные форматы ответа.
     *
     * @var array
     */
    public array $defaultFormatters = [
        self::FORMAT_JSON  => '\Ge\Http\Response\JsonResponseFormatter',
        self::FORMAT_JSONP => '\Ge\Http\Response\JsonpResponseFormatter',
        self::FORMAT_XML   => '\Ge\Http\Response\XmlResponseFormatter',
        self::FORMAT_HTML  => '\Ge\Http\Response\HtmlResponseFormatter',
    ];

    /**
     * Соответствие статусу коду состояния, если код указан как строка.
     * 
     * @var array
     */
    protected array $accordanceStatusCodes = ['database' => 503];

    /**
     * Состояние соответствующие текущему коду.
     * 
     * @see Response::getAccordanceStatusCode()
     * 
     * @var string
     */
    protected string $accordanceStatusCode = '';

    /**
     * Статус кода состояния по умолчанию.
     * 
     * @var int
     */
    protected int $statusCode = 200;

    /**
     * Фраза кода состояния по умолчанию.
     * 
     * @var string
     */
    protected string $reasonPhrase = '';

    /**
     * Были ли отправлены заголовки.
     * 
     * @var bool
     */
    protected bool $headersSent = false;

    /**
     * Был ли отправлен ответ.
     * 
     * @see Response::send()
     * 
     * @var bool
     */
    public bool $isSent = false;

    /**
     * Была ли вынуждена остановка отправки ответа.
     * 
     * Устанавливает значение `true` при возникновении исключения в приложении.
     *
     * @var bool
     */
    public bool $stopSend = false;

    /**
     * Последнее содержимое ответа.
     * 
     * @see Response::sendContent()
     * 
     * @var mixed
     */
    protected mixed $sentContent = '';

    /**
     * Коллекция Куки в ответе.
     * 
     * @see Response::getCookie()
     * 
     * @var CookieCollection|null
     */
    protected ?CookieCollection $cookies = null;

    /**
     * Форматер.
     * 
     * Обрабатывает данные перед их выводом.
     * 
     * @see Response::setFormat()
     * 
     * @var AbstractResponseFormatter|null
     */
    protected ?AbstractResponseFormatter $formatter = null;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'response';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (GE_ENABLE_ERROR_HANDLER) {
            if (Ge::$app->errorHandler->hasExceptions())
                $this->stopSend = true;
        }
        $this->formatters = array_merge($this->defaultFormatters, $this->formatters);
    }

    /**
     * Проверяет, является ли указанный формат, форматом ответа.
     * 
     * @param string $format Проверяемый формат.
     * 
     * @return bool
     */
    public function isFormat(string $format): bool
    {
        return $this->format === $format;
    }

    /**
     * Возвращает объект форматера.
     * 
     * Определяется заданным название формата {@see Response::$format}.
     * 
     * @return AbstractResponseFormatter|null
     * 
     * @throws Exception\InvalidConfigException Не поддерживается указанный формат.
     */
    public function getFormatter(): ?AbstractResponseFormatter
    {
        if (isset($this->formatters[$this->format])) {
            $formatter = $this->formatters[$this->format];
            if (!is_object($formatter)) {
                $this->formatters[$this->format] = $formatter = Ge::$services->get($formatter, $this);
            }
        } elseif ($this->format === self::FORMAT_RAW) {
            $formatter = null;
        } else {
            throw new Exception\InvalidConfigException(
                sprintf('Unsupported response format: %s', $this->format)
            );
        }
        return $formatter;
    }

    /**
     * Определяет и создаёт форматер по данным из запроса.
     * 
     * @see Response::setFormat()
     * 
     * @return $this
     */
    public function defineFormat(): static
    {
        /** @var \Ge\Http\Request $request */
        $request = Ge::$app->request;

        if ($request->isConsole()) {
            $this->setFormat(self::FORMAT_RAW);
        } else
        if ($request->isAjax()) {
            if ($request->IsPjax()) {
                $this->setFormat(self::FORMAT_JSONP);
            } else {
                $this->setFormat(self::FORMAT_JSON);
            }
        } else {
            $this->setFormat(self::FORMAT_HTML);
        }
        return $this;
    }

    /**
     * Устанавливает формат вывода и создаёт для этого форматер.
     * 
     * @see Response::getFormatter()
     * 
     * @param string $format Формат вывода.
     * 
     * @return $this
     */
    public function setFormat(string $format): static
    {
        // если был установлен ранее форматтер, то удаляем события связанные с ним, 
        // т.к. эти же события вызываются при отправке ответа
        if ($this->format != $format) {
            if ($this->formatter !== null) {
                $this
                    ->off(self::EVENT_BEFORE_SEND)
                    ->off(self::EVENT_SET_EXCEPTION);
            }
        }
    
        $this->format    = $format;
        $this->formatter = $this->getFormatter();
        return $this;
    }

    /**
     * Добавляет новый форматер.
     * 
     * @see Response::$formatters
     * 
     * @param string $format Имя формата.
     * @param AbstractResponseFormatter|string $formatter Класс форматера или его экземпляр.
     * 
     * @return $this
     */
    public function addFormatter(string $format, AbstractResponseFormatter|string $formatter): static
    {
        $this->formatters[$format] = $formatter;
        return $this;
    }

    /**
     * Возвращает название формата вывода данных.
     * 
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Определяет, является ли текущий формат вывода данных простым фортам (не 
     * содержащий HTML разметку).
     * 
     * @return bool
     */
    public function isPlain(): bool
    {
        return $this->format !== self::FORMAT_HTML;
    }

    /**
     * Возвращает код ответа, которому соответствует указанный статус.
     * 
     * Если статус найден, он будет указан для {@see Response::$accordanceStatusCode}.
     * 
     * @param string $str Статус, которому соответствует код.
     * @param int $default Значение кода статуса по умолчанию 500.
     * 
     * @return int
     */
    public function getAccordanceStatusCode(string $str, int $default = 500): int
    {
        if (isset($this->accordanceStatusCodes[$str])) {
            $this->accordanceStatusCode = $str;
            return $this->accordanceStatusCodes[$str];
        } else
            return $default;
    }

    /**
     * Проверяет, были ли отправлены заголовки.
     * 
     * @return bool
     */
    public function headersSent(): bool
    {
        return headers_sent();
    }

    /**
     * Отправляет заголовки.
     * 
     * @return $this
     */
    public function sendHeaders(): static
    {
        if ($this->headersSent()) {
            return $this;
        }
        $status = $this->statusLineToString();
        header($status);
        $this->prepareHeaders();
        if ($this->headers !== null) {
            $this->headers->send();
        }
        $this->headersSent = true;
        return $this;
    }

    /**
     * Подготавливает заголовки для ответа.
     * 
     * Возможность добавления своих заголовков.
     * 
     * @return $this
     */
    public function prepareHeaders(): static
    {
        $this->addHeaderPowered();
        return $this;
    }

    /**
     * Добавляет заголовок в ответ "powered by".
     *
     * @param boolean $useHeader (по умолчанию `true`).
     * 
     * @return void
     */
    public function addHeaderPowered(bool $useHeader = true): void
    {
        $headers = $this->getHeaders();
        if ($params = Ge::$app->unifiedConfig->get('page')) {
            $text      = !empty($params['textPowered']) ? $params['textPowered'] : Ge::$app->version->getPoweredBy();
            $useHeader = isset($params['useHeaderPowered']) ? $params['useHeaderPowered'] : true;
        } else
            $text = Ge::$app->version->getPoweredBy();
        if ($useHeader && $text)
            $headers->add(self::HEADER_POWERED, $text, false);
    }

    /**
     * Отправляет токены, cookie, заголовки и контент клиенту, и прекращает выполнение 
     * текущего скрипта.
     * 
     * @param bool $nonStop Если значение `true`, не отправляет ответ (по умолчанию `false`).
     */
    public function send(bool $nonStop = false): void
    {
        if (!$nonStop)
            if ($this->stopSend) return;

        $this->trigger(self::EVENT_BEFORE_SEND, ['response' => $this]);
        $this->sendToken();
        $this->sendCookies();
        $this->sendHeaders();
        $this->sendContent();
        $this->isSent = true;
        $this->trigger(self::EVENT_AFTER_SEND, ['response' => $this]);
        exit;
    }

    /**
     * Отправляет файл в браузер.
     *
     * @param string $filename Имя файла (включает путь)
     * @param null|string $attachmentName Имя вложения. Если значение `null`, то имя 
     *     определяется из имени файла (по умолчанию `null`).
     * @param array $options Настйроки добавляемые в заголовок браузера:
     *     - 'mimeType', MIME-тип ответа. Если значение `null`, заголовок 'Content-Type' 
     *     не будет установлен;
     *     - 'inline', устанавливает, должен ли браузер открывать файл в окне браузера. 
     *     Если значение `false`, то  появится диалоговое окно загрузки;
     *     - 'fileSize', длина загружаемого файла в байтах. Если значение `null`, то 
     *     заголовок 'Content-Length' не будет установлен.
     * 
     * @return $this
     */
    public function sendFile(string $filename, ?string $attachmentName = null, array $options = []): static
    {
        $options = array_merge(
            [
                'mimeType' => null,
                'inline'   => false,
                'fileSize' => null 
            ],
            $options
        );

        // MIME-тип
        if ($options['mimeType'] === null) {
            $mimeType = mime_content_type($filename);
            $options['mimeType'] = $mimeType === false ? 'application/octet-stream' : $mimeType;
        }
        // размер файла
        if ($options['fileSize'] === null) {
            $fileSize = filesize($filename);
            $options['fileSize'] = $fileSize === false ? null : $fileSize;
        }
        // вложение
        if ($attachmentName === null) {
            $attachmentName = basename($filename);
        }
        $this->getHeaders()
            ->setDownload($attachmentName, $options['mimeType'], $options['inline'], $options['fileSize']);

        /** @var string|false $content */
        $content = file_get_contents($filename, true);
        if ($content !== false) {
            $this->setContent($content);
        }
        return $this;
    }

    /**
     * Устанавливает токен для сессии и отправляет клиенту в cookie.
     */
    public function sendToken(): void
    {
        // если проверка CSRF
        if ($this->sendCsrfToken) {
            /** @var \Ge\Http\Request $request */
            $request = Ge::$app->request;
            if ($request->enableCsrfValidation && ($request->enableCsrfCookie || $request->enableCsrfSession)) {
                $token = $request->getCsrfToken();
                // если использовать токен для cookie
                if ($request->enableCsrfCookie) {
                    $this->getCookies()->add($request->createCsrfCookie($token, false));
                }
                // если использовать токен для сессии
                if ($request->enableCsrfSession) {
                    Ge::$app->session->setToken($token);
                }
            }
        }
    }

    /**
     * Выводит содержимое ответа.
     */
    public function sendContent(): void
    {
        $content = $this->replaceContent();
        if ($this->formatter) {
            $content = $this->formatter->format($this, $content);
        }
        echo $this->sentContent = $content;
    }

    /**
     * Возвращает последнее содержимое ответа в виде строки.
     * 
     * @return mixed Возвращает значение `null`, если содержимое не был отправлен или 
     *     сформирован.
     */
    public function getSentContent(): mixed
    {
        return $this->sentContent;
    }

    /**
     * Возвращает "неочищенный" контент {@see $sentContent}.
     * 
     * Если объект преобразуется в строку для формирования контента, то getRawContent
     * возвращает сам объект.
     * 
     * @return mixed Если значение `null`, контент не был отправлен или сформирован.
     */
    public function getRawContent(): mixed
    {
        return null;
    }

    /**
     * Сброс фразы и установка кода состояния.
     *
     * @see Response::$reasonPhrase
     * @see Response::$statusCode
     * 
     * @param int $code Код состояния.
     * 
     * @return $this
     */
    protected function saveStatusCode(int $code): static
    {
        $this->reasonPhrase = '';
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Возвращение строки вида: "HTTP/<версия> <код> <фраза>".
     * 
     * @return string
     */
    public function statusLineToString(): string
    {
        $status = sprintf(
            'HTTP/%s %d %s',
            $this->getVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        return trim($status);
    }

    /**
     * Устанавлевает код состояния HTTP-ответу.
     * 
     * @see Response::saveStatusCode()
     * 
     * @param int $code Код состояния.
     * 
     * @return $this
     */
    public function setStatusCode(int $code): static
    {
        if (!is_numeric($code)) {
            $code = $this->getAccordanceStatusCode($code);
        }
        if ($code == 0) {
            $code = 200;
        }
        $const = get_class($this) . '::STATUS_CODE_' . $code;
        if (!is_numeric($code) || !defined($const)) {
            $code = is_scalar($code) ? $code : gettype($code);
            throw new Exception\InvalidArgumentException(Ge::t('app','Invalid status response code provided: "{0}"', [$code]));
        }
        $this->saveStatusCode($code);
        return $this;
    }

    /**
     * Устанавливает код состояния по указанному исключению.
     * 
     * @see Response::setStatusCode()
     * 
     * @param \Ge\Exception\HttpException|\Exception $exception Исключение.
     * 
     * @return $this
     */
    public function setStatusCodeByException($exception): static
    {
        if ($exception instanceof Ge\Exception\HttpException) {
            $this->setStatusCode($exception->statusCode);
        } else {
            $this->setStatusCode(500);
        }
        return $this;
    }

    /**
     * Устанавливает код состояния по указанному исключению.
     * 
     * @see Response::setStatusCodeByException()
     * 
     * @param \Ge\Exception\HttpException|\Exception $exception Исключение.
     * @param mixed $content Содержимое ответа (по умолчанию `null`).
     * 
     * @return void
     */
    public function setByException($exception, $content = null): void
    {
        $this->setStatusCodeByException($exception);
        if ($content !== null) {
            $this->content = $content;
        }
        $this->trigger(
            self::EVENT_SET_EXCEPTION,
            [
                'response'  => $this,
                'exception' => $exception,
                'content'   => $content
            ]
        );
    }

    /**
     * Возвращает код состояния HTTP.
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Устанавливает фразу соответсвующая коду состояния HTTP.
     * 
     * @param string $reasonPhrase фраза.
     * 
     * @return $this
     */
    public function setReasonPhrase(string $reasonPhrase): static
    {
        $this->reasonPhrase = trim($reasonPhrase);
        return $this;
    }

    /**
     * Возвращает и устанавливает фразу соответсвующую коду состояния HTTP.
     * 
     * @see Response::$reasonPhrase
     * 
     * @return string
     */
    public function getReasonPhrase(): string
    {
        if ('' === $this->reasonPhrase and isset(self::$recommendedReasonPhrases[$this->statusCode])) {
            $this->reasonPhrase = self::$recommendedReasonPhrases[$this->statusCode];
        }
        return $this->reasonPhrase;
    }

    /**
     * Проверяет, имеет ли статус ошибку клиента.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isClientError(): bool
    {
        $code = $this->getStatusCode();
        return ($code < 500 && $code >= 400);
    }

    /**
     * Проверяет, имеет ли статус запрет доступа к ресурсу.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isForbidden(): bool
    {
        return (403 == $this->getStatusCode());
    }

    /**
     *  Проверяет, имеет ли статус информационность.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isInformational(): bool
    {
        $code = $this->getStatusCode();
        return ($code >= 100 && $code < 200);
    }

    /**
     * Проверяет, имеет ли статус не найденого ресурса.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isNotFound(): bool
    {
        return (404 === $this->getStatusCode());
    }

    /**
     * Проверяет, имеет ли статус успеха.
     * 
     * Ответ успешен если код состояния 200.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isOk(): bool
    {
        return (200 === $this->getStatusCode());
    }

    /**
     * Проверяет, имеет ли статус ошибку сервера.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isServerError(): bool
    {
        $code = $this->getStatusCode();
        return (500 <= $code && 600 > $code);
    }

    /**
     * Проверяет, имеет ли статус перенаправление на ресурс.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isRedirect(): bool
    {
        $code = $this->getStatusCode();
        return (300 <= $code && 400 > $code);
    }

    /**
     * Проверяет коду состояния HTTP, успешен ли ответ.
     * 
     * @see Response::getStatusCode()
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        $code = $this->getStatusCode();
        return (200 <= $code && 300 > $code);
    }

    /**
     * Возвращает категорию кода состояния.
     * 
     * @param null|int $code Если код состояния `null`, используется текущий код 
     *     состояния {@see Response::getStatusCode()} (по умолчанию `null`).
     * 
     * @return string|null Возвращает значение `null`, если код состояния не соответствует 
     *     категории.
     */
    public function getStatusCategory(?int $code = null): ?string
    {
        if ($code === null)
            $code = $this->getStatusCode();
        // 1xx Informational
        if ($code >= 100 && $code < 200) {
            return 'informational';
        }
        // 2xx Success
        if (200 <= $code && 300 > $code) {
            return 'success';
        }
        // 3xx Redirection
        if (300 <= $code && 400 > $code) {
            return 'redirection';
        }
        // 4xx Client Error
        if ($code < 500 && $code >= 400) {
            return 'client error';
        }
        // 5xx Server Error
        if (500 <= $code && 600 > $code) {
            return 'server error';
        }
        return null;
    }

    /**
     * Перенаправляет браузер на указанный URL-адрес.
     *
     * Этот метод добавляет к текущему ответу заголовок "Location", он не отправляет заголовок, 
     * пока не будет вызван {@see Response::send()}.
     * В действии контроллера можно использовать следующим образом:
     * 
     * ```php
     * return Ge::$app->getResponse()->redirect($url);
     * ```
     *
     * Если необходимо сразу отправить заголовок "Location", используйте следующий код:
     *
     * ```php
     * Ge::$app->getResponse()->redirect($url)->send();
     * return;
     * ```
     *
     * В режиме AJAX, работать должным образом не будет, если нет клиентского кода JavaScript 
     * (обрабатывающий перенаправление). Чтобы решить эту проблему, будет отправляться 
     * заголовок "X-Redirect" вместо "Location".
     * 
     * @param string|array $url Параметры используемые при создании URL-адреса {@see \Ge\Url\UrlManager::createUrl()}.
     *
     * - URL-адрес представлен в виде строки (например "http://example.com");
     * - URL-адрес представлен в виде массива:
     *     `[$route, '?' => [...пары имя-значение...], '#' => $anchor]` (например `['foo/bar', '?' => ['ref' => 1], '#' => 'goto']`)
     * Обратите внимание, что маршрут относится ко всему приложению, а не к контроллеру или модулю.
     * {{@see \Ge\Helper\Url::to()} будет использоваться для преобразования массива в URL.
     *
     * @param int $statusCode Код состояния HTTP (по умолчанию 302).
     *    Для получения подробной информации о коде статуса HTTP, смотри {@link https://tools.ietf.org/html/rfc2616#section-10}.
     * @param bool $checkAjax Следует ли специально обрабатывать запросы AJAX (и PJAX). По умолчанию установлено значение true,
     *    что означает, что если текущий запрос является запросом AJAX или PJAX, то вызов этого метода приведет к перенаправлению 
     *    браузера на указанный URL-адрес. Если false, будет отправлен заголовок "Location", который при получении в виде 
     *    ответа AJAX / PJAX не может вызвать перенаправление браузера.
     * Вступает в силу только при отсутствии заголовка запроса `X-Ie-Redirect-Compatibility`.
     * 
     * @return $this
     */
    public function redirect($url, int $statusCode = 302, bool $checkAjax = true): static
    {
        $url = Url::to($url);
        if ($checkAjax) {
            $request = Ge::$app->request;
            if ($request->isAjax()) {
                if (in_array($statusCode, [301, 302]) && preg_match('/Trident\/|MSIE[ ]/', $request->getUserAgent())) {
                    $statusCode = 200;
                }
                if ($request->isPjax()) {
                    $this->getHeaders()->set('X-Pjax-Url', $url);
                } else {
                    $this->getHeaders()->set('X-Redirect', $url);
                }
            } else {
                $this->getHeaders()->set('Location', $url);
            }
        } else {
            $this->getHeaders()->set('Location', $url);
        }
        $this->setStatusCode($statusCode);
        return $this;
    }

    /**
     * Устанавливает Куки.
     * 
     * @link https://www.php.net/manual/ru/function.setcookie
     * 
     * @return void
     */
    public function sendCookies(): void
    {
        if ($this->cookies === null) return;

        foreach ($this->cookies as $cookie) {
            // если версия PHP => 7.3
            if (PHP_VERSION_ID >= 70300) {
                setcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    [
                        'expires'  => $cookie->getExpiresTime(),
                        'path'     => $cookie->getPath(),
                        'domain'   => $cookie->getDomain(),
                        'secure'   => $cookie->isSecure(),
                        'httpOnly' => $cookie->isHttpOnly(),
                        'sameSite' => $cookie->getSameSite() ?? null
                    ]
                );
            } else {
                $sameSite = $cookie->getSameSite();
                $path     = $cookie->getPath();
                if ($sameSite !== null) {
                    $path .= '; samesite=' . $sameSite;
                }
                setcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $path,
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            }
        }
    }

    /**
     * Возвращает коллекцию Куки.
     *
     * @return CookieCollection
     */
    public function getCookies(): CookieCollection
    {
        if ($this->cookies === null) {
            $this->cookies = new CookieCollection();
        }
        return $this->cookies;
    }

    /**
     * Вызывает метод доступный только для форматировщика (fromatter) ответа.
     *
     * @param string $name Имя вызываемого метода.
     * @param array $arguments Нумерованный массив, содержащий параметры, переданные 
     *     в вызываемый метод `$name`.
     * 
     * @return mixed Результат выполнения метода.
     */
    public function __call(string $name, array $arguments) {
        return $this->formatter ? call_user_func_array([$this->formatter, $name], $arguments) : null;
    }

    /**
     * Возращает значение свойства форматировщика (fromatter) ответа.
     *
     * @param string $property Свойство форматировщика ответа.
     * 
     * @return mixed Возвращает значение `null`, если указанное свойство форматировщика 
     *     не существует или форматировщика ещё не создан.
     */
    public function __get(string $property)
    {
        if ($this->formatter) {
            return isset($this->formatter->{$property}) ? $this->formatter->{$property} : null;
        }
        /** для behaviors */
        return $this->getBehavior($property);
    }
}
