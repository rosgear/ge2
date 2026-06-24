<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Exception;

use Ge\Exception;

/**
 * Исключение возникающие при работе Карты SQL-запросов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Exception
 * @since 2.0
 */
class QueriesMapException extends Exception\UserException
{
    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'Queries map exception';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Queries map exception';
    }
}
