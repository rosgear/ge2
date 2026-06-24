<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session\Storage;

use Ge;
use Ge\Exception;
use Ge\Session\AbstractContainer;
use Ge\Session\Session;

/**
 * Хранилище (место хранения) контейнера сессии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session\Storage
 * @since 2.0
 */
class SessionStorage extends AbstractContainer  implements StorageInterface
{
    /**
     * Конструктор класса.
     *
     * @param null|Session $session Сессия.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Имя, переданное контейнеру, недействительно.
     */
    public function __construct(?Session $session = null)
    {
        $this->setName($this->name);
        $this->setSession($session);
        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function read(): mixed
    {
        return $this->all();
    }

    /**
     * {@inheritdoc}
     * 
     * @return $this
     */
    public function write(mixed $content): static
    {
        $this->setAll($content);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function isInit(): bool
    {
        return Ge::$services->getAs('session')->hasSessionId();
    }
}
