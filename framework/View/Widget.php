<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

use Ge;
use Ge\View\View;
use Ge\Exception\NotDefinedException;

/**
 * Класс виджета для формирования элементов интерфейса в представлении.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Widget
 * @since 2.0
 */
class Widget extends BaseWidget
{
    /**
     * Имя или файл шаблона представления.
     * 
     * @var string
     */
    public string $viewFile;

    /**
     * Настройки представления виджета.
     * 
     * - useTheme  Выполнять поиск файла шаблона представления в каталоге темы.
     * - useLocalize Выполнять поиск файла шаблона представления с локализацией.
     * - renderer Имя визуализатора представления.
     * 
     * @var array
     */
    public array $viewOptions = [];

    /**
     * Счётчик для получения уникального идентификатора виджета.
     * 
     * @see Widget::getId()
     * 
     * @var int
     */
    public static int $autoCounter = 0;

    /**
     * Префикс для получения уникального идентификатора виджета.
     * 
     * @see Widget::getId()
     * 
     * @var string
     */
    public static string $autoPrefix = 'widget';

    /**
     * @var array
     */
    public static array $widgets = [];

    /**
     * Модель представления.
     * 
     * @see Widget::getView()
     * 
     * @var View
     */
    protected View $view;

    /**
     * Уникальный идентификатор виджета.
     * 
     * @see Widget::getId()
     * 
     * @var string
     */
    public string $id;

    /**
     * @see Widget::widget()
     * 
     * @var Widget|null
     */
    protected static ?Widget $instance = null;

    /**
     * Создание и возвращение контента виджета.
     * 
     * @param array $params Параметры виджета.
     * 
     * @return string
     */
    public static function widget(array $params = []): string
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $widget = static::$instance = new static($params);
            $result = '';
            if ($widget->beforeRun()) {
                $result = $widget->run();
                $result = $widget->afterRun($result);
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
     * Возвращает указатель на последний виджет полученный с помощью этого класса.
     * 
     * @return Widget|null
     */
    public static function getWidget(): ?Widget
    {
        return static::$instance;
    }

    /**
     * Начинает виджет.
     * 
     * Создаёт экземпляр класса (виджета) с указанными параметрами конфигурации виджета.
     * Т.к. виджет может использовать буферизацию вывода, необходимо использовать метод
     * {@see Widget::end()} для избежания нарушения вложенности буферов вывода.
     * 
     * @see Widget::end()
     * 
     * @param array $params Параметры конфигурации виджета в виде массива пар "имя - значение" 
     *     (по умолчачнию `[]`).
     * 
     * @return Widget Возвращает указатель на новый виджет.
     */
    public static function begin(array $params = []): Widget
    {
        $widget = static::$instance = new static($params);
        static::$widgets[] = $widget;
        return $widget;
    }

    /**
     * Заканчивает виджет.
     * 
     * Результат визуализации виджета отображается напрямую (без буфиризации).
     * 
     * @see Widget::begin()
     * 
     * @return Widget Возвращает указатель на виджет совершивший окончание.
     * 
     * @throws NotDefinedException Несоответствие начала и конца виджета.
     */
    public static function end(): Widget
    {
        if (static::$widgets) {
            // последний виджет
            $widget = array_pop(static::$widgets);

            if (get_class($widget) === get_called_class()) {
                if ($widget->beforeRun()) {
                    $result = $widget->run();
                    $result = $widget->afterRun($result);
                    echo $result;
                }
                return $widget;
            }
            throw new NotDefinedException(
                sprintf('Not defined "end()" of "%s" for "%s".', get_class($widget), get_called_class())
            );
        }
        throw new NotDefinedException(
            sprintf('Not defined "begin()" for "%s".', get_called_class())
        );
    }

    /**
     * Возвращает представление.
     * 
     * @param array $config Параметры конфигурации виджета.
     * 
     * @return View
     */
    public function getView(array $config = []): View
    {
        if (!isset($this->view)) {
            $this->view = Ge::$app->getView();
            // изменить настройки представляния для текущего видежта
            if ($this->viewOptions) {
                $this->view->configure($this->viewOptions);
            }
        }
        return $this->view;
    }

    /**
     * Возвращает идентификатор виджета.
     * 
     * @return string
     */
    public function getId(): string
    {
        if (!isset($this->id)) {
            $this->id = static::$autoPrefix . static::$autoCounter++;
        }
        return $this->id;
    }

    /**
     * Устанавливает идентификатор виджету.
     * 
     * @param string $id Идентификатор.
     * 
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Возвращает визуализацию содержимого виджета.
     * 
     * @see View::renderFile()
     * 
     * @param string $viewFile Имя шаблона или файл шаблона представления.
     * @param array $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления (по умолчанию `[]`).
     * 
     * @return string
     */
    public function render(string $viewFile, array $params = [])
    {
        return $this->getView()->render($viewFile, $params);
    }

    /**
     * Выводит содержимое шаблона виджета.
     * 
     * @see View::renderFile()
     * 
     * @param string $filename Имя файл (включает путь) шаблона.
     * @param array|null $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления. Если значение `null`, будет использоваться 
     *     {@see BaseView::$params} (по умолчанию null).
     * 
     * @return string
     */
    public function renderFile(string $filename, ?array $params = null): mixed
    {
        return $this->getView()->renderFile($filename, $params);
    }
}
