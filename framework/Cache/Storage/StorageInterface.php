<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Cache\Storage;

/**
 * Интерфейс хранилища данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Cache\Storage
 * @since 2.0
 */
interface StorageInterface
{
    /**
     * Установка параметров.
     *
     * @param array|Adapter\AdapterOptions $options
     * 
     * @return StorageInterface
     */
    public function setOptions($options);

    /**
     * Возвращение параметров
     *
     * @return Adapter\AdapterOptions
     */
    public function getOptions();

    /**
     * Возвращение элемента
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed данные при успехе, null при отказе
     */
    public function getItem($key, & $success = null, & $casToken = null);

    /**
     * Возвращение нескольких элементов
     *
     * @param  array $keys
     * @return array ассоциативный массив ключей и значений
     */
    public function getItems(array $keys);

    /**
     * Проверка существования элемента
     *
     * @param  string $key
     * @return bool
     */
    public function hasItem($key);

    /**
     * Проверка существования элементов
     *
     * @param  array $keys
     * @return array массив найденных ключей
     */
    public function hasItems(array $keys);

    /**
     * Возвращение метаданных элементов по ключу
     *
     * @param  string $key
     * @return array|bool метаданные на успехе, ложные при отказе
     */
    public function getMetadata($key);

    /**
     * Возвращение всех метаданных элементов
     *
     * @param  array $keys
     * @return array ассоциативный массив ключей и метаданных
     */
    public function getMetadatas(array $keys);

    /**
     * Установка элемента
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function setItem($key, $value);

    /**
     * Установка нескольких элементов
     *
     * @param  array $keyValuePairs
     * @return array массив не сохраненных ключей
     */
    public function setItems(array $keyValuePairs);

    /**
     * Добавление элемента
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function addItem($key, $value);

    /**
     * Добавление нескольких элементов
     *
     * @param  array $keyValuePairs
     * @return array массив не сохраненных ключей
     */
    public function addItems(array $keyValuePairs);

    /**
     * Замена существующего элемента
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function replaceItem($key, $value);

    /**
     * Замена нескольких существующих элементов
     *
     * @param  array $keyValuePairs
     * @return array массив не сохраненных ключей
     */
    public function replaceItems(array $keyValuePairs);

    /**
     * Установка элемента только в том случае, если токены совпадают
     *
     * Он использует маркер, полученный от getItem(), чтобы проверить, имеет ли элемент
     * изменения перед перезаписью.
     *
     * @param mixed  $token
     * @param string $key
     * @param mixed  $value
     * @return bool
     * @see getItem()
     * @see setItem()
     */
    public function checkAndSetItem($token, $key, $value);

    /**
     * Сброс срока службы элемента
     *
     * @param  string $key
     * @return bool
     */
    public function touchItem($key);

    /**
     * Сброс срока службы нескольких элементов
     *
     * @param  array $keys
     * @return array массив не обновленных ключей
     */
    public function touchItems(array $keys);

    /**
     * Удаление элемента
     *
     * @param  string $key
     * @return bool
     */
    public function removeItem($key);

    /**
     * Удаление нескольких элементов
     *
     * @param  array $keys
     * @return array массив не удаленных ключей
     */
    public function removeItems(array $keys);

    /**
     * Увеличение элемента
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool новое значение при успехе, false при отказе
     */
    public function incrementItem($key, $value);

    /**
     * Увеличение нескольких элементов
     *
     * @param  array $keyValuePairs
     * @return array ассоциативный массив ключей и новых значений
     */
    public function incrementItems(array $keyValuePairs);

    /**
     * Уменьшение элемента
     *
     * @param string $key
     * @param int $value
     * @return int|bool новое значение при успехе, false при отказе
     */
    public function decrementItem($key, $value);

    /**
     * Уменьшение нескольких элементов
     *
     * @param array $keyValuePairs
     * @return array ассоциативный массив ключей и новых значений
     */
    public function decrementItems(array $keyValuePairs);

    /**
     * Возвращение возможностей хранилища
     *
     * @return Capabilities
     */
    public function getCapabilities();
}
