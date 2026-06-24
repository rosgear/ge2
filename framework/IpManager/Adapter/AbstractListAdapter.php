<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\IpManager\Adapter;

use Ge\Exception\InvalidArgumentException;

/**
 * Абстрактный класс адаптера, списка диапазонов IP-адресов.
 * 
 * Адаптер предназначен для создания и проверки вхождения указанных 
 * IPv4 и IPv6-адресов в диапазон.
 * 
 * Список диапазонов состоит из атрибутов записей IP-адресов:
 *     - `id`, уникальный идентификатор записи;
 *     - `address`, IPv4 или IPv6-адрес;
 *     - `range_begin`, начало диапазона (числовой), полученный из IP-адреса и его маски;
 *     - `range_end`, конец диапазона (числовой), полученный из IP-адреса и его маски;
 *     - `range_address`, диапазон (строка), полученный из IP-адреса и его маски;
 *     - `note`, описание;
 *     - `backend`, принадлежность диапазону к backend;
 *     - `frontend`, принадлежность диапазону к frontend.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\IpManager\Adapter
 * @since 2.0
 */
abstract class AbstractListAdapter implements AdapterInterface
{
    /**
     * Настройки адаптера.
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Имя таблицы списка диапазонов IP-адресов в базе данных.
     * 
     * @var string
     */
    protected string $tableName;

    /**
     * Ошибка полученная при выполнении запроса.
     * 
     * @var string
     */
    protected ?string $error = null;

    /**
     * Атрибуты записи IP-адреса.
     * 
     * @var array
     */
    protected array $attributes = [];

    /**
     * Конструктор класса.
     * 
     * @param array $options Настройки адаптера.
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->tableName = $options['tableName'] ?? null;
    }

    /**
     * Проверяет, входит ли IPv4 или IPv6-адрес в диапазон IP-адресов списка,  
     * принадлежаего сайту (frontend).
     * 
     * Если IP-адрес входит в перый же диапазон, то информация будет возвращена 
     * только об этом диапазоне (для `$check = false`), остальные диапазоны будут 
     * пропущены.
     * 
     * @param string $ipAddress Действующий IPv4 или IPv6-адрес.
     * @param bool $check Если true, результатом будет `bool`, иначе `array`.
     * 
     * @return mixed Если указанный IP-адрес не входит в диапазон, то результат 
     *    `false` или `null`. Если диапазон принадлежит IP-адресу, то `true` или `array` 
     *    (информация о диапазоне указанного IP-адреса).
     */
    public function inFrontendRange(string $ipAddress, bool $check = true): mixed
    {
        return false;
    }

    /**
     * Проверяет, входит ли IPv4 или IPv6-адрес в диапазон IP-адресов списка, 
     * принадлежаего панели управления (backend).
     * 
     * Если IP-адрес входит в перый же диапазон, то информация будет возвращена 
     * только об этом диапазоне (для `$check = false`), остальные диапазоны будут 
     * пропущены.
     * 
     * @param string $ipAddress Действующий IPv4 или IPv6-адрес.
     * @param bool $check Если true, результатом будет `bool`, иначе `array`.
     * 
     * @return mixed Если указанный IP-адрес не входит в диапазон, то результат 
     *    `false` или `null`. Если диапазон принадлежит IP-адресу, то `true` или `array` 
     *    (информация о диапазоне указанного IP-адреса).
     */
    public function inBackendRange(string $ipAddress, bool $check = true): mixed
    {
        return false;
    }

    /**
     * Проверяет, входит ли IPv4 или IPv6-адрес в диапазон IP-адресов списка.
     * 
     * Если IP-адрес входит в перый же диапазон, то информация будет возвращена 
     * только об этом диапазоне (для `$check = false`), остальные диапазоны будут 
     * пропущены.
     * 
     * @param string $ipAddress Действующий IPv4 или IPv6-адрес.
     * @param int|string $side Диапазон IP-адресов принадлежащих одной из сторон:
     *     - `0`, `backend` (BACKEND), панель управления;
     *     - `1`, `frontend` (FRONTEND), сайт.
     * @param bool $check Если true, результатом будет `bool`, иначе `array`.
     * 
     * @return mixed Если указанный IP-адрес не входит в диапазон, то результат 
     *    `false` или `null`. Если диапазон принадлежит IP-адресу, то `true` или `array` 
     *    (информация о диапазоне указанного IP-адреса).
     * 
     * @throws InvalidArgumentException Неправильно указан аргумент $side.
     */
    public function inRange(string $ipAddress, $side, bool $check = true): mixed
    {
        if ($side === 0 || $side === BACKEND)
            return $this->inBackendRange($ipAddress, $check);
        else
        if ($side === 1 || $side === FRONTEND)
            return $this->inFrontendRange($ipAddress, $check);
        else
            throw new InvalidArgumentException(
                sprintf('Unable to check range, side argument "%s" is incorrect', $side)
            );
    }

    /**
     * Добавляет запись IP-адреса в список.
     * 
     * @param array $attributes Атрибуты записи IP-адреса.
     * 
     * @return bool Возвращает значение `false`, если информация о записи IP-адреса 
     *     не добавлена.
     */
    public function add(array $attributes): bool
    {
        return false;
    }

    /**
     * Обновляет запись IP-адреса в списке.
     * 
     * @param array $attributes Атрибуты записи IP-адреса.
     * @param int $id Идентификатор записи IP-адреса.
     * 
     * @return bool Возвращает значение `false`, если информация о записи IP-адреса 
     *     не добавлена.
     */
    public function update(array $attributes, int $id): bool
    {
        return false;
    }

    /**
     * Удаляет запись IP-адреса из списка.
     * 
     * @param null|int $id Идентификатор записи IP-адреса.
     * 
     * @return bool Возвращает значение `false`, если информация о записи IP-адреса 
     *     не удалена.
     */
    public function remove(?int $id = null): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->info = null;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return false;
    }

    /**
     * Возвращает информацию о записи IP-адреса.
     * 
     * @param null|int $id Идентификатор записи IP-адреса.

     * @return mixed Возвращает значение `false`, если информация о записи IP-адреса 
     *     отсутствует.
     */
    public function get(?int $id = null): mixed
    {
        return false;
    }

    /**
     * Проверяет, была ли ошибка в последнем запросе.
     * 
     * @see AbstractListAdapter::$error
     * 
     * @return bool Возвращает значение `true`, если ошибка была в последнем запросе.
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Сброс ошибки, полученной в последнем запросе.
     * 
     * @see AbstractListAdapter::$error
     * 
     * @return void
     */
    public function resetError(): void
    {
        $this->error = null;
    }

    /**
     * Возвращает ошибку полученную в последнем запросе.
     * 
     * @see AbstractListAdapter::$error
     * 
     * @return null|string Возвращает значение `null`, если ошибки отсутствует.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Устанавливает значение атрибута записи IP-адреса.
     *
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Удаляет атрибут записи IP-адреса.
     *
     * @param string $name Имя атрибута.
     * 
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Возращает значение значение атрибута записи IP-адреса.
     *
     * @param string $name Имя атрибута.
     * 
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }
}
