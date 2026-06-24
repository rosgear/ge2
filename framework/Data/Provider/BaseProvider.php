<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data\Provider;

use Ge;
use Ge\Stdlib\BaseObject;
use Ge\Exception\InvalidArgumentException;

/**
 * Базовый класс поставщика данных.
 * 
 * Поставщик данных предоставляет возможность выполнять разбивку элементов данных 
 * на страницы и выполнять их сортировку.
 * 
 * Используется с такими виджетами как: Pager (управление страницами элементов), 
 * ListView (список элементов данных) и др. виджеты предназначенные для управления 
 * данными. Таким образом, класс инкапсулирует пагинацию {@see BaseProvider::$pagination} и 
 * сортировку данных {@see BaseProvider::$sort}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Provider
 * @since 2.0
 */
class BaseProvider extends BaseObject
{
    /**
     * Идентификатор является уникальным и служит для отличия одного поставщика 
     * данных от других.
     * 
     * Генерируется автоматически, добавляя к указанным символам номер счётчика 
     * {@see BaseProvider::$counter}, например: 'p1', 'p2' и т.д.
     * 
     * Идентификатор используется в качестве приставок к именам параметров в HTTP-запросе 
     * для разбивки элементов и их сортировки.
     * 
     * @see BaseProvider::generateId()
     * 
     * @var string
     */
    public string $id;

    /**
     * Сортировщик или параметры конфигурации сортировщика элементов данных.
     * 
     * Если указаны параметры, то будет создан объект сортировки в {@see BaseProvider::configure()}.
     * Если значение `false`, сортировка будет не доступна.
     * 
     * @see BaseProvider::setSort()
     *
     * @var Sort|array|false|null
     */
    public Sort|array|false|null $sort = null;

    /**
     * Пагинация или параметры конфигурации пагинации элементов данных.
     * 
     * Если указаны параметры, то будет создан объект пагинации в {@see BaseProvider::configure()}.
     * Если значение `false`, разбивка элементов на страницы будет не доступна.
     * 
     * @see BaseProvider::setPage()
     *
     * @var Pagination|array|false|null
     */
    public Pagination|array|false|null $pagination = null;

    /**
     * Маршрут действия контроллера для отображения содержимого.
     * 
     * Используется для получения ссылок при разбивке элементов на страницы или их 
     * сортировке.
     * 
     * Если не установлен, используется текущий маршрут ('*') запроса.
     * В маршруте можно указать символы:
     * - '*', будет подставлен текущий URI, независимо от правил формирования 
     * URL-адреса (пример: '/foo/bar', '/foo/bar.html');
     * - '', в URL-адресе будет отсутстовать URL-путь (пример: 'http://domain.com/news/?page=1' => '?page=1').
     * 
     * @see BaseProvider::getUrlParams()
     * 
     * @var string|null
     */
    public ?string $route = null;

    /**
     * Параметры (компоненты), предназначенные для формирования URL-адреса ссылок 
     * при разбивке элементов на страницы или их сортировке.
     * 
     * Пример: `['local' => true, 'path' => '/foo/bar', '#' => 'anchor', ...]`.
     * 
     * @see \Ge\Helper\Url::build()
     * @see BaseProvider::getUrlParams()
     * 
     * @var array
     */
    public array $urlParams = [];

    /**
     * Элементы данных в виде массивов атрибутов пар "ключ - значение".
     * 
     * @see BaseProvider::prepare()
     *
     * @var array
     */
    protected array $items;

    /**
     * Общее количество элементов данных.
     * 
     * @see BaseProvider::getTotalCount()
     * 
     * @var int
     */
    protected int $totalCount;

    /**
     * Общий счётчик для всех поставщиков данных используемых на странице.
     * 
     * Предназначен для генерации уникального идентификатора поставщика данных.
     * 
     * @var int
     */
    protected static $counter = 0;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!isset($this->id)) {
            $this->id = $this->generateId();
        }

        if (is_array($this->pagination)) {
            $this->setPagination($this->pagination);
        }
        if (is_array($this->sort)) {
            $this->setSort($this->sort);
        }
    }

    /**
     * Возвращает уникальный идентификатор поставщика данных.
     * 
     * @see BaseProvider::$counter
     * 
     * @return string
     */
    public function generateId(): string
    {
        return 'p' . (++self::$counter);
    }

    /**
     * Возвращает объект сортировки, используемый поставщиком данных.
     * 
     * @see BaseProvider::$sort
     * 
     * @return Sort|false Возвращает значение `false`, если сортировка не доступна.
     */
    public function getSort(): Sort|false
    {
        if ($this->sort === null) {
            $this->setSort([]);
        }
        return $this->sort;
    }

    /**
     * Устанавливает или создаёт объект сортировки.
     * 
     * @see BaseProvider::$sort
     * 
     * @param Sort|array|false $value Объект сортировки или параметры конфигурации для 
     *     создания объекта. Если значение `false`, сортировка будет не доступна.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException Неправильно указано значение сортировки.
     */
    public function setSort(Sort|array|false $value): void
    {
        if (is_array($value)) {
            // попытка установить имя параметра в HTTP-запросе с 
            // уникальным идентификатором
            if (!isset($value['param']) && !empty($this->id)) {
                $value['param'] = $this->id . '-sort';
            }
            if (isset($value['class']))
                $this->sort = Ge::createObject($value);
            else
                $this->sort = new Sort($value);
        } else
        if ($value instanceof Sort || $value === false)
            $this->sort = $value;
        else
            // Для установки сортировки, необходимо чтобы значение имело массив 
            // параметров конфигурации или значение `false`
            throw new InvalidArgumentException(
                'To set sorting, it is necessary that the value has an array of configuration parameters or false.'
            );
    }

    /**
     * Возвращает объект пагинации (разбивки элементов на страницы), используемый 
     * поставщиком данных.
     * 
     * @see BaseProvider::$pagination
     * 
     * @return Pagination|false Возвращает значение `false`, если пагинация не доступна.
     */
    public function getPagination(): Pagination|false
    {
        if ($this->pagination === null) {
            $this->setPagination([]);
        }
        return $this->pagination;
    }

    /**
     * Устанавливает или создаёт объект пагинации (разбивки элементов на страницы).
     * 
     * @see BaseProvider::$pagination
     * 
     * @param Pagination|array|false $value Объект пагинации или параметры конфигурации для 
     *     создания объекта. Если значение `false`, пагинация будет не доступна.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException Неправильно указано значение пагинации.
     */
    public function setPagination(Pagination|array|false $value): void
    {
        if (is_array($value)) {
            // попытка установить имя параметра в HTTP-запросе с 
            // уникальным идентификатором
            if ($this->id) {
                if (!isset($value['limitParam']))
                    $value['limitParam'] = $this->id . '-limit';
                else
                    $value['limitParam'] = $this->id . '-' . $value['limitParam'];
                if (!isset($value['pageParam']))
                    $value['pageParam'] = $this->id . '-page';
                else
                    $value['pageParam'] = $this->id . '-' . $value['pageParam'];
            }
            if (isset($value['class']))
                $this->pagination = Ge::createObject($value);
            else
                $this->pagination = new Pagination($value);
        } else
        if ($value instanceof Pagination || $value === false)
            $this->pagination = $value;
        else
            // Для установки нумерация страниц, необходимо чтобы значение имело массив 
            // параметров конфигурации или значение `false`
            throw new InvalidArgumentException(
                'To set pagination, it is necessary that the value has an array of configuration parameters or false.'
            );
    }

    /**
     * Возвращает общее количество элементов.
     * 
     * Перед возвращением выполняет подготовку {@see BaseProvider::prepareTotalCount()}.
     * 
     * @return int
     */
    public function getTotalCount(): int
    {
        if (!isset($this->totalCount)) {
            if ($this->pagination !== null && $this->pagination->totalCount !== null)
                $this->totalCount = $this->pagination->totalCount;
            else 
                $this->totalCount = $this->prepareTotalCount();
        }
        return $this->totalCount;
    }

    /**
     * Выполняет подготовку к получению общего количества элементов.
     * 
     * Здесь реализуется сам процесс получения общего количества элементов.
     * Если невозможно получить, тогда значение - 0.
     * 
     * @return int
     */
    public function prepareTotalCount(): int
    {
        return 0;
    }

    /**
     * Подготавливает элементы данных для возврата.
     * 
     * Здесь реализуется процесс обработки каждого элемента данных.
     * 
     * @return array
     */
    public function prepareItems(): array
    {
        return [];
    }

    /**
     * Выполняет подготовку для возращения элементов данных.
     * 
     * @see BaseProvider::prepareItems()
     * 
     * @return void
     */
    public function prepare(): void
    {
        $this->items = $this->prepareItems();
    }

    /**
     * Возвращает элементы данных для текущей страницы.
     * 
     * @see BaseProvider::prepare()
     * 
     * @return array
     */
    public function getItems(): array
    {
        if (!isset($this->items)) {
            $this->prepare();
        }
        return $this->items;
    }

    /**
     * Возвращает параметры запроса (к поставщику данных) полученные из URL-адреса.
     * 
     * @param null|int $page Порядковый номер страницы.
     * @param null|int $limit Количество элементов на странице.
     * @param null|string $sort Сортировка элементов.
     * 
     * @return array Возвращаемые параметры могут иметь вид:
     *     `['page' => 1, 'limit' => 20, 'sort' => 'name,d']`.
     */
    public function getQueryParams(?int $page = null, ?int $limit = null, ?string $sort = null): array
    {
        $params = [];
        if ($this->pagination !== null) {
            $params = $this->pagination->getQueryParams($page, $limit);
        }

        if ($this->sort !== null) {
            $sortParams = $this->sort->getQueryParams($sort);
            $params = $params ? array_merge($params, $sortParams) : $sortParams;
        }
        return $params;
    }

    /**
     * @var array
     */
    protected $_queryParams;

    /**
     * Возвращает параметры для создания URL-адреса с указанием маршрута и параметрами 
     * запроса.
     * 
     * @see \Ge\Url\UrlManager::createUrl()
     * 
     * @param int $page Порядковый номер страницы.
     * @param array $params Компоненты URL {@see \Ge\Url\UrlManager::buildUrl()} если 
     *     они не указаны, используется {@see BaseProvider::$urlParams}.
     * 
     * @return array
     */
    public function getUrlParams(int $page, array $params = []): array
    {
        if ($this->_queryParams === null) {
            $this->_queryParams = $this->getQueryParams();
        }

        if (empty($params)) {
            $params = $this->urlParams;
        }

        // если не установлен URL-путь (path)
        if (!isset($params[0])) {
            if ($this->route === null) {
                $params[0] = '*';
            } else
            if ($this->route === '') {
                $params[0] = '';
                $params['local'] = true;
                
            } else
                $params[0] = $this->route;
        }
        // параметры запроса в URL
        $params['?'] = $this->_queryParams;

        if ($this->pagination !== null) {
            $params['?'][$this->pagination->pageParam] = $page;
        }
        return $params;
    }
}
