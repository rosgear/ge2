<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Helper\OpenGraph;

use Ge\View\Helper\AbstractMeta;

/**
 * Вспомогательный класс формирования "Open Graph" метатега HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper\OpenGraph
 * @since 2.0
 */
class OpenGraph extends AbstractMeta
{
    /**
     * @var string Добавляет приставку к значению атрибута метатега.
     */
    public const OG_PREFIX = 'og';

    /**
     * Разделитель имён.
     * 
     * @var string
     */
    protected string $namePrefix = 'og:';

    /**
     * Шаблон схемы Open Graph.
     * 
     * @var string
     */
    protected string $schemaPattern = 'og: http://ogp.me/ns%s#';

    /**
     * Типы объектов.
     * 
     * @var array
     */
    protected array $types = [];

    /**
     * Плагины.
     * 
     * @var array<string, string>
     */
    protected array $plugins = [
        'twitter'   => 'Ge\View\Helper\OpenGraph\TwitterCard',
        'schemaOrg' => 'Ge\View\Helper\OpenGraph\SchemaOrg',
        'vk'        => 'Ge\View\Helper\OpenGraph\VKSchema'
    ];

    /**
     * Возвращение плагин по указанному имени.
     *
     * @param string $pluginName Имя плагина.
     * 
     * @return mixed
     */
    public function __get(string $pluginName)
    {
        if (!isset($this->$pluginName)) {
            if (!isset($this->plugins[$pluginName])) {
                return null;
            }
            $plugin = $this->plugins[$pluginName];
            // deprecated PHP 8.2 (creation of dynamic property)
            return @$this->$pluginName = new $plugin();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        $this->setName('type', 'article');
        if (isset($names['title']))
            $this->setName('title', $names['title']);
        if (isset($names['url']))
            $this->setName('url', $names['url']);
        if (isset($names['description']))
            $this->setName('description', $names['description']);
        if (isset($names['image']))
            $this->setName('image', $names['image']);
        if (isset($names['site']))
            $this->setName('site_name', $names['site']);
        if (isset($names['tag']))
            $this->setName('tag', $names['tag']);
        return $this;
    }

    /**
     * Возвращаение схемы объектов Open Graph.
     *
     * @return array
     */
    public function getHtmlAttributes(): array
    {
        $result = [];
        foreach($this->plugins as $plugin => $class) {
            if (isset($this->$plugin)) {
                $attributes = $this->$plugin->getHtmlAttributes();
                
                if ($attributes)
                    $result = array_merge($result, $attributes);
            }
        }

        if (sizeof($this->types) > 0) {
            $type = end($this->types);
            $type = $type['type'];
            $result['prefix'] = sprintf($this->schemaPattern, '/' . $type);
        }
        return $result;
    }

    /**
     * Установка типа объекта ("Audio" "Video", "Article").
     *
     * @param string $name Ключ для типа объектов ($types).
     * @param string $type Тип объекта.
     * @param array $attributes Массив атрибутов объекта.
     * 
     * @return $this
     */
    public function setType(string $name, string $type, array $attributes): static
    {
        $this->types[$name] = ['type' => $type, 'attributes' => $attributes];
        return $this;
    }

    /**
     * Возвращание собранных тегов по протоколу Open Graph.
     *
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function renderTypes(string $indent = ''): string
    {
        $tags = '';
        foreach ($this->types as $type => $options) {
            $type       = $options['type'];
            $attributes = $options['attributes'];
            if (isset($attributes[$type])) {
                $tags .= $this->renderTag('property', self::OG_PREFIX . $type, $attributes[$type]) . PHP_EOL . $indent;
                unset($attributes[$type]);
            }
            foreach ($attributes as $attr => $value) {
                $tags .= $this->renderTag('property', self::OG_PREFIX . $type . ':' . $attr, $value) . PHP_EOL . $indent;
            }
        }
        return $tags;
    }

    /**
     * Возвращание собранных тегов Open Graph.
     *
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function renderTags(string $indent = ''): string
    {
        $tags = '';
        foreach ($this->meta as $name => $attributes) {
            if (is_string($attributes))
                $tags .= $this->renderComment($attributes, $indent);
            else
                $tags .= $this->renderTagAttr($attributes) . PHP_EOL . $indent;
        }
        return $tags;
    }

    /**
     * Возвращание всех собранных тегов Open Graph.
     *
     * @param string $indent Отступ от левого края в символах (по умолчанию '').
     * 
     * @return string
     */
    public function render(string $indent = ''): string
    {
        $html = '';
        // выводить комментарий
        if ($this->renderComments && ($this->meta || $this->types))
            $html .= $this->renderComment('Open Graph data', $indent);
        // если есть свойства
        if ($this->meta)
            $html .= $this->renderTags($indent);
        // если есть указаны типы
        if ($this->types)
            $html .= $this->renderTypes($indent);
        // вывод плагинов
        foreach ($this->plugins as $plugin => $class) {
            if (isset($this->$plugin)) {
                $html .= $this->$plugin->render($indent);
            }
        }
        return $html;
    }
}
