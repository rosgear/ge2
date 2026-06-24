<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\WidgetManager;

use Ge\ModuleManager\BaseRepository;

/**
 * Класс репозитория виджетов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\WidgetManager
 * @since 2.0
 */
class WidgetRepository extends BaseRepository
{
    /**
     * Конструктор класса.
     * 
     * @param null|WidgetManager $manager Менеджер виджетов.
     * 
     * @return void
     */
    public function __construct(?WidgetManager $manager = null)
    {
        $this->manager = $manager;
    }
}
