<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql;

use Ge\Db\Adapter\Platform\PlatformInterface;

/**
 * Интерфейс инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Driver
 * @since 2.0
 */
interface SqlInterface
{
    /**
     * Получить строку SQL для оператора.
     *
     * @param null|PlatformInterface $adapterPlatform
     *
     * @return string
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null): string;
}
