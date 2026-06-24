<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http\Response;

use Ge\Http\Response;

/**
 * Абстрактный класс Форматтера, необходимый для форматирования ответа перед его 
 * отправкой.
 * 
 * - подготавливает контент к форматированию в событии {@see Response::EVENT_BEFORE_SEND} 
 * HTTP-ответа;
 * - форматирует указанный ответ согласно брошенному исключению для события {@see Response::EVENT_SET_EXCEPTION} 
 * HTTP-ответа.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http\Response
 * @since 2.0
 */
abstract class AbstractResponseFormatter implements ResponseFormatterInterface
{
    /**
     * Конструктор класса.
     * 
     * @param \Ge\Http\Response $response HTTP-ответ.
     * 
     * @return void
     */
    public function __construct(\Ge\Http\Response $response)
    {
        $response
            ->on(Response::EVENT_BEFORE_SEND, [$this, 'prepare'])
            ->on(Response::EVENT_SET_EXCEPTION, [$this, 'formatException']);
    }

    /**
     * {@inheritdoc}
     */
    public function format(\Ge\Http\Response $response, mixed $content): mixed
    {
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(\Ge\Http\Response $response, $exception, mixed $content): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(\Ge\Http\Response $response): void
    {
    }
}
