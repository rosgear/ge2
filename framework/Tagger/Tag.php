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
 * Активная запись метки (тега).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Tagger
 * @since 1.0
 */
class Tag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'         => 'id', // идентификатор метки
            'parentId'   => 'parent_id', // идентификатор родителя метки
            'languageId' => 'language_id', // идентификатор языка
            'name'       => 'name', // название метки
            'desc'       => 'desc', // описание
            'slug'       => 'slug', // слаг метки
            'counter'    => 'counter', // количество терминов имеющих метку (JSON формат)
            'style'      => 'style', // стили CSS тега
            'image'      => 'image', // изображение в теге
            'hits'       => 'hits', // количество просмотров
            'visible'    => 'visible' // видимость метки
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteDependencies(mixed $condition): void
    {
        (new TagTerms())->deleteByTag($condition[$this->primaryKey()]);
    }

    /**
     * Обновляет счетчик всех меток.
     * 
     * @param int|null $tagId Обновление счетчика указанной метки. Если значение `null`,
     *     то обновит счетчики всех меток (по умолчанию `null`).
     * 
     * @return void
     */
    public function updateCounter(?int $tagId = null): void
    {
        $tblTag = $this->tableName();
        $tblTagTerms = '{{tag_terms}}';

        $sql = "UPDATE $tblTag, "
             . "(SELECT tag_id, COUNT(id) counter FROM $tblTagTerms "
             . ($tagId ? 'WHERE tag_id = ' . $tagId : '')
             . 'GROUP BY tag_id) term '
             . "SET $tblTag.counter = term.counter "
             . 'WHERE id = term.tag_id';

        $this->db
            ->createCommand($sql)
                ->execute();
    }

    /**
     * Обнуляет счетчик всех меток.
     * 
     * @param int|null $tagId Обнуляет счетчик указанной метки. Если значение `null`,
     *     то обнулит счетчики всех меток (по умолчанию `null`).
     * 
     * @return void
     */
    public function resetCounter(?int $tagId = null): void
    {
        $this->updateRecord(['counter' => 0], $tagId ? ['tag_id' => $tagId] : null);
    }

    /**
     * Проверяет, отображается ли метка.
     * 
     * @return bool
     */
    public function isVisible(): bool
    {
        return (int) $this->visible > 0;
    }

    /**
     * Возвращает запись по указанному идентификатору.
     * 
     * @see ActiveRecord::selectByPk()
     * 
     * @param mixed $identifier Идентификатор метки.
     * 
     * @return null|Tag Активная запись при успешном запросе, иначе `null`.
     */
    public function get(mixed $identifier): ?static
    {
        return $this->selectByPk($identifier);
    }

    /**
     * Возвращает метку по указанному слагу.
     * 
     * @see ActiveRecord::selectByPk()
     * 
     * @param string $slug Слаг метки.
     * 
     * @return null|Tag Метка при успешном запросе, иначе `null`.
     */
    public function getBySlug(string $slug): ?static
    {
        return $this->selectOne(['slug' => $slug]);
    }

    /**
     * Возвращает все метки указанной записи с указанным термином.
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
        $tblTag = $this->tableName();
        $tblTagTerms = '{{tag_terms}}';

        $sql = "SELECT `tg`.* FROM $tblTag tg, "
             . "(SELECT * FROM $tblTagTerms WHERE id = $rowId AND term_id = $termId) tt " 
             . 'WHERE tg.id = tt.tag_id AND tg.visible = 1 ';
        if ($order) {
            $_order = [];
            foreach ($order as $field => $sort) {
                $_order[] = 'tg.' . $field . ' ' . $sort;
            }
            $sql .= 'ORDER BY ' . implode(', ', $_order);
        }
        if ($limit > 0) $sql .= 'LIMIT ' . $limit;

        return $this->db
                ->createCommand($sql)
                    ->queryAll();        
    }

    /**
     * Создаёт таблицу меток.
     * 
     * @return void
     */
    public function createTable(): void
    {
        $table  = $this->tableName();

        $command = $this->db->createCommand();
        if ($command->tableExists($table)) return;

        $sql = "CREATE TABLE `$table` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_id` int(11) unsigned DEFAULT NULL,
            `language_id` int(11) unsigned DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `desc` text DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `image` text DEFAULT NULL,
            `style` text DEFAULT NULL,
            `counter` int(11) unsigned DEFAULT 0,
            `hits` int(11) unsigned DEFAULT 0,
            `visible` tinyint(1) unsigned DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE={engine} 
        DEFAULT CHARSET={charset} COLLATE {collate}";
        $command = $this->db->createCommand();
        $command
            ->createTable($sql)
            ->execute();
    }
}
