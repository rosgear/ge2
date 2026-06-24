<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log;

use Ge\ServiceManager\AbstractManager;

/**
 * Класс менеджера служб Логгера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log
 * @since 2.0
 */
class LoggerManager extends AbstractManager
{
    /**
     * {@inheritdoc}
     */
    protected array $invokableClasses = [
        'xml'     => 'Ge\Log\Writer\XmlWriter',
        'file'    => 'Ge\Log\Writer\FileWriter',
        'message' => 'Ge\Log\Writer\MessageWriter',
        'error'   => 'Ge\Log\Writer\ErrorWriter',
        'debug'   => 'Ge\Log\Writer\DebugWriter',
    ];
}
