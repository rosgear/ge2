<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015-2025 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge;
use Ge\Helper\Url;

/**
 * Вспомогательный класс Html, предоставляет набор статических методов для генерации 
 * часто используемых тегов HTML.
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Html extends Helper
{
    /**
     * Список пустых элементов.
     * 
     * @see http://www.w3.org/TR/html-markup/syntax.html#void-element
     * 
     * @var array<string, true>
     */
    public static array $voidElements = [
        'area'    => true,
        'base'    => true,
        'br'      => true,
        'col'     => true,
        'command' => true,
        'embed'   => true,
        'hr'      => true,
        'img'     => true,
        'input'   => true,
        'keygen'  => true,
        'link'    => true,
        'meta'    => true,
        'param'   => true,
        'source'  => true,
        'track'   => true,
        'wbr'     => true,
    ];

    /**
     * Предпочтительный порядок атрибутов в теге.
     * 
     * В основном это влияет на порядок атрибутов, отображаемых {@see Html::renderTagAttributes()}.
     * 
     * @var array<int, string>
     */
    public static array $attributeOrder = [
        'type', 'id', 'class', 'name', 'value', 'href', 'src', 'srcset', 'form', 'action', 'method',
        'selected', 'checked', 'readonly', 'disabled', 'multiple', 'size', 'maxlength', 'width', 'height',
        'rows', 'cols', 'alt', 'title', 'rel', 'media'
    ];

    /**
     * Cписок атрибутов тегов, которые следует обрабатывать особо, если их значения 
     * имеют тип array.
     * 
     * В частности, если значение атрибута `data` = ['name' => 'abc', 'age' => 7] `, 
     * два атрибута будет сгенерирован вместо одного: `data-name = "abc" data-age ="7"`.
     * 
     * @var array<int, string>
     */
    public static array $dataAttributes = ['aria', 'data', 'data-ng', 'ng'];

    /**
     * Кодирует специальные символы в HTML-сущности.
     * 
     * @see https://secure.php.net/manual/en/function.htmlspecialchars.php
     * @see Html::decode()
     * @see Ge::charset()
     * 
     * @param string $content Кодируемый контент.
     * @param bool $doubleEncode Если `true`, то PHP не будет преобразовывать существующие 
     *    HTML-сущности. По умолчанию преобразуется все без ограничений (по умолчанию `true`).
     * 
     * @return string Закодированный контент.
     */
    public static function encode(string $content, bool $doubleEncode = true): string
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, Ge::charset('UTF-8'), $doubleEncode);
    }

    /**
     * Декодирует специальные HTML-сущности обратно в соответствующие символы.
     * 
     * Это противоположно {@see Html::encode()}.
     * 
     * @see https://secure.php.net/manual/en/function.htmlspecialchars-decode.php
     * @see Html::encode()
     * 
     * @param string $content Контент для декодирования.
     * 
     * @return string Декодированный контент.
     */
    public static function decode(string $content): string
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Создаёт полный HTML-тег.
     * 
     * @see Html::beginTag()
     * @see Html::endTag()
     * 
     * @param string|false|null $name Имя тега. Если $name имеет значение `null` 
     *    или `false`, соответствующий контент будет отображаться без тега.
     * @param array|string $content Содержимое, которое должно быть заключено между 
     *    начальным и конечным тегами. Он не будет закодирован в HTML. Если это исходит 
     *    от конечных пользователей, следует подумать о {@see Html::encode()}, чтобы 
     *    предотвратить XSS атаки.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) в 
     *    виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться.
     *
     * Например, при использовании: 
     * ```php
     * ['class' => 'my-class', 'target' => '_blank', 'value' => null]
     * ```
     * это приведет к отображению атрибутов HTML следующим образом: 
     * ```php
     * class="my-class" target="_blank"
     * ```
     *
     * @return string Сгенерированный HTML-тег.
     */
    public static function tag(
        string|false|null $name, 
        array|string $content = '', 
        array $attributes = []
    ): string
    {
        if ($name === null || $name === false) {
            return $content;
        }

        if (is_array($content)) {
            $content = implode('', $content);
        }
        $html = "<$name" . static::renderTagAttributes($attributes) . '>';
        return isset(static::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    /**
     * Создаёт полные HTML-теги указанные в виде массива параметров.
     * 
     * Массива параметров задаются в виде:
     * ```php
     * [
     *     [
     *         $name, // имя тега
     *         $content, // содержимое
     *         $attributes // атрибуты тега
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @param array $rows Массива параметров тегов.
     * 
     * @return string Сгенерированные HTML-теги.
     */
    public static function tags(array $rows): string
    {
        $html = '';
        foreach ($rows as $row) {
            if (is_string($row))
                $html .= $row;
            else
                $html .= static::tag($row[0], $row[1] ?? '', $row[2] ?? []);
        }
        return $html;
    }

    /**
     * Создаёт начальный тег.
     * 
     * @see Html::endTag()
     * @see Html::tag()
     * 
     * @param string|false|null $name Имя тега. Если `$name` имеет значение `null` или `false`,
     *    соответствующий контент будет отображаться без тега.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный начальный тег.
     */
    public static function beginTag(string|false|null $name, array $attributes = []): string
    {
        if ($name === null || $name === false) {
            return '';
        }
        return "<$name" . static::renderTagAttributes($attributes) . '>';
    }

    /**
     * Создаёт закрывающий тег.
     * 
     * @see Html::beginTag()
     * @see Html::tag()
     * 
     * @param string|false|null $name Имя тега. Если $name имеет значение `null` или `false`,
     *    соответствующий контент будет отображаться без тега.
     * 
     * @return string Cгенерированный конечный тег.
     */
    public static function endTag(string|false|null $name): string
    {
        if ($name === null || $name === false) {
            return '';
        }
        return "</$name>";
    }

    /**
     * Создаёт тег стиля.
     * 
     * @param string $content Cодержание стиля.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Cгенерированный тег стиля
     */
    public static function style(string $content, array $attributes = []): string
    {
        return static::tag('style', $content, $attributes);
    }

    /**
     * Создаёт тег скрипта.
     * 
     * @param string $content Cодержание сценария.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Cгенерированный тег скрипта.
     */
    public static function script(string $content, array $attributes = []): string
    {
        return static::tag('script', $content, $attributes);
    }

    /**
     * Сортирует атрибуты тега HTML.
     * 
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *     в виде пар "имя => значение".
     * 
     * @return array Отсортированные атрибуты тега.
     */
    public static function sortTagAttributes(array $attributes): array
    {
        if (sizeof($attributes) == 1) {
            return $attributes;
        }

        $sorted = [];
        foreach (static::$attributeOrder as $name) {
            if (isset($attributes[$name])) {
                $sorted[$name] = $attributes[$name];
            }
        }
        return array_merge($sorted, $attributes);
    }

    /**
     * Вывод атрибутов тега HTML.
     *
     * Атрибуты, значения которых имеют логический тип, будут рассматриваться как атрибут ["boolean"] 
     * (http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
     *
     * Атрибуты, значения которых `null`, отображаться не будут.
     *
     * Значения атрибутов будут закодированы в HTML с использованием {@see Html::encode()}.
     * 
     * Атрибут "data" обрабатывается особым образом, когда получает значение в виде массива.
     * Например, если указан массив:
     * ```php 
     * ['data' => ['value' => 1, 'name" => 'gear']]
     * ```
     * то атрибуты будут иметь вид:
     * ```php
     * data-value="1" data-name="gear"
     * ```
     * или
     * ```php
     * ['data' => ['params' => ['value' => 1, 'name' => 'gear'], 'status' => 'ok']
     * ```
     * то
     * ```php
     * data-params='{"value":1,"name":"gear"}' data-status="ok"
     * ```
     *
     * @param array<string, string> $attributes Атрибуты для рендеринга. Значения атрибутов 
     *    будут закодированы в HTML с использованием {@see Html::encode()}.
     * @return string Результат рендеринга. Если атрибуты не пустые, они будут преобразованы в 
     *    строку с начальным пробелом (чтобы его можно было напрямую добавить к имени тега в теге. 
     *    Если атрибут отсутствует, будет возвращена пустая строка.
     */
    public static function renderTagAttributes(array $attributes): string
    {

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . Json::htmlEncode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . static::encode($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(implode(' ', $value)) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . Json::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . static::encode($value) . '"';
            }
        }
        return $html;
    }

    /**
     * Преобразует массив стилей CSS в строковое представление.
     *
     * Например,
     * ```
     * <?php 
     * print_r(Html::cssStyleFromArray(['width' => '100px', 'height' => '200px']));
     * // отобразит: 'width: 100px; height: 200px;'
     * ?>
     * ```
     *
     * @param array<string, string> $style Массив стилей CSS. Ключи массива - это имена 
     *    свойств CSS, а значения массива - соответствующие значения свойств CSS.
     * @return string Строка стиля CSS. Если стиль CSS пуст, будет возвращено значение `null`.
     */
    public static function cssStyleFromArray(array $style): string
    {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }
        return $result === '' ? null : rtrim($result);
    }

    /**
     * Преобразует строку стиля CSS в представление массива.
     *
     * Ключи массива - это имена свойств CSS, а значения массива - соответствующие 
     * значения свойств CSS.
     *
     * Например,
     *
     * ```
     * <?php
     * print_r(Html::cssStyleToArray('width: 100px; height: 200px;'));
     * // will display: ['width' => '100px', 'height' => '200px']
     * ?>
     * ```
     *
     * @param string $style Строка стиля CSS.
     * 
     * @return array<string, string> Представление стиля CSS в виде массива.
     */
    public static function cssStyleToArray(string $style): array
    {
        $result = array();
        foreach (explode(';', $style) as $property) {
            $property = explode(':', $property);
            if (count($property) > 1) {
                $result[trim($property[0])] = trim($property[1]);
            }
        }
        return $result;
    }

    /**
     * Создаёт тег гиперссылки.
     * 
     * @see Url::to()
     * 
     * @param string $text Тело ссылки. Оно не будет закодировано в HTML. Поэтому 
     *    вы можете передать HTML-код, например тег изображения. Если это делает 
     *    сам пользователь, следует использовать {@see Html::encode()} чтобы 
     *    предотвратить XSS атаки.
     * @param string|array|null URL-адрес тега гиперссылки. Этот параметр будет 
     *    обработан {@see Url::to()} и будет использоваться для атрибута "href" тега. 
     *    Если этот параметр равен `null` или `#`, атрибут "href" не будет сгенерирован
     *    (по умолчанию `null`).
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) в 
     *    виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}
     *    (по умолчанию `[]`). 
     * 
     * @return string Созданная гиперссылка.
     */
    public static function a(
        string $text, 
        string|array|null $url = null, 
        array $attributes = []
    ): string
    {
        if ($url !== null) {
            // если главная страница, нет смысла получать URL
            if ($url === static::$app->baseUrl)
                $attributes['href'] = $url;
            else
                $attributes['href'] = $url !== '#' ? Url::to($url) : '#';
        }
        return static::tag('a', $text, $attributes);
    }

    /**
     * Создаёт гиперссылку mailto.
     * 
     * @param string $text Тело ссылки. Оно не будет закодировано в HTML. Поэтому вы 
     *    можете передать HTML-код, например тег изображения. Если это делает сам 
     *    пользователь, следует использовать {@see Html::encode()} чтобы предотвратить 
     *    XSS атаки.
     * @param string|null $email Адрес электронной почты. Если это значение `null`, 
     *    первый параметр (тело ссылки) будет рассматриваться как адрес электронной 
     *    почты и использоваться (по умолчанию `null`).
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированная ссылка mailto.
     */
    public static function mailto(
        string $text, 
        ?string $email = null, 
        array $attributes = []
    ): string
    {
        $attributes['href'] = 'mailto:' . ($email === null ? $text : $email);
        return static::tag('a', $text, $attributes);
    }

    /**
     * Создаёт тег изображения.
     * 
     * @param array|string $src URL изображения. Этот параметр будет обработан через {@see Url::to()}.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться.
     *    В качестве атрибута можно передать параметр `srcset` в виде массива, ключи которого 
     *    являются дескрипторами, а значения - URL-адресами. Все URL-адреса будут обработаны 
     *    {@see Url::to()} (по умолчанию `[]`).
     * @param bool $defineUrl Если true, URL не будет обработа через {@see Url::to()} 
     *    (по умолчанию `true`).
     * @return string Сгенерированный тег изображения.
     */
    public static function img(
        array|string $src, 
        array $attributes = [], 
        bool $defineUrl = true
    ): string
    {
        $attributes['src'] = $defineUrl ? Url::to($src) : $src;
        if (isset($attributes['srcset']) && is_array($attributes['srcset'])) {
            $srcset = [];
            foreach ($attributes['srcset'] as $descriptor => $url) {
                $srcset[] = Url::to($url) . ' ' . $descriptor;
            }
            $attributes['srcset'] = implode(',', $srcset);
        }
        if (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }
        return static::tag('img', '', $attributes);
    }

    /**
     * Возвращает контент изображения.
     * 
     * Контент имеет вид: `data:{type};{content}`
     * 
     * @param string $type Тип изображения (по умолчанию 'image/gif').
     * @param string|null $data Контент изображения. Если `null`, будет контент "пустого" 
     *     изображения в base64 (по умолчанию `null`).

     * @return string Контент изображения.
     */
    public static function imgDataSrc(string $type = 'image/gif', ?string $data = null): string
    {
        if ($data === null) {
            $data = 'base64,R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        }
        return 'data:' . $type . ';' . $data;
    }

    /**
     * Возвращает контент изображения.
     * 
     * Контент имеет вид: `data:{type};{content}`
     * 
     * @param string $filename Имя файла.
     * 
     * @return string|false Значение `false`, если невозможно загрузить изображение.
     */
    public static function encodeImgData(string $filename): string|false
    {
        $content = file_get_contents($filename, true);
        if ($content) {
            return 'data:' . mime_content_type($filename) . ';base64,' . base64_encode($content);
        }
        return false;
    }

    /**
     * Создаёт тег метки.
     * 
     * @param string $content Текст метки. Он не будет закодирован в HTML. Поэтому вы 
     *    можете передать HTML-код, например тег изображения. Если это делает сам 
     *    пользователь, следует использовать {@see Html::encode()} чтобы предотвратить 
     *    XSS атаки. Если это значение `null`, атрибут "for" не будет сгенерирован.
     * @param string|null $for (по умолчанию `null`)
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег метки.
     */
    public static function label(string $content, ?string $for = null, array $attributes = []): string
    {
        $attributes['for'] = $for;
        return static::tag('label', $content, $attributes);
    }

    /**
     * Создаёт тег шаблона "tpl".
     * 
     * @param string|array $content Текст шаблона. Он не будет закодирован в HTML. 
     *    Поэтому вы можете передать HTML-код, например тег изображения. Если это 
     *    делает сам пользователь, следует использовать {@see Html::encode()} чтобы 
     *    предотвратить XSS атаки.
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег шаблона "tpl".
     */
    public static function tpl(string|array $content, array $attributes = []): string
    {
        return static::tag('tpl', $content, $attributes);
    }

    /**
     * Создаёт тег шаблона "tpl" с условием сравнения.
     * 
     * Пример:
     * ```php
     * tplSwitch([
     *     ['case_name', 'case content'],
     *     // ...
     * ], 'switch_name')
     * ```
     * Результат рендеринга:
     * ```php
     * <tpl switch="switch_name">
     *     <tpl case="case_name">
     *        case content
     *     <tpl default>
     *        default content
     * </tpl>
     * ```
     * 
     * @param array $cases Маccив сверяемых и результирующих значений выбора.
     *    Где массив, имеет вид:  ```[['сверяемое значение', 'результат'],...]```
     * @param string $switch Условие сравнения.
     * 
     * @return string Сгенерированный тег шаблона "tpl".
     */
    public static function tplSwitch(array $cases, string $switch): string
    {
        if (is_array($cases)) {
            $content = '';
            foreach ($cases as $case) {
                if (is_array($case))
                    $content .= '<tpl case="' . $case[1] . '">' . $case[0];
                else
                    $content .= $case;
            }
        } else
            $content = $cases;
        return static::tag('tpl', $content, ['switch' => $switch]);
    }

    /**
     * Создаёт тег шаблона "tpl" с условием выбора.
     * 
     * Пример: 
     * ```php
     * tplCase('you select foobar', 'foobar')
     * ```
     * Результат рендеринга: 
     * ```php
     * <tpl case="foobar">you select foobar</tpl>
     * ```
     * 
     * @param string $content Результат выбора.
     * @param string $case Условие выбора.
     * 
     * @return string Сгенерированный тег шаблона "tpl" с условием выбора.
     */
    public static function tplCase(string $content, string $case): string
    {
        return '<tpl case="' . $case. '">' . $content;
    }

    /**
     * Создаёт тег шаблона "tpl" с выражением.
     * 
     * Пример: 
     * ```php
     * tplIf('foobar==1', 'good choice', 'bad choice')
     * ```
     * Результат рендеринга: 
     * ```php
     * <tpl if="foobar">good choice<tpl else>bad choice</tpl>
     * ```
     * 
     * @param string $if Выражение.
     * @param string $then Фрагмент для выражения, принимающего значение `true`.
     * @param string $else Фрагмент для выражения, принимающего значение `false` 
     *     (по умолчанию '').
     * 
     * @return string Сгенерированный тег шаблона "tpl" с выражением.
     */
    public static function tplIf(string $if, string $then, string $else = ''): string
    {
        return static::tag('tpl', $then . '<tpl else>' . $else, ['if' => $if]);
    }

    /**
     * Cоздаёт тег плавающего фрейма.
     * 
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег шаблона "tpl" с выражением.
     */
    public static function iframe(array $attributes = []): string
    {
        return static::tag('iframe', '', $attributes);
    }

    /**
     * Создаёт тег кнопки.
     * 
     * @param string $content Содержимое, заключенное в тег кнопки. Оно не будет 
     *    закодировано в HTML. Поэтому вы можете передать HTML-код, например тег 
     *    изображения. Если это делает сам пользователь, следует использовать {@see Html::encode()}, 
     *    чтобы предотвратить XSS атаки (по умолчанию 'button').
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки.
     */
    public static function button(string $content = 'Button', array $attributes = []): string
    {
        if (!isset($attributes['type'])) {
            $attributes['type'] = 'button';
        }
        return static::tag('button', $content, $attributes);
    }

    /**
     * Создаёт тег элемента ввода заданного типа.
     * 
     * @param string $type Атрибут типа.
     * @param string $name Атрибут имени. Если `null`, атрибут имени не будет сгенерирован
     *   (по умолчанию `null`).
     * @param string $value Атрибут значения. Если `null`, атрибут значения не будет 
     *   сгенерирован (по умолчанию `null`).
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег элемента ввода заданного типа.
     */
    public static function input(
        string $type, 
        ?string $name = null, 
        ?string $value = null, 
        array $attributes = []
    ): string
    {
        if (!isset($attributes['type'])) {
            $attributes['type'] = $type;
        }
        $attributes['name'] = $name;
        $attributes['value'] = $value === null ? null : (string) $value;
        return static::tag('input', '', $attributes);
    }

    /**
     * Создаёт кнопку ввода.
     * 
     * @param string $label Атрибут значения. Если `null`, атрибут значения не 
     *    будет сгенерирован (по умолчанию 'button').
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки ввода.
     */
    public static function buttonInput(string $label = 'Button', array $attributes =[]): string
    {
        $attributes['type'] = 'button';
        $attributes['value'] = $label;
        return static::tag('input', '', $attributes);
    }

    /**
     * Создаёт кнопку отправки данных.
     *
     * @param string $label Атрибут значения. Если `null`, атрибут значения не будет 
     *    сгенерирован (по умолчанию 'Submit').
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки отправки данных.
     */
    public static function submitInput(string $label = 'Submit', array $attributes = []): string
    {
        $attributes['type'] = 'submit';
        $attributes['value'] = $label;
        return static::tag('input', '', $attributes);
    }

    /**
     * Создаёт кнопку сброса данных.
     * 
     * @param string $label Атрибут значения. Если `null`, атрибут значения не будет 
     *   сгенерирован (по умолчанию 'Reset').
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки сброса данных.
     */
    public static function resetInput(string $label = 'Reset', array $attributes = []): string
    {
        $attributes['type'] = 'reset';
        $attributes['value'] = $label;
        return static::tag('input', '', $attributes);
    }

    /**
     * Создаёт поле ввода текста.
     * 
     * @param string $name Атрибут имени.
     * @param string $value Значение (по умолчанию `null`).
     * @param array<string, string> $attributes Атрибуты тега HTML (параметры HTML) 
     *    в виде пар "имя => значение". Они будут отображаться как атрибуты результирующего 
     *    тега. Значения будут закодированы в HTML с использованием {@see Html::encode()}. 
     *    Если значение `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег ввода текста.
     */
    public static function textInput(string $name, ?string $value = null, array $attributes = []): string
    {
        return static::input('text', $name, $value, $attributes);
    }

   /**
     * Создаёт скрытое поле ввода.
     * 
     * @see Html::input()
     * 
     * @param string $name Атрибут имени.
     * @param string $value Значение (по умолчанию `null`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий 
     *    атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный скрытый тег ввода.
     */
    public static function hiddenInput(string $name, ?string $value = null, array $options = []): string
    {
        return static::input('hidden', $name, $value, $options);
    }

   /**
     * Создаёт скрытое поле ввода для проверки CSRF токена.
     * 
     * @see Html::input()
     * 
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут 
     *    закодированы в HTML с использованием {@see Html::encode()}. Если значение 
     *    `null`, соответствующий атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный скрытый тег ввода для проверки CSRF токена. Если 
     *    значение {@see \Ge\Http\Request::$enableCsrfValidation} false, то результат 
     *    пустое значение.
     */
    public static function csrfInput(array $options = []): string
    {
        /** @var \Ge\Http\Request $request */
        $request = static::$app->request;
        // если проверка CSRF
        if ($request->enableCsrfValidation) {
            return static::input('hidden', $request->csrfParamName, $request->getCsrfToken(), $options);
        }
        return '';
    }

    /**
     * Создаёт поле ввода пароля.
     * 
     * @see Html::input()
     * 
     * @param string $name Атрибут имени.
     * @param string $value Значение (по умолчанию `null`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий 
     *    атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег ввода пароля.
     */
    public static function passwordInput(string $name, ?string $value = null, array $options = []): string
    {
        return static::input('password', $name, $value, $options);
    }

    /**
     * Создаёт поле ввода файла.
     * 
     * Чтобы использовать поле ввода файла, вы должны установить для атрибута "enctype" 
     * формы значение "multipart/form-data". После отправки формы, информация о загруженном файле 
     * можно получить через $ _FILES [$ name] (см. документацию PHP).
     * 
     * @see Html::input()
     * 
     * @param string $name Атрибут имени.
     * @param string $value Значение (по умолчанию `null`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий 
     *    атрибут не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег ввода файла.
     */
    public static function fileInput(string $name, ?string $value = null, array $options = []): string
    {
        return static::input('file', $name, $value, $options);
    }

    /**
     * Создаёт логическое поле ввода.
     * 
     * @param string $type Тип ввода. Это может быть либо `radio` или `checkbox`.
     * @param string $name Атрибут имени.
     * @param bool $checked Установка флажка (по умолчанию `false`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение". 
     *    Следующие параметры обрабатываются особым образом:
     *    - `uncheck`: string, значение, связанное с состоянием снятого флажка. Когда 
     *    этот атрибут присутствует, будет сгенерирован скрытый ввод (hidden input), 
     *    так что, если флажок не установлен и отправлен, значение этого атрибута все 
     *    равно будет отправлено на сервер через скрытый ввод (hidden input).
     *    - `label`: string, рядом с флажком отображается метка. Он не будет закодирован 
     *    в HTML. Поэтому вы можете передать HTML-код, например тег изображения. Если делает 
     *    это сам пользователь, следует использовать {@see Html::encode()}, чтобы 
     *    предотвратить XSS атаки. Когда этот параметр указан, флажок будет вложен в метку.
     *    - `labelOptions`: array, атрибуты HTML для тега метки. Не устанавливайте эту опцию, 
     *    если вы не установили опцию "label".
     *
     *    Остальные параметры будут отображаться как атрибуты, результирующего тега флажка. 
     *    Значения будут закодированы в HTML с использованием {@see Html::encode()}. Если 
     *    значение `null`, соответствующий атрибут не будет отображаться.
     *
     * @return string Cгенерированный тег логического поля ввода.
     */
    protected static function booleanInput(
        string $type, 
        string $name, 
        bool $checked = false, 
        array $options = []
    ): string
    {
        // опция "checked" имеет приоритет над аргументом $checked
        if (!isset($options['checked'])) {
            $options['checked'] = (bool) $checked;
        }
        $value = array_key_exists('value', $options) ? $options['value'] : '1';
        if (isset($options['uncheck'])) {
            // добавить скрытое поле, чтобы, если флажок не установлен, он все равно отправлял значение
            $hiddenOptions = array();
            if (isset($options['form'])) {
                $hiddenOptions['form'] = $options['form'];
            }
            // убедиться, что отключенный input не отправляет никакого значения
            if (!empty($options['disabled'])) {
                $hiddenOptions['disabled'] = $options['disabled'];
            }
            $hidden = static::hiddenInput($name, $options['uncheck'], $hiddenOptions);
            unset($options['uncheck']);
        } else {
            $hidden = '';
        }
        if (isset($options['label'])) {
            $label = $options['label'];
            $labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : array();
            unset($options['label'], $options['labelOptions']);
            $content = static::label(static::input($type, $name, $value, $options) . ' ' . $label, null, $labelOptions);
            return $hidden . $content;
        }
        return $hidden . static::input($type, $name, $value, $options);
    }

    /**
     * Создаёт радиокнопку формы.
     * 
     * @see Html::booleanInput()
     * 
     * @param string $name Атрибут имени.
     * @param bool $checked Установка переключателя (по умолчанию `false`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    См. {@see Html::booleanInput()} для получения подробной информации о принятых 
     *    атрибутах (по умолчанию `[]`).
     *
     * @return string Сгенерированный тег радиокнопки.
     */
    public static function radio(string $name, bool $checked = false, array $options = []): string
    {
        return static::booleanInput('radio', $name, $checked, $options);
    }

    /**
     * Создаёт флажок формы.
     * 
     * @see Html::booleanInput()
     * 
     * @param string $name Атрибут имени.
     * @param bool $checked Установка флажка (по умолчанию `false`).
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    См. {@see Html::booleanInput()} для получения подробной информации о принятых 
     *    атрибутах (по умолчанию `[]`).
     *
     * @return string Сгенерированный тег флажка.
     */
    public static function checkbox(string $name, bool $checked = false, array $options = []): string
    {
        return static::booleanInput('checkbox', $name, $checked, $options);
    }

    /**
     * Создаёт ввод текстовой области.
     * 
     * @see Html::tag()
     * 
     * @param string $name Атрибут имени.
     * @param string $value Входное значение, оно будет закодирован с использованием 
     *    {@see Html::encode()} (по умолчанию '').
     * @param array<string, string> $options Параметры тега в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут 
     *    закодированы в HTML с использованием {@see Html::encode()}. Если значение `null`, 
     *    соответствующий атрибут не будет отображаться.
     *    Специальные параметры:
     *    - `doubleEncode`: определяет, следует ли дважды кодировать объекты HTML в `$value`. 
     *    Если `false`, HTML сущности в `$value` не будут кодироваться.
     *
     * @return string Cгенерированный тег текстовой области.
     */
    public static function textarea(string $name, string $value = '', array $options = []): string
    {
        $options['name'] = $name;
        return static::tag('textarea', static::encode($value, $options['doubleEncode'] ?? true), $options);
    }

    /**
     * Добавдяет CSS класс в параметры.
     * 
     * @param array $options Параметры.
     * @param mixed $cssClass CSS класс.
     * 
     * @return void
     */
    public static function addCssClass(array &$options, mixed $cssClass): void
    {
        if ($cssClass) {
            if (is_array($cssClass)) {
                $cssClass = implode(' ', $cssClass);
            } else
                $cssClass = (string) $cssClass;
            if (isset($options['class']))
                $options['class'] = $options['class'] . ' ' . $cssClass;
            else
                $options['class'] = $cssClass;
        }
    }

    /**
     * @param string $name Имя тега.
     * @param array<string, mixed> $arguments Атрибуты тега.
     *
     * @return string
     */
    public static function __callStatic(string $name, array $arguments): string
    {
        return static::tag($name, $arguments[0] ?? '', $arguments[1] ?? []);
    }
}