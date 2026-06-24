<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ModuleManager;

/**
 * Класс репозитория модулей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ModuleManager
 * @since 2.0
 */
class ModuleRepository extends BaseRepository
{
    /**
     * Конструктор класса.
     *
     * @param null|ModuleManager $manager Менеджер модулей.
     *
     * @return void
     */
    public function __construct(?ModuleManager $manager = null)
    {
        $this->manager = $manager;
    }
}