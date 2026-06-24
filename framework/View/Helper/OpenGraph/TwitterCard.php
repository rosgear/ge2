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
 * Вспомогательный класс формирования "Open Graph" метатега для "Twitter Card" HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper\OpenGraph
 * @since 2.0
 */
class TwitterCard extends AbstractMeta
{
    /**
     * {@inheritdoc}
     */
    protected string $namePrefix = 'twitter:';

    /**
     * {@inheritdoc}
     */
    public string $comment = 'Twitter card data';

    /**
     * {@inheritdoc}
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        $this
            ->setName('card', 'summary')
            ->setName('title', $names['title'] ?? '')
            ->setName('site', $names['site'] ?? '')
            ->setName('description', $names['description'] ?? '')
            ->setName('image', $names['image'] ?? '');

        if (isset($names['author']) && $names['author']) {
            $this->setName('creator', '@' . $names['author']);
        }
        return $this;
    }
}
