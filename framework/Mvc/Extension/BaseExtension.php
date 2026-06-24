<?php
/**
 * RosGear
 * 
 * @link https://rosgear.ru
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Extension;

use Ge\Mvc\Module\BaseModule;

/**
 * Расширение модуля является базовым классом для всех классов-наследников расширения.
 * 
 * Расширение модуля дополняет архитектуру MVC и может содержать такие ёё элементы, как модели, 
 * представления, контроллеры и т.д.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Extension
 * @since 2.0
 */
class BaseExtension extends BaseModule
{
    /**
     * Модуль управляющий расширением.
     * 
     * Устанавливается из конфигурации в конструкторе класса.
     * 
     * @var BaseModule|null
     */
    public ?BaseModule $parent = null;

    /**
     * {@inheritdoc}
     */
    public function getId(bool $signature = false): string
    {
        if ($signature)
            return 'extension:' . $this->id;
        else
            return $this->id;
    }
}
