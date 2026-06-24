<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Exception;

use Throwable;
use Ge\Exception\HttpException;

/**
 * Исключение при работе драйвера адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Exception
 * @since 2.0
 */
class CommandException extends HttpException
{
    /**
     * {@inheritdoc}
     */
    public string $viewFile = '//errors/database';

    /**
     * {@inheritdoc}
     */
    public string $error = '';

    /**
     * SQL запрос к серверу базы данных.
     *
     * @var string
     */
    public string $sql = '';

    /**
     * Конструктор класса.
     * 
     * @param string $message Текст ошибки (по умолчанию '').
     * @param string $error Ошибки  (по умолчанию '').
     * @param string $sql SQL запрос  (по умолчанию '').
     * @param int $code Код ошибки  (по умолчанию 0).
     * @param Throwable|null $previous Предыдущие исключение (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $message = '', string $error = '', string $sql = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->error = $error;
        $this->sql   = $sql;

        parent::__construct(503, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return GE_MODE_DEV 
            ? sprintf('Error "%s" performing query "%s"', $this->error ?: 'unknow', $this->sql ?: 'unknow') 
            : 'Error executing database query.';
    }
}
