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
 * Исключение представлено в виде ошибки HTTP 403 с запретом IP-адресу клиента 
 * просмотра контента.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class IpAddressNotAllowedHttpException extends HttpException
{
    /**
     * {@inheritdoc}
     */
    public string $viewFile = '//errors/423';

    /**
     * IP-адрес клиента.
     * 
     * @var string
     */
    public string $ipAddress = '';

    /**
     * Конструктор класса.
     * 
     * @param string $ipAddress IP-адрес клиента.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $ipAddress = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->ipAddress  = $ipAddress;

        parent::__construct(423, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'Your IP address ' . ($this->ipAddress ? '"' . $this->ipAddress . '" ' : '') . 'cannot be used to access content.';
    }
}
