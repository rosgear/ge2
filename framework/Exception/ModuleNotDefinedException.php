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
 * Исключение вызванное обращением к модулю или его объекту.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Exception
 * @since 2.0
 */
class ModuleNotDefinedException extends UserException
{
    /**
     * Идентификатор модуля.
     * 
     * @var string
     */
    public string $moduleId = '';

    /**
     * Конструктор класса.
     * 
     * @param string $moduleId Идентификатор модуля.
     * @param string $message Текст ошибки  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $moduleId, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->moduleId = $moduleId;

        parent::__construct($message, 500, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Cannot call module with ID "%s". Module does not exist or is not installed.', $this->moduleId);
    }

        /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Module error';
    }
}
