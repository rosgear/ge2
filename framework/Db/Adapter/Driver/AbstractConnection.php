<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Driver;

/**
 * Соединение является абстрактным классом для подключения к серверу базы данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver
 * @since 4.0
 */
abstract class AbstractConnection
{
    /**
     * Название драйвера подключения к базе данных ('MySqli', 'MySql', 'Oracle'...)
     * 
     * @var string
     */
    protected string $driverName = '';

    /**
     * Параметры соединения с сервером базы данных
     * 
     * @var array
     */
    protected array $parameters = [];

    /**
     * Состояние соединения с сервером.
     * 
     * Если значение `true`  - соединение установлено.
     * 
     * @var bool
     */
    protected bool $connected = false;

    /**
     * Указатель на объект подключения к базе данных.
     * 
     * Например, для связи с базой данных MySQL используется класс 'mysqli'.
     * 
     * @var object|null
     */
    protected ?object $resource = null;

    /**
     * Конструктор класса.
     * 
     * @param null|array $connectionInfo Параметры соединения.
     * 
     * @return void
     */
    public function __construct(?array $connectionInfo = null)
    {
        if ($connectionInfo) {
            $this->setParameters($connectionInfo);
        }
    }

    /**
     * Деструктор класса.
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Открывает соединения с сервером базы данных.
     * 
     * @return $this
     */
    public function connect(): static
    {
        return $this;
    }

    /**
     * Закрывает соединения с сервером базы данных.
     * 
     * @return $this
     */
    public function disconnect(): static
    {
        if ($this->isConnected()) {
            $this->resource = null;
        }
        return $this;
    }

    /**
     * Возвращает указатель на объект работы с сервером базы данных.
     *
     * @return object
     */
    public function getResource(): object
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        return $this->resource;
    }

    /**
     * Возвращает параметры соединения с сервером базы данных.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Возвращает значение указанного параметра соединения с сервером базы данных.
     * 
     * @param string $name Имя параметра.
     * @param mixed|null $default Значение по умолчанию, если параметр не найден.
     * 
     * @return mixed
     */
    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Возвращает название драйвера.
     *
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driverName;
    }

    /**
     * Проверяет соединения с сервером базы данных.
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Установка параметров соединения с сервером базы данных.
     * 
     * @param array $parameters Параметры соединения.
     * 
     * @return $this
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Возвращает последнее сгенерированное значение автоинкремента таблицы.
     * 
     * @return int|string
     */
    public function getLastGeneratedValue(): int|string
    {
        return 0;
    }
}
