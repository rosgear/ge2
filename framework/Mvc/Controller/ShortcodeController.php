<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Controller;

use Ge;
use Ge\View\HelperManager;
use Ge\Mvc\Module\BaseModule;

/**
 * Базовый класс контроллера реализующий логику шорткодов (shortcodes).
 * 
 * Контроллер имеет свойства и методы для взаимодействия с шорткодами.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Controller
 * @since 2.0
 */
class ShortcodeController extends Controller
{
    /**
     * Менеджер управления помощниками представления.
     * 
     * @var HelperManager
     */
    public HelperManager $viewHelperManager;

    /**
     * Имя шорткода.
     * 
     * @var string
     */
    public string $shortcode = '';

    /**
     * Атрибуты шорткодов.
     * 
     * @var array
     */
    protected array $shortcodeAttributes = [];

    /**
     * Контент шорткода.
     * 
     * @var string
     */
    protected string $shortcodeContent = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(BaseModule $module, string $action = '', array $config = [])
    {
        parent::__construct($module, $action, $config);

        $this->viewHelperManager = Ge::$services->getAs('viewHelperManager');
    }

    /**
     * Устанавливает контент шорткода.
     * 
     * Пример: '[widget name="foobar"]'.
     * 
     * @param string $content Контент шорткода.
     * 
     * @return $this
     */
    public function setShortcodeContent(string $content): static
    {
        $this->shortcodeContent = $content;
        return $this;
    }

    /**
     * Устанавливает атрибуты шорткода.
     * 
     * @param array $attributes Атрибуты шорткода.
     * 
     * @return $this
     */
    public function setAttributes(array $attributes): static
    {
        $this->shortcodeAttributes = $attributes;
        return $this;
    }

    /**
     * Устанавливает атрибут шорткода.
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->shortcodeAttributes[$name] = $value;
        return $this;
    }

    /**
     * Возвращает атрибут шорткода.
     * 
     * @param string $name Имя атрибута
     * @param mixed $default Значение по умолчанию (если атрибут не существует).
     * 
     * @return mixed
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        if (isset($this->shortcodeAttributes[$name])) {
            return $this->shortcodeAttributes[$name];
        }
        return $default;
    }

    /**
     * Возвращает атрибуты шорткодов.
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->shortcodeAttributes;
    }
}
