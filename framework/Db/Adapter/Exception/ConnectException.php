<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Exception;

/**
 * Исключение при подключении адаптером к серверу базы данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Exception
 * @since 2.0
 */
class ConnectException extends AdapterException
{
    /**
     * {@inheritdoc}
     */
    public string $viewFile = '//errors/database';

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Connection error: when making a connection to the server (%s)', $this->error ?: 'unknow');
    }
}
