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
 * Исключение возникающие при удалении файла или директории.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class DeleteException extends UserException
{
    /**
     * Имя файла или директория.
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Конструктор класса.
     * 
     * @param string $path Имя файла или директория (по умолчанию '').
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $path = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error deleting "%s"', $this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Delete exception';
    }
}
