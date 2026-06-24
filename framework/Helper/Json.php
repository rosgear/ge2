<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge;
use Ge\Exception;

/**
 * Вспомогательный класс Json, обеспечивает кодирование и декодирование данных JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Json
{
    /**
     * Использовать исключения при неуспешном выполнении метода.
     * 
     * @var bool
     */
    public static bool $throwException = false;

    /**
     * Принимает закодированную в JSON строку и преобразует её в PHP-значение.
     * 
     * @link https://www.php.net/manual/ru/function.json-decode.php
     * 
     * @param string $json Строка JSON для декодирования. 
     * @param bool $associative Если `true`, объекты JSON будут возвращены как ассоциативные 
     *     массивы (array); если `false`, объекты JSON будут возвращены как объекты (object). 
     *     Если `null`, объекты JSON будут возвращены как ассоциативные массивы (array) или 
     *     объекты (object) в зависимости от того, установлена ли JSON_OBJECT_AS_ARRAY в flags. 
     * @param int $depth Максимальная глубина вложенности структуры, для которой будет производиться 
     *     декодирование. Значение должно быть больше 0 и меньше или равно 2147483647. 
     * @param int $flags Битовая маска из констант JSON_BIGINT_AS_STRING, JSON_INVALID_UTF8_IGNORE, 
     *     JSON_INVALID_UTF8_SUBSTITUTE, JSON_OBJECT_AS_ARRAY, JSON_THROW_ON_ERROR. 
     * 
     * @return mixed
     */
    public static function decode(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
    {
        return json_decode($json, $associative, $depth);
    }

    /**
     * Пытается принять закодированную в JSON строку и преобразовть её в PHP-значение.
     * 
     * @see Json::decode()
     * 
     * @param string $json Строка JSON для декодирования. 
     * @param bool $associative Если `true`, объекты JSON будут возвращены как ассоциативные 
     *     массивы (array); если `false`, объекты JSON будут возвращены как объекты (object). 
     *     Если `null`, объекты JSON будут возвращены как ассоциативные массивы (array) или 
     *     объекты (object) в зависимости от того, установлена ли JSON_OBJECT_AS_ARRAY в flags. 
     * @param int $depth Максимальная глубина вложенности структуры  (по умолчанию '512').
     * @param int $flags Битовая маска из констант (по умолчанию '0').
     * 
     * @return false|array|object Если значение `false`, ошибка преобразования строки JSON.
     * 
     * @throws Exception\JsonFormatException Ошибка преобразования строки JSON.
     */
    public static function tryDecode(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
    {
        $var = self::decode($json, $associative, $depth, $flags);
        if ($error = self::error()) {
            if (self::$throwException)
                throw new Exception\JsonFormatException(\Ge::t('app', 'Could not JSON decode: {0}', [$error]));
            else
                return false;
        }
        return $var;
    }

    /**
     * Возвращает JSON-представление данных.
     * 
     * @param mixed $value Значение, которое будет закодировано. Может быть любого 
     *     типа, кроме resource.
     * @param bool $safe Экранировать полученное JSON-представление (по умолчанию `true`).
     * @param int $flags Битовая маска, составляемая из значений 
     *     {@link https://www.php.net/manual/ru/json.constants.php} (по умолчанию '0').
     * @param int $depth Устанавливает максимальную глубину. Должен быть больше нуля 
     *     (по умолчанию '512').
     * 
     * @return string|false Возвращает строку (string), закодированную JSON или `false` 
     *     в случае возникновения ошибки. 
     */
    public static function encode($value, bool $safe = true, int $flags = 0, int $depth = 512): string|false
    {
        $str = json_encode($value, $flags);
        if ($str !== false && $safe) {
            $str = static::safeCurlyBraces($str);
        }
        return $str;
    }

    /**
     * Выполняет экранирование фигурных скобок в JSON-представлении. 
     *
     * Чтобы избежать замены 2-х фигурных скобок, добавляется между ними пробел.
     * 
     * @param string $str Строка JSON.
     * 
     * @return string
     */
    public static function safeCurlyBraces(string $str): string
    {
        return str_replace(['{{', '}}'], ['{ {', '} } '], $str);
    }

    /**
     * Возвращает все возможные ошибки (сообщения) при преобразовании JSON-представления.
     * 
     * @return array
     */
    public static function getErrorMessages(): array
    {
        return [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
            JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given',
            JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded'
        ];
    }

    /**
     *  Возвращает последнюю ошибку при преобразовании JSON-представления.
     * 
     * @return false|string Возвращает значение `false`, если код ошибки невозможно 
     *     получить. Иначе, текст ошибки.
     */
    public static function error(): false|string
    {
        $errors = self::getErrorMessages();
        return $errors[json_last_error()] ?? false;
    }

    /**
     * Кодирует заданное значение в строку JSON, экранирующую HTML-объекты, чтобы 
     * его можно было безопасно встроить в HTML-код.
     *
     * Этот метод улучшает `json_encode()`, поддерживая выражения JavaScript.
     *
     * Обратите внимание, что данные, закодированные как JSON, должны быть закодированы 
     * в UTF-8 в соответствии со спецификацией JSON.
     * Вы должны убедиться, что строки, переданные этому методу, имеют правильную кодировку.
     * 
     * @see Json::encode()
     * 
     * @param mixed $value Данные для кодирования.
     * 
     * @return string|false Возвращает строку (string), закодированную JSON или `false` 
     *     в случае возникновения ошибки. 
     */
    public static function htmlEncode(mixed $value): string|false
    {
        return static::encode($value, true, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    /**
     * Возвращает JSON-представление данных c форматированием.
     * 
     * @param string $json Строка JSON без форматирования. 

     * 
     * @return string|false Возвращает строку (string), закодированную JSON или `false` 
     *     в случае возникновения ошибки. 
     * @param int $depth Максимальная глубина вложенности структуры, для которой будет производиться 
     *     декодирование. Значение должно быть больше 0 и меньше или равно 2147483647 (по умолчанию '512').
     * @param int $flags Битовая маска из констант JSON_BIGINT_AS_STRING, JSON_INVALID_UTF8_IGNORE, 
     *     JSON_INVALID_UTF8_SUBSTITUTE, JSON_OBJECT_AS_ARRAY, JSON_THROW_ON_ERROR.
     * 
     * @return string|false Возвращает строку (string), закодированную JSON или `false` 
     *     в случае возникновения ошибки. 
     */
    public static function prettyEncode(string $json, int $depth = 512, int $flags = 0): string|false
    {
        $value = json_decode($json, null, $depth, $flags);
        if (self::error()) {
            return false;
        }
        return json_encode($value, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    }

    /**
     * Читает JSON-представление из файла и декодирует его.
     * 
     * @param string $filename Имя читаемого файла (файл включает путь). 
     * @param null|bool $associative Если значение `true`, объекты JSON будут возвращены 
     *     как ассоциативные массивы (`array`); если `false`, объекты JSON будут возвращены 
     *     как объекты (`object`) (по умолчанию `true`).
     * @param null|resource $context Корректный ресурс контекста, созданный с помощью 
     *     функции `stream_context_create()` (по умолчанию `null`).
     * 
     * @return mixed
     * 
     * @throws Exception\JsonFormatException Ошибка декодирования файла.
     */
    public static function loadFromFile(string $filename, ?bool $associative = true, $context = null): mixed
    {
        $json = file_get_contents($filename, true, $context);
        if ($json === false) {
            return false;
        }

        $result = static::decode($json, $associative);
        if ($error = self::error()) {
            if (self::$throwException)
                throw new Exception\JsonFormatException(Ge::t('app', 'Could not JSON decode: {0}', [$error]));
            else
                return false;
        }
        return $result;
    }

    /**
     * Сохраняет данные в файл в JSON-представлении.
     * 
     * @link https://www.php.net/manual/ru/function.file-put-contents.php
     * 
     * @param string $filename Имя читаемого файла (файл включает путь).
     * @param mixed $data
     * @param int $flags Значением может быть любая комбинация следующих флагов, 
     *    соединённых бинарным оператором ИЛИ (|):
     *    - FILE_USE_INCLUDE_PATH, ищет filename в подключаемых директориях;
     *    - FILE_APPEND, если файл filename уже существует, данные будут дописаны в конец файла;
     *    - LOCK_EX, получить эксклюзивную блокировку на файл на время записи.
     * @param null|resource $context Корректный ресурс контекста, созданный с помощью 
     *     функции `stream_context_create()` (по умолчанию `null`).
     * 
     * @return bool
     */
    public static function saveToFile(string $filename, $data, $flags = 0, $context = null, int $encodeFlags = 0): bool
    {
        $result = file_put_contents($filename, json_encode($data, $encodeFlags), $flags, $context);
        return $result !== false;
    }
}
