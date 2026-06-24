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
 * Интерфейс драйвера адаптера подключения к базе данных.
 * 
 * @author Zend Framework (http://framework.zend.com/)
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver
 * @since 2.0
 */
interface DriverInterface
{
    const PARAMETERIZATION_POSITIONAL = 'positional';
    const PARAMETERIZATION_NAMED = 'named';
    const NAME_FORMAT_CAMELCASE = 'camelCase';
    const NAME_FORMAT_NATURAL = 'natural';

    /**
     * Возвращает название платформы базы данных.
     *
     * @param string $nameFormat Формат возвращаемого имени.
     * 
     * @return string
     */
    public function getDatabasePlatformName(string $nameFormat = self::NAME_FORMAT_CAMELCASE): string;

    /**
     * Проверяет окружение.
     *
     * @return bool
     */
    public function checkEnvironment(): bool;

    /**
     * Возвращает соединение с базой данных.
     *
     * @return object
     */
    public function getConnection();

    /**
     * Создаёт заявление.
     *
     * @param string|resource $sqlOrResource
     * 
     * @return object
     */
    public function createStatement($sqlOrResource = null);

    /**
     * Создаёт результат.
     *
     * @param resource $resource
     * 
     * @return object
     */
    public function createResult($resource);

    /**
     * Возвращает тип подготовки.
     *
     * @return array
     */
    public function getPrepareType(): array;

    /**
     * Выполняет формат параметра.
     *
     * @param string $name Имя параметра.
     * @param mixed $type Тип формата.
     * 
     * @return string
     */
    public function formatParameterName(string $name, mixed $type = null): string;

    /**
     * Возвращает последнее сгенерированное значение.
     *
     * @return mixed
     */
    public function getLastGeneratedValue(): mixed;
}
