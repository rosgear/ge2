<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * @link https://rosgear.ru
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Tagger;

use Ge\Db\ActiveRecord;

/**
 * Активная запись меток (тегов) терминов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Tagger
 * @since 1.0
 */
class TagTerms extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * 
     * Среди составного первичного ключа, играет основную роль `tag_id`, т.к. он 
     * обеспечивает связь 1-н ко многим.
     */
    public function primaryKey(): string
    {
        return 'tag_id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{tag_terms}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'      => 'id', // идентификатор записи
            'tagId'   => 'tag_id', // идентификатор метки
            'termId'  => 'term_id', // идентификатор термина
            'groupId' => 'group_id', // идентификатор группы
        ];
    }

    /**
     * Удаляет записи по указанному идентификатору записи.
     * 
     * @param array|int $rowId Идентификатор записи.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteById(array|int $rowId): false|int
    {
        return $this->deleteRecord(['id' => $rowId]);
    }

    /**
     * Удаляет записи по указанному идентификатору тега.
     * 
     * @param array|int $tagId Идентификатор тега.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteByTag(array|int $tagId): false|int
    {
        return $this->deleteRecord(['tag_id' => $tagId]);
    }

    /**
     * Удаляет записи по указанному идентификатору термина.
     * 
     * @param array|int $termId Идентификатор термина.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteByTerm(array|int $termId): false|int
    {
        return $this->deleteRecord(['term_id' => $termId]);
    }

    /**
     * Удаляет записи по указанному идентификатору группы.
     * 
     * @param array|int $groupId Идентификатор группы.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteByGroup(array|int $groupId): false|int
    {
        return $this->deleteRecord(['group_id' => $groupId]);
    }

    /**
     * Удаляет записи по указанным идентификаторам.
     *
     * @param array|int $rowId Идентификатор записи или записей.
     * @param array|int $tagId Идентификатор метки или меток.
     * @param array|int $termId Идентификатор термина или терминов.
     * @param int|null $groupId Идентификатор группы или групп записей.
     * 
     * @return false|int Возвращает значение `false`, если ошибка выполнения запроса. 
     *     Иначе, количество удалённых записей.
     */
    public function deleteBy(
        array|int|null $rowId, 
        array|int|null $tagId, 
        array|int|null $termId, 
        array|int|null $groupId
    ): false|int
    {
        $where = [];
        if ($rowId)   $where['id'] = $rowId;
        if ($tagId)   $where['tag_id'] = $tagId;
        if ($termId)  $where['term_id'] = $termId;
        if ($groupId) $where['group_id'] = $groupId;
        return $this->deleteRecord($where);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where['id']      = $this->id;
        $where['tag_id']  = $this->tagId;
        if ($this->termId) $where['term_id'] = $this->termId;
        if ($this->groupId) $where['group_id'] = $this->groupId;
    }

    /**
     * Возвращает метки указанной записи и термина.
     * 
     * @param int $rowId Идентификатор записи.
     * @param int $termId Идентификатор термина.
     * @param bool $onlyId Возвращает все столбцы если значение `false`. Иначе только 
     *     идентификаторы меток (по умолчанию `true`).
     * 
     * @return array
     */
    public function getTags(int $rowId, int $termId, bool $onlyId = true): array
    {
        /** @var \Ge\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        /** @var \Ge\Db\Sql\Select $select */
        $select = $db
            ->select($this->tableName())
            ->columns([$onlyId ? 'tag_id' : '*'])
            ->where([
                'id'      => $rowId,
                'term_id' => $termId
            ]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand($select);
        /** @var array $rows */
        $rows = $onlyId ? $command->queryColumn() : $command->queryAll();
        return $rows;
    }

    /**
     * Сохраняет (добавляет или удаляет) метки указанной записи и термина.
     * 
     * Например, для материала с идентификатором '100' и термином материала с 
     * с идентификатором '2' необходимо сохранить метки `[7, 8, 9]`. Тогда запись
     * будет иметь вид:
     * ```php
     * saveTags([7, 8, 9], 100, 2)
     * ```
     * 
     * @see saveTags::getTags()
     * @see saveTags::addTags()
     * @see saveTags::deleteTags()
     * 
     * @param array<int, int> $tags Идентификаторы меток, которые необходимо сохранить.
     * @param int $rowId Идентификатор записи.
     * @param int $termId Идентификатор термина.
     * @param int|null $groupId Идентификатор группы записей.
     *
     * @return void
     */
    public function saveTags(array $tags, int $rowId, int $termId, ?int $groupId = null): void
    {
        // теги не указаны (их нет), но они могут быть в таблице, для это убедимся
        // удалив их
        if (empty($tags)) {
            $this->deleteTags($tags, $rowId, $termId);
            return;
        }

        $allTags = $this->getTags($rowId, $termId);
        $toAdd = array_diff($tags, $allTags);
        // если необходимо добавить
        if ($toAdd) {
            $this->addTags($toAdd, $rowId, $termId, $groupId);
        }
        $toDelete = array_diff($allTags, $tags);
        // если необходимо удалить
        if ($toDelete) {
            $this->deleteTags($toDelete, $rowId, $termId);
        }
    }

    /**
     * Добавляет метки к указанной записи и термину.
     *
     * @param array<int, int> $tags Идентификаторы меток, которые будут добавлены.
     * @param int $rowId Идентификатор записи.
     * @param int $termId Идентификатор термина.
     * @param int|null $groupId Идентификатор группы записей.
     * 
     * @return void
     */
    public function addTags(array $tags, int $rowId, int $termId, ?int $groupId = null): void
    {
        foreach ($tags as $tagId) {
            $this->insertRecord([
                'id'       => $rowId,
                'tag_id'   => $tagId,
                'term_id'  => $termId,
                'group_id' => $groupId
            ]);
        }
    }

    /**
     * Удаляет метки из указанной записи и термина.
     *
     * @param array<int, int> $tags Идентификаторы меток, которые будут удалены.
     * @param int $rowId Идентификатор записи.
     * @param int $termId Идентификатор термина.
     * @param int|null $groupId Идентификатор группы записей.
     *
     * @return false|int Значение `false`, если ошибка выполнения инструкции SQL. 
     *     Иначе количество удалённых записей.
     */
    public function deleteTags(array $tags, int $rowId, int $termId, ?int $groupId = null): false|int
    {
        $where = ['id' => $rowId, 'term_id' => $termId];

        if ($tags) $where['tag_id'] = $tags;
        if ($groupId) $where['group_id'] = $groupId;
        return $this->deleteRecord($where);
    }

    /**
     * Возвращает запись по указанному значению первичного ключа.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $id Идентификатор записи.
     * @param int $tagId Идентификатор метки.
     * @param int $termId Идентификатор термина.
     * @param int|null $groupId Идентификатор группы записей.
     * 
     * @return TagTerms|null Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $id, int $tagId, int $termId, ?int $groupId = null): ?static
    {
        $where = ['id' => $id, 'tag_id' => $tagId, 'term_id' => $termId];

        if ($groupId) $where['group_id'] = $groupId;
        return $this->selectOne($where);
    }

    /**
     * Создаёт таблицу.
     * 
     * @return void
     */
    public function createTable(): void
    {
        $table  = $this->tableName();

        $command = $this->db->createCommand();
        if ($command->tableExists($table)) return;

        $sql = "CREATE TABLE `$table` (
            `id` int(11) unsigned NOT NULL,
            `tag_id` int(11) unsigned NOT NULL,
            `term_id` int(11) unsigned NOT NULL,
            `group_id` int(11) unsigned NOT NULL,
            PRIMARY KEY (`id`,`tag_id`,`term_id`)
        ) ENGINE={engine} 
        DEFAULT CHARSET={charset} COLLATE {collate}";
        $command = $this->db->createCommand();
        $command
            ->createTable($sql)
            ->execute();
    }

    /**
     * Удаляет все записи.
     * 
     * @throws \Ge\Db\Adapter\Driver\Exception\CommandException Невозможно выполнить инструкцию SQL.
     */
    public function deleteAll()
    {
        $this->getDb()
            ->createCommand()
                ->truncateTable($this->tableName())
                ->execute();
    }
}
