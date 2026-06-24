<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem;

use Ge\Exception;
use Ge\Config\Config;
use Ge\Stdlib\Service;
use Ge\Filesystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem as LeagueFs;

/**
 * Менеджер файловой системы для PHP-пакета Flysystem.
 * 
 * FilesystemManager - это служба приложения, доступ к которой можно получить через 
 * `Ge::$app->fileSystem`.
 * 
 * Менеджер содержит простые в использовании адаптеры для работы с локальными файловыми 
 * системами: Amazon S3, Rackspace Cloud Storage, FTP и др.
 * 
 * Под дисками менеджера файловой системы {@see FilesystemManager::$disks} понимают 
 * файловую систему {@see League\Flysystem\Filesystem}. Каждому диску соответствует 
 * своя файловая система Flysystem со своим адаптером подключения, таким как:  Amazon S3, 
 * Rackspace Cloud Storage, FTP и т.д.
 * 
 * 
 * @link https://github.com/thephpleague/flysystem
 * @link https://flysystem.thephpleague.com/v1/docs/
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Filesystem
 * @since 2.0
 */
class FilesystemManager extends Service
{
    /**
     * Адаптеры менеджера файловой системы.
     *
     * @var array<string, string>
     */
    protected array $adapterClasses = [
        'local'  => '\Ge\Filesystem\Adapter\LocalAdapter',
        'zip'    => '\Ge\Filesystem\Adapter\ZipAdapter',
        'memory' => '\Ge\Filesystem\Adapter\MemoryAdapter',
    ];

    /**
     * Конфигуратор.
     *
     * @var Config|null
     */
    public ?Config $config = null;

    /**
     * Диски файловой системы.
     *
     * @var array<string, LeagueFs>
     */
    protected array $disks = [];

    /**
     * Возвращает диск.
     * 
     * @see FilesystemManager::get()
     * 
     * @param null|string $name Имя диска. Если имя не указано, применяется имя диска 
     *     по умолчанию.
     * 
     * @return LeagueFs
     * 
     * @throws Exception\InvalidArgumentException Диск по умолчанию не найден.
     */
    public function disk(?string $name = null): LeagueFs
    {
        $name = $name ?: $this->getDefaultDisk();
        if ($name === null) {
            throw new Exception\InvalidArgumentException('Default disk not found.');
        }
        return $this->get($name);
    }

    /**
     * Создаёт диск с указанной конфигурацией.
     * 
     * @param string $name Имя диска.
     * @param array $config Конфигурация диска.
     * 
     * @return LeagueFs
     * 
     * @throws Exception\InvalidArgumentException Неправильно указана конфигурация диска.
     */
    public function diskWith(string $name, array $config): LeagueFs
    {
        return $this->disks[$name] = $this->createDiskWith($config);
    }

    /**
     * Создаёт диск облака с адаптером облака по умолчанию.
     * 
     * @return LeagueFs
     * 
     * @throws Exception\InvalidArgumentException Диск облака с адаптером облака по умолчанию не указан.
     */
    public function cloud(): LeagueFs
    {
        /** @var string|null $name */
        $name = $this->getDefaultCloudDisk();
        if ($name === null) {
            throw new Exception\InvalidArgumentException('Default cloud disk not found.');
        }
        return $this->get($name);
    }

   /**
     * Возвращает класс адаптера по указанному имени.
     * 
     * @return string|null Возвращает значение `null`, если класс адаптера не найден.
     */
    public function getAdapterClass(string $name): ?string
    {
        return $this->adapterClasses[$name] ?? null;
    }

   /**
     * Возвращает конфигурацию диска.
     * 
     * @param string $name Имя диска.
     * 
     * @return null|array<string, mixed> Возвращает значение `null`, если конфигурация 
     *     диска не найдена.
     */
    public function getDiskConfig(string $name): ?array
    {
        return $this->config?->disks[$name];
    }

    /**
     * Возвращает имя диска по умолчанию.
     *
     * @return string|null Возвращает значение `null`, если название диска по умолчанию 
     *     не найдено.
     */
    public function getDefaultDisk(): ?string
    {
        return $this->config?->default;
    }

    /**
     * Возвращает имя облака по умолчанию.
     *
     * @return string|null Возвращает значение `null`, если имя облака по умолчанию  
     *     не найдено.
     */
    public function getDefaultCloudDisk(): ?string
    {
        return $this->config?->cloud;
    }

    /**
     * Возвращает диск с указанным именем.
     * 
     * @param string $name Имя диска.
     * 
     * @return LeagueFs
     * 
     * @throws InvalidArgumentException Диску не указана конфигурация при его созданни.
     */
    protected function get(string $name): LeagueFs
    {
        if (isset($this->disks[$name])) {
            return $this->disks[$name];
        }
        return $this->disks[$name] = $this->createDisk($name);
    }

    /**
     * Устанавливает диск с указанным именем.
     * 
     * @param string $name Имя диска.
     * @param LeagueFs $disk Диск.
     * 
     * @return $this
     */
    public function set(string $name, LeagueFs $disk): static
    {
        $this->disks[$name] = $disk;
        return $this;
    }

    /**
     * Удаляет диск или диски с указанным именемами.
     *
     * @param string|array<int, string> $disk Название диска (дисков).
     * 
     * @return $this
     */
    public function removeDisk(string|array $disk): static
    {
        $disk = (array) $disk;
        foreach ($disk as $name) {
            unset($this->disks[$name]);
        }
        return $this;
    }

    /**
     * Создаёт и возвращает адаптер с указанным именем.
     *
     * @param string $name Имя адаптера.
     * @param array $config Конфигурация адаптера.
     * 
     * @return AbstractAdapter
     * 
     * @throws InvalidArgumentException Класс адаптера не существует.
     */
    public function createAdapter(string $name, array $config): AbstractAdapter
    {
        $className = $this->getAdapterClass($name);
        if ($className && class_exists($className)) {
            return $className::factory($config);
        }
        throw new Exception\InvalidArgumentException(sprintf('Adapter class "%s" not exists.', $className));
    }

    /**
     * Создаёт диск с указанным именем.
     * 
     * @see FilesystemManager::getDiskConfig()
     * 
     * @param string $name Имя диска.
     * 
     * @return LeagueFs
     * 
     * @throws Exception\InvalidArgumentException Если диск не имеет конфигурацию.
     */
    public function createDisk(string $name): LeagueFs
    {
        /** @var array|null $config */
        $config = $this->getDiskConfig($name);
        // если нет конфигурации
        if (empty($config) || empty($config['adapter'])) {
            throw new Exception\InvalidArgumentException(
                sprintf('The disk "%s" does not have a configuration.', $name)
            );
        }
        return $this->createAdapter($config['adapter'], $config)->getDriver();
    }

    /**
     * Создаёт диск с указанной конфигурацией.
     *
     * @param array $config Конфигурация диска.
     * 
     * @return LeagueFs
     * 
     * @throws InvalidArgumentException Если нет конфигурации.
     */
    public function createDiskWith(array $config): LeagueFs
    {
        // если нет конфигурации
        if (empty($config) || empty($config['adapter'])) {
            throw new Exception\InvalidArgumentException('The configuration does not have option "adapter".');
        }
        return $this->createAdapter($config['adapter'], $config)->getDriver();
    }

    /**
     * Вызывает метод диска (по умолчанию).
     * 
     * @see FilesystemManager::disk()
     * 
     * @param string $method Имя метода.
     * @param array $parameters Аргументы.
     * 
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
