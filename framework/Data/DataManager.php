<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data;

use Ge;

/**
 * Менеджер данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data
 * @since 2.0
 */
class DataManager
{
    /**
     * @var string Вид назначения поля (asignType) - форма.
     */
    public const AT_FIELD = 'field';

    /**
     * @var string Вид назначения поля (asignType) - столбец.
     */
    public const AT_COLUMN = 'column';

    /**
     * @var string Имя аудита поля "замок" (данные не доступны для правки).
     */
    public const AR_LOCK = '_lock';

    /**
     * @var string Имя аудита поля "дата создания" (формат "Y-m-d H:i:s").
     */
    public const AR_CREATED_DATE = '_created_date';

    /**
     * @var string Имя аудита поля "дата обновления" (формат "Y-m-d H:i:s").
     */
    public const AR_UPDATED_DATE = '_updated_date';

    /**
     * @var string Имя аудита поля "идентификатор пользователя создавший запись".
     */
    public const AR_CREATED_USER = '_created_user';

    /**
     * @var string Поле аудита записи "идентификатор пользователя изменивший запись".
     */
    public const AR_UPDATED_USER = '_updated_user';

    /**
     * @var string Разрешение "чтению только своих записей" (RLS - Record-Level Sharing).
     */
    public const PERMISSION_RECORD_RLS = 'recordRls';

    /**
     * @var string Разрешение "запись действий" (в Журнал аудита).
     */
    public const PERMISSION_WRITE_AUDIT = 'writeAudit';

    /**
     * @var string Разрешение "просмотр записей" (Журнала аудита).
     */
    public const PERMISSION_VIEW_AUDIT = 'viewAudit';

    /**
     * Параметры конфигурации менеджера данных.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Имя таблицы.
     * 
     * @var string
     */
    public string $tableName = '';

    /**
     * Имя псевдонима таблицы.
     * 
     * @var string
     */
    public string $tableAlias = '';

    /**
     * Имя первичного ключа таблицы.
     * 
     * @var string
     */
    public string $primaryKey = '';

    /**
     * Имя внешнего ключа для связи с таблицами.
     * 
     * @var string
     */
    public string $foreignKey = '';

    /**
     * Имена таблиц для которых необходимо сбрасывать последовательность (автоинкримент).
     * 
     * @see \Ge\Db\Adapter\Driver\AbstractCommand::resetIncrement()
     * 
     * Значение определяется из свойства "resetIncrements" конфигурации модели данных {@see $config} 
     * и имеет вид: ["{{tableName1}}", "{{tableName2}}"...].
     * 
     * @var array<int, string>
     */
    public array $resetIncrements = [];

    /**
     * Параметры зависимостей.
     * 
     * @see DataManager::getDependencies()
     * 
     * @var array
     */
    public array $dependencies = [];

    /**
     * Параметры фильтра.
     * 
     * @var array
     */
    public array $filter = [];

    /**
     * Псевдонимы полей в виде пар 'псевдоним - имя'.
     * 
     * Имеет вид: `['fieldAlias' => 'fieldName', ...]`.
     * 
     * @see DataManager::initField()
     *
     * @var array<string, string>
     */
    public array $fieldAliases = [];

    /**
     * Имена полей в виде пар 'имя - псевдоним'.
     * 
     * Имеет вид: `['fieldName' => 'fieldAlias', ...]`.
     * 
     * @see DataManager::initField()
     *
     * @var array<string, string>
     */
    public array $fieldNames = [];

    /**
     * Параметры полей в виде пар 'псевдоним - параметры'.
     * 
     * Имеет вид:
     * ```php
     * [
     *     'fieldAlias' => [
     *         'field'  => 'field', 
     *         'alias'  => 'fieldAlias', 
     *         'direct' => 'directName', 
     *         'assign' => 'assignType',
     *         // ...
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @see DataManager::initField()
     *
     * @var array<string, array>
     */
    public array $fieldOptions = [];

    /**
     * Имена полей с их псевдонимами.
     * 
     * Значения определяются при инициализации {@see initFields()} настроек полей.
     * Имеет вид: ["fieldName" => "fieldAlias",...]
     *
     * @var array<string, string>
     */
    public array $fields = [];

    /**
     * Имена полей, значения которых необходимо проверить на уникальность.
     * 
     * Поле проверяемое на уникальность должно иметь параметр 'unique'.
     * Например: `['fields' => [['field', 'unique' => true], ...]`.
     * 
     * @var array<string, array>
     */
    public array $uniqueFields = [];

    /**
     * Указывает на не доступность правки данных.
     *
     * Такие данны нельзя изменить или удалить.
     * 
     * Если значение `true`, то при каждом изменении иди удалении данных будет 
     * выполняться проверка значения поля {@see DataManager::AR_LOCK}. Если значение 
     * '1', то действие над данными откланяется.
     * 
     * @var bool
     */
    public bool $lockRows = false;

    /**
     * Правила проверки полей.
     * 
     * @var array
     */
    public array $validationRules = [];

    /**
     * Правила форматирования полей.
     * 
     * Например:
     * ```php
     * [
     *    ['attribute', 'trim'],
     *    [['attribute', 'attribute1', ...], 'to' => 'int'],
     *    // ...
     * ]
     * ```
     * 
     * @var array<int, array>
     */
    public array $formatterRules = [];

    /**
     * Правила преобразования значений полей.
     * 
     * @var array
     */
    public array $converterRules = [];

    /**
     * Использовать аудит данных.
     * 
     * Если значение `true`, добавляет поля аудита {@see DataManager::addAuditRecords()} 
     * ('_updated_date', '_updated_user', '_created_date', '_created_user').
     * 
     * @return bool
     */
    public bool $useAudit = false;

    /**
     * Вид назначения полей для формирования конфигурации столбцов списка или полей формы.
     * 
     * Может имееть значение:
     *    - 'field', применяются только для полей формы;
     *    - 'column', применяются только столбцов сетки;
     *    - null, для всех.
     * 
     * @var null|string
     */
    public ?string $assignType = null;

    /**
     * Модель данных.
     * 
     * @var mixed
     */
    protected $model;

    /**
     * Модуль. 
     * 
     * @var null|\Ge\Mvc\Module\Module
     */
    protected $module;

    /**
     * Конструктор класса.
     *
     * @param array $config Параметры конфигурации менеджера жанных.
     * @param mixed $model Модель данных.
     * @param null|string $assignType Вид назначения полей для формирования конфигурации 
     *     столбцов списка или полей формы. Значение может быть указано через `$config['assignType']`.
     * 
     * @return void
     */
    public function __construct(array $config, $model, ?string $assignType = null)
    {
        $this->model = $model;
        $this->module = $model->module;
        $this->config = $config;
        $this->assignType = $assignType;

        Ge::configure($this, $config);

        $this->init();
    }

    /**
     * Возвращает параметры конфигурации.
     * 
     * @return array
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * Возвращает параметр конфигурации указанный черех конструктор.
     * 
     * @param string $name Название параметра.
     * @param mixed $default Возвращает значение если параметр отсутствует (по умолчанию `null`).
     * 
     * @return mixed
     */
    public function getConfigParam(string $name, mixed $default = null): mixed
    {
        return $this->config[$name] ?? $default;
    }

    /**
     * Инициализация параметр конфигурации.
     * 
     * @return void
     */
    public function init(): void
    {
        // избавляемся от "{{table}}" => "prefix_table", т.к. при фильтрации и использовании
        // оператов составления запроса, скобки могут экранироваться ("{{table}}" => "`{``{`table`}``}`").
        // Скобки может убрать только адаптер бд.
        if ($this->tableName) {
            $this->tableName = Ge::$app->db->rawExpression($this->tableName);
        }
        // если в конфигурации модели указан параметр "useAudit" (аудит записей) и 
        // предназначен для списка записей
        if ($this->useAudit) {
            $this->addAuditFields();
        }
        // если в конфигурации модели указан параметр "lockRows" (ограниченный доступ)
        if ($this->lockRows) {
            $this->addLockFields();
        }
        // опции полей
        if ($this->fields) {
            $this->initFields($this->fields);
        }
    }

    /**
     * Инициализация (определение псевдонима, имени, вида...) полей по указанным 
     * параметрам.
     * 
     * @param array $options Параметры полей.
     * 
     * @return $this
     */
    public function initFields(array $options): static
    {
        foreach ($options as $field) {
            $this->initField($field);
        }
        return $this;
    }

    /**
     * Инициализация (определение псевдонима, имени, вида...) поля по его параметрам (опциям).
     * 
     * @param array $options Параметры поля.
     * 
     * @return $this
     */
    public function initField(array $options): static
    {
        // определение типа связи
        $assign = isset($options['assign']) ? $options['assign'] : null;
        if ($assign !== null && $assign !== $this->assignType) {
            return $this;
        }
        // определение имени поля
        // если указан параметр "field"
        if (isset($options['field'])) {
            $fieldName  = $options['field'];
        // если параметр отсутствует, то 1-й элемент
        } else {
            if (isset($options[0])) {
                $fieldName = $options['field'] = $options[0];
            } else {
                $fieldName = '';
            }
        }
        // определение псевдонима поля
        $fieldAlias = isset($options['alias']) ? $options['alias'] : '';
        if (empty($fieldAlias)) {
            $fieldAlias = $fieldName;
        }
        // если поле с таким псевдоним уже существует
        if (isset($this->fieldAliases[$fieldAlias]))
            throw new Exception\UnexpectedValueException(Ge::t('app', 'A field with aliases "{0}" already exists', [$fieldAlias]));
        // если указано имя поля
        if ($fieldName) {
            // если поле с таким именем уже существует
            if (isset($this->fieldNames[$fieldName])) {
                throw new Exception\UnexpectedValueException(Ge::t('app', 'A field with name "{0}" already exists', [$fieldName]));
            }
            $this->fieldNames[$fieldName] = $fieldAlias;
        } else
            $fieldName = $fieldAlias;
        $this->fieldAliases[$fieldAlias] = $fieldName;
        $this->fieldOptions[$fieldAlias] = $options;
        // проверка значений полей на уникальность параметром "unique"
        $isUnique = $options['unique'] ?? false;
        if ($isUnique) {
            $this->uniqueFields[] = $fieldAlias;
        }
        return $this;
    }

    /**
     * Проверяет, указаны ли параметры конфигурации менеджера данных.
     * 
     * @return bool Возвращает значение `true`, если параметры указаны.
     */
    public function isExists(): bool
    {
        return !empty($this->config);
    }

    /**
     * Возвращает значение, указывающее, существование псевдонима поля с указанным 
     * именем.
     * 
     * @param string $alias Псевдоним поля.
     * 
     * @return bool Возвращает значение `true`, если псевдоним поля с указанным именем 
     *     существуе.
     */
    public function hasAlias(string $alias): bool
    {
        return isset($this->fieldAliases[$alias]);
    }

    /**
     * Возвращает значение, указывающее, существование поля с указанным именем.
     * 
     * @param string $field Имя поля.
     * 
     * @return bool Возвращает значение `true`, если поле с указанным именем существует.
     */
    public function hasField(string $field): bool
    {
        return isset($this->fieldNames[$field]);
    }

    /**
     * Возвращает псевдоним поля по его имени.
     * 
     * @param string $field Имя поля.
     * 
     * @return null|string Возвращает значение `null`, если псевдоним поля с указанным 
     *     названием не существует.
     */
    public function getAlias(string $field): ?string
    {
        return $this->hasField($field) ? $this->fieldNames[$field] : null;
    }

    /**
     * Возвращает имя поля поего псевдониму.
     * 
     * @param string $alias Псевдоним поля.
     * 
     * @return null|string Возвращает значение `null`, если поле с указанным псевдонимом 
     *     не существует.
     */
    public function getField(string $alias): ?string
    {
        return $this->hasAlias($alias) ? $this->fieldAliases[$alias] : null;
    }

    /**
     * Возвращает полное имя поля (имеет имя таблицы) с использованием параметра "direct".
     * 
     * @param string $alias Псевдоним поля.
     * 
     * @return null|string Возвращает значение `null`, если поле с указанным псевдонимом 
     *     не существует.
     */
    public function getFullField(string $alias): ?string
    {
        if ($alias === null) {
            return null;
        }
        $options = $this->getFieldOptions($alias, true);
        if ($options !== null) {
            if (isset($options['direct'])) {
                return $options['direct'];
            }
            if (isset($options['field'])) {
                return $options['field'];
            }
        }
        return null;
    }

    /**
     * Возвращает параметры поля.
     * 
     * @param string $name Псевдоним или имя поля.
     * @param bool $useAlias Использовать псевдоним поля.
     * 
     * @return null|array Возвращает значение `null`, если поля не существуют.
     */
    public function getFieldOptions(string $name, bool $useAlias = true): ?array
    {
        // если это не псевдоним поля
        if (!$useAlias)
            $alias = $this->getAlias($name);
        // если это псевдоним поля
        else
            $alias = $name;
        if ($alias)
            return isset($this->fieldOptions[$alias]) ? $this->fieldOptions[$alias] : null;
        else
            return null;
    }

    /**
     * Возвращает полное имя поля, которое содержит псевдоним таблицы или имя таблицы.
     * 
     * Псевдоним имени таблицы определятся из параметра "tableAlias", имя самой таблицы  определятся 
     * из параметра "tableName".
     * 
     * @param string $fieldName Имя поля (как суффикс к имени таблицы).
     * 
     * @return string
     */
    public function tableField(string $fieldName): string
    {
        if ($this->tableAlias)
            return $this->tableAlias . '.' . $fieldName;
        else
            return $this->tableName . '.' . $fieldName;
    }

    /**
     * Возвращает имя первичного ключа.
     * 
     * Имя указываемого первичного ключа должно совподать с именем в таблице базы данных.
     * 
     * @return string Первичный ключ таблицы базы данных.
     */
    public function fullPrimaryKey(): string
    {
        return $this->tableName . '.' . $this->primaryKey;
    }

    /**
     * Добавляет оператору условия фильтрации записей для доступа на уровне записей 
     * (RLS - Record-Level Sharing).
     * 
     * @param \Ge\Db\Sql\AbstractSql|\Ge\Db\Sql\Select $operator
     * 
     * @return void
     */
    public function addFilterRecordRls($operator): void
    {
        $operator->where([$this->tableField(self::AR_CREATED_USER) => Ge::$app->user->getId()]);
    }

    /**
     * Добавляет поля для аудита записи.
     * 
     * @param array $columns Поля таблицы.
     * 
     * @return void
     */
    public function setUpdateAuditColumns(array &$columns): void
    {
        
        $columns[self::AR_UPDATED_DATE] = Ge::$app->db->makeDateTime(Ge::$app->dataTimeZone);
        $columns[self::AR_UPDATED_USER] = Ge::$app->user->getId();
    }

    /**
     * Добавляет поля (дата добавления записи пользователем) аудита записи.
     * 
     * @param array $columns Поля таблицы.
     * 
     * @return void
     */
    public function setInsertAuditColumns(array &$columns): void
    {
        $columns[self::AR_CREATED_DATE] = Ge::$app->db->makeDateTime(Ge::$app->dataTimeZone);
        $columns[self::AR_CREATED_USER] = Ge::$app->user->getId();
    }

    /**
     * Добавляет поле с параметрами через указания псевдонима поля.
     * 
     * @param string $fieldAlias Псевдоним поля.
     * @param array $options Параметры поля.
     *  
     * @return bool Возвращает значение `false`, если инициализация поля не выполнена 
     *     (поле уже существует).
     */
    public function addAlias(string $fieldAlias, array $options): bool
    {
        if (!$this->hasAlias($fieldAlias)) {
            return false;
        }
        $this->initField($options);
        return true;
    }

    /**
     * Добавляет параметры поля.
     * 
     * Поле проверяется по имени (параметр `$options[0]`). Если поле существует, 
     * параметры не будут добавлены.
     * 
     * @see DataManager::initField()
     * 
     * @param array $options Параметры поля.
     *  
     * @return $this
     */
    public function addField(array $options): static
    {
        if (!$this->hasField($options[0])) {
            $this->initField($options);
        }
        return $this;
    }

    /**
     * Добавляет параметры полей.
     * 
     * @see DataManager::initField()
     * 
     * @param array $fields Параметры полей.
     *  
     * @return $this
     */
    public function addFields(array $fields): static
    {
        foreach ($fields as $field) {
            if ($this->hasField($field[0])) continue;
            $this->initField($field);
        }
        return $this;
    }

    /**
     * Устанавливает псевдоним указаному полю.
     * 
     * После установки псевдонима, выполняет инициализацию поля.
     * 
     * @param string $fieldAlias Псевдоним поля.
     * @param string $fieldName Имя поля.
     *  
     * @return $this
     */
    public function setAlias(string $fieldAlias, string $fieldName): static
    {
        $options = $this->getFieldOptions($fieldAlias);
        if ($options === null) {
            $options = [];
        }
        $this->removeAlias($fieldAlias);

        $options['field'] = $fieldName;
        $options['alias'] = $fieldAlias;
        $this->initField($options);
        return $this;
    }

    /**
     * Устанавливает имя указаному псевдониму поля.
     * 
     * После установки имени поля, выполняет инициализацию поля.
     * 
     * @param string $fieldName Имя поля.
     * @param string $fieldAlias Псевдоним поля.
     * 
     * @return $this
     */
    public function setField(string $fieldName, string $fieldAlias): static
    {
        $options = $this->getFieldOptions($fieldName, false);
        if ($options === null)
            $options = array();
        $this->removeField($fieldName);

        $options['field'] = $fieldName;
        $options['alias'] = $fieldAlias;
        $this->initField($options);
        return $this;
    }

    /**
     * Добавляет параметры поля с "замком" (lock).
     * 
     * Параметры такого поля ограничивают доступ к данным и добавляются в том случаи
     * если {@see DataManager::$lockRows} имеет значение `true`.
     *
     * @return $this
     */
    public function addLockFields(): static
    {
        $this->initField(['field' => self::AR_LOCK, 'alias' => 'lockRow', 'direct' => $this->tableField(self::AR_LOCK)]);
        return $this;
    }

    /**
     * Добавляет правило проверки данных.
     * 
     * @see DataManager::$validationRules
     *
     * @param array $rule Правило проверки.
     * 
     * @return $this
     */
    public function addValidationRule(array $rule): static
    {
        $this->validationRules[] = $rule;
        return $this;
    }

    /**
     * Добавляет правила проверки данных.
     * 
     * @see DataManager::$validationRules
     * 
     * @param array $rules Правила проверки.
     * 
     * @return $this
     */
    public function addValidationRules(array $rules): static
    {
        $this->validationRules = array_merge($this->validationRules, $rules);
        return $this;
    }

    /**
     * Добавляет правило форматирования данных.
     * 
     * @see DataManager::$formatterRules
     * 
     * @param array $rule Правило форматирования.
     * 
     * @return $this
     */
    public function addFormatterRule(array $rule): static
    {
        $this->formatterRules[] = $rule;
        return $this;
    }

    /**
     * Добавляет правила форматирования данных.
     * 
     * @see DataManager::$formatterRules
     * 
     * @param array $rules Правила форматирования.
     * 
     * @return $this
     */
    public function addFormatterRules(array $rules): static
    {
        $this->formatterRules = array_merge($this->formatterRules, $rules);
        return $this;
    }

    /**
     * Добавляет к маске полей, маску поля "замок" (lock).
     * 
     * @see DataManager::AR_LOCK
     * 
     * @param array $mask Маска полей.
     * 
     * @return $this
     */
    public function addLockFieldsToMask(array &$mask): static
    {
        $mask['lockRow'] = self::AR_LOCK;
        return $this;
    }

    /**
     * Добавляет параметры полей для аудита.
     * 
     * @return $this
     */
    public function addAuditFields(): static
    {
        $this->initField([
            'alias'  => 'logId',
            'direct' => $this->tableField($this->primaryKey),
            'render' => 'renderPrimaryKey'
        ]);
        $this->initField([
            'field'  => self::AR_UPDATED_USER,
            'alias'  => 'logUpdatedUser',
            'direct' => $this->tableField(self::AR_UPDATED_USER)
        ]);
        $this->initField([
            'field'      => self::AR_UPDATED_DATE,
            'alias'      => 'logUpdatedDate',
            'direct'     => $this->tableField(self::AR_UPDATED_DATE),
            'filterType' => 'datetime'
        ]);
        $this->initField([
            'field'  => self::AR_CREATED_USER,
            'alias'  => 'logCreatedUser',
            'direct' => $this->tableField(self::AR_CREATED_USER)
        ]);
        $this->initField([
            'field'      => self::AR_CREATED_DATE,
            'alias'      => 'logCreatedDate',
            'direct'     => $this->tableField(self::AR_CREATED_DATE),
            'filterType' => 'datetime'
        ]);
        return $this;
    }

    /**
     * Добавляет к маске полей, маску поля аудита.
     * 
     * @param array $mask Маска полей.
     * 
     * @return $this
     */
    public function addAuditFieldsToMask(array &$mask): static
    {
        $mask['logId']          = $this->primaryKey;
        $mask['logUpdatedUser'] = self::AR_UPDATED_USER;
        $mask['logUpdatedDate'] = self::AR_UPDATED_DATE;
        $mask['logCreatedUser'] = self::AR_CREATED_USER;
        $mask['logCreatedDate'] = self::AR_CREATED_DATE;
        return $this;
    }

    /**
     * Устанавливает параметры указанному полю.
     * 
     * @param string $name Имя поля или псевдоним.
     * @param array $options Параметры поля.
     * 
     * @return $this
     */
    public function setFieldOptions(string $name, array $options): static
    {
        $this->fieldOptions[$name] = $options;
        return $this;
    }

    /**
     * Удаляет поле (с параметрами) с указанием имени.
     * 
     * @param string $fieldName Имя поля.
     * 
     * @return bool Возвращает значение `true`, если поле было удалено.
     */
    public function removeField(string $fieldName): bool
    {
        if (!$this->hasField($fieldName)) {
            return false;
        }
        $fieldAlias = $this->getAlias($fieldName);
        unset($this->fieldAliases[$fieldAlias], $this->fieldNames[$fieldName], $this->fieldOptions[$fieldAlias]);
        return true;
    }

    /**
     * Удаляет поле (с параметрами) с указанием псевдонима.
     * 
     * @param string $fieldAlias Псевдоним поля.
     * 
     * @return bool Возвращает значение `true`, если поле было удалено.
     */
    public function removeAlias(string $fieldAlias): bool
    {
        if (!$this->hasAlias($fieldAlias)) {
            return false;
        }
        $fieldName = $this->getField($fieldAlias);
        unset($this->fieldAliases[$fieldAlias], $this->fieldNames[$fieldName], $this->fieldOptions[$fieldAlias]);
        return true;
    }

    /**
     * Возвращает указанную зависимость.
     * 
     * Где, зависимость - это параметр(ы), указывающие на данные над которыми необходимо 
     * выполнить действие.
     * 
     * Например, если `$name = 'delete'`, а зависимости `['delete' => 'table1', ...]`, 
     * то результатом будет 'table1'.
     * 
     * @param string $name Имя зависимости.
     * 
     * @return array|null Возвращает значение `null`, если зависимость не найдена.
     */
    public function getDependency(string $name): ?array
    {
        if ($this->dependencies) {
            return isset($this->dependencies[$name]) ? $this->dependencies[$name] : null;
        }
        return null;
    }

    /**
     * @var bool Разрешение пользователю на чтению только своих записей 
     * (RLS - Record-Level Sharing).
     */
    protected bool $_prmRecordRls;

    /**
     * Проверяет, есть ли у пользователя разрешения на чтению только своих 
     * записей (RLS - Record-Level Sharing).
     * 
     * Записи буду доступны пользователю только те, которые он добавил.
     * 
     * @return bool Если true, пользователь имеет разрешение на чтение только своих записей.
     */
    public function canUseRecordRls(): bool
    {
        if (!isset($this->_prmRecordRls)) {
            $this->_prmRecordRls =  $this->module->getPermission()->isAllow(self::PERMISSION_RECORD_RLS);
        }
        return $this->_prmRecordRls;
    }

    /**
     * @var bool Разрешение пользователя для просмотра аудита записей.
     */
    protected bool $_prmViewAudit;

    /**
     * Проверяет, есть ли у пользователя разрешение для просмотра аудита записей.
     * 
     * Под просмотром аудита записей понимают: отображение соответствующих элементов 
     * (поля формы, столбцы списка и т.д.) представления.
     * 
     * @param null|bool $canView (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `true`, если пользователь имеет разрешение для 
     *     просмотра аудита записей.
     */
    public function canViewAudit(?bool $canView = null): bool
    {
        if ($canView !== null) {
            $this->_prmViewAudit = $canView;
        }
        if (!isset($this->_prmViewAudit)) {
            $this->_prmViewAudit = $this->module->getPermission()->isAllow(self::PERMISSION_VIEW_AUDIT);
        }
        return $this->_prmViewAudit;
    }

    /**
     * @var bool Разрешение пользователю на запись его действий в журнал.
     */
    protected bool $_prmWriteAudit;

    /**
     * Проверяет, есть ли у пользователя разрешение на запись его действий в журнал.
     * 
     * @return bool Если true, пользователь имеет разрешение для зиписи своих действий в журнал.
     */
    public function canWriteAudit(): bool
    {
        if (!isset($this->_prmWriteAudit)) {
            $this->_prmWriteAudit =  $this->module->getPermission()->isAllow(self::PERMISSION_VIEW_AUDIT);
        }
        return $this->_prmWriteAudit;
    }
}