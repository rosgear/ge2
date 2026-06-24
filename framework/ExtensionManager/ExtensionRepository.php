<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ExtensionManager;

use Ge\ModuleManager\BaseRepository;

/**
 * Класс репозитория расширений модулей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class ExtensionRepository extends BaseRepository
{
    /**
     * Конструктор класса.
     *
     * @param null|ExtensionManager $manager Менеджер расширений.
     *
     * @return void
     */
    public function __construct(?ExtensionManager $manager = null)
    {
        $this->manager = $manager;
    }
}
