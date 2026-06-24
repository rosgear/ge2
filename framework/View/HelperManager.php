<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

use Ge\View\Helper\HelperInterface;

/**
 * Менеджер помощников для модели представления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class HelperManager
{
    /**
     * Псевдонимы классов помощников.
     *
     * @var array
     */
    protected array $invokableClasses = [
        'stylesheet' => 'Ge\View\Helper\Stylesheet',
        'script'     => 'Ge\View\Helper\Script',
        'link'       => 'Ge\View\Helper\Link',
        'meta'       => 'Ge\View\Helper\Meta',
        'openGraph'  => 'Ge\View\Helper\OpenGraph\OpenGraph',
        'favicon'    => 'Ge\View\Helper\Favicon',
        'html'       => 'Ge\View\Helper\Html',
    ];

    /**
     * Помощники.
     * 
     * @var array<string, HelperInterface>
     */
    protected array $helpers = [];

    /**
     * Возвращает названия класса помощника.
     * 
     * @param string $name Название помощника.
     * 
     * @return string|false
     */
    public function getInvokableClass(string $name): string|false
    {
        return isset($this->invokableClasses[$name]) ? $this->invokableClasses[$name] : false;
    }

    /**
     * Возвращает помощника.
     * 
     * @see HelperManager::addHelper()
     * 
     * @param string $name Название помощника.
     * 
     * @return HelperInterface|null Возвращает значение `null`, если класс помощника 
     *     не найден.
     */
    public function getHelper(string $name): ?HelperInterface
    {
        if (isset($this->helpers[$name])) {
            return $this->helpers[$name];
        }
        return $this->addHelper($name);
    }

    /**
     * Создаёт помощника, если он не был создан ранее.
     * 
     * @param string $name Название помощника.
     * 
     * @return HelperInterface|null Возвращает значение `null`, если класс помощника 
     *     не найден.
     */
    public function addHelper(string $name): ?HelperInterface
    {
        $class = $this->getInvokableClass($name);
        if ($class === false) {
            return null;
        }
        return $this->helpers[$name] = new $class();
    }

    /**
     * Удаляет помощника.
     * 
     * @param string $name Название помощника.
     * 
     * @return $this
     */
    public function removeHelper(string $name): static
    {
        if (isset($this->helpers[$name])) {
            unset($this->helpers[$name]);
        }
        return $this;
    }

    /**
     * Проверяет, был ли создан помощник с указанным именем.
     * 
     * @param string $name Название помощника.
     * 
     * @return bool
     */
    public function hasHelper(string $name): bool
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Возвращает помощника.
     * 
     * @see HelperManager::getHelper()
     * 
     * @param string $name Название помощника.
     * 
     * @return HelperInterface|null Возвращает значение `null`, если класс помощника 
     *     не найден.
     */
    public function get(string $name): ?HelperInterface
    {
        return $this->getHelper($name);
    }
}
