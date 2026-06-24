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
 * Вспомогательный класс формирования микроразметки "VK" HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper\OpenGraph
 * @since 2.0
 */
class VKSchema extends AbstractMeta
{
    /**
     * {@inheritdoc}
     */
    protected string $namePrefix = 'vk:';

    /**
     * {@inheritdoc}
     */
    public string $comment = 'VK data';

    /**
     * {@inheritdoc}
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        $this
            ->setName('image', $names['image'] ?? '');
        return $this;
    }
}
