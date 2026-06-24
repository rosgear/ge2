<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db;

use Ge\Db\Sql\Insert;
use Ge\Db\Sql\Delete;
use Ge\Stdlib\BaseObject;
use Ge\Db\Adapter\Adapter;
use Ge\Db\Adapter\Driver\Exception\CommandException;

/**
 * QueriesMap это выполнение очередности SQL-запросов с помощью карты.
 * 
 * Карта SQL-запросов состоит из разделов:
 * - {@see QueriesMap::MAP_DROP}, выполняет удаление таблиц, например:  
 * ```php
 * ['{{table1}}', 'prefix_table2']
 * ```
 * - {@see QueriesMap::MAP_DELETE}, выполняет удаление записей из таблиц,  например:  
 * ```php
 * [
 *     '{{table1}}',
 *     'prefix_table2',
 *     '{{table3}}' => ['id' => [1, 2, 3], 'column' => 'value'],
 *     ...
 * ]
 * ```
 * - {@see QueriesMap::MAP_CREATE}, создаёт таблицы, например:
 * ```php
 * [
 *     '{{table1}}' => 'CREATE TABLE `{{table1}}` (...)',
 *     '{{table2}}' => function () {
 *         return 'CREATE TABLE `{{table2}}` (...)';
 *     }
 * ]
 * ```
 * - {@see QueriesMap::MAP_TRUNCATE}, выполняет удаление записей из таблиц и сброс 
 * автоинкремента, например:
 * ```php
 * [
 *     '{{table1}}', 'prefix_table2', ...
 * ]
 * ```
 * - {@see QueriesMap::MAP_INSERT}, выполняет добавление записей в таблицу, например:
 * ```php
 * [
 *     '{{table1}}'    => [['id' => 1, 'column' => 'value', ...], ...],
 *     'prefix_table2' => [['id' => 1, 'column' => 'value', ...], ...],
 *     ...
 * ]
 * ```
 * - {@see QueriesMap::MAP_RUN}, выполняет маршрут действий, например:
 * ```php
 * [
 *     'install'   => ['drop', 'create', 'insert'],
 *     'uninstall' => ['drop']
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db
 * @since 2.0
 */
class QueriesMap extends BaseObject
{
    /**
     * @var string Раздел карты "Сбросить".
     */
    public const MAP_DROP = 'drop';

    /**
     * @var string Раздел карты "Удалить".
     */
    public const MAP_DELETE = 'delete';

    /**
     * @var string Раздел карты "Создать".
     */
    public const MAP_CREATE = 'create';

    /**
     * @var string Раздел карты "Отрезать".
     */
    public const MAP_TRUNCATE = 'truncate';

    /**
     * @var string Раздел карты "Добавить".
     */
    public const MAP_INSERT = 'insert';

    /**
     * @var string Раздел карты "Выполнить".
     */
    public const MAP_RUN = 'run';

    /**
     * Адаптер подключения к базе данных.
     * 
     * @var Adapter
     */
    public Adapter $adapter;

    /**
     * Имя файла карты SQL-запросов.
     * 
     * Имя включает путь к файлу.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Переменные в виде пар "ключ - значение".
     * 
     * Используются в процессе обработки карты SQL-запросов.
     * 
     * @var array
     */
    public array $variables = [];

    /**
     * Выполнять загрузку файла после создания карты.
     * 
     * @var bool
     */
    public bool $autoload = false;

    /**
     * Дополнительные параметры.
     * 
     * Такие параметры могут использоваться в файле карты SQL-запросов.
     * 
     * @var array
     */
    public array $params = [];

    /**
     * Указывает на то, что файл карты был загружен.
     * 
     * @see QueriesMap::load()
     * 
     * @var bool
     */
    public bool $loaded = false;

    /**
     * Карта разделов.
     * 
     * @see QueriesMap::load()
     * 
     * @var array
     */
    protected array $map = [];

    /**
     * Последний выполненый SQL-запрос.
     * 
     * @var string
     */
    protected string $lastSql = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);

        if ($this->autoload) {
            $this->load();
        }
    }

    /**
     * Выполняет загрузку из файла карты SQL-запросов.
     * 
     * @see QueriesMap::include()
     * @see QueriesMap::addVariables()
     * 
     * @return bool Возвращает значение `false`, если файл карты SQL-запросов не 
     *     существует.
     */
    public function load(): bool
    {
        if (file_exists($this->filename)) {
            $this->map = include($this->filename);
            // если указаны переменные
            if (isset($this->map['variables'])) {
                $this->addVariables($this->map['variables']);
            }
            return $this->loaded = true;
        }
        return $this->loaded = false;
    }

    /**
     * Возвращает значение параметра.
     * 
     * @param string $name Имя параметра.
     * @param mixed $default Значение по умолчанию если параметр отсутсвует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Добавляет переменные.
     * 
     * @param array $variables Переменные карты SQL-запросов.
     * 
     * @return $this
     */
    public function addVariables(array $variables): static
    {
        $this->variables = array_merge($this->variables, $variables);
        return $this;
    }

    /**
     * Устанавливает переменные.
     * 
     * @param array $variables Переменные карты SQL-запросов.
     * 
     * @return $this
     */
    public function setVariables(array $variables): static
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Возвращает переменные.
     * 
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Устанавливает переменную.
     * 
     * @param string $name Название переменной.
     * @param mixed $value Значение переменной.
     * 
     * @return $this
     */
    public function setVariable(string $name, mixed $value): static
    {
        if ($value === null)
            unset($this->variables[$name]);
        else
            $this->variables[$name] = $value;
        return $this;
    }

    /**
     * Возвращает значение переменной.
     * 
     * @param string $name Название переменной.
     * @param mixed $default Значение, возвращаемое если при отсутствии переменной 
     *     (по умолчанию `null`).
     * 
     * @return $this
     */
    public function getVariable(string $name, mixed $default = null): mixed
    {
        return $this->variables[$name] ?? $default;
    }

    /**
     * Устанавливает имя файла карты SQL-запросов.
     *
     * @param string $filename Имя файла (включает путь) карты SQL-запросов.
     * 
     * @return $this
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Возврашает раздел или всю карту SQL-запросов.
     * 
     * @param null|string $name Имя раздела карты. Если значение `null`, возвратит 
     *     всю карты (по умолчанию `null`).
     * 
     * @return array|null Возврашает значение `null`, если указан раздел, а он не 
     *     существует.
     */
    public function getMap(?string $name = null): ?array
    {
        if ($name === null)
            return $this->map;
        else
            return $this->map[$name] ?? null;
    }

    /**
     * Возвращает содержимое раздела 'drop' карты SQL-запросов.
     * 
     * @return array|null Возврашает значение `null`, если раздел не существует.
     */
    public function getDropQueries(): ?array
    {
        if (empty($this->map[self::MAP_DROP]))
            return [];
        else {
            return $this->map[self::MAP_DROP] ?? null;
        }
    }

    /**
     * Возвращает последний выполненный SQL-запрос.
     * 
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }

    /**
     * Выполняет удаление таблиц.
     *
     * @param array $queries Имена таблиц, например:
     * ```php
     * [
     *     '{{table1}}', 
     *     'prefix_table2', 
     *     '{{table3}}' => function () { return '...' }, 
     *     // ...
     * ]
     * ```
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function dropAction(array $queries): void
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->adapter->createCommand();
        foreach ($queries as $query) {
            if (is_string($query))
                $command->dropTable($query, 'EXISTS');
            else
            if (is_callable($query))
                $command->setSql($query());
            else
                continue;
            $this->lastSql = $command->getSql();
            $command->execute();
        }
    }

    /**
     * Выполняет создание таблиц.
     *
     * @param array $queries Имена таблиц, например: 
     * ```php 
     * [
     *     '{{table1}}' => 'CREATE TABLE `{{table1}}` (...)',
     *     '{{table2}}' => function () { return 'CREATE TABLE `{{table2}}` (...)'; },
     *     // ...
     * ]
     * ``` 
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function createAction(array $queries): void
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->adapter->createCommand();
        foreach ($queries as $table => $query) {
            if (is_string($query))
                $command->createTable($query);
            else
            if (is_callable($query))
                $command->createTable($query());
            else
                continue;
            $this->lastSql = $command->getSql();
            $command->execute();
        }
    }

    /**
     * Выполняет добавление записей в таблицы.
     *
     * @param array $items Имена таблиц с записями, например: 
     * ```php
     * [
     *     '{{table1}}'    => [['id' => 1, 'column' => 'value', ...], ...],
     *     'prefix_table2' => [['id' => 1, 'column' => 'value', ...], ...],
     *     // ...
     * ]
     * ```
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function insertAction(array $items): void
    {
        /** @var \Ge\Db\Adapter\Platform\AbstractPlatform $platform */
        $platform = $this->adapter->getPlatform();
        foreach ($items as $table => $rows) {
            if (is_callable($rows)) {
                $rows = $rows();
            }

            $insert = new Insert($table);
            /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
            $command = $this->adapter->createCommand();
            foreach ($rows as $row) {
                $insert->columns($row);
                $sql = $insert->getSqlString($platform);
                $command->setSql($this->lastSql = $sql);
                $command->execute();
            }
        }
    }

    /**
     * Удаление записей из таблиц
     *
     * @param array $queries Имена таблиц с условиями, например: 
     * ```php
     * [
     *     '{{table1}}',
     *     'prefix_table2',
     *     '{{table3}}' => ['id' => [1, 2, 3], 'column' => 'value'],
     *     ...
     * ]
     * ```
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function deleteAction(array $queries): void
    {
        /** @var \Ge\Db\Adapter\Platform\AbstractPlatform $platform */
        $platform = $this->adapter->getPlatform();
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->adapter->createCommand();
        foreach ($queries as $table => $query) {
            if (is_string($query)) {
                $delete = new Delete($query);
                $command->setSql(
                    $this->lastSql = $delete->getSqlString($platform)
                );
            } else
            if (is_array($query)) {
                $delete = new Delete($table);
                $delete->where($query);
                $command->setSql(
                    $this->lastSql = $delete->getSqlString($platform)
                );
            } else
            if (is_callable($query)) {
                $delete = new Delete($table);
                $command->setSql($query($delete));
            } else
                continue;
            $command->execute();
        }
    }

    /**
     * Выполняет удаление записей из таблиц и сброс автоинкремента.
     *
     * @param array $queries Имена таблиц, например: 
     * ```php
     * [
     *     '{{table1}}', 'prefix_table2', ...
     * ]
     * ```
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function truncateAction(array $queries): void
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->adapter->createCommand();
        foreach ($queries as $table) {
            $command->truncateTable($table);
            $command->execute();
        }
    }

    /**
     * Выполняет указанное действие на таблицами.
     * 
     * @param string $action Имя действия, например: 'truncate', 'delete', 'insert', 
     *     'create', 'drop'.
     * 
     * @param null|array $queries Имена таблиц с параметрами, передаваемые в метод 
     *     действия (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если действия нет на карте SQL-запросов.
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     */
    public function doAction(string $action, ?array $queries = null): bool
    {
        if ($queries === null) {
            $queries = $this->map[$action] ?? null;
        }

        if ($queries) {
            $method = $action . 'Action';
            if (method_exists($this, $method)) {
                $this->$method($queries);
                return true;
            }
        }
        return false;
    }

    /**
     * Выполняет указаный маршрут в разделе "run" карты SQL-запросов.
     * 
     * @param string $name Название действия в разделе "run".
     * 
     * @return void
     * 
     * @throws CommandException Невозможно выполнить инструкцию SQL.
     * @throws Exception\RunQueriesMapException Отсутствие раздела "run" карты SQL-запросов.
     */
    public function run(string $name): void
    {
        $run = $this->map['run'][$name] ?? false;

        if ($run === false) {
            throw new Exception\RunQueriesMapException(
                sprintf('Run "%s" not found in queries map.', $name)
            );
        }

        foreach ($run as $index => $action) {
            if (is_numeric($index))
                $this->doAction($action);
            else
                $this->doAction($index, $action);
        }
    }

    /**
     * Запускается при вызове недоступных методов в контексте объекта.
     * 
     * Вызов метода:
     * - 'getVar' => 'getVariable';
     * - 'setVar' => 'setVariable'.
     * 
     * @param string $name Название метода.
     * @param array $arguments Массив аргументов.
     * 
     * @return mixed|void
     */
    public function __call(string $name, array $arguments) 
    {
        if ($name === 'getVar') {
            return call_user_func_array([$this, 'getVariable'], $arguments);
        }
        if ($name === 'setVar') {
            return call_user_func_array([$this, 'setVariable'], $arguments);
        }
    }
}