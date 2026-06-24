<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem;

use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * Класс-обвертка для SymfonyFinder, позволяющий создавать правила для поиска файлов и 
 * каталогов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem
 * @since 2.0
 */
class Finder extends SymfonyFinder
{
    /**
     * Указывает на то, что поиск был осуществлён.
     * 
     * @var bool
     */
    static protected bool $searched = false;

    /**
     * {@inheritdoc}
     */
    public function in(array|string $dirs): static
    {
        static::$searched = true;

        return parent::in($dirs);
    }

    /**
     * {@inheritdoc}
     */
    public function append(iterable $iterator): static
    {
        static::$searched = true;

        return parent::in($iterator);
    }

    /**
     * Определяет, был ли зайдействован поиск.
     * 
     * @return bool
     */
    static public function isSearched(): bool
    {
        return static::$searched;
    }
}
