<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ExtensionManager\Exception;

use Ge\Exception\UserException;

/**
 * Исключение возникающие при удалении модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager\Exception
 * @since 2.0
 */
class UninstallException extends UserException
{
    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return 'Uninstalling module.';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Uninstalling module';
    }
}
