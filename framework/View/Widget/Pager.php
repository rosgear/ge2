<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Widget;

use Ge\Helper\Html;
use Ge\View\Widget;
use Ge\Data\Provider\BaseProvider;

/**
 * Виджет "Пейджер" предназначен для отображения списка гиперссылок, управляющий 
 * навигацией перехода между страницами.
 * 
 * Пейджер работает с объектом поставщика данных {@see \Ge\Data\Provider\BaseProvider}, 
 * который включает в себя: сортировку, фильтрацию и разбивку на страницы, а также, 
 * формирует параметры URL для отображения гиперссылок в навигации.
 * 
 * Виджет генерирует только необходимую для навигации HTML-разметку. Для изменения 
 * интерфейса Пейджера, необходимо подключать соответствющие вашему сайту стили CSS.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class Pager extends Widget
{
    /**
     * Количество страниц.
     * 
     * @var int
     */
    public int $pageCount = 0;

    /**
     * Текущая страница.
     * 
     * @var int
     */
    public int $activePage = 1;

    /**
     * Максимальное количество выводимых ссылок на страницы.
     * 
     * Не включает переходы на первую и последнюю страницы.
     * 
     * @var int
     */
    public int $maxButtonCount = 8;

    /**
     * Разделитеть ссылок.
     * 
     * @var string
     */
    public string $separator = '';

    /**
     * Имя тега навигации.
     * 
     * Используется для расположения элементов в навигационной панели.
     * Например: 'nav'.
     * 
     * @var string
     */
    public string $navTag;

    /**
     * Атрибуты HTML для тега навигации.
     * 
     * Например: `['aria-label' => 'pagination]`.
     * 
     * @var array
     */
    public array $navOptions = [];

    /**
     * Имя тега контейнера кнопок.
     * 
     * @var string 
     */
    public string $tag = 'ul';

    /**
     * Текстовая метка для кнопки "первой" страницы.
     * 
     * Текст не будет подвергаться HTML-кодированию.
     * Если указано значение `false`, то кнопка не будет отображаться.
     * 
     * @var string|false
     */
    public string|false $firstPageLabel = '&laquo;';

    /**
     * Текстовая метка для кнопки "предыдущая" страница.
     * 
     * Текст не будет подвергаться HTML-кодированию.
     * Если указано значение `false`, то кнопка не будет отображаться.
     * 
     * @var string|false
     */
    public string|false $prevPageLabel = '&lt;';

    /**
     * Текстовая метка для кнопки "следующая" страница.
     * 
     * Текст не будет подвергаться HTML-кодированию.
     * Если указано значение `false`, то кнопка не будет отображаться.
     * 
     * @var string|false
     */
    public string|false $nextPageLabel = '&gt;';

    /**
     * Текстовая метка для кнопки "последняя" страница.
     * 
     * Текст не будет подвергаться HTML-кодированию.
     * Если указано значение `false`, то кнопка не будет отображаться.
     * 
     * @var string|false
     */
    public string|false $lastPageLabel = '&raquo;';

    /**
     * Атрибуты HTML для тега контейнера кнопок.
     * 
     * @var array
     */
    public array $options = ['class' => 'pagination'];

    /**
     * Шаблон для отображения неактивных кнопок.
     * 
     * Выражение `{link}` шаблона будет заменен фактической ссылкой HTML для каждого
     * неактивного элемента.
     * 
     * @var string
     */
    public string $itemTpl = '<li class="page-item">{link}</li>';

    /**
     * Шаблон для отображения активной кнопки.
     * 
     * Выражение `{link}` шаблона будет заменен фактической ссылкой HTML для каждого
     * активного элемента.
     * 
     * @var string 
     */
    public string $activeItemTpl = '<li class="page-item active">{link}</li>';

    /**
     * Шаблон для отображения троеточия между кнопками.
     * 
     * Применяется для обозначения большого количества страниц.
     * 
     * @var string 
     */
    public string $dotsItemTpl = '<li class="page-item dots">...</li>';

    /**
     * Атрибуты HTML для ссылок в шаблоне отображения кнопок.
     * 
     * @var array
     */
    public array $linkOptions = ['class' => 'page-link'];

    /**
     * Скрыть виджет, если существует только одна страница.
     * 
     * @var bool
     */
    public bool $hideOnSinglePage = true;

    /**
     * Поставщик данных.
     * 
     * Применяется для получения параметров URL-адреса ссылок на страниц.
     * 
     * @var BaseProvider
     */
    public BaseProvider $dataProvider;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий номер страницы.
     * 
     * Параметр передаётся с помощью метода GET и определяется {@see Pagination::definePage()}.
     * Если значение параметра `false`, тогда значение будет '1'.
     * 
     * @var string|null
     */
    public ?string $pageParam;

    /**
     * Выполняет расчёт количества кнопок в контейнере Пейджера.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     ['first', 1, 'label'], // кнопка с индексом "1" и переходом на "первую" страницу
     *     ['prev', i - 1, 'label'], // кнопка с индексом "i - 1" и переходом на "предыдущую" страницу
     *     ['dots', 0], // троеточие с индексом "0"
     *     // ...
     *     ['item', 3], // кнопка с индексом страницы
     *     // ...
     *     ['active', i],  // кнопка с индексом "i", текущая страница
     *     ['dots', 0], // троеточие с индексом "0"
     *     ['next', i + 1, 'label'],  // кнопка с индексом "i + 1" и переходом на "следущую" страницу
     *     ['last', n, 'label'], // кнопка с индексом "n" и переходом на "последнюю" страницу
     * ]
     * ```
     * где, i - индекс текущей страницы, n - количество страниц.
     * 
     * 
     * @return array
     */
    public function calculate(): array
    {
        if ($this->pageCount < 2 && $this->hideOnSinglePage) return [];

        $items = [];

        // до
        $offsetButtons = intdiv($this->maxButtonCount, 2);
        // предыдущая и первая страница
        if ($this->activePage - $offsetButtons - 1 > 0) {
            if ($this->firstPageLabel !== false) {
                $items[] = ['first', 1, $this->firstPageLabel];
            }
            $items[] = ['prev', $this->activePage - 1, $this->prevPageLabel];
            $items[] = ['dots', 0];
        }
        // перед текущей страницы
        for ($i = $offsetButtons; $i >= 1; $i--) {
            if ($this->activePage - $i > 0) {
                $items[] = ['item', $this->activePage - $i];
            }
        }

        // текущая страница
        $items[] = ['active', $this->activePage];

        // после текущей страницы
        for ($i = 1; $i <= $offsetButtons; $i++) {
            if ($this->activePage + $i <= $this->pageCount) {
                $items[] = ['item', $this->activePage + $i];
            }
        }
        // следующая и последняя страница 
        if ($this->activePage + $offsetButtons + 1 <= $this->pageCount) {
            $items[] = ['dots', 0];
            $items[] = ['next', $this->activePage + 1, $this->nextPageLabel];
            if ($this->lastPageLabel !== false) {
                $items[] = ['last', $this->pageCount, $this->lastPageLabel];
            }
        }
        return $items;
    }

    /**
     * Выводит кнопку (ссылку) пейджера.
     * 
     * @param string $type Тип кнопки: 'first', 'prev', 'next', 'last', 'dots', 'item', 'active'.
     * @param int $page Индекс страницы.
     * @param string $label Текст метки кнопки (по умолчанию `null`).
     * 
     * @return string Возвращает кнопку пейджера.
     */
    public function renderButton(string $type, int $page, ?string $label = null): string
    {
        if ($type === 'dots') {
            return $this->dotsItemTpl;
        }

        if ($type === 'active') {
            $url = '#';
            $template = $this->activeItemTpl;

        } else {
            if (isset($this->dataProvider))
                $url = $this->dataProvider->getUrlParams($page);
            else
                $url = '#';
            $template = $this->itemTpl;
        }

        $link = Html::a($label ?: $page, $url, $this->linkOptions);
        return strtr($template, ['{link}' => $link]);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): mixed
    {
        $buttons = [];

        $items = $this->calculate();
        foreach ($items as $item) {
            $buttons[] = $this->renderButton($item[0], $item[1], $item[2] ?? null);
        }
        $content = Html::tag($this->tag, implode($this->separator, $buttons), $this->options) . "\n";

        if (isset($this->navTag)) {
            return Html::tag($this->navTag, $content, $this->navOptions);
        }
        return $content;
    }
}
