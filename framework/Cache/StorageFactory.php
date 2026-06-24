<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache;

use Ge;
use Ge\Cache\Exception;

/**
 * Фабрика хранения предназначена для хранения ресурсов адаптеров (шаблон хранения данных), 
 * таких как память и файловая система.
 * 
 * Все манипуляции с ресурсами адаптеров выполняются через менеджер адаптеров.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache
 * @since 2.0
 */
abstract class StorageFactory
{
    /**
     * Менеджер плагинов для загрузки адаптеров
     *
     * @var null|Storage\AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Создаёт адаптер хранения.
     *
     * @param array $cfg
     * 
     * @return Storage\StorageInterface
     * 
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($cfg)
    {
        if (!is_array($cfg)) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'The factory needs an associative array as an argument')
            );
        }

        // создание экземпляра адаптера
        if (!isset($cfg['adapter'])) {
            throw new Exception\InvalidArgumentException(Ge::t('app', 'Missing "adapter"'));
        }
        $adapterName    = $cfg['adapter'];
        $adapterOptions = [];
        if (is_array($cfg['adapter'])) {
            if (!isset($cfg['adapter']['name'])) {
                throw new Exception\InvalidArgumentException(Ge::t('app', 'Missing "adapter.name"'));
            }

            $adapterName    = $cfg['adapter']['name'];
            $adapterOptions = isset($cfg['adapter']['options']) ? $cfg['adapter']['options'] : array();
        }
        if (isset($cfg['options'])) {
            $adapterOptions = array_merge($adapterOptions, $cfg['options']);
        }
        $adapter = static::adapterFactory((string) $adapterName, $adapterOptions);

        return $adapter;
    }

    public static function factoryBySession($config, $sessionNamespace, $session)
    {
        $adapter = static::factory($config);
        $adapter->getOptions()->setSessionContainer(
            new Ge\Session\Container($sessionNamespace, $session)
        );
        return $adapter;
    }

    /**
     * Возвращает указатель на созданный менеджер плагинов адаптера.
     *
     * @param string|Storage\StorageInterface $adapterName Название адаптера.
     * @param array|Storage\Adapter\AdapterOptions $options Параметры адаптера.
     * 
     * @return Storage\StorageInterface
     */
    public static function adapterFactory($adapterName, $options = [])
    {
        if ($adapterName instanceof Storage\StorageInterface)
            $adapter = $adapterName;
        else
            $adapter = static::getAdapterPluginManager()->get($adapterName);

        if ($options)
            $adapter->setOptions($options);
        return $adapter;
    }

    /**
     * Возвращает указатель на менеджер плагинов адаптера.
     *
     * @return Storage\AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new Storage\AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Устаналвивает менеджер плагинов адаптера.
     *
     * @param Storage\AdapterPluginManager $adapters
     * 
     * @return void
     */
    public static function setAdapterPluginManager(Storage\AdapterPluginManager $adapters): void
    {
        static::$adapters = $adapters;
    }

    /**
     * Сбрасывает менеджер плагинов адаптера.
     *
     * @return void
     */
    public static function resetAdapterPluginManager(): void
    {
        static::$adapters = null;
    }
}
