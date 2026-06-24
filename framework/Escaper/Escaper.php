<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Escaper;

/**
 * Класс получения безопасных строк.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Escaper
 * @since 2.0
 */
class Escaper
{
    /**
     * Карта сущностей отображения представления Unicode в HTML формате.
     * 
     * @var array
     */
    protected static array $htmlNamedEntityMap = [
        34 => 'quot', // quotation mark
        38 => 'amp', // ampersand
        60 => 'lt', // less-than sign
        62 => 'gt', // greater-than sign
    ];

    /**
     * Текущая кодировка на выходе. Если не UTF-8, конвертируются строки из этой кодировки
     * перед экранированием и обратно в эту кодировку после экранирования
     * 
     * @var string
     */
    protected string $encoding = 'utf-8';

    /**
     * Содержит значение специальных флагов, переданных в качестве второго параметра
     * htmlspecialchars(). Езменено для версии PHP 5.4, чтобы воспользоваться
     * новым флагом ENT_SUBSTITUTE для корректной работы с последовательностью
     * UTF-8 символов
     *
     * @var string
     */
    protected string $htmlSpecialCharsFlags = ENT_QUOTES;

    /**
     * Matcher экранирующий символы для HTML-атрибутов
     *
     * @var callable
     */
    protected $htmlAttrMatcher;

    /**
     * Matcher экранирующий символы для Javascript
     *
     * @var callable
     */
    protected $jsMatcher;

    /**
     * Matcher экранирующий символы для атрибутов CSS
     *
     * @var callable
     */
    protected $cssMatcher;

    /**
     * Список всех поддерживаемых кодировок
     *
     * @var array
     */
    protected array $supportedEncodings = [
        'iso-8859-1',   'iso8859-1',    'iso-8859-5',   'iso8859-5',
        'iso-8859-15',  'iso8859-15',   'utf-8',        'cp866',
        'ibm866',       '866',          'cp1251',       'windows-1251',
        'win-1251',     '1251',         'cp1252',       'windows-1252',
        '1252',         'koi8-r',       'koi8-ru',      'koi8r',
        'big5',         '950',          'gb2312',       '936',
        'big5-hkscs',   'shift_jis',    'sjis',         'sjis-win',
        'cp932',        '932',          'euc-jp',       'eucjp',
        'eucjp-win',    'macroman'
    ];

    /**
     * Конструктор: единственный параметр позволяющий установить глобальную кодировку для использования текущим объектом.
     * the current object. Если версия PHP 5.4, устанавливается дополнительный флаг ENT_SUBSTITUTE
     * для вызова htmlspecialchars().
     *
     * @param null|string $encoding
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(?string $encoding = null)
    {
        if ($encoding !== null) {
            $encoding = (string) $encoding;
            if ($encoding === '') {
                throw new Exception\InvalidArgumentException(
                    get_class($this) . ' constructor parameter does not allow a blank value'
                );
            }

            $encoding = strtolower($encoding);
            if (!in_array($encoding, $this->supportedEncodings)) {
                throw new Exception\InvalidArgumentException(
                    'Value of \'' . $encoding . '\' passed to ' . get_class($this)
                    . ' constructor parameter is invalid. Provide an encoding supported by htmlspecialchars()'
                );
            }

            $this->encoding = $encoding;
        }

        if (defined('ENT_SUBSTITUTE')) {
            $this->htmlSpecialCharsFlags|= ENT_SUBSTITUTE;
        }

        // set matcher callbacks
        $this->htmlAttrMatcher = [$this, 'htmlAttrMatcher'];
        $this->jsMatcher       = [$this, 'jsMatcher'];
        $this->cssMatcher      = [$this, 'cssMatcher'];
    }

    /**
     * Возвращение кодировки в которой все выходные / входные данные должны быть закодированы
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Экранируйте строку для контекста HTML Body, в которой очень мало символов 
     * специального значения. Внутри это будет использовать htmlspecialchars().
     *
     * @param string $string
     * 
     * @return string
     */
    public function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, $this->htmlSpecialCharsFlags, $this->encoding);
    }

    /**
     * Экранирует строку для контекста атрибута HTML. Используется расширенный набор 
     * символов для экранирования, которые не охватываются htmlspecialchars(), 
     * чтобы охватить случаи, когда атрибут может быть не заключен в кавычки или 
     * заключен в кавычки намерено.
     *
     * @param string $string
     * 
     * @return string
     */
    public function escapeHtmlAttr(string $string): string
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', $this->htmlAttrMatcher, $string);
        return $this->fromUtf8($result);
    }

    /**
     * Экранируйте строку для контекста Javascript. Здесь не используется json_encode(). 
     * Расширенный набор символов экранируется за пределами правил ECMAScript для экранирования 
     * литеральных строк Javascript, чтобы предотвратить неверную интерпретацию Javascript 
     * как HTML, ведущую к внедрению специальных символов и вхождений. Используемое экранирование 
     * должно быть терпимым к случаям, когда экранирование HTML не применялось правильно поверх 
     * экранирования Javascript. Экранирование обратной косой черты не используется, поскольку 
     * оно по-прежнему оставляет экранированный символ как есть и поэтому бесполезно в контексте HTML.
     *
     * @param string $string
     * 
     * @return string
     */
    public function escapeJs(string $string): string
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9,\._]/iSu', $this->jsMatcher, $string);
        return $this->fromUtf8($result);
    }

    /**
     * Экранируйте строку для контекстов URI или параметра.
     *
     * @param string $string
     * 
     * @return string
     */
    public function escapeUrl(string $string): string
    {
        return rawurlencode($string);
    }

    /**
     * Экранируйте строку для контекста CSS. Экранирование CSS можно применить к любой 
     * строке, вставляемой в CSS, и экранирует все, кроме букв и цифр.
     *
     * @param string $string
     * 
     * @return string
     */
    public function escapeCss(string $string): string
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9]/iSu', $this->cssMatcher, $string);
        return $this->fromUtf8($result);
    }

    /**
     * Функция обратного вызова для preg_replace_callback, которая применяет экранирование 
     * атрибутов HTML ко всем совпадениям.
     *
     * @param array $matches
     * 
     * @return string
     */
    protected function htmlAttrMatcher(array $matches): string
    {
        $chr = $matches[0];
        $ord = ord($chr);

        /**
         * Заменяем символы, не определенные в HTML.
         */
        if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r")
            || ($ord >= 0x7f && $ord <= 0x9f)
        ) {
            return '&#xFFFD;';
        }

        if (strlen($chr) > 1) {
            $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
        }

        $hex = bin2hex($chr);
        $ord = hexdec($hex);
        if (isset(static::$htmlNamedEntityMap[$ord])) {
            return '&' . static::$htmlNamedEntityMap[$ord] . ';';
        }

        /**
         * Согласно рекомендациям OWASP, мы будем использовать шестнадцатеричное 
         * множенство для символов.
         */
        if ($ord > 255) {
            return sprintf('&#x%04X;', $ord);
        }
        return sprintf('&#x%02X;', $ord);
    }

    /**
     * Функция обратного вызова для preg_replace_callback, которая применяет экранирование 
     * Javascript ко всем совпадениям.
     *
     * @param array $matches
     * 
     * @return string
     */
    protected function jsMatcher(array $matches): string
    {
        $chr = $matches[0];
        if (strlen($chr) == 1) {
            return sprintf('\\x%02X', ord($chr));
        }
        $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
        return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
    }

    /**
     * Функция обратного вызова для preg_replace_callback, которая применяет экранирование 
     * CSS ко всем совпадениям.
     *
     * @param array $matches
     * 
     * @return string
     */
    protected function cssMatcher(array $matches): string
    {
        $chr = $matches[0];
        if (strlen($chr) == 1) {
            $ord = ord($chr);
        } else {
            $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
            $ord = hexdec(bin2hex($chr));
        }
        return sprintf('\\%X ', $ord);
    }

    /**
     * Преобразует строку в UTF-8 из базовой кодировки. Базовая кодировка устанавливается 
     * через конструктор этого класса.
     *
     * @param string $string
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    protected function toUtf8(string $string): string
    {
        if ($this->getEncoding() === 'utf-8') {
            $result = $string;
        } else {
            $result = $this->convertEncoding($string, 'UTF-8', $this->getEncoding());
        }

        if (!$this->isUtf8($result)) {
            throw new Exception\RuntimeException(
                sprintf('String to be escaped was not valid UTF-8 or could not be converted: %s', $result)
            );
        }

        return $result;
    }

    /**
     * Преобразует строку из UTF-8 в базовую кодировку. Базовая кодировка устанавливается 
     * через конструктор этого класса.
     * 
     * @param string $string
     * 
     * @return string
     */
    protected function fromUtf8(string $string): string
    {
        if ($this->getEncoding() === 'utf-8') {
            return $string;
        }

        return $this->convertEncoding($string, $this->getEncoding(), 'UTF-8');
    }

    /**
     * Проверяет, является ли данная строка допустимой UTF-8 или нет.
     *
     * @param string $string
     * 
     * @return bool
     */
    protected function isUtf8(string $string): bool
    {
        return ($string === '' || preg_match('/^./su', $string));
    }

    /**
     * Оборачивает iconv и mbstring там, где они существуют, или выдает исключени, 
     * когда ни одно из них недоступно.
     *
     * @param string $string
     * @param string $to
     * @param array|string $from
     * 
     * @return string
     * 
     * @throws Exception\RuntimeException
     */
    protected function convertEncoding(string $string, string $to, array|string $from): string
    {
        if (function_exists('iconv')) {
            $result = iconv($from, $to, $string);
        } elseif (function_exists('mb_convert_encoding')) {
            $result = mb_convert_encoding($string, $to, $from);
        } else {
            throw new Exception\RuntimeException(
                get_class($this)
                . ' requires either the iconv or mbstring extension to be installed'
                . ' when escaping for non UTF-8 strings.'
            );
        }

        if ($result === false) {
            return ''; // возвращать нефатальную пустую строку при ошибках кодирования от пользователей
        }
        return $result;
    }
}
