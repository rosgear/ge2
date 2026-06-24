<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Widget;

use Ge\View\Widget;
use Ge\Helper\Html;
use Ge\Helper\Url;

/**
 * Виджет "Меню" отображает многоуровневое меню с использованием вложенных HTML-списков.
 *
 * Основным свойством Меню является свойство {@see Menu::$items}, которое определяет 
 * возможные элементы в меню. Пункт меню может содержать подпункты, определяющие подменю 
 * под этим пунктом меню.
 *
 * Пример использования меню:
 *
 * ```php
 * echo Menu::widget([
 *     'items' => [
 *         ['label' => 'Главная', 'url' => '/'],
 *         [
 *             'label' => 'Новости',
 *             'url'   => '#',
 *             'items' => [
 *                 ['label' => 'Спорт', 'url' => 'news/sport'],
 *                 ['label' => 'Игры',  'url' => 'news/games']
 *             ]
 *         ],
 *         ['label' => 'Авторизация', 'url' => 'login', 'visible' => Ge::$app->user->isGuest()]
 *     ]
 * ]);
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class Menu extends Widget
{
    /**
     * Указывает, следует ли HTML-кодировать метки ссылок.
     * 
     * @var bool
     */
    public bool $encode = true;

    /**
     * Имя тега контейнера пунктов меню.
     * 
     * @var string 
     */
    public string $tag = 'ul';

    /**
     * Атрибуты HTML для тега контейнера пунктов меню.
     * 
     * @var array
     */
    public array $options = [];

    /**
     * Список ссылок, которые будут отображаться в пунктах меню.
     * 
     * Если ссылки отсутствуют, визуализации (рендера) не будет.
     * Каждый элемент массива представляет собой одну ссылку в пунктах меню со 
     * следующей структурой:
     * ```php
     * [
     *     'label'    => 'Метка ссылки', // обязательно
     *     'url'      => '...' или [...],  // будет подставляться в {@see \Ge\Helper\Url::to()}
     *     'template' => '<li>{link}</li>', // шаблон или будет использоваться {@see Menu::$itemTemplate}
     * ]
     * ```
     * Если элемент не активный, указывается параметр "url", в других случаях - нет.
     * 
     * Для указания в метках ссылок кода HTML, необходимо установить {@see Menu::$encode} 
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
    public array $items = [];

    /**
     * Класс CSS добавляемый к активному пункту меню.
     * 
     * @var string
     */
    public string $activeCssClass = 'active';

    /**
     * Шаблон используется для отображения пункта меню.
     * 
     * Выражение `{link}` шаблона будет заменен фактической ссылкой HTML для каждого
     * пункта меню.
     * 
     * @var string
     */
    public string $itemTemplate = "<li>{link}</li>";

    /**
     * Шаблон используется для отображения подпунктов меню.
     * 
     * Выражение `{link}` шаблона будет заменено фактической ссылкой HTML для каждого
     * пункта меню.
     * Выражение `{items}` шаблона будет заменено списком подпунктов меню.
     * 
     * @var string
     */
    public string $submenuTemplate = "<li><a href=\"#\">{label}</a> \n{items}\n</li>";

    /**
     * {@inheritdoc}
     */
    public function run(): mixed
    {
        if (empty($this->items)) {
            return '';
        }

        return $this->renderItems($this->items);
    }

    /**
     * Выводит сформированное меню с пунктами (и подпунктами).
     * 
     * @param array $items Массив пунктов или подпунктов меню.
     * 
     * @return string
     */
    protected function renderItems(array $items): string
    {
        $rows = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                $item = ['label' => $item];
            }

            // если пункт меню имеет подпункты
            if (isset($item['items'])) {
                $menu = $this->renderItems($item['items']);
                $rows[] = $this->renderSubmenu($item, $menu);
            } else {
                $rows[] = $this->renderItem($item);
            }
        }
        return Html::tag($this->tag, "\n\t" . implode("\n\t", $rows) . "\n", $this->options);
    }

    /**
     * Выводит элемент меню.
     * 
     * @param array $item Параметры элемент меню.
     * 
     * @return string Возвращает элемент меню.
     */
    protected function renderItem(array $item): string
    {
        // если необходимо кодировать метки
        if (isset($item['encode']) ? $item['encode'] : $this->encode) {
            $label = Html::encode($item['label']);
        } else {
            $label = $item['label'];
        }

        // если указан шаблон
        $template = $item['template'] ?? $this->itemTemplate;
        // если пункт активный
        $active = isset($item['active']) ? $item['active'] === true : false;

        // если URL-адрес
        if (isset($item['url'])) {
            $options = $item;
            if ($active) {
                Html::addCssClass($options, $this->activeCssClass);
            }
            unset($options['template'], $options['label'], $options['url'], $options['active']);
            $link = Html::a($label, $item['url'], $options);
        } else {
            $link = $label;
        }
        return strtr($template, ['{link}' => $link]);
    }

    /**
     * Выводит подменю.
     * 
     * @param array $item Параметры ссылки.
     * @param string $items Элементы подменю в HTML виде.
     * 
     * @return string Возвращает подменю.
     */
    protected function renderSubmenu(array $item, string $items): string
    {
        // если необходимо кодировать метки
        if (isset($link['encode']) ? $item['encode'] : $this->encode) {
            $label = Html::encode($item['label']);
        } else {
            $label = $item['label'];
        }

        // если указан шаблон
        $template = $item['template'] ?? $this->submenuTemplate;
        $replace = [
            '{label}' => $label,
            '{items}' => $items
        ];
        if (isset($item['url'])) {
            $replace['{url}'] = Html::encode(Url::to($item['url']));
        }

        return strtr($template, $replace);
    }

    /**
     * Добавляет элемент (пункт) меню.
     * 
     * @param string $label Название элемента меню.
     * @param string|null $url URL-адрес (по умолчанию `null`).
     * @param string|null $template Шаблон элемента меню, если не указан, будет 
     *     использоваться {@see Menu::$itemTemplate} (по умолчанию `null`).
     * 
     * @return $this
     */
    public function addItem(string $label, ?string $url = null, ?string $template = null): static
    {
        $item = ['label' => $label];

        if ($url) {
            $item['url'] = $url;
        }
        if ($template) {
            $item['template'] = $template;
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * Добавляет элемент (пункт) меню подменю.
     * 
     * @param string $label Название элемента меню.
     * @param array $items Подпункты меню.
     * @param string|null $template Шаблон элемента меню, если не указан, будет 
     *     использоваться {@see Menu::$itemTemplate} (по умолчанию `null`).
     * 
     * @return $this
     */
    public function addMenu(string $label, array $items, ?string $template = null): static
    {
        $item = ['label' => $label, 'items' => $items];

        if ($template) {
            $item['template'] = $template;
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * Устанавливает элемент (пункт) меню.
     * 
     * @param int $index Порядковый номер элемента меню.
     * @param array $options Параметры элемента меню.
     * 
     * @return $this
     */
    public function setItem(int $index, array $options): static
    {
        $this->items[$index] = $options;
        return $this;
    }

    /**
     * Удаляет элемент (пункт) меню.
     * 
     * @param int $index Порядковый номер элемента меню.
     * 
     * @return $this
     */
    public function removeItem(int $index): static
    {
        unset($this->items[$index]);
        return $this;
    }
}
