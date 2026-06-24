<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http\Response;

/**
 * ResponseFormatterInterface определяет интерфейс Форматтера, необходимый для 
 * форматирования ответа перед его отправкой.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http\Response
 * @since 2.0
 */
interface ResponseFormatterInterface
{
    /**
     * Форматирует указанный ответ.
     * 
     * @param \Ge\Http\Response HTTP-ответ.
     * @param mixed $content Ответ который должен быть отформатирован.
     * 
     * @return mixed
     */
    public function format(\Ge\Http\Response $response, mixed $content): mixed;

    /**
     * Форматирует указанный ответ согласно брошенному исключению.
     * 
     * @param \Ge\Http\Response HTTP-ответ.
     * @param \Exception $exception Исключение.
     * @param mixed $content Ответ который должен быть отформатирован.
     * 
     * @return void
     */
    public function formatException(\Ge\Http\Response $response, $exception, mixed $content): void;

    /**
     * Подготовка к форматированию контента.
     * 
     * @param \Ge\Http\Response HTTP-ответ.
     * 
     * @return void
     */
    public function prepare(\Ge\Http\Response $response): void;
}
