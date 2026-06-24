<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log;

use Ge\Session\Storage\SessionStorage;

/**
 * Хранилище записей журнала в сессии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log
 * @since 2.0
 */
class LogStorage extends SessionStorage
{
    /**
     * {@inheritdoc}
     */
    protected $namespace = 'Ge_Storage';
}
