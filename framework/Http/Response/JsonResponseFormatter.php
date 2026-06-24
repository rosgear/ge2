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
 * Класс Форматтера для форматирования HTTP-ответа в формат JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class JsonResponseFormatter extends AbstractResponseFormatter
{
    /**
     * {@inheritdoc}
     */
    public function prepare(\Ge\Http\Response $response): void
    {
        $response->getHeaders()->add('content-type', 'application/json');
    }
}
