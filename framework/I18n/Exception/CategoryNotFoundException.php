<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\Exception;

use Ge\Exception\NotDefinedException;

/**
 * Исключение возникающие при отсутствии категории локализации сообщения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\Exception
 * @since 2.0
 */
class CategoryNotFoundException extends NotDefinedException
{
}
