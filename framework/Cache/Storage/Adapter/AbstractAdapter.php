<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage\Adapter;

use Ge\Cache\Exception;
use Ge\Cache\Storage\StorageInterface;

/**
 * Абстрактный класс хранилища адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage\Adapter
 * @since 2.0
 */
abstract class AbstractAdapter implements StorageInterface
{
    /**
     * Настройки
     *
     * @var mixed
     */
    protected $options;

    /**
     * Конструктор
     *
     * @param null|array|Traversable|AdapterOptions $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Установка настроеек
     *
     * @param  array|AdapterOptions $options
     * @return AbstractAdapter
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if ($this->options !== $options) {
            if (!$options instanceof AdapterOptions) {
                $options = new AdapterOptions($options);
            }

            if ($this->options) {
                $this->options->setAdapter(null);
            }
            $options->setAdapter($this);
            $this->options = $options;
        }
        return $this;
    }

    /**
     * Возвращение настроек
     *
     * @return AdapterOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new AdapterOptions());
        }
        return $this->options;
    }

    /**
     * Включает/Выключает кэширование
     *
     * Псевдоним setWritable и setReadable
     *
     * @see setWritable()
     * @see setReadable()
     * @param bool $flag
     * @return AbstractAdapter
     */
    public function setCaching($flag)
    {
        $flag    = (bool) $flag;
        $options = $this->getOptions();
        $options->setWritable($flag);
        $options->setReadable($flag);
        return $this;
    }

    /**
     * Проверка на возможность кэширования
     *
     * Псевдоним getWritable и getReadable
     *
     * @see getWritable()
     * @see getReadable()
     * @return bool
     */
    public function getCaching()
    {
        $options = $this->getOptions();
        return ($options->getWritable() && $options->getReadable());
    }

    /**
     * Возвращение элемента списка
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     */
    public function getItem($key, & $success = null, & $casToken = null)
    {
        if (!$this->getOptions()->getReadable()) {
            $success = false;
            return;
        }
        $this->normalizeKey($key);

        return $this->internalGetItem($key, $success, $casToken);
    }

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null);

    /**
     * Get multiple items.
     *
     * @param  array $keys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItems.pre(PreEvent)
     * @triggers getItems.post(PostEvent)
     * @triggers getItems.exception(ExceptionEvent)
     */
    public function getItems(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return array();
        }
        $this->normalizeKeys($keys);

        return $this->internalGetItems($keys);
    }

    /**
     * Internal method to get multiple items.
     *
     * @param array $normalizedKeys
     * 
     * @return array Associative array of keys and values
     * 
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $success = null;
        $result  = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $value = $this->internalGetItem($normalizedKey, $success);
            if ($success) {
                $result[$normalizedKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItem.pre(PreEvent)
     * @triggers hasItem.post(PostEvent)
     * @triggers hasItem.exception(ExceptionEvent)
     */
    public function hasItem($key)
    {
        if (!$this->getOptions()->getReadable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalHasItem($key);
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $success = null;
        $this->internalGetItem($normalizedKey, $success);
        return $success;
    }

    /**
     * Test multiple items.
     *
     * @param  array $keys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItems.pre(PreEvent)
     * @triggers hasItems.post(PostEvent)
     * @triggers hasItems.exception(ExceptionEvent)
     */
    public function hasItems(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return array();
        }
        $this->normalizeKeys($keys);

        return $this->internalHasItems($keys);
    }

    /**
     * Internal method to test multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItems(array & $normalizedKeys)
    {
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if ($this->internalHasItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Get metadata of an item.
     *
     * @param  string $key
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getMetadata.pre(PreEvent)
     * @triggers getMetadata.post(PostEvent)
     * @triggers getMetadata.exception(ExceptionEvent)
     */
    public function getMetadata($key)
    {
        if (!$this->getOptions()->getReadable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalGetMetadata($key);
    }

    /**
     * Internal method to get metadata of an item.
     *
     * @param  string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        return array();
    }

    /**
     * Get multiple metadata
     *
     * @param  array $keys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     *
     * @triggers getMetadatas.pre(PreEvent)
     * @triggers getMetadatas.post(PostEvent)
     * @triggers getMetadatas.exception(ExceptionEvent)
     */
    public function getMetadatas(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return array();
        }
        $this->normalizeKeys($keys);

        return $this->internalGetMetadatas($keys);

    }

    /**
     * Internal method to get multiple metadata
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadatas(array & $normalizedKeys)
    {
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $metadata = $this->internalGetMetadata($normalizedKey);
            if ($metadata !== false) {
                $result[$normalizedKey] = $metadata;
            }
        }
        return $result;
    }

    /* writing */

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItem.pre(PreEvent)
     * @triggers setItem.post(PostEvent)
     * @triggers setItem.exception(ExceptionEvent)
     */
    public function setItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalSetItem($key, $value);

    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalSetItem(& $normalizedKey, & $value);

    /**
     * Store multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItems.pre(PreEvent)
     * @triggers setItems.post(PostEvent)
     * @triggers setItems.exception(ExceptionEvent)
     */
    public function setItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }
        $this->normalizeKeyValuePairs($keyValuePairs);

        return $this->internalSetItems($keyValuePairs);
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $failedKeys = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalSetItem($normalizedKey, $value)) {
                $failedKeys[] = $normalizedKey;
            }
        }

        return $failedKeys;
    }

    /**
     * Add an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItem.pre(PreEvent)
     * @triggers addItem.post(PostEvent)
     * @triggers addItem.exception(ExceptionEvent)
     */
    public function addItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalAddItem($key, $value);
    }

    /**
     * Internal method to add an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItem(& $normalizedKey, & $value)
    {
        if ($this->internalHasItem($normalizedKey)) {
            return false;
        }
        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Add multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItems.pre(PreEvent)
     * @triggers addItems.post(PostEvent)
     * @triggers addItems.exception(ExceptionEvent)
     */
    public function addItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }
        $this->normalizeKeyValuePairs($keyValuePairs);

        return $this->internalAddItems($keyValuePairs);
    }

    /**
     * Internal method to add multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItems(array & $normalizedKeyValuePairs)
    {
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalAddItem($normalizedKey, $value)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Replace an existing item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItem.pre(PreEvent)
     * @triggers replaceItem.post(PostEvent)
     * @triggers replaceItem.exception(ExceptionEvent)
     */
    public function replaceItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalReplaceItem($key, $value);

    }

    /**
     * Internal method to replace an existing item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItem(& $normalizedKey, & $value)
    {
        if (!$this->internalhasItem($normalizedKey)) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Replace multiple existing items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItems.pre(PreEvent)
     * @triggers replaceItems.post(PostEvent)
     * @triggers replaceItems.exception(ExceptionEvent)
     */
    public function replaceItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }
        $this->normalizeKeyValuePairs($keyValuePairs);

        return $this->internalReplaceItems($keyValuePairs);
    }

    /**
     * Internal method to replace multiple existing items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItems(array & $normalizedKeyValuePairs)
    {
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalReplaceItem($normalizedKey, $value)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Set an item only if token matches
     *
     * It uses the token received from getItem() to check if the item has
     * changed before overwriting it.
     *
     * @param  mixed  $token
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    public function checkAndSetItem($token, $key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalCheckAndSetItem($token, $key, $value);

    }

    /**
     * Internal method to set an item only if token matches
     *
     * @param  mixed  $token
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    protected function internalCheckAndSetItem(& $token, & $normalizedKey, & $value)
    {
        $oldValue = $this->internalGetItem($normalizedKey);
        if ($oldValue !== $token) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of an item
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItem.pre(PreEvent)
     * @triggers touchItem.post(PostEvent)
     * @triggers touchItem.exception(ExceptionEvent)
     */
    public function touchItem($key)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalTouchItem($args['key']);

    }

    /**
     * Internal method to reset lifetime of an item
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItem(& $normalizedKey)
    {
        $success = null;
        $value   = $this->internalGetItem($normalizedKey, $success);
        if (!$success) {
            return false;
        }

        return $this->internalReplaceItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of multiple items.
     *
     * @param  array $keys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItems.pre(PreEvent)
     * @triggers touchItems.post(PostEvent)
     * @triggers touchItems.exception(ExceptionEvent)
     */
    public function touchItems(array $keys)
    {
        if (!$this->getOptions()->getWritable()) {
            return $keys;
        }
        $this->normalizeKeys($keys);

        return $this->internalTouchItems($keys);
    }

    /**
     * Internal method to reset lifetime of multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItems(array & $normalizedKeys)
    {
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (!$this->internalTouchItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItem.pre(PreEvent)
     * @triggers removeItem.post(PostEvent)
     * @triggers removeItem.exception(ExceptionEvent)
     */
    public function removeItem($key)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalRemoveItem($args['key']);

    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalRemoveItem(& $normalizedKey);

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItems.pre(PreEvent)
     * @triggers removeItems.post(PostEvent)
     * @triggers removeItems.exception(ExceptionEvent)
     */
    public function removeItems(array $keys)
    {
        if (!$this->getOptions()->getWritable()) {
            return $keys;
        }
        $this->normalizeKeys($keys);

        return $this->internalRemoveItems($args['keys']);

    }

    /**
     * Internal method to remove multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItems(array & $normalizedKeys)
    {
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (!$this->internalRemoveItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Increment an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItem.pre(PreEvent)
     * @triggers incrementItem.post(PostEvent)
     * @triggers incrementItem.exception(ExceptionEvent)
     */
    public function incrementItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalIncrementItem($key, $value);
    }

    /**
     * Internal method to increment an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $success  = null;
        $value    = (int) $value;
        $get      = (int) $this->internalGetItem($normalizedKey, $success);
        $newValue = $get + $value;

        if ($success) {
            $this->internalReplaceItem($normalizedKey, $newValue);
        } else {
            $this->internalAddItem($normalizedKey, $newValue);
        }

        return $newValue;
    }

    /**
     * Increment multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItems.pre(PreEvent)
     * @triggers incrementItems.post(PostEvent)
     * @triggers incrementItems.exception(ExceptionEvent)
     */
    public function incrementItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array();
        }
        $this->normalizeKeyValuePairs($keyValuePairs);

        return $this->internalIncrementItems($keyValuePairs);
    }

    /**
     * Internal method to increment multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItems(array & $normalizedKeyValuePairs)
    {
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $newValue = $this->internalIncrementItem($normalizedKey, $value);
            if ($newValue !== false) {
                $result[$normalizedKey] = $newValue;
            }
        }
        return $result;
    }

    /**
     * Decrement an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers decrementItem.pre(PreEvent)
     * @triggers decrementItem.post(PostEvent)
     * @triggers decrementItem.exception(ExceptionEvent)
     */
    public function decrementItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }
        $this->normalizeKey($key);

        return $this->internalDecrementItem($key, $value);
    }

    /**
     * Internal method to decrement an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $success  = null;
        $value    = (int) $value;
        $get      = (int) $this->internalGetItem($normalizedKey, $success);
        $newValue = $get - $value;

        if ($success) {
            $this->internalReplaceItem($normalizedKey, $newValue);
        } else {
            $this->internalAddItem($normalizedKey, $newValue);
        }

        return $newValue;
    }

    /**
     * Decrement multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItems.pre(PreEvent)
     * @triggers incrementItems.post(PostEvent)
     * @triggers incrementItems.exception(ExceptionEvent)
     */
    public function decrementItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array();
        }
        $this->normalizeKeyValuePairs($keyValuePairs);

        return $this->internalDecrementItems($keyValuePairs);
    }

    /**
     * Internal method to decrement multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItems(array & $normalizedKeyValuePairs)
    {
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $newValue = $this->decrementItem($normalizedKey, $value);
            if ($newValue !== false) {
                $result[$normalizedKey] = $newValue;
            }
        }
        return $result;
    }

    /* status */

    /**
     * Get capabilities of this adapter
     *
     * @return Capabilities
     * @triggers getCapabilities.pre(PreEvent)
     * @triggers getCapabilities.post(PostEvent)
     * @triggers getCapabilities.exception(ExceptionEvent)
     */
    public function getCapabilities()
    {
        return $this->internalGetCapabilities();
    }

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $this->capabilityMarker = new stdClass();
            $this->capabilities     = new Capabilities($this, $this->capabilityMarker);
        }
        return $this->capabilities;
    }

    /* internal */

    /**
     * Валидация и нормализация ключа
     *
     * @param  string $key
     * @return void
     * @throws Exception\InvalidArgumentException на валидацию ключа
     */
    protected function normalizeKey(& $key)
    {
        $key = (string) $key;
        $key .= '';

        if ($key === '') {
            throw new Exception\InvalidArgumentException(
                "An empty key isn't allowed"
            );
        } elseif (($p = $this->getOptions()->getKeyPattern()) && !preg_match($p, $key)) {
            throw new Exception\InvalidArgumentException(
                "The key '{$key}' doesn't match against pattern '{$p}'"
            );
        }
    }

    /**
     * Validates and normalizes multiple keys
     *
     * @param  array $keys
     * @return void
     * @throws Exception\InvalidArgumentException On an invalid key
     */
    protected function normalizeKeys(array & $keys)
    {
        if (!$keys) {
            throw new Exception\InvalidArgumentException(
                "An empty list of keys isn't allowed"
            );
        }

        array_walk($keys, array($this, 'normalizeKey'));
        $keys = array_values(array_unique($keys));
    }

    /**
     * Validates and normalizes an array of key-value pairs
     *
     * @param  array $keyValuePairs
     * @return void
     * @throws Exception\InvalidArgumentException On an invalid key
     */
    protected function normalizeKeyValuePairs(array & $keyValuePairs)
    {
        $normalizedKeyValuePairs = array();
        foreach ($keyValuePairs as $key => $value) {
            $this->normalizeKey($key);
            $normalizedKeyValuePairs[$key] = $value;
        }
        $keyValuePairs = $normalizedKeyValuePairs;
    }
}
