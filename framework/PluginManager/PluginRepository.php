<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\PluginManager;

use Ge\ModuleManager\BaseRepository;

/**
 * Класс репозитория плагинов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\PluginManager
 * @since 2.0
 */
class PluginRepository extends BaseRepository
{
    /**
     * Конструктор класса.
     * 
     * @param null|PluginManager $manager Менеджер плагинов.
     * 
     * @return void
     */
    public function __construct(?PluginManager $manager = null)
    {
        $this->manager = $manager;
    }
}
