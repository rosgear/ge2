<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

use Ge\Stdlib\BaseObject;

/**
 * Базовый класс виджета для формирования элементов интерфейса в представлении.
 * 
 * Базовый класс виджета имеет все методы и свойства необходимые для вызова его из 
 * {@see \Ge\View\BaseView::widget()} Менеджером виджетов {@see \Ge\WidgetManager\WidgetManager}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class BaseWidget extends BaseObject
{
    /**
     * Файл последнего шаблона из которого был вызван виджет.
     * 
     * Устанавливается в конструкторе виджета параметром конфигурации.
     * 
     * @see \Ge\View\BaseView::widget()
     * 
     * @var string
     */
    public string $calledFromViewFile = '';

    /**
     * Изменять конфигурацию виджета, когда виджет уже создан.
     * 
     * Это свойство указывается владельцам (менеджеру) виджета, что виджет готов поменять
     * свою конфигурацию.
     * 
     * Пример:
     * ```php
     * $widget = new \Ge\Widget\FooBar(['width' => 500]);
     * if ($widget->useReconfigure) {
     *     $widget->configure(['width' => 700]);
     * }
     * ```
     * 
     * @var bool
     */
    public bool $useReconfigure = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * Инициализация виджета.
     * 
     * Этот метод вызывается в конце конструктора после инициализации вижета 
     * заданной конфигурацией.
     * 
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Выводит визуализацию содержимого виджета.
     * 
     * @return string
     */
    public function renderMe(): string
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $result = '';
            if ($this->beforeRun()) {
                $result = $this->run();
                $result = $this->afterRun($result);
            }
        } catch (\Exception $e) {
            // закрыть открытый буфер вывода, если он еще не был закрыт
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean() . $result;
    }

    /**
     * Событие перед запуском виджета. 
     * 
     * @return bool Возвращает значение `true`, если продолжить запуск виджета.
     */
    public function beforeRun(): bool
    {
        return true;
    }

    /**
     * Событие после запуска виджета. 
     * 
     * @param mixed $result Содержимое полученное после запуска виджета.
     * 
     * @return mixed
     */
    public function afterRun(mixed $result): mixed
    {
        return $result;
    }

    /**
     * Выполняет запуск виджета.
     * 
     * @return mixed
     */
    public function run(): mixed
    {
        return '';
    }
}
