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
 * Класс адаптера, списка временно заблокированных IP-адресов в базе данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\IpManager\Adapter
 * @since 2.0
 */
class DbBlockAdapter extends AbstractBlockAdapter
{
    /**
     * {@inheritdoc}
     */
    public function add(array $ipInfo, ?string $ipAddress = null): bool
    {
        $this->resetError();
        $ipAddress = $ipAddress ?: $this->ipAddress;
        if (empty($ipAddress)) {
            $this->error = 'IP address not found.';
            return false;
        }

        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            $this->error = 'Unable to convert IP address to number.';
            return false;
        }
        $ipInfo['address'] = $ipAddress;
        $ipInfo['id']      = $id;

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $result = $command->insert($this->options['tableName'], $ipInfo)->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }
        return $this->error ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $ipInfo, ?string $ipAddress = null): bool
    {
        $this->resetError();
        $ipAddress = $ipAddress ?: $this->ipAddress;
        if (empty($ipAddress)) {
            $this->error = 'IP address not found.';
            return false;
        }

        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            $this->error = 'Unable to convert IP address to number.';
            return false;
        }

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->update($this->options['tableName'], $ipInfo, ['id' => $id]);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }

        if ($this->error) {
            return false;
        }
        return $command->getResult() === true ? ($command->getAffectedRows() > 0) : false;
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
            return $this->update($this->ipInfo, $this->ip);
        else
            return $this->add($this->ipInfo, $this->ip);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(?string $ipAddress = null): bool
    {
        $this->resetError();
        $ipAddress = $ipAddress ?: $this->ipAddress;
        if (empty($ipAddress)) {
            $this->error = 'IP address not found.';
            return false;
        }
        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            $this->error = 'Unable to convert IP address to number.';
            return false;
        }
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        try {
            $command->delete($this->options['tableName'], ['id' => $id]);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }
        if ($this->error)
            return false;
        else
            return $command->getResult() === true ? ($command->getAffectedRows() > 0) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function get(?string $ipAddress = null): mixed
    {
        $this->resetError();
        $ipAddress = $ipAddress ?: $this->ipAddress;
        if (empty($ipAddress)) {
            $this->error = 'IP address not found.';
            return false;
        }

        $id = IpHelper::ip2long($ipAddress);
        if ($id === false) {
            $this->error = 'Unable to convert IP address to number.';
            return false;
        }

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $select = Ge::$app->db
            ->select($this->options['tableName'])
            ->columns(['*'])
            ->where(['id' => $id]);
        $result = Ge::$app->db->createCommand($select)->queryOne();
        if ($result) {
            $this->ipInfo = $result;
        }
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
            $command->delete($this->options['tableName']);
            $command->execute();
        } catch (CommandException $e) {
            $this->error = $command->getError();
        }

        if ($this->error) {
            return false;
        }
        return $command->getResult() === true ? ($command->getAffectedRows() > 0) : false;
    }
}
