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
 * Класс писателя отсылаемых писем в файл журнала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
class MailWriter extends FileWriter
{
    /**
     * {@inheritdoc}
     */
    public string $formatMessage = "[%timestamp%]  %message% \r\n%body%";

    /**
     * {@inheritdoc}
     */
    public function write(array $message): void
    {
        $msg = $message['message'];
        if (is_array($msg)) {
            $message['message'] = $msg['message'] ?? '';
            $message['body']    = $$msg['body'] ?? '';
        }

        parent::write($message);
    }
}
