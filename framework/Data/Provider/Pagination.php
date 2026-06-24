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

/**
 * Класс Pagination (пагинация) представляет информацию о разбивке на страницы элементов 
 * данных.
 * 
 * Используется тогда, когда необходимо отобразить данные с разбивкой на несколько страниц.
 * Для этого применяются такие свойства, как:
 * - количества выводимых элементов на странице {@see Pagination::$limit};
 * - номер текущей страницы {@see Pagination::$page}.
 * Эти свойства могут передаваться виджету пагинации {@see \Ge\View\WidgetPagination} для 
 * вывода кнопок пагинации или ссылок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Provider
 * @since 2.0
 */
class Pagination extends BaseObject
{
    /**
     * Количества выводимых элементов.
     * 
     * Значение определяется с помощью {@see Pagination:defineLimit()}, но есть возможность 
     * указать в конфигурации конструктора класса.
     *
     * @var int
     */
    public int $limit;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий количество элементов на странице.
     * 
     * Параметр передаётся с помощью метода GET и определяется {@see Pagination::defineLimit()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see Pagination::$defaultLimit}.
     * 
     * @var string|false
     */
    public string|false $limitParam = 'limit';

    /**
     * Фильтр количества элементов.
     * 
     * Применяется для фильтрации количества элементов передаваемых в HTTP-запросе.
     * Если фильтр не указан, будет использоваться любое количества элементов в HTTP-запросе.
     * 
     * Пример фильтра: `[10, 20, 30, ...]`.
     * 
     * @var array
     */
    public array $limitFilter = [];

    /**
     * Значение количества выводимых элементов по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseProvider::$limitParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var int
     */
    public int $defaultLimit = 10;

    /**
     * Максимальное допустимое количество выводимых элементов.
     * 
     * Применяется для проверки в том случаи, если значение отличное от `null` и не 
     * установлен фильтр количества элементов {@see BaseProvider::$limitFilter}.
     * 
     * @var int|null
     */
    public ?int $maxLimit = null;

    /**
     * Номер страницы.
     * 
     * Значение определяется с помощью {@see Pagination:definePage()}, но есть возможность 
     * указать в конфигурации конструктора класса.
     *
     * @var int
     */
    public int $page;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий номер страницы.
     * 
     * Параметр передаётся с помощью метода GET и определяется {@see Pagination::definePage()}.
     * Если значение параметра `false`, тогда значение будет '1'.
     * 
     * @var string|false
     */
    public string|false $pageParam = 'page';

    /**
     * Общее количество элементов.
     * 
     * @var int|null
     */
    public ?int $totalCount = null;

    /**
     * Определяет, что парамтер $limit получен из HTTP-запроса.
     * 
     * @see Pagination::defineLimit()
     * 
     * @var bool
     */
    protected bool $hasLimit = false;

    /**
     * Определяет, что парамтер $page получен из HTTP-запроса.
     * 
     * @see Pagination::definePage()
     * 
     * @var bool
     */
    protected bool $hasPage = false;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        $this->limit = $this->defineLimit();
        $this->page = $this->definePage();
    }

    /**
     * Определяет количество элементов на странице.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see Pagination::$limit};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see Pagination::$defaultLimit};
     * - если значение параметра не входит в указанный фильтр или является не допустимым, 
     * тогда возвратит {@see Pagination::$defaultLimit}.
     * 
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function defineLimit(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->limit)) {
            return $this->limit;
        }
        // если запрещено получать значение из HTTP-запроса
        if ($this->limitParam === false) {
            return  $this->defaultLimit;
        }

        $limit = Ge::$app->request->getQuery($this->limitParam, null);
        if ($limit === null) {
            return $this->defaultLimit;
        }
        // параметр был получен из запроса
        $this->hasLimit = true;

        $limit = (int) $limit;
        if ($limit <= 1) {
            return $this->defaultLimit;
        }

        if ($this->limitFilter) {
            if (!in_array($limit, $this->limitFilter)) {
                return $this->defaultLimit;
            }
        } else {
            if ($this->maxLimit && $limit > $this->maxLimit) {
                return $this->defaultLimit;
            }
        }
        return $limit;
    }

    /**
     * Определяет номер текущей страницы.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see Pagination::$page};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит '1';
     * - если значение параметра является не допустимым, тогда возвратит '1'.
     * 
     * @see Pagination::$pageParam
     * 
     * @return int
     */
    public function definePage(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->page)) {
            return $this->page;
        }
        // если запрещено получать значение из HTTP-запроса
        if ($this->pageParam === false) {
            return  1;
        }

        $page = Ge::$app->request->getQuery($this->pageParam, null);
        if ($page === null) {
            return 1;
        }
        // параметр был получен из запроса
        $this->hasPage = true;

        $page = (int) $page;
        // если значение превышает допустимое количество
        if ($this->totalCount !== null) {
            if ($page > $this->getPageCount())
                return 1;
        }
        return $page < 1 ? 1 : $page;
    }

    /**
     * Возврашает количество элементов, которые необходимо пропустить перед выводом.
     * 
     * @see Pagination::$page
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    /**
     * Возврашает количество элементов выводимых на странице.
     * 
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Возвращает количество элементов на странице.
     * 
     * @param null|int $totalCount Общее количество элементов. Если значение `null`,
     *     будет использовано {@see Pagination::$totalCount} (по умолчанию `null`).
     * 
     * @see Pagination::$totalCount
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function getPageCount(?int $totalCount = null): int
    {
        if ($totalCount === null) {
            $totalCount = (int) $this->totalCount;
        }
        $totalCount = $totalCount < 0 ? 0 : (int) $totalCount;

        // если выводятся все элементы
        if ($this->limit === 0) {
            return $totalCount > 0 ? 1 : 0;
        }
        return intdiv($totalCount + $this->limit - 1, $this->limit);
    }

    /**
     * Возвращает параметры запроса (для поставщика данных) полученные из URL-адреса.
     * 
     * @see BaseProvider::getQueryParams()
     * 
     * @param null|int $page Количество элементов на странице. Если значение указано, 
     *     то оно обязательно будет в возвращаемом параметре (по умолчанию `null`).
     * @param null|int $limit Количество элементов выводимых на странице. Если значение 
     *     указано, то оно обязательно будет в возвращаемом параметре (по умолчанию `null`).
     * 
     * @return array Возвращаемые параметры могут иметь вид: `['page' => 1, 'limit' => 20]`.
     */
    public function getQueryParams(?int $page = null, ?int $limit = null): array
    {
        $params = [];
        if ($page)
            $params[$this->pageParam] = $page;
        else
        if ($this->hasPage)
            $params[$this->pageParam] = $this->page;

        if ($limit)
            $params[$this->limitParam] = $limit;
        else
        if ($this->hasLimit)
            $params[$this->limitParam] = $this->limit;
        return $params;
    }
}
