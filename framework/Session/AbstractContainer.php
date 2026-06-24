<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session;

use Ge;
use Ge\Stdlib\Collection;

/**
 * Абстрактный класс контейнера, обёртки для работы с параметром сессии представленного 
 * в виде массива данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session
 * @since 2.0
 */
abstract class AbstractContainer extends Collection
{
    /**
     * Имя контейнера.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * Сессия.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * Инициализация контейнера.
     *
     * @return void
     */
    public function init(): void
    {
        $this->session->open();
        $this->create();
    }

    /**
     * Создаёт контейнер сессии.
     *
     * @return void
     */
    public function create(): void
    {
        if (!$this->session->has($this->name)) {
            $this->session->set($this->name, []);
        }
        $this->container = &$this->session->getFor($this->name);
    }

    /**
     * Удаляет контейнер сессии.
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->container = [];
        $this->session->remove($this->name);
    }

    /**
     * Устанавливает имя контейнера.
     * 
     * @param string $name Имя контейнера.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Имя, переданное контейнеру, недействительно.
     */
    public function setName(string $name): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_\\\\]+$/i', $name)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Name passed to container is invalid; must consist of alphanumerics, backslashes and underscores only')
            );
        }
        $this->name = $name;
    }

    /**
     * Возвращает имя контейнера.
     *
     * @return string Имя контейнера.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает сессию для контейнера.
     *
     * @param null|Session $session Сессия.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Если объект не является экземпляром класса сессии.
     */
    public function setSession(?Session $session = null): void
    {
        if (null === $session) {
            $session = Ge::$services->getAs('session');
            if (!$session instanceof Session) {
                throw new Exception\InvalidArgumentException(
                    Ge::t('app', 'Manager provided is invalid; must implement ManagerInterface')
                );
            }
        }
        $this->session = $session;
    }

    /**
     * Возвращает сессию контейнера.
     *
     * @return Session Сессия контейнера.
     */
    public function getSession(): Session
    {
        return $this->session;
    }
}
