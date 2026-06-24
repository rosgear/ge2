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
 * Исключение возникающие при попытки переименовать oldname в newname (файла, директории).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Exception
 * @since 2.0
 */
class RenameException extends UserException
{
    /**
     * Старое название (файла, директории).
     * 
     * @var string
     */
    public string $oldName = '';

    /**
     * Новое название (файла, директории).
     * 
     * @var string
     */
    public string $newName = '';

    /**
     * Конструктор класса.
     * 
     * @param string $oldName Старое название (файла, директории).
     * @param string $newName Новое название (файла, директории).
     * @param string $message Текст ошибки (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $oldName, string $newName, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->oldName = $oldName;
        $this->newName = $newName;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Error trying to rename "%s" to "%s"', $this->oldName, $this->newName);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Rename exception';
    }
}
