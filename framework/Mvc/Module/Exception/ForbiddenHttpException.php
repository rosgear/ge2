<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Module\Exception;

use Ge\Exception;

/**
 * Исключение возникающие при ограниченном доступе к модулю.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Module\Exception
 * @since 2.0
 */
class ForbiddenHttpException extends Exception\ForbiddenHttpException
{
    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'You are not allowed to perform this action';
    }
}
