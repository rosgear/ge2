<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace  Ge\Api\Response;

use Ge\Stdlib\Collection;

/**
 * Класс метаданных используемых для HTTP-ответа (результат выполнения API) в формате JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Api\Response
 * @since 2.0
 */
class ApiMetadata extends Collection
{
    /**
     * Свойство определяющие возврат контента метаданных.
     * 
     * @var string
     */
    public string $contentProperty = 'data';

    /**
     * Конструктор класса.
     */
    public function __construct()
    {
        $this->container = [
            'success'              => true,
            'message'              => '',
            'status'               => 'OK', // код ошибки, например, 'ERROR' или успех 'OK'
            $this->contentProperty => ''
        ];
    }

    /**
     * Добавляет значение элементу коллекции, превращая элемент в нумерованный массив.
     * 
     * @param mixed $key Ключ элемента коллекции.
     * @param mixed $value Значение элемента.
     * 
     * @return $this
     */
    public function add(string $key, mixed $value): static
    {
        if (!isset($this->container[$key]))
            $this->container[$key] = [$value];
        else {
            // избежать дублирования
            if (!in_array($value, $this->container[$key])) {
                $this->container[$key][] = $value;
            }
        }
        return $this;
    }

    /**
     * Устанавливает сообщение для метаданных.
     * 
     * @param string $message Сообщение.
     * 
     * @return $this
     */
    public function message(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Устанавливает успех ответу.
     * 
     * @param null|string $message Сообщение (по умолчанию `null`).
     * 
     * @return $this
     */
    public function success(?string $message = null): static
    {
        if ($message) {
            $this->message($message);
        }
        $this->success = true;
        return $this;
    }

    /**
     * Устанавливает ответу ошибку.
     * 
     * @param null|string $message Текст ошибки (по умолчанию `null`).
     * @param string $status Состояние ответа (код ошибки, например 'NOT_FOUND')
     * 
     * @return $this
     */
    public function error(?string $message = null, string $status = ''): static
    {
        if ($message) {
            $this->message($message);
        }
        $this->success = false;
        $this->status = $status;
        return $this;
    }

    /**
     * Проверяет, успешен ли ответ.
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success === true;
    }

    /**
     * Проверяет, была ли установлена ошибка.
     * 
     * @return bool
     */
    public function isError(): bool
    {
        return $this->success === false;
    }

    /**
     * Устанавливает контент для HTTP-ответа.
     * 
     * @param string|array $content Контент.
     * 
     * @return $this
     */
    public function content(string|array $content): static
    {
        $this->container[$this->contentProperty] = $content;
        return $this;
    }

    /**
     * Возвращает текст ошибки если она была.
     * 
     * @return null|string Если `null`, ошибки не было. Иначе, текст ошибки.
     */
    public function getMsgError(): ?string
    {
        return !$this->isSuccess() ? $this->message : null;
    }

    /**
     * Конвертирует метаданные в строку.
     * 
     * @return string|bool Если `false`, ошибка конвертирования.
     */
    public function toString(): false|string
    {
        return $this->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
