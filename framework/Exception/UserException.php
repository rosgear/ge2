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
 * Базовый класс исключения, который предназначен для вывода конечному пользователю.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class UserException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'User exception.';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'User exception';
    }
}
