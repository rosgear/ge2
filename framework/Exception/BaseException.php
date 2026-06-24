<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Exception;

/**
 * Базовый класс исключения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class BaseException extends \Exception implements ExceptionInterface
{
    /**
     * Формат HTTP-ответа.
     * 
     * Если формат указан, он будет установлен HTTP-ответу (даже если он уже имеет 
     * установленный формат). Формат устанавливается обработчиком ошибок {@see \Ge\ErrorHandler\ErrorHandler} 
     * во время рендера исключения {@see \Ge\ErrorHandler\WebErrorHandler::_renderException()}.
     * 
     * Значение должно соответствовать формату [FORMAT_RAW, FORMAT_HTML...] HTTP-ответа {@see \Ge\Http\Response}.
     * 
     * В зависимости от режима работы приложения, можно установить:
     * ```php
     * $responseFormat = GE_MODE_PRO ? 'raw' : null;
     * // или
     * $responseFormat = GE_MODE_PRO ? Response::FORMAT_RAW : null;
     * ```
     * что позволяет отображать отладочную информацию в режиме `development`.
     * 
     * @var null|string
     */
    public ?string $responseFormat = null;

    /**
     * Получает подготовленное сообщение исключения.
     * 
     * @return string Сообщение.
     */
    public function getDispatchMessage(): string
    {
        return '';
    }

    /**
     * Подготавливает и возвращает сообщение исключения для ответа.
     * 
     * Если исключение имеет сообщение {@see \Exception::getMessage()}, возвращает его. 
     * Иначе, подготовленное сообщение исключения {@see BaseException::getDispatchMessage()}.
     * 
     * @see BaseException::getDispatchMessage()
     * 
     * @return string Сообщение.
     */
    public function getDispatch(): string
    {
        if ($this->message) {
            return $this->message;
        }
        return $this->getDispatchMessage();
    }

    /**
     * Подготавливает и возвращает сообщение исключения для ответа без форматирования.
     * 
     * Сообщение исключает использование HTML.
     * 
     * Если исключение имеет сообщение {@see \Exception::getMessage()}, возвращает его. 
     * Иначе, подготовленное сообщение исключения {@see BaseException::getDispatchMessage()}.
     * 
     * @see BaseException::getDispatchMessage()
     * 
     * @return string Сообщение.
     */
    public function getPlainDispatch(): string
    {
        return $this->getDispatch();
    }

    /**
     * Возвращает имя исключения.
     * 
     * @return string Имя исключения.
     */
    public function getName(): string
    {
        return 'Exception';
    }
}
