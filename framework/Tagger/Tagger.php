<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Tagger;

use Ge\Stdlib\BaseObject;

/**
 * Tagger применяется для управления метками (тегами) терминов (terms).
 * 
 * Теггер - это менеджер меток (тегов), доступ к которому можно получить через `Ge::$app->tagger`.
 * 
 * Метки терминов - это метки компонентов веб-приложения, например: материалы, категории 
 * материалов, фотоальбомы и т.д.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Tagger
 * @since 2.0
 */
class Tagger extends BaseObject
{
    /**
     * Создаёт таблицу меток.
     * 
     * @see Tag::createTable()
     * 
     * @return void
     */
    public function createTagTable(): void
    {
        (new Tag())->createTable();
    }

    /**
     * Создаёт таблицу меток терминов.
     * 
     * @see TagTerms::createTable()
     * 
     * @return void
     */
    public function createTagTermsTable(): void
    {
        (new TagTerms())->createTable();
    }

    /**
     * Обновляет счетчики всех меток.
     * 
     * @see Tag::updateCounter()
     * 
     * @return void
     */
    public function updateTagsCounter(): void
    {
        (new Tag())->updateCounter(null);
    }

    /**
     * Обнуляет счетчик всех меток.
     * 
     * @see Tag::resetCounter()
     * 
     * @return void
     */
    public function resetTagsCounter(): void
    {
        (new Tag())->resetCounter(null);
    }

    /**
     * Добавляет метку.
     * 
     * @see Tag::save()
     * 
     * @param array<string, mixed> $attributes Атрибуты меток в виде пар "ключ - значение".
     * 
     * @return void
     */
    public function addTag(array $attributes): void
    {
        $tag = new Tag();
        $tag->setAttributes($attributes);
        $tag->save();
    }

    /**
     * Обновляет метку.
     * 
     * @see Tag::save()
     * 
     * @param array<string, mixed> $attributes Атрибуты меток в виде пар "ключ - значение".
     * @param int $tagId Идентификатор метки.
     * 
     * @return void
     */
    public function updateTag(array $attributes, int $tagId): void
    {
        /** @var Tag|null $tagId */
        $tag = (new Tag())->get($tagId);
        if ($tag) {
            $tag->setAttributes($attributes);
            $tag->save();
        }
    }

    /**
     * Удаляет метку.
     * 
     * @see Tag::delete()
     * 
     * @param int $tagId Идентификатор метки.
     * 
     * @return bool Возвращает значение `false`, если не удалось выполнить удаление.
     */
    public function deleteTag(int $tagId): bool
    {
        /** @var Tag|null $tagId */
        $tag = (new Tag())->get($tagId);
        if ($tag) {
            return $tag->delete() !== false;
        }
        return false;
    }

    /**
     * Удаляет метки.
     * 
     * @see TagTerms::deleteByTag()
     * 
     * @param array $tagsId Идентификаторы меток.
     * 
     * @return void
     */
    public function deleteTags(array $tagsId): void
    {
        $tag = new Tag();
        $tag->deleteByPk($tagsId);
        (new TagTerms())->deleteByTag($tagsId);
    }

    /**
     * Удаляет все метки и метки терминов.
     * 
     * @return void
     */
    public function deleteAllTags(): void
    {
        (new Tag())->deleteRecord([]);
        (new TagTerms())->deleteRecord([]);
    }

    /**
     * Удаляет записи меток терминов по указанному идентификатору записи.
     * 
     * @param array|int $rowId Идентификатор записи.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteTermTagsById(array|int $rowId): false|int
    {
        $result = (new TagTerms())->deleteById($rowId);
        if ($result !== false) {
            $this->updateTagsCounter();
        }
        return $result;
    }

    /**
     * Удаляет записи меток терминов по указанному идентификатору меток.
     * 
     * @param array|int $tagId Идентификатор метки.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteTermTagsByTag(array|int $tagId): false|int
    {
        $result = (new TagTerms())->deleteByTag($tagId);
        if ($result !== false) {
            $this->updateTagsCounter();
        }
        return $result;
    }

    /**
     * Удаляет записи меток терминов по указанному идентификатору термина.
     * 
     * @param array|int $termId Идентификатор термина.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteTermTagsByTerm(array|int $termId): false|int
    {
        $result = (new TagTerms())->deleteByTerm($termId);
        if ($result !== false) {
            $this->updateTagsCounter();
        }
        return $result;
    }

    /**
     * Удаляет записи меток терминов по указанному идентификатору группы.
     * 
     * @param array|int $groupId Идентификатор группы.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteTermTagsByGroup(array|int $groupId): false|int
    {
        $result = (new TagTerms())->deleteByGroup($groupId);
        if ($result !== false) {
            $this->updateTagsCounter();
        }
        return $result;
    }

    /**
     * Удаляет записи по указанному идентификатору записи и термину.
     * 
     * @param array|int $rowId Идентификатор записи или записей.
     * @param array|int $tagId Идентификатор метки или меток.
     * @param array|int $termId Идентификатор термина или терминов.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteTermTagsBy(
        array|int|null $rowId, 
        array|int|null $tagId, 
        array|int|null $termId, 
        array|int|null $groupId
    ): false|int
    {
        $result = (new TagTerms())->deleteBy($rowId, $tagId, $termId, $groupId);
        if ($result !== false) {
            $this->updateTagsCounter();
        }
        return $result;
    }

    /**
     * Возвращает все метки указанной записи с указанным термином.
     * 
     * @see Tag::getTermTags()
     * 
     * @param int $rowId Идентификатор записи (например, идент. материала).
     * @param int $termId Идентификатор термина (например, идент. термина материала "article").
     * @param array $order Порядок сортировки (по умолчанию `['name' => 'ASC']`).
     * @param int $limit Количество выводимых меток. Если '0', то все метки (по умолчанию '0').
     * 
     * @return array
     */
    public function getTermTags(int $rowId, int $termId, array $order = ['name' => 'ASC'], int $limit = 0): array
    {
        return (new Tag())->getTermTags($rowId, $termId, $order, $limit);
    }

    /**
     * Возвращает все метки.
     * 
     * @see Tag::fetchAll()
     * 
     * @param array $order Порядок сортировки (по умолчанию `['name' => 'ASC']`).
     * @param int $termId Идентификатор термина (например, идент. термина материала "article").
     * 
     * @return array
     */
    public function getTags(?int $languageId, array $order = ['name' => 'ASC'], ?int $termId = null): array
    {
        $where = ['visible' => 1];
        if ($languageId === null)
            $where[] = 'language_id IS NULL';
        else
            $where[] = 'language_id = ' . $languageId . ' OR language_id IS NULL';
        return (new Tag())->fetchAll(null, ['*'], $where, $order);
        //return (new Tag())->getTermTags($rowId, $termId, $order, $limit);
    }

    /**
     * Сохраняет (добавляет или удаляет) метки указанной записи и термина.
     * 
     * @see TagTerms::saveTags()
     * 
     * @param array<int, int> $tags Идентификаторы меток, которые необходимо сохранить.
     * @param int $rowId Идентификатор записи.
     * @param int $termId Идентификатор термина.
     * @param int|null $groupId Идентификатор группы записей.
     *
     * @return void
     */
    public function saveTermTags(array $tags, int $rowId, int $termId, ?int $groupId = null): void
    {
        (new TagTerms())->saveTags($tags, $rowId, $termId, $groupId);
        $this->updateTagsCounter();
    }

    /**
     * Возвращает метку по указанному слагу.
     * 
     * @param string $slug Слаг.
     * 
     * @return null|Tag Метка при успешном запросе, иначе `null`.
     */
    public function getTagBySlug(string $slug): ?Tag
    {
        return (new Tag())->getBySlug($slug);
    }
}
