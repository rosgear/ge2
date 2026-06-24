<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\MultiSite;

use Ge\Stdlib\Collection;

/**
 * Класс коллекции (карты) доменов.
 * 
 * В коллекции домены представлены в виде пар "ключ - значение". Где ключа - домен,
 * а значение - уникальный идентификатор сайта.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\MultiSite
 * @since 2.0
 */
class Domains extends Collection
{
    /**
     * Удаляет домены по указанным идентификаторам сайта.
     *
     * @param string|array $id Уникальный идентификатор(ы) сайта.
     * 
     * @return static
     */
    public function removeBySiteId(string $id): static
    {
        foreach ($this->container as $domain => $siteId) {
            if ($id === $siteId) {
                unset($this->container[$domain]);
            }
        }
        return $this;
    }
}
