<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme\Info\Exception;

use Throwable;
use Ge\Exception\UserException;

/**
 * Исключение возникающие при записи в файл.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme\Info\Exception
 * @since 2.0
 */
class FileNotWriteException extends UserException
{
    /**
     * Имя файла.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $filename, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error writing to file "%s"', $this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Write file exception';
    }
}
