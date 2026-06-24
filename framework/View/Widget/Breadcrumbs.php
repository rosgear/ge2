<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Widget;

use Ge;
use Ge\View\Widget;
use Ge\Helper\Html;

/**
 * Виджет "Хлебные крошки" (навигационные цепочки, breadcrumbs) предназначен для 
 * отображения списка ссылок, указывающих положение текущей страницы  в иерархической 
 * структуре сайта.
 * 
 * Например, "Главная / Категория / Статья". Элемент "Статья", является активным 
 * элементом указывающий на положение текущей страницы. Все остальные элементы, будут
 * иметь ссылки.
 * 
 * Для использования, необходимо укатать параметр "links" в конфигурации виджета.
 * Пример:
 * ```php
 * echo Breadcrumbs::widget([
 *     'links' => [
 *         [
 *             'label' => 'Категория статьи',
 *             'url'   => 'post-category'
 *         ],
 *         [
 *              'label' => 'Стьтья'
 *          ]
 *     ]
 * ]);
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class Breadcrumbs extends Widget
{
    /**
     * Указывает, следует ли HTML-кодировать метки ссылок.
     * 
     * @var bool
     */
    public bool $encode = true;

    /**
     * Имя тега контейнера хлебных крошек.
     * 
     * @var string 
     */
    public string $tag = 'ul';

    /**
     * Атрибуты HTML для тега контейнера хлебных крошек.
     * 
     * @var array
     */
    public array $options = ['class' => 'breadcrumb'];

    /**
     * Первая гиперссылка в хлебных крошках (домашняя ссылка).
     * 
     * Если ссылка не установлена, то будет использоваться {@see \Ge\Mvc\Application::$homeLink}.
     * 
     * @var array
     */
    public array $homeLink;

    /**
     * Список ссылок, которые будут отображаться в хлебных крошках.
     * 
     * Если ссылки отсутствуют, визуализации (рендера) не будет.
     * Каждый элемент массива представляет собой одну ссылку в хлебных крошках со 
     * следующей структурой:
     * ```php
     * [
     *     'label'    => 'Метка ссылки', // обязательно
     *     'url'      => '...' или [...],  // будет подставляться в {@see \Ge\Helper\Url::to()}
     *     'template' => '<li>{link}</li>', // шаблон или будет использоваться {@see Breadcrumbs::$itemTemplate}
     * ]
     * ```
     * Если элемент не активный, указывается параметр "url", в других случаях - нет.
     * 
     * Для указания в метках ссылок кода HTML, необходимо установить {@see Breadcrumbs::$encode} 
     * в `true` или `'encode' => true` в параметрах ссылки, например:
     * ```php
     * [
     *     'label'  => '<strong>Моя метка</strong>',
     *     'encode' => true
     * ]
     * ```
     * 
     * @var array
     */
    public array $links = [];

    /**
     * Шаблон используется для отображения активных элементов.
     * 
     * Выражение `{link}` шаблона будет заменен фактической ссылкой HTML для каждого
     * активного элемента.
     * 
     * @var string 
     */
    public string $activeItemTemplate = "<li class=\"active\">{link}</li>";

    /**
     * Шаблон используется для отображения неактивных элементов.
     * 
     * Выражение `{link}` шаблона будет заменено фактической ссылкой HTML для каждого
     * неактивного элемента.
     * 
     * @var string
     */
    public string $itemTemplate = "<li>{link}</li>";

    /**
     * {@inheritdoc}
     */
    public function run(): mixed
    {
        if (empty($this->links)) {
            return '';
        }

        $links = [];
        // если домашняя ссылка не указана
        if (!isset($this->homeLink)) {
            $links[] = $this->renderItem([
                'label' => Ge::t('app', 'Home'),
                'url' => Ge::$app->baseUrl,
            ], $this->itemTemplate);
        } else
        if ($this->homeLink) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
        }

        foreach ($this->links as $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate);
        }
        return Html::tag($this->tag, implode('', $links), $this->options);
    }

    /**
     * Выводит элемент (ссылку) навигационной цепочки.
     * 
     * @param array $link Параметры ссылки, см. {@see Breadcrumbs::$links}.
     * @param string $template Шаблон элемента, где выражение `{link}` шаблона будет 
     *     заменено фактической ссылкой HTML.
     * 
     * @return string Возвращает элемент навигационной цепочки.
     */
    protected function renderItem(array $link, string $template): string
    {
        // если необходимо кодировать метки
        if (isset($link['encode']) ? $link['encode'] : $this->encode) {
            $label = Html::encode($link['label']);
        } else {
            $label = $link['label'];
        }

        // если указан шаблон
        if (isset($link['template'])) {
            $template = $link['template'];
        }

        // если URL-адрес
        if (isset($link['url'])) {
            $options = $link;
            unset($options['template'], $options['label'], $options['url']);
            $link = Html::a($label, $link['url'], $options);
        } else {
            $link = $label;
        }
        return strtr($template, ['{link}' => $link]);
    }

    /**
     * Добавляет элемент (ссылку) в навигационную цепочку.
     * 
     * @param string $label Название элемента навигационной цепочки.
     * @param null|string $url URL-адрес (по умолчанию `null`).
     * @param null|string $template Шаблон элемента, если не указан, будет 
     *     использоваться {@see Breadcrumbs::$itemTemplate} (по умолчанию `null`).
     * 
     * @return $this
     */
    public function addLink(string $label, ?string $url = null, ?string $template = null): static
    {
        $item = ['label' => $label];

        if ($url) {
            $item['url'] = $url;
        }
        if ($template) {
            $item['template'] = $template;
        }
        $this->links[] = $item;
        return $this;
    }

    /**
     * Устанавливает элемент (ссылку) в навигационной цепочке.
     * 
     * @param int $index Порядковый номер элемента навигационной цепочки.
     * @param mixed $options Параметры элемента навигационной цепочки.
     * 
     * @return $this
     */
    public function setLink(int $index, mixed $options): static
    {
        $this->links[$index] = $options;
        return $this;
    }

    /**
     * Удаляет элемент (ссылку) из навигационной цепочки.
     * 
     * @param int $index Порядковый номер элемента навигационной цепочки.
     * 
     * @return $this
     */
    public function removeLink(int $index): static
    {
        unset($this->links[$index]);
        return $this;
    }
}
