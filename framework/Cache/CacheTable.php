<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache;

use Ge;
use Ge\Exception;
use Ge\Helper\Arr;
use Ge\Stdlib\Service;
use Ge\Stdlib\Collection;
use Ge\Cache\HashTable;

/**
 * CacheTable
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache
 * @since 2.0
 */
class CacheTable extends Service
{
    /**
     * {@inheritdoc}
     */
    public $enabled;

    /**
     * $patterns
     * 
     * Устанавливается в конфигурации службы.
     * 
     * @var array|Collection
     */
    public $patterns;

    /**
     * Адаптер подключения к серверу базы данных.
     * 
     * @var \Ge\Db\Adapter\Adapter
     */
    public $db;

    /**
     * Префикс добавляемый к каждому ключу кэша, чтобы он была уникальной 
     * во всем хранилище кэша.
     * 
     * Пример: 'dhash:'.
     * 
     * @var string
     */
    public $keyPrefix;

    /**
     * $name
     * 
     * @var string
     */
    protected $name;

    /**
     * $pattern
     * 
     * @var array
     */
    protected $pattern;

    /**
     * $error
     * 
     * @var string
     */
    protected $error;

    /**
     * $hash
     * 
     * @var HashTable
     */
    protected $hash;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        // адаптер подключения к серверу базы данных
        if ($this->db === null) {
            $this->db = Ge::$app->db;
        }
        // служба кэш-таблиц включена в том случаи если включена служба кэширования
        if ($this->enabled === null) {
            $cache = Ge::$app->cache;
            $this->enabled = $cache->enabled ? $cache->tableCaching : false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->initPatterns();
    }

    /**
     * initPatterns
     * 
     * @return void
     */
    public function initPatterns()
    {
        foreach ($this->patterns as &$item) {
            if (isset($item['table']) && isset($item['query'])) {
                throw new Exception\InvalidArgumentException('Collection parameters "table" or "query" specified incorrectly.');
            }
            $item = array_merge([
                'marker'  => '',
                'expiry'  => 0,
                'hashing' => false
            ], $item);
        }
        $this->patterns = Collection::createInstance($this->patterns);
    }

    public function getHash()
    {
        if ($this->enabled && $this->hash === null) {
            $this->hash = new HashTable(null, Ge::$app->cache);
            if ($this->keyPrefix) {
                $this->hash->keyPrefix = $this->keyPrefix;
            }
        }
        return $this->hash;
    }

    /**
     * searchTable
     * 
     * @param string $tableName
     * 
     * @return array|null
     */
    public function searchTable(string $tableName): ?array
    {
        return $this->patterns->subSearch('table', $tableName);
    }

    /**
     * searchName
     * 
     * @param string $tableName
     * 
     * @return string|null
     */
    public function searchName(string $tableName): ?string
    {
        $search = $this->patterns->subSearch('table', $tableName);
        return $search ? $search['key'] : null;
    }

    /**
     * name
     * 
     * @param string $name
     * 
     * @return CacheTable
     */
    public function name(string $name): self
    {
        $pattern = $this->patterns->get($name);
        if ($pattern === null) {
            throw new Exception\InvalidArgumentException(
                sprintf('The specified name "%s" is not in the patterns.', $name)
            );
        }
        $this->name    = $name;
        $this->pattern = $pattern;
        //
        if ($hash = $this->getHash()) {
            //
            if (isset($pattern['table'])) {
                $hash->name([$pattern['table'], $pattern['marker']]);
            } else {
                $hash->name([$pattern['marker'] ?? md5($pattern['query']), '']);
            }
            $hash
                ->useArray(!($pattern['hashing']))
                ->expiry($pattern['expiry']);
        }
        return $this;
    }

    public function pattern(string $name): bool
    {
        if ($this->patterns->has($name)) {
            $this->name($name);
            return true;
        }
        return false;
    }

    public function hasPattern(string $name): bool
    {
        return $this->patterns->has($name);
    }

    public function setPattern(string $name, array $pattern): self
    {
        $this->patterns->set($name, $pattern);
        return $this;
    }

    public function addPattern(string $name, array $pattern, bool $active = false): bool
    {
        if ($this->patterns->has($name)) {
            return false;
        } else {
            // проверка шаблона
            if (!isset($pattern['expiry'])) {
                $pattern['expiry'] = 0;
            }
            $this->patterns->set($name, $pattern);
            if ($active) {
                $this->name($name);
            }
            return true;
        }
    }

    /**
     * caching
     * 
     * @param bool $value
     * 
     * @return CacheTable
     */
    public function caching(bool $value): self
    {
        $this->enabled = $value;
        return $this;
    }

    /**
     * caching
     * 
     * @return bool
     */
    public function isCaching(): bool
    {
        return $this->enabled;
    }

    /**
     * setError
     * 
     * @param string $error
     * 
     * @return CacheTable
     */
    public function setError(string $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * getError
     * 
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * hasError
     * 
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * getCacheKeys
     * 
     * @param int $limit
     * 
     * @return array
     */
    public function getCacheKeys(int $limit = 0): array
    {
        $hash = $this->getHash();
        return $hash ? $hash->getHashKeys('*', $limit) : [];
    }

    /**
     * Проверяет, создана ли ранее кэш-таблица.
     * 
     * Результат зависит от доступности службы {@see CacheTable::$enabled}. Если служба 
     * не доступна, результат `false`.
     * 
     * @see HashTable::has()
     * 
     * @return bool Если `false`, кэш-таблица отсутствует в кэше, ключ {@see CacheTable::$name} 
     *     для неё не установлен или службы не доступна.
     */
    public function exists(): bool
    {
        $hash = $this->getHash();
        return $hash? $hash->has() : false;
    }

    /**
     * flushCache
     * 
     * @return bool
     */
    public function flushCache(): bool
    {
        $hash = $this->getHash();
        return $hash ? $hash->flush() : false;
    }

    /**
     * flushAllCache
     * 
     * @param int $limit
     * 
     * @return bool
     */
    public function flushAllCache(int $limit = 0): bool
    {
        $hash = $this->getHash();
        return $hash ? $hash->flushAll($limit) : false;
    }

    /**
     * fill
     * 
     * @param bool $check
     * 
     * @return CacheTable
     */
    public function fill(bool $check = false): self
    {
        $hash = $this->getHash();
        if ($hash) {
            if ($check) {
                if (!$hash->has()) {
                    $hash->set($this->fetchAll($this->pattern));
                }
            } else {
                $hash->set($this->fetchAll($this->pattern));
            }
        }
        return $this;
    }

    /**
     * getAll
     * 
     * @param bool|null $caching
     * 
     * @return array
     */
    public function getAll(): array
    {
        $hash = $this->getHash();
        if ($hash) {
            $self = $this;
            return $hash->getOrSet(function () use ($self) { 
                return $self->fetchAll($self->pattern); 
            });
        }
        return $this->fetchAll($this->pattern);
    }

    /**
     * setRow
     * 
     * @param array $row
     * 
     * @return bool
     */
    public function setRow($key, $row): bool
    {
        $hash = $this->getHash();
        return $hash ? $hash->setRow($key, $row) : false;
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
        $hash = $this->getHash();
        return $hash ? $hash->setRows($rows) : false;
    }

    /**
     * getRow
     * 
     * @param string|int $key
     * @param bool|null $caching
     * 
     * @return null|array
     */
    public function getRow($key): ?array
    {
        $hash = $this->getHash();
        if ($hash) {
            if (!$hash->has()) {
                $success = $hash->set($this->fetchAll($this->pattern));
                if ($success === false) {
                    $this->setError(sprintf('Unable to set hash table for "%s" in "%s"', $this->name, __METHOD__));
                    return null;
                }
            }
            return $hash->getRow($key);
        }
        return $this->fetchRow($key, $this->pattern);
    }

    /**
     * getRows
     * 
     * @param array $keys
     * @param bool|null $caching
     * 
     * @return array
     */
    public function getRows(array $keys): array
    {
        $hash = $this->getHash();
        if ($hash) {
            if (!$hash->has()) {
                $success = $hash->set($this->fetchAll($this->pattern));
                if ($success === false) {
                    $this->setError(sprintf('Unable to set hash table for "%s" in "%s"', $this->name, __METHOD__));
                    return [];
                }
            }
            return $hash->getRows($keys);
        }
        return $this->fetchRows($keys, $this->pattern);
    }

    /**
     * Обновление строки кэш-таблицы по указанному ключу.
     * 
     * @see CacheTable::refreshRows()
     * 
     * @param string|int $key Ключ строки кэш-таблицы, которую необходимо обновить. 
     * @param bool $checkKeys Проверяет, если указанный ключ ($keys) не возвращен 
     *     запросом {@see CacheTable::fetchRows}, то следует его удалить из кэш-таблицы (по умолчанию `false`).
     * 
     * @return bool Если `false`, сткрока кэш-таблицы не обновлена.
     */
    public function refreshRow($key, bool $checkKeys = false): bool
    {
        return $this->refreshRows([$key], $checkKeys);
    }

    /**
     * Обновление строк кэш-таблицы по указанным ключам.
     * 
     * @param array $keys Массив ключей кэш-таблицы, строки которой необходимо обновить.
     * @param bool $checkKeys Проверяет, если указанные ключи ($keys) не возвращены 
     *     запросом {@see CacheTable::fetchRows}, то следует их удалить из кэш-таблицы (по умолчанию `false`).
     * 
     * @return bool Если `false`, сткроки кэш-таблицы не обновлены.
     */
    public function refreshRows(array $keys, bool $checkKeys = false): bool
    {
        $hash = $this->getHash();
        if ($hash === null) {
            return false;
        }
        $rows = $this->fetchRows($keys, $this->pattern);
        $deleteRows = [];
        foreach ($keys as $key) {
            if (!isset($rows[$key])) {
                $deleteRows[] = $key;
            }
        }
        // если есть, что обновить
        if ($rows) {
            return $hash->setRows($rows);
        }
        // т.к. запрашиваемые записи с указанными ключами могли быть ранее удалены 
        // из базы данных, то удаляем их из кэша
        if ($checkKeys && $deleteRows) {
            $hash->deleteRows($deleteRows);
        }
        return false;
    }

    /**
     * refreshRows
     * 
     * @param array $keys
     * 
     * @return bool
     */
    public function refreshRowsWhere($where): bool
    {
        $hash = $this->getHash();
        if ($hash === null) {
            return false;
        }
        $rows = $this->fetchRowsWhere($this->pattern, $where);
        // если есть что обновить
        if ($rows) {
            return $hash->setRows($rows);
        }
        return false;
    }

    /**
     * getCountRows
     * 
     * @param bool|null $caching
     * 
     * @return int
     */
    public function getCountRows(): int
    {
        $hash = $this->getHash();
        if ($hash) {
            return $hash->getCountRows();
        }
        $rows = $this->fetchAll($this->pattern);
        return sizeof($rows);
    }

    /**
     * deleteRow
     * 
     * @see CacheTable::_deleteRow()
     * 
     * @param int|string $key
     * @param bool|null $inCache
     * 
     * @return void
     */
    public function deleteRow($key)
    {
        $hash = $this->getHash();
        if ($hash)
            $hash->deleteRow($key);
        else
            $this->_deleteRow($key, $this->pattern);
    }

    /**
     * deleteRows
     * 
     * @see CacheTable::_deleteRow()
     * 
     * @param array $keys
     * @param bool|null $inCache
     * 
     * @return void
     */
    public function deleteRows(array $keys)
    {
        $hash = $this->getHash();
        if ($hash)
            $hash->deleteRows($keys);
        else
            $this->_deleteRows($keys, $this->pattern);
    }

    /**
     * _deleteRow
     * 
     * @see CacheTable::_deleteRows()
     * 
     * @param int|string $key
     * @param array $params
     * 
     * @return void
     */
    protected function _deleteRow($key, array $params)
    {
        $this->_deleteRows([$key], $params);
    }

    /**
     * _deleteRows
     * 
     * @param array $keys
     * @param array $params
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException
     */
    protected function _deleteRows(array $keys, array $params)
    {
        if (!isset($params['table'])) {
            throw new Exception\InvalidArgumentException('Can\'t delete row, parameter specified incorrectly.');
        }
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->db->createCommand();
        $command->delete(
            $params['table'], 
            [
                $params['primaryKey'] => $keys
            ]
        );
        $command->query();
    }

    /**
     * fetchAll
     * 
     * @param array $params
     * 
     * @return array
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function fetchAll(array $params): array
    {
        // если указан SQL-запрос
        if (isset($params['query'])) {
            $command = $this->db->createCommand($params['query']);
            $command->query();
            if (isset($params['groupBy']))
                $rows = $command->fetchToGroups($params['groupBy'], $params['primaryKey'] ?? null);
            else
                $rows = $command->fetchAll($params['primaryKey'] ?? null);
            return is_array($rows) ? $rows : [];
        }
        // если указана таблица
        if (isset($params['table'])) {
            $select = $this->db
                ->select($params['table'])
                ->columns($params['columns'] ?? ['*']);
            //
            if ($where = $params['where'] ?? null) {
                $select->where($where);
            }
            if ($order = $params['order'] ?? null) {
                $select->order($order);
            }
            if ($limit = $params['limit'] ?? null) {
                $select->limit($limit);
            }
            if ($offset = $params['offset'] ?? null) {
                $select->offset($offset);
            }
            $command = $this->db->createCommand($select);
            $command->query();
            if (isset($params['groupKey']))
                $rows = $command->fetchToGroups($params['groupKey'], $params['primaryKey'] ?? null);
            else
                $rows = $command->fetchAll($params['primaryKey'] ?? null);
            return is_array($rows) ? $rows : [];
        }
        throw new Exception\InvalidArgumentException('Can\'t fetch all elements, parameter specified incorrectly.');
    }

    /**
     * fetchRow
     * 
     * @param string|int|array $keys
     * @param array $params
     * 
     * @return array
     */
    public function fetchRow($key, array $params): array
    {
        $rows = $this->fetchRows([$key], $params);
        $row = reset($rows);
        return $row ?: [];
    }

    /**
     * fetchRows
     * 
     * @param string|int|array $keys
     * @param array $params
     * 
     * @return array
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function fetchRows(array $keys, array $params): array
    {
        // если указан SQL-запрос
        if (isset($params['query'])) {
            /** @var \Ge\Db\Adapter\Driver\AbstractCommand  $command */
            $command = $this->db->createCommand($params['query']);
            $command->query();
            // если указан параметр 'groupBy' группирования записей
            if (isset($params['groupBy'])) {
                $rows = $command->fetchToGroups($params['groupBy'], $params['primaryKey'] ?? null);
            } else {
                $rows = $command->fetchAll($params['primaryKey'] ?? null);
            }
            if (is_array($rows) && $rows) {
                return Arr::getSomeKeys($keys, $rows);
            }
            return [];
        }
        // если указана таблица
        if (isset($params['table'])) {
            /** @var \Ge\Db\Sql\Select $select */
            $select = $this->db
                ->select($params['table'])
                ->columns($params['columns'] ?? ['*'])
                ->where([$params['primaryKey'] => $keys]);
            // если указан параметр 'where' условия запроса
            if ($where = $params['where'] ?? null) {
                $select->where($where);
            }
            /** @var \Ge\Db\Adapter\Driver\AbstractCommand  $command */
            $command = $this->db->createCommand($select);
            $command->query();
            $rows = $command->fetchAll($params['primaryKey'] ?? null);
            return is_array($rows) ? $rows : [];
        }
        throw new Exception\InvalidArgumentException('Can\'t fetch rows, parameter specified incorrectly.');
    }

    /**
     * fetchRowsWhere
     * 
     * @param array $params
     * @param string|array $where
     * 
     * @return array
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function fetchRowsWhere(array $params, $where): array
    {
        if (!isset($params['table'])) {
            throw new Exception\InvalidArgumentException('Can\'t fetch rows, parameter specified incorrectly.');
        }
        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->db
            ->select($params['table'])
            ->columns($params['columns'] ?? ['*'])
            ->where($where);
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand  $command */
        $command = $this->db->createCommand($select);
        $command->query();
        $rows = $command->fetchAll($params['primaryKey'] ?? null);
        return is_array($rows) ? $rows : [];
    }
}
