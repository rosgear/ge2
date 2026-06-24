<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Validator;

use Ge\Helper\Json;

/**
 * Formatter предназначен для форматирования значений атрибутов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Formatter
{    
    /**
     * Форматирует значение атрибутов.
     * 
     * @param array $rules Правила форматирования значений атрибутов.
     * @param array $attributes Атрибуты с их значениями.
     * 
     * @return void
     */
    public function format(array $rules, array &$attributes): void
    {
        foreach ($rules as $rule) {
            $names   = (array) $rule[0];
            $options = array_slice($rule, 1);
            foreach ($names as $name) {
                $value = $attributes[$name] ?? null;
                $attributes[$name] = $this->formatWithOptions($value, $name, $options);
            }
        }
    }

    /**
     * Форматирует значение атрибутов c параметрами.
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param array $options Параметры.
     * 
     * @return mixed
     */
    public function formatWithOptions(mixed $value, string $attribute, array $options): mixed
    {
        foreach ($options as $optName => $params) {
            if (is_int($optName)) {
                $optName = $params;
                $params  = [];
            } else {
                $params = (array) $params;
            }
            array_unshift($params, $attribute);
            array_unshift($params, $value);
            
            $value = call_user_func_array([$this, 'do' . $optName], $params);
        }
        return $value;
    }

    /**
     * doTags
     * 
     * Например: '["value1","value2"]' => 'value1,value2'
     * 
     * @param mixed $value
     * @param string $attribute
     * 
     * @return string
     */
    public function doTags($value, string $attribute): string
    {
        if ($value) {
            $arr = json_decode($value, true);
            return implode(',', $arr);
        }
        return '';
    }

    /**
     * doTrim
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param string $type
     * 
     * @return string
     */
    public function doTrim($value, string $attribute, string $type = 'both'): string
    {
        if ($type === 'left')
            return trim($value);
        else
        if ($type === 'right')
            return trim($value);
        else
            return trim($value);
    }

    /**
     * Задаёт тип указанному значению атрибута.
     * 
     * Пример:
     * ```php
     * ['attribute', 'type' => ['float']]]
     * ```
     * или
     * ```php
     * [['attribute', ...], 'type' => 'float']
     * ```
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param string $type
     *     Допустимыми значениями являются:
     *     "boolean" или "bool"
     *     "integer" или "int"
     *     "float" или "double"
     *     "string"
     *     "array"
     *     "object"
     *     "null".
     * 
     * @return mixed
     */
    public function doType(mixed $value, string $attribute, string $type, array $options = []): mixed
    {
        if ($type === 'float' || $type === 'double') {
            $value = strtr($value, ',', '.');
        }
        settype($value, $type);
        return $value;
    }

    /**
     * doLogic
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param string|int|false $true
     * @param string|int|false $false
     * 
     * @return mixed
     */
    public function doLogic($value, string $attribute, $true = '1', $false = '0')
    {
        if (!isset($_POST[$attribute])) {
            $value = null;
        }
        if ($value === null)
            return $false;
        else
        if (is_numeric($value))
            return (int) $value > 0 ? $true : $false;
        else
            return $value === 'on' ? $true : $false;
    }

    /**
     * doSafe
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * 
     * @return string
     */
    public function doSafe($value, string $attribute): string
    {
        if (is_string($value)) {
            $value = trim($value);
            $value = strip_tags($value);
        } else
            return '';
        return $value;
    }

    /**
     * doCombo
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * 
     * @return null|string
     */
    public function doCombo($value, string $attribute): ?string
    {
        return $value === 'null' ? null : $value;
    }

    /**
     * Форматирует значение атрибута в указанный формат даты или времени.
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param string $format Результирующий формат даты или времени (по умолчанию 'Y-m-d').
     * 
     * @return null|string Возвращает значение `null`, если значение атрибута пустое.
     */
    public function doDate($value, string $attribute, string $format = 'Y-m-d'): ?string
    {
        // для корректного формативарония: d/m/Y => d-m-Y (иначе: d/m/Y => m/d/Y)
        if ($value) {
            $value = str_replace('/', '-', $value);
            return date($format, strtotime($value));
        }
        return null;
    }

    /**
     * Форматирует значение атрибута c помощью замены.
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param array $replace Массив замены значения в виде пар 'значение - новое значение' (по умолчанию `[]`).
     * 
     * @return null|string Возвращает значение `null`, если значение атрибута пустое.
     */
    public function doReplace($value, string $attribute, array $replace = []): ?string
    {
        if ($replace && array_key_exists($value, $replace))
            return $replace[$value];
        else
            return $value;
    }

    /**
     * Форматирует значение атрибута в JSON-формат.
     * 
     * @param mixed $value Значение атрибута.
     * @param string $attribute Название атрибута.
     * @param array $merge Делает слияение указанного массива с форматируемым значением 
     *     (по умолчанию `[]`).
     * @param array $format Правила форматирования значения (по умолчанию `[]`).
     * 
     * @return string
     */
    public function doJson($value, string $attribute, array $merge = [], array $format = []): string
    {
        if (is_string($value)) {
            $value = $value ? Json::decode($value) : [];
        }

        if ($value) {
            if ($merge)  $value = array_merge($merge, $value);
            if ($format) $this->format($format, $value);
            return Json::encode($value, true);
        }
        return '{}';
    }
}
