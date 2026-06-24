<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Adapter;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\FilesystemAdapter as LeagueFsAdapter;

/**
 * Адаптер "Local" менеджера файловой системы Flysystem. Предназначен для 
 * выполнения операций с файлами и директориями.
 * 
 * Опции конфигурации адаптера:
 * - 'root', имя текущей директории;
 * - 'lock', флаг записи директории или файла;
 * - 'links', добавление линков;
 * - 'permissions', добавление прав доступа на файлы и директории.
 * 
 * @link https://github.com/thephpleague/flysystem-ziparchive
 * @link https://flysystem.thephpleague.com/v1/docs/adapter/zip-archive/
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Adapter
 * @since 2.0
 */
class LocalAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function createLeagueFsAdapter(): ?LeagueFsAdapter
    {
        $links = ($this->options['links'] ?? null) === 'skip'
            ? LocalFilesystemAdapter::SKIP_LINKS
            : LocalFilesystemAdapter::DISALLOW_LINKS;

        return new LocalFilesystemAdapter(
            $this->options['root'],
            $this->options['lock'] ?? LOCK_EX,
            $links,
            $this->options['permissions'] ?? []
        );
    }
}
