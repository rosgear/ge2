<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Uploader;

/**
 * Класс загружаемего файла на сервер.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Uploader
 * @since 2.0
 */
class UploadedEmptyFile extends UploadedFile
{
    /**
     * {@inheritdoc}
     */
    public function defineParams(): void
    {
    }
}
