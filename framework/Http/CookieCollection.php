<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http;

use Ge;
use Ge\Helper\Arr;
use Ge\Stdlib\Collection;

/**
 * Коллекция поддерживает cookie, доступные в текущем запросе.
 * 
 * В качестве элементов коллекции используется cookie {@see \Symfony\Component\HttpFoundation\Cookie}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class CookieCollection extends Collection
{
    /**
     * Конструктор класса.
     *
     * @param array $container Контейнер элементов коллекции.
     */
    public function __construct(array $container = [])
    {
        $this->setAll($container);
    }

    /**
     * Добавляет cookie в коллекцию.
     * 
     * Этот метод требуется для интерфейса SPL `\ArrayAccess`.
     * Он вызывается неявно, если использовать `$collection[$name] = $cookie;`.
     * Это эквивалентно {@see CookieCollection::add()}.
     * 
     * @param string $name Имя cookie.
     * @param Cookie|array $cookie Объект cookie или массив параметров cookie, который будет добавлен.
     */
    public function offsetSet(mixed $name, mixed  $cookie): void
    {
        $this->add($cookie);
    }

    /**
     * Проверяет, есть ли cookie с указанным именем.
     * 
     * Если cookie отмечен для удаления из браузера, то вернет `false`.
     * 
     * @see CookieCollection::remove()
     * 
     * @param string $name Имя cookie.
     * 
     * @return bool Если `true`, cookie существует.
     */
    public function has(mixed $name): bool
    {
        if (isset($this->container[$name])) {
            $value  = $this->container[$name]->getValue();
            $expire = $this->container[$name]->getExpiresTime();
            return $value !== '' && $expire === null || $expire === 0 || $expire >= time();
        }
        return false;
    }

    /**
     * Создаёт объект cookie с указанными параметрами.
     * 
     * @see \Symfony\Component\HttpFoundation\Cookie
     * 
     * @param array $config Параметры cookie, где:
     *     1. имя cookie;
     *     2. значение cookie;
     *     3. время истечения срока действия cookie;
     *     4. путь на сервере, на котором cookie будет доступен;
     *     5. домен, для которого доступен файл cookie;
     *     6. должен ли клиент отправлять обратно cookie только через HTTPS или null, чтобы автоматически включить, когда запрос уже использует HTTPS;
     *     7. будет ли cookie доступен только по протоколу HTTP;
     *     8. следует ли отправлять значение cookie без кодировки URL-адреса;
     *     9. будет ли cookie доступен для межсайтовых запросов.
     * 
     * @return Cookie
     */
    public function create($config): Cookie
    {
        if (Arr::isAssoc($config))
            return Cookie::createWith($config);
        else
            return new Cookie(...$config);
    }

     /**
     * {@inheritdoc}
     */
    public function setAll(array $cookies): static
    {
        foreach ($cookies as $key => $value) {
            $this->container[$key] = new Cookie($key, $value, 0);
        }
        return $this;
    }

    /**
     * Добавляет cookie в коллекцию.
     * 
     * @param Cookie|array $cookie Объект cookie или его параметры.
     * 
     * @return $this
     */
    public function add(mixed $cookie): static
    {
        if ($cookie !== null) {
            if (is_array($cookie)) {
                $cookie = $this->create($cookie);
            }
            $this->container[$cookie->getName()] = $cookie;
        }
        return $this;
    }

    /**
     * Удаляет массив cookie.
     * 
     * Если `$removeFromBrowser = true` , cookie будут удалены из браузера и
     * в коллекцию будут добавлены cookie с истекшим сроком действия.
     * 
     * @param Cookie[]|string[] $cookies Массив объектов cookie или их имён.
     * @param bool $removeFromBrowser Если true, удаляет cookie из браузера (по умолчанию true).
     * 
     * @return $this
     */
    public function removeMultiple(array $cookies, bool $removeFromBrowser = true): static
    {
        foreach ($cookies as $cookie) {
            $this->remove($cookie, $removeFromBrowser);
        }
        return $this;
    }

    /**
     * Удаляет cookie.
     * 
     * Если `$removeFromBrowser = true` , cookie будет удален из браузера и
     * в коллекцию будет добавлен cookie с истекшим сроком действия.
     * 
     * @param Cookie|string $cookie Объект cookie или имя удаляемого cookie.
     * @param bool $removeFromBrowser Если true, удаляет cookie из браузера (по умолчанию true).
     * 
     * @return $this
     */
    public function remove(mixed $cookie, bool $removeFromBrowser = true): static
    {
        if ($cookie instanceof Cookie) {
            $cookie->setExpire(1);
            $cookie->setValue('');
        } else {
            $cookie = $this->create([$cookie, '', 1]);
        }
        if ($removeFromBrowser) {
            $this->container[$cookie->getName()] = $cookie;
        } else {
            unset($this->container[$cookie->getName()]);
        }
        return $this;
    }

    /**
     * Шифрует cookie с указанным ключом шифрования.
     * 
     * @param array|string $cookies Имена cookie или `*` (для указания всех cookie).
     * @param string $key Ключ шифрования.
     * 
     * @return void
     */
    public function encrypt(array|string $cookies, string $key): void
    {
        if ($cookies === '*') {
            $cookies = $this->getKeys();
        }
        $cookies   = (array) $cookies;
        $encrypter = Ge::$app->encrypter;
        foreach ($cookies as $cookie) {
            if (isset($this->container[$cookie])) {
                $value = $this->container[$cookie]->getValue();
                $value = $encrypter->encrypt($value, true, $key);
                $this->container[$cookie]->setValue($value);
            }
        }
    }

    /**
     * Расшифровывает cookie указанным ключом шифрования.
     * 
     * @param array|string $cookies Имена cookie или `*` (для указания всех cookie).
     * @param string $key Ключ шифрования.
     * 
     * @return void
     */
    public function decrypt(array|string $cookies, string $key): void
    {
        if ($cookies === '*') {
            foreach ($this->container as $cookie) {
                $cookie->decrypt($key);
            }
        } else {
            $cookies = (array) $cookies;
            foreach ($cookies as $cookie) {
                if (isset($this->container[$cookie])) {
                    $this->container[$cookie]->decrypt($key);
                }
            }
        }
    }
}
