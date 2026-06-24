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
 * Исключение возникающие при ошибке создания экземпляр класса.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class NotInstantiableException extends InvalidConfigException
{
    /**
     * Имя класса.
     * 
     * @var string
     */
    public string $className = '';

    /**
     * Конструктор класса.
     * 
     * @param string $className Имя класса.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $className, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->className = $className;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Failed to instantiate component or class "%s".', $this->className);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Not instantiable exception';
    }
}
