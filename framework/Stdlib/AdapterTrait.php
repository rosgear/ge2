<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

/**
 * Трейт адаптера (менеджер адаптеров), как расширение класса службы (сервиса).
 * 
 * Менеджер адаптеров предназначение для управления расширениями службы {@see \Ge\Stdlib\Service}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
trait AdapterTrait
{
    /**
     * Контейнер объектов (адаптеров) в виде пары "имя адаптера => объект".
     *
     * @var array
     */
    protected array $adapterContainer = [];

    /**
     * Именна классов адаптеров в виде пары "имя адаптера => имя класса".
     *
     * @var array
     */
    public array $adapterClasses = [];

    /**
     * Имя адаптеров по умолчанию.
     *
     * @var string
     */
    public string $defaultAdapter = '';

    /**
     * Имя метода инициализации адаптера.
     *
     * @var string
     */
    public string $adapterInitMethod = 'init';

    /**
     * Возвращает адаптер по указанному имени.
     * 
     * @param null|string $name Имя адаптера. Если `null`, имя адаптера по умолчанию.
     *
     * @return mixed Если значение `null`, адаптер с указанным именем отсутствует.
     */
    public function getAdapter(?string $name = null): mixed
    {
        if ($name === null) {
            if ($this->defaultAdapter === null) {
                return null;
            }
            $name = $this->defaultAdapter;
        }

        if (isset($this->adapterContainer[$name])) {
            return $this->adapterContainer[$name];
        }

        $adapter = $this->createAdapter($name);
        $this->setAdapter($name, $adapter);
        return $adapter;
    }

    /**
     * Удаляет адаптер из контейнера.
     * 
     * @param null|string $name Имя адаптера.
     * 
     * @return bool
     */
    public function resetAdapter(?string $name = null): bool
    {
        if ($name === null) {
            if ($this->defaultAdapter === null) {
                return false;
            }
            $name = $this->defaultAdapter;
        }

        if (isset($this->adapterContainer[$name])) {
            unset($this->adapterContainer[$name]);
            return true;
        }
        return false;
    }

    /**
     * Создаёт адаптер.
     * 
     * @param string $name Имя адаптера. Если нет класса адаптера, 
     *    сопоставленного его имени, то имя - класс адаптера.
     *
     * @return mixed
     */
    public function createAdapter(string $name): mixed
    {
        $className = $this->adapterClasses[$name] ?? $name;
        $adapter = new $className($this);
        if (method_exists($adapter, $this->adapterInitMethod)) {
            $adapter->{$this->adapterInitMethod}($this);
        }
        return $adapter;
    }

    /**
     * Добавляет экземпляр класса адаптера в контейнер.
     * 
     * @param string $name Имя адаптера.
     * @param mixed $adapter Экземпляр класса адаптера.
     * 
     * @return $this
     */
    public function setAdapter(string $name, mixed $adapter): static
    {
        if ($adapter === null) {
            if (isset($this->adapterContainer[$name]))
                unset($this->adapterContainer[$name]);
        } else
            $this->adapterContainer[$name] = $adapter;
        return $this;
    }

    /**
     * Проверяет принадлежность экземпляра класса адаптера контейнеру.
     * 
     * @param string $name Имя адаптера.
     * 
     * @return bool Если `true`, экземпляр класса адаптера принадлежит контейнеру.
     */
    public function hasAdapter(string $name): bool
    {
        return isset($this->adapterContainer[$name]);
    }

    /**
     * Добавляет имена адаптеров с их классами.
     * 
     * @param array $classes Имена адаптеров с их классами в виде пары "имя адаптера => имя класса".
     *    Если имена адаптеров ранее были добавлены, заменит их.
     * 
     * @return $this
     */
    public function addAdapterClasses(array $classes): static
    {
        $this->adapterClasses = array_merge($classes, $this->adapterClasses);
        return $this;
    }

    /**
     * Возвращает имя класса адаптера.
     * 
     * @param string $name Имя адаптера.
     * 
     * @return string Если класс адаптера не найден, возвратит имя адаптера.
     */
    public function getAdapterClassName(string $name): string
    {
        return $this->adapterClasses[$name] ?? $name;
    }

    /**
     * Проверяет существование имени класса адаптера.
     * 
     * @param string $name Имя адаптера.
     * 
     * @return bool Если `false`, класс адаптера не существует.
     */
    public function hasAdapterClassName(string $name): bool
    {
        return isset($this->adapterClasses[$name]);
    }

    /**
     * Устанавливает имя класса адаптера.
     * 
     * @param string $name Имя адаптера.
     * @param string $className Имя класса адаптера. Если имя класса 
     *    адаптера `null`, удаляет его.
     * 
     * @return $this
     */
    public function setAdpaterClass(string $name, string $className): static
    {
        if ($className === null)
            unset($this->adapterClasses[$name]);
        else
            $this->adapterClasses[$name] = $className;
        return $this;
    }

    /**
     * Устанавливает имена классов адаптеров.
     * 
     * @param array $classes Имена адаптеров с их классами в виде пары "имя адаптера => имя класса".
     * 
     * @return $this
     */
    public function setAdapterClasses(array $classes): static
    {
        $this->adapterClasses = $classes;
        return $this;
    }

    /**
     * Возвращает имена классов адаптеров.
     * 
     * @return array
     */
    public function getAdapterClasses(): array
    {
        return $this->adapterClasses;
    }
}
