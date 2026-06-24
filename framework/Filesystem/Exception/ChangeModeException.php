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
 * Исключение возникающие при изменении режима доступа к файлу.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class ChangeModeException extends UserException
{
    /**
     * Путь к файлу.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Режим доступа.
     * 
     * @var int
     */
    public int $mode = 0;

    /**
     * Конструктор класса.
     * 
     * @param string $filename Путь к файлу (по умолчанию '').
     * @param int $mode Режим доступа (по умолчанию 0).
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $filename = '', int $mode = 0, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;
        $this->mode = $mode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error trying to change mode for "%s" on "%s"', $this->filename, (string) $this->mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Change mode exception';
    }
}
