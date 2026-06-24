<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache;

use Ge\Helper\Arr;

/**
 * HashTable
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache
 * @since 2.0
 */
class HashTable
{
   /**
     * Адаптер кэширования.
     *
     * @var \Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    protected $adapter;

    /**
     * $cache
     * 
     * @var Cache
     */
    protected Cache $cache;

    /**
     * $expiry
     * 
     * @var int
     */
    protected int $expiry;

    /**
     * $name
     * 
     * @var string|array
     */
    protected string|array $name = '';

    /**
     * $key
     * 
     * @var string
     */
    protected string $key = '';

    /**
     * $useArray
     * 
     * @var bool
     */
    protected bool $useArray = false;

    /**
     * Префикс добавляемый к каждому ключу кэша, чтобы он была уникальной 
     * во всем хранилище кэша.
     * 
     * @var string
     */
    public string $keyPrefix = 'hash:';

    /**
     * Конструктор класса.
     * 
     * @param array|string $name
     * @param Cache $cache
     * 
     * @return void
     */
    public function __construct(array|string $name = '', Cache $cache)
    {
        $this->cache   = $cache;
        $this->adapter = $cache->adapter();
        $this->name($name);
        $this->expiry($cache->defaultExpiry);
    }

    /**
     * JSONrowsToArray
     * 
     * @param array $rows
     * 
     * @return array
     */
    public function JSONrowsToArray(array $rows): array
    {
        $arr = [];
        foreach ($rows as $id => $row) {
            if (is_string($row)) {
                $arr[$id] = json_decode($row, true);
            }
        }
        return $arr;
    }

    /**
     * arrayToJSONrows
     * 
     * @param mixed $arr
     * 
     * @return array
     */
    public function arrayToJSONrows(array $arr): array
    {
        $rows = [];
        foreach ($arr as $id => $row) {
            $rows[$id] = json_encode($row);
        }
        return $rows;
    }

    /**
     * name
     *
     * @param string $name
     * 
     * @return $this
     */
    public function name(array|string $name): self
    {
        if (is_array($name))
            $this->name   = [
                $name[0] ?? '',
                $name[1] ?? ''
            ];
        else
        if (is_string($name))
            $this->name = [$name, ''];

        $this->key = $this->buildHashKey($this->name);
        return $this;
    }

    /**
     * expiresAfter
     * 
     * @param int $time
     * 
     * @return HashTable
     */
    public function expiry(int $time): self
    {
        $this->expiry = $time;
        return $this;
    }

    /**
     * useArray
     * 
     * @param bool $value
     * 
     * @return HashTable
     */
    public function useArray(bool $value = true): self
    {
        $this->useArray = $value;
        return $this;
    }

    /**
     * has
     * 
     * @return bool
     */
    public function has(): bool
    {
        return $this->adapter->hasItem($this->key);
    }

    /**
     * getOrSet
     * 
     * @param callable $callback
     * 
     * @return array
     */
    public function getOrSet(callable $callback): array
    {
        $adapter = $this->adapter;
        if ($this->useArray) {
            return $adapter->getOrSet($this->key, $callback, $this->expiry);
        } else {
            if ($adapter->hasItem($this->key)) {
                $rows = $adapter->hashGetAll($this->key);
                return $rows ? $this->JSONrowsToArray($rows) : [];
            } else {
                $values = $callback();
                if (!is_array($values)) {
                    $values = [];
                }
                $adapter->hashMultiSet($this->key, $values ? $this->arrayToJSONrows($values) : []);
                return $values;
            }
        }
    }

    /**
     * getAll
     * 
     * @return array
     */
    public function getAll(): array
    {
        if ($this->useArray) {
            $rows = $this->adapter->getItem($this->key)->get();
            return is_array($rows) ? $rows : [];
        } else {
            $rows = $this->adapter->hashGetAll($this->key);
            return $rows ? $this->JSONrowsToArray($rows) : [];
        }
    }

    /**
     * getRow
     * 
     * @param string|int $key
     * 
     * @return null|array
     */
    public function getRow($key): ?array
    {
        if ($this->useArray) {
            $value = $this->adapter->getItem($this->key)->get();
            if (is_array($value)) {
                return $value[$key] ?? null;
            }
        } else {
            $value = $this->adapter->hashGet($this->key, $key);
            if (is_string($value) && $value) {
                return json_decode($value, true);
            }
        }
        return null;
    }

    /**
     * getRows
     * 
     * @param array $keys
     * 
     * @return array
     */
    public function getRows(array $keys): array
    {
        if ($this->useArray) {
            $value = $this->adapter->getItem($this->key)->get();
            return is_array($value) && $value ? Arr::getSomeKeys($keys, $value) : [];
        } else {
            $rows = $this->adapter->hashMultiGet($this->key, $keys);
            return $this->JSONrowsToArray($rows);
        }
    }

    /**
     * getRowsKeys
     * 
     * @return array
     */
    public function getRowsKeys(): array
    {
        if ($this->useArray) {
            $value = $this->adapter->getItem($this->key)->get();
            return is_array($value) && $value ? array_keys($value) : [];
        } else {
            return $this->adapter->hashKeys($this->key);
        }
    }

    /**
     * getCountRows
     * 
     * @return int
     */
    public function getCountRows(): int
    {
        if ($this->useArray) {
            $value = $this->adapter->getItem($this->key)->get();
            return is_array($value) ? sizeof($value) : 0;
        } else {
            return $this->adapter->hashLength($this->key);
        }
    }

    /**
     * set
     * 
     * @param array $rows
     * 
     * @return bool
     */
    public function set(array $rows): bool
    {
        if ($this->useArray) {
            $item = $this->adapter->getItem($this->key);
            $item->set($rows);
            if ($this->expiry) {
                $item->expiresAfter($this->expiry);
            }
            return $this->adapter->save($item);
        } else  {
            $this->adapter->deleteItem($this->key);
            $rows = $this->arrayToJSONrows($rows);
            return $this->adapter->hashMultiSet($this->key, $rows);
        }
    }

    /**
     * setRows
     * 
     * @param array $row
     * 
     * @return bool
     */
    public function setRow($key, $row): bool
    {
        if ($this->useArray) {
            $item = $this->adapter->getItem($this->key);
            $value = $item->get();
            if (is_array($value)) {
                $value[$key] = $row;
            } else {
                $value = [$key => $row];
                if ($this->expiry) {
                    $item->expiresAfter($this->expiry);
                }
            }
            $item->set($value);
            return $this->adapter->save($item);
        } else {
            if (is_array($row)) {
                $row = json_encode($row);
            }
            return $this->adapter->hashMultiSet($this->key, [$key => $row]);
        }
    }

    /**
     * setRows
     * 
     * @param array $rows
     * 
     * @return bool
     */
    public function setRows(array $rows): bool
    {
        if ($this->useArray) {
            $item = $this->adapter->getItem($this->key);
            $value = $item->get();
            if (is_array($value)) {
                $item->set(array_merge($value, $rows));
            } else {
                $item->set($rows);
                if ($this->expiry) {
                    $item->expiresAfter($this->expiry);
                }
            }
            return $this->adapter->save($item);
        } else {
            $rows = $this->arrayToJSONrows($rows);
            return $this->adapter->hashMultiSet($this->key, $rows);
        }
    }

    /**
     * hasRow
     * 
     * @param int|string $key
     * 
     * @return bool
     */
    public function hasRow($key): bool
    {
        if ($this->useArray) {
            $hash = $this->adapter->getItem($this->key)->get();
            if (is_array($hash)) {
                return isset($hash[$key]);
            }
            return false;
        } else {
            return $this->adapter->hashExists($this->key, $key);
        }
    }

    /**
     * deleteRow
     * 
     * @param string|int $key
     * 
     * @return int
     */
    public function deleteRow($key): int
    {
        if ($this->useArray) {
            $item = $this->adapter->getItem($this->key);
            $value = $item->get();
            $count = 0;
            if (is_array($value)) {
                if (isset($value[$key])) {
                    unset($value[$key]);
                    $count = 1;
                }
                $item->set($value);
                $this->adapter->save($item);
            }
            return $count;
        } else  {
            return $this->adapter->hashDelete($this->key, [$key]);
        }
    }

    /**
     * deleteRows
     * 
     * @param array $keys
     * 
     * @return int
     */
    public function deleteRows(array $keys): int
    {
        if ($this->useArray) {
            $item = $this->adapter->getItem($this->key);
            $value = $item->get();
            if (is_array($value)) {
                $count = 0;
                foreach ($keys as $key) {
                    if (isset($value[$key])) {
                        $count++;
                        unset($value[$key]);
                    }
                }
                $item->set($value);
                $this->adapter->save($item);
                return $count;
            }
            return 0;
        } else  {
            return $this->adapter->hashDelete($this->key, $keys);
        }
    }

    /**
     * flush
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return $this->adapter->deleteItem($this->key);
    }

    /**
     * flushAll
     * 
     * @param int $limit
     * 
     * @return bool
     */
    public function flushAll(int $limit = 0): bool
    {
        return $this->adapter->deleteItems($this->getHashKeys('*', $limit));
    }

    /**
     * buildHashKey
     * 
     * @param array $name
     * 
     * @return string
     */
    public function buildHashKey(array $name): string
    {
        $marker = $name[1] ?? '';
        $name   = $name[0];
        if ($name === '' || $name === '*') {
        } else {
            $name = strtr($name, ['{{' => '', '}}' => '']);
            if ($marker) {
                $name = "$name:$marker";
            }
        }
        return $this->cache->keyPrefix . $this->keyPrefix . $name;
    }

    /**
     * getHashKeys
     * 
     * @param string $name
     * @param int $limit
     * 
     * @return array
     */
    public function getHashKeys($name = '*', int $limit = 0): array
    {
        $key = $this->buildHashKey((array) $name);
        return $this->adapter->keys($key, $limit);
    }
}
