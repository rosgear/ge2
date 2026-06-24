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
 * Исключение возникающие при вызове недействительных аргументов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class XMLTagException extends UserException
{
    /**
     * Имя тега.
     * 
     * @var string
     */
    protected string $tagName;

    /**
     * Конструктор класса.
     * 
     * @param string $tagName Имя тега.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $tagName, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->tagName = $tagName;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Invalid tag name or tag "%s" does not exist.', $this->tagName);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'XML Tag exception';
    }
}
