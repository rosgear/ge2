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
use Ge\Stdlib\Service;

/**
 * Абстрактный класс стандарта HTTP сообщения.
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
abstract class AbstractMessage extends Service
{
    /**
     * @var string Версии HTTP-запроса.
     */
    public const VERSION_10 = '1.0';
    public const VERSION_11 = '1.1';

    /**
     * Версия HTTP-запроса.
     * 
     * @var string
     */
    protected string $version = self::VERSION_11;

    /**
     * Загаловки HTTP-запроса.
     * 
     * @see AbstractMessage::setHeaders()
     * 
     * @var Headers
     */
    protected Headers $headers;

    /**
     * Содержимое сообщения.
     * 
     * @var mixed
     */
    protected mixed $content = '';

    /**
     * Содержимое сообщения при получении исключения.
     * 
     * @var string
     */
    protected string $exceptionContent = '';

    /**
     * Замена содержимого сообщения на указанные значения указаничем массива пар 
     * "ключ - значение".
     * 
     * @see AbstractMessage::replaceContent()
     * 
     * @var array
     */
    protected array $replaceWith = [];

    /**
     * Устанавливает версию HTTP запроса.
     *
     * @see AbstractMessage::$version
     * 
     * @param string $version Версия 1.0 или 1.1.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException Версию не поддерживает HTTP.
     */
    public function setVersion(string $version): static
    {
        if ($version != self::VERSION_10 && $version != self::VERSION_11) {
            throw new Exception\InvalidArgumentException(Ge::t('app', 'Not valid or not supported HTTP version: {0}', [$version]));
        }
        $this->version = $version;
        return $this;
    }

    /**
     * Возвращает версию HTTP-запроса.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Устанавливает загаловки сообщению.
     *
     * @param Headers $headers Заголовки.
     * 
     * @return $this
     */
    public function setHeaders(Headers $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Возвращает загаловки сообщения.
     *
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        if (!isset($this->headers)) {
            $this->headers = new Headers();
        }
        return $this->headers;
    }

    /**
     * Устанавливает содержимое сообщения.
     *
     * @param mixed $content Содержимое сообщения.
     * 
     * @return $this
     */
    public function setContent(mixed $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Возвращает содержимое сообщения.
     *
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Добавляет к содержимому сообщения текст.
     * 
     * @param string $text Текст.
     * 
     * @return void
     */
    public function endContent(string $text): void
    {
        if (is_string($this->content)) {
            $this->content .= $text;
        }
    }

    /**
     * Проверяет, содержимое сообщения.
     *
     * @return bool
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Устанавливает параметры замены содержимого сообщения.
     * 
     * Используется для сообщения типа string.
     * 
     * @see AbstractMessage::replaceWith
     * 
     * @param array|string $search Искомые значения для замены.
     * @param array|string $replace Значение замены.
     * 
     * @return $this
     */
    public function setReplaceWith(array|string $search, array|string $replace): static
    {
        $this->replaceWith = [$search, $replace];
        return $this;
    }

    /**
     * Замена содержимого сообщения указанным значением.
     * 
     * Используется для сообщения типа string.
     *
     * @param mixed $content Значение замены. Если значение не имеет тип string, то
     *     замена не будет выполнена. Если значение `null`, то вместо `$content` будет
     *     {@see AbstractMessage::$content} (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function replaceContent(mixed $content = null): mixed
    {
        if ($content === null) {
            $content = $this->content;
        }
        if ($this->replaceWith && is_string($content)) {
            $content = str_replace($this->replaceWith[0], $this->replaceWith[1], $content);
        }
        return $content;
    }

    /**
     * Подготовка содержимого сообщения перед его выводом.
     *
     * @param mixed $content Указатель содержимое сообщения.
     * 
     * @return void
     */
    public function prepareContent(mixed &$content): void
    {
    }

    /**
     * Устанавливает содержимое исключению.
     * 
     * @see AbstractMessage::$exceptionContent
     * 
     * @param mixed $content Cодержимое исключения.
     * 
     * @return void
     */
    public function exceptionContent(mixed $content): void
    {
        $this->exceptionContent = $content;
    }

    /**
     * Преобразовывает содержимого сообщения в строку.
     *
     * @return string
     */
    public function toString(): string
    {
        return '';
    }

    /**
     * Возвращает строковое представление текущего сообщения.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
