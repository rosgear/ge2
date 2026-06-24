<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage\Adapter;

use stdClass;
use Ge\Cache\Exception;
use Ge\Cache\Storage\Capabilities;
use Ge\Cache\Storage\ClearByPrefixInterface;
use Ge\Cache\Storage\FlushableInterface;
use Ge\Session\Container as SessionContainer;

/**
 * Класс сессионного адаптера хранилища.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage\Adapter
 * @since 2.0
 */
class Session extends AbstractAdapter implements
    ClearByPrefixInterface,
    FlushableInterface
{
    /**
     * Валидация и нормализация ключа
     *
     * @param  string $key
     * @return void
     */
    protected function normalizeKey(& $key)
    {
        $key = 'id' . $key;

        parent::normalizeKey($key);
    }

    /**
     * Установка настроеек
     *
     * @param  array|SessionOptions $options
     * @return Memory
     * @see getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof SessionOptions) {
            $options = new SessionOptions($options);
        }
        return parent::setOptions($options);
    }

    /**
     * Возвращение настроек
     *
     * @return SessionOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new SessionOptions());
        }
        return $this->options;
    }

    /**
     * Возвращение контейнера сеанса
     *
     * @return SessionContainer
     */
    protected function getSessionContainer()
    {
        $sessionContainer = $this->getOptions()->getSessionContainer();
        if (!$sessionContainer) {
            throw new Exception\RuntimeException("No session container configured");
        }
        return $sessionContainer;
    }

    /**
     * Очистка всего контейнера сеанса
     *
     * @return bool
     */
    public function flush()
    {
        $this->getSessionContainer()->exchangeArray([]);
        return true;
    }

    /**
     * Удаление элементов соответствующие заданному префиксу
     *
     * @param string $prefix
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return true;
        }

        $data    = $cntr->offsetGet($ns);
        $prefixL = strlen($prefix);
        foreach ($data as $key => & $item) {
            if (substr($key, 0, $prefixL) === $prefix) {
                unset($data[$key]);
            }
        }
        $cntr->offsetSet($ns, $data);

        return true;
    }

    /* для чтения */

    /**
     * Внутренний метод для получения элемента
     *
     * @param  string $normalizedKey
     * @param  bool $success
     * @param  mixed $casToken
     * 
     * @return mixed Data on success, null on failure
     * 
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $cntr    = $this->getSessionContainer();
        $ns      = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            $success = false;
            return;
        }

        $data    = $cntr->offsetGet($ns);
        $success = array_key_exists($normalizedKey, $data);
        if (!$success) {
            return;
        }

        $casToken = $value = $data[$normalizedKey];
        return $value;
    }

    /**
     * Внутренний метод получения нескольких элементов
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return array();
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (array_key_exists($normalizedKey, $data)) {
                $result[$normalizedKey] = $data[$normalizedKey];
            }
        }
        return $result;
    }

    /**
     * Внутренний метод проверки наличия элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @return bool
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        return array_key_exists($normalizedKey, $data);
    }

    /**
     * Внутренний метод проверки нескольких элементов
     *
     * @param array $normalizedKeys нормализованные ключи
     * @return array массив найденных ключей
     */
    protected function internalHasItems(array & $normalizedKeys)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return array();
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (array_key_exists($normalizedKey, $data)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Получение метаданных элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @return array|bool Metadata on success, false on failure
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        return $this->internalHasItem($normalizedKey) ? array() : false;
    }

    /* для записи */

    /**
     * Внутренний метод хранения элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @param mixed $value
     * @return bool
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();
        $data = $cntr->offsetExists($ns) ? $cntr->offsetGet($ns) : array();
        $data[$normalizedKey] = $value;
        $cntr->offsetSet($ns, $data);
        return true;
    }

    /**
     * Внутренний метод хранения нескольких элементов
     *
     * @param array $normalizedKeyValuePairs пары нормализованный ключ и значение
     * @return array массив не сохраненных ключей
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = array_merge($cntr->offsetGet($ns), $normalizedKeyValuePairs);
        } else {
            $data = $normalizedKeyValuePairs;
        }
        $cntr->offsetSet($ns, $data);

        return array();
    }

    /**
     * Добавить элемент
     *
     * @param string $normalizedKey нормализованный ключ
     * @param mixed $value значение
     * @return bool
     */
    protected function internalAddItem(& $normalizedKey, & $value)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);

            if (array_key_exists($normalizedKey, $data)) {
                return false;
            }

            $data[$normalizedKey] = $value;
        } else {
            $data = array($normalizedKey => $value);
        }

        $cntr->offsetSet($ns, $data);
        return true;
    }

    /**
     * Внутренний метод добавления нескольких элементов
     *
     * @param  array $normalizedKeyValuePairs
     * @return array массив не сохраненных ключей
     */
    protected function internalAddItems(array & $normalizedKeyValuePairs)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        $result = array();
        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);

            foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
                if (array_key_exists($normalizedKey, $data)) {
                    $result[] = $normalizedKey;
                } else {
                    $data[$normalizedKey] = $value;
                }
            }
        } else {
            $data = $normalizedKeyValuePairs;
        }

        $cntr->offsetSet($ns, $data);
        return $result;
    }

    /**
     * Внутренний метод для замены существующего элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @param mixed $value значение
     * @return bool
     */
    protected function internalReplaceItem(& $normalizedKey, & $value)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        if (!array_key_exists($normalizedKey, $data)) {
            return false;
        }
        $data[$normalizedKey] = $value;
        $cntr->offsetSet($ns, $data);

        return true;
    }

    /**
     * Внутренний метод для замены нескольких существующих элементов
     *
     * @param array $normalizedKeyValuePairs
     * @return array массив не сохраненных ключей
     */
    protected function internalReplaceItems(array & $normalizedKeyValuePairs)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();
        if (!$cntr->offsetExists($ns)) {
            return array_keys($normalizedKeyValuePairs);
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!array_key_exists($normalizedKey, $data)) {
                $result[] = $normalizedKey;
            } else {
                $data[$normalizedKey] = $value;
            }
        }
        $cntr->offsetSet($ns, $data);

        return $result;
    }

    /**
     * Внутренний метод удаления элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @return bool
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        if (!array_key_exists($normalizedKey, $data)) {
            return false;
        }

        unset($data[$normalizedKey]);

        if (!$data) {
            $cntr->offsetUnset($ns);
        } else {
            $cntr->offsetSet($ns, $data);
        }

        return true;
    }

    /**
     * Внутренний метод для увеличения элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @param int $value значение
     * @return int|bool новое значение на успех, false на провал
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);
        } else {
            $data = array();
        }

        if (array_key_exists($normalizedKey, $data)) {
            $data[$normalizedKey]+= $value;
            $newValue = $data[$normalizedKey];
        } else {
            // initial value
            $newValue             = $value;
            $data[$normalizedKey] = $newValue;
        }

        $cntr->offsetSet($ns, $data);
        return $newValue;
    }

    /**
     * Внутренний метод для уменьшения элемента
     *
     * @param string $normalizedKey нормализованный ключ
     * @param int $value значение
     * @return int|bool новое значение на успех, false на провал
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);
        } else {
            $data = array();
        }

        if (array_key_exists($normalizedKey, $data)) {
            $data[$normalizedKey]-= $value;
            $newValue = $data[$normalizedKey];
        } else {
            // initial value
            $newValue             = -$value;
            $data[$normalizedKey] = $newValue;
        }

        $cntr->offsetSet($ns, $data);
        return $newValue;
    }

    /* для статуса */

    /**
     * Внутренний метод получения возможностей адаптера
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $this->capabilityMarker = new stdClass();
            $this->capabilities = new Capabilities(
                $this,
                $this->capabilityMarker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => true,
                        'boolean'  => true,
                        'integer'  => true,
                        'double'   => true,
                        'string'   => true,
                        'array'    => 'array',
                        'object'   => 'object',
                        'resource' => false,
                    ),
                    'supportedMetadata'  => array(),
                    'minTtl'             => 0,
                    'maxKeyLength'       => 0,
                    'namespaceIsPrefix'  => false,
                    'namespaceSeparator' => '',
                )
            );
        }
        return $this->capabilities;
    }

    public function exists()
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        return $cntr->offsetExists($ns);
    }
}
