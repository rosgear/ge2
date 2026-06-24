<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Adapter;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\FilesystemAdapter as LeagueFsAdapter;

/**
 * Адаптер "Memory" менеджера файловой системы Flysystem.
 * 
 * Этот адаптер хранит файловую систему в памяти. Это полезно, 
 * когда вам нужна файловая система, но не нужно, чтобы она сохранялась.
 * 
 * @link https://github.com/thephpleague/flysystem-memory
 * @link https://flysystem.thephpleague.com/v1/docs/adapter/memory/
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Adapter
 * @since 2.0
 */
class MemoryAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function createAdapter(): ?LeagueFsAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}
