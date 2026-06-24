<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\ISO\Adapter;

/**
 * Абстрактный класс адаптера обозначений согласно ISO.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO\Adapter
 * @since 2.0
 */
abstract class AbstractAdapter
{
    /**
     * Карта обозначений ISO.
     *
     * @var array
     */
    protected array $map;

    /**
     * Тип обозначений ISO.
     * 
     * Менеджер обозначений ISO {@see \Ge\I18n\ISO\ISO::$invokableClasses}.
     *
     * @var string
     */
    protected string $standard = '';

    /**
     * Конструктор класса.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * Возвращает имя файла обозначений ISO.
     * 
     * @return string
     */
    public function getFilename(): string
    {
        return 'standards' . DS . $this->standard . '.php';
    }

    /**
     * Загружает данные файла в карту обозначений.
     * 
     * @return array
     */
    public function load(): array
    {
        if (!isset($this->map)) {
            $this->map = (array) require($this->getFilename());
        }
        return $this->map;
    }

    /**
     * Возвращает все данные карты обозначений.
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->map;
    }

    /**
     * Возвращает все ключи карты обозначений.
     * 
     * @return array
     */
    public function getAllCodes(): array
    {
        return array_keys($this->map);
    }

    /**
     * Возвращает карту обозначений без ключей.
     * 
     * @return array
     */
    public function getAllValues(): array
    {
        return array_values($this->map);
    }

    /**
     * Поиска значения по карте.
     * 
     * @param mixed $value Значение поиска.
     * @param string $key Ключ поиска.
     * @param bool $collect Собирать в массив все найденные значения (по умолчанию `false`).
     * 
     * @return array|null
     */
    public function find(mixed $value, string $key, bool $collect = false): ?array
    {
        $result = [];
        foreach($this->map as $index => $item) {
            if ($item[$key] === $value) { 
                $item['code'] = $index;
                if ($collect) {
                    $result[] = $item;
                } else {
                    return $item;
                }
            }
        }
        return $collect ? $result : null;
    }

    /**
     * Поиска значения в массиве по карте.
     * 
     * @param mixed $value Значение поиска.
     * @param string $key Ключ поиска.
     * @param string $subKey Ключ поиска в массиве.
     * 
     * @param bool $collect Собирать в массив все найденные значения (по умолчанию `false`).
     * 
     * @return array|null
     */
    public function findIn(mixed $value, string $key, string $subKey, bool $collect = false): ?array
    {
        $result = [];
        foreach($this->map as $index => $item) {
            if ($item[$key][$subKey] === $value) {
                $item['code'] = $index;
                if ($collect)
                    $result[] = $item;
                else
                    return $item;
            }
        }
        return $collect ? $result : null;
    }

    /**
     * Возвращает записи карты обозначений по указанным кодам.
     * 
     * Пример: `['code' => ['key' => 'value'...], 'code' => ['key' => 'value'...]...]`
     * 
     * @param array<int, string> $codes Коды карты.
     * 
     * @return array<string, array>
     */
    public function getSome(array $codes): array
    {
        $result = [];
        foreach ($codes as $code) {
            if (isset($this->map[$code])) {
                $row         = $this->map[$code];
                $row['code'] = $code;
                $result[]    = $row;
            }
        }
        return $result;
    }

    /**
     * Возвращает записи карты обозначений по указанному ключу записи.
     * 
     * Пример: `['code' => ['key' => 'value'...], 'code' => ['key' => 'value'...]...]`.
     * 
     * @param string $key Ключ записи.
     * 
     * @return array<string, array>
     */
    public function getSomeValues(string $key): array
    {
        $result = [];
        foreach ($this->map as $code => $row) {
            if (isset($row[$key])) {
                $result[] = $row[$key];
            }
        }
        return $result;
    }

    /**
     * Возвращает запись карты обозначений по указанному коду.
     * 
     * @param string|array<int, string> $code Код(ы) карты.
     * @param mixed $default Значение по умолчанию если запись отсутствует с 
     * указанным кодом (только если указан один код).
     * 
     * @return mixed
     */
    public function get(string|array $code, mixed $default = null): mixed
    {
        if (is_string($code))
            return $this->map[$code] ?? $default;
        else
        if (is_array($code)) {
           return $this->getSome($code);
        }
        return null;
    }

    /**
     * Проверяет, имеет ли карта обозначений запись с указанным кодом.
     * 
     * @param string $code Код карты.
     * 
     * @return bool
     */
    public function validate(string $code): bool
    {
        return isset($this->map[$code]);
    }

    /**
     * Возвращает количество записей карты обозначений.
     * 
     * @return int
     */
    public function getCount(): int
    {
        return sizeof($this->map);
    }
}
