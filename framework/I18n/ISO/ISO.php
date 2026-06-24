<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\ISO;

use Ge\ServiceManager\AbstractManager;

/**
 * Менеджер обозначений ISO.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO
 * @since 2.0
 */
class ISO extends AbstractManager
{
    /**
     * {@inheritdoc}
     */
    protected array $invokableClasses = [
        'languages' => '\Ge\I18n\ISO\Adapter\Languages',
        'countries' => '\Ge\I18n\ISO\Adapter\Countries',
        'locales'   => '\Ge\I18n\ISO\Adapter\Locales',
        'scripts'   => '\Ge\I18n\ISO\Adapter\Scripts'
    ];

    /**
     * Возвращает значения по указанному ключу (магический метод).
     * 
     * @param string $key Ключ.
     * 
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }
}
