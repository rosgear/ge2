<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage;

use Ge\ServiceManager\ServiceManager;

/**
 * Менеджер плагинов адаптера.
 * 
 * Менеджер создаёт и хранит плагины адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
class AdapterPluginManager extends ServiceManager
{
    /**
     * {@inheritdoc}
     */
    public $invokableClasses = [
        'session'   => 'Ge\Cache\Storage\Adapter\Session',
        'memcache'  => 'Ge\Cache\Storage\Adapter\Memcache',
        'memcached' => 'Ge\Cache\Storage\Adapter\Memcached'
    ];
}
