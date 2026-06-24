<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Exception;

use Throwable;

/**
 * Исключение возникающие при появлении ошибки PHP.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class ErrorException extends \ErrorException implements ExceptionInterface
{
    /**
     * Конструктор класса.
     * 
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки (по умолчанию '0').
     * @param int $severity Строгость исключения.
     * @param string|null $filename Файл, в котором возникло исключение (по умолчанию `null`).
     * @param int|null $line Cтрока, в которой возникло исключение (по умолчанию `null`).
     * @param Throwable|null $previous Предыдущие исключение.
     * 
     * @return void
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        int $severity = E_ERROR,
        ?string $filename = null,
        ?int $line = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatch(): string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainDispatch(): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'Error';
    }


    /**
     * Возвращает, если ошибка фатального типа.
     *
     * @param array|null $error Ошибка получена из `error_get_last()`.
     * 
     * @return bool Если значение `true`, то ошибка фатального типа.
     */
    public static function isFatalError(?array $error): bool
    {
        if ($error === null) {
            return false;
        }
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
    }
}
