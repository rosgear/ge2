<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

/**
 * Трейт обработки ошибок объекта.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
trait ErrorTrait
{
    /**
     * Массив сообщение об ошибках.
     * 
     * @var array
     */
    protected array $errors = [];

    /**
     * Код последней ошибки.
     * 
     * @var mixed
     */
    protected mixed $errorCode = null;

    /**
     * Возвращает значение, указывающее, была ли ошибка.
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
 
    /**
     * Удаляет все ошибки из очереди.
     * 
     * @return $this
     */
    public function clearErrors(): static
    {
        $this->errors = [];
        $this->errorCode = null;
        return $this;
    }

    /**
     * Добавляет ошибку в очередь.
     * 
     * @param string $error Сообщение об ошибке.
     * @param mixed $code Код ошибки (по умолчанию `null`).
     * 
     * @return $this
     */
    public function addError(string $error, mixed $code = null): static
    {
        $this->errors[] = $error;
        if ($code) {
            $this->errorCode = $code;
        }
        return $this;
    }

    /**
     * Возвращает сообщение об ошибке из указанного кода.
     * 
     * @param mixed $code Код ошибки.
     * 
     * @return null|string
     */
    public function getErrorFromCode(mixed $code): ?string
    {
        return null;
    }

    /**
     * Получает все ошибки от указанного объекта.
     * 
     * @param mixed $from Отправитель ошибок.
     * 
     * @return void
     */
    public function assignErrors(mixed $from): void
    {
        if ($from !== null) {
            $this->errors = $from->getErrors();
        }
    }

    /**
     * Добавляет сообщение об ошибке в очередь с указанным кодом ошибки.
     * 
     * @param mixed $code Код ошибки определяет сообщение {@see getErrorFromCode()}.
     * @param string $default Значение по умолчанию если отсутствует сообщение по 
     *     указанному коду.
     * 
     * @return $this
     */
    public function addErrorByCode(mixed $code, string $default = 'Unknown error'): static
    {
        $error = $this->getErrorFromCode($code);
        $this->errors[] = $error ?: $default;
        return $this;
    }

    /**
     * Устанавливает сообщение об ошибке в очередь.
     * 
     * @param string $error Сообщение об ошибке.
     * @param mixed $code Код ошибки (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setError(string $error, mixed $code = null): static
    {
        $this->errors[0] = $error;
        if ($code) {
            $this->errorCode = $code;
        }
        return $this;
    }

    /**
     * Возвращает первое сообщение об ошибке из очереди.
     * 
     * @return string Сообщение об ошибке.
     */
    public function getError(): string
    {
        return $this->getErrors(0);
    }

    /**
     * Устанавливает код ошибки.
     * 
     * @param mixed $code Код ошибки.
     * 
     * @return void
     */
    public function setErrorCode(mixed $code): void
    {
        $this->errorCode = $code;
    }

    /**
     * Возвращает последний код ошибки.
     * 
     * @return mixed
     */
    public function getErrorCode(): mixed
    {
        return $this->errorCode;
    }

    /**
     * Возвращает сообщение (ия) об ошибке из очереди.
     * 
     * @param null|int $index Порядковый номер сообщения об ошибке в очереде (по умолчанию `null`).
     * 
     * @return array|string Если порядковый номер неверно указан, возвратит ''.
     */
    public function getErrors(?int $index = null): array|string
    {
        if ($index === null) {
            return $this->errors;
        }
        return $this->errors[$index] ?? '';
    }
}
