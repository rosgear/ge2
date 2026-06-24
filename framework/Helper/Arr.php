<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use ArrayAccess;
use InvalidArgumentException;
use Ge\Stdlib\Collection;

/**
 * Вспомогательный класс Array.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Arr
{
    /**
     * Заменяет значения в ассоциативном массиве $subject на значения указанные в 
     * ассоциативном массиве $replace.
     * 
     * Пример:
     * ```php
     * replaceValues(['fruit' => 'apple'], ['apple' => 'meat'])
     * // результат: ['fruit' => 'meat']
     * ```
     *
     * @param array $subject
     * @param array $replace
     * 
     * @return void
     */
    public static function replaceValues(array &$subject, array $replace): void
    {
        array_walk_recursive($subject, function (&$value, $key) use ($replace) {
            if (is_string($value) && isset($replace[$value])) {
                $value = $replace[$value];
            }
        });
    }

    /**
     * Возвращает ассоциативный массив, только с теми ключами $keys, которые указанные в $arr.
     * 
     * @param array $keys
     * @param array $arr
     * 
     * @return array
     */
    public static function getSomeKeys(array $keys, array $arr): array
    {
        $rows = [];
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                $rows[$key] = $arr[$key];
            }
        }
        return $rows;
    }

    /**
     * Возвращает значение по умолчанию для данного значения.
     *
     * @param mixed $value
     * @param mixed $args
     * 
     * @return mixed
     */
    public static function value($value, ...$args): mixed
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }

    /**
     * Определяет, является ли данное значение доступным для массива.
     *
     * @param mixed $value
     * 
     * @return bool
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Добавляет элемент в массив, используя обозначение "dot", если он не существует.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * 
     * @return array
     */
    public static function add(array $array, string $key, mixed $value): array
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }
        return $array;
    }

    /**
     * Сварачивает массив массивов в один массив.
     *
     * @param array $array
     * 
     * @return array
     */
    public static function collapse($array): array
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Перекрестное соединение массивов, возвращая все возможные перестановки.
     *
     * @param array ...$arrays
     * 
     * @return array
     */
    public static function crossJoin(...$arrays): array
    {
        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];

            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;

                    $append[] = $product;
                }
            }
            $results = $append;
        }
        return $results;
    }

    /**
     * Разделяет массив на два массива. Один с ключами, другой со значениями.
     *
     * @param array $array
     * 
     * @return array
     */
    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Возвращает многомерный ассоциативный массив с помощью точек.
     *
     * @param array $array
     * @param string $prepend (по умолчанию '').
     * 
     * @return array
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }
        return $results;
    }

    /**
     * Получает весь заданный массив, кроме указанного массива ключей.
     *
     * @param array $array
     * @param array|string $keys
     * 
     * @return array
     */
    public static function except(array $array, array|string $keys): array
    {
        static::forget($array, $keys);
        return $array;
    }

    /**
     * Определяет, существует ли данный ключ в предоставленном массиве.
     *
     * @param ArrayAccess|array $array
     * @param string|int $key
     * 
     * @return bool
     */
    public static function exists(ArrayAccess|array $array, string|int $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }

    /**
     * Возвращает первый элемент массива, прошедший заданный тест на истинность.
     *
     * @param array $array
     * @param null|callable $callback (по умолчанию `null`).
     * @param mixed $default (по умолчанию `null`).
     * 
     * @return mixed
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return static::value($default);
            }
            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return static::value($default);
    }

    /**
     * Возвращает последний элемент массива, прошедший заданный тест на истинность.
     *
     * @param array $array
     * @param null|callable  $callback (по умолчанию `null`).
     * @param mixed $default (по умолчанию `null`).
     * 
     * @return mixed
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? static::value($default) : end($array);
        }
        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Сводит многомерный массив в одномерный.
     *
     * @param array $array
     * @param int|float $depth (по умолчанию `INF`).
     * 
     * @return array
     */
    public static function flatten(array $array, int|float $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Удалите один или несколько элементов массива из заданного массива, используя обозначение "точка".
     *
     * @param array $array
     * @param array|string $keys
     * 
     * @return void
     */
    public static function forget(array &$array, array|string $keys): void
    {
        $original = &$array;
        $keys = (array) $keys;

        if (count($keys) === 0) return;

        foreach ($keys as $key) {
            // если точный ключ существует на верхнем уровне, удаляет его
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);

            // очищает перед каждым проходом
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Получает элемент из массива, используя обозначение "dot".
     *
     * @param ArrayAccess|array  $array
     * @param string|int|null  $key
     * @param mixed  $default (по умолчанию `null`).
     * 
     * @return mixed
     */
    public static function get(ArrayAccess|array $array, string|int|null $key, mixed $default = null): mixed
    {
        if (! static::accessible($array)) {
            return static::value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? static::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }
        return $array;
    }

    /**
     * Проверяет, существует ли элемент или элементы в массиве, используя обозначение "dot".
     *
     * @param ArrayAccess|array $array
     * @param string|array $keys
     * 
     * @return bool
     */
    public static function has(ArrayAccess|array $array, string|array  $keys): bool
    {
        $keys = (array) $keys;

        if (! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Определяет, существует ли какой-либо ключ в массиве, используя обозначение "dot".
     *
     * @param ArrayAccess|array $array
     * @param string|array $keys
     * 
     * @return bool
     */
    public static function hasAny(ArrayAccess|array $array, string|array $keys): bool
    {
        if (is_null($keys)) return false;

        $keys = (array) $keys;

        if (!$array) return false;

        if ($keys === []) return false;

        foreach ($keys as $key) {
            if (static::has($array, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Определяет, является ли массив ассоциативным.
     *
     * Массив является "ассоциативным", если он не имеет последовательных цифровых ключей, 
     * начинающихся с нуля.
     *
     * @param array $array
     * 
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Получает подмножество элементов из заданного массива.
     *
     * @param array $array
     * @param array|string $keys
     * 
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Распакует аргументы "value" и "key" переданные в "pluck".
     *
     * @param string|array $value
     * @param string|array|null $key
     * 
     * @return array
     */
    protected static function explodePluckParameters(string|array $value, string|array|null $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Добавляет элемент в начало массива.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key (по умолчанию `null`).
     * 
     * @return array
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if (func_num_args() == 2)
            array_unshift($array, $value);
        else
            $array = [$key => $value] + $array;
        return $array;
    }

    /**
     * Получает значение из массива и удаляет его.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default (по умолчанию `null`).
     * 
     * @return mixed
     */
    public static function pull(array &$array, string $key, mixed $default = null): mixed
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }

    /**
     * Преобразует массив в строку запроса.
     *
     * @param array $array
     * 
     * @return string
     */
    public static function query(array $array): string
    {
        return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Получает одно или заданное количество случайных значений из массива.
     *
     * @param array $array
     * @param int|null $number (по умолчанию `null`).
     * @param bool $preserveKeys (по умолчанию `false`).
     * 
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function random(array $array, int|null $number = null, bool $preserveKeys = false): mixed
    {
        $requested = is_null($number) ? 1 : $number;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) return $array[array_rand($array)];
        if ((int) $number === 0) return [];

        $keys = array_rand($array, $number);

        $results = [];
        if ($preserveKeys) {
            foreach ((array) $keys as $key) {
                $results[$key] = $array[$key];
            }
        } else {
            foreach ((array) $keys as $key) {
                $results[] = $array[$key];
            }
        }
        return $results;
    }

    /**
     * Установит для элемента массива заданное значение, используя обозначение "dot".
     *
     * Если методу не присвоен ключ, будет заменен весь массив.
     *
     * @param array $array
     * @param string|null $key
     * @param mixed $value
     * 
     * @return array
     */
    public static function set(array &$array, string|null $key, mixed $value): array
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        foreach ($keys as $i => $ikey) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // Если ключ не существует на этой глубине, мы просто создадим пустой массив 
            // для хранения следующего значения, что позволяет нам создавать массивы для 
            // хранения окончательного значения значения на правильной глубине. Затем мы 
            // продолжим копаться в массиве.
            if (! isset($array[$ikey]) || ! is_array($array[$ikey])) {
                $array[$ikey] = [];
            }

            $array = &$array[$ikey];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * Перетасует массив и вернет результат.
     *
     * @param array $array
     * @param int|null $seed (по умолчанию `null`).
     * 
     * @return array
     */
    public static function shuffle(array $array, int|null $seed = null): array
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }
        return $array;
    }

    /**
     * Рекурсивно сортирует массив по ключам и значениям.
     *
     * @param array $array
     * @param int $options (по умолчанию `SORT_REGULAR`).
     * @param bool $descending (по умолчанию `false`).
     * 
     * @return array
     */
    public static function sortRecursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value, $options, $descending);
            }
        }

        if (static::isAssoc($array)) {
            $descending
                    ? krsort($array, $options)
                    : ksort($array, $options);
        } else {
            $descending
                    ? rsort($array, $options)
                    : sort($array, $options);
        }
        return $array;
    }

    /**
     * Скомпилирует классы из массива в список классов CSS.
     *
     * @param array $array
     * 
     * @return string
     */
    public static function toCssClasses(array $array): string
    {
        $classList = static::wrap($array);

        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }
        return implode(' ', $classes);
    }

    /**
     * Отфильтрирует массив, используя заданный обратный вызов.
     *
     * @param array $array
     * @param callable $callback
     * 
     * @return array
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Если данное значение не является массивом и не равно null, сделает его массивом.
     *
     * @param mixed $value
     * 
     * @return array
     */
    public static function wrap(mixed $value): array
    {
        if (is_null($value)) return [];

        return is_array($value) ? $value : [$value];
    }
}
