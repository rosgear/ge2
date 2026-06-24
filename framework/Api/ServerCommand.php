<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

 namespace Ge\Api;

use Ge;
use CurlHandle;
use Ge\Helper\Json;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\ErrorTrait;

/**
 * Комманды выполнения запросов к серверу API.
 * 
 * В качестве сервера используется сервер RosGear API.
 * Класс использует cURL для получения обновлений, лицензионных ключей и т.д. для текущей 
 * редакции веб-приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Api
 * @since 1.0
 */
class ServerCommand extends BaseObject
{
    use ErrorTrait;

    /**
     * URL-адрес API сервера.
     *
     * @var string
     */
    public string $url;

    /**
     * Если результат выполнения последней команды был успешен.
     * 
     * @see ServerCommand::execute()
     *
     * @var bool
     */
    public bool $isSuccess = true;

    /**
     * Если полученный ответ содержит успешный результат.
     * 
     * Если ответ в формате JSON, то он должен иметь свойство 'success'. 
     * Например: `{"success": true}`.
     * 
     * @see ServerCommand::execute()
     *
     * @var bool
     */
    public bool $isResponseSuccess = false;

    /**
     * Статус возвращаемой в последнем HTTP-запросе.
     * 
     * Если ответ в формате JSON, то он должен иметь свойство 'status'. 
     * Например: `{"status": "NOT_FOUND"}`.
     * 
     * @see ServerCommand::execute()
     * 
     * @var string|null
     */
    public ?string $responseStatus = null;

    /**
     * Максимально время (в сек.) ожидания HTTP-ответа.
     * 
     * @var int
     */
    public int $timeout = 0;

    /**
     * Последнии параметры в запросе методом POST или GET.
     * 
     * @var array
     */
    public array $postFields = [];

    /**
     *  Последнии заголовки в запросе.
     * 
     * @var array
     */
    public array $headers = [];

    /**
     * Код статуса возвращаемого HTTP-запроса.
     *
     * @var int|null
     */
    public ?int $statusCode = null;

    /**
     * Содержимое полученного заголовка Content-Type.
     * 
     * Если значение `null`, то сервер не послал правильный заголовок Content-Type.
     * 
     * @var string|null
     */
    public ?string $contentType = null;

    /**
     * @var string|null
     */
    public ?String $errorStatus = null;

    /**
     * Дескриптор последнего сеанса cURL.
     * 
     * @see ServerCommand::execute()
     *
     * @var CurlHandle|false
     */
    protected CurlHandle|false $handle = false;

    /**
     * Результат последнего запроса cURL.
     * 
     * @see ServerCommand::execute()
     *
     * @var mixed
     */
    protected mixed $response = null;

    /**
     * Результат последнего запроса cURL.
     * 
     * @see ServerCommand::execute()
     *
     * @var mixed
     */
    protected mixed $responseText = null;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!isset($this->url)) {
            $this->url = $this->getUrl();
        }
    }

    /**
     * Возвращает дескриптор последнего сеанса cURL.
     * 
     * @return CurlHandle|false
     */
    public function getHandle(): CurlHandle|false
    {
        return $this->handle;
    }

    /**
     * Возвращает результат последнего запроса cURL.
     * 
     * @return mixed
     */
    public function getResponse(): mixed
    {
        return $this->response;
    }

    /**
     * Возвращает результат последнего запроса cURL.
     * 
     * @return mixed
     */
    public function getResponseText(): mixed
    {
        return $this->responseText;
    }

    /**
     * Возвращает URL-адрес API сервера.
     * 
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return Ge::$app->config->apiServer['url'] ?? null;
    }

    /**
     * @see ServerCommand::getLicenseKey()
     * 
     * @var string
     */
    private string $licenseKey;

    /**
     * Возвращает лицензионный ключ редакции веб-приложения.
     * 
     * @return string
     */
    public function getLicenseKey(): string
    {
        if (!isset($this->licenseKey)) {
            $this->licenseKey = Ge::$app->license->getKey();
        }
        return $this->licenseKey;
    }

    /**
     * Возвращает имя домена.
     * 
     * Применяется для проверки лицензионного ключа.
     * 
     * @return string
     */
    public function getDomain(): string
    {
        return Ge::$app->request->getServerName();
    }

    /**
     * Возвращает первое локализованное сообщение об ошибке из очереди.
     * 
     * Для локализации должен быть подключен файл локализации API запросов '.../lang/ru_RU/api.php'
     * (категория 'api' для применения {@see \Ge::t()}).
     * Пример: `Ge::t('api', 'Unable to execute API request')`.
     * 
     * @param bool $validate Проверяет возможность локализации ошибки. Если значение 
     *     `true` и нет перевода ошибки, то результат ''.
     * 
     * @return string
     */
    public function getLocalizedError(bool $validate = false): string
    {
        $error = $this->getErrors(0);
        if ($error) {
            $loError = Ge::t('api', $error);
            if ($validate) {
                return $error === $loError ? '' : $loError;
            }
            return $loError;
        }
        return '';
    }

    /**
     * Проверяет, была ли ошибка в лицензионном ключе в последнем запросе к API серверу.
     * 
     * @return bool
     */
    public function licenseKeyHasError(): bool
    {
        if (!$this->isResponseSuccess && $this->responseStatus) {
            /** @var \Ge\License\License */
            return Ge::$app->license->hasStatus($this->responseStatus);
        }
        return false;
    }

    /**
     * Выполняет сброс значений перед выполнением запроса к API серверу.
     * 
     * @return void
     */
    protected function reset(): void
    {
        $this->response       = null;
        $this->responseText   = null;
        $this->responseStatus = null;
        $this->statusCode     = null;
        $this->contentType    = null;
        $this->isSuccess      = true;
        $this->isResponseSuccess = false;
    }

    /**
     * Имена параметров с их значениями передаваемых в запроса по умолчанию.
     * 
     * @see ServerCommand::execute()
     * 
     * @param array $post Имена параметров с их значениями передаваемые методом POST.
     * 
     * @return array
     */
    public function defaultPostFields(array $post): array
    {
        if (!isset($post['licenseKey'])) {
            $post['licenseKey'] = $this->getLicenseKey();
        }
        if (!isset($post['domain'])) {
            $post['domain'] = $this->getDomain();
        }
        if (!isset($post['language'])) {
            $post['language'] = Ge::$app->language->locale;
        }
        return $post;
    }

    /**
     * Выполняет запроса к API серверу.
     * 
     * @return false|array
     */
    public function execute(string $urlPath = '', array $params = []): false|array
    {
        $this->reset();

        $this->postFields = $this->defaultPostFields($params['post'] ?? []);
        $this->headers    = $params['headers'] ?? [];

        $this->handle = $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_URL, $this->url . $urlPath);

        curl_setopt($curl, CURLOPT_POST, true);
        if ($this->postFields) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->postFields));
        }

        if ($this->timeout) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        }

        $this->responseText = curl_exec($curl);
        $this->statusCode   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->isSuccess    = $this->statusCode === 200;
        $this->contentType  = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        Ge::debug('API request', [
            'URL'         => $this->url . $urlPath,
            'header'      => $this->headers,
            'postFields'  => $this->postFields,
            'statusCode'  => $this->statusCode,
            'success'     => $this->isSuccess,
            'contentType' => $this->contentType,
            'reponseText' => $this->responseText,
        ]);

        if(curl_errno($curl)) {
            $this->addError(curl_error($curl));
            return false;
        }

        if (empty($this->responseText) && !$this->isSuccess) {
            // Невозможно выполнить API запрос
            $this->addError('Unable to execute API request');
            return false;
        }

        $response = Json::decode($this->responseText);
        if (Json::error()) {
            // Невозможно получить данные из API запроса в формате JSON
            $this->addError('Unable to get API data in JSON format');
            return false;
        }

        $this->response          = $response;
        $this->responseStatus    = $response['status'] ?? null;
        $this->isResponseSuccess = (bool) ($response['success'] ?? false);

        if (!$this->isResponseSuccess) {
            $message = $response['message'] ?? null;
            // Возникла неизвестная ошибка при получении данных из API запроса
            $this->addError($message ?: 'An unknown error occurred while fetching API data');
            return false;
        }
        return $this->response;
    }

    /**
     * Выполняет запроса к API серверу.
     * 
     * @return bool
     */
    public function download(mixed $downloadHandle, string $urlPath = '', array $params = []): bool
    {
        $this->reset();

        $this->postFields = $params['post'] ?? [];
        $this->headers    = $params['headers'] ?? [];

        if (!isset($this->postFields['licenseKey'])) {
            $this->postFields['licenseKey'] = $this->getLicenseKey();
        }

        $this->handle = $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_URL, $this->url . $urlPath);
        curl_setopt($curl, CURLOPT_POST, true);
        if ($this->postFields)
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->postFields));
        if ($this->timeout)
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FILE, $downloadHandle);
        
        curl_exec($curl);
        $this->statusCode  = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->isSuccess   = $this->statusCode === 200;
        $this->contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        if(curl_errno($curl)) {
            $this->addError(curl_error($curl));
            return false;
        }

        if (!$this->isSuccess) {
            // ошибка записана в файл
            if (rewind($downloadHandle)) {
                $this->responseText = stream_get_contents($downloadHandle);
            }

            if (empty($this->responseText)) {
                // Невозможно выполнить API запрос
                $this->addError('Unable to execute API request');
                return false;
            }

            $response = Json::decode($this->responseText);
            if (Json::error()) {
                // Невозможно получить данные из API запроса в формате JSON
                $this->addError('Unable to get API data in JSON format');
                return false;
            }

            $this->response    = $response;
            $this->errorStatus = $response['status'] ?? null;

            $message = $response['message'] ?? null;
            // Возникла неизвестная ошибка при получении данных из API запроса
            $this->addError($message ?: 'An unknown error occurred while fetching API data');
            return false;
        }
        return true;
    }
}
