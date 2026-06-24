<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Api\Response;

use Ge\Exception\BaseException;
use Ge\Http\Response\JsonResponseFormatter;

/**
 * Класс Форматтера для форматирования HTTP-ответа (результат выполнения API) в формат JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Api\Response
 * @since 2.0
 */
class ApiResponseFormatter extends JsonResponseFormatter
{
    /**
     * Метаданные формата JSON.
     * 
     * @var ApiMetaData
     */
    public ApiMetaData $meta;

    /**
     * Конструктор класса.
     * 
     * @param \Ge\Http\Response $response HTTP-ответ.
     * 
     * @return void
     */
    public function __construct(\Ge\Http\Response $response)
    {
        parent::__construct($response);

       $this->meta = new ApiMetadata();
    }

   /**
     * {@inheritdoc}
     */
    public function format(\Ge\Http\Response $response, mixed $content): mixed
    {
        /** 
         * не будем использовать $content самого Response,
         * нужен чистый ответ 
         * $this->meta->content($content); 
         */
        $text = $this->meta->message;
        // добавление к контенту исключений
        if ($response->exceptionContent) {
            $this->meta->success = false;
            $text .= $response->exceptionContent;
        }
        $this->meta->message = $text;
        $result = $this->meta->toString();
        if ($result === false)
            return json_last_error_msg();
        else
            return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(\Ge\Http\Response $response, $exception, mixed $content): void
    {
        // Если исключение поймано через обработчик ошибок {\Ge\ErrorHandler\WebErrorHandler} и
        // $content не был указан перед вызовом исключения, то он будет содержать сообщение об ошибке, 
        // полученное через getPlainDispatch() исключения.
    
        // удостоверимся, что это наше исключение
        if ($exception instanceof BaseException)
            $message = $exception->getPlainDispatch();
        else 
            $message = $exception->getMessage();
        // если режим "development"
        if (GE_MODE_DEV)
            $this->meta->error($message);
        // если режим "production"
        else
            $this->meta->error('Server Error');
    }
}
