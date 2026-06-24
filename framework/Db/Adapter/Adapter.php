<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter;

use Ge;
use DateTime;
use DateTimeZone;
use Ge\Config\Config;
use Ge\Stdlib\Service;
use Ge\Db\Sql\Select;
use Ge\Db\Sql\AbstractSql;
use Ge\Db\Sql\QueryBuilder;
use Ge\Db\Sql\TableIdentifier;
use Ge\Db\Adapter\Driver\AbstractCommand;
use Ge\Db\Adapter\Platform\AbstractPlatform;
use Ge\Db\Adapter\Driver\AbstractConnection;

/**
 * Адаптер предназначен подключения к серверу базы данных.
 * 
 * Adapter - это служба приложения, доступ к которой можно получить через `Ge::$app->db`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter
 * @since 2.0
 */
class Adapter extends Service
{
    /**
     * Параметры конфигурации подключения к базе данных в виде пар "название - параметры".
     * 
     * Имеет вид:
     * ```php
     * [
     *     'name' => [
     *         'driver'      => 'MySqli',
     *         'host'        => 'localhost',
     *         'password'    => 'admin',
     *         'username'    => 'admin',
     *         'schema'      => 'sample',
     *         'charset'     => 'utf8',
     *         'collate'     => 'utf8_general_ci',
     *         'port'        => '3306',
     *         'engine'      => 'InnoDB',
     *         'tablePrefix' => 'GE_'
     *     ],
     *     // ...
     * ]
     * ```
     * @see Adapter::configure()
     * 
     * @var Config
     */
    public Config $connections;

    /**
     * Имя последнего подключения к базе данных.
     * 
     * @see Adapter::setConnectionName()
     * 
     * @var string
     */
    public string $connectionName = 'default';

    /**
     * Параметры последнего подключения к базе данных.
     * 
     * @see Adapter::setConnectionName()
     * 
     * @var array<string, string>
     */
    public array $connectionParams = [];

    /**
     * Автоматическое подключение к базе данных при инициализации адаптера 
     * подключения.
     * 
     * @see Adapter::init()
     *
     * @var bool
     */
    public bool $autoConnect = true;

    /**
     * @var bool
     */
    public bool $enableProfiling = false;

    /**
     * Платформа адаптера подключения к базе данных.
     * 
     * @see Adapter::getPlatform()
     *
     * @var AbstractPlatform
     */
    protected AbstractPlatform $platform;

    /**
     * Имя драйвера подключения к базе данных.
     *
     * @var string
     */
    protected string $driverName = 'MySqli';

    /**
     * Подключение к базе данных.
     * 
     * @see Adapter::connect()
     * 
     * @var AbstractConnection
     */
    protected AbstractConnection $connection;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\AdapterException Невозможно определить параметры адаптера из подключения.
     */
    public function init(): void
    {
        $this->setConnectionName($this->connectionName);
        if ($this->autoConnect) {
            $this->connect();
        }
    }

    /**
     * Устанавливает соединения с базой данных.
     * 
     * @see Adapter::$connections
     * 
     * @param string $name Имя соединения с базой данных.
     *
     * @throws Exception\AdapterException Невозможно определить параметры адаптера из подключения.
     */
    public function setConnectionName(string $name): static
    {
        $params = $this->connections->get($name);
        if ($params === false) {
            throw new Exception\AdapterException(Ge::t('app', 'Could find connection config'));
        }

        $this->driverName = $params['driver'] ?? $this->driverName;

        Ge::$services
            ->setInvokableClass('connection' . $this->driverName, '\Ge\Db\Adapter\Driver\\' . $this->driverName  . '\Connection')
            ->setInvokableClass('command' . $this->driverName, '\Ge\Db\Adapter\Driver\\' . $this->driverName  . '\Command')
            ->setInvokableClass('adapterPlatform' . $this->driverName, '\Ge\Db\Adapter\Platform\\' . $this->driverName);
        $this->connection       = Ge::$services->get('connection' . $this->driverName, $params);
        $this->connectionName   = $name;
        $this->connectionParams = $params;
        $this->initVariables();
        return $this;
    }

    /**
     * Возвращает имя соединения с базой данных.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * Проверяет, выполняется ли профилирование запросов к базе данных.
     *
     * @return bool
     */
    public function isProfiling(): bool
    {
        return GE_MODE_DEV && $this->enableProfiling;
    }

    /**
     * Выполняет соединение с базой данных.
     *
     * @return false|AbstractConnection Возвращает значение `false`, если ошибка соединения.
     */
    public function connect(): false|AbstractConnection 
    {
        if ($this->connection) {
            if ($this->connection->isConnected()) {
                return $this->connection;
            }
            $this->connection->connect();
            return $this->connection;
        } else
            return false;
    }

    /**
     * Проверяет, было ли открыто соединение с базой данных.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connection ? $this->connection->isConnected() : false;
    }

    /**
     * Возвращает конструктор инструкций SQL.
     *
     * @param string|null $table Имя таблицы (по умолчанию `null`).
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(?string $table = null): QueryBuilder
    {
        return new QueryBuilder($this, null, $table);
    }

    /**
     * Возвращает подключения к базе данных.
     *
     * @return false|AbstractConnection
     */
    public function getConnection(): false|AbstractConnection
    {
        return $this->connection;
    }

    /**
     * Возвращает платформу адаптера подключения к базе данных.
     *
     * @return AbstractPlatform
     */
    public function getPlatform(): AbstractPlatform
    {
        if (!isset($this->platform)) {
            $this->platform = Ge::$services->get('adapterPlatform' . $this->driverName, $this->connection);
        }
        return $this->platform;
    }

    /**
     * Возвращает строку с текущей датой и временем, преобразованной согласно формату 
     * поля таблицы "дата и время".
     * 
     * Формат поля определяет платформа драйвера {@see Adapter::$platform} подключения 
     * к базе данных.
     * 
     * @param DateTimeZone $timezone Часовой пояс, относительно которого определяется 
     *     текущая дата и время.
     * 
     * @return string
     */
    public function makeDateTime(DateTimeZone $timezone): string
    {
        return (new DateTime('now', $timezone))
            ->format($this->getPlatform()->dateTimeFormat);
    }

    /**
     * Возвращает строку с текущей датой, преобразованной согласно формату поля таблицы 
     * "дата".
     * 
     * Формат поля определяет платформа драйвера {@see Adapter::$platform} подключения 
     * к базе данных.
     * 
     * @param DateTimeZone $timezone Часовой пояс, относительно которого определяется 
     *     текущая дата.
     * 
     * @return string
     */
    public function makeDate(DateTimeZone $timezone): string
    {
        return (new DateTime('now', $timezone))
            ->format($this->getPlatform()->dateFormat);
    }

    /**
     * Возвращает строку с текущем временем, преобразованной согласно формату поля 
     * таблицы "дата".
     * 
     * Формат поля определяет платформа драйвера {@see Adapter::$platform} подключения 
     * к базе данных.
     * 
     * @param DateTimeZone $timezone Часовой пояс, относительно которого определяется 
     *     текущее время.
     * 
     * @return string
     */
    public function makeTime(DateTimeZone $timezone): string
    {
        return (new DateTime('now', $timezone))
            ->format($this->getPlatform()->timeFormat);
    }

    /**
     * Возвращает приставку имени таблицы.
     * 
     * @see Adapter::$connectionParams
     * 
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->connectionParams['tablePrefix'] ?? '';
    }

    /**
     * Возвращает имя таблицы с приставкой.
     * 
     * @param string $table Имя таблицы.
     * 
     * @return string
     */
    public function prefixTable(string $table): string
    {
        return $this->getTablePrefix() . $table;
    }

    /**
     * Возвращает оператор Select (выборки данных) инструкции SQL.
     *
     * @param TableIdentifier|string|array|null $table Имя таблицы (по умолчанию `null`).
     *
     * @return Select
     */
    public function select(TableIdentifier|string|array|null $table = null): Select
    {
        return new Select($table);
    }

    /**
     * Возвращает инструкции SQL c заменой префиксов таблиц.
     * 
     * @return string
     */
    public function rawExpression(string $str): string
    {
        $prefix = $this->connectionParams['tablePrefix'] ?? '';
        return strtr($str,
            [
                '{{' => $prefix,
                '}}' => ''
            ]
        );
    }

    /**
     * Создаёт команду выполнения инструкций SQL.
     *
     * @param QueryBuilder|Select|string|null $sql Инструкция SQL (по умолчанию `null`).
     *
     * @return AbstractCommand
     * 
     * @throws \Ge\Db\Sql\Exception\InvalidArgumentException Невозможно получить инструкцию SQL.
     * @throws \Ge\ServiceManager\Exception\NotInstantiableException Ошибка при создании 
     *     экземпляра класса.
     */
    public function createCommand(QueryBuilder|Select|string|null $sql = null): AbstractCommand
    {
        $command = Ge::$services->create('command' . $this->driverName, $this);
        if ($sql !== null) {
            if (is_string($sql))
                $command->setSql($sql);
            else
            if ($sql instanceof QueryBuilder)
                $command->setSql($sql->getSqlString());
            else
            if ($sql instanceof AbstractSql) {

                $command->setSql($sql->getSqlString($this->getPlatform()));
            }
        }
        return $command;
    }

    /**
     * Начало профилирования запросов к базе данных.
     * 
     * @return $this
     */
    public function beginProfile(): static
    {
        if (isset(Ge::$app)) {
            $log = Ge::$app->logger;

            if (GE_DEBUG && $log->isProfilingDbEnabled()) {
                $log->beginProfile('commandQuery', 'query');
            }
        }
        return $this;
    }

    /**
     * Конец профилирования запросов к базе данных.
     * 
     * @param array<string, mixed> $extra Параметры передаваемые в профиль запроса 
     *     (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function endProfile(array $extra = []): static
    {
        if (isset(Ge::$app)) {
            $log = Ge::$app->logger;

            if (GE_DEBUG && $log->isProfilingDbEnabled()) {
                $log->endProfile('commandQuery', $extra['rawSql'], $extra);
            }
        }
        return $this;
    }

    /**
     * Сохраняет значения (идентифицированные с ключами) в кэш, полученные в результате 
     * выполнения SQL-запроса.
     * 
     * Если кэш уже содержит такой ключ, тогда возвратит его значение.
     * 
     * Для хранения записей в кэше полученных в результате SQL-запроса используется
     * хэш-таблица кэша {@see \Ge\Cache\HashTable}.
     
     * @param string|array $name Имя (ключ) кэша состоит из имени таблицы  и 
     *     маркера SQL-запроса (слово или фраза описывающая запрос) к ней. По умолчанию 
     *     маркер '' (не используется). Имя можно указать как:
     *     - `['table_name', 'marker']` array;
     *     - `['table_name']` array;
     *     - `'table_name'` string.
     * @param callable $callback Callback-функция должна вернуть кэшируемое значение.
     * @param bool $useArray
     * @param int $expiry Продолжительность, по умолчанию в секундах до истечения срока 
     *    действия кэша. По умолчанию '0' (без ограничений).
     * 
     * @return mixed Значение хранящееся в кэше или результат выполнения SQL-запроса.
     */
    public function cache(string|array $name, callable $callback, bool $useArray = true, int $expiry = 0): mixed
    {
        /** @var \Ge\Cache\Cache|null $cache */
        $cache = Ge::$services->getAs('cache');
        if ($cache) {
            // если кэширование доступно
            if ($cache->enabled) {
                return $cache
                    ->getHashTable($name)
                    ->useArray($useArray)
                    ->expiry($expiry)
                    ->getOrSet($callback);
            }
        }
        return $callback();
    }

    /**
     * Удаляет ключ кэша с соответствующем ему массивом записей, полученных при выполнении 
     * SQL-запроса.
     * 
     * Для хранения записей в кэше полученных в результате SQL-запроса используется
     * хэш-таблица кэша {@see \Ge\Cache\HashTable}.
     * 
     * @param string|array $name Имя (ключ) кэша состоит из имени таблицы и 
     *     маркера SQL-запроса (слово или фраза описывающая запрос) к ней. По умолчанию 
     *     маркер '' (не используется). Имя можно указать как:
     *     - `['table_name', 'marker']` array;
     *     - `['table_name']` array;
     *     - `'table_name'` string.
     * 
     * @return bool Возвращает значение `false`, если во время удаления кэша произошла 
     *     ошибка.
     */
    public function flushCache(string|array $name): bool
    {
        /** @var \Ge\Cache\Cache|null $cache */
        $cache = Ge::$services->getAs('cache');
        if ($cache) {
            // если кэширование доступно
            if ($cache->enabled) {
                return $cache
                    ->getHashTable($name)
                    ->flush();
            }
        }
        return false;
    }
}
