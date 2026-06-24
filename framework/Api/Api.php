<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace  Ge\Api;

use Ge\Stdlib\BaseObject;
use Ge\Stdlib\ErrorTrait;

/**
 * Класс реализации REST API для модулей и их расширений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Api
 * @since 2.0
 */
class Api extends BaseObject
{
    use ErrorTrait;

    /**
     * Состояние ответа на API-запрос.
     * 
     * Если запрос успешный - значение 'OK'. Если ошибка при обработке запроса, то 
     * код ошибки, например 'NOT_FOUND'.
     * 
     * @var string
     */
    public string $status = '';

    /**
     * Конфигурация владельца (модуля, расширения) выполняющего API.
     * 
     * @var array
     */
    public array $owner = [];

    /**
     * Включает буфер вывода перед формирование контента ответа.
     * 
     * @see Api::apiCall()
     * 
     * @var bool
     */
    public bool $useOutputBuffer = false;

    /**
     * Маршрут для определения API-ответа.
     * 
     * @see Api::apiCall()
     * 
     * @var string
     */
    public string $route;

    /**
     * @var array
     */
    public array $module = [];

    /**
     * @return void
     */
    public function invalidParameter(string $name, string $defaultMsg = ''): void
    {
        if (GE_MODE_DEV)
            $error = sprintf(
                'Invalid parameter "%s" for class "%s" of module "%s"',
                $name,
                $this->getClass(),
                $this->owner['id'] ?? SYMBOL_NONAME
            );
        else
            $error = $defaultMsg ?: sprintf('Invalid parameter "%s"', $name);

        $this->addError($error, 422, 'INVALID_PARAMETER');
    }

    /**
     * Удаляет все ошибки из очереди.
     * 
     * @return $this
     */
    public function clearErrors(): static
    {
        $this->errors = [];
        $this->errorCode = null;
        $this->status = '';
        return $this;
    }

    /**
     * Добавляет ошибку в очередь.
     * 
     * @param string $error Сообщение об ошибке.
     * @param mixed $code Код ошибки (по умолчанию `null`).
     * @param string $status Статус ошибки (по умолчанию '').
     * 
     * @return $this
     */
    public function addError(string $error, mixed $code = null, string $status = ''): static
    {
        $this->errors[] = $error;
        if ($code)
            $this->errorCode = $code;
        if ($status)
            $this->status = $status;
        return $this;
    }

    /**
     * Устанавливает сообщение об ошибке в очередь.
     * 
     * @param string $error Сообщение об ошибке.
     * @param mixed $code Код ошибки (по умолчанию `null`).
     * @param string $status Статус ошибки (по умолчанию '').
     * 
     * @return $this
     */
    public function setError(string $error, mixed $code = null, string $status = ''): static
    {
        $this->errors[0] = $error;
        if ($code)
            $this->errorCode = $code;
        if ($status)
            $this->status = $status;
        return $this;
    }

    /**
     * Возвращает состояние ответа.
     * 
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Возвращает маршруты определяющие API-ответ.
     * 
     * Для каждого маршрута указывается имя метода, формирующий ответ.
     * Пример: `['api/post/view' => 'postView', ...]`.
     * 
     * @return array
     */
    public function getRoutes(): array
    {
        return [];
    }

    /**
     * Вызывает метод формирующий HTTP-ответ.
     * 
     * @param null|string $route Маршрут запроса.
     * 
     * @return mixed Возвращает значение `false`, если возникла ошибка при формировании HTTP-ответа.
     */
    public function apiCall(?string $route = null): mixed
    {
        if (!isset($route)) {
            $route = $this->route;
        }

        /** @var array $routes Маршруты ответа */
        $routes = $this->getRoutes();

        // если не указан маршрут
        if (empty($route) || empty($routes)) {
            $this->addError(
                GE_MODE_DEV ? sprintf('Module "%s" API class "%s", route not specified', $this->module['id'], $this->getClass()) : '',
                404,
                'NOT_FOUND'
            );
            return false;
        }

        // если нет необходимого маршрута
        if (!isset($routes[$route])) {
            $this->addError(
                GE_MODE_DEV ? sprintf('Module "%s" API class "%s" has no route "%s"', $this->module['id'], $this->getClass(), $route) : '',
                404,
                'NOT_FOUND'
            );
            return false;
        }

        // если метод не существует для указанного маршрута
        $method = $routes[$route];
        if (!method_exists($this, $method)) {
            $this->addError(
                GE_MODE_DEV ? sprintf('Module "%s" API class "%s" has no method "%s"', $this->module['id'], $this->getClass(), $method) : '',
                422,
                'NOT_FOUND'
            );
            return false;
        }

        // использовать буфер вывода
        if ($this->useOutputBuffer) {
            ob_start();
            $this->$method();
            $content = ob_get_contents();
            ob_end_clean();
        } else
            $content = $this->$method();
        return $content;
    }
}
