<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Router\Matcher;

use Ge\Stdlib\Collection;

/**
 * Класс результата сопоставления маршрута компонента (модуля (module), расширения 
 * модуля (extension)).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Router\Matcher
 * @since 2.0
 */
class RouteMatch extends Collection
{
    /**
     * Идентификатор компонента, чей маршрут был определён.
     *
     * @var string
     */
    public string $id = '';

    /**
     * Тип сопоставления маршрута (имя сопостовителя).
     * 
     * @see \Ge\Router\Router::$matcher
     * 
     * @var string
     */
    public string $type = '';

    /**
     * Параметры сопоставления маршрута.
     * 
     * Эти параметры указываются компонентом (в параметрах установки компонента) для 
     * определения его маршрутизации
     *
     * @var array
     */
    public array $options = [];

    /**
     * Конструктор класса.
     * 
     * @param string $id Идентификатор компонента, чей маршрут был определён.
     * @param string $type Тип сопоставления маршрута (имя сопостовителя).
     * @param array $options Параметры маршрута.
     * @param array $result Результат сопоставления.
     * 
     * @return void
     */
    public function __construct(string $id, string $type, array $options = [], array $result = [])
    {
        $this->id = $id;
        $this->type = $type;
        $this->options = $options;
        $this->container = $result;
    }

    /**
     * Проверяет текущий тип сопоставления маршрута.
     *
     * @param string $type Тип сопоставления маршрута.
     * 
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Возвращает значение параметра из результата сопоставления.
     * 
     * @param string $name Параметр.
     * @param mixed $default Значение по умолчанию.
     * 
     * @return mixed Если значение `null`, параметра не найден.
     */
    public function get(mixed $name, mixed $default = null): mixed
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        } else
        /* т.к. метод используется для получения параметров после 
         * разбора маршрута, а некоторые параметры после разбора могут быть не 
         * доступны (для каскадного сопоставления они будут в "params"), то проверку 
         * делаем и в "params".
         * Например: `$name` будет доступен для одного вида сопоставления в "parameters", а для 
         * другого в "params".
         */
        if (isset($this->container['params'][$name])) {
            return $this->container['params'][$name];
        }
        return $default;
    }
}
