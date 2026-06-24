<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log\Writer;

/**
 * Класс писателя сообщений в файл журнала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
class MessageWriter extends FileWriter
{
    /**
     * {@inheritdoc}
     */
    public string $formatMessage = '[%timestamp%]: %message%';
}
