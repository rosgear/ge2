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
 * Исключение возникающие при создании директории.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class MakeDirectoryException extends UserException
{
    /**
     * Директория.
     * 
     * @var string
     */
    public string $directory = '';

    /**
     * Конструктор класса.
     * 
     * @param string $directory Директория (по умолчанию '').
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $directory = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->directory = $directory;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error creating directory "%s"', $this->directory);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Make directory exception';
    }
}
