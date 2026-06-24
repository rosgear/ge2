<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Config\Exception;

use Ge\Exception;

/**
 * Исключение вызываемое при отсутствии файла кофигурации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Config\Exception
 * @since 2.0
 */
class FileNotFoundException extends Exception\BootstrapException
{
}
