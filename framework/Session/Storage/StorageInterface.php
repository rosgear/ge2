<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session\Storage;

/**
 * Интерфейс хранилища (место хранения) контейнера сессии.
 * 
 * Сигнатуры таких методов, как: `remove()`, `get()`, `set()` должны быть совместимы 
 * с методами {@see \Ge\Stdlib\Collection}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session\Storage
 * @since 2.0
 */
interface StorageInterface
{
    /**
     * Возвращает содержимое хранилища.
     * 
     * Поведение не определено, когда хранилище пусто.
     *
     * @return mixed
     */
    public function read(): mixed;

    /**
     * Удаляет ключ из хранилища.
     * 
     * @param string $key Ключ.
     * 
     * @return $this
     */
    public function remove(mixed $key): static;

    /**
     * Записывает содержимое в хранилище.
     * 
     * @param mixed $content Содержимое хранилища.
     * 
     * @return $this
     */
    public function write(mixed $content): static;

    /**
     * Проверяет, было ли создано хранилище или было изменение содержимого хранилища.
     * 
     * @return bool
     */
    public static function isInit(): bool;

    /**
     * Возвращает значение ключа из хранилища.
     * 
     * @param mixed $key Ключ хранилища.
     * 
     * @return mixed Возвращает значение `null`, если ключ не существует.
     */
    public function get(mixed $key): mixed;

    /**
     * Устанавливает значение ключа.
     * 
     * @param mixed $key Ключ хранилища.
     * @param mixed $value Значение ключа. Если значение `null`, ключ удаляется из 
     *     хранилища.
     * 
     * @return $this
     */
    public function set(mixed $key, mixed $value): static;
}
