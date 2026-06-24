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
use Ge\Http\Response;
use Ge\Data\DataManager;
use Ge\Mvc\Module\BaseModule;
use Ge\Mvc\Controller\BaseController;
use Ge\Cache\StorageFactory;
use Ge\Cache\Storage\StorageInterface;

/**
 * Трейт модели данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
trait DataModelTrait
{
    /**
     * Менеджер данных.
     * 
     * @var DataManager|null
     */
    protected ?DataManager $dataManager;

    /**
     * Указатель на объект хранилища, полученный через метод {@see getStorage()}.
     * 
     * @var StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * Назначение типа полей для формирования конфигурации столбцов списка 
     * или полей формы указанных в менеджере данных.
     * 
     * Имеет значения: 
     *     - 'field', поля формы; 
     *     - 'column', столбцы списка; 
     *     - `null`, поля и столбцы.
     * 
     * @var string|null
     */
    protected ?string $assignType = null;

    /**
     * Модуль приложения.
     *
     * @var BaseModule
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * Инициализация модели данных.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->dataManager = $this->getDataManager();
    }

    /**
     * Возвращает название модели данных.
     * 
     * @return string
     */
    public function getModelName(): string
    {
        return $this->getReflection()->getShortName();
    }

    /**
     * Возвращает текущий контроллер модуля.
     * 
     * @see \Ge\Mvc\Module\BaseModule::controller()
     * 
     * @return BaseController|null
     */
    public function controller(): ?BaseController
    {
        return $this->module->controller();
    }

    /**
     * Возвращает текущий HTTP-ответ контроллера.
     * 
     * @see \Ge\Mvc\Controller\BaseController::getResponse()
     * 
     * @return Response|null
     */
    public function response(): ?Response
    {
        return $this->module->controller()->getResponse();
    }

    /**
     * Устанавливает параметры конфигурации менеджера данных.
     * 
     * Настройки менеджера данных расположены в разделе "dataManager" файла конфигурации модуля.
     * И возвращаются по названию модели данных {@see getName()}.
     * 
     * @return array
     */
    public function getDataManagerConfig(): array
    {
        $dataManager = $this->module->getConfigParam('dataManager');
        return $dataManager[$this->getModelName()] ?? [];
    }

    /**
     * Возвращает менеджер данных.
     * 
     * @see DataModelTrait::createDataManager()
     * 
     * @return DataManager|null
     */
    public function getDataManager(): ?DataManager
    {
        if (!isset($this->dataManager)) {
            $this->dataManager = $this->createDataManager();
        }
        return $this->dataManager;
    }

    /**
     * Создаёт менеджер данных.
     * 
     * @see DataModelTrait::getDataManagerConfig()
     * 
     * @return DataManager|null
     */
    public function createDataManager(): ?DataManager
    {
        $config = $this->getDataManagerConfig();
        return $config ? new DataManager($config, $this, $this->assignType) : null;
    }

    /**
     * Устанавливает параметры хранилища ресурсов.
     * 
     * @return array
     */
    public function storage(): array
    {
        return [
            'adapter' => [
                'name'    => 'session',
                'options' => [
                    'namespace' => 'sample_namespace',
                ]
            ]
        ];
    }

    /**
     * Создаёт хранилище ресурсов.
     * 
     * Параметры хранилища ресурсов определяется из {@see DataModelTrait::storage()}.
     * 
     * @return StorageInterface
     */
    public function createStorage(): StorageInterface
    {
        return StorageFactory::factoryBySession($this->storage(), Ge::getName('_DataModel'), Ge::$app->session);
    }

    /**
     * Возвращает хранилище ресурсов.
     * 
     * @see DataModelTrait::createStorage()
     * 
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        if (!isset($this->storage)) {
            $this->storage = $this->createStorage();
        }
        return $this->storage;
    }

    /**
     * Возвращает имя таблицы базы данных.
     * 
     * Метод должен соответствовать {@see \Ge\Data\Model\AbstractModel::tableName()}.
     * 
     * @see \Ge\Data\DataManager::$tableName
     * 
     * @return string Имя таблицы базы данных.
     */
    public function tableName(): string
    {
        return $this->dataManager->tableName ?? '';
    }

    /**
     * Возвращает имя псевдонима таблицы базы данных.
     * 
     * @see \Ge\Data\DataManager::$tableAlias
     * 
     * @return string Имя псевдонима таблицы базы данных.
     */
    public function tableAlias(): string
    {
        return $this->dataManager->tableAlias;
    }

    /**
     * Возвращает зависимые связи для текущей таблицы.
     * 
     * @see \Ge\Data\DataManager::$dependencies
     * 
     * @return null|array
     */
    public function tableDependencies(): ?array
    {
        return $this->dataManager->dependencies;
    }

    /**
     * Возвращает имена таблиц для которых необходимо сбрасывать последовательность 
     * (автоинкримент) при удалении всех записей.
     * 
     * @see \Ge\Data\DataManager::$resetIncrements
     * 
     * @return array
     */
    public function tableResetIncrements(): ?array
    {
        return $this->dataManager->resetIncrements;
    }

    /**
     * Возвращает имя первичного ключа.
     * 
     * Имя указываемого первичного ключа должно совподать с именем 
     * в таблице базы данных.
     * 
     * Метод должен соответствовать {@see \Ge\Data\Model\AbstractModel::primaryKey()}.
     * 
     * @see \Ge\Data\DataManager::$primaryKey
     * 
     * @return string Первичный ключ таблицы базы данных.
     */
    public function primaryKey(): string
    {
        return $this->dataManager->primaryKey ?? '';
    }

    /**
     * Возвращает полное имя первичного ключа.
     * 
     * Имя первичного ключа должно совподать с именем в таблице базы данных.
     * 
     * @see \Ge\Data\DataManager::fullPrimaryKey()
     * 
     * @return string Полное имя первичного ключа ключа таблицы базы данных.
     */
    public function fullPrimaryKey(): string
    {
        return $this->dataManager->fullPrimaryKey();
    }

    /**
     * Возвращает имя внешнего ключа для связи с таблицами.
     * 
     * @see \Ge\Data\DataManager::$foreignKey
     * 
     * @return string Внешний ключ.
     */
    public function foreignKey(): ?string
    {
        return $this->dataManager->foreignKey;
    }

    /**
     * Возвращает полное имя поля, которое содержит имя псевдонима таблицы или имя таблицы.
     * 
     * @see \Ge\Data\DataManager::tableField()
     * 
     * @param string $fieldName Имя поля таблицы.
     * 
     * @return string
     */
    public function tableField(string $fieldName): string
    {
        return $this->dataManager->tableField($fieldName);
    }

    /**
     * Возвращает маску полей записи таблицы базы данных.
     * 
     * Маска необходима для безопасного формирования полей с их значениями, те
     * полей которые не прошли через маску, являются "небезопасными".
     * Маска не применяется к первичному полю таблицы базы данных, но указывается
     * для этого поля.
     * 
     * Пример маски:
     * ```php
     * return [maskField => field, maskField2 => field2, ...]
     * ```
     * 
     * @see \Ge\Data\DataManager::$fieldAliases
     * 
     * @return array
     */
    public function maskedRow(): array
    {
        return $this->dataManager->fieldAliases ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function t($text)
    {
        return $this->module->t($text);
    }

    /**
     * Возвращает параметры конфигурации таблицы кэша.
     * 
     * @see \Ge\Mvc\Module\BaseModule::$config::$cacheTable
     * 
     * @return null|array
     */
    public function cacheTable(): ?array
    {
        return $this->module->getConfigParam('cacheTable', null);
    }

    /**
     * Возвращает таблицу кэша.
     * 
     * @see DataModelTrait::cacheTable()
     * 
     * @return null|\Ge\Cache\CacheTable
     */
    public function getCacheTable()
    {
        $table = $this->cacheTable();
        if ($table) {
            /** @var \Ge\Cache\CacheTable $cache */
            $cache = Ge::$app->tables;
            $name  = $table['name'] ?? null;
            if ($name !== null) {
                if ($cache->pattern($name)) {
                    return $cache;
                }
            }
            $pattern = $table['pattern'] ?? null;
            if ($pattern !== null) {
                $cache
                    ->setPattern($name, $pattern)
                    ->pattern($name);
                return $cache; 
            }
        }
        return null;
    }

    /**
     * Обновляет строку кэш-таблицы по указанному идентификатору (ключу).
     * 
     * Обновление строки будет выполнено, если будут установлены параметры конфигурации 
     * кэш-таблицы для текущей модели записи {@see DataModelTrait::cacheTable()}.
     * 
     * @see \Ge\Cache\CacheTable::refreshRow()
     * 
     * @param mixed $identifier Идентификатор записи (ключ). 
     * 
     * @return void
     */
    public function updateCacheTableRow(mixed $identifier): void
    {
        /** @var \Ge\Cache\CacheTable $table */
        if ($table = $this->getCacheTable()) {
            // если кэш-таблица не создана, нет смысла обновлять строки
            if ($table->exists()) {
                $table->refreshRow($identifier);
            }
        }
    }

    /**
     * Удаляет строку кэш-таблицы по указанному идентификатору (ключу).
     * 
     * Удаление строки будет выполнено, если будут установлены параметры конфигурации 
     * кэш-таблицы для текущей модели записи {@see DataModelTrait::cacheTable()}.
     * 
     * @see \Ge\Cache\CacheTable::deleteRow()
     * 
     * @param mixed $identifier Идентификатор записи (ключ).
     * 
     * @return void
     */
    public function deleteCacheTableRow(mixed $identifier = null): void
    {
        /** @var \Ge\Cache\CacheTable $table */
        if ($table = $this->getCacheTable()) {
            $table->deleteRow($identifier);
        }
    }

    /**
     * Удаляет строки кэш-таблицы по указанным идентификаторам (ключам).
     * 
     * Удаление строк будет выполнено, если будут установлены параметры конфигурации 
     * кэш-таблицы для текущей модели записи {@see DataModelTrait::cacheTable()}.
     * 
     * @see \Ge\Cache\CacheTable::deleteRows()
     * @see \Ge\Cache\CacheTable::flushCache()
     * 
     * @param null|array $identifier Идентификаторы записей (ключи). Если значение `null`, 
     *     будут удалены все строки.
     * 
     * @return void
     */
    public function deleteCacheTableRows(?array $identifier = null): void
    {
        /** @var \Ge\Cache\CacheTable $table */
        if ($table = $this->getCacheTable()) {
            if ($identifier === null) {
                $table->flushCache();
            } else {
                $table->deleteRows($identifier);
            }
        }
    }
}
