<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data\Model;

use Ge;
use Ge\Exception;
use Ge\Db\Sql;
use Ge\Db\Adapter\Driver\AbstractCommand;
use Ge\Helper\Json;

/**
 * Модель сетки данных (при взаимодействии с 
 * представлением, использующий компонент Ge.view.grid.Grid GeJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Panel\Data\Model
 * @since 1.0
 */
class ListModel extends BaseModel
{
    /**
     * @var string Событие, возникшее после успешного получения записи по запросу.
     * 
     * @see ListModel::selectOne()
     * @see ListModel::selectAll()
     */
    const EVENT_AFTER_SELECT = 'afterSelect';

    /**
     * @var string Событие, возникшее перед удалением записи.
     * 
     * @see ListModel::deleteRecord()
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @var string Событие, возникшее после удаления записи.
     * 
     *  @see ListModel::deleteRecord()
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @var string Событие, перед после.
     */
    const EVENT_BEFORE_SET_FILTER = 'beforeSetFilter';

    /**
     * @var string Событие, процесс установки фильтра.
     */
    const EVENT_ON_SET_FILTER = 'onSetFilter';

    /**
     * @var string Событие, возникшее после.
     */
    const EVENT_AFTER_SET_FILTER = 'afterSetFilter';

    /**
     * Количество записей на странице.
     * 
     * @var int
     */
    protected int $limit = 10;

    /**
     * Текущая страница списка.
     * 
     * @var int
     */
    protected int $page = 0;

    /**
     * Индекс начала списка записей.
     * 
     * @var int
     */
    protected int $offset = 0;

    /**
     * Порядок сортировки списка.
     * 
     * @var array
     */
    protected array $order = [];

    /**
     * "Быстрый" фильтр через столбец
     * 
     * @var array
     */
    protected array $fastFilter = [];

    /**
     * "Прямой" фильтр по запросу
     * 
     * @var array
     */
    protected array $directFilter = [];


    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        /** @var \Ge\Http\Request $request */
        $request = Ge::$app->request;

        // количество записей на странице
        $this->limit = $request->getPost('limit', $this->limit, 'int');
        // определение вида сортировки полей
        $this->order = $this->defineOrder($request->getPost('sort'), $this->order);
        // индекс начала списка записей
        $this->offset = $request->getPost('start', $this->offset, 'int');
    }

    /**
     * Определяет параметры сортировки списка (переданные в формате JSON).
     *
     * @param string|array $orderJson Параметры сортировки в формате JSON.
     *
     * @return array Параметры сортировки списка.
     */
    protected function defineOrder(string|array $orderJson, array $default = []): array
    {
        if (empty($orderJson)) {
            return $default;
        }
        $order = [];
        /* если $orderJson маска полей с видом сортировки 
         * имеет вид: array("alias1" => "asc", "alias2" => "desc"...) 
         */
        if (is_array($orderJson)) {
            foreach($orderJson as $alias => $direction) {
                $field = $this->dataManager->getFullField($alias);
                if ($field !== null)
                    $order[$field] = $direction;
            }
            return $order;
        }
        
        try {
            $orderDecode = Json::decode($orderJson);
            $error = Json::error();
            if ($error)
                throw new Exception\JsonFormatException('Could not get sort type list');
        } catch(\Exception $e) {
            Ge::error($e->getMessage());
        }
        if ($orderDecode) {
            foreach($orderDecode as $item) {
                $property = isset($item['property']) ? $item['property'] : false;
                $direction = isset($item['direction']) ? strtoupper($item['direction']) : false;
                // если параметры были переданы не правильно
                if (empty($property) || empty($direction)) {
                    throw new Exception\InvalidArgumentException('Unable to sort the list, there are no property or sort type');
                }
                $options = $this->dataManager->getFieldOptions($property);
                // если нет опций для сортирумего поля
                if ($options === null) {
                    throw new Exception\InvalidArgumentException('Unable to sort the list, there are no field options for the property "' . $property . '"');
                }
                // проверка существования 
                if (isset($options['direct']))
                    $field = $options['direct'];
                else
                    $field = $options['field'];
                $order[$field] = $direction;
            }
            return $order;
        }
        return $order;
    }

    /**
     * Возвращает параметры "быстрого фильтра".
     * 
     * @param null|string $filterJson Фильтр в JSON формате.
     *
     * @return array
     */
    protected function defineFastFilter(?string $filterJson): array
    {
        if (empty($filterJson)) {
            return [];
        }
        $filter = [];
        try {
            $filter = Json::decode($filterJson);
            $error  = Json::error();
            if ($error)
                throw new Exception\JsonFormatException();
        } catch(\Exception $e) {
            Ge::error($e->getMessage());
        }
        return $filter;
    }

    /**
     * Возвращает параметры "прямого фильтра".
     * 
     * @return array
     */
    public function defineDirectFilter(): array
    {
        $store = $this->module->getStorage();
        if ($store->directFilter !== null) {
            $modelName = $this->getName();
            // если есть фильтр для конкретной модели данные (т.к. в настройках компонента, может быть несколько списков с фильтрами)
            if (isset($store->directFilter[$modelName]))
                return $store->directFilter[$modelName];
        }
        return [];
    }

    /**
     * Установка "прямого фильтра".
     * 
     * @return void
     */
    public function setDirectFilter(): void
    {
        /** @var \Ge\Http\Request $request */
        $request = Ge::$app->request;
        $store   = $this->module->getStorage();

        // если фильтр не создан
        if ($store->directFilter === null) {
            $store->directFilter = [];
        }
        $directFilter = $store->directFilter;
        $filter = [];

        $this->beforeSetFilter();
        // если менеджер данных имеет опцию "filter",
        // для использования "прямого фильтра" или указаны поля аудита записи в самом фильтре.
        if ($this->dataManager->filter || $filter) {
            foreach ($this->dataManager->filter as $key => $params) {
                $value = $request->post($key);
                if ($value === null) continue;
                // валидация значений фильтра
                $value = $this->validateFilterValue($key, $value);
                if ($value === false) continue;
                $filter[] = [
                    'value'    => $value,
                    'property' => $key,
                    'operator' => $params['operator'],
                    'where'    => $params['where'] ?? null
                ];
            }
        }
        $this->onSetFilter($filter);
        // установка фильтра именно для этой модели данных,
        // т.к. в модуле может быть много моделей
        $directFilter[$this->getName()] = $filter;
        $store->directFilter = $directFilter;
        $this->afterSetFilter($filter);
    }

    /**
     * Подготовка к установке "прямого фильтра"
     * 
     * @return void
     */
    protected function beforeSetFilter(): void
    {
        $this->trigger(self::EVENT_BEFORE_SET_FILTER);
    }

    /**
     * Подготовка к установке "прямого фильтра"
     * 
     * @return void
     */
    protected function afterSetFilter(array $filter): void
    {
        $this->trigger(self::EVENT_AFTER_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Процесс установки "прямого фильтра".
     * 
     * @return void
     */
    protected function onSetFilter(array &$filter): void
    {
        $this->trigger(self::EVENT_ON_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Валидация значения при установке {@see setDirectFilter()} "прямого фильтра".
     * 
     * @param string $field Название поля фильтра.
     * @param string $value Значение.
     * 
     * @return mixed Если `false`, значение не проверено.
     */
    protected function validateFilterValue(string $field, mixed $value): mixed
    {
        if (strlen($value) > 0 && $value !== 'null')
            return $value;
        else
            return false;
    }

    /**
     * Проверяет, был ли задействован "быстрый"  (через столбец) фильтр.
     * 
     * @return bool
     */
    public function hasFastFilter(): bool
    {
        return !empty($this->fastFilter);
    }

    /**
     * Проверяет, был ли задействован "прямой"  (по запросу) фильтр.
     * 
     * @return bool
     */
    public function hasDirectFilter(): bool
    {
        $store = $this->module->getStorage();
        if ($store->directFilter !== null) {
            $modelName = $this->getName();
            // если есть фильтр для конкретной модели данные (т.к. в настройках компонента, может быть несколько списков с фильтрами)
            return !empty($store->directFilter[$modelName]);
        }
        return false;
    }

    /**
     * Проверяет, был ли задействован один из фильтров: "быстрый" (через столбец), 
     * "прямой" (по запросу).
     * 
     * @see GridModel::hasFastFilter()
     * @see GridModel::hasDirectFilter()
     * 
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->hasFastFilter() || $this->hasDirectFilter();
    }

    /**
     * Добавление сортировки в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildOrder(Sql\AbstractSql $operator): void
    {
        $operator->order($this->order);
    }

    /**
     * Добавление лимита записей в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildLimit(Sql\AbstractSql $operator): void
    {
        // значение: 0, 1...|null (если null не использовать)
        $operator->limit($this->limit);
    }

    /**
     * Добавление смещение записей в конструктор запроса.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildOffset(Sql\AbstractSql $operator): void
    {
        // значение: 0, 1...|null (если null не использовать)
        $operator->offset($this->offset);
    }

    /**
     * Строит и выполняет инструкцию SQL.
     *
     * @param Sql\AbstractSql $operator Оператор инструкции SQL.
     *
     * @return AbstractCommand
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException
     */
    public function buildQuery(Sql\AbstractSql $operator)
    {
        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        $this->buildOrder($operator);
        $this->buildLimit($operator);
        $this->buildOffset($operator);
        $this->buildFilter($operator);
        /** @var AbstractCommand $command */
        $command = $db->createCommand(
            $operator->getSqlString($db->getPlatform())
        );
        $this->beforeSelect($command);
        $command->query();
        //die($command->getRawSql());
        return $command;
    }

    /**
     * Этот метод вызывается перед выполнением запроса в {@see buildQuery()}.
     * 
     * Возможность изменить конструкцию $command перед выполнением запроса.
     *
     * @param AbstractCommand|null $command
     * 
     * @return void
     */
    public function beforeSelect(mixed $command = null): void
    {
    }

    /**
     * Этот метод вызывается после выполнения запроса в {@see selectBySql()} или в {@see selectAll()}.
     * 
     * @param array $rows Массив записей, как результат запроса.
     * @param AbstractCommand $command
     * 
     * @return array Имеет вид:
     *     [
     *         "total" => 10, // количество записей в запросе
     *         "rows"  => [...] // записи запроса
     *     ]
     */
    public function afterSelect(array $rows, mixed $command = null): array
    {
        $this->trigger(self::EVENT_AFTER_SELECT, ['rows' => $rows, 'command' => $command]);
        return [
            'total' => $command->getFoundRows(),
            'rows'  => $rows
        ];
    }

    /**
     * Выполнение SQL запроса к базе данных.
     * 
     * @see GridModel::afterSelect()
     * 
     * @param string $sql SQL запрос к базе данных.
     *
     * @return array
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException
     */
    public function selectBySql(string $sql): array
    {
        $command = $this->commandBySql($sql);
        $this->beforeFetchRows();
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * Выполнение SQL запроса к базе данных с использованием конструктора запроса {@see buildQuery()}.
     *
     * @param string $sql SQL запрос к базе данных.
     *
     * @return AbstractCommand
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException
     */
    public function commandBySql(string $sql)
    {
        /** @var \Ge\Db\Sql\Query $query */
        $query = $this->builder()->sql($sql);
        /** @var AbstractCommand $command */
        return $this->buildQuery($query);
    }

    /**
     * Возвращает записи из таблицы $tableName с использованием конструктора запроса {@see buildQuery()}.
     *
     * @param null|string $tableName Название таблицы.
     *    Если null, используется менеджер данных.
     * 
     * @return array Результат запроса {@see afterSelect()}.
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException
     * @throws Sql\Exception\InvalidArgumentException
     */
    public function selectAll(string $tableName = null): array
    {
        /** @var \Ge\Db\Sql\Select $select */
        $select = $this->builder()->select($this->dataManager->tableName);
        $select->quantifier(new \Ge\Db\Sql\Expression('SQL_CALC_FOUND_ROWS'));
        $select->columns(['*']);

        /** @var AbstractCommand $command */
        $command = $this->buildQuery($select);
        $this->beforeFetchRows();
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * Выполнение команды адаптера {@see \Ge\Db\Adapter\Adapter} базы данных.
     *
     * @param object $builder Оператор конструктора запросов.
     *
     * @return mixed Выполнение SQL запроса оператора.
     */
    public function command($builder)
    {
        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        return $db->createCommand($builder->getSqlString($db->getPlatform()));
    }

    /**
     * Возвращает указатель на созданный адаптером базы данных конструктор запросов.
     *
     * @return \Ge\Db\Sql\QueryBuilder
     */
    public function builder()
    {
        return $this->getDb()->getQueryBuilder();
    }

    /**
     * Возвращает записи полученные в результате запроса к базе данных из {@see selectBySql()}, {@see selectAll()}.
     *
     * @param null|array|AbstractCommand $receiver Получатель записей.
     *
     * @return array Если $receiver null, возвращает пустой маccив, иначе массив записей запроса.
     */
    public function fetchRows(mixed $receiver = null): array
    {
        $mask = $this->maskedRow();
        // если метод \Ge\Panel\Data\Model\DataModel::maskedRow() будет перегружен, то 
        // будет отсутствовать маска для полей аудита записи, таким образом устраняем ошибку,
        // Хоть изначально, такая маска для полей ранее добавлена через \Ge\Data\DataManager::addLockFields(), 
        // {@see \Ge\Data\DataManager::addAuditFields()}
        if ($this->dataManager->lockRows) {
            $this->dataManager->addLockFieldsToMask($mask);
        }
        if ($this->dataManager->useAudit) {
            $this->dataManager->addAuditFieldsToMask($mask);
        }
        $primaryKey   = $this->dataManager->primaryKey;
        $fieldOptions = $this->dataManager->fieldOptions;
        $useAudit     = $this->dataManager->useAudit && $this->dataManager->canViewAudit();
        $mask[$primaryKey] = $primaryKey;
        $rows = [];
        if ($receiver === null) {
            return $rows;
        }
        while ($row = $receiver->fetch()) {
            if ($this->collectRowsId) {
                $this->rowsId[] = $row[$primaryKey];
            }
            $this->beforeFetchRow($row);
            $row = $this->fetchRow($row);
            if ($row === null) {
                continue;
            }
            if ($mask)
                $row = $this->maskedFetchRow($row, $mask, $fieldOptions);
            // если в конфигурации модели данных указан аудит записей "useAudit" и есть
            // разрешение на просмотр аудита записей, то выполняется обработка столбцов аудита,
            // установка соответствующего часового пояса
            if ($useAudit) {
                $this->auditRow($row);
            }
            $this->prepareRow($row);
            $this->afterFetchRow($row, $rows);
        }
        return $rows;
    }

    /**
     * Возвращает значения $row ввиде маски $mask с использованием рендера
     * (если название рендера указано в параметре поля).
     *
     * @param array $row Имена полей с их значениями, полученные на каждом шаге итерации {@see fetchRow()}.
     * @param array $mask Маска полей {@see maskedRow()}.
     * @param array $options Настройки полей из менеджера данных {@see \Ge\Data\DataManager::$fieldOptions}.
     *
     * @return array Маска полей с их значениями.
     */
    public function maskedFetchRow(array $row, array $mask, array $options): array
    {
        if (empty($row) || empty($mask)) return $row;

        $masked = [];
        foreach ($mask as $alias => $field) {
            $value = isset($row[$field]) ? $row[$field] : null;
            if (isset($options[$alias]['render'])) {
                $render = $options[$alias]['render'];
                $value = $this->{$render}($value, $row, $options[$alias]);
            }
            if ($value !== null) {
                // чтобы следующее поле в маске видело измнения совершенные предыдущем полем,
                // если у поля был параметр "render".
                $row[$field] = $value;
                $masked[$alias] = $value;
            } else {
                $masked[$alias] = $value;
            }
        }
        return $masked;
    }

    /**
     * Подготавливает запись к дальнейшему выводу.
     * 
     * @param array $row Имена полей с их значениями, полученные по маске из {@see maskedFetchRow()}.
     *    Если одно из полей записи имеет значение null, $row его не будет включать. 
     * 
     * @return void
     */
    public function prepareRow(array &$row): void
    {
    }

    /**
     * Возвращает запись запроса для {@see fetchRows()}.
     *
     * @param array $row Запись из запроса {@see beforeFetchRow()}.
     *
     * @return array
     */
    public function fetchRow(array $row): array
    {
        return $row;
    }

    /**
     * Возвращает запись запроса для {@see fetchRow()}.
     *
     * @param array $row Запись запроса.
     *
     * @return void
     */
    public function beforeFetchRow(array &$row): void
    {
    }

    /**
     * Добавляет запись запроса полученная из {@see fetchRow()} или {@see maskedFetchRow()}
     * к результирующим записям.
     *
     * @param array $row Запись запроса.
     * @param array $rows Все записи запроса.
     *
     * @return void
     */
    public function afterFetchRow(array $row, array &$rows): void
    {
        $rows[] = $row;
    }

    /**
     * Событие, возникающие перед получением записей.
     * 
     * Получение записей {@see fetchRows()}.
     * 
     * @see GridMode::selectBySql()
     * @see GridMode::selectAll()
     *
     * @return void
     */
    public function beforeFetchRows(): void
    {
    }

    /**
     * Метод вызывается после выполнения запроса в {@see selectBySql()}, {@see selectAll()} и получения записей {@see fetchRows()}.
     *
     * @param array $rows Записи полученные в результате запроса.
     *
     * @return array
     */
    public function afterFetchRows(array $rows): array
    {
        return $rows;
    }

    /**
     * Возвращает одну запись методом {@see selectByPk()} по значению первичного ключа таблицы.
     *
     * @return array
     */
    public function getRow(): array
    {
        return [];
    }

    /**
     * Возвращает записи с использованием конструктора запроса {@see buildQuery()}.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->selectAll();
    }
}
