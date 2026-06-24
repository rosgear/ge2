<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge\Config\Config;

/**
 * Конфигуратор установщика.
 * 
 * Сохраняет параметры полученные на каждом шаге установки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerConfig extends Config
{
    /**
     * Удаляет / сбрасывает параметры, полученные на каждом шаге установки.
     * 
     * @return bool
     */
    public function reset(): bool
    {
        /** @var \Ge\Stdlib\Serializer $serializer */
        $serializer = $this->getSerializer();
        return $serializer->delete();
    }
}
