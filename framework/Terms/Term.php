<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * @link https://rosgear.ru
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Terms;

use Ge\Db\ActiveRecord;

/**
 * Активная запись термина компонента веб-приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Tagger
 * @since 1.0
 */
class Term extends ActiveRecord
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
        return '{{term}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'            => 'id', // идентификатор
            'name'          => 'name', // название термина
            'componentId'   => 'component_id', // идентификатор компонента (модуль, расширение, виджет, плагин)
            'componentType' => 'component_type', // тип компонента (module, extension, widget, plugin)
        ];
    }


    /**
     * Возвращает запись по указанному идентификатору.
     * 
     * @see ActiveRecord::selectByPk()
     * 
     * @param mixed $identifier Идентификатор термина.
     * 
     * @return null|Term Активная запись при успешном запросе, иначе `null`.
     */
    public function get(mixed $identifier): ?static
    {
        return $this->selectByPk($identifier);
    }

    /**
     * Возвращает метку по указанному слагу.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param string $name Название.
     * @param string $componentId Идентификатор компонента.
     * 
     * @return null|Term Термин при успешном запросе, иначе `null`.
     */
    public function getByName(string $name, string $componentId): ?static
    {
        return $this->selectOne([
            'name'         => $name,
            'component_id' => $componentId
        ]);
    }

    /**
     * Создаёт таблицу терминов.
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
            `name` varchar(255) DEFAULT NULL,
            `component_id` varchar(100) DEFAULT NULL,
            `component_type` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE={engine} 
        DEFAULT CHARSET={charset} COLLATE {collate}";
        $command = $this->db->createCommand();
        $command
            ->createTable($sql)
            ->execute();
    }
}
