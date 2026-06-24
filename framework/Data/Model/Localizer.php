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

/**
 * Локализатор представлен в виде класса для локализации значений атрибутов активных записей.
 * 
 * Значения атрибутов активных записей хранятся в таблице базы данных с именем '{table}_locale'.
 * Таблица должна иметь следующие поля:
 * - `foreignKey` int(11) unsigned NOT NULL, имя внешнего ключа для связи с моделью {@see Localizer::$form};
 * - `language_id` int(11) unsigned NOT NULL, идентификатор языка (`Ge::$app->language->code`);
 * - имена полей участвующие в локализации.
 * 
 * Первичный ключ таблицы состоит из `foreignKey` и `language_id`.
 * 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
class Localizer
{
    /**
     * Модель (форма, владелец) активной записи, относительно которой формируются 
     * связи с локализованным моделями данных.
     *
     * @var RecordModel
     */
    protected RecordModel $form;

    /**
     * Имя таблицы базы данных, где хранятся значения атрибутов локализованных 
     * моделей.
     * 
     * Имя должно иметь вид '{table}_locale'.
     * 
     * @var string
     */
    public string $tableName;

    /**
     * Имя параметра в запросе методом POST, передающий значения атрибутов локализованных 
     * моделей данных.
     * 
     * Значение параметра в запросе будет иметь вид, пример:
     * `locale[ru-RU][name]: "value"`, `locale[en-GB][name]: "value"`, ...
     * 
     * @var string
     */
    public string $paramName = 'locale';

    /**
     * Имя внешего ключа таблицы базы данных локализованных моделей для связи с 
     * формой (владельцем).
     * 
     * @see Localizer::$model
     * 
     * @var string
     */
    public string $foreignKey;

    /**
     * Имя класса для создания локализованной модели данных.
     * 
     * @var string
     */
    public string $modelName;

    /**
     * Локализованные модели данных.
     * 
     * @see Localizer::getModel()
     * 
     * @var array<int, RecordModel>
     */
    public array $models = [];

    /**
     * Конструктор класса.
     *
     * @param RecordModel $form Модель (форма, владелец) активной записи, относительно 
     *     которой формируются связи с локализованным моделями данных.
     * 
     * @return void
     */
    public function __construct(RecordModel $form)
    {
        $this->form = $form;
        if (isset($form->localizerParams)) {
            Ge::configure($this, $form->localizerParams);
        }
    }

    /**
     * Создаёт локализованную модель данных.
     *
     * @return RecordModel
     */
    public function createModel()
    {
    }

    /**
     * Создаёт локализованные модели данных для всех установленных языков.
     *
     * @return void
     */
    public function createModels(): void
    {
        /** @var array $languages параметры конфигурации установленных языков */
        $languages = Ge::$app->language->available->getAll();
        foreach ($languages as $locale => $language) {
            $model = new $this->modelName;
            $this->models[$language['tag']] = $model;
        }
    }

    /**
     * Возвращает локализованную модель данных по указанному тегу языка.
     * 
     * @param null|string $languageTag Тег языка ('ru-RU', 'en-GB', ...). Если `null`, 
     *     тег текущего языка (по умолчанию `null`).
     * 
     * @return ActiveRecord|RecordModel
     */
    public function getModel(?string $languageTag = null)
    {
        if ($languageTag === null) {
            $languageTag = Ge::$app->language->tag;
        }
        if (!isset($this->models[$languageTag])) {
            return $this->models[$languageTag] = new $this->modelName();
        }
        return $this->models[$languageTag];
    }

    /**
     * Проверяет, существует ли локализованная модель по указанному тегу языка.
     * 
     * @param string $languageTag Тег языка ('ru-RU', 'en-GB', ...).
     * 
     * @return bool
     */
    public function hasModel(string $languageTag): bool
    {
        return isset($this->models[$languageTag]);
    }

    /**
     * Проверяет, были ли созданы локализованные модели данных.
     * 
     * @return bool
     */
    public function hasModels(): bool
    {
        return !empty($this->models);
    }

    /**
     * Устанавливает значения атрибутам локализованным моделям данных, полученных 
     * из HTTP-запроса.
     * 
     * @return void
     */
    public function load(): void
    {
        if (!isset($this->form->unsafeAttributes[$this->paramName])) {
            return;
        }
        /** @var array $languages параметры конфигурации установленных языков */
        $languages = Ge::$app->language->available->getAll();
        if ($languages) {
            // если добавляется новая запись через форму (владельца)
            $isNewRecord = $this->form->isNewRecord();
            if (!$isNewRecord) {
                // идентификатор изменяемой записи
                $identifier = $this->form->getIdentifier();
            }
            /** @var array $loAttributes поля локализации изменяемой / добавляемой записи с их значениями */
            $loAttributes = $this->form->unsafeAttributes[$this->paramName];
            // для всех установленных языков
            foreach ($languages as $locale => $params) {
                $tag = $params['tag'];
                $model = $this->getModel($tag);
                if (!$isNewRecord) {
                    // попытка получить запись локализации из базы для определения изменения ёё значений
                    $loModel = $model->get($identifier, $params['code']);
                    // если запись отсутствует, необходимо указать атрибуты для ёё добавления без маски 
                    if ($loModel === null) {
                        $model->setPopulateAttributes([
                            $this->foreignKey => $identifier,
                            'language_id'     => $params['code']
                        ], false);
                    } else {
                        $model = $loModel;
                    }
                }
                // если переданы значения полей для модели локализации
                if (isset($loAttributes[$tag])) {
                    $model->load($loAttributes[$tag]);
                }
            }
        }
    }


    /**
     * Сохраняет атрибуты локализованных моделей данных. 
     * 
     * Аналогия с {@see \Ge\Db\ActiveRecord::save()}.
     *
     * @return void
     */
    public function save(): void
    {        
        // если добавили новую запись через форму (владельца), значит результатом будет 
        // последний идентификатор записи, иначе идентификатор редактируемой записи
        if ($isNewRecord = $this->form->isNewRecord()) {
            $identifier = $this->form->getResult();
            // если была ошибка при добавлении записи
            if (empty($identifier)) {
                return;
            }
        } else {
            // не забыть указать в менеджере данных формы (владельца) поле c 
            // первичным ключём `'fields' => [['field' => 'primaryKey'], ...], ...`, иначе будет `NULL`
            $identifier = $this->form->valuePrimaryKey();
        }
        /** @var \Ge\Language\AvailableLanguage $available установленные языки */ 
        $available = Ge::$app->language->available;
        foreach ($this->models as $languageTag => $model) {
            // конфигурация языка по указзаному тегу
            $language = $available->getBy($languageTag, 'tag');
            // добавление атрибутов без маски для связи с формой (владельцом)
            if ($isNewRecord) {
                $model->setPopulateAttributes([
                    $this->foreignKey => $identifier,
                    'language_id'     => $language['code']
                ], false);
            }
            $model->save();
        }
    }

    /**
     * Выполняет проверку значений атрибутов локализованных моделей данных.
     * 
     * Аналогия с {@see AbstractModel:validate()}.
     * 
     * @return bool Если `true`, проверка всех значений атрибутов была успешна.
     */
    public function validate(): bool
    {
        /** @var array $languages параметры конфигурации установленных языков */
        $languages = Ge::$app->language->available->getAll();
        // для всех установленных языков
        foreach ($languages as $locale => $params) {
            $model = $this->getModel($params['tag']);
            if (!$model->validate()) {
                $this->form->setError($model->getError());
                return false;
            }
        }
        return true;
    }

    /**
     * Заполняет атрибуты локализованных моделей значениями, полученными по запросу 
     * из базы данных.
     * 
     * @return void
     */
    public function fillModels(): void
    {
        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->form->getDb();
        /** @var null|string|int $id значение первичного ключа модели формы (владельца) */
        $id = $this->form->valuePrimaryKey();
        /** @var \Ge\Db\Sql\Select $select */
        $select = $db
            ->select($this->tableName)
            ->columns(['*'])
            ->where([$this->foreignKey => $id]);
        $rows = $db->createCommand($select)->queryAll();
        if ($rows) {
            /** @var \Ge\Language\AvailableLanguage $available установленные языки */ 
            $available = Ge::$app->language->available;
            foreach ($rows as $row) {
                if (isset($row['language_id'])) {
                    // конфигурация языка по указзаному коду
                    $language = $available->getBy($row['language_id'], 'code');
                    if ($language !== null) {
                        $model = $this->getModel($language['tag']);
                        if ($model !== null) {
                            $model::populate($model, $row);
                        }
                    }
                }
            }
        }
    }

    /**
     * Заполняет атрибуты формы (владельца) значениями атрибутов локализованным моделей данных.
     * 
     * Применяется при выводе значений атрибутов формы (владельца) в интерфейс.
     * 
     * @return void
     */
    public function fillAttributes(): void
    {
        if (!$this->hasModels()) {
            return;
        }

        foreach($this->models as $language => $model) {
            // если у модели есть атрибуты полученные через "get" или "load"
            if ($model->attributes !== null)
                foreach($model->attributes as $name => $value) {
                    $this->form->attributes["{$this->paramName}[$language][$name]"] = $value;
                }
        }
    }
}