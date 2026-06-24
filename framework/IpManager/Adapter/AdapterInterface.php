<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\IpManager\Adapter;

/**
 * Интерфейс адаптера проверки IP-адресов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\IpManager\Adapter
 * @since 2.0
 */
interface AdapterInterface
{
    /**
     * Сбрасывает полученную информацию о записяз IP-адресов.
     * 
     * @return void
     */
    public function reset(): void;

    /**
     * Удаляет всю информацию о записях IP-адресов.
     * 
     * @return bool
     */
    public function clear(): bool;
}
