<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db;

use Ge;
use Closure;
use Ge\Db\Sql\Where;
use Ge\Db\Sql\Select;
use Ge\Data\Model\AbstractModel;
use Ge\Db\Adapter\Driver\AbstractCommand;
use Ge\Db\Adapter\Exception\CommandException;

/**
 * ActiveRecord является базовым классом для классов, представляющих реляционные 
 * данные в моделях и объектах.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db
 * @since 2.0
 */
class ActiveRecord extends AbstractModel
{
    /**
     * @var string Событие, возникшее перед сохранением.
     */
    public const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @var string Событие, возникшее после сохранения.
     */
    public const EVENT_AFTER_SAVE = 'afterSave';

    /**
     * @var string Событие, возникшее после успешного получения записи по запросу.
     */
    public const EVENT_AFTER_SELECT = 'afterSelect';

    /**
     * @var string Событие, возникшее перед обновлением записи.
     */
    public const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * @var string Событие, возникшее перед добавлением записи.
     */
    public const EVENT_BEFORE_INSERT = 'beforeInsert';

    /**
     * @var string Событие, возникшее перед удалением записи.
     */
    public const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @var string Событие, возникшее после удаления записи.
     */
    public const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * Название атрибутов, сеттеры которых необходимо исключить.
     * 
     * Например, для `['id' => true, 'name' => true]` не будут вызываться 
     * методы: `setId()`, `setName()`.
     * 
     * @var array
     */
    public array $excludeSetters = [];

    /**
     * Название атрибутов, геттеры которых необходимо исключить.
     * 
     * Например, для `['id' => true, 'name' => true]` не будут вызываться 
     * методы: `getId()`, `getName()`.
     * 
     * @var array
     */
    public array $excludeGetters = [];

    /**
     * @var bool
     */
    protected bool $checkDirtyAttributes = true;

    /**
     * Последний результат выполнения операции над записью.
     *
     * Результат содержит:
     * - количество удаленных записей {@see ActiveRecord::deleteRecord()};
     * - идентификатор добавленной записи {@see ActiveRecord::insertRecord()};
     * - количество изменённых записей {@see ActiveRecord::updateRecord()}.
     * Если ошибка, значение `false`.
     *
     * @return false|int|string|null
     */
    protected false|int|string|null $result = null;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!isset($this->db)) {
            $this->db = $this->getDb();
        }
    }

    /**
     * Сохраняет значения (идентифицированные с ключами) в кэш, полученные в результате 
     * выполнения SQL-запроса к указанной таблице.
     * 
     * Если кэш уже содержит такой ключ, тогда возвратит его значение.
     * Где ключ - значение, полученное из имени таблицы {@see AbstractModel::tableName()}.
     * 
     * Для хранения записей в кэше полученных в результате SQL-запроса используется
     * хэш-таблица кэша {@see \Ge\Cache\HashTable}.
     * 
     * @param callable $callback Callback-функция должна вернуть кэшируемое значение.
     * @param string|array|null $name Имя (ключ) кэша состоит из имени таблицы {@see AbstractModel::tableName()}  и 
     *     маркера SQL-запроса (слово или фраза описывающая запрос) к ней. По умолчанию 
     *     маркер '' (не используется). Имя можно указать как:
     *     - `['table_name', 'marker']` array;
     *     - `['table_name']` array;
     *     - `'table_name'` string;
     *     - `null`, будет иметь вид: `[{@see AbstractModel::tableName()}, '']`.
     * @param bool $useArray
     * @param int $expiry Продолжительность, по умолчанию в секундах до истечения срока 
     *    действия кэша. По умолчанию '0' (без ограничений).
     * 
     * @return mixed Значение хранящееся в кэше или результат выполнения SQL-запроса.
     */
    public function cache(callable $callback, string|array|null $name = null, bool $useArray = true, int $expiry = 0)
    {
        /** @var \Ge\Cache\Cache $cache */
        $cache = Ge::$services->getAs('cache');
        // если кэширование доступно
        if ($cache->enabled) {
            return $cache
                ->getHashTable($name ?: [$this->tableName(), ''])
                ->useArray($useArray)
                ->expiry($expiry)
                ->getOrSet($callback);
        }
        return $callback();
    }

    /**
     * Удаляет ключ кэша с соответствующем ему массивом записей, полученных при выполнении 
     * SQL-запроса к текущей таблице.
     * 
     * Для хранения записей в кэше полученных в результате SQL-запроса используется
     * хэш-таблица кэша {@see \Ge\Cache\HashTable}.
     * 
     * @param string|array $name Имя (ключ) кэша состоит из имени таблицы {@see AbstractModel::tableName()}  и 
     *     маркера SQL-запроса (слово или фраза описывающая запрос) к ней. По умолчанию 
     *     маркер '' (не используется). Имя можно указать как:
     *     - `['table_name', 'marker']` array;
     *     - `['table_name']` array;
     *     - `'table_name'` string;
     *     - `null`, будет иметь вид: `[{@see AbstractModel::tableName()}, '']`.
     * 
     * @return bool Значение `false`, если во время удаления кэша произошла ошибка.
     */
    public function flushCache(string|array|null $name = null): bool
    {
        /** @var \Ge\Cache\Cache $cache */
        $cache = Ge::$services->getAs('cache');
        // если кэширование доступно
        if ($cache->enabled) {
            return $cache
                ->getHashTable($name ?: [$this->tableName(), ''])
                ->flush();
        }
        return false;
    }

    /**
     * Возвращает последний результат выполнения операции над записью.
     * 
     * @return false|int|string|null
     */
    public function getResult(): false|int|string|null
    {
        return $this->result;
    }

    /**
     * Возвращает условия зависимостей для удаления записей.
     * 
     * @return array
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * Создаёт объект ActiveRecord.
     * 
     * @param array $config Параметры конфигурации объекта в виде пар "имя-значение", 
     *     которые будут использоваться для инициализации свойств объекта.
     *
     * @return $this
     */
    public static function factory(array $config = []): static
    {
        return new static($config);
    }

    /**
     * Устанавливает атрибуты созданной активной записи.
     * 
     * Применяется в методах выборки записей: {@see ActiveRecord::selectOne()}, 
     * {@see ActiveRecord::selectAll()}.
     * 
     * @see ActiveRecord::setPopulateAttributes()
     * @see ActiveRecord::setOldAttributes()
     * @see ActiveRecord::setValuePrimaryKeyFromRow()
     * 
     * @param ActiveRecord $record Запись, которой устанавливают атрибуты.
     * @param array<string, mixed> $row Атрибуты записи.
     * 
     * @return ActiveRecord
     */
    public static function populate(ActiveRecord $record, array $row): ActiveRecord
    {
        $record->setPopulateAttributes($row);
        $record->setOldAttributes($record->attributes);
        // автоматически добавляем значение первичного ключа 
        // в старые атрибуты модели. Он необходим для изменения записи при
        // вызове {@see valuePrimaryKey()}.
        $record->setValuePrimaryKeyFromRow($row);
        return $record;
    }

    /**
     * {@inheritdoc}
     */
    public function setPopulateAttributes(array $attributes, bool $safeOnly = true): static
    {
        parent::setPopulateAttributes($attributes, $safeOnly);

        foreach ($this->attributes as $name => $value) {
            $setter = 'set' . $name;
            if (!isset($this->excludeSetters[$name]) && method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes, bool $safeOnly = true): static
    {
        parent::setAttributes($attributes, $safeOnly);

        foreach ($this->attributes as $name => $value) {
            $setter = 'set' . $name;
            if (!isset($this->excludeSetters[$name]) && method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        return $this;
    }

    /**
     * Устанавливает значения первичного ключа из указанных атрибутов.
     * 
     * @see ActiveRecord::primaryKey()
     * @see ActiveRecord::setOldAttribute()
     * 
     * @param array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение".
     * 
     * @return void
     */
    public function setValuePrimaryKeyFromRow(array $attributes): void
    {
        $primaryKey = $this->primaryKey();
        if ($primaryKey && isset($row[$primaryKey])) {
            $this->setOldAttribute($primaryKey, $attributes[$primaryKey]);
        }
    }

    /**
     * Устанавливает значение первичному ключу.
     * 
     * @see ActiveRecord::primaryKey()
     * @see ActiveRecord::setOldAttribute()
     * 
     * @param mixed $value Значение первичного ключа.
     * 
     * @return void
     */
    public function setValuePrimaryKey(mixed $value): void
    {
        $primaryKey = $this->primaryKey();
        if ($primaryKey) {
            $this->setOldAttribute($primaryKey, $value);
        }
    }

    /**
     * Возвращает значение первичного ключа.
     * 
     * @see ActiveRecord::primaryKey()
     * @see ActiveRecord::getOldAttribute()
     * 
     * @return mixed Значение `null`, если ключ не существует.
     */
    public function valuePrimaryKey(): mixed
    {
        $primaryKey = $this->primaryKey();
        return $primaryKey ? $this->getOldAttribute($primaryKey) : null;
    }

    /**
     * Проверяет, установлен ли первичный ключ.
     * 
     * @see ActiveRecord::primaryKey()
     * 
     * @return bool Значение `false`, если ключ не установлен.
     */
    public function hasPrimaryKey(): bool
    {
        $primaryKey = $this->primaryKey();
        return $primaryKey ? isset($this->oldAttributes[$primaryKey]) : false;
    }

    /**
     * Возвращает оператор Select (выборки) инструкции SQL.
     * 
     * @param array<int, string> $columns Столбцы текущей таблицы. Если столбцы не  
     *     указаны, то по умолчанию будет значение `['*']` (все столбцы).
     * @param Where|Closure|string|array|null $where Условие выполнения запроса (по умолчанию `null`).
     * @param string|array|null $order Порядок сортировки, например: `['field' => 'ASC', 'field1' => 'DESC']` 
     *     (по умолчанию `null`).
     * 
     * @return Select Оператор Select (выборки) инструкции SQL.
     */
    public function select(
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null,
        string|array|null $order = null
    ): Select
    {
        $select = new Select($this->tableName());
        $select->columns($columns);
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        return $select;
    }

    /**
     * Возвращает только одну активную запись по указанному значению первичного ключа.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param mixed $value Значение первичного ключа таблицы.
     * 
     * @return ActiveRecord|null Активная запись при успешном запросе, иначе `null`.
     */
    public function selectByPk(mixed $value): ?ActiveRecord
    {
        return $this->selectOne([$this->primaryKey() => $value]);
    }

    /**
     * Возвращает только одну активную запись по указанному условию запроса.
     * 
     * @param Select|Where|Closure|string|array $selectOrWhere Оператор Select (выборки) 
     *     инструкции SQL или условие запроса.
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function selectOne(Select|Where|Closure|string|array $selectOrWhere): ?ActiveRecord
    {
        if ($selectOrWhere instanceof Select)
            $select = $selectOrWhere;
        else
            $select = $this->select(['*'], $selectOrWhere);

        /** @var array|null $row */
        $row = $this->db
                ->createCommand($select)
                    ->queryOne();
        if ($row) {
            $this->reset();
            $this->afterSelect();
            $this->populate($this, $row);
            $this->afterPopulate();
            return $this;
        }
        return null;
    }

    /**
     * Возвращает активные записи полученные по указанному условию запроса.
     * 
     * @param Select|Where|Closure|string|array|null $selectOrWhere Оператор Select (выборки) 
     *     инструкции SQL или условие запроса.
     * 
     * @return array<int, ActiveRecord> Активные записи.
     */
    public function selectAll(Select|Where|Closure|string|array|null $selectOrWhere = null): array
    {
        if ($selectOrWhere instanceof Select)
            $select = $selectOrWhere;
        else
            $select = $this->select(['*'], $selectOrWhere);

        /** @var AbstractCommand $command */
        $command = $this->db->createCommand($select);
        $command->query();
        $records = [];
        while ($row = $command->fetch()) {
            $record = static::factory();
            $record->afterSelect();
            $records[] = static::populate($record, $row);
        }
        return $records;
    }

    /**
     * Возвращает все записи текущей таблицы с указанием ключа.
     * 
     * Результирующий набор определяется {@see \Ge\Db\Adapter\Driver\AbstractCommand::$fetchMode}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @param string|null $fetchKey Ключ возвращаемого ассоциативного массива записей. Если `null`, 
     *     результатом будет индексированный массив записей (по умолчачнию `null`).
     * @param array $columns Столбцы выборки текущей таблицы. Если вы не укажете столбцы 
     *     для выборки, то по умолчанию будет подставлено значение `['*']` (означающее "все столбцы"). 
     * @param Where|Closure|string|array|null $where Условие выполнения запроса (по умолчанию `null`).
     * @param string|array|null $order Порядок сортировки, например: `['field' => 'ASC', 'field1' => 'DESC']` 
     *     (по умолчанию `null`).
     * 
     * @return array Все записи текущей таблицы.
     */
    public function fetchAll(
        ?string $fetchKey = null, 
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null, 
        string|array|null $order = null
    ): array
    {
        /** @var Select $select */
        $select = $this->select($columns, $where);
        if ($order) {
            $select->order($order);
        }
        return $this->db
                ->createCommand($select)
                    ->queryAll($fetchKey);
    }

    /**
     * Возвращает записи текущей таблицы по указанным значениям первичного ключа.
     * 
     * Результирующий набор определяется {@see \Ge\Db\Adapter\Driver\AbstractCommand::$fetchMode}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @param mixed $keys Значение или значения первичного ключа.
     * @param null|string $fetchKey Ключ возвращаемого ассоциативного массива записей. Если `null`, 
     *     результатом будет индексированный массив записей (по умолчачнию `null`).
     * @param array $columns Столбцы выборки текущей таблицы. Если вы не укажете столбцы 
     *     для выборки, то по умолчанию будет подставлено значение `['*']` (означающее "все столбцы"). 
     * 
     * @return array
     */
    public function fetchByPk(mixed $keys, ?string $fetchKey = null, array $columns = ['*']): array
    {
        /** @var Select $select */
        $select = $this->select($columns, [$this->primaryKey() => $keys]);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryAll($fetchKey);
    }

    /**
     * Возвращает запись текущей таблицы по указанному значению первичного ключа.
     * 
     * Результирующий набор определяется {@see \Ge\Db\Adapter\Driver\AbstractCommand::$fetchMode}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @param mixed $key Значение первичного ключа.
     * 
     * @return mixed
     */
    public function fetchOne(mixed $key): mixed
    {
        /** @var Select $select */
        $select = $this->select($this->maskedAttributes() ?: ['*'], [$this->primaryKey() => $key]);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в объект, ассоциативный 
     * массив, обычный массив или в оба, и связывает их с указанным ключем. Результат 
     * выбранных строк группируется по значениям ключа группы.
     * 
     * Результирующий набор определяется {@see \Ge\Db\Adapter\Driver\AbstractCommand::$fetchMode}.
     * По умолчанию `PDO::FETCH_ASSOC`.
     * 
     * @see \Ge\Db\Adapter\Driver\AbstractCommand::fetchToGroups()
     * 
     * @param string $groupKey Ключ для группирования результата выбранных строк.
     * @param null|string $fetchKey Ключ возвращаемого ассоциативного массива результирующего 
     *     набора. Если `null`, результатом будет индексированный массив результирующего 
     *     набора (по умолчачнию `null`).
     * @param array $columns Столбцы выборки текущей таблицы. Если вы не укажете столбцы 
     *     для выборки, то по умолчанию будет подставлено значение `['*']` (означающее "все столбцы"). 
     * @param string|array|null $order Порядок сортировки, например: `['field' => 'ASC', 'field1' => 'DESC']` 
     *     (по умолчанию `null`).
     * 
     * @return array
     */
    public function fetchToGroups(
        string $groupKey, 
        ?string $fetchKey = null, 
        array $columns = ['*'], 
        string|array|null $order = null
    ): array
    {
        /** @var Select $select */
        $select = $this->select($columns);
        if ($order)
            $select->order($order);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->query()
                    ->fetchToGroups($groupKey, $fetchKey);
    }

    /**
     * Проверяет, есть ли записи по указанному условию выполнения запроса.
     * 
     * @param Where|Closure|string|array $where Условие выполнения запроса.
     * @param string $combination
     * 
     * @return bool Если `false`, по указанному условию выполнения запроса нет записей.
     */
    public function exists(
        Where|Closure|string|array $where, 
        string $combination = 'AND'
    ): bool
    {
        $select = new Select($this->tableName());
        $select
            ->columns([$this->primaryKey()])
            ->where($where, $combination)
            ->limit(1);
        $row = $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
        // не учитывать с
        if ($row) {
            if ($id = $this->valuePrimaryKey()) {
                if ($row[$this->primaryKey()] == $id) {
                    $row = false;
                }
            }
        }
        return $row ? true : false;
    }

    /**
     * Сохраняет значения атрибутов текущей записи.
     * 
     * Если значения атрибутов записи изменились, то будет вызван метод {@see ActiveRecord::update()}.
     * Иначе, метод {@see ActiveRecord::insert()}.
     *
     * @param bool $useValidation Выполнять проверку значений атрибутов (по умолчанию `false`).
     * @param null|array $attributeNames Имена атрибутов, значения которых необходимо сохранить.
     *     Если `null`, значения всех атрибутов будут сохранены (по умолчанию `null`).
     * 
     * @return bool|int|string Результат изменения значений атрибутов записи:
     *     - для update, если `false`, ошибка выполнения запроса SQL. Иначе, количество 
     *     изменённых записей;
     *     - для insert, если `false`, ошибка выполнения запроса SQL. Значение `true` (если составной первичный ключ), 
     *    запрос SQL был успешно выполнен. Иначе, идентификатор добавленной записи.
     */
    public function save(bool $useValidation = false, ?array $attributeNames = null): bool|int|string
    {
        if ($this->IsNewRecord()) {
            $result = $this->insert($useValidation, $attributeNames);
            // если составной первичный ключ
            return $result === 0 ? true : $result;
        } else
            return $this->update($useValidation, $attributeNames) !== false;
    }

    /**
     * Удаляет запись(и) по значению первичного ключа таблицы.
     * 
     * @see ActiveRecord::deleteRecord()
     * 
     * @param mixed $value Значение первичного ключа.
     * 
     * @return false|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteByPk(mixed $value): false|int
    {
        return $this->deleteRecord([$this->primaryKey() => $value]);
    }

    /**
     * Удаляет текущую запись.
     * 
     * @see ActiveRecord::deleteProcess()
     * 
     * @return false|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function delete(): false|int
    {
        return $this->deleteProcess();
    }

    /**
     * Подготавливает и удаляет запись.
     * 
     * @see ActiveRecord::delete()
     * 
     * @return false|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    protected function deleteProcess(): false|int
    {
        $this->result = false;
        if ($this->beforeDelete()) {
            // условие запроса удаления записи
            $where = [];
            $this->deleteProcessCondition($where);
            $this->deleteDependencies($where);
            $this->result = $this->deleteRecord($where);
            // сброс атрибутов записи
            $this->attributes = [];
            $this->oldAttributes = [];
            $this->afterDelete($this->result);
        }
        return $this->result;
    }

    /**
     * Удаление зависимых записей по условию.
     * 
     * @see ActiveRecord::deleteProcess()
     * 
     * @param mixed $condition Условие запроса.
     * 
     * @return void
     */
    protected function deleteDependencies(mixed $condition): void
    {
    }

    /**
     * Процесс подготовки условий для удаления записи.
     * 
     * @see ActiveRecord::deleteProcess()
     * 
     * @param array $where Условие удаления записи.
     * 
     * @return void
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where[$this->primaryKey()] = $this->valuePrimaryKey();
    }

    /**
     * Удаляет запись или записи из таблицы по условию.
     * 
     * @param Where|Closure|string|array $where Условие удаления записи или записей.
     * 
     * @return false|int Значение `false`, если ошибка выполнения инструкции SQL. 
     *     Иначе количество удалённых записей.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function deleteRecord(Where|Closure|string|array $where): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->db->createCommand();
        $command
            ->delete($this->tableName(), $where)
            ->execute();
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * Изменяет текущую запись.
     * 
     * @see ActiveRecord::validate()
     * @see ActiveRecord::updateProcess()
     * 
     * @param bool $useValidation Выполнять проверку атрибутов (по умолчанию `false`).
     * @param null|array<string, mixed> $attributes Атрибуты записи со значениями в 
     *     виде пар "ключ - значение", которые необходимо изменить. Если значение 
     *     `null`, то будут применяться текущие атриубты (по умолчанию `null`).
     * 
     * @return false|int Значение `false`, если ошибка выполнения инструкции SQL или 
     *     проверки атрибутов. Иначе количество изменённых записей.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function update(bool $useValidation = false, ?array $attributes = null): false|int
    {
        if ($useValidation && !$this->validate($attributes)) {
            return false;
        }
        return $this->updateProcess($attributes);
    }

    /**
     * Подготавливает запись к изменению.
     * 
     * @param null|array<string, mixed> $attributes Атрибуты записи со значениями в виде 
     *     пар "ключ - значение", которые необходимо изменить. Если значение `null`, то 
     *     будут применяться текущие атриубты (по умолчанию `null`).
     * 
     * @return false|int Значение `false`, если ошибка выполнения запроса. Иначе, 
     *     количество изменённых записей.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    protected function updateProcess(?array $attributes = null): false|int
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        // условие запроса обновление записи
        $where = [];
        $this->updateProcessCondition($where);

        if ($this->checkDirtyAttributes) {
            // возвращает только те атрибуты, которые были изменены
            $dirtyAttributes = $this->getDirtyAttributes($attributes);
            if (empty($dirtyAttributes)) {
                $this->afterSave(false);
                return 0;
            }
        } else
            $dirtyAttributes = $this->attributes;

        // возвращает атрибуты без псевдонимов (если они были указаны)
        $columns = $this->unmaskedAttributes($dirtyAttributes);
        if (empty($columns)) {
            $this->afterSave(false);
            return 0;
        }

        // дополнительные атрибуты
        $append = $this->appendAttributes(false);
        if ($append) {
            $columns = array_merge($columns, $append);
        }

        $this->beforeUpdate($columns);

        // изменение записи
        $this->result = $this->updateRecord($columns, $where);
        $this->setOldAttributes($this->attributes);
        $this->afterSave(false, $columns, $this->result);
        return $this->result;
    }

    /**
     * Условие в подготовке к изменению записи.
     * 
     * @see ActiveRecord::updateProcess()
     * 
     * @param array $where Условие изменения записи или записей.
     * 
     * @return void
     */
    protected function updateProcessCondition(array &$where): void
    {
        $where[$this->primaryKey()] = $this->valuePrimaryKey();
    }

    /**
     * Изменяет запись или записи в таблице по условию.
     * 
     * @param array<string, mixed> $attributes Атрибуты записи со значениями в виде 
     *     пар "ключ - значение", которые необходимо изменить.
     * @param Where|Closure|string|array|null $where Условие изменения записи или записей 
     *     (по умолчанию `null`).
     * 
     * @return false|int Значение `false`, если ошибка выполнения запроса. Иначе, 
     *     количество изменённых записей.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function updateRecord(array $attributes, Where|Closure|string|array|null $where = null): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->db->createCommand();
        $command->update($this->tableName(), $attributes, $where);
        $command->execute();
        
        if ($command->getResult() === true) {
            $count = $command->getAffectedRows();
            return is_int($count) ? $count : 1;
        }
        return false;
    }

    /**
     * Добавляет запись.
     * 
     * @see ActiveRecord::validate()
     * @see ActiveRecord::insertProcess()
     * 
     * @param bool $useValidation Выполнять проверку атрибутов (по умолчанию `false`).
     * @param null|array<string, mixed> $attributes Атрибуты записи со значениями в 
     *     виде пар "ключ - значение", которые необходимо добавить. Если значение 
     *     `null`, то будут применяться текущие атриубты (по умолчанию `null`).
     * 
     * @return false|int|string Значение `false`, если ошибка выполнения инструкции SQL или 
     *     проверки атрибутов. Иначе '0', если инструкция SQL успешно выполнена (для 
     *     составного первичного ключа) или идентификатор добавленной записи.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function insert(bool $useValidation = false, ?array $attributes = null): false|int|string
    {
        if ($useValidation && !$this->validate($attributes)) {
            return false;
        }
        return $this->insertProcess($attributes);
    }

    /**
     * Подготавливает запись к добавлению.
     * 
     * @see ActiveRecrod::insert()
     * 
     * @param null|array<string, mixed> $attributes Атрибуты записи со значениями в 
     *     виде пар "ключ - значение", которые необходимо добавить. Если значение 
     *     `null`, то будут применяться текущие атриубты (по умолчанию `null`).
     * 
     * @return false|int|string Значение `false`, если ошибка выполнения инструкции SQL или 
     *     проверки атрибутов. Иначе '0', если инструкция SQL успешно выполнена (для 
     *     составного первичного ключа) или идентификатор добавленной записи.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    protected function insertProcess(array $attributes = null): false|int|string
    {
        if (!$this->beforeSave(true)) {
            return false;
        }

        // возвращает атрибуты без псевдонимов (если они были указаны)
        $columns = $this->unmaskedAttributes($this->attributes);

        // дополнительные атрибуты
        $append = $this->appendAttributes(true);
        if ($append) {
            $columns = array_merge($columns, $append);
        }

        $this->beforeInsert($columns);

        // добавление записи
        $this->result = $this->insertRecord($columns);
        $this->afterSave(true, $columns, $this->result);
        return $this->result;
    }

    /**
     * Добавляет запись в таблицу.
     * 
     * @param array<string, mixed> $columns Атрибуты записи со значениями в виде 
     *     пар "ключ - значение", которые необходимо добавить.
     * 
     * @return int|string Значение сгенерированного автоинкремента первичного ключа 
     *     таблицы. Если первичный ключ составной, то значение '0'.
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function insertRecord(array $columns): int|string
    {
        /** @var AbstractCommand $command */
        $command = $this->db->createCommand();
        $command
            ->insert($this->tableName(), $columns)
            ->execute();
        return $this->db->getConnection()->getLastGeneratedValue();
    }

    /**
     * Возвращает автоинкремент из статуса таблицы.
     * 
     * @return int
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function getNextId(): int|string
    {
        /** @var AbstractCommand $command */
        $command = $this->db->createCommand();
        return $command
            ->getIncrement($this->tableName());
    }

    /**
     * Возвращает значение, указывающее, является ли текущая запись новой.
     * 
     * @see ActiveRecord::$oldAttributes
     * 
     * @return bool Значение `true`, если текущая запись является новой.
     */
    public function isNewRecord(): bool
    {
        return empty($this->oldAttributes);
    }

    /**
     * {@inheritdoc}
     * 
     * @param bool $gettering Если значение `true`, то будет применяться метод атрибута 
     *     для возвращения его значения. Например, если название атрибута 'profile', то
     *     метода атрибута `unProfile()`. Метод для атрибута применяется в том случаи, 
     *     если тип значения атрибута отличается от типа значения сохраняемого в базу данных.
     */
    public function unmaskedAttributes(array $attributes, bool $gettering = true): array
    {
        $mask = $this->maskedAttributes();
        if ($mask) {
            $result = [];
            foreach ($attributes as $alias => $value) {
                if (isset($mask[$alias])) {
                    if ($gettering) {
                        $getter = 'un' . $alias;
                        if (method_exists($this, $getter)) {
                            $result[$mask[$alias]] = $this->$getter();
                            continue;
                        }
                    }
                    $result[$mask[$alias]] = $value;
                }
            }
            return $result;
        }
        return $attributes;
    }

    /**
     * Возвращает атрибуты со значениями, полученные при замене атрибутов текущей 
     * записи на "старые" (с первоначальными значениями).
     * 
     * @see ActiveRecord::$attributes
     * @see ActiveRecord::$oldAttributes
     * 
     * @return array<string, mixed> Атрибуты со значениями в виде пар "ключ - значение".
     */
    public function loadDefaultValues(): array
    {
        return $this->attributes = $this->oldAttributes;
    }

    /**
     * Cобытие вызываемое перед добавлением или изменением записи.
     * 
     * @see ActiveRecord::insertProcess()
     * @see ActiveRecord::updateProcess()
     * 
     * @param bool $isInsert Значение `true` указывает на добавление записи, иначе, 
     *     изменение.
     * 
     * @return bool Значение `true` указывает на продолжение добавления или изменения 
     *     записи, иначе, событие будет прервано.
     */
    public function beforeSave(bool $isInsert): bool
    {
        return true;
    }

    /**
     * Cобытие вызываемое после добавления или изменения записи.
     * 
     * @see ActiveRecord::insertProcess()
     * @see ActiveRecord::updateProcess()
     * 
     * @param bool $isInsert Значение `true` указывает на добавление записи, иначе, 
     *     изменение.
     * @param null|array<string, mixed> $attributes Атрибуты записи (без маски) со 
     *     значениями в виде пар "ключ - значение", которые необходимо добавить или 
     *     изменить. Если значение `null`, то атрибуты не будут применяться  (по 
     *     умолчанию `null`).
     * @param false|int|string|null $result Результат добавления или изменения записи.
     * 
     * @return void
     */
    public function afterSave(
        bool $isInsert, 
        array $attributes = null, 
        false|int|string|null $result = null
    ): void
    {
    }

    /**
     * Метод вызываемый перед изменением записи.
     * 
     * Возможность изменить атрибуты записи перед их сохранением в базу данных.
     * 
     * @see ActiveRecord::updateProcess()
     * 
     * @param array<string, mixed> $attributes Атрибуты записи (без маски) со 
     *     значениями в виде пар "ключ - значение", которые будут сохранены.
     * 
     * @return void
     */
    public function beforeUpdate(array &$attributes): void
    {
    }

    /**
     * Метод вызываемый перед добавлением записи.
     * 
     * Возможность изменить атрибуты записи перед их добавлением в базу данных.
     * 
     * @see ActiveRecord::insertProcess()
     * 
     * @param array<string, mixed> $columns Атрибуты записи (без маски) со 
     *     значениями в виде пар "ключ - значение", которые будут добавлены.
     * 
     * @return void
     */
    public function beforeInsert(array &$columns): void
    {
    }

    /**
     * Cобытие вызываемое перед удалением записи.
     * 
     * @see ActiveRecord::deleteProcess()
     * 
     * @return bool Значение `true` указывает на продолжение удаления записи.
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Событие вызываемое после удаления записи.
     * 
     * @see ActiveRecord::deleteProcess()
     * 
     * @param false|int|null $result Значение `false`, если ошибка выполнения инструкции SQL. 
     *     Иначе количество удалённых записей.
     * 
     * @return void
     */
    public function afterDelete(false|int|null $result = null): void
    {
    }

    /**
     * Событие вызывается только после успешного получения записи по запросу.
     * 
     * Необходимо вызывать перед методом {@see ActiveRecord::populate()}.
     * 
     * @see ActiveRecord::selectOne()
     * @see ActiveRecord::selectAll()
     *
     * @return void
     */
    public function afterSelect(): void
    {
    }

    /**
     * Событие возникает после установки атрибутов активной записи.
     * 
     * Необходимо вызывать после метода {@see ActiveRecord::populate()}.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @return void
     */
    public function afterPopulate(): void
    {
    }

    /**
     * Проверяет существование ключа (магический метод).
     * 
     * @param mixed $key Ключ.
     * 
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Устанавливает значения по указанному ключу (магический метод).
     * 
     * @param string $key Ключ.
     * @param mixed $value Значение.
     * 
     * @return void
     */
    public function __set($key, $value)
    {
        $setter = 'set' . $key;
        if (!isset($this->excludeSetters[$key]) && method_exists($this, $setter)) {
            $this->$setter($key, $value);
        } else
            $this->attributes[$key] = $value;
    }

    /**
     * Удаляет значения по указанному ключу (магический метод).
     * 
     * @param string $key Ключ.
     * 
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Возвращает значения по указанному ключу (магический метод).
     * 
     * @param string $key Ключ.
     * 
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            // только для атрибута
            $getter = 'get' . $key;
            if (!isset($this->excludeGetters[$key]) && method_exists($this, $getter)) {
                return $this->$getter($key);
            } else
                return $this->attributes[$key];
        }
    }
}