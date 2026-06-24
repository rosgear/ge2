<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Widget;

use Closure;
use Ge;
use Ge\View\Widget;
use Ge\Helper\Html;
use Ge\Data\Provider\BaseProvider;
use Ge\Exception\InvalidConfigException;
use Ge\Exception\BadMethodCallException;

/**
 * Виджет ListView предназначен для отображения данных из поставщика данных.
 * 
 * Т.к. поставщик данных включает в себя сортировку, фильтрацию и разбивку на страницы, 
 * то его использование удобно для отображения информации пользователю и создания 
 * интерфейса управления данными.
 * 
 * Пример:
 * ```php
 * $select = new Select('{{article}}');
 * $select->quantifier(new \Ge\Db\Sql\Expression('SQL_CALC_FOUND_ROWS'));
 * $select->columns(['*']);
 * 
 * $provider = new QueryProvider([
 *     'query' => $select,
 *     'sort' => [
 *         'default' => 'date,a;header,d',
 *         'filter'  => ['date' => 'publish_date', 'header' => 'header']
 *     ],
 *     'pagination' => [
 *         'limit' => 10
 *     ]
 * ]);
 * 
 * echo ListView::widget([
 *     'dataProvider' => $dataProvider,
 *     'layout'       => "{pager}\n{items}\n{pager}"
 * ]);
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class ListView extends Widget
{
    /**
     * Поставщик данных.
     * 
     * @var BaseProvider|array
     */
    public BaseProvider|array $dataProvider = [];

    /**
     * HTML-атрибуты тега для отображения содержимого списка.
     * 
     * По умолчанию тег "div".

     * @var array
     */
    public array $options = [];

    /**
     * Имя или файл шаблона представления для вывода элементов данных или обратный 
     * вызов (например, анонимная функция) для вывода каждого элемента данных. 
     * 
     * Если указано имя и файла представления, в представлении будут доступны следующие 
     * переменные:
     * - `$items`, список выводимых элементов;
     * - `$widget`, экземпляр виджета ListView.
     * 
     * Если это свойство указано как обратный вызов, оно должно иметь следующую сигнатуру:
     * ```php
     * function ($items, $widget)
     * ```
     * 
     * @var Closure|string
     */
    public Closure|string $itemsView = '';

    /**
     * Разделитель, который будет отображаться между двумя последовательными элементами 
     * списка.
     * 
     * @see ListView::renderItems()
     * 
     * @var string
     */
    public string $itemsSeparator = "\n";

    /**
     * Имя или файл шаблона представления  или обратный вызов (например, анонимная 
     * функция) для вывода элемента данных.
     * 
     * Если указано имя и файла представления, в представлении будут доступны следующие 
     * переменные:
     * - `$item`, значения элемента в виде пар "ключ - значение";
     * - `$index`, порядковый номер элемента в списке;
     * - `$widget`, экземпляр виджета ListView.
     * 
     * Если это свойство указано как обратный вызов, оно должно иметь следующую сигнатуру:
     * ```php
     * function ($item, $index)
     * ```
     * 
     * @see ListView::renderItem()
     * 
     * @var Closure|string
     */
    public Closure|string $itemView = '';

    /**
     * Имя анонимной функции, которая вызывается один раз до отображения элемента 
     * данных.
     * 
     * Если результатом будет значение `null`, то к отображению элемента ничего не 
     * добавится.
     * 
     * Имеет следующую сигнатуру:
     * ```php
     * function ($item, $index)
     * ```
     * 
     * @see ListView::renderBeforeItem()
     * 
     * @var Closure|null
     */
    public ?Closure $beforeItem = null;

    /**
     * Имя анонимной функции, которая вызывается один раз после отображения элемента 
     * данных.
     * 
     * Если результатом будет значение `null`, то к отображению элемента ничего не 
     * добавится.
     * 
     * Имеет следующую сигнатуру:
     * ```php
     * function ($item, $index)
     * ```
     * 
     * @see ListView::renderAfterItem()
     * 
     * @var Closure|null
     */
    public ?Closure $afterItem = null;

    /**
     * HTML-атрибуты тега для отображения элемента списка.
     * 
     * По умолчанию тег "div".

     * @var array
     */
    public array $itemOptions = [];

    /**
     * Макет, который определяет, как должны быть организованы различные части 
     * представления списка.
     * 
     * Части макета указываются свойством {@see ListView::$partials} и соответствуют 
     * следующим директивам:
     * - `{items}`, список элементов данных  {@see renderItems()}
     * - `{pager}`
     * 
     * @var string
     */
    public string $layout = "{pager}\n{items}";

    /**
     * Имена частей, которые используются в макете виджета.
     * 
     * Имена частей должны быть указаны без символов '{}', т.к. эти символы используются 
     * в макете. Например 'pager' соответствует '{pager}' в мекете.
     * 
     * @var array
     */
    public array $partials = ['pager', 'items'];

    /**
     * Содержимое, которое будет отображаться, когда поставщик не имеет данных.
     * 
     * Если свойство имеет значение `false`, выводиться ничего не будет.
     * Если свойство имеет значение `null`, то будет выводиться текст "Результаты не найдены".
     * 
     * @var false|string|null
     */
    public false|string|null $emptyText = null;

    /**
     * HTML-атрибуты тега для отображения текста emptyText в списке.
     * 
     * По умолчанию тег "div".

     * @var array
     */
    public array $emptyTextOptions = ['class' => 'empty-text'];

    /**
     * Показывать пустой список, если поставщик не имеет данных.
     * 
     * Если значение `false`, будет отображаться {@see ListView::$emptyText}.
     * 
     * @var bool
     */
    public bool $showEmptyText = false;

    /**
     * Параметры виджета разбивки на страницы элементов данных.
     * 
     * По умолчанию используется виджет {@see Pager}. Возможно использовать другой 
     * класс виджета, указав параметр "class".
     * 
     * @var array
     */
    public array $pager;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        
        $this->initDataProvider();
    }

    /**
     * Инициализация поставщика данных.
     * 
     * @return void
     */
    public function initDataProvider(): void
    {
        if (empty($this->dataProvider)) {
            throw new InvalidConfigException('The "dataProvider" must be setted.');
        }

        if (is_array($this->dataProvider)) {
            $this->dataProvider = Ge::createObject($this->dataProvider);
        }

        if (!$this->dataProvider instanceof BaseProvider) {
            // "dataProvider" должен быть унаследован от "BaseProvider"
            throw new InvalidConfigException('The "dataProvider" must be inherited from "BaseProvider".');
        }
    }

    /**
     * Возвращает представление содержимого, указывающее, что в списка нет данных.
     * 
     * @see ListView::$emptyText
     * 
     * @return string
     */
    public function renderEmptyText(): string
    {
        return $this->emptyText ? Html::tag('div', $this->emptyText, $this->emptyTextOptions) : '';
    }

    /**
     * Возвращает представление макета шаблона с выводом его частей.
     * 
     * Имена частей шаблона должны быть указаны в {@see ListView::$partials}.
     * 
     * @see ListView::$layout
     * 
     * @return string
     */
    public function renderPartials(): string
    {
        if (empty($this->layout)) return '';

        $partials = [];
        foreach ($this->partials as $name) {
            $method = 'render' . $name;
            if (!method_exists($this, $method)) {
                throw new BadMethodCallException('Method "' . $method . '" does not exist.');
            }
            $pname = '{' . $name . '}';
            // для предотвращения повторного вызова
            if (!isset($partials[$pname])) {
                $partials[$pname] = $this->{$method}();
            }
        }
        return strtr($this->layout, $partials);
    }

    /**
     * Возвращает представление элементов данных.
     * 
     * @return string
     */
    public function renderItems(): string
    {
        $rows  = [];
        $items = $this->dataProvider->getItems();
        foreach ($items as $index => $item) {
            $before = $this->renderBeforeItem($item, $index);
            if ($before !== null) {
                $rows[] = $before;
            }

            $rows[] = $this->renderItem($item, $index);

            $after = $this->renderAfterItem($item, $index);
            if ($after !== null) {
                $rows[] = $after;
            }
        }
        return implode($this->itemsSeparator, $rows);
    }

    /**
     * Вызывается перед получением представления элемента списка.
     * 
     * Если {@see ListView::$beforeItem} на анонимная функция, то результатом будет `null`.
     *
     * @param array $item Значения элемента в виде пар "ключ - значение".
     * @param int $index Порядковый номер элемента в списке.
     * 
     * @return string|null Возвращает значение `null`, если результатом будет не 
     *     анонимная функция.
     */
    protected function renderBeforeItem(array $item, int $index): ?string
    {
        if ($this->beforeItem instanceof Closure) {
            return call_user_func($this->beforeItem, $item, $index, $this);
        }
        return null;
    }

    /**
     * Вызывается после получения представления элемента списка.
     * 
     * Если {@see ListView::$afterItem} на анонимная функция, то результатом будет `null`.
     *
     * @param array $item Значения элемента в виде пар "ключ - значение".
     * @param int $index Порядковый номер элемента в списке.
     * 
     * @return string|null Возвращает значение `null`, если результатом будет не 
     *     анонимная функция.
     */
    protected function renderAfterItem(array $item, int $index): ?string
    {
        if ($this->afterItem instanceof Closure) {
            return call_user_func($this->afterItem, $item, $index, $this);
        }
        return null;
    }

    /**
     * Возвращает представление элемента списка.
     * 
     * @param array $item Значения элемента в виде пар "ключ - значение".
     * @param int $index Порядковый номер элемента в списке.
     * 
     * @return string
     */
    protected function renderItem(array $item, int $index): string
    {
        if ($this->itemView) {
            if (is_string($this->itemView))
                $content = $this->render(
                    $this->itemView, ['item' => $item, 'index' => $index, 'widget' => $this]
                );
            else
            if (is_callable($this->itemView))
                $content = call_user_func($this->itemView, $item, $index);
            else
                $content = $index;
        } else
            $content = $index;
        return Html::tag('div', $content, $this->itemOptions);
    }

    /**
     * Возвращает представление виджета разбивки на страницы элементов данных.
     * 
     * @see ListView::$pager
     * 
     * @return string
     */
    public function renderPager(): string
    {
        /** @var \Ge\Data\Provider\Pagination $pagination */
        $pagination = $this->dataProvider->pagination;
        /** @var int $totalCount Общее количество элементов */
        $totalCount = $this->dataProvider->getTotalCount();

        if (!isset($this->pager)) {
            $this->pager = [];
        }
        $pager = $this->pager;
        $pager['activePage'] = $pagination->page;
        $pager['pageCount']  = $pagination->getPageCount($totalCount);
        $pager['dataProvider'] = $this->dataProvider;

        $widgetClass = $pager['class'] ?? '\Ge\View\Widget\Pager';
    
        return $widgetClass::widget($pager);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): mixed
    {
        $items = $this->dataProvider->getItems();

        if (is_string($this->itemsView))
            return $this->render(
                $this->itemsView, ['items' => $items, 'widget' => $this]
            );
        else
        if (is_callable($this->itemsView))
            $content = call_user_func($this->itemsView, $items, $this);
        else {
            if (sizeof($items) > 0)
                $content = $this->renderPartials();
            else
                $content = $this->showEmptyText ? $this->renderEmptyText() : '';
        }
        return Html::tag('div', $content, $this->options);
    }
}
