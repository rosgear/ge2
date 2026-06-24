<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage\Adapter;

use Ge\Session\Container as SessionContainer;

/**
 * Параметры адаптера сессии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage\Adapter
 * @since 2.0
 */
class SessionOptions extends AdapterOptions
{
    /**
     * Контейнер сессии
     *
     * @var null|SessionContainer
     */
    protected ?SessionContainer $sessionContainer = null;

    /**
     * Установка контейнера сессии
     *
     * @param null|SessionContainer $sessionContainer
     * 
     * @return $this
     */
    public function setSessionContainer(?SessionContainer $sessionContainer = null): static
    {
        if ($this->sessionContainer != $sessionContainer) {
            $this->sessionContainer = $sessionContainer;
        }
        return $this;
    }

    /**
     * Возвращение контейнера сессии
     *
     * @return null|SessionContainer
     */
    public function getSessionContainer(): ?SessionContainer
    {
        return $this->sessionContainer;
    }
}
