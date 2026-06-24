<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl;

use Ge\Db\Sql\AbstractSql;
use Ge\Db\Adapter\Platform\PlatformInterface;

/**
 * Класс DropTable создаёт инструкцию SQL "DROP TABLE" для удаления таблицы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl
 * @since 2.0
 */
class DropTable extends AbstractSql implements SqlInterface
{
    /**
     * @var string Ключ "table" (удаление таблицы) в спецификации.
     */
    public const TABLE = 'table';

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::TABLE => 'DROP TABLE %1$s'
    ];

    /**
     * Имя таблицы.
     * 
     * @see DropTable::__construct()
     * 
     * @var string
     */
    protected string $table = '';

    /**
     * Конструктор класса.
     * 
     * @param string $table Имя таблицы (по умолчанию '').
     */
    public function __construct(string $table = '')
    {
        $this->table = $table;
    }

    /**
     * Возвращает выражение TABLE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * 
     * @return array
     */
    protected function processTable(PlatformInterface $adapterPlatform): array
    {
        return [$adapterPlatform->quoteIdentifier($this->table)];
    }
}
