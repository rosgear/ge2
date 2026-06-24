<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Helper;

/**
 * Вспомогательный класс формирования атрибутов тега "<HTML>".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Html implements HelperInterface
{
    /**
     * @var string Разделитель значений атрибутов тега.
     */
    public const ATTR_SEPARATOR = ' ';

    /**
     * Атрибуты в виде пар "ключ - значение".
     * 
     * @var array
     */
    protected array $attributes = [];

    /**
     * Добавление значения атрибуту владельца.
     *
     * @param string $owner Владелец атрибута.
     * @param string $attribute Название атрибута.
     * @param string $value Значение.
     * 
     * @return $this
     */
    public function appendAttribute(string $owner, string $attribute, string $value): static
    {
        if (!isset($this->attributes[$owner]))
            $this->attributes[$owner] = array();
        if (isset($this->attributes[$owner][$attribute]))
            $this->attributes[$owner][$attribute] = $this->attributes[$owner][$attribute] . self::ATTR_SEPARATOR . $value;
        else
            $this->attributes[$owner][$attribute] = $value;
        return $this;
    }

    /**
     * Добавление атрибутов владельцу.
     *
     * @param string $owner Владелец атрибута.
     * @param array $attributes Массив атрибутов.
     * 
     * @return $this
     */
    public function appendAttributes(string $owner, array $attributes): static
    {
        foreach ($attributes as $attribute => $value) {
            $this->appendAttribute($owner, $attribute, $value);
        }
        return $this;
    }

    /**
     * Установка значения атрибута владельца.
     *
     * @param string $owner Владелец атрибута.
     * @param array $attribute Газвание атрибута.
     * @param string $value Значение.
     * 
     * @return $this
     */
    public function setAttribute(string $owner, array $attribute, string $value): static
    {
        if (!isset($this->attributes[$owner])) {
            $this->attributes[$owner] = [];
        }
        $this->attributes[$owner][$attribute] = $value;
        return $this;
    }

    /**
     * Установка значения атрибута владельца.
     *
     * @param string $owner Владелец атрибута.
     * @param null|string $attribute Название атрибута.
     * 
     * @return string|null
     */
    public function getAttribute(string $owner, ?string $attribute = null): ?string
    {
        if (!isset($this->attributes[$owner])) return null;

        if ($attribute === null)
            return $this->attributes[$owner];
        else
            return $this->attributes[$owner][$attribute];
    }

    /**
     * Удаление атрибута владельца.
     *
     * @param string $owner Владелец атрибута.
     * @param string $attribute Название атрибута.
     * 
     * @return $this
     */
    public function unsetAttribute(string $owner, string $attribute): static
    {
        if (!isset($this->attributes[$owner])) return $this;

        unset($this->attributes[$owner][$attribute]);
        return $this;
    }

    /**
     * Возвращает атрибуты с его значением.
     *
     * @param string $name Название атрибута.
     * @param string $value Значение атрибута.
     * 
     * @return string
     */
    public static function attribute(string $name, string $value): string
    {
        if (empty($value)) {
            return $name;
        }
        return $name . '="' . $value . '"';
    }

    /**
     * Возвращает все сформированные атрибуты с их значениями.
     *
     * @param string $owner Владелец.
     * 
     * @return string
     */
    public function renderAttributes(string $owner): string
    {
        if (!isset($this->attributes[$owner])) return '';

        $arr = $this->attributes[$owner];
        $str = '';
        foreach ($arr as $name => $value) {
            $str .= ' ' . self::attribute($name, $value);
        }
        return $str;
    }

    /**
     * Возвращает сформированный атрибут с его значением.
     *
     * @param string $owner Владелец атрибута.
     * @param string $attribute Название атрибута.
     * 
     * @return string
     */
    public function renderAttribute(string $owner, string $attribute): string
    {
        if (!isset($this->attributes[$owner][$attribute])) return '';

        return self::attribute($attribute, $this->attributes[$owner][$attribute]);
    }
}
