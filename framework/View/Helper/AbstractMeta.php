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
 * Абстрактый класс формирования метатегов HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class AbstractMeta implements HelperInterface
{
    /**
     * Разделитель имент.
     * 
     * @var string
     */
    protected string $namePrefix = '';

    /**
     * Массив подключаемых метатегов.
     * 
     * @var array
     */
    protected array $meta = [];

    /**
     * Вывод комментариев.
     * 
     * @var bool
     */
    public bool $renderComments = true;

    /**
     * Комментарий.
     * 
     * @var string
     */
    public string $comment = '';

    /**
     * Используемые метатеги.
     * 
     * @var array
     */
    protected array $common = [
        'site'        => '',
        'url'         => '',
        'author'      => '',
        'title'       => '',
        'keywords'    => '',
        'description' => '',
        'tag'         => '',
        'image'       => '',
    ];

    /**
     * Устанавливает найболее часто используемые атрибуты.
     *
     * @param array $names Атрибуты со значениями в виде пар "ключ - значение".
     * 
     * @return $this
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        return $this;
    }

    /**
     * Установка тега meta с указанием имени, контента и его атрибутов.
     * 
     * @param string $attribute Атрибуты.
     * @param string $name Название тега.
     * @param string|array $content Контент.
     * @param string $prefix Префикс (по умолчанию `null`).
     * 
     * @return $this
     */
    public function set(string $attribute, string $name, string|array $content, ?string $prefix = null): static
    {
        if ($content) {
            if ($prefix === null) {
                $prefix = $this->namePrefix;
            }
            $name = $prefix . $name;
            $this->meta[$name] = [$attribute => $name, 'content' => $content];
        }
        return $this;
    }

    /**
     * Устанавливает атрибуты тегу "meta" с указаным именем.
     *
     * @param string $name Название тега
     * @param array $attributes Атрибуты тега в виде пар "ключ - значение" (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function setTag(string $name, array $attributes = []): static
    {
        $this->meta[$name] = $attributes;
        return $this;
    }

    /**
     * Устанавливает значение атрибута "content" тегу "meta" с указаным именем.
     * 
     * @param string $name Название тега.
     * @param string|array $content Значение атрибута тега "content".
     * @param null|string $prefix Префикс к имени тега. Если значение `null`, то будет 
     *     применяться {@see AbstractMeta::$namePrefix} (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setName(string $name, string|array $content, ?string $prefix = null): static
    {
        if ($content) {
            if ($prefix === null) {
                $prefix = $this->namePrefix;
            }
            $name = $prefix . $name;
            if (is_array($content)) {
                $this->meta[$name] = [];
                foreach ($content as $value) {
                    $this->meta[$name][] = ['name' => $name, 'content' => str_replace('"', '&quot;', $value)];
                }
            } else {
                $this->meta[$name] = ['name' => $name, 'content' => str_replace('"', '&quot;', $content)];
            }
        }
        return $this;
    }

    /**
     * Установка тега meta с указанием контента и свойств
     * (вид: "<<property> property="<property>" content="<content>" />").
     *
     * @param string $property Название тега и свойства.
     * @param string|null $content Контент тега.
     * @param string|null $prefix Префикс имени тега (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setProperty(string $property, ?string $content, ?string $prefix = null): static
    {
        if ($prefix === null) {
            $prefix = $this->namePrefix;
        }

        $property = $prefix . $property;
        $this->meta[$property] = ['property' => $property, 'content' => $content ?: ''];
        return $this;
    }

    /**
     * Установка тега meta с указанием http-equiv и контента
     * (вид: "<<name> http-equiv="<name>" content="<content>" />").
     *
     * @param string $name Название тега и http-equiv.
     * @param string $content Контент тега.
     * 
     * @return $this
     */
    public function setEquiv(string $name, ?string $content): static
    {
        $this->meta[$name] = ['http-equiv' => $name, 'content' => $content ?: ''];
        return $this;
    }

    /**
     * Удаление тега meta.
     *
     * @param string $name Название тега.
     * 
     * @return $this
     */
    public function unsetTag(string $name): static
    {
        if (isset($this->meta[$name])) {
            unset($this->meta[$name]);
        }
        return $this;
    }

    /**
     * Замена тега meta.
     *
     * @param string $name Название тега.
     * @param array $attributes Атрибуты (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function appendTag(string $name, array $attributes = []): static
    {
        $this->meta[$name] = $attributes;
        return $this;
    }

    /**
     * Возвращение тега meta в ввиде html.
     *
     * @param string $attribute Атрибуты тега ("name", "property", "http-equiv", "itemprop").
     * @param string $name Название тена.
     * @param string $content Контент тега.
     * 
     * @return string
     */
    public function renderTag(string $attribute, string $name, string $content): string
    {
        return '<meta ' . $attribute . '="' . $name . '" content="' . $content . '">';
    }

    /**
     * Возвращение тега meta в ввиде html с указанием массива атрибутов.
     *
     * @param array|null $attributes Атрибуты.
     * 
     * @return string
     */
    public function renderTagAttr(?array $attributes): string
    {
        if (empty($attributes)) return '';

        $tag = '';
        foreach ($attributes as $attr => $value) {
            $tag .= $attr . '="' . $value . '" ';
        }
        return '<meta ' . $tag . '>';
    }

    /**
     * Возвращение комментария ввиде html.
     * 
     * @param string $comment Комментарий.
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function renderComment(string $comment, string $indent = ''): string
    {
        return '<!-- ' . $comment . ' -->' . PHP_EOL . $indent;
    }

    /**
     * Добавление комментария.
     *
     * @param string $comment Комментарий.
     * 
     * @return $this
     */
    public function addComment(string $comment): static
    {
        $this->meta[] = $comment;
        return $this;
    }


    /**
     * Возвращение всех метатегов ввиде html.
     * 
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function render(string $indent = ''): string
    {
        $tags = '';
        if ($this->renderComments && $this->comment) {
            // есть ли смысл выводить комментарий если нет информации
            if ($this->meta)
                $tags .= $this->renderComment($this->comment, $indent);
        }
        foreach ($this->meta as $name => $attributes) {
            if (is_string($attributes))
                $tags .= $this->renderComment($attributes, $indent);
            else {
                if (array_is_list($attributes)) {
                    foreach ($attributes as $one) {
                        $tags .= $this->renderTagAttr($one) . PHP_EOL . $indent;
                    }
                } else
                    $tags .= $this->renderTagAttr($attributes) . PHP_EOL . $indent;
            }
        }
        return $tags;
    }

    /**
     * Возвращение атрибутов схемы в теге "<html>".
     *
     * @return array
     */
    public function getHtmlAttributes(): array
    {
        return [];
    }

    /**
     * Возвращает метатеги.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->meta;
    }
}
