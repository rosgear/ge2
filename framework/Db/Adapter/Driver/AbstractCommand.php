<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Driver;

use PDO;
use Closure;
use Ge\Db\Sql\Select;
use Ge\Db\Sql\Insert;
use Ge\Db\Sql\Update;
use Ge\Db\Sql\Delete;
use Ge\Db\Sql\Replace;
use Ge\Db\Sql\AbstractSql;
use Ge\Db\Adapter\Adapter;
use Ge\Db\Sql\Predicate\PredicateInterface;
use Ge\Db\Adapter\Platform\AbstractPlatform;

/**
 * Команда является абстрактным классом для выполнения инструкций SQL к серверу базы 
 * данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver
 * @since 2.0
 */
abstract class AbstractCommand
{
    /**
     * Последняя отправленная на сервер инструкция SQL.
     * 
     * @var string
     */
    protected string $sql = '';

    /**
     * Результат выполнения инструкции SQL.
     * 
     * Результат определяется драйвером подключения к базе данных.
     * 
     * @see AbstractCommand::execute()
     * @see AbstractCommand::query()
     * 
     * @var mixed
     */
    public mixed $result = false;

    /**
     * Экземпляр класса связи между PHP и базой данных.
     * 
     * Определяется соединением с сервером базы данных {@see AbstractCommand::$connection->getResource()}.
     * Пример: `\mysqli`...
     * 
     * @see AbstractCommand::setConnection()
     * 
     * @var object
     */
    protected object $resource;

    /**
     * Адаптер подключения к базе данных.
     * 
     * @see AbstractCommand::__construct()
     * 
     * @var Adapter
     */
    protected Adapter $db;

    /**
     * Платформа адаптера.
     * 
     * @see AbstractCommand::__construct()
     * 
     * @var AbstractPlatform
     */
    protected AbstractPlatform $platform;

    /**
     * Соединение с сервером базы данных.
     * 
     * @see AbstractCommand::setConnection()
     * 
     * @var AbstractConnection
     */
    protected AbstractConnection $connection;

    /**
     * Параметры подставляемые в инструкцию SQL в виде пар "ключ - значение".
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Устанавливает режим получения данных.
     * 
     * Может иметь значения соответствующие константам класса `PDO`:
     * - `PDO::FETCH_ASSOC`, массив, индексированный именами столбцов результирующего 
     * набора;
     * - `PDO::FETCH_NUM`, массив, индексированный номерами столбцов (начиная с 0);
     * - `PDO::FETCH_BOTH`, анонимный объект со свойствами, соответствующими именам столбцов 
     * результирующего набора;
     * - `PDO::FETCH_OBJ`, анонимный объект со свойствами, соответствующими именам столбцов 
     * результирующего набора.
     * 
     * @see AbstractCommand::setFetchMode()
     * 
     * @var int
     */
    public int $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Режим получения данных для сохранения и восстановления.
     * 
     * @see AbstractCommand::saveFetchMode()
     * @see AbstractCommand::restoreFetchMode()
     * 
     * @var int|null
     */
    protected ?int $prevFetchMode = null;

    /**
     * Конструктор класса.
     * 
     * @param Adapter $db Адаптер подключения к базе данных.
     * 
     * @return void
     */
    public function __construct(Adapter $db)
    {
        $this->db = $db;
        $this->platform = $db->getPlatform();
        $this->setConnection($db->getConnection());
    }

    /**
     * Устанавливает соединение с сервером базы данных.
     * 
     * @param AbstractConnection $connection Соединение с сервером базы данных.
     * 
     * @return $this
     */
    public function setConnection(AbstractConnection $connection): static
    {
        $this->connection = $connection;
        $this->resource = $connection->getResource();
        return $this;
    }

    /**
     * Устанавливает инструкцию SQL для выполнения запроса к серверу базы данных.
     * 
     * @see AbstractCommand::$sql
     * 
     * @param string $sql Инструкция SQL.
     * 
     * @return $this
     */
    public function setSql(AbstractSql|string $sql): static
    {
        if ($sql instanceof AbstractSql) {
            $sql = $sql->getSqlString($this->platform);
        }
        $this->sql = $sql;
        return $this;
    }

    /**
     * Возвращает инструкцию SQL для выполнения запроса к серверу базы данных.
     * 
     * @see AbstractCommand::$sql
     * 
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Добавляет параметр в инструкцию SQL.
     * 
     * @see AbstractCommand::$params
     * 
     * @param string $name Имя параметра.
     * @param mixed $value Значение параметра.
     * 
     * @return $this
     */
    public function bindValue(string $name, mixed $value): static
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Добавляет параметры в инструкцию SQL.
     * 
     * @see AbstractCommand::$params
     * 
     * @param array $values Параметры в виде пар "ключ - значение".
     * 
     * @return $this
     */
    public function bindValues(array $values): static
    {
        if (empty($values)) {
            return $this;
        }
        foreach ($values as $name => $value) {
            $this->params[$name] = $value;
        }
        return $this;
    }

    /**
     * Возвращает столбцы таблицы с их значениями в виде пар "столбец - значение" по 
     * указанным опциям.
     * 
     * @param array $params Имена полей с их псевдонимами в виде пар "поле - псевдоним".
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * @param array $options Опции столбцов таблицы.
     * 
     * @return array
     */
    public function bindColumns(array $params, array $columns, array $options): array
    {
        $row = [];
        foreach ($params as $param => $field) {
            if (isset($columns[$field])) {
                if (isset($options[$param]['renderColumn']))
                    $row[$param] = $options[$param]['renderColumn']($columns[$field]);
                else
                    $row[$param] = $columns[$field];
            }
        }
        return $row;
    }

    /**
     * Возвращает результата выполнения инструкции SQL.
     * 
     * Результат зависит от драйвера подключения к базе данных.
     * 
     * @return mixed
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Возвращает подробную информацию о результате последнего запроса.
     * 
     * @see AbstractCommand::$result
     * 
     * @return mixed
     */
    public function getResultInfo(): mixed
    {
        return false;
    }

    /**
     * Возвращает инструкцию SQL с подставленными именами таблиц.
     * 
     * @param null|array<string, string> $params Параметры замены имен таблиц в виде 
     *     пар "ключ - значение" (по умолчанию `null`).
     * 
     * @return string 
     */
    protected function getRawTable(?array $params = null): string
    {
        $connection = $this->db->connectionParams;
        if (key_exists('tablePrefix', $connection)) {
            if ($params === null)
                $params = [
                    '{{' => $connection['tablePrefix'],
                    '}}' => ''
                ];
            return strtr($this->sql, $params);
        }
        return $this->sql;
    }

    /**
     * Возвращает инструкцию SQL с подставленными параметрами запроса.
     * 
     * Параметры запроса {@see AbstractCommand::$params}.
     * 
     * @see AbstractCommand::getRawTable()
     * 
     * @return string
     */
    public function getRawSql(): string
    {
        $sql = $this->getRawTable();
        if (empty($this->params)) {
            return $sql;
        }
        $params = [];
        foreach ($this->params as $name => $value) {
            if (is_string($name) && strncmp(':', $name, 1)) {
                $name = ':' . $name;
            }
            if ($name[1] === '@') {
                $params[$name] = $value;
            } elseif (is_array($value)) {
                $params[$name] = implode(',', $value);
            } elseif (is_string($value)) {
                $params[$name] = $this->platform->quoteValue($value);
            } elseif (is_bool($value)) {
                $params[$name] = ($value ? 'TRUE' : 'FALSE');
            } elseif ($value === null) {
                $params[$name] = 'NULL';
            } elseif (!is_object($value) && !is_resource($value)) {
                $params[$name] = $value;
            }
        }
        if (!isset($params[1])) {
            return strtr($sql, $params);
        }
        $sql = '';
        foreach (explode('?', $sql) as $i => $part) {
            $sql .= (isset($params[$i]) ? $params[$i] : '') . $part;
        }
        return $sql;
    }

    /**
     * Проверяет, установлен ли режим получения данных, как массив индексированный 
     * именами столбцов или анонимный объект со свойствами, соответствующими именам 
     * столбцов результирующего набора.
     * 
     * Свойство {@see AbstractCommand::$fetchMode} должно иметь значение `PDO::FETCH_ASSOC` 
     * или `PDO::FETCH_OBJ`.
     * 
     * @return bool
     */
    public function isFetchAO(): bool
    {
        return $this->fetchMode === PDO::FETCH_ASSOC || $this->fetchMode === PDO::FETCH_OBJ;
    }

    /**
     * Проверяет, установлен ли режим получения данных, как анонимный объект со свойствами, 
     * соответствующими именам столбцов результирующего набора.
     * 
     * Свойство {@see AbstractCommand::$fetchMode} должно иметь значение `PDO::FETCH_OBJ`.
     * 
     * @return bool
     */
    public function isFetchObject(): bool
    {
        return $this->fetchMode === PDO::FETCH_OBJ;
    }

    /**
     * Проверяет, установлен ли режим получения данных, как массив индексированный 
     * именами столбцов.
     * 
     * Свойство {@see AbstractCommand::$fetchMode} должно иметь значение `PDO::FETCH_ASSOC`.
     * 
     * @return bool
     */
    public function isFetchAssoc(): bool
    {
        return $this->fetchMode === PDO::FETCH_ASSOC;
    }

    /**
     * Выполняет инструкцию SQL.
     * 
     * Инструкция SQL для выполнения запроса {@see AbstractCommand::getRawSql()}.
     * 
     * @return $this
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function execute(): static
    {
        return $this;
    }

    /**
     * Выполняет инструкцию SQL.
     * 
     * Псевдоним {@see AbstractCommand::execute()}.
     * 
     * Инструкция SQL для выполнения запроса {@see AbstractCommand::getRawSql()}.
     * 
     * @return $this
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function query(): static
    {
        return $this;
    }

    /**
     * Выбирает массив или объект после выполнения инструкции SQL.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetch()
     * 
     * @return mixed Возвращает значение `null`, если запись отсутствует, иначе 
     *     результат соответствует {@see AbstractMode::$fetchMode}.
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryOne(): mixed
    {
        $this->query();
        return $this->fetch();
    }

    /**
     * Выбирает значения первого столбца таблицы из результирующего набора после 
     * выполнения инструкции SQL.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_NUM`.
     * 
     * Пример, смотри {@see AbstractCommand::fetchColumn()}.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetchColumn()
     * 
     * @return array<int, mixed>
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryColumn(): array
    {
        $this->query();
        return $this->fetchColumn();
    }

    /**
     * Выбирает все строки из результирующего набора после выполнения инструкции SQL.
     * 
     * Если указан ключ `$fetchToKey`, то будет проверяться режим получения данных, он
     * должен соответствовать: `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим 
     * указан не верно, то результатом будет пустой массив.
     * 
     * Пример, смотри {@see AbstractCommand::fetchAll()}.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetchAll()
     * 
     * @param null|string $fetchToKey Ключ возвращаемого ассоциативного массива записей. 
     *     Если значение `null`, то результатом будет индексированный массив записей 
     *     (по умолчачнию `null`).
     * 
     * @return array<int|string, array|object>
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryAll(?string $fetchToKey = null): array
    {
        $this->query();
        return $this->fetchAll($fetchToKey);
    }

    /**
     * Возвращает записи c указанными столбцами после выполнения инструкции SQL.
     * 
     * Внимание: будет проверяться режим получения данных, он должен соответствовать: 
     * `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим указан не верно, то 
     * результатом будет пустой массив.
     * 
     * Пример, смотри {@see AbstractCommand::fetchTo()}.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetchTo()
     * 
     * @param array<int, string> $columns Имена возвращаемых столбцов. Если столбцы не указаны, 
     *     результатом будут, только значения этих столбцов в виде индексированного 
     *     массива.
     * 
     * @return array<int, array<int, mixed>>
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryTo(array $columns = []): array
    {
        $this->query();
        return $this->fetchTo($columns);
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в массив в виде 
     * пар "ключ, значение" после выполнения инструкции SQL.
     * 
     * Внимание: будет проверяться режим получения данных, он должен соответствовать: 
     * `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим указан не верно, то 
     * результатом будет пустой массив.
     * 
     * Пример, смотри {@see AbstractCommand::fetchToCombo()}.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetchToCombo()
     * 
     * @param string $key Уникальный идентификатор элемента combo (первичный ключ).
     * @param string $key Название элемента combo (столбец таблицы).
     * 
     * @return array
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryToCombo(string $key, string $column): array
    {
        $this->query();
        return $this->fetchToCombo($key, $column);
    }

    /**
     * Выбирает строки в виде пар "ключ - значение" после выполнения инструкции SQL.
     * 
     * Где первый столбцей таблицы играет роль ключа, а второй - значение.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_NUM`.
     * 
     * Пример, смотри {@see AbstractCommand::fetchPairs()}.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetchPairs()
     * 
     * @return array<mixed, mixed>
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryPairs(): array
    {
        $this->query();
        return $this->fetchPairs();
    }

    /**
     * Выбирает значения с указанным ключём после выполнения инструкции SQL. 
     * 
     * Где, значения становятся самими ключами в результирующем массиве, которым будут 
     * соответствовать значения указанного столбца.
     * 
     * Если значения по указанному ключу `$key` пустые, то они будут пропускаться. Если 
     * значения по указанному столбцу `$column` отсутствуют, то они будут иметь `null`.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_ASSOC`.
     * 
     * Пример, смотри {@see AbstractCommand::fetchToColumn()}.
     * 
     * @param string $key Ключ, значения которого станут ключами.
     * @param string $column Имя столбца, значения которого будут установлены ключам.
     * 
     * @return array<mixed, mixed>
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryToColumn(string $key, string $column): array
    {
        $this->query();
        return $this->fetchToColumn($key, $column);
    }

    /**
     * Выбирает значения первого столбца каждой строки результирующего набора после 
     * выполнения инструкции SQL.
     * 
     * @see AbstractCommand::query()
     * @see AbstractCommand::fetch()
     * 
     * @return mixed Возвращает значение `null`, если невозможно получить результат.
     * 
     * @throws Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function queryScalar(): mixed
    {
        $this->query();
        $row = $this->fetch();
        if ($row === null) return null;
        return current($row);
    }

    /**
     * Устанавливает режим выборки записей.
     * 
     * @see AbstractCommand::$fetchMode
     * 
     * @param int $mode Режим получения данных: `PDO::FETCH_ASSOC`, `PDO::FETCH_NUM`, 
     *     `PDO::FETCH_OBJ`, `PDO::FETCH_BOTH`.
     * 
     * @return $this
     */
    public function setFetchMode(int $mode): static
    {
        $this->fetchMode = $mode;
        return $this;
    }

    /**
     * Сохраняет предыдущий режим выборки записей и устанавливает новый.
     * 
     * @see AbstractCommand::$prevFetchMode
     * 
     * @param int $mode Устанавливает новый режим получения данных: 
     *     `PDO::FETCH_ASSOC`, `PDO::FETCH_NUM`, `PDO::FETCH_OBJ`, `PDO::FETCH_BOTH`.
     * 
     * @return $this
     */
    public function saveFetchMode(int $mode): static
    {
        $this->prevFetchMode = $this->fetchMode;
        $this->fetchMode = $mode;
        return $this;
    }

    /**
     * Восстанавливает предыдущий режим выборки записей.
     * 
     * @see AbstractCommand::$prevFetchMode
     * 
     * @return $this
     */
    public function restoreFetchMode(): static
    {
        if ($this->prevFetchMode !== null) {
            $this->fetchMode = $this->prevFetchMode;
        }
        return $this;
    }

    /**
     * Возвращает общее количество записей в таблице к которой применялся запрос 
     * выборки (select) записей.
     * 
     * @return int
     */
    public function getFoundRows(): int
    {
        return 0;
    }

    /**
     * +Выбирает массив или объект при успещном запросе, соответствующий обработанному 
     * ряду результата запроса и сдвигает внутренний указатель данных вперёд. 
     * 
     * Возвращаемый результат определяется видом записей {@see AbstractCommand::$fetchMode}.
     * 
     * Пример возвращаемого результата:
     * - `[column1 => value1, column2 => value2, ...]`;
     * - `[value1, value2, ...]`;
     * - `object{[column1] => value1, [column2] => value2, ...}`.
     * 
     * @return mixed Возвращает массив или объект со строковыми свойствами, соответствующими 
     *     полученному ряду, или `null`, если рядов больше нет. 
     */
    public function fetch(): mixed
    {
        return null;
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в массив.
     * 
     * @param int $fetchMode Режим получения данных {@see AbstractCommand::$fetchMode}.
     * 
     * @return mixed Возвращает массив объектов, строк или `false`, если рядов больше нет. 
     */
    public function fetchAllRows(int $fetchMode): mixed
    {
    }

    /**
     * Выбирает все строки из результирующего набора.
     * 
     * Если указан ключ `$fetchToKey`, то будет проверяться режим получения данных, он
     * должен соответствовать: `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим 
     * указан не верно, то результатом будет пустой массив.
     * 
     * Пример с указанием ключа fetchKey: 
     * ```php
     * // 1)
     * ['fetchKey' => [...], 'fetchKey1' => [...], ...]
     * // 2)
     * ['fetchKey' => object{...}, 'fetchKey1' => object{...}, ...]
     * ```
     * 
     * Пример без указания ключа fetchKey:
     * ```php
     * // 1) 
     * [[...], [...], ...]
     * // 2)
     * [object{...}, object{...}, ...]
     * ```
     * 
     * @see AbstractCommand::fetch()
     * 
     * @param null|string $fetchKey Ключ возвращаемого ассоциативного массива результирующего 
     *     набора. Если `null`, результатом будет индексированный массив результирующего 
     *     набора (по умолчачнию `null`).
     * 
     * @return array<int|string, array|object>
     */
    public function fetchAll(?string $fetchKey = null): array
    {
        $rows = [];
        if ($fetchKey) {
            if ($this->isFetchAssoc()) {
                while ($row = $this->fetch()) $rows[$row[$fetchKey]] = $row;
            } else
            if ($this->isFetchObject()) {
                while ($row = $this->fetch()) $rows[$row->$fetchKey] = $row;
            }
            return $rows;
        }

        while ($row = $this->fetch()) $rows[] = $row;
        return $rows;
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в объект, ассоциативный 
     * массив, обычный массив или в оба, и связывает их с указанным ключем. Результат 
     * выбранных строк группируется по значениям ключа группы.
     * 
     * Внимание: будет проверяться режим получения данных, он должен соответствовать: 
     * `PDO::FETCH_ASSOC`. Если режим указан не верно, то результатом будет пустой массив.
     * 
     * Пример с указанием ключа fetchKey:
     * ```php
     * // 1)
     * ['groupKey' => ['fetchKey' => [...], 'fetchKey1' => [...], ...], ...]
     * // 2)
     * ['groupKey' => ['fetchKey' => object{...}, 'fetchKey1' => object{...}, ...], ...]
     * ```
     * 
     * Пример без указания ключа fetchKey: 
     * ```php
     * // 1)
     * [['groupKey' => [[...], ...], ...]
     * // 2)
     * [['groupKey' => [object{...}, ...], ...]
     * ```
     * 
     * @see AbstractCommand::fetch()
     * 
     * @param string $groupKey Ключ для группирования результата выбранных строк.
     * @param null|string $fetchKey Ключ возвращаемого ассоциативного массива результирующего 
     *     набора. Если `null`, результатом будет индексированный массив результирующего 
     *     набора (по умолчачнию `null`).
     * 
     * @return array
     */
    public function fetchToGroups(string $groupKey, ?string $fetchKey = null): array
    {
        if (!$this->isFetchAssoc()) return [];

        $rows = [];
        if ($fetchKey !== null) {
            while ($row = $this->fetch()) {
                $groupValue = $row[$groupKey];
                if (!isset($rows[$groupValue])) {
                    $rows[$groupValue] = [];
                }
                $rows[$groupValue][$row[$fetchKey]] = $row;
            }
        } else {
            while ($row = $this->fetch()) {
                $groupValue = $row[$groupKey];
                if (!isset($rows[$groupValue])) {
                    $rows[$groupValue] = [];
                }
                $rows[$groupValue][] = $row;
            }
        }
        return $rows;
    }

    /**
     * +Выбирает все строки из результирующего набора (с указанными столбцами) и 
     * помещает их в ассоциативный массив (если столбцы  указаны) или в нумерованный 
     * массив.
     * 
     * Внимание: будет проверяться режим получения данных, он должен соответствовать: 
     * `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим указан не верно, то 
     * результатом будет пустой массив.
     * 
     * Например, для `$columns = ['fruit', 'validity']`:
     * ```php
     * // исходный массив
     * [
     *     ['fruit' => 'apple', 'validity' => 'good', ...], 
     *     ['fruit' => 'banana', 'validity' => 'bad', ...], 
     *     ...
     * ]
     * // результирующий массив
     * [['apple', 'good'], ['banana', 'bad'], ...]
     * ```
     * 
     * @param array $columns Имена возвращаемых столбцов.
     * 
     * @return array<int, array<int, mixed>>
     */
    public function fetchTo(array $columns = []): array
    {
        if (!$this->isFetchAO()) return [];

        $isFetchObject = $this->isFetchObject();

        $rows = [];
        while ($fetch = $this->fetch()) {
            if ($columns) {
                $row = [];

                if ($isFetchObject)
                    foreach ($columns as $column) $row[] = $fetch->$column;
                else
                    foreach ($columns as $column) $row[] = $fetch[$column];

                $rows[] = $row;
            } else
                $rows[] = array_values($fetch);
        }
        return $rows;
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в массив в виде 
     * пар "ключ, значение".
     * 
     * Внимание: будет проверяться режим получения данных, он должен соответствовать: 
     * `PDO::FETCH_ASSOC` или `PDO::FETCH_OBJ`. Если режим указан не верно, то 
     * результатом будет пустой массив.
     * 
     * Например, для `$key = 'fruit'`, `$column = 'validity'`:
     * ```php
     * // исходный массив
     * [
     *     ['fruit' => 'apple', 'validity' => 'good', ...], 
     *     ['fruit' => 'banana', 'validity' => 'bad', ...], 
     *     ...
     * ]
     * // результирующий массив
     * [['apple', 'good'], ['banana', 'bad'], ...]
     * ```
     * 
     * @param string $key Уникальный идентификатор элемента combo (первичный ключ).
     * @param string $key Название элемента combo (столбец таблицы).
     * 
     * @return array
     */
    public function fetchToCombo(string $key, string $column): array
    {
        $rows = [];
        if ($this->isFetchAssoc()) {
            while ($fetch = $this->fetch()) $rows[] = [$fetch[$key], $fetch[$column]];
        } else
        if ($this->isFetchObject()) {
            while ($fetch = $this->fetch()) $rows[] = [$fetch->$key, $fetch->$column];
        }
        return $rows;
    }

    /**
     * Выбирает значения с указанным ключём, где значения становятся самими ключами в
     * результирующем массиве, которым будут соответствовать значения указанного столбца.
     * 
     * Если значения по указанному ключу `$key` пустые, то они будут пропускаться. Если 
     * значения по указанному столбцу `$column` отсутствуют, то они будут иметь `null`.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_ASSOC`.
     * 
     * Например, для `$key = 'fruit'`, `$column = 'validity'`:
     * ```php
     * // исходный массив
     * [
     *     ['fruit' => 'apple', 'validity' => 'good', ...], 
     *     ['fruit' => 'banana', 'validity' => 'bad', ...], 
     *     ...
     * ]
     * // результирующий массив
     * ['apple' => 'good', 'banana' => 'bad', ...]
     * ```
     * 
     * @param string $key Ключ, значения которого станут ключами.
     * @param string $column Имя столбца, значения которого будут установлены ключам.
     * 
     * @return array<mixed, mixed>
     */
    public function fetchToColumn(string $key, string $column): array
    {
        $this->saveFetchMode(PDO::FETCH_ASSOC);

        $rows = [];
        while ($fetch = $this->fetch()) {
            if (!empty($fetch[$key])) {
                $value = $fetch[$key];
                $rows[$value] = isset($fetch[$column]) ? $fetch[$column] : null;
            }
        }

        $this->restoreFetchMode();
        return $rows;
    }

    /**
     * Выбирает строки в виде пар "ключ - значение".
     * 
     * Где первый столбец таблицы играет роль ключа, а второй - значение.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_NUM`.
     * 
     * Например:
     * ```php
     * // исходный массив
     * [
     *     ['column1' => 'apple', 'column1' => 'good', ...], 
     *     ['column1' => 'banana', 'column2' => 'bad', ...], 
     *     ...
     * ]
     * // результирующий массив
     * ['apple' => 'good', 'banana' => 'bad', ...]
     * ```
     * 
     * @return array<mixed, mixed>
     */
    public function fetchPairs(): array
    {
        $this->saveFetchMode(PDO::FETCH_NUM);

        $rows = [];
        while ($row = $this->fetch()) {
            $rows[$row[0]] = $row[1];
        }

        $this->restoreFetchMode();
        return $rows;
    }

    /**
     * Выбирает значения первого столбца таблицы из результирующего набора.
     * 
     * Внимание: запрос будет выполнен в режиме получения данных `PDO::FETCH_NUM`.
     * 
     * Например:
     * ```php
     * // исходный массив
     * [
     *     ['column1' => 'apple', 'column1' => 'good', ...], 
     *     ['column1' => 'banana', 'column2' => 'bad', ...], 
     *     ...
     * ]
     * // результирующий массив
     * [['apple'], ['banana'], ...]
     * ```
     * 
     * @return array<int, mixed>
     */
    public function fetchColumn(): array
    {
        $this->saveFetchMode(PDO::FETCH_NUM);

        $rows = [];
        while ($row = $this->fetch()) {
            $rows[] = $row[0];
        }

        $this->restoreFetchMode();
        return $rows;
    }

    /**
     * Устанавливает инструкцию SQL для добавления записи в таблицу.
     * 
     * @see AbstractCommand::setSql()
     * 
     * @param string $table Имя таблицы.
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * 
     * @return $this
     */
    public function insert(string $table, array $columns): static
    {
        $insert = new Insert($table);
        $insert->columns($columns);

        $this->setSql($insert);
        return $this;
    }

    /**
     * Устанавливает инструкцию SQL для замены/добавления записи в таблицу.
     * 
     * @see AbstractCommand::setSql()
     * 
     * @param string $table Имя таблицы.
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * 
     * @return $this
     */
    public function replace(string $table, array $columns): static
    {
        $replace = new Replace($table);
        $replace->columns($columns);

        $this->setSql($replace);
        return $this;
    }

    /**
     * Устанавливает инструкцию SQL для выборки записей из таблицы.
     * 
     * @see AbstractCommand::setSql()
     * 
     * @param string $table Имя таблицы.
     * @param array $columns Столбцы таблицы выборки. Если столбцы выборки не указаны, 
     *     то по умолчанию будет значение `['*']` (означающее "все столбцы"). 
     * @param PredicateInterface|Closure|string|array|null $where Условие выполнения 
     *     запроса (по умолчанию `null`).
     * 
     * @return $this
     */
    public function select(
        string $table, 
        array $columns = ['*'], 
        PredicateInterface|Closure|string|array|null $where = null
    ): static
    {
        $select = new Select($table);
        $select->columns($columns);
        if ($where) {
            $select->where($where);
        }

        $this->setSql($select);
        return $this;
    }

    /**
     * Устанавливает инструкцию SQL для обновления записи таблицы.
     * 
     * @param string $table Имя таблицы.
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * @param PredicateInterface|Closure|string|array|null $where Условие выполнения запроса.
     * 
     * @return $this
     */
    public function update(
        string $table, 
        array $columns, 
        PredicateInterface|Closure|string|array|null $where = null
    ): static
    {
        $update = new Update($table);
        $update->set($columns);
        if ($where) {
            $update->where($where);
        }

        $this->setSql($update);
        return $this;
    }

    /**
     * Устанавливает инструкцию SQL для удаление записей таблицы.
     * 
     * @param string $table Имя таблицы.
     * @param PredicateInterface|Closure|string|array|null $where Условие выполнения 
     *     запроса (по умолчанию `null`).
     * 
     * @return $this
     */
    public function delete(
        string $table, 
        PredicateInterface|Closure|string|array|null $where = null
    ): static
    {
        $delete = new Delete($table);
        if ($where) {
            $delete->where($where);
        }

        $this->setSql($delete);
        return $this;
    }

    /**
     * Формирует инструкцию SQL для создания базы данных.
     * 
     * @see AbstractCommand::setSql()
     * 
     * @param string $database Имя базы данных. 
     * 
     * @return $this
     */
    public function createDatabase(string $database): static
    {
        return $this;
    }

    /**
     * Формирует инструкцию SQL для создания таблицы.
     * 
     * @see AbstractCommand::setSql()
     * 
     * @param string $sql Шаблон инструкции SQL для создания таблицы, куда будут 
     *     добавлены соответствующие параметры.
     * 
     * @return $this
     */
    public function createTable(string $sql): static
    {
        return $this;
    }

    /**
     * Возвращает инструкцию SQL для сброса значения автоинкремента таблицы.
     * 
     * Автоинкремент позволяет автоматически генерировать уникальный номер при вставке 
     * новой записи в таблицу, часто это поле первичного ключа.
     * 
     * @param string $table Имя таблицы.
     * @param int|string $increment Значение инкремента.
     * 
     * @return $this
     */
    public function resetIncrement(string $table, int|string $increment = 1): static
    {
        return $this;
    }

    /**
     * Удаляет все записи и сбрасывает значения автоинкремента таблицы.
     * 
     * @param string $table Имя таблицы.
     * 
     * @return $this
     */
    public function truncateTable(string $table): static
    {
        return $this;
    }

    /**
     * Удаляет таблицу из базы данных.
     * 
     * @param string $table Имя таблицы.
     * @param string|null $exists Сигнатура добавляемая в SQL инструкцию. Может иметь 
     *     значения: 'EXISTS', 'NOT EXISTS', `null` (по умолчанию `null`).
     * 
     * @return $this
     */
    public function dropTable(string $table, string $exists = null): static
    {
        return $this;
    }

    /**
     * Возвращает ошибку последнего запроса.
     * 
     * @return string
     */
    public function getError(): string
    {
        return '';
    }

    /**
     * Возвращает количество записей полученных в последнем запросе выборке (select) 
     * записей.
     * 
     * @return int
     */
    public function getAffectedRows(): int
    {
        return 0;
    }

    /**
     * Возвращает названия наборов символов (character set), используемых сервером базы данных.
     * 
     * @param string $column Столбец, указывающий на название набора символов.
     * 
     * @return array<int|string, array<string, mixed>>
     */
    public function getCharsets(string $column = ''): array
    {
        return [];
    }

    /**
     * Возвращает названия сопоставлений (collation) наборов символов, используемых 
     * сервером базы данных.
     * 
     * @param string $column Столбец, указывающий на название сопоставления.
     * 
     * @return array<int|string, array<string, mixed>>
     */
    public function getCollations(string $column = ''): array
    {
        return [];
    }

    /**
     * Проверяет, сущестует ли указанная таблица.
     * 
     * @param string $table Имя таблицы в базе данных.
     * @param string|null $schema Имя (схема) базе данных.
     * 
     * @return bool
     */
    public function tableExists(string $table, ?string $schema = null): bool
    {
        return false;
    }

    /**
     * Возвращает статус таблицы.
     * 
     * @param string $table Имя таблицы в базе данных.
     * 
     * @return mixed
     */
    public function getTableStatus(string $table): mixed
    {
        return null;
    }

    /**
     * Возвращает автоинкремент из статуса таблицы.
     * 
     * @return int|string
     */
    public function getIncrement(string $table): int|string
    {
        return 0;
    }
}
