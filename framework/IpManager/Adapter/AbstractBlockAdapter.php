<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\IpManager\Adapter;

use Ge;

/**
 * Абстрактный класс адаптера, списка временно заблокированных IP-адресов.
 * 
 * Адаптер предназначен для создания и проверки доступности IPv4 и IPv6-адресов за 
 * установленный промежуток времени.
 * 
 * Список  временно заблокированных состоит из атрибутов записей IP-адресов:
 *     - `id`, уникальный идентификатор записи;
 *     - `address`, IPv4 или IPv6-адрес;
 *     - `attempt`, индекс попытки проверки доступности IP-адреса;
 *     - `attempts`, количество попыток проверки доступности IP-адреса;
 *     - `time`, время блокировки IP-адреса;
 *     - `note`, описание.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\IpManager\Adapter
 * @since 2.0
 */
abstract class AbstractBlockAdapter implements AdapterInterface
{
    /**
     * Настройки адаптера.
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Текущий IPv4 или IPv6-адрес.
     * 
     * @see AbstractBlockAdapter::ip()
     * @see AbstractBlockAdapter::getIpAddress
     * 
     * @var string|null
     */
    protected ?string $ipAddress = null;

    /**
     * Информация о записи IP-адреса.
     * 
     * @see AbstractBlockAdapter::get()
     * @see AbstractBlockAdapter::getIpInfo()
     * 
     * @var array|null
     */
    protected ?array $ipInfo = null;

    /**
     * Ошибка полученная при выполнении запроса.
     * 
     * @var string
     */
    protected ?string $error = null;

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
    }

    /**
     * Устанавливает текущий IPv4 или IPv6-адрес.
     * 
     * Чтобы в дальнейшем не указывать для методов: `add`, `update`, `remove`, `get`.
     *
     * @param string $ipAddress Действующий IPv4 или IPv6-адрес. Если значение 'current', 
     *     то IP-адрес текущего пользователя. 
     * 
     * @return $this
     */
    public function ip(string $ipAddress): static
    {
        if ($ipAddress === 'current') {
            $ipAddress = Ge::$app->request->getUserIp();
        }
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * Возвращает установленный IPv4 или IPv6-адрес.
     * 
     * @return string Возвращает значение `null`, если IP-адрес не установлен.
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * Возвращает информацию о записи IP-адреса.
     * 
     * Прежде, должен быть выполнен метод {@see AbstractBlockAdapter::get()}.
     * 
     * @return array Возвращает значение `null`, если запись IP-адреса не найдена.
     */
    public function getIpInfo(): ?array
    {
        return $this->ipInfo;
    }

    /**
     * Выполняет обновление и добавление информации о записи IP-адреса.
     * 
     * @return bool Возвращает значение `false`, если информация о записи IP-адреса не 
     *     обновлена или не добавлена.
     */
    public function save(): bool
    {
        return true;
    }

    /**
     * Добавляет информацию о записи IP-адреса.
     * 
     * @param array $ipInfo Информация о записи IP-адреса в виде пар "имя-значение".
     * @param null|string $ipAddress Действующий IPv4 или IPv6-адрес. Если значение:
     *     - 'current', IP-адрес текущего пользователя;
     *     - `null`, IP-адрес установлен с помощью {@see AbstractBlockAdapter::ip()}.

     * @return bool Возвращает значение `false`, если информация о записи IP-адреса 
     *     не добавлена, ошибка {@see AbstractBlockAdapter::$error}.
     */
    public function add(array $ipInfo, ?string $ipAddress = null): bool
    {
        return false;
    }

    /**
     * Обновляет информацию о записи IP-адреса.
     * 
     * @param array $ipInfo Обновляемая информация о записи IP-адреса в виде пар "имя-значение".
     * @param null|string $ipAddress Действующий IPv4 или IPv6-адрес, запись которого необходимо обновить. 
     *     Если значение:
     *     - 'current', IP-адрес текущего пользователя;
     *     - `null`, IP-адрес установлен с помощью {@see AbstractBlockAdapter::ip()}.

     * @return bool Возвращает значение `false`, информация о записи IP-адреса не 
     *     обновлена, ошибка {@see AbstractBlockAdapter::$error}.
     */
    public function update(array $ipInfo, ?string $ipAddress = null): bool
    {
        return false;
    }

    /**
     * Удалаяет информацию о записи IP-адреса.
     * 
     * @param null|string $ipAddress Действующий IPv4 или IPv6-адрес, запись которого необходимо удалить. 
     *     Если значение:
     *     - 'current', IP-адрес текущего пользователя;
     *     - `null`, IP-адрес установлен с помощью {@see AbstractBlockAdapter::ip()}.

     * @return bool Возвращает значение `false`, информация о записи IP-адреса не удалена, 
     *     ошибка {@see AbstractBlockAdapter::$error}.
     */
    public function remove(?string $ipAddress = null): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->ipInfo = null;
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
     * @param null|string $ipAddress Действующий IPv4 или IPv6-адрес, запись которого необходимо получить. 
     *     Если значение:
     *     - 'current', IP-адрес текущего пользователя;
     *     - `null`, IP-адрес установлен с помощью {@see AbstractBlockAdapter::ip()}.

     * @return mixed Возвращает значение `false`, если информация 
     *     о записи IP-адреса отсутствует.
     */
    public function get(?string $ipAddress = null): mixed
    {
        return false;
    }

    /**
     * Проверяет, была ли ошибка в последнем запросе.
     * 
     * @see AbstractBlockAdapter::$error
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
     * @see AbstractBlockAdapter::$error
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
     * @see AbstractBlockAdapter::$error
     * 
     * @return null|string Возвращает значение `null`, если ошибки отсутствует.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Устанавливает время блокировки IP-адреса.
     * 
     * @param int $time Время блокировки IP-адреса в мс.
     * 
     * @return int
     */
    public function timeout(int $time): int
    {
        return $this->timeout = \gmdate('U') + $time;
    }

    /**
     * Уменьшить количество попыток для IP-адреса.
     * 
     * @return int
     */
    public function decreaseAttempt(): int
    {
        return $this->attempt = (int) $this->attempt - 1;
    }

    /**
     * Проверяет, вышло ли время для блокировки IP-адреса.
     * 
     * @return bool
     */
    public function isTimeout(): bool
    {
        return (int) $this->timeout < \gmdate('U');
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
        $this->ipInfo[$name] = $value;
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
        unset($this->ipInfo[$name]);
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
        if (isset($this->ipInfo[$name])) {
            return $this->ipInfo[$name];
        }
        return null;
    }
}
