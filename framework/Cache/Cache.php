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
use Ge\Stdlib\Service;
use Ge\Exception;
use Ge\Helper\Str;

/**
 * Кэш поддерживает различные реализации кэш-хранилища.
 * 
 * Cache - это служба приложения, доступ к которой можно получить через `Ge::$app->cache`.
 * 
 * Кэш-хранилища реализауются с помощью адаптеров, где каждый адаптер представлен в виде 
 * компонента "Symfony".
 * 
 * Элемент данных можно сохранить в кэше, вызвав {@see Cache::set()}, и получить обратно 
 * позже (в том же или другом запросе) от {@see Cache::get()}. В обеих операциях 
 * требуется ключ, идентифицирующий элемент данных. Срок действия (expiration) 
 * также можно указать при вызове {@see Cache::set()}. Если срок действия элемента данных 
 * истекает , кэш не вернет никаких данных.
 *
 * Типичный образец использования кэша выглядит следующим образом:
 * 
 * <?php
 * $value = Ge::$app->cache->get("key");
 * if ($value === null) {
 *     Ge::$app->cache->set("key", "value", $expiration);
 * }
 * ?>
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache
 * @since 2.0
 */
class Cache extends Service
{
    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

   /**
     * Классы адаптеров кэширования в виде пары (имя адаптера / класс).
     *
     * @var array
     */
    protected $adapterClasses = [
        'redis'      => '\Symfony\Component\Cache\Adapter\RedisAdapter',
        'memcached'  => '\Symfony\Component\Cache\Adapter\MemcachedAdapter',
        'filesystem' => '\Symfony\Component\Cache\Adapter\FilesystemAdapter',
    ];

   /**
     * Адаптер кэширования.
     *
     * @var \Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    protected $adapter;

   /**
     * Имя адаптер кэширования по умолчанию.
     * 
     * Устанавливается в конфигурации службы.
     * 
     * @var string
     */
    public $default = '';

   /**
     * Настройки адаптеров кэширования в виде (имя адаптера / параметры).
     * 
     * Устанавливается в конфигурации службы.
     * 
     * @var string
     */
    public $adapters = [];

    /**
     * Префикс добавляемый к каждому ключу кэша, чтобы он была уникальной 
     * во всем хранилище кэша.
     * 
     * Рекомендуется установить уникальный префикс ключа кэша для каждого приложения, 
     * если одно и то же хранилище кэша используется разными приложениями.
     * Для обеспечения совместимости следует использовать только буквенно-цифровые символы.
     * 
     * Устанавливается в конфигурации службы.
     * 
     * @var string
     */
    public $keyPrefix = '';

    /**
     * Продолжительность (cрок действия) по умолчанию в секундах до истечения срока действия записи в кэше.
     * Значение по умолчанию - 0, что означает бесконечность.
     * 
     * Это значение используется {@see Cache::set()}, если продолжительность не указана явно.
     * 
     * Устанавливается в конфигурации службы.
     * 
     * @var int
     */
    public $defaultExpiry = 0;

    /**
     * Кэширования данных классификаторов.
     * 
     * Указывает на возможность применения классификаторами кэширования.
     *
     * @var bool
     */
    public $tableCaching = false;

    /**
     * Контейнер объектов - адаптеров.
     *
     * @var \Symfony\Component\Cache\Adapter\AbstractAdapter[]
     */
    protected $container = [];

    /**
     * Абсолютный путь к файлам кэша.
     * 
     * @var string
     */
    public $path;

    /**
     * @var null|HashTable
     */
    public $hash = null;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'cache';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if ($this->path === null)
            $this->path = Ge::getAlias('@runtime/cache');
        else
            $this->path = Ge::getAlias($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        Ge::setAlias('@cache', $this->path);
    }

    public function getHashTable($name = null)
    {
        if ($this->hash === null) {
            $this->hash = new HashTable($name, $this);
        } else {
            if ($name !== null) {
                $this->hash->name($name);
            }
        }
        return $this->hash;
    }

   /**
     * Возвращает класс адаптера по его имени.
     * 
     * @return string|null Если null имя адаптера не найдено.
     */
    public function getAdapterClass(string $name)
    {
        return $this->adapterClasses[$name] ?? null;
    }

    /**
     * Возвращает имя адаптера по умолчанию.
     *
     * @return string
     */
    public function getDefaultAdapter()
    {
        return $this->default;
    }

    /**
     * Устанавливает имя адаптера по умолчанию.
     *
     * @param string $name Имя адаптера.
     * 
     * @return Cache
     */
    public function setDefaultAdapter(string $name)
    {
        $this->default = $name;
        return $this;
    }

    /**
     * Возращает настройки адаптера кэширования по его имени.
     *
     * @param string $name Имя адаптера.
     * 
     * @return null|array Если null, настройки адаптера не найдены.
     */
    protected function getAdapterConfig(?string $name = null)
    {
        if ($name === null) {
            $name = $this->default;
        }
        return $this->adapters[$name] ?? null;
    }

    public function setAdapterConfig(string $name, array $config = [])
    {
        $this->adapters[$name] = $config;
        return $this;
    }

    /**
     * Создаёт адаптер кэширования из указанного имени.
     *
     * @param string $name Имя адаптера кэширования.
     * @param null|array $config Параметры адаптера.
     * 
     * @return \Symfony\Component\Cache\Adapter\AbstractAdapter
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function createAdapter(string $name, ?array $config = null)
    {
        $config = $config === null ? $this->getAdapterConfig($name) : $config;
        if ($config === null) {
             throw new Exception\InvalidArgumentException(sprintf('Cache adapter "%s" is not defined', $name));
        }
        $class = $this->getAdapterClass($name);
        if ($class === null) {
            throw new Exception\InvalidArgumentException(sprintf('Cache adapter "%s" class is not defined', $name));
        }
        return $class::factory($config);
    }

    /**
     * Возвращает адаптер кэширования. Если адаптер не создан, создаёт его.
     *
     * @param null|string $name Имя адаптера кэширования. Если null, 
     *     имя адаптера по умолчанию {@see Cache::$default}.
     * 
     * @return \Symfony\Component\Cache\Adapter\AbstractAdapter
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function adapter(?string $name = null)
    {
        if ($name === null) {
            $name = $this->default;
        }

        if (!isset($this->container[$name])) {
            $this->container[$name] = $this->createAdapter($name);
        }
        return $this->container[$name];
    }

    public function hasAdapter(string $name): bool
    {
        return isset($this->adapterClasses[$name]);
    }

    /**
     * Сохраняет значение (идентифицированное с ключом) в кэш.
     * 
     * Если кэш уже содержит такой ключ, существующее значение и срок 
     * годности будет заменен на новый соответственно.
     *
     * @param string $key Ключ, идентифицирующий кэшированное значение.
     * @param mixed $value Значение для кэширования.
     * @param null|int $expiration Продолжительность, по умолчанию в секундах до истечения срока 
     *    действия кэша. Если не установлен, используется значение по умолчанию {@see Cache:defaultExpiration}.
     * 
     * @return bool Если true, значение успешно сохранено в кэш.
     */
    public function set(string $key, $value, ?int $expiry = null): bool
    {
        $key  = $this->buildKey($key);
        $item = $this->adapter()->getItem($key);
        $item->set($value);
        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }
        if ($expiry) {
            $item->expiresAfter($expiry);
        }
        return $this->adapter()->save($item);
    }

    /**
     * Сохраняет значения (идентифицированные с ключами) в кэш.
     * 
     * Если кэш уже содержит такие ключи, существующие значения и срок 
     * годности будет заменен на новый соответственно.
     *
     * @param mixed<string, string> $values Список строковых ключей с кэшированными значениями.
     * @param int $expiration Продолжительность (срок действия), по умолчанию в секундах до истечения срока 
     *    действия кэша. Если не установлен, используется значение по умолчанию {@see Cache:defaultExpiry}.
     * 
     * @return bool Если false, одно из значений не сохранено в кэш.
     */
    public function multiSet(array $values, int $expiration = 0): bool
    {
        $adapter = $this->adapter();
        $ok = true;
        foreach ($values as $key => $value) {
            $key  = $this->buildKey($key);
            $item = $adapter->getItem($key);
            $item->set($value, $expiration);
            if (!$adapter->save($item))
                $ok = false;
        }
        return $ok;
    }
/*
    public function setTable($tableName, array $rows, bool $useHash = false, int $expiration = null): bool
    {
        if (!$this->enabled) {
            return false;
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            $cache->deleteItem($key);
            foreach ($rows as $id => $row) {
                $rows[$id] = json_encode($row);
            }
            return $cache->hashMultiSet($key, $rows);
        } else {
            if ($expiration === null) {
                $expiration = $this->defaultExpiry;
            }
            $item = $cache->getItem($key);
            $item->set($rows);
            if ($expiration) {
                $item->expiresAfter($expiration);
            }
            return $cache->save($item);
        }
    }
*/
/*
    public function setTableRows($tableName, array $rows, bool $useHash = false, int $expiration = null)
    {
        if (!$this->enabled) {
            return false;
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            foreach ($rows as $id => $row) {
                $rows[$id] = json_encode($row);
            }
            return $cache->hashMultiSet($key, $rows);
        } else {
            if ($expiration === null) {
                $expiration = $this->defaultExpiry;
            }
            $item = $cache->getItem($key);
            $value = $item->get();
            if (is_array($value)) {
                foreach ($rows as $id => $row) {
                    $value[$id] = $row;
                }
                $item->set($value);
            } else {
                $item->set($rows);
                if ($expiration) {
                    $item->expiresAfter($expiration);
                }
            }
            return $cache->save($item);
        }
    }
*/
    /**
     * Извлекает значение из кэша с указанным ключом.
     * 
     * @param string $key Ключ, идентифицирующий кэшированное значение.
     * 
     * @return mixed Значение хранящееся в кэше. Если false, значение нет в кэше или время истекло.
     */
    public function get(string $key)
    {
        $buildKey = $this->buildKey($key);
        return $this->adapter()->getItem($buildKey)->get();
    }

    /**
     * Извлекает несколько значений из кэша с указанными ключами.
     * 
     * Некоторые адаптеры кэша (например, memcache, apc) позволяют извлекать несколько 
     * кэшированных значений одновременно, что может улучшить производительность. 
     * Если кэш не поддерживает эту функцию изначально, этот метод попытается смоделировать ее.
     * 
     * @param string[] $keys Список строковых ключей, идентифицирующих кэшированные значения.
     * 
     * @return array Список кэшированных значений, соответствующих указанным ключам. Массив возвращается 
     * в виде пар (ключ, значение). Если значение не кэшировано или срок его действия не истек, 
     * соответствующее значение массива будет null.
     */
    public function multiGet(...$keys)
    {
        $buildKeys = $this->buildKeys($keys);
        return $this->adapter()->getAdditionItems($buildKeys);
    }
/*
    public function getTable($tableName, bool $useHash = false): array
    {
        if (!$this->enabled) {
            return [];
        }
        $key = $this->buildTableKey($tableName);
        if ($useHash) {
            $hash = $this->adapter()->hashGetAll($key);
            $rows = [];
            if (is_array($hash)) {
                foreach ($hash as $id => $row) {
                    if (is_string($row)) {
                        $rows[$id] = json_decode($row, true);
                    }
                }
            }
        } else
            $rows = $this->adapter()->getItem($key)->get();
        return is_array($rows) ? $rows : [];
    }
*/
/*
    public function getTableRows($tableName, array $rowsId, bool $useHash = false): array
    {
        if (!$this->enabled) {
            return [];
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            $hash = $cache->hashMultiGet($key, $rowsId);
            $rows = [];
            if (is_array($hash)) {
                foreach ($hash as $id => $row) {
                    if (is_string($row)) {
                        $rows[$id] = json_decode($row, true);
                    }
                }
            }
        } else {
            $hash = $cache->getItem($key)->get();
            $rows = [];
            if (is_array($hash)) {
                foreach ($rowsId as $id) {
                    if (isset($hash[$id])) {
                        $rows[$id] = $hash[$id];
                    }
                }
            }
        }
        return $rows;
    }*/
/*
    public function getTableRowsId($tableName, bool $useHash = false): array
    {
        if (!$this->enabled) {
            return [];
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            return $cache->hashKeys($key);
        } else {
            $hash = $cache->getItem($key)->get();
            return is_array($hash) ? array_keys($hash) : [];
        }
    }

    public function getTableCountRows($tableName, bool $useHash = false): int
    {
        if (!$this->enabled) {
            return 0;
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            return (int) $cache->hashLength($key);
        } else {
            $hash = $cache->getItem($key)->get();
            return is_array($hash) ? sizeof($hash) : 0;
        }
    }
*/
    /**
     * Сохраняет значение (идентифицированное с ключом) в кэш.
     * 
     * Если кэш уже содержит такой ключ, тогда возвратит его значение.
     * 
     * @param string $key Ключ, идентифицирующий кэшированное значение.
     * @param callable $callback Callback-функция должна вернуть кэшируемое значение.
     *     Аргументы функции:
     *     - `$item` mixed, элемент адаптера кэша, используемый для манипуляции со значением ключа.
     * @param null|int $expiration Продолжительность, по умолчанию в секундах до истечения срока 
     *     действия кэша. Если не установлен, используется значение по умолчанию {@see Cache:defaultExpiry}.
     * 
     * @return mixed Значение хранящееся в кэше. Если false, значение нет в кэше или время истекло.
     */
    public function getOrSet(string $key, callable $callback, ?int $expiry = null) // remember
    {
        if (!$this->enabled) {
            return $callback(null);
        }
        $key = $this->buildKey($key);
        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }
        return $this->adapter()->getOrSet($key, $callback, $expiry);
    }

    /**
     * Сохраняет значения (идентифицированные с ключами) в кэш, полученные в результате 
     * выполнения SQL-запроса к указанной таблице.
     * 
     * Если кэш уже содержит такой ключ, тогда возвратит его значение.
     * Где ключ - значение, полученное из имени таблицы {@see \Ge\Cache\Cache::buildTableKey()}.
     * 
     * @param string $tableName Имя таблицы в SQL-запросе.
     * @param string $marker Маркер SQL-запроса (слово или фраза описывающая запрос). По 
     *     умолчанию '' (не используется).
     * @param callable $callback Callback-функция должна вернуть кэшируемое значение.
     *     Аргументы функции:
     *     - `$item` mixed, элемент адаптера кэша, используемый для манипуляции со значением ключа.
     * @param bool $useHash
     * @param int $expiration Продолжительность, по умолчанию в секундах до истечения срока 
     *     действия кэша. Если не установлен, используется значение по умолчанию 
     *     {@see Cache:defaultExpiry}. По умолчанию '0' (без ограничений).
     * 
     * @return mixed Значение хранящееся в кэше. Если `null`, значение нет в кэше или время истекло.
     */
    /*public function getOrSetTable($tableName, $callback, bool $useHash = false, int $expiration = 0)
    {
        if (!$this->enabled) {
            return $callback();
        }
        if ($useHash) {
            return $this->adapter()->hashSetOrGetAll($this->buildTableKey($tableName), $callback);
        } else {
            if ($expiry === null) {
                $expiry = $this->defaultExpiry;
            }
            return $this->adapter()->getOrSet($this->buildTableKey($tableName), $callback, $expiry);
        }
    }*/
/*
    public function getOrSetQuery($query, $callback, bool $useHash = false, int $expiration = 0)
    {
        if (!$this->enabled) {
            return $callback(null);
        }
        if ($useHash) {
            return $this->adapter()->hashSetOrGetAll($this->buildQueryKey($query, $marker), $callback);
        } else {
            if ($expiry === null) {
                $expiry = $this->defaultExpiry;
            }
            return $this->adapter()->getOrSet($this->buildQueryKey($query, $marker), $callback, $expiry);
        }
    }
*/
    /**
     * Проверяет, существует ли указанный ключ в кэше.
     * 
     * Это метод может работать быстрее, чем получение значения из кэша, если данных много.
     * Если кэш не поддерживает эту функцию изначально, этот метод попытается смоделировать ёё, 
     * но не улучшит производительность по сравнению с ёё получением.

     * @param string $key Ключ, идентифицирующий кэшированное значение.
     * 
     * @return bool Значение true, если значение существует в кэше, 
     * значение false, если значение отсутствует в кэше или срок его действия истек.
     */
    public function has(string $key): bool
    {
        $buildKey = $this->buildKey($key);
        return $this->adapter()->hasItem($buildKey);
    }

    /**
     * Проверяет, существует ли указанные ключ в кэше.
     * 
     * Это метод может работать быстрее, чем получение значения из кэша, если данных много.
     * Если кэш не поддерживает эту функцию изначально, этот метод попытается смоделировать ёё, 
     * но не улучшит производительность по сравнению с ёё получением.

     * @param string[] $key Массив ключей, идентифицирующие кэшированные значения.
     * 
     * @return array[string]bool Массив результатов проверки, если true, значения существует в кэше.
     *    Если false, значение отсутствует в кэше или срок его действия истек.
     */
    public function multiHas(...$keys): array
    {
         $adapter = $this->adapter();
         $has = [];
         foreach ($keys as $key) {
            $has[$key] = $adapter->hasItem($this->buildKey($key));
         }
         return $has;
    }
/*
    public function hasTableRow($tableName, $rowId, bool $useHash = false): bool
    {
        if (!$this->enabled) {
            return [];
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            return $cache->hashExists($key, $rowId);
        } else {
            $hash = $cache->getItem($key)->get();
            if (is_array($hash)) {
                return isset($hash[$rowId]);
            }
        }
        return false;
    }
*/
    /**
     * Удаляет ключи кэша с их значениями указывающие на массив записей, полученных при 
     * выполнении SQL-запроса к указанной таблице.
     * 
     * Варианты удаления ключей, связанных с выполнением SQL-запроса к указанной таблице:
     * - `$tableName = ''` или `$query = '*'` и `$marker = ''`, буду удалены все ключи 
     * связанные с таблицами. Маска ключей должна иметь вид `<prefix>table:*`;
     * - `$tableName = ''` или `$tableName = '*'` и `$marker = 'marker'`, буду удалены все ключи 
     * имеющие маркер и связанные  таблицами. Маска ключей должна иметь вид`<prefix>table:[marker]*`;
     * - `$tableName = 'table_name'` и `$marker = ''`, буду удалены все ключи имеющие указанныю 
     * таблицу. Маска ключей должна иметь вид `<prefix>table:<table_name>*`;
     * - `$tableName = 'table_name'` и `$marker = 'marker'`, буду удалены все ключи имеющие таблицу  
     *  и указанный маркер. Маска ключей должна иметь вид `<prefix>table:<table_name>:[marker]`;
     * 
     * @param string $tableName Имя таблицы, определяющие значение, которое нужно удалить из кэша.
     * 
     * @return bool Если false, во время удаления произошла ошибка.
     */
    /*public function flushTable($tableName): bool
    {
        if (!$this->enabled) {
            return false;
        }
        return $this->adapter()->deleteItem($this->buildTableKey($tableName));
    }*/
    /*
    public function flushTables(string $table, string $marker = '*', int $limit = 0): bool
    {
        if (!$this->enabled) {
            return false;
        }
        $key = $this->buildTableKey([$table, $marker]);
        return $this->adapter()->deleteItems($this->keys($key, $limit));
    }
    */
    /**
     * Удаляет ключи кэша с их значениями указывающие на результат выполнения SQL-запроса.
     * 
     * Варианты удаления ключей связанных с SQL-запросом:
     * - `$query = ''` или `$query = '*'` и `$marker = null`, буду удалены все ключи 
     * связанные с SQL-запросами. Маска ключей должна иметь вид `prefix:query:*`;
     * - `$query = ''` или `$query = '*'` и `$marker = 'marker'`, буду удалены все ключи 
     * имеющие маркер и связанные с SQL-запросами. Маска ключей должна иметь вид`prefix:query:marker:*`;
     * - `$query = 'sql'` и `$marker = null`, буду удалены все ключи имеющие результат 
     * выполнения указанного SQL-запроса. Маска ключей должна иметь вид `prefix:query:sql`;
     * - `$query = 'sql'` и `$marker = 'marker'`, буду удалены все ключи имеющие результат 
     * выполнения указанного SQL-запроса и маркера. Маска ключей должна иметь вид `prefix:query:marker:sql`;

     * @param string $query SQL-запрос.
     * @param string $marker Маркер запроса.
     * @param int $limit Количество ключей, которые необходимо удалить. Если значение '0', 
     *     то будут удалены все ключи удовлетворяющие запросу (по умолчанию '0').
     * 
     * @return bool Если false, во время удаления произошла ошибка.
     */
    public function flushQuery(string $query, string $marker = '', int $limit = 0): bool
    {
        if (!$this->enabled) {
            return false;
        }
        if ($query === '' || $query === '*')
            return $this->adapter()->deleteItems($this->queryKeys($query, $marker, $limit));
        else
            return $this->adapter()->deleteItem($this->buildQueryKey($query, $marker));
    }

    public function keys(string $pattern = '*', int $limit = 0)
    {
        return $this->adapter()->keys($pattern, $limit);
    }

    /**
     * Удаляет все значения из кэша.
     * 
     * Будьте осторожны при выполнении этой операции, если кэш совместно используется несколькими приложениями.
     * 
     * @return bool Если true, операция очистки успешна.
     */
    public function clear(): bool
    {
        return $this->adapter()->clear();
    }

    /**
     * Удаляет значение с указанным ключом из кэша.
     * 
     * @param string $key Ключ, определяющий значение, которое нужно удалить из кэша.
     * 
     * @return bool Если false, во время удаления произошла ошибка.
     */
    public function delete(string $key): bool
    {
        $key = $this->buildKey($key);
        return $this->adapter()->deleteItem($key);
    }

    /**
     * Удаляет значения с указанными ключами из кэша.
     * 
     * @param string[] $keys Массив ключей, определяющие значения, которое нужно удалить из кэша.
     * 
     * @return bool Если false, во время удаления произошла ошибка.
     */
    public function multiDelete(...$keys): bool
    {
        $buildKeys = array_map([$this, 'buildKey'], $keys);
        return $this->adapter()->deleteItems($buildKeys);
    }
/*
    public function deleteTableRows($tableName, array $rowsId, bool $useHash = false)
    {
        if (!$this->enabled) {
            return false;
        }
        $key   = $this->buildTableKey($tableName);
        $cache = $this->adapter();
        if ($useHash) {
            return $cache->hashDelete($key, $rowsId);
        } else {
            $item = $cache->getItem($key);
            $value = $item->get();
            if (is_array($value)) {
                $count = 0;
                foreach ($rowsId as $id) {
                    if (isset($value[$id])) {
                        $count++;
                        unset($value[$id]);
                    }
                }
                $item->set($value);
                $cache->save($item);
                return $count;
            } 
        }
    }
*/
    /**
     * Возвращает ключи с их значениями из кэша, полученные в результате выполнения SQL-запроса к указанной таблице.
     * 
     * Где ключ(и), идентификатор или массив идентификаторов записей.
     * 
     * @param string|int|array|null $rowId Ключ, идентификатор или массив идентификаторов записей.
     *    Если `null`, результатом будут все записи.
     * @param array[string]mixed $params Параметры запроса:
     *    - `table` string имя таблицы;
     *    - `primaryKey` string имя первичного ключа таблицы;
     *    - `columns` array название полей таблицы;
     *    - `where` string|array условия выборки записей;
     *    - `order` array порядок сортировки записей;
     *    - `limit` int количество выводимых записей;
     *    - `offset` int смещение диапазона вывода записей.
     * @param int $expiration Продолжительность, по умолчанию в секундах до истечения срока 
     *    действия кэша. Если не установлен, используется значение по умолчанию {@see Cache:defaultExpiry}.
     * 
     * @return mixed Значение хранящееся в кэше. Если false, значение нет в кэше или время истекло.
     */
    /*public function getRememberTableRows($rowId, array $params, int $expiration = 0)
    {
        $rows = $this->setOrGEt($params, $expiration);
        if ($rowId === null) {
            return $rows;
        } else
        if (is_array($rowId)) {
            foreach ($rowId as $id) {
                if (!isset($rows[$id]))
                    unset($rows[$id]);
            }
            return $rows;
        } else
            return $rows[$rowId] ?? null;
    }*/

    /**
     * Строит нормализованный ключ кэша из заданного ключа.
     * 
     * Ключ является строкой, содержащей только буквенно-цифровые символы и 
     * не более 32 символов, то ключ будет возвращен с префиксом {@see Cache::$keyPrefix}. 
     * В противном случае нормализованный ключ создается путем применения хеширования MD5 и префикса {@see Cache::$keyPrefix}.
     *
     * @param string $key Ключ, который нужно нормализовать.
     * 
     * @return string Cгенерированный ключ кэша.
     */
    public function buildKey(string $key): string
    {
        $key = ctype_alnum($key) && Str::byteLength($key) <= 32 ? $key : md5($key);
        return $this->keyPrefix . $key;
    }

    public function buildKeys(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->keyPrefix . (ctype_alnum($key) && Str::byteLength($key) <= 32 ? $key : md5($key));
        }
        return $result;
    }
/*
    public function tableKeys($tableName, int $limit = 0): array
    {
        $key = $this->buildTableKey($tableName);
        return $this->keys($key . '*', $limit);
    }
*/
    public function queryKeys(string $query, string $marker = '', int $limit = 0): array
    {
        $key = $this->buildQueryKey($query, $marker);
        return $this->keys($key . '*', $limit);
    }

    /**
     * Строит нормализованный ключ кэша из заданного имени таблицы.
     * 
     * Получаемое выражение: `<prefix>table:[table_name]:[marker_name]`, где:
     * - `prefix`, значение {@see Cache::$keyPrefix};
     * - `marker_name`, маркер SQL-запроса;
     * - `table_name`, имя таблицы в SQL-запросе.
     * 
     * Если имя таблицы имеет значения: '', '*', то имя маркера SQL-запроса и имя таблицы 
     * в ключе будут отсутствовать. Ключ примет вид: `<prefix>table:`.
     * 
     * Варианты нормализованного ключ кэша (`выражение` => `результат`):
     * - `<prefix>table:` => `g-12345:table:`;
     * - `<prefix>table:<table_name>` => `g-12345:table:table_name`;
     * - `<prefix>table:<table_name>:<marker_name>` => `g-12345:table:table_name:marker_name`;
     * 
     * @param string|array $tableName Имя таблицы в SQL-запросе, может иметь вид:
     *     - 'table_name' (string),  имя таблицы;
     *     - ['table_name', 'marker_name'] (array), имя таблицы и маркера SQL-запроса.
     *     Имя таблицы может быть экранированным, пример: '{{table_name}}'.
     * @param string $marker Маркер SQL-запроса (слово или фраза описывающая запрос).
     * 
     * @return string Нормализованный ключ кэша из заданного имени таблицы.
     */
    /*public function buildTableKey($tableName): string
    {
        if (is_array($tableName)) {
            $marker    = $tableName[1] ?? '';
            $tableName = $tableName[0] ?? '';
        // is_string($tableName)
        } else {
            $marker = '';
        }
        if ($tableName === '' || $tableName === '*') {
            $tableName = '';
        } else {
            $tableName = strtr($tableName, ['{{' => '', '}}' => '']);
            if ($marker) {
                $tableName = "$tableName:$marker";
            }
        }
        return $this->keyPrefix . "table:$tableName";
    }*/

    /**
     * Строит нормализованный ключ кэша из заданного SQL-запроса.
     * 
     * Выражение для получения ключа: `<prefix>query:[marker_name]:[sql_hash]`, где:
     * - `prefix`, значение {@see Cache::$keyPrefix};
     * - `marker_name`, маркер SQL-запроса;
     * - `sql_hash`, полученный хэш из указанного SQL-запроса.
     * 
     * Пример:
     * - `<prefix>query:` => `g-12345:query:`;
     * - `<prefix>query:<marker_name>` => `g-12345:query:marker_name`;
     * - `<prefix>query:<sql_hash>` => `g-12345:query:2bbfa55dc58149cbdc795d293e8a5f7a`;
     * - `<prefix>query:<marker_name>:<sql_hash>` => `g-12345:query:marker_name:2bbfa55dc58149cbdc795d293e8a5f7a`.
     * 
     * @param string $query SQL-запрос.
     * @param string $marker Маркер SQL-запроса (слово или фраза описывающая запрос).
     * 
     * @return string Нормализованный ключ кэша из заданного SQL-запроса.
     */
    public function buildQueryKey(string $query, string $marker = ''): string
    {
        if ($query === '' || $query === '*') {
            $query = $marker;
        } else {
            $query = md5($query);
            if ($marker) {
                $query = "$marker:$query";
            }
        }
        return $this->keyPrefix . "query:$query";
    }
}
