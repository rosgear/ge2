<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Uploader;

/**
 * Класс шаблона локального пути (папки).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Uploader
 * @since 2.0
 */
class PathTemplate
{
    /**
     * Шаблон пути.
     * 
     * Например: '{year}/{month}/{day}/{ID}'.
     * 
     * @var string
     */
    public string $template = '';

    /**
     * Параметры передаваемые в шаблон в виде пар "ключ - значение".
     * 
     * @var array
     */
    public array $params = [];

    /**
     * Конструктор класса.
     * 
     * @param string $template Шаблон пути, например '{year}/{month}/{day}/{id}'.
     * @param array $params Параметры передаваемые в шаблон в виде пар "ключ - значение".
     */
    public function __construct(string $template, array $params = [])
    {
        $this->template = $template;
        $this->params   = $params;
    }

    /**
     * Возвращает параметры по умолчанию подставляемые в шаблон.
     * 
     * @return array
     */
    protected function getDefaultParams(): array
    {
        return [
            'Year'  => gmdate('Y'), // номер года, 2 цифры
            'year'  => gmdate('y'), // полное числовое представление года, не менее 4 цифр
            'Month' => gmdate('n'), // порядковый номер месяца без ведущего нуля (например, от 1 до 12 )
            'month' => gmdate('m'), // порядковый номер месяца с ведущим нулём (например, от 01 до 12 )
            'Day'   => gmdate('D'), // день месяца без ведущего нуля (например, от 1 до 31 )
            'day'   => gmdate('d'), // день месяца, 2 цифры с ведущим нулём (например, от 01 до 31 )
            'id'    => '0', // идентификатор объекта, которому принадлежит файл
        ];
    }

    /**
     * Возвращает параметры подставляемые в шаблон.
     * 
     * @return array
     */
    protected function getReplaceParams(): array
    {
        $result = [];
        $params = array_merge($this->getDefaultParams(), $this->params);
        foreach ($params as $name => $value) {
            $result['{' . $name . '}'] = $value;
        }
        return $result;
    }

    /**
     * Возвращает путь (шаблон с подставленными в него параметрами).
     * 
     * @return string
     */
    public function render(): string
    {
        if (empty($this->template)) return '';

        return strtr(trim($this->template, '\/'), $this->getReplaceParams());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
