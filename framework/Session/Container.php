<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session;

/**
 * Контейнер - это обёртка для работы с параметром сессии представленного в виде массива 
 * данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session
 * @since 2.0
 */
class Container extends AbstractContainer
{
    /**
     * Конструктор класса.
     *
     * @param string $name Имя контейнера.
     * @param null|Session $session Сессия.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Имя, переданное контейнеру, недействительно.
     */
    public function __construct(string $name = 'Default', ?Session $session = null)
    {
        $this->setName($name);
        $this->setSession($session);
        $this->init();
    }
}
