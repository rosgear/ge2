<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Exception;

use Throwable;
use Ge\Exception\UserException;

/**
 * Исключение возникающие при копировании файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class FileCopyException extends UserException
{
    /**
     * Путь к исходному файлу.
     * 
     * @var string
     */
    public string $source = '';

    /**
     * Путь к целевому файлу.
     * 
     * @var string
     */
    public string $dest = '';

    /**
     * Конструктор класса.
     * 
     * @param string $source Путь к исходному файлу (по умолчанию '').
     * @param string $dest Путь к целевому файлу (по умолчанию '').
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $source = '', string $dest = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->source = $source;
        $this->dest = $dest;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error trying to copy "%s" to "%s"', $this->source, $this->dest);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Copy exception';
    }
}
