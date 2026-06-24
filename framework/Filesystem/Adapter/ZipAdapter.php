<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Adapter;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use League\Flysystem\FilesystemAdapter as LeagueFsAdapter;

/**
 * Адаптер "Zip-архив" менеджера файловой системы Flysystem. Предназначен для 
 * выполнения операций с zip-архивами.
 * 
 * Опции конфигурации адаптера:
 * - 'filename', имя zip-архива.
 * 
 * @link https://github.com/thephpleague/flysystem-ziparchive
 * @link https://flysystem.thephpleague.com/v1/docs/adapter/zip-archive/
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Adapter
 * @since 2.0
 */
class ZipAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function createAdapter(): ?LeagueFsAdapter
    {
        return new ZipArchiveAdapter(
            $this->options['filename']
        );
    }
}
