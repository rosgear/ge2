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
 * Вспомогательный класс формирования микроразметки "SchemaOrg" HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper\OpenGraph
 * @since 2.0
 */
class SchemaOrg extends AbstractMeta
{
    /**
     * {@inheritdoc}
     */
    public string $comment = 'Schema.org markup';

    /**
     * {@inheritdoc}
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        $this
            ->set('itemprop', 'name', $names['title'] ?? '')
            ->set('itemprop', 'description', $names['description'] ?? '')
            ->set('itemprop', 'image', $names['image'] ?? '');
        return $this;
    }

    /**
     * Возвращение схемы объектов Open Graph
     *
     * @return array
     */
    public function getHtmlAttributes(): array
    {
        return [
            'itemscope' => '',
            'itemtype'  => 'http://schema.org/Article'
        ];
    }
}
