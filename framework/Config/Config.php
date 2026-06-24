<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Config;

use Ge\Stdlib\Serializer;

/**
 * Конфигуратор реализуется в классах приложений, модулей и служб для настройки их
 * свойств из файлов конфигурации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Config
 * @since 2.0
 */
class Config extends BaseConfig
{
    /**
     * Название файла конфигурации.
     *
     * @var string|null
     */
    protected ?string $filename;

    /**
     * Использовать сериализацию параметров.
     * 
     * Если значение `true`, параметры будует сохранены в файл '*.so.php'.
     *
     * @var bool
     */
    protected bool $useSerialize = false;

    /**
     * Объект сериализации опций конфигурации.
     *
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * Параметры конфигуратора по умолчанию.
     *
     * @var array
     */
    public array $defaults = [];

    /**
     * Если параметры конфигуратора были загружены из файла.
     *
     * @var bool
     */
    public bool $isLoaded = false;

    /**
     * Конструктор класса.
     * 
     * @param null|string $filename Имя файла конфигурации.
     * @param bool $useSerialize Если значение `true`, использовать сериализацию 
     *     параметров конфигурации.
     * 
     * @return void
     */
    public function __construct(?string $filename = null, bool $useSerialize = false)
    {
        $this->filename = $filename;
        $this->useSerialize = $useSerialize;
        if ($filename !== null) {
            $this->load();
        }
    }

    /**
     * Устанавливает конфигуратору значения параметров.
     * 
     * @param array $params Параметры с их значениями.
     * 
     * @return $this
     */
    public function factoryConfigure(array $params): static
    {
        foreach ($params as $name => $value) {
            // deprecated PHP 8.2 (creation of dynamic property)
            @$this->$name = $value;
        }
        return $this;
    }

    /**
     * Создаёт новый конфигуратор из указанных параметров текущего конфигуратора.
     * 
     * @param mixed $nameOrParameters Название файла конфигурации или параметры конфигуратора.
     * 
     * @return $this
     */
    public function factory(mixed $nameOrParameters): static
    {
        $config = new static();

        if (is_string($nameOrParameters))
            $parameters = $this->get($nameOrParameters);
        else
            $parameters = $nameOrParameters;
        // 
        if (isset($parameters['factory'])) {
            $config->factoryConfigure($parameters['factory']);
            $config->load();
        } else {
            $config->setAll($parameters);
        }
        return $config;
    }

    /**
     * Устанавливает использование сериализации параметров конфигуратора.
     * 
     * @param bool $use Использовать сериализацию параметров конфигуратора.
     * 
     * @return bool
     */
    public function setUseSerialize(bool $use): bool
    {
        return $this->useSerialize = $use;
    }

    /**
     * Устанавливает имя файла конфигурации.
     * 
     * @param string $filename Имя файла конфигурации.
     * 
     * @return string
     */
    public function setFilename(string $filename): string
    {
        return $this->filename = $filename;
    }

    /**
     * Проверяет, используется ли сериализация параметров конфигуратора.
     * 
     * @return bool
     */
    public function isUseSerialize(): bool
    {
        return $this->useSerialize;
    }

    /**
     * Возвращает имя файла конфигурации.
     * 
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Проверяет, была ли сериализация параметров.
     *
     * @return bool
     */
    public function existsSerializer(): bool
    {
        return $this->getSerializer()->exists();
    }

    /**
     * Возвращает объект сериализации параметров конфигуратора.
     * 
     * @param null|string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Config::$filename} (по умолчанию `null`).
     * 
     * @return Serializer
     */
    public function getSerializer(?string $filename = null): Serializer
    {
        if (!isset($this->serializer)) {
            if ($filename === null) {
                $filename = $this->filename;
            }
            return $this->serializer = new Serializer($filename);
        }
        return $this->serializer;
    }

    /**
     * Инициализация после загрузки файла конфигурации.
     * 
     * @return void
     */
    protected function init(): void
    {
    }

    /**
     * Загружает параметры из файла конфигурации.
     * 
     * @param null|string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Config::$filename} (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\FileNotFoundException Файл конфигурации не найден.
     */
    public function load(?string $filename = null): static
    {
        $require = false;
        if ($filename === null) {
            $filename = $this->filename;
        }
        if ($this->useSerialize) {
            $parameters = $this->getSerializer()->load();
            $this->isLoaded = $parameters !== false;
            if ($this->isLoaded)
                $this->setAll($parameters);
            else {
                if (!file_exists($filename)) {
                    throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $filename));
                }
                $require = require($filename);
            }
        } else {
            if (!file_exists($filename)) {
                throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $filename));
            }
            $require = require($filename);
        }
        if ($require) {
            if ($this->defaults) {
                $require = array_merge($this->defaults, $require);
            }
            $this->setAll($require);
        }
        $this->init();
        return $this;
    }

    /**
     * Перезагружает параметры конфигурации.
     *
     * @param bool $useSerialize Если значение `true`, использовать сериализацию 
     *     параметров конфигурации.
     * 
     * @return $this
     * 
     * @throws Exception\FileNotFoundException Файл конфигурации не найден.
     */
    public function reload(bool $useSerialize = false): static
    {
        $require = false;
        if ($useSerialize) {
            $parameters = $this->getSerializer()->load();
            $this->isLoaded = $parameters !== false;
            if ($this->isLoaded)
                $this->setAll($parameters);
            else {
                if (!file_exists($this->filename)) {
                    throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $this->filename));
                }
                $require = require($this->filename);
            }
        } else {
            if (!file_exists($this->filename)) {
                throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $this->filename));
            }
            $require = require($this->filename);
        }
        if ($require) {
            if ($this->defaults) {
                $require = array_merge($this->defaults, $require);
            }
            $this->setAll($require);
        }
        $this->init();
        return $this;
    }

    /**
     * Загружает параметры конфигурации из указанного файла.
     *
     * @param null|string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Config::$filename} (по умолчанию `null`).
     * 
     * @return array
     */
    public function loadConfig(?string $filename = null): array
    {
        if ($filename === null) {
            $filename = $this->filename;
        }
        return require($filename);
    }

    /**
     * Добавляет к параметрам конфигуратора загруженные параметры из указанного файла.
     *
     * @param string $filename Имя файла конфигурации.
     * 
     * @return $this
     */
    public function append(string $filename): static
    {
        if (!file_exists($filename)) {
            throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $filename));
        }

        $require = require($filename);
        $this->merge($require);
        return $this;
    }

    /**
     * Сохраняет параметры конфигуратора.
     * 
     * @param null|string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Config::$filename} (по умолчанию `null`).
     * @param bool $onlyUpdatable Только обновить. Для обновления текущих параметров, 
     *     в файле конфигурации должен быть раздел 'updatable'. В котром будут указаны 
     *     параметры, которые необходимо обновить (по умолчанию `false`).
     * 
     * @return bool
     */
    public function save(?string $filename = null, bool $onlyUpdatable = false): bool
    {
        $parameters = $this->getAll();
        // только параметры разделов которые могут обновлятся "извне"
        if ($onlyUpdatable) {
            $config = $this->loadConfig();
            if ($config) {
                foreach ($config as $key => $value) {
                    if (is_array($value)) {
                        $updatable = isset($value['updatable']) ? $value['updatable'] : false;
                        if (!$updatable)
                            $parameters[$key] = $value;
                    }
                }
            }
        }
        return $this->getSerializer($filename)
            ->save($parameters);
    }
}