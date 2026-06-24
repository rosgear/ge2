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
use Ge\Encryption\Exception;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * Коллекция поддерживает cookie, доступные в текущем запросе.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class Cookie extends SymfonyCookie
{
    /**
     * Создаёт cookie с указанной конфигурацией.
     *
     * @param array $config Параметры конфигурации.
     * 
     * @return Cookie
     */
    public static function createWith(array $config): Cookie
    {
        return new self(
            $config['name'],
            $config['value'] ?? null,
            $config['expire'] ?? 0,
            $config['path'] ?? '/',
            $config['domain'] ?? null,
            $config['secure'] ?? null,
            $config['httpOnly'] ?? true,
            $config['raw'] ?? false,
            $config['sameSite'] ?? self::SAMESITE_LAX
        );
    }

    /**
     * Устанавливает имя cookie.
     * 
     * @param string $name Имя cookie.
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Устанавливает значение cookie.
     * 
     * @param string $value Значение cookie.
     * 
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Устанавливает домен для cookie.
     * 
     * @param string|null $domain Домен.
     * 
     * @return void
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Установить время истечения срока действия cookie.
     * 
     * @param int|string|\DateTimeInterface $expire Время истечения срока действия cookie.
     * 
     * @return void
     */
    public function setExpire($expire): void
    {
        $this->expire = $expire;
    }

    /**
     * Указывает путь на сервере, на котором будет доступен cookie.
     * 
     * @param string|null $path Путь на сервере.
     * 
     * @return void
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * Проверяет, следует ли передавать cookie от клиента только через защищенное HTTPS-соединение.
     * 
     * @param bool $secure
     * 
     * @return void
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    /**
     * Проверяет, будет ли файл cookie доступен только по протоколу HTTP.
     * 
     * @param bool $isHttpOnly
     * 
     * @return void
     */
    public function setHttpOnly(bool $isHttpOnly): void
    {
        $this->httpOnly = $isHttpOnly;
    }

    /**
     * Устанавливает, следует ли отправлять значение cookie без кодировки URL.
     *
     * @param bool $raw
     * 
     * @return void
     */
    public function setRaw(bool $raw): void
    {
        $this->raw = $raw;
    }

    /**
     * Будет ли файл cookie доступен для межсайтовых запросов.
     * 
     * @param string $value
     * 
     * @param void
     */
    public function setSameSite(?string $value): void
    {
        $this->sameSite = $value;
    }

    /**
     * Шифровать значение cookie.
     *
     * @param string $key Ключ шифрования.
     * 
     * @return $this
     */
    public function encrypt(string $key): static
    {
        $value = $this->value;
        try {
            $value = Ge::$app->encrypter->encrypt($value, true, $key);
        } catch(Exception\EncryptException $e) { }
        $this->value = $value;
        return $this;
    }

    /**
     * Расшифровать значение cookie.
     *
     * @param string $key Ключ шифрования.
     * 
     * @return $this
     */
    public function decrypt(string $key): static
    {
        $value = $this->value;
        try {
            $value = Ge::$app->encrypter->decrypt($value, true, $key);
        } catch(Exception\DecryptException $e) { }
        $this->value = $value;
        return $this;
    }
}
