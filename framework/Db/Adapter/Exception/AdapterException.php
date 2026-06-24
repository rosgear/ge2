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
 * Исключение при работе адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Exception
 * @since 2.0
 */
class AdapterException extends HttpException
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
     * {@inheritdoc}
     * 
     * @param string $error Текст ошибки (по умолчанию '').
     */
    public function __construct(string $message = '', string $error = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->error = $error;
        if (is_string($error)) {
            $message .= ' (' . $error . ').';
        }

        parent::__construct(503, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Database adapter error "%s"', $this->error ?: 'unknow');
    }
}