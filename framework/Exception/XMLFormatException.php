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
 *  Исключение представлено в виде ошибки при обработке XML.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class XMLFormatException extends FormatException
{
    /**
     * Ошибка XML.
     * 
     * @var mixed
     */
    public mixed $error;

    /**
     * Конструктор класса.
     * 
     * @param mixed $error Ошибка XML.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(mixed $error, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->error = $error;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('XML %s: "%s" at line %s in column %s of file the "%s".',
            $this->getLevelMessage(), $this->error->message, $this->error->line, $this->error->column, $this->error->file
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'XML Format';
    }

    /**
     * Возращает уровень ошибки.
     * 
     * @return string
     */
    protected function getLevelMessage(): string
    {
        switch ($this->error->level) {
            case LIBXML_ERR_WARNING: return 'Warning';
            case LIBXML_ERR_ERROR:   return 'Error';
            case LIBXML_ERR_FATAL:   return 'Fatal error';;
        }
        return 'Error';
    }

    /**
     * Устанавливает ошибку XML.
     * 
     * @param mixed $error Ошибка XML.
     * 
     * @return $this
     */
    public function setError(mixed $error): static
    {
        $this->error = $error;
        return $this;
    }
}
