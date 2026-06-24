<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Driver\MySqli;

use Ge;
use PDO;
use Ge\Db\Adapter\Exception;
use Ge\Db\Adapter\Driver\AbstractCommand;

/**
 * Команда для выполнения драйвером "MySqli" инструкций SQL к серверу базы данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver\MySqli
 * @since 2.0
 */
class Command extends AbstractCommand
{
    /**
     * {@inheritdoc}
     * 
     * @link https://www.php.net/manual/ru/class.mysqli-result.php
     * 
     * @var mysqli_result|false
     */
    public mixed $result = false;

    /**
     * {@inheritdoc}
     */
    public function execute(): static
    {
        
        if (empty($this->sql)) {
            throw new Exception\CommandException(
                Ge::t('app', 'Can not execute SQL request, query is empty')
            );
        }

        $sql = $this->getRawSql();
        // начало профилирования запроса
        $this->db->beginProfile();

        try {
            $this->result = $this->resource->query($sql);
        } catch (\mysqli_sql_exception $e) {
            $this->result = false;
        }

        // конец профилирования запроса
        $this->db->endProfile([
            'sql'    => $this->sql,
            'rawSql' => $sql,
            'error'  => $this->result === false ? $this->getError() : [],
            'params' => $this->params
        ]);

        // ошибка выполнения запроса
        if ($this->result === false) {
            throw new Exception\CommandException(
                GE_MODE_DEV ? 
                    Ge::t('app', 'Query error: {0}, {1}', [$this->getError(), $sql]) : 
                    Ge::t('app', 'Error executing database query'),
                $this->getError(),
                $sql
            );
        }
        $this->params = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function query(): static
    {
        if (empty($this->sql)) {
            throw new Exception\CommandException(
                Ge::t('app', 'Can not execute SQL request, query is empty')
            );
        }
        $sql = $this->getRawSql();

        // начало профилирования запроса
        $this->db->beginProfile();

        try {
            $this->result = $this->resource->query($sql);
        } catch (\mysqli_sql_exception $e) {
            $this->result = false;
        }

        // конец профилирования запроса
        $this->db->endProfile([
            'sql'    => $this->sql,
            'rawSql' => $sql,
            'error'  => $this->result === false ? $this->getError() : [],
            'params' => $this->params,
            'result' => $this->getResultInfo()
        ]);

        // ошибка выполнения запроса
        if ($this->result === false) {
            throw new Exception\CommandException(
                GE_MODE_DEV ? 
                Ge::t('app', 'Query error: {0}, {1}', [$this->getError(), $sql]) : 
                Ge::t('app', 'Error executing database query'),
                $this->getError(),
                $sql
            );
        }
        $this->params = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): mixed
    {
        if ($this->result === false) {
            return false;
        }

        // результат возвращается в виде ассоциативного массива с именами полей в 
        // качестве индексов
        if ($this->fetchMode === PDO::FETCH_ASSOC) {
            return $this->result->fetch_assoc();
        }
        //  результат возвращается в виде массива объектов
        if ($this->fetchMode === PDO::FETCH_OBJ) {
            return $this->result->fetch_object();
        }
        // результат возвращается в виде индексного массива 
        if ($this->fetchMode === PDO::FETCH_NUM) {
            return $this->result->fetch_row();
        }
        // результат возвращается в виде массива, который содержит как числовой, 
        // так и ассоциативный индексы
        if ($this->fetchMode === PDO::FETCH_BOTH) {
            return $this->result->fetch_array(MYSQLI_BOTH);
        }
        return false;
    }

    /**
     * Выбирает все строки из результирующего набора и помещает их в ассоциативный 
     * массив, обычный массив или в оба.
     * 
     * @param int $fetchMode Режим получения данных: `PDO::FETCH_ASSOC`, `PDO::FETCH_NUM`, 
     *     `PDO::FETCH_BOTH`.
     * 
     * @return mixed Возвращает массив объектов, строк или `false`, если рядов больше нет. 
     */
    public function fetchAllRows(int $fetchMode): mixed
    {
        if ($this->result === false) {
            return false;
        }

        // результат возвращается в виде ассоциативного массива с именами полей в 
        // качестве индексов
        if ($fetchMode === PDO::FETCH_ASSOC) {
            return $this->result->fetch_all(MYSQLI_ASSOC);
        }
        // результат возвращается в виде индексного массива 
        if ($fetchMode === PDO::FETCH_NUM) {
            return $this->result->fetch_all(MYSQLI_NUM);
        }
        // результат возвращается в виде массива, который содержит как числовой, 
        // так и ассоциативный индексы
        if ($fetchMode === PDO::FETCH_BOTH) {
            return $this->result->fetch_all(MYSQLI_BOTH);
        }
        return false;
    }

    /**
     * Формирует инструкцию SQL для создания базы данных.
     * 
     * @link https://dev.mysql.com/doc/refman/8.0/en/charset-database.html
     * 
     * @param string $database Имя базы данных. 
     * @param string|null $charset Набор символов. 
     * @param string|null $collate Набор символов для сравнения. 
     * 
     * @return $this
     */
    public function createDatabase(string $database, ?string $charset = null, ?string $collate = null): static
    {
        $sql = 'CREATE DATABASE ' . $this->platform->quoteIdentifier($database);
        if ($charset) {
            $sql .= ' CHARACTER SET ' . $charset;
        }
        if ($collate) {
            $sql .= ' COLLATE ' . $collate;
        }
        $this->setSql($sql);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createTable(string $sql): static
    {
        $params = $this->connection->getParameters();
        $this->setSql(
            str_replace(
                ['{engine}', '{charset}', '{collate}'], 
                [$params['engine'], $params['charset'], $params['collate']], 
            $sql)
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFoundRows(): int
    {
        $this->setSql('SELECT FOUND_ROWS()');
        $row = $this->queryScalar();
        return $row ?: 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resetIncrement(string $table, int|string $increment = 1): static
    {
        $this->setSql("ALTER TABLE `$table` AUTO_INCREMENT = $increment");
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function truncateTable(string $table): static
    {
        $this->setSql('TRUNCATE TABLE ' . $this->platform->quoteIdentifier($table));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable(string $table, ?string $exists = null): static
    {
        if ($exists)
            $sql = "DROP TABLE IF $exists " . $this->platform->quoteIdentifier($table);
        else
            $sql = 'DROP TABLE ' . $this->platform->quoteIdentifier($table);
        $this->setSql($sql);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): string
    {
        return isset($this->resource->error) ? $this->resource->error : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedRows(): int
    {
        return isset($this->resource->affected_rows) ? (int) $this->resource->affected_rows : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultInfo(): mixed
    {
        if (is_bool($this->result)) {
            return $this->result;
        }
        if (empty($this->result)) {
            return false;
        }
        return [
            'fieldCount' => $this->result->field_count,
            'numRows'    => $this->result->num_rows,
            'lengths'    => $this->result->lengths
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCharsets(string $column = 'Charset'): array
    {
        $this->setSql('SHOW CHARACTER SET');
        $this->execute();

        $this->fetchMode = PDO::FETCH_ASSOC;
        return $this->fetchAll($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollations(string $column = 'Collation'): array
    {
        $this->setSql('SHOW COLLATION');
        $this->execute();

        $this->fetchMode = PDO::FETCH_ASSOC;
        return $this->fetchAll($column);
    }

    /**
     * {@inheritdoc}
     */
    public function tableExists(string $table, ?string $schema = null): bool
    {
        if ($schema === null) {
            $schema = $this->db->getConnection()->getParam('schema', '');
        }

        $this->setSql('SHOW TABLES WHERE Tables_in_' . $schema . ' LIKE ' . $this->platform->quoteValue($table));
        /** @var null|array $row */
        $row = $this->queryOne();
        return $row !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableStatus(string $table): mixed
    {
        $this->setSql('SHOW TABLE STATUS LIKE ' . $this->platform->quoteValue($table));
        return $this->queryOne();
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrement(string $table): int|string
    {
        $this->fetchMode = PDO::FETCH_ASSOC;
        $status = $this->getTableStatus($table);
        return $status ? ($status['Auto_increment'] ?? 0) : 0;
    }
}
