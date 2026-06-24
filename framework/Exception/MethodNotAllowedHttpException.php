<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Exception;

use Throwable;

/**
 * Исключение представлено в виде ошибки HTTP «Метод не разрешен» с кодом состояния 405.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class MethodNotAllowedHttpException extends HttpException
{
    /**
     * {@inheritdoc}
     */
    public ?string $responseFormat = GE_MODE_PRO ? 'raw' : null;

    /**
     * Метод запроса известен серверу, но был отключён и не может быть использован.
     * 
     * @var string
     */
    public string $method = '';

    /**
     *  Доступные метод(ы) запроса.
     * 
     * @var array|string
     */
    public array|string $allowed = [];

    /**
     * Конструктор класса.
     * 
     * @param string $method Метод запроса известен серверу, но был отключён и не может быть использован.
     * @param array|string $allowed Доступные метод(ы) запроса.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $method, array|string $allowed = [], string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->method  = $method;
        $this->allowed = $allowed;

        parent::__construct(405, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        if ($this->allowed) {
            if (is_array($this->allowed)) {
                $this->allowed = implode(', ', $this->allowed);
            }
            return sprintf('Method "%s" Not Allowed. This URL can only handle the following request methods: %s', $this->method, $this->allowed);
        }
        return sprintf('Method "%s" Not Allowed.', $this->method);
    }
}
