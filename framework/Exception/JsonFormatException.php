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
 *  Исключение представлено в виде ошибки при последнем JSON кодировании/декодировании.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class JsonFormatException extends FormatException
{
    /**
     * Конструктор класса.
     * 
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if ($message === '') {
            switch($code = json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $message = 'maximum stack depth exceeded';
                    break;

                case JSON_ERROR_CTRL_CHAR:
                    $message= 'unexpected control character found';
                    break;

                case JSON_ERROR_SYNTAX:
                    $message = 'syntax error, malformed JSON';
                    break;

                default:
                    $message = 'unknow JSON exception';
            }
            $message = "JSON error ($message)";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'JSON Format';
    }
}
