<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Exception;

use Ge\Exception\UserException;

/**
 * Исключение возникающие во время выполнения действия.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class RuntimeException extends UserException
{
    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'Runtime exception';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Runtime exception';
    }
}
