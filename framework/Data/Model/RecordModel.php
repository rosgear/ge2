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
use Closure;
use Ge\Db\Sql\Where;
use Ge\Db\ActiveRecord;
use Ge\Data\DataManager;

/**
 * Модель активной записи под управлением менеджера данных.
 * 
 * Класс использует трейт модели данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
class RecordModel extends ActiveRecord
{
    use DataModelTrait;

    /**
     * Идентификатор записи.
     * 
     * @see RecordModel::getIdentifier()
     *
     * @var mixed
     */
    protected mixed $identifier = null;

    /**
     * Локализатор модели данных.
     * 
     * Применяется для обновления локализованных записей в базе данных.
     *
     * @var Localizer
     */
    protected ?Localizer $localizer;

    /**
     * Параметры локализатора.
     * 
     * Если параметры указаны, локализатор будет задействован.
     *
     * @var array
     */
    public array $localizerParams = [];

    /**
     * {@inheritdoc}
     */
    public function updateRecord(array $columns, Where|Closure|string|array|null $where = null): false|int
    {
        $where = $where ?: [];
        if ($this->dataManager && $this->dataManager->useAudit) {
            $this->dataManager->setUpdateAuditColumns($columns);
            if (is_array($where) && $this->dataManager->lockRows) {
                $where[] = DataManager::AR_LOCK . '<>1';
            }
        }
        return parent::updateRecord($columns, $where);
    }

    /**
     * {@inheritdoc}
     */
    public function insertRecord(array $columns): int|string
    {
        if ($this->dataManager && $this->dataManager->useAudit) {
            $this->dataManager->setInsertAuditColumns($columns);
        }
        return parent::insertRecord($columns);
    }

    /**
     * Выпоолняет сброс значений автоинкремента таблиц.
     * 
     * @param int|string $value
     * 
     * @return bool
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function resetIncrement(int|string $value = 1): bool
    {
        $tables = $this->tableResetIncrements();
        if ($tables) {
            $command = $this->getDb()->createCommand();
            foreach ($tables as $tableName) {
                $command->resetIncrement($tableName, $value)->execute();
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $db = $this->getDb();

        // если в запросе указан идентификатор
        $identifier = $this->getIdentifier();
        if ($identifier) {
            $where[$db->rawExpression($this->fullPrimaryKey())] = $identifier;
        }
        // если есть поле "_lock" в таблице
        if ($this->dataManager && $this->dataManager->lockRows) {
            $where[] = $db->rawExpression($this->tableName() . '.' . DataManager::AR_LOCK . '<>1');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDependencies(mixed $condition = []): void
    {
        $dependencies = $this->tableDependencies();
        if (empty($dependencies)) {
            return;
        }

        // если указаны методы класса для удаления зависимых записей 
        if (isset($dependencies['callable'])) {
            $callables = $dependencies['callable'];
            unset($dependencies['callable']);
        } else
            $callables = [];

        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        $masterTableName = $db->rawExpression( $this->tableName());
        // на каждую таблицу зависимости создается конструктор
        foreach($dependencies as $slaveTableName => $slaveCondition) {
            // Элемент массива - это название зависимой таблицы, где
            // $slaveCondition - название таблицы. Массив имеет вид: array("{{tableName}}"...).
            if (is_numeric($slaveTableName)) {
                /** @var \Ge\Db\Sql\QueryBuilder $builder */
                $builder = $db->getQueryBuilder();
                $delete = $builder->delete(array($db->rawExpression($slaveCondition)));
                $sql = $builder->getSqlString();
            // в остальных случаях ассоц. элемент массива
            } else {
                $slaveTableName = $db->rawExpression($slaveTableName);
                // если условие - SQL запрос
                if (is_string($slaveCondition)) {
                    $sql = $slaveCondition;
                } else {
                    /** @var \Ge\Db\Sql\QueryBuilder $builder */
                    $builder = $db->getQueryBuilder();
                    $delete = $builder
                        ->delete(array($slaveTableName))
                        ->using(array($slaveTableName, $masterTableName));
    
                    // если условие - массив полей таблиц $slaveTableName и $masterTableName
                    if (is_array($slaveCondition)) {
                        foreach($slaveCondition as $field1 => $field2) {
                            $delete->where->equalTo(
                                $slaveTableName . '.' . $field1,
                                $masterTableName . '.' . $field2,
                                \Ge\Db\Sql\ExpressionInterface::TYPE_IDENTIFIER,
                                \Ge\Db\Sql\ExpressionInterface::TYPE_IDENTIFIER
                            );
                        }
                    // если условие - SQL оператор
                    } else
                    if (is_object($slaveCondition)) {
                        $slaveCondition($delete->where, $slaveTableName, $masterTableName);
                    }
                    $delete->where($condition);
                    $sql = $builder->getSqlString();
                }
            }
            // удаляем зависимые записи по запросу
            $db->createCommand($sql)->execute();
        }

        // если указаны методы класса для удаления зависимых записей
        if ($callables) {
            if ($id = $this->getIdentifier()) {
                foreach ($callables as $callable) {
                    $this->$callable($id);
                }
            }           
        }
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return $this->dataManager->fieldAliases ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return $this->dataManager->validationRules ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function formatterRules(): array
    {
        return $this->dataManager->formatterRules ?? [];
    }

    /**
     * Возвращает поля, значения которых необходимо проверить на уникальность.
     * 
     * Варианты записи полей:
     * 1) `['fields' => ['field1', 'field2', ...], 'operator' => 'OR']`.
     * 2) `['field1', 'field2', ...]`.
     * 
     * @return array
     * 
     */
    public function uniqueFields(): array
    {
        return $this->dataManager->uniqueFields ?? [];
    }

    /**
     * Возвращает идентификатор записи, полученный из запроса одним
     * из методов: GET, POST,...
     *
     * @return mixed
     */
    public function getIdentifier(): mixed
    {
        return null;
    }

    /**
     * Проверка существования идентификатора записи.
     *
     * @return bool
     */
    public function hasIdentifier(): bool
    {
        $identifier = $this->getIdentifier();
        return !empty($identifier);
    }

    /**
     * Возвращает запись по значению первичного ключа таблицы.
     * 
     * @param mixed $identifier идентификатор записи таблицы.
     *     Если значение null, значение будет получено из {@see getIdentifier()}.
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе 
     *     значение `null`.
     */
    public function get(mixed $identifier = null)
    {
        if ($identifier === null) {
            $identifier = $this->getIdentifier();
        }
        return $this->selectByPk($identifier);
    }

    /**
     * Проверяет уникальность значений полей записи.
     * 
     * @param array<int, string> $fields Имена полей записи с маской (`['mask' => 'field',...]`).
     * @param string $combination Объединение полей в условие запроса с помощью 
     *     SQL-опрератора (по умолчанию 'AND'). Пример: `field AND field1 AND...`
     * 
     * @return bool Если `true`, запись с указанными значениями полей является уникальной.
     */
    public function checkUniqueness(array $fields, string $combination = 'OR'): bool
    {
        $where = [];
        $mask = $this->maskedAttributes();
        foreach ($fields as $fieldMask) {
            $value = $this->getAttribute($fieldMask);
            $field = $mask[$fieldMask] ?? null;
            if ($value !== null && $field) {
                $where[$field] = $value;
            }
        }

        if ($this->exists($where, $combination)) {
            $this->setError(Ge::t(BACKEND, 'A record with the specified field values already exists'));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(bool $isInsert): bool
    {
        /** @var array $uniqueFields */
        $uniqueFields = $this->uniqueFields();
        if ($uniqueFields) {
            // если имеет объявление в виде: ['fields' => ['field1', 'field2', ...], 'operator' => 'OR']
            if (isset($uniqueFields['fields'])) {
                $isUniqueness = $this->checkUniqueness($uniqueFields['fields'], $uniqueFields['operator'] ?? 'OR');
            // если имеет объявление в виде: ['field1', 'field2', ...]
            } else {
                $isUniqueness = $this->checkUniqueness($uniqueFields);
            }
            if (!$isUniqueness) return false;
        }

        $canSave = true;
        /** @var bool $canSave возможность сохранения записи определяет событие */
        $this->trigger(self::EVENT_BEFORE_SAVE, ['isInsert' => $isInsert, 'canSave' => &$canSave]);
        return $canSave;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave(
        bool $isInsert, 
        ?array $columns = null, 
        false|int|string|null $result = null
    ): void
    {
        if ($this->useLocalizer()) {
            $this->getLocalizer()->save();
        }
        $this->trigger(
            self::EVENT_AFTER_SAVE,
            [
                'isInsert' => $isInsert,
                'columns'  => $columns,
                'result'   => $result
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function afterSelect(): void
    {
        $this->trigger(self::EVENT_AFTER_SELECT);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete(): bool
    {
        $canDelete = true;
        /** @var bool $canDelete возможность удаления записи определяет событие */
        $this->trigger(self::EVENT_BEFORE_DELETE, ['canDelete' => &$canDelete]);
        return $canDelete;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(false|int|null $result = null): void
    {
        $this->trigger(self::EVENT_AFTER_DELETE, ['result' => $result]);
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($this->useLocalizer()) {
            if (!$this->getLocalizer()->validate())
                return false;
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function afterPopulate(): void
    {
        if ($this->useLocalizer()) {
            $this->getLocalizer()->fillModels();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad(): void
    {
        if ($this->useLocalizer()) {
            $this->getLocalizer()->load();
        }
    }

    /**
     * Возвращает локализатор модели.
     * 
     * @return Localizer
     */
    public function getLocalizer(): Localizer
    {
        if (!isset($this->localizer)) {
            $this->localizer = new Localizer($this);
        }
        return $this->localizer;
    }

    /**
     * Проверяет, использовать ли локализатор модели.
     * 
     * @return bool
     */
    public function useLocalizer(): bool
    {
        return !empty($this->localizerParams);
    }
}