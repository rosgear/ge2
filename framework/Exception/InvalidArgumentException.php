<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Exception;

/**
 * Исключение возникающие при вызове недействительных аргументов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class InvalidArgumentException extends UserException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Invalid Argument';
    }
}
