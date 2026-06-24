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
 * Исключение представлено в виде ошибки HTTP («служба не доступна») с кодом состояния 503.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class PageUnavailableException extends NotFoundException
{
    /**
     * {@inheritdoc}
     */
    public string $viewFile = '//errors/unavailable';

    /**
     * Конструктор класса.
     * 
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $viewFile = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if ($viewFile) {
            $this->viewFile = $viewFile;
        }

        parent::__construct(503, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('503 Service Unavailable.');
    }
}
