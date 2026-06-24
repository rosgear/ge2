<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Debug;

use Ge;

/**
 * Дампер вывода выражений PHP в консоль или в HTML.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Debug
 * @since 2.0
 */
class Dumper
{
    protected static array $times = [];

    /**
     * HTML с информацией о переменной.
     * 
     * HTML должен содержать:
     * - "%label%", название переменной;
     * - "%dump%", значение переменной.
     * 
     * @var string
     */
    public static string $formatHtml = '<pre class="dumper">%label%%dump%</pre>';

    /**
     * Форматирует переменную в строку.
     * 
     * @param mixed $var Переменная.
     * @param bool $highlight Подсветка переменных в строке.
     * 
     * @return string
     */
    public static function dumpAsString($var, bool $highlight = true): string
    {
        ob_start();

        if ($highlight) {
            $output = highlight_string('<?php' . @var_export($var, true), true);
            echo str_replace('&lt;?php', ' ', $output);
        } else
            var_dump($var);

        $content = ob_get_clean();
        // очистить строки и отступы
        return preg_replace("/\]\=\>\n(\s+)/m", "] => ", $content);
    }

    /**
     * Форматирует данные для вывода в консоль.
     * 
     * @param string $output Данные для вывода.
     * @param string|null $label Метка добавляемая в начало вывода.
     * 
     * @return string
     */
    protected static function exportConsole(string $output, ?string $label = null): string
    {
        $label = $label === null ? '' : rtrim($label) . ' ';
        return PHP_EOL . $label
             . PHP_EOL . $output
             . PHP_EOL;
    }

    /**
     * Форматирует данные для вывода в формат HTML.
     * 
     * @param string $output Данные для вывода.
     * @param null|string $label Метка добавляемая в начало вывода.
     * 
     * @return string
     */
    protected static function exportHtml(string $output, ?string $label = null): string
    {
        return strtr(
            static::$formatHtml,
            [
                '%label%' => $label === null ? '' : trim($label) . ' ',
                '%dump%'  => $output
            ]
        );
    }

    /**
     * Выводит информацию о переменной.
     * 
     * Это обвёртка для `var_dump()`, которая добавляе теги `<pre>`, очищает символы 
     * новой строки, и отступы, и выполняет `htmlentities()` перед выводом.
     *
     * @param mixed $var Переменная для вывода.
     * @param null|string $label Метка добавляемая в начало вывода.
     * @param bool $highlight
     * @param bool $echo Вывод `echo`, если истина.
     * 
     * @return string
     */
    public static function dump($var, ?string $label = null, bool $highlight = true, bool $echo = true)
    {
        $dump = static::dumpAsString($var, $highlight);
        if (defined('IS_CONSOLE') && IS_CONSOLE)
            $output = static::exportConsole($dump, $label);
        else
            $output = static::exportHtml($dump, $label);
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Возвращает время работы сценария.
     * 
     * @param int $precision Количество чисел после запятой.
     * 
     * @return string
     */
    public static function executeTime(int $precision = 3): string
    {
        $timeEnd = microtime(true);
        $timeTotal = $timeEnd - GE_DEBUG_START;
        return (function_exists('number_format_i18n')) ? \number_format_i18n($timeTotal, $precision) : number_format($timeTotal, $precision);
    }

    /**
     * Начало выполения сценария. 
     * 
     * @param string $name Название метки.
     * 
     * @return void
     */
    public static function beginTime(string $name): void
    {
        static::$times[$name] = microtime(true);
    }
    /**
     * Конец выполнения сценария. 
     * 
     * @param string $name Название метки.
     * @param int $precision Количество чисел после запятой.
     * 
     * @return void
     */
    public static function endTime(string $name, int $precision = 3): void
    {
        if (isset(static::$times[$name])) {
            static::$times[$name] =  microtime(true) - static::$times[$name];
        }
    }

    /**
     * Подсчитывает время выполнения сценария по указанной метки.
     * 
     * @param string $name Название метки.
     * @param int $precision Количество чисел после запятой.
     * 
     * @return string
     */
    public static function getTime(string $name, int $precision = 3): string
    {
        if (!isset(static::$times[$name])) {
            return '0';
        }
        $timeTotal = static::$times[$name];
        return (function_exists('number_format_i18n')) ? \number_format_i18n($timeTotal, $precision) : number_format($timeTotal, $precision);
    }

    /**
     * Возвращает пиковое значение объёма памяти, выделенное PHP.
     * 
     * @see https://www.php.net/manual/ru/function.memory-get-peak-usage.php
     * 
     * @param bool $realUsage Передача true в качестве этого аргумента позволяет 
     *     получить реальный объем памяти, выделенный системой. Если аргумент не 
     *     задан или равен false, возвращаются сведения только о памяти, выделенной 
     *     функцией emalloc(). 
     * @param bool $toString Форматирует результат в строку с использованием 
     *     {@see \Ge\I18n\Formatter::toShortSizeDataUnit()}.
     * 
     * @return int|string Пиковое значение объёма памяти, выделенное PHP.
     */
    public static function memoryPeakUsage(bool $realUsage = false, bool $toString = true): int|string
    {
        $memory = memory_get_peak_usage($realUsage);
        return $toString ? Ge::$app->formatter->toShortSizeDataUnit($memory) : $memory;
    }

    /**
     * Возвращает количество памяти, выделенное для PHP.
     * 
     * @see https://www.php.net/manual/ru/function.memory-get-usage.php
     * 
     * @param bool $realUsage Передача true позволяет узнать реальное количество памяти, 
     *     выделенной PHP скрипту системой, включая неиспользуемые страницы. Если аргумент 
     *     не задан или равен false, будет возвращено только количество используемой памяти. 
     * @param bool $toString Форматирует результат в строку с использованием 
     *     {@see \Ge\I18n\Formatter::toShortSizeDataUnit()}.
     * 
     * @return int|string Количество памяти, выделенное для PHP.
     */
    public static function memoryUsage(bool $realUsage = false, bool $toString = true): int|string
    {
        $memory = memory_get_usage($realUsage);
        return $toString ? Ge::$app->formatter->toShortSizeDataUnit($memory) : $memory;
    }
}
