<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

/**
 * Вспомогательный класс String, обеспечивает вывод и формат строк.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Str extends Helper
{
    /**
     * @var string Разделитель идентификатора и строки.
     */
    public const ID_SEPARATOR = '-';

    /**
     * @var string Позиция идентификатора в строке "слева".
     */
    public const ID_POS_LEFT  = 'left';

    /**
     * @var string Позиция идентификатора в строке "справа".
     */
    public const ID_POS_RIGHT = 'right';

    /**
     * @var string Конец сроки.
     */
    public const EOL = "\r\n";

    /**
     * @var string Шаблон конца сроки.
     */
    public const EOL_PATTERN = '/(\r\n)|\r|\n/';

    /**
     * @var string Разделитель в локализации названия файла.
     */
    public const LOCALIZE_DELIMITER = '-';

   /**
     * Возвращает количество байтов в заданной строке.
     * 
     * @link https://php.net/manual/en/function.mb-strlen.php
     * 
     * @param string $string Измеряемая строка.
     * 
     * @return int Количество байтов в указанной строке.
     */
    public static function byteLength(string $string): int
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Конвертация строки из UTF16 в UTF8
     * 
     * @param string $str Строка.
     * 
     * @return string
     */
    public static function utf16ToUtf8(string $str): string
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
            },
            $str
        );
    }

    /**
     * Сравнение 2-х строк, чтобы избежать временных атак.
     *
     * C функция memcmp() напрямую использует PHP, завершается как только разница 
     * найдется в 2-х буферах.
     *
     * @param mixed $expected Ожидаемая значение.
     * @param mixed $actual Текущее значение.
     * 
     * @return bool
     */
    public static function compareStrings(mixed $expected, mixed $actual): bool
    {
        $expected = (string) $expected;
        $actual   = (string) $actual;

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        $lenExpected  = strlen($expected);
        $lenActual    = strlen($actual);
        $len          = min($lenExpected, $lenActual);

        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }
        $result |= $lenExpected ^ $lenActual;
        return ($result === 0);
    }

    /**
     * Создаёт случайную строку указанной длины.
     *
     * Использует список символов для генерации новой строки.
     *
     * @param int $length Длина строки.
     * @param string $charlist Список символов.
     * 
     * @return string
     */
    public static function randomChars(int $length, string $charlist): string
    {
        $result = '';
        $listLen = mb_strlen($charlist);
        for ($i = 0; $i < $length; $i++) {
            $pos = rand(0, $listLen);
            $result .= $charlist[$pos];
        }
        return $result;
    }

    /**
     * Сокращает длину строки.
     *
     * @param string $str Строка.
     * @param int $start Начало строки.
     * @param int $width Максимальная длина.
     * @param string $trimmarker Знак после урезания строки (по умолчанию '...').
     * 
     * @return string
     */
    public static function ellipsis(
        string $str, 
        int $start, 
        int $width, 
        string $trimmarker = '...' 
    ): string
    {
        return $str ? rtrim(mb_strimwidth(trim((string) $str), $start, $width, $trimmarker)) : '';
    }

    /**
     * Возвращает заполненный массив из разобранной строки.
     * 
     * Массив заполняется значением `true`.
     * 
     * @param string $str Строка с разделителем ",", например: 'a,b,c,d'.
     * 
     * @return array
     */
    public static function parseStringToSArray(string $str): array
    {
        $arr = explode(',', $str);
        return array_fill_keys($arr, true);
    }

    /**
     * Возвращает ассоциативный массив из разобранной строки.
     * 
     * Например: `'width=10;height=20'` => `['width' => 10, 'height' => 20]`.
     * 
     * @param string $str Разбираемая строка с разделителем.
     * @param string $delimiterRows Разделитель выражаений (по умолчанию ';').
     * @param string $delimiterVars Разделитель атрибутов и их значений (по умолчанию '=').
     * 
     * @return array
     */
    public static function parseStringToArray(
        string $str, 
        string $delimiterRows = ';', 
        string $delimiterVars = '='
    ): array
    {
        $items = [];
        $rows = explode($delimiterRows, trim($str));
        foreach ($rows as $row) {
            $vars = explode($delimiterVars, $row);
            if ($vars[0])
                $items[$vars[0]] = $vars[1];
        }
        return $items;
    }

    /**
     * Возвращает строку из ассоциативного массива.
     * 
     * Например: `['width' => 10, 'height' => 20]` => `'width=10;height=20'`.
     * 
     * @param array $array Разбираемый массив.
     * @param string $delimiterRows Разделитель выражаений (по умолчанию ';').
     * @param string $delimiterVars Разделитель атрибутов и их значений (по умолчанию '=').
     * 
     * @return string
     */
    public static function parseArrayToString(
        array $array, 
        string $delimiterRows = ';', 
        string $delimiterVars = '='
    ): string
    {
        if (empty($array)) return '';

        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $key . $delimiterVars . $value;
        }
        return implode($delimiterRows, $result);
    }

    /**
     * Возвращение строки полученные при "склеивании" значений ассоциативного массива.
     * 
     * @param array $pieces Массив в виде пар "атрибут - значение".
     * @param string $glueVars Разделитель атрибутов и из значений.
     * @param string $glueRows Разделитель выражаений
     * @param int $varLength Максимальная длина значений (по умолчанию 0).
     * 
     * @return string
     */
    public static function implodeParameters(
        array $pieces, 
        string $glueVars, 
        string $glueRows, 
        int $varLength = 0
    ): string
    {
        $str = '';
        if (empty($pieces)) return $str;

        foreach ($pieces as $key => $value) {
            if ($varLength) {
                if (is_string($value) && mb_strlen($value) > $varLength)
                    $value = mb_substr($value, 0, $varLength);
                if (is_array($value)) {
                    $value = '[array]';
                }
            }
            $str .= $key . $glueVars . $value . $glueRows;
        }
        return $str;
    }

    /**
     * Преобразует значение в 'true' или 'false'.
     *
     * @param mixed $value Преобразуемое значение.
     * 
     * @return string
     */
    public static function boolToStr(mixed $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Преобразует значение в логический (boolean) тип.
     *
     * @param mixed $value Преобразуемое значение.
     * 
     * @return bool
     */
    public static function toBool(mixed $value): bool
    {
        if ($value === 'true' || $value === 'on')
            return true;
        else
        if ($value === 'false')
            return false;
        else
            return boolval($value);
    }

    /**
     * Убирает из строки указанное слово слева.
     *
     * @param string $str Строка.
     * @param string $word Слово.
     * 
     * @return string
     */
    public static function ltrimWord(string $str, string $word): string
    {
        $pos = strpos($str, $word);
        if ($pos === 0) {
            return substr($str, strlen($word));
        }
        return $str;
    }

    /**
     * Убирает из строки указанное слово справа.
     *
     * @param string $str Строка.
     * @param string $word Слово.
     * 
     * @return string
     */
    public static function rtrimWord(string $str, string $word): string
    {
        $pos = strpos($str, $word, strlen($str) - strlen($word));
        if ($pos !== false) {
            return substr($str, 0, strlen($word));
        }
        return $str;
    }

    /**
     * Возвращение идентификатора из строки.
     *
     * @param string $str Строка.
     * @param string $position Положение идентификатора в строке: ID_POS_LEFT, 
     *     ID_POS_RIGHT (по умолчанию ID_POS_LEFT).
     * @param string $separator Разделитель строки и идентификатора (по умолчанию ID_SEPARATOR).
     * 
     * @return false|string
     */
    public static function idFromStr(
        string $str, 
        string $position = self::ID_POS_LEFT, 
        string $separator = self::ID_SEPARATOR
    ): false|string
    {
        if (strpos($str, $separator) === false) return false;

        if ($position === self::ID_POS_LEFT) {
            $id = (int) ltrim($str);
            return $id > 0 ? $id : false;
        }
        return $str;
    }

    /**
     * Добавляет идентификатора в строку.
     *
     * @param string $str Строка.
     * @param string|int $id Идентификатор.
     * @param string $position Положение идентификатора в строке: `ID_POS_LEFT`, `ID_POS_RIGHT` 
     *     (по умолчанию `ID_POS_LEFT`).
     * @param string $separator Разделитель строки и идентификатора (по умолчанию `ID_SEPARATOR`).
     * 
     * @return string
     */
    public static function idToStr(
        string $str, 
        string|int $id, 
        string $position = self::ID_POS_LEFT, 
        string $separator = self::ID_SEPARATOR
    ): string
    {
        if ($position === self::ID_POS_LEFT)
            return $id . $separator . $str;
        else
        if ($position === self::ID_POS_RIGHT)
            return $str . $separator . $id;
        else
            return  $str;
    }

    /**
     * Выполняет замену (спецсимволов) конца строки.
     *
     * @param string $string Строка.
     * @param string $replacement Замена спецсимволов на значение (по умолчанию '').
     * 
     * @return string
     */
    public static function replaceEOL(string $string, string $replacement = ''): string
    {
        return $string ? preg_replace('/(\r\n)|\r|\n/', $replacement, $string) : '';
    }

    /**
     * Замена символов строки на спецсимволы (конца строки).
     *
     * @param string $pattern Заменяемы символы.
     * @param string $string Строка.
     * 
     * @return string
     */
    public static function EOLReplace(string $pattern, string $string): string
    {
        return $string ? str_replace($pattern, self::EOL, $string) : '';
    }

    /**
     * Разбивает указанную строку спецсимволами (конца строки).
     *
     * @param string $string Строка.
     * 
     * @return array|false
     */
    public static function splitEOL(string $string): array|false
    {
        return preg_split(self::EOL_PATTERN, $string);
    }

    /**
     * Склеивает массив строк шаблоном (конца строки).
     *
     * @param array $array Массив строк.
     * 
     * @return string
     */
    public static function implodeEOL(array $array): string
    {
        return implode(self::EOL, $array);
    }

    /**
     * Генерирует указанное количество случайных байтов.
     * 
     * Обратите внимание, что вывод не может быть ASCII.
     * 
     * @param int $length Количество байтов для генерации (по умолчанию 32).
     * 
     * @return string Возвращает сгенерированные случайные байты.
     */
    public static function random(int $length = 32): string
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    /**
     * Добавляет косую черту в скобках.
     * 
     * Например: '{hello}' => '\{hello\}'.
     *
     * @param string $str Строка.
     * 
     * @return string
     */
    public static function addBracketSlashes(string $str): string
    {
        return str_replace(['{', '}'], ['\{', '\}'],  $str);
    }

    /**
     * Убирает косую черту из скобок.
     * 
     * Например: '\{hello\}' => '{hello}'.
     *
     * @param string $str Строка.
     * 
     * @return string
     */
    public static function stripBracketSlashes(string $str): string
    {
        return str_replace(['\{', '\}'], ['{', '}'],  $str);
    }

    /**
     * Выполняет локализацию имени файла.
     * 
     * Например: 'filename.php' => 'filename-ru_RU.php'.
     *
     * @param string $filename Имя файла.
     * @param \Ge\Language\Language|null $language Язык (по умолчанию `null`).
     * @param string|null $extension Расширение файла (по умолчанию `null`).
     * 
     * @return string
     */
    public static function localizeFilename(
        string $filename, 
        $language = null, 
        ?string $extension = null
    ): string
    {
        if ($language === null) {
            $language = static::$app->language;
        }
        if ($extension === null) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
        }
        return str_replace(
            '.' . $extension, self::LOCALIZE_DELIMITER . $language->locale . '.'. $extension, $filename
        );
    }

    /**
     * Возвращает интерпретируемое строковое представление переменной.
     * 
     * @link https://www.php.net/manual/ru/function.var-export.php
     * 
     * @param mixed $value Переменная, которую необходимо экспортировать. 
     * @param bool $format Выполнить форматирование результата в одну строку 
     *     (по умолчанию `false`).
     * 
     * @return string
     */
    public static function varExport($value, bool $format = false): string
    {
        $str = var_export($value, true);
        if ($format) {
            $str = str_replace("\n", '', $str);
            $str = str_replace(['array (', ')'], ['[', ']'], $str);
            $pattern = '~(?<!\\\\)(?:\\\\{2})*(?:"[^\\\\"]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\')(*SKIP)(*F)|\h+~s';
            return preg_replace($pattern, '', $str);
        }
        return $str;
    }

    /**
     * Добавляет приставку к имени файла.
     * 
     * @param string $filename Имя файла.
     * @param string $prefix Приставка к имени файла.
     * @param string $position Позиция приставки к имени файла: 'left', 'right'
     *    (по умолчанию 'right').
     * 
     * @return string
     */
    public static function addPefixToFilename(
        string $filename, 
        string $prefix, 
        string $position = 'right'
    ): string
    {
        $info = pathinfo($filename);

        $filename = $info['filename'];
        switch ($position) {
            case 'left': 
                if (mb_strpos($filename, $prefix) !== 0)
                    $filename = $prefix . $filename; 
                break;

            case 'right':
                if (!(mb_strpos($filename, $prefix) > 0))
                    $filename = $filename . $prefix;
                break;
        }
        return $info['dirname'] . '/' . $filename . '.' . $info['extension'];
    }
}