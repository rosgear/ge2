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
use mysqli as PhpMySqli;
use Ge\Db\Adapter\Driver\AbstractConnection;
use Ge\Db\Adapter\Exception\ConnectException;
use Ge\Db\Adapter\Driver\Exception\DriverException;

/**
 * Подключение с помощью драйвера "MySqli" к серверу базы данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver\MySqli
 * @since 2.0
 */
class Connection extends AbstractConnection
{
    /**
     * {@inheritdoc}
     */
    protected string $driverName = 'MySqli';

    /**
     * {@inheritdoc}
     * 
     * @var PhpMySqli|null
     */
    protected ?object $resource = null;

    /**
     * {@inheritdoc}
     * 
     * @throws ConnectException Ошибка соединения.
     * @throws DriverException Ошибка установки кодировки.
     */
    public function connect(): static
    {
        if ($this->resource) return $this;

        $this->resource = new PhpMySqli();
        // $this->resource->init(); deprecated php 8.1+

        try {
            @$this->resource->real_connect(
                $this->parameters['host'] ?? '',
                $this->parameters['username'] ?? '',
                $this->parameters['password'] ?? '',
                $this->parameters['schema'] ?? '',
                $this->parameters['port'] ?? '',
                $this->parameters['socket'] ?? '',
            );
        // т.к. может быть установлен режим MYSQLI_REPORT_STRICT, то необходимо проверить catch
        } catch (\mysqli_sql_exception $e) {
            throw new ConnectException(
                Ge::t('app', 'Connection error: when making a connection to the server'), $this->resource->connect_error
            );
        }

        // ошибка соединенния
        if ($this->resource->connect_error) {
            throw new ConnectException(
                Ge::t('app', 'Connection error: when making a connection to the server'), $this->resource->connect_error
            );
        }

        // установка кодировки
        $charset = $this->parameters['charset'] ?? '';
        if (!empty($charset)) {
            if (!$this->resource->set_charset($charset))
                throw new DriverException(
                    Ge::t('app', 'Connection error: loading {0} character set', [$charset]), $this->resource->error
                );
        }
        $this->connected = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): static
    {
        if ($this->isConnected()) {
            $this->resource->close();
            $this->resource = null;
            $this->connected = false;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastGeneratedValue(): int|string
    {
        return $this->resource ? $this->resource->insert_id : 0;
    }
}
