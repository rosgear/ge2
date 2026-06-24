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
use Ge\Helper\IpHelper;
use Ge\Db\Adapter\Exception\CommandException;

/**
 * Абстрактный адаптер.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\BlockIpAddress\Adapter
 * @since 2.0
 */
class DbListAdapter extends AbstractListAdapter
{
    /**
     * {@inheritdoc}
     */
    public function inFrontendRange(string $ipAddress, bool $check = true): mixed
    {
        $ipAddress = IpHelper::ip2long($ipAddress);
        if ($ipAddress === false) {
            return $check ? false : null;
        }

        /** @var \Ge\Db\Sql\Select $select */
        $select = Ge::$app->db
            ->select($this->tableName)
            ->columns([$check ? 'id' : '*'])
            ->where(["`range_begin` <= $ipAddress AND $ipAddress <= `range_end` AND `frontend` = 1"]);
        $result = Ge::$app->db
            ->createCommand($select)
                ->queryOne();
        return $check ? $result !== null : $result;
    }

    /**
     * {@inheritdoc}
     */
    public function inBackendRange(string $ipAddress, bool $check = true): mixed
    {
        $ipAddress = IpHelper::ip2long($ipAddress);
        if ($ipAddress === false) {
            return $check ? false : null;
        }

        /** @var \Ge\Db\Sql\Select $select */
        $select = Ge::$app->db
            ->select($this->tableName)
            ->columns([$check ? 'id' : '*'])
            ->where(["`range_begin` <= $ipAddress AND $ipAddress <= `range_end` AND `backend` = 1"]);
        $command = Ge::$app->db->createCommand($select);
        $result = $command->queryOne();
        return $check ? $result !== null : $result;
    }

    /**
     * Добавляет IP-адрес в список.
     * 
     * @param string $ipAddress IP-адрес. Если значение 'current', то IP-адрес текущего пользователя.
     * @param bool $forBackend Применить для backend.
     * @param bool $forFrontend Применить для frontend.
     * @param string $note Комментарий.
     * 
     * @return bool Возвращает значение `false`, ошибка добавления.
     */
    public function addAddress(
        string $ipAddress = 'current', 
        bool $forBackend = true, 
        bool $forFrontend = true, 
        string $note = ''
    ): bool {
        if ($ipAddress === 'current') {
            $ipAddress = Ge::$app->request->getUserIp();
        }
        return $this->add([
            'address'  => $ipAddress,
            'backend'  => $forBackend ? 1 : 0, 
            'frontend' => $forFrontend ? 1 : 0,
            'note'     => $note
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $attributes): bool
    {
        if (empty($attributes['address'])) {
            $this->error = 'IP address is empty.';
            return false;
        }

        // определяем номер версии IP-адреса
        $version = IpHelper::getIpVersion($attributes['address']);
        // определяем диапазон IP-адресов
        $range = IpHelper::ip2range($attributes['address'], true);
        if ($range === false) {
            $this->error = 'Incorrect IP address.';
            return false;
        }

        if (is_array($range)) {
            $attributes['range_begin'] = $range[0];
            $attributes['range_end']   = $range[1];
            if ($version === IpHelper::IPV4) {
                $from = long2ip($range[0]);
                $to   = long2ip($range[1]);
                if (!($from === false || $to === false)) {
                    $attributes['range_address']  = $from . ' - ' . $to;
                }
            }
        } else {
            $attributes['range_begin']   = $range;
            $attributes['range_end']     = $range;
            $attributes['range_address'] = $attributes['address'];
        }

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->insert($this->tableName, $attributes);
            $result = $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }
        return $this->error ? false : true;
    }

    /**
     * Обновляет запись IP-адреса в списке.
     * 
     * @param mixed array $attributes Атрибуты записи IP-адреса.
     * @param string $ipAddress Запись IP-адреса, которую необходимо обновить. Если 
     *     значение 'current', то IP-адрес текущего пользователя.
     * 
     * @return bool Возвращает значение `false`, если ошибка обновления.
     */
    public function updateAddress(array $attributes, string $ipAddress = 'current'): bool
    {
        if ($ipAddress === 'current') {
            $ipAddress = Ge::$app->request->getUserIp();
        }

        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            return false;
        }
        return $this->update($attributes, $id) === false ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributes, int $id): bool
    {
        $this->resetError();
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->update($this->tableName, $attributes, ['id' => $id]);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }

        if ($this->error) {
            return false;
        }
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * Выполняет обновление и добавление информации о записи IP-адреса.
     * 
     * @return bool Возвращает значение `false`, если информация о записи IP-адреса не 
     *     обновлена или не добавлена.
     */
    public function save(): bool
    {
        if ($this->id)
            return $this->update($this->attributes, $this->id);
        else
            return $this->add($this->attributes);
    }

    /**
     * Удаляет IP-адрес из списка.
     * 
     * @param string $ipAddress IP-адрес. Если значение 'current', то IP-адрес текущего пользователя.
     * 
     * @return bool Возвращает значение `false`, елси ошибка удаления. 
     */
    public function removeAddress(string $ipAddress = 'current'): bool
    {
        if ($ipAddress === 'current') {
            $ipAddress = Ge::$app->request->getUserIp();
        }

        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            return false;
        }
        return $this->remove($id) === false ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(?int $id = null): bool
    {
        if ($id === null) {
            if ($this->id === null)
                return false;
            else
                $id = $this->id;
        }

        $this->resetError();
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->delete($this->tableName, ['id' => $id]);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }

        if ($this->error) {
            return false;
        }
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function get(?int $id = null): mixed
    {
        if ($id === null) {
            return $this->attributes;
        }

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $select = Ge::$app->db
            ->select($this->tableName)
            ->columns(['*'])
            ->where(['id' => $id]);
        $result = Ge::$app->db->createCommand($select)->queryOne();
        $this->attributes = $result ?: [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->resetError();
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->delete($this->tableName);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }

        if ($this->error) {
            return false;
        }
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }
}
