<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem\Adapter;

use League\Flysystem\Filesystem as LeagueFs;
use League\Flysystem\FilesystemAdapter as LeagueFsAdapter;

/**
 * Абстрактный класс адаптера менеджера файловой системы Flysystem.
 * 
 * Под драйвером адаптера {@see AbstractAdapter::$driver} понимают файловую система 
 * Flysystem {@see League\Flysystem\Filesystem}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem\Adapter
 * @since 2.0
 */
abstract class AbstractAdapter
{
    /**
     * Сылка на экземпляр класса.
     *
     * @var AbstractAdapter
     */
    protected static $instance = null;

    /**
     * Драйвер.
     * 
     * @see AbstractAdapter::createDriver()
     * 
     * @var LeagueFs
     */
    protected LeagueFs $driver;

    /**
     * Опции конфигурации адаптера.
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Конструктор класса.
     * 
     * @param array $options Опции конфигурации адаптера.
     * 
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->setCreationOptions($options);
        $this->createDriver();
    }

    /**
     * Создание опций конфигурации адаптера.
     * 
     * @param array $options Опции конфигурации адаптера.
     * 
     * @return void
     */
    public function setCreationOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Возвращает указатель на созданный экземпляр класса (если класс не создан, создаст его).
     * 
     * @param array $options Опции конфигурации адаптера.
     * 
     * @return $this
     */
    public static function factory(array $options = []): static
    {
        if (static::$instance === null) {
            static::$instance = new static($options);
        }
        return static::$instance;
    }

    /**
     * Возвращает драйвер.
     * 
     * @return LeagueFs
     */
    public function getDriver(): LeagueFs
    {
        return $this->driver;
    }

    /**
     * Создаёт и возвращает адаптер файловой системы Flysystem.
     * 
     * @return LeagueFsAdapter
     */
    protected function createLeagueFsAdapter(): ?LeagueFsAdapter
    {
        return null;
    }

    /**
     * Создаёт драйвер.
     * 
     * @return LeagueFs
     */
    protected function createDriver(): LeagueFs
    {
        if (!isset($this->driver)) {
            $this->driver = new LeagueFs(
                $this->createLeagueFsAdapter(),
                count($this->options) > 0 ? $this->options : null
            );
        }
        return $this->driver;
    }
}
