<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\WidgetManager\Exception;

use Throwable;
use Ge\Exception\NotFoundException;

/**
 * Исключение возникающие при отсутствии виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager\Exception
 * @since 2.0
 */
class WidgetNotFoundException extends NotFoundException
{
    /**
     * {@inheritdoc}
     */
    public string $viewFile = '//pages/404';

    /**
     * {@inheritdoc}
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
