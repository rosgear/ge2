<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data\Model\Exception;

use Ge\Exception\UserException;

/**
 * Исключения возникающие при отсутствии записей (элементов).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model\Exception
 * @since 2.0
 */
class EntriesNotFoundException extends UserException
{
    /**
     * {@inheritdoc}
     */
    public bool $report = false;

    /**
     * {@inheritdoc}
     */
    public function getMessageBox(): array
    {
        return [
            'icon'   => 'icon-warning',
            'status' => '#Warning',
            'text'   => $this->message
        ];
    }
}
