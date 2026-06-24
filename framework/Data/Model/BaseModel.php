<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license//
 */

namespace Ge\Data\Model;

use Ge;
use Closure;
use Ge\Db\Sql\Where;
use Ge\Stdlib\BaseObject;
use Ge\Db\Adapter\Adapter;
use Ge\Db\Adapter\Driver\AbstractCommand;

/**
 * Модель данных является базовым классом для всех классов-наследников модели.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
class BaseModel extends BaseObject
{
    /**
     * Возвращает адаптера подключения к базе данных.
     * 
     * @return Adapter
     */
    public function getDb(): Adapter
    {
        return Ge::$app->db;
    }

    /**
     * Сбрасывает автоинкремент первичного ключа таблицы к указанному значению.
     * 
     * @param int|string $increment Значение автоинкремента первичного ключа.
     * @param null|string $tableName Имя таблицы (по умолчанию `null`).
     * 
     * @return void
     */
    public function resetIncrement(int|string $increment = 1, ?string $tableName = null): void
    {
        $this->getDb()
            ->createCommand()
                ->resetIncrement($tableName, $increment)
                ->execute();
    }

    /**
     * Удаляет записи из таблицы.
     * 
     * @param Where|Closure|string|array $where Условие выполнения запроса.
     * @param null|string $tableName Имя таблицы (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе, 
     *     количество удалённых записей.
     */
    public function deleteRecord(Where|Closure|string|array $where, ?string $tableName = null): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->getDb()
            ->createCommand()
                ->delete($tableName, $where)
                ->execute();
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * Обновляет записи таблицы.
     * 
     * @param array $columns Cтолбцы таблицы с их значениями в виде пар "ключ - значение".
     * @param Where|Closure|string|array|null $where Условие выполнения запроса (по умолчанию `null`).
     * @param null|string $tableName Имя таблицы (по умолчанию `null`).
     * 
     * @return false|int Если значение `false`, ошибка выполнения запроса. Иначе количество 
     *     обновленных записей.
     */
    public function updateRecord(
        array $columns, 
        Where|Closure|string|array|null $where = null, 
        ?string $tableName = null
    ): false|int
    {
        /** @var AbstractCommand $command */
        $command = $this->getDb()
            ->createCommand()
                ->update($tableName, $columns, $where)
                ->execute();
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * Добавляет запись.
     * 
     * @param array $columns Имена столбцов таблицы с их значениями в виде пар "ключ - значение".
     * @param null|string $tableName Имя таблицы (по умолчанию `null`).
     * 
     * @return int|string Идентификатор добавленной записи.
     */
    public function insertRecord(array $columns, ?string $tableName = null): int|string
    {
        /** @var Adapter $db */
        $db = $this->getDb();
        $db->createCommand()
            ->insert($tableName, $columns)
            ->execute();
        return $db->getConnection()->getLastGeneratedValue();
    }

    /**
     * Выбирает все строки по указанной инструкции SQL из результирующего набора и 
     * помещает их в объект, ассоциативный массив, обычный массив или в оба.
     * 
     * Возвращаемый результат определяется видом записей {@see AbstractCommand::$fetchMode}.

     * @param string $sql Инструкция SQL.
     * 
     * @return array
     */
    public function selectBySql(string $sql): array
    {
        return $this->getDb()
            ->createCommand($sql)
                ->queryAll();
    }
}
