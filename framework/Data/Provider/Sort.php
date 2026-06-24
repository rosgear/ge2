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
 * Класс Sort представляет информацию о порядке сортировки элементов данных на 
 * странице.
 * 
 * Используется тогда, когда необходимо данные отсортировать по одному или нескольким 
 * признакам. Для этого, класс формирует соответствующие гиперссылки.

 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Provider
 * @since 2.0
 */
class Sort extends BaseObject
{
    /**
     * Параметр передаваемый HTTP-запросом, указывающий направление и поле сортировки.
     * 
     * Параметр передаётся с помощью метода GET и определяется {@see Sort::define()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see Sort::$default}.
     * 
     * @var string|false
     */
    public string|false $param = 'sort';

    /**
     * Устанавливает возможность сортировки по нескольким полям.
     * 
     * @var bool
     */
    public bool $useMultiSort = false;

    /**
     * Фильтр сортировки.
     * 
     * Применяется для фильтрации полей сортировки передаваемых в HTTP-запросе.
     * Внимание: если фильтр не указан, будут использоваться любые поля.
     * 
     * Пример фильтра: `['name' => 'field_name', ...]`.
     * 
     * @var array
     */
    public array $filter = [];

    /**
     * Значение сортировки по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see Sort::$param} 
     * отсутствует в HTTP-запросе.
     * 
     * Например, 'name,a'.
     * 
     * @var string|null
     */
    public ?string $default = null;

    /**
     * Символ, используемый для разделения сортируемого поля и направления его 
     * сортировки.
     * 
     * Например, 'name,a' или 'name,d'.
     * 
     * @var string
     */
    public string $separator = ',';

    /**
     * Символ, используемый для разделения атрибутов сортировки.
     * 
     * Например, 'name1,a;name2,d' или 'name1;name2'.
     * 
     * @var string
     */
    public string $multiSeparator = ';';

    /**
     * Атрибуты сортирвки.
     * 
     * Значение определяется с помощью {@see Sort:define()}.
     *
     * @var array|null
     */
    protected ?array $sort = null;

    /**
     * Определяет, что парамтер $sort получен из HTTP-запроса.
     * 
     * @see Sort::define()
     * 
     * @var bool
     */
    protected bool $hasSort = false;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        $this->sort = $this->define();
    }

    /**
     * Выполняет нормализацию атрибутов сортировки.
     * 
     * Если установлен фильтр {@see Sort::$filter}, он будет применяться.
     * 
     * Пример:
     * - `['name']` => `['name', 'a']`;
     * - `['name', 'desc', 'some value', ...]` => `['name', 'a']`;
     * - `['name', 'd', 'some value', ...]` => `['name', 'd']`.
     * 
     * @param array $value
     * 
     * @return array Возвращает значение `null`, если атрибуты сортировки не прошли 
     *     нормализацию.
     */
    public function normalizeSort(array $value): ?array
    {
        if (empty($value[0])) return null;

        //  если установлен фильтр
        if ($this->filter && !isset($this->filter[$value[0]])) return null;

        // если неверно установлен атрибут
        if (isset($value[1])) {
            if ($value[1] !== 'a' && $value[1] !== 'd') $value[1] = 'a';
        } else
            $value[1] = 'a';
        return [$value[0], $value[1]];
    }

    /**
     * Преобразует указанное значение в массив атрибутов сортировки.
     *
     * @param mixed $param Значение параметра определяющие атрибуты сортировки.
     * 
     * @return array Возврашает атрибуты сортировки.
     */
    public function parseParam(mixed $param): array
    {
        if (is_array($param))
            return $param;
        else
        if (!is_string($param))
            return [];

        $result = [];
        // если используется сортировка по нескольким атрибутам
        if ($this->useMultiSort) {
            // 'name1,a;name2,d' => ['name1,a', 'name2,d']
            $items = explode($this->multiSeparator, $param);
            foreach ($items as $item) {
                if ($item === '') continue;

                $item = $this->normalizeSort(explode($this->separator, $item));
                if ($item !== null)
                    $result[] = $item;
            }
        } else {
            $item = $this->normalizeSort(explode($this->separator, $param));
            if ($item !== null)
                $result[] = $item;
        }
        return $result;
    }

    /**
     * Создаёт параметр сортировки для HTTP-запроса.
     * 
     * Пример:
     * - `[['name', 'a']]` => 'name';
     * - `[['name1', 'd'], ['name2', 'a']]` => 'name1,d;name2';
     * 
     * @see Sort::getQueryParams()
     * 
     * @param array $value Массив атрибутов сортировки елементов.
     * 
     * @return string Возвращает значение параметра сортировки.
     */
    public function buildParam(array $value): string
    {
        $result = [];
        foreach ($value as $sort) {
            if ($sort[1] === 'a')
                $result[] = $sort[0];
            else
                $result[] = $sort[0] . $this->separator . $sort[1];
        }
        return implode($this->multiSeparator, $result);
    }

    /**
     * Определяет атрибуты сортировки.
     * 
     * @see Sort::$param
     * 
     * @return array
     */
    public function define(): array
    {
        if ($this->param === false) {
            return  $this->parseParam($this->default);
        }

        $sort = Ge::$app->request->getQuery($this->param, null);
        if ($sort === null)
            $sort = $this->default;
        else
            $this->hasSort = true;
        return $this->parseParam($sort);
    }

    /**
     * Возвращает порядок сортировки.
     * 
     * @return array
     */
    public function getOrder(): array
    {
        if (empty($this->sort)) return [];

        $filter = &$this->filter;
        $order = [];
        foreach ($this->sort as $item) {
            $name = $item[0];
            if ($filter) {
                if (!isset($filter[$name])) continue;
                $name = $filter[$name];
            }
            $order[$name] = $item[1] === 'a' ? 'ASC' : 'DESC';
        }
        return $order;
    }

    /**
     * Возвращает параметры запроса (для поставщика данных) полученные из URL-адреса.
     * 
     * @see BaseProvider::getQueryParams()
     * 
     * @param null|string $sort Сортировка элементов, например: 'name,d;index,a'.
     *     Если значение указано, то оно обязательно будет в возвращаемом параметре 
     *     (по умолчанию `null`).
     * 
     * @return array Возвращаемые параметры могут иметь вид: `['sort' => 'name,d;index,a']`.
     */
    public function getQueryParams(?string $sort = null): array
    {
        $params = [];
        if ($sort)
            $params[$this->param] = $sort;
        else
        // если был запрос и сортировка имеет значение после нормализации
        if ($this->hasSort && $this->sort) {
            $params[$this->param] = is_array($this->sort) ? $this->buildParam($this->sort) : $this->sort; 
        }
        return $params;
    }
}
