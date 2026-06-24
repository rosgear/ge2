<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage\Adapter;

use Ge\Cache\Exception;
use Ge\Cache\Storage\StorageInterface;
use Ge\Stdlib\AbstractOptions;

/**
 * Параметры адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage\Adapter
 * @since 2.0
 */
class AdapterOptions extends AbstractOptions
{
    /**
     * Адаптер, использующий эти параметры
     *
     * @var null|StorageInterface
     */
    protected $adapter;

    /**
     * Проверить ключ на шаблон
     *
     * @var string
     */
    protected string $keyPattern = '';

    /**
     * Опция пространства имен
     *
     * @var string
     */
    protected string $namespace = 'gcache';

    /**
     * Опция чтения
     *
     * @var bool
     */
    protected bool $readable = true;

    /**
     * TTL опция
     *
     * @var int|float 0 means infinite or maximum of adapter
     */
    protected $ttl = 0;

    /**
     * Опция записи
     *
     * @var bool
     */
    protected bool $writable = true;

    /**
     * Адаптер с использованием этого экземпляра.
     *
     * @param StorageInterface|null $adapter
     * 
     * @return $this
     */
    public function setAdapter(?StorageInterface $adapter = null): static
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Устанавливает шаблон ключа.
     *
     * @param null|string $keyPattern Шаблон ключ.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function setKeyPattern($keyPattern): static
    {
        $keyPattern = (string) $keyPattern;
        if ($this->keyPattern !== $keyPattern) {
            // validate pattern
            if ($keyPattern !== '') {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid pattern "%s"',
                    $keyPattern
                ));
            }
            $this->keyPattern = $keyPattern;
        }
        return $this;
    }

    /**
     * Возвращает шаблон ключа.
     *
     * @return string
     */
    public function getKeyPattern(): string
    {
        return $this->keyPattern;
    }

    /**
     * Установка namespace
     *
     * @param string $namespace
     * 
     * @return $this
     */
    public function setNamespace(string $namespace): static
    {
        $namespace = (string) $namespace;
        if ($this->namespace !== $namespace) {
            $this->namespace = $namespace;
        }
        return $this;
    }

    /**
     * Возвращение namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Установка на чтение.
     *
     * @param bool $readable Чтение.
     * 
     * @return $this
     */
    public function setReadable(bool $readable): static
    {
        if ($this->readable !== $readable) {
            $this->readable = $readable;
        }
        return $this;
    }

    /**
     * Возвращение доступа не чтение из кэша.
     *
     * @return bool
     */
    public function getReadable(): bool
    {
        return $this->readable;
    }

    /**
     * Установка опции ttl.
     *
     * @param int|float $ttl
     * 
     * @return AdapterOptions
     */
    public function setTtl($ttl)
    {
        $this->normalizeTtl($ttl);
        if ($this->ttl !== $ttl) {
            $this->ttl = $ttl;
        }
        return $this;
    }

    /**
     * Возвращение опции ttl
     *
     * @return float
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Установка режима записи в кэш
     *
     * @param bool $writable на запись.
     * 
     * @return AdapterOptions
     */
    public function setWritable($writable)
    {
        $writable = (bool) $writable;
        if ($this->writable !== $writable) {
            $this->writable = $writable;
        }
        return $this;
    }

    /**
     * Проверка на запись данных в кэш
     *
     * @return bool
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Валидация и нормализация TTL.
     *
     * @param  int|float $ttl
     * @return void
     * 
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeTtl(&$ttl)
    {
        if (!is_int($ttl)) {
            $ttl = (float) $ttl;

            if ($ttl === (float) (int) $ttl) {
                $ttl = (int) $ttl;
            }
        }

        if ($ttl < 0) {
            throw new Exception\InvalidArgumentException("TTL can't be negative");
        }
    }
}
