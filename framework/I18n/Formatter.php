<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear Software
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n;

use Ge;
use DateTime;
use IntlCalendar;
use IntlTimeZone;
use DateTimeZone;
use DateInterval;
use NumberFormatter;
use DateTimeInterface;
use DateTimeImmutable;
use IntlDateFormatter;
use Ge\Exception;
use Ge\Stdlib\Service;
use Ge\Helper\Converter;
use Ge\I18n\Source\BaseSource as MessageSource;

/**
 * Форматтер предоставляет набор часто используемых методов форматирования данных.
 * 
 * Formatter - это служба приложения, доступ к которой можно получить через `Ge::$app->formatter`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n
 * @since 2.0
 */
class Formatter extends Service
{
    /**
     * @var int При расчёте информации учитывается, что в килобайте 1024 байт.
     */
    public const SIZE_DATA_UNIT_BINARY = 1024;

    /**
     * @var int При расчёте информации учитывается, что в килобайте 1000 байт.
     */
    public const SIZE_DATA_UNIT_DECIMAL = 1000;

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Строковый формат даты.
     * 
     * Используется для форматирования {@see Formatter::(toDate)()}.
     * 
     * Формат может быть: "short", "medium", "long" или "full". Можно указать свой 
     * формат согласно {@link http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax руководству ICU}.
     * 
     * В качестве альтернативы может быть строка с префиксом "php:", представляющая 
     * формат, который может быть распознан функцией {@link https://www.php.net/manual/ru/function.date.php PHP date}.
     * 
     * Например:
     *    - "MM/dd/yyyy" дата в формате ICU;
     *    - "php:m/d/Y" дата в формате PHP.
     */
    public string $dateFormat = 'd.m.Y';

    /**
     * Строковый формат времени.
     * 
     * Используется для форматирования {@see Formatter::toTime()}.
     * 
     * Формат может быть: "short", "medium", "long" или "full". Можно указать свой 
     * формат согласно {@link http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax руководству ICU}.
     * 
     * В качестве альтернативы может быть строка с префиксом "php:", представляющая 
     * формат, который может быть распознан функцией {@link https://www.php.net/manual/ru/function.date.php PHP date}.
     * 
     * Например:
     *    - "HH:mm:ss" время в формате ICU;
     *    - "php:H:i:s" время в формате PHP.
     */
    public string $timeFormat = 'H:i:s';

    /**
     * Строковый формат даты и времени.
     * 
     * Используется для форматирования {@see Formatter::toDateTime()}, и 
     * получется в результате сложения форматов {@see Formatter::$dateFormat} и 
     * {@see Formatter::$timeFormat}.
     * 
     * Формат может быть: "short", "medium", "long" или "full". Можно указать свой формат 
     * согласно {@link http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax руководству ICU}.
     * 
     * В качестве альтернативы может быть строка с префиксом "php:", представляющая 
     * формат, который может быть распознан функцией {@link https://www.php.net/manual/ru/function.date.php PHP date}.
     * 
     * Например:
     *    - "MM/dd/yyyy HH:mm:ss" дата и время в формате ICU;
     *    - "php:m/d/Y H:i:s" дата и время в формате PHP.
     */
    public string $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * Календарь, который будет использоваться для форматирования даты.
     * 
     * Значение свойства будет напрямую передано в конструктор класса 
     * {@link https://www.php.net/manual/ru/intldateformatter.create.php "IntlDateFormatter"}
     * 
     * По умолчанию null, что означает, что будет использоваться григорианский календарь. 
     * Также можно передать константу "\IntlDateFormatter::GREGORIAN" на прямую в календарь. 
     * Чтобы использовать альтернативный календарь например: календарь Джалали, необходимо 
     * установить свойству значение "\IntlDateFormatter::TRADITIONAL".
     * 
     * Доступные названия календарей можно найти в {@link http://userguide.icu-project.org/datetime/calendar руководстве ICU}.
     * 
     * @see https://www.php.net/manual/ru/intldateformatter.create.php
     * @see https://www.php.net/manual/ru/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
     * @see https://www.php.net/manual/ru/class.intlcalendar.php
     * 
     * @var IntlCalendar|int|null
     */
    public IntlCalendar|int|null $calendar = null;

    /**
     * Часовой пояс, используемый для форматирования значений времени и даты.
     * 
     * Это может быть любое значение, переданное в {@link https://www.php.net/manual/ru/function.date-default-timezone-set.php date_default_timezone_set()}
     * такое как "UTC", "Europe/Moscow" или "Europe/Berlin".
     * 
     * @see Formatter::init()
     * 
     * @var DateTimeZone|string
     */
    public DateTimeZone|string $timeZone;

    /**
     * Первый день недели.
     * 
     * @var string
     */
    public string $firstWeekDay = 'monday';

    /**
     * Формат едениц измерения информации.
     * 
     * Информация рассчитывается в килобайтах (1000 или 1024 байт на килобайт), 
     * с помощью {@see Formatter::toShortSizeDataUnit()} и {@see Formatter::toSizeDataUnit()}.
     * По умолчанию 1024.
     * 
     * @var int
     */
    public int $sizeDataUnitFormat = self::SIZE_DATA_UNIT_DECIMAL;

    /**
     * Текст, который будет отображаться при форматировании логического значения.
     * 
     * Первый элемент соответствует тексту, отображаемому для «false», второй элемент для «true».
     * По умолчанию ['No', 'Yes'], переводится согласно локали {@see Formatter::$locale}.
     * 
     * @see Fomratter::toBoolean()
     * 
     * @var array
     */
    public array $booleanFormat;

    /**
     * Код локализации.
     * 
     * @see \Ge\Language\Language::$parameters
     * 
     * @var string
     */
    public string $locale = '';

    /**
     * Предыдущие значение часового пояса.
     * 
     * Устанавливается в {@see Formatter::setTimeZone()} как предыдущие значение 
     * часового пояса.
     * 
     * @var DateTimeZone|string|null
     */
    protected DateTimeZone|string|null $_timeZone = null;

    /**
     * Часто вызываемые форматтеры расширения PHP intl.
     * 
     * Форматтеры созданные расширением PHP intl (NumberFormatter, IntlDateFormatter...).
     * Каждый форматтер вызывается по ключу, созданный одним из методов {@see Formatter}.
     * 
     * @var array
     */
    protected array $formatters = [];

    /**
     * Бросать исключение если возникла ошибка при форматировании с 
     * помощью расширения PHP intl.
     * 
     * Если false, ошибка будет указана в {@see Formatter::$error}.
     * 
     * @var bool
     */
    public bool $throwException = true;

    /**
     * Ошибка последнего форматирования с помощью расширения intl.
     * 
     * Ошибка возникает при вызове NumberFormatter::format 
     * имеет вид: ["код ошибки", "текст ошибки"].
     * 
     * @var array
     */
    public array $error = [];

    /**
     * Символ, отображаемый как десятичная точка при форматировании числа.
     * 
     * Если не установлен, будет использоваться десятичный разделитель, соответствующий 
     * локали {@see Formatter::$locale}.
     * Если расширение PHP intl {@link https://www.php.net/manual/ru/book.intl.php} не 
     * доступно, тогда по умолчанию будет ".".
     * 
     * @var string|null
     */
    public ?string $decimalSeparator = null;

    /**
     * Cимвол, отображаемый как разделитель тысяч (также называемый разделителем групп) при 
     * форматировании числа.
     * 
     * Если не установлен, будет использоваться разделитель тысяч, соответствующий 
     * локали {@see Formatter::$locale}.
     * Если расширение PHP intl {@link https://www.php.net/manual/ru/book.intl.php} не 
     * доступно, тогда по умолчанию будет ",".
     * 
     * @var string|null
     */
    public ?string $thousandsSeparator = null;

    /**
     * Источника локализации сообщений (шаблонов форматов).
     * 
     * Применяется для локализации шаблонов форматов.
     * 
     * @see Formatter::addFormatPatterns()
     * 
     * @var MessageSource
     */
    public MessageSource $messageSource;

    /**
     * Загружено ли расширение intl PHP.
     * 
     * @link https://www.php.net/manual/ru/book.intl.php
     * 
     * @var bool
     */
    protected bool $intlIsLoaded = false;

    /**
     * Использовать расширение intl PHP при форматирование 
     * (если расширение загружено).
     * 
     * Если значение `false`, форматирование будет без использования расширения 
     * intl PHP.
     * 
     * @var bool
     */
    protected bool $useIntl = true;

    /**
     * Были ли добавлены к переводчику шаблон перевода форматов.
     * 
     * @see Formatter::addFormatPatterns()
     * 
     * @var bool
     */
    protected bool $formatPatternsAdded = false;

    /**
     * Часовой пояс UTC.
     * 
     * @see Formatter::init()
     *
     * @var DateTimeZone
     */
    public DateTimeZone $timeZoneUTC;

    /**
     * Cопоставление имен кратких форматов со значениями констант IntlDateFormatter.
     * 
     * @var array
     */
    private array $intlDateFormats = [
        'short'  => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long'   => 1, // IntlDateFormatter::LONG,
        'full'   => 0 // IntlDateFormatter::FULL
    ];

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'formatter';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->setIntIsLoaded(extension_loaded('intl'));
        // часовой пояс в UTC
        $this->timeZoneUTC = new DateTimeZone('UTC');
        // текущий часовой пояс
        if (is_string($this->timeZone)) {
            $this->timeZone = new DateTimeZone($this->timeZone);
        }
        if ($this->locale === '') {
            $this->locale = Ge::$services->getAs('language')->get('locale', '');
        }
    }

    /**
     * Возвращает название текущего часового пояса.
     * 
     * @return string|null
     */
    public function getTimeZone(): ?string
    {
        return $this->timeZone ? $this->timeZone->getName() : null;
    }

    /**
     * Добавляет к переводчику шаблон перевода форматов.
     * 
     * @see \Ge\I18n\Translator\Source\BaseSource::addLocalePatterns()
     * @see Formatter::$formatPatternsAdded
     * 
     * @return $this
     */
    protected function addFormatPatterns(): static
    {
        if (isset($this->messageSource) && !$this->formatPatternsAdded) {
            $this->messageSource->addLocalePatterns('format');
            $this->formatPatternsAdded = true;
        }
        return $this;
    }

    /**
     * Указывает, что расширение intl PHP подключено.
     * 
     * @see Formatter::$intlIsLoaded
     * 
     * @param bool $value Если значение `true`, расширение intl PHP подключено.
     * 
     * @return void
     */
    public function setIntIsLoaded(bool $value): void
    {
        $this->intlIsLoaded = $value;
        if ($value) {
            $this->decimalSeparator = $this->decimalSeparator === null ? '.' : $this->decimalSeparator;
            $this->thousandsSeparator = $this->thousandsSeparator === null ? ',' : $this->thousandsSeparator;
            if ($this->calendar === null)
                $this->calendar = IntlDateFormatter::TRADITIONAL;
        }
    }

    /**
     * Устанавливает часовой пояс с возможностью восстановления предыдущего.
     * 
     * Восстановление ранее установленого часового пояса {@see Formatter::recoveryTimeZone()}.
     * 
     * @param DateTimeZone|string $timeZone Часовой пояс.
     * 
     * @return void
     */
    public function setTimeZone(DateTimeZone|string $timeZone): void
    {
        $this->_timeZone = $this->timeZone;
        $this->timeZone = is_string($timeZone) ? new DateTimeZone($timeZone) : $timeZone;
    }

    /**
     * Восстановливает ранее установленый часовой пояс.
     * 
     * @return void
     */
    public function recoveryTimeZone(): void
    {
        $this->timeZone = $this->_timeZone;
    }

    /**
     * Возвращает все доступные часовые пояса.
     * 
     * Все часовые пояса со смещение по времени относительно часового пояса UTC.
     * 
     * @return array
     */
    public function getTimeZones(): array
    {
        // сохраняем часовой пояси клиента и расчёт делаем в UTC
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $dateTimeUTC = new DateTime('now');
        $result = [];
        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $dateTimeZone = new DateTimeZone($timezone);
            if ($this->intlIsLoaded && $this->useIntl) {
                $intlTimeZone = \IntlTimeZone::createTimeZone($timezone);
                if ($intlTimeZone->getID() === 'Etc/Unknown' or $timezone === 'UTC')
                    $name = $timezone;
                else
                    $name = $intlTimeZone->getDisplayName(false, 3, $this->locale);
            } else
                $name = $timezone;
            // смещение
            $offset = $dateTimeZone->getOffset($dateTimeUTC);
            if ($offset)
                $offsetTime = date('H:i', abs($offset));
            else
                $offsetTime = '00:00';
           $result[] = ['timezone' => $timezone, 'name' => $name, 'offsetTime' => (($offset < 0) ? '-' : '+') . $offsetTime, 'offset' => $offset];
        }
        // восстанавливаем часовой пояс клиенту
        date_default_timezone_set($defaultTimezone);
        return $result;
    }

    /**
     * Форматирует значение в дату с указанным интервалом.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp (всегда в формате UTC);  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * 
     *    Форматтер преобразует значения даты в соответствии с указанным часовым поясом
     *    `$timeZone`. Если часовой пояс не указан, будет применяться текущий часовой 
     *    пояс {@see Formatter::$timeZone}.
     * @param string $interval Интервал {@link https://www.php.net/manual/ru/dateinterval.construct.php}, 
     *    отрицательный интервал с приставкой '-'.
     * @param null|string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param DateTimeZone|string|null $timeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     *
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Если указанное значение даты не 
     *    является датой и не подлежит форматированию или неправильно указано значение $timeZone.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного формата времени.
     */
    public function toDateInterval(
        DateTimeInterface|string|int $value, 
        string $interval, 
        ?string $format = null, 
        DateTimeZone|string|null $timeZone = null
    ): string
    {
        if ($timeZone === null)
            $timeZone = $this->timeZone;
        else {
            if (is_string($timeZone))
            $timeZone = new DateTimeZone($timeZone);
            else
            if (!$timeZone instanceof DateTimeZone)
                throw new Exception\InvalidArgumentException('The value of the $toTimeZone parameter is incorrect, has the wrong type');
        }

        if ($format === null) {
            $format = $this->dateFormat;
        }

        /** @var DateTime $dateTime с часовым поясом UTC */
        $dateTime = $this->normalizeDateTimeValue($value);
        if (empty($dateTime)) {
            return $value;
        }

        if ($dateTime instanceof DateTimeImmutable)
            $dateTime = $dateTime->setTimezone($timeZone);
        else
            $dateTime->setTimezone($timeZone);

        // если указано смещение по интервалу '-'
        if (strncmp($interval, '-', 1) === 0) {
            $dateTime->sub(new DateInterval(substr($interval, 1)));
        } else
            $dateTime->add(new DateInterval($interval));
        return $dateTime->format($format);
    }

    /**
     * Форматирует значение в дату.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp (всегда в формате UTC);  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * 
     *    Форматтер преобразует значение даты в соответствии с указанным часовым поясом
     *    `$timeZone`. Если часовой пояс не указан, будет применяться текущий часовой 
     *    пояс {@see Formatter::$timeZone}.
     * @param null|string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param bool $normalize Если значение `true`, значение даты пройдет нормализацию и 
     *    форматирование (с использованием PHP intl), иначе форматирование с помощью 
     * {@link https://www.php.net/manual/ru/function.date} (по умолчанию `true`).
     * @param DateTimeZone|string|null $timeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     *
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Если указанное значение даты не 
     *    является датой и не подлежит форматированию.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного формата времени.
     */
    public function toDate(
        DateTimeInterface|string|int $value, 
        ?string $format = null, 
        bool $normalize = true, 
        DateTimeZone|string|null $timeZone = null
    ): string
    {
        if ($format === null) {
            $format = $this->dateFormat;
        }

        if ($normalize)
            return $this->formatDateTimeValue($value, $format, 'date', $timeZone);
        else
            return $this->_formatDateTimeValue($value, $format, $timeZone);
    }

    /**
     * Форматирует значение в время.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp (всегда в формате UTC);  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * 
     *    Форматтер преобразует значение времени в соответствии с указанным часовым поясом
     *    `$timeZone`. Если часовой пояс не указан, будет применяться текущий часовой 
     *    пояс {@see Formatter::$timeZone}.
     * @param null|string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param bool $normalize Если значение `true`, значение даты пройдет нормализацию и 
     *    форматирование (с использованием PHP intl), иначе форматирование с помощью 
     * {@link https://www.php.net/manual/ru/function.date} (по умолчанию `true`).
     * @param DateTimeZone|string|null $timeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     *
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Если указанное значение даты не 
     *    является датой и не подлежит форматированию.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного 
     *    формата времени.
     */
    public function toTime(
        DateTimeInterface|string|int $value, 
        ?string $format = null, 
        bool $normalize = true, 
        DateTimeZone|string|null $timeZone = null
    ): string
    {
        if ($format === null) {
            $format = $this->timeFormat;
        }

        if ($normalize)
            return $this->formatDateTimeValue($value, $format, 'time', $timeZone);
        else
            return $this->_formatDateTimeValue($value, $format, $timeZone);
    }

    /**
     * Форматирует значение даты и времени в часовой пояс UTC.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp;  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * @param null|string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param bool $normalize Если значение `true`, значение даты пройдет нормализацию и 
     *    форматирование (с использованием PHP intl), иначе форматирование с помощью 
     *    {@link https://www.php.net/manual/ru/function.date} (по умолчанию `true`).
     * @param DateTimeZone|string|null $fromTimeZone Часовой пояс в котором находится 
     *    значение для форматирования. Если часовой пояс имеет значение `null`, то
     *    применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     * 
     * @return string
     */
    public function toTimeUTC(
        DateTimeInterface|string|int $value, 
        ?string $format = null, 
        bool $normalize = true, 
        DateTimeZone|string|null $fromTimeZone = null
    ): string
    {
        if ($format === null) {
            $format = $this->timeFormat;
        }
    
        // из часового пояса
        if ($fromTimeZone === null)
            $fromTimeZone = $this->timeZone;
        else
            $fromTimeZone = is_string($fromTimeZone) ? new DateTimeZone($fromTimeZone) : $fromTimeZone;

        if ($normalize)
            return $this->formatDateTimeValue($value, $format, 'datetime', $this->timeZoneUTC);
        else
            return $this->_formatDateTimeValue($value, $format, $this->timeZoneUTC);
    }

    /**
     * Форматирует дату, время или дату и время в число с плавающей точкой, как UNIX 
     * timestamp (секунды с 01.01.1970).
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp;  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * @param bool $floatNumber Если значение `true` будет добавлена плавающая точка
     *    (по умолчанию `false`).
     *
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Если входное значение не представлено как 
     *     значение даты и времени.
     */
    public function toTimestamp(DateTimeInterface|string|int $value, bool $floatNumber = false): ?string
    {
        if ($value === null) {
            return $value;
        }

        $timestamp = $this->normalizeDatetimeValue($value);
        if ($floatNumber)
            return number_format($timestamp->format('U'), 0, '.', '');
        else
            return $timestamp->format('U');
    }

    /**
     * Форматирует значение в дату и время.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp;  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * @param null|string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param bool $normalize Если значение `true`, значение даты пройдет нормализацию и 
     *    форматирование (с использованием PHP intl), иначе форматирование с помощью 
     *    {@link https://www.php.net/manual/ru/function.date} (по умолчанию `true`).
     * @param DateTimeZone|string|null $timeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     *
     * @return string
     * .
     * @throws Exception\InvalidArgumentException Если указанное значение даты не 
     *    является датой и не подлежит форматированию.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного 
     *    формата времени.
     */
    public function toDateTime(
        DateTimeInterface|string|int $value, 
        ?string $format = null, 
        bool $normalize = true, 
        DateTimeZone|string|null $timeZone = null
    ): string
    {
        if ($format === null) {
            $format = $this->dateTimeFormat;
        }

        if ($normalize) {
            return $this->formatDateTimeValue($value, $format, 'datetime', $timeZone);
        } else
            return $this->_formatDateTimeValue($value, $format, $timeZone);
    }

    /**
     * Форматирует значение в дату и время из часового пояса в указанный.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp;  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * @param string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param bool $normalize Если значение `true`, значение даты пройдет нормализацию и 
     *    форматирование (с использованием PHP intl), иначе форматирование с помощью 
     *    {@link https://www.php.net/manual/ru/function.date} (по умолчанию `true`).
     * @param DateTimeZone|string|null $fromTimeZone Часовой пояс форматируемого значения. 
     *    Если значение `null`, часовой пояс соответствует {@see Formatter::$timeZoneUTC}
     *    (по умолчанию `null`).
     * @param DateTimeZone|string|null $toTimeZone Часовой пояс в который форматируется 
     *    значение. Если значение часового пояса `null`, то применяется {@see Formatter::$timeZone}
     *    (по умолчанию `null`).
     * 
     * @return string
     * .
     * @throws Exception\InvalidArgumentException Если указанное значение даты не 
     *    является датой и не подлежит форматированию.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного 
     *    формата времени.
     */
    public function toDateTimeZone(
        DateTimeInterface|string|int $value, 
        ?string $format = null, 
        bool $normalize = true, 
        DateTimeZone|string|null $fromTimeZone = null, 
        DateTimeZone|string|null $toTimeZone = null
    ): string
    {
        if ($format === null) {
            $format = $this->dateTimeFormat;
        }
        // из часового пояса
        if ($fromTimeZone === null)
            $fromTimeZone = $this->timeZoneUTC;
        else
            $fromTimeZone = is_string($fromTimeZone) ? new DateTimeZone($fromTimeZone) : $fromTimeZone;
        // в часовой пояс
        if ($toTimeZone === null)
            $toTimeZone = $this->timeZone;
        else
            $toTimeZone = is_string($toTimeZone) ? new DateTimeZone($toTimeZone) : $toTimeZone;

        if ($value instanceof DateTimeImmutable)
            $value = $value->setTimezone($fromTimeZone);
        else
        if ($value instanceof DateTime)
            $value->setTimezone($fromTimeZone);
        else
            $value = new DateTime($value, $fromTimeZone);

        if ($normalize)
            $datetime = $this->formatDateTimeValue($value, $format, 'datetime', $toTimeZone);
        else
            $datetime = $this->_formatDateTimeValue($value, $format, $toTimeZone);
        return $datetime;
    }

    /**
     * Создает средство форматирования чисел на основе заданного типа и формата.
     *
     * @param int $style Тип форматирования чисел.
     *    Тип может быть:
     *       - NumberFormatter::PATTERN_DECIMAL (формат с десятичной точкой заданный шаблоном);
     *       - NumberFormatter::DECIMAL (формат с десятичной точкой);
     *       - NumberFormatter::CURRENCY (денежный формат);
     *       - NumberFormatter::PERCENT (процентный формат);
     *       - NumberFormatter::SCIENTIFIC (научный формат);
     *       - NumberFormatter::SPELLOUT (разобранный формат на основе правил);
     *       - NumberFormatter::ORDINAL (числительный формат на основе правил);
     *       - NumberFormatter::DURATION (формат длительности на основе правил);
     *       - NumberFormatter::PATTERN_RULEBASED (формат на основе правил по шаблону);
     *       - NumberFormatter::CURRENCY_ACCOUNTING (формат валюты для учета);
     *       - NumberFormatter::DEFAULT_STYLE (формат по умолчанию для локали);
     *       - NumberFormatter::IGNORE (псевдоним для PATTERN_DECIMAL).
     * @param null|int $decimals Количество цифр после запятой (устанавливается 
     *    для: NumberFormatter::MAX_FRACTION_DIGITS, ::MIN_FRACTION_DIGITS).
     * @param array $options Опции форматирования чисел.
     * 
     * @return NumberFormatter
     */
    public function createNumberFormatter(int $style, ?int $decimals = null, array $options = [], array $textOptions = []): NumberFormatter
    {
        $formatterId = "$style\0{$this->locale}\0$decimals";

        if (!isset($this->formatters[$formatterId])) {
            $this->formatters[$formatterId] = new NumberFormatter($this->locale, $style);
        }
        $formatter = $this->formatters[$formatterId];
        // опции форматирования чисел
        foreach ($options as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        // атрибуты текста
        foreach ($textOptions as $name => $value) {
            $formatter->setTextAttribute($name, $value);
        }
        // символы, связанный с форматером
        if ($this->decimalSeparator !== null) {
            $formatter->setSymbol(
                NumberFormatter::DECIMAL_SEPARATOR_SYMBOL,
                $this->decimalSeparator
            );
        }
        if ($this->thousandsSeparator !== null) {
            $formatter->setSymbol(
                NumberFormatter::GROUPING_SEPARATOR_SYMBOL,
                $this->thousandsSeparator
            );
            $formatter->setSymbol(
                NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL,
                $this->thousandsSeparator
            );
        }

        // максимальное и минимальное число цифр после запятой
        if ($decimals !== null) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        }
        return $formatter;
    }

    /**
     * Создает средство форматирования даты и времени.
     *
     * @param int|string $dateType Тип даты.
     *    Тип может быть:
     *    - IntlDateFormatter::NONE (не включать этот элемент);
     *    - IntlDateFormatter::FULL (полный формат (Tuesday, April 12, 1952 AD or 3:30:42pm PST));
     *    - IntlDateFormatter::LONG (длинный формат (January 12, 1952 or 3:30:32pm));
     *    - IntlDateFormatter::MEDIUM (средний формат (Jan 12, 1952));
     *    - IntlDateFormatter::SHORT (наиболее сокращенный формат (12/13/52 или 3:30pm)).
     * @param int|string $timeType Тип времени, такой же как Тип даты (none, short, medium, long, full).
     * @param IntlTimeZone|DateTimeZone|null $timeZone Часовой пояс (по умолчанию `null`).
     * @param IntlCalendar|int|null $calendar Тип календаря (по умолчанию `null`).
     *    Тип может быть:
     *    - IntlDateFormatter::TRADITIONAL (не Григорианский календарь);
     *    - IntlDateFormatter::GREGORIAN (Григорианский календарь) или `null`.
     * @param string $format
     * 
     * @return IntlDateFormatter
     */
    public function createDateFormatter(
        int|string $dateType, 
        int|string $timeType, 
        IntlTimeZone|DateTimeZone|null $timeZone = null, 
        IntlCalendar|int|null $calendar = null, 
        string $format = ''
    ): IntlDateFormatter
    {
        if ($timeZone) {
            if ($timeZone instanceof DateTimeZone)
                $strTimeZone = $timeZone->getName();
            else
            if ($timeZone instanceof IntlTimeZone) 
                $strTimeZone = $timeZone->getDisplayName();
            else
                $strTimeZone = 'null';
        } else
            $strTimeZone = 'null';

        $formatterId = "{$this->locale}\0$dateType\0$timeType\0$strTimeZone\0$calendar\0$format";

        if (!isset($this->formatters[$formatterId])) {
            $dateType = $this->intlDateFormats[$dateType] ?? $dateType;
            $timeType = $this->intlDateFormats[$timeType] ?? $timeType;
            if ($calendar === null) {
                $calendar = $this->calendar;
            }
            $this->formatters[$formatterId] = new IntlDateFormatter($this->locale, $dateType,
                $timeType, $timeZone, $calendar, $format);
        }
        return $this->formatters[$formatterId];
    }

    /**
     * Форматирует значение как десятичное число.
     *
     * @param mixed $value Форматируемое значение.
     * @param int $decimals Количество цифр после запятой 
     *     (если используется расширение PHP intl, устанавливается свойствам 
     *     класса: NumberFormatter::MAX_FRACTION_DIGITS, ::MIN_FRACTION_DIGITS). Если 
     *     расширение PHP intl не доступно, значение будет "2".
     * @param array $options Опции форматирования чисел (только для класса NumberFormatter 
     *     расширения PHP intl, по умолчанию `[]`).
     * @param array $textOptions Опции текста в форматировании числа (только для класса 
     *     NumberFormatter расширения PHP intl, по умолчанию `[]`).
     * 
     * @return string Результирующие значение формата.
     * 
     * @throws Exception\InvalidArgumentException Если значение не является числом или 
     *    ошибка форматирования.
     */
    public function toDecimal(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        if ($this->intlIsLoaded && $this->useIntl) {
            $formatter = $this->createNumberFormatter(NumberFormatter::DECIMAL, $decimals, $options, $textOptions);
            if (($result = $formatter->format($normValue)) === false) {
                if ($this->throwException) {
                    throw new Exception\InvalidArgumentException(sprintf('Error formatting decimal number: %s (%s)',
                        $formatter->getErrorMessage(), $formatter->getErrorCode()));
                }
                $this->error = [$formatter->getErrorCode(), $formatter->getErrorMessage()];
                return $normValue;
            }
            return $result;
        }
        return number_format(
            $normValue, 
            $decimals === null ? 2 : $decimals, 
            $this->decimalSeparator, 
            $this->thousandsSeparator
        );
    }

    /**
     * Форматирования числа на основе заданного типа и формата.
     *
     * @param mixed $value Форматируемое число.
     * @param int $style Стиль форматирования (NumberFormatter::PATTERN_DECIMAL, ::DECIMAL, ::CURRENCY, ::PERCENT...) 
     *    {@link https://www.php.net/manual/ru/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle}.
     * @param int $decimals Количество цифр после запятой 
     *     (если используется расширение PHP intl, устанавливается свойствам класса: 
     *     NumberFormatter::MAX_FRACTION_DIGITS, ::MIN_FRACTION_DIGITS, по умолчанию `null`).
     * @param array $options Опции форматирования чисел (только для класса NumberFormatter 
     *     расширения PHP intl, по умолчанию `[]`).
     * @param array $textOptions Опции текста в форматировании числа (только для класса 
     *     NumberFormatter расширения PHP intl, по умолчанию `[]`).
     * 
     * @return string Результирующие значение формата.
     * 
     * @throws Exception\InvalidArgumentException Если значение не является числом 
     *    или ошибка форматирования.
     */
    public function toNumber(mixed $value, int $style, ?int $decimals = null, array $options = [], array $textOptions = []): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        if ($this->intlIsLoaded && $this->useIntl) {
            $formatter = $this->createNumberFormatter($style, $decimals, $options, $textOptions);
            if (($result = $formatter->format($normValue)) === false) {
                if ($this->throwException) {
                    throw new Exception\InvalidArgumentException(sprintf('Number formatting error: %s (%s)',
                        $formatter->getErrorMessage(), $formatter->getErrorCode()));
                }
                $this->error = [$formatter->getErrorCode(), $formatter->getErrorMessage()];
                return $normValue;
            }
            return $result;
        }
        return number_format($normValue, $decimals, $this->decimalSeparator, $this->thousandsSeparator);
    }

    /**
     * Форматирует значение в виде числа прописью (пример: "23" => "двадцать три").
     *
     * Форматирование только с расширением PHP intl {@link https://www.php.net/manual/ru/book.intl.php}.
     *
     * @param mixed $value Форматируемое значение.
     * 
     * @return string Результирующие значение формата.
     * 
     * @throws Exception\InvalidArgumentException Если форматирование без расширения 
     *     PHP intl или ошибка форматирования.
     */
    public function toSpellout(mixed $value): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        if ($this->intlIsLoaded) {
            $formatter = $this->createNumberFormatter(NumberFormatter::SPELLOUT);
            if (($result = $formatter->format($normValue)) === false) {
                if ($this->throwException) {
                    throw new Exception\InvalidArgumentException(sprintf('Spellout formatting error: %s (%s)',
                        $formatter->getErrorMessage(), $formatter->getErrorCode()));
                }
                $this->error = [$formatter->getErrorCode(), $formatter->getErrorMessage()];
                return $normValue;
            }
            return $result;
        }
        if ($this->throwException) {
            throw new Exception\InvalidArgumentException('Format Spellout is only supported PHP intl extension.');
        }
        $this->error = [0, 'Format Spellout is only supported PHP intl extension.'];
        return $value;
    }

    /**
     * Форматирует значение как научное число.
     *
     * @param mixed $value Форматируемое значение.
     * @param int $decimals Количество цифр после запятой (если используется расширение 
     *     PHP intl, устанавливается свойствам класса: NumberFormatter::MAX_FRACTION_DIGITS, 
     *     ::MIN_FRACTION_DIGITS, по умолчанию `null`).
     * @param array $options Опции форматирования чисел (только для класса NumberFormatter 
     *     расширения PHP intl, по умолчанию `[]`).
     * @param array $textOptions Опции текста в форматировании числа (только для класса 
     *     NumberFormatter расширения PHP intl, по умолчанию `[]`).
     * 
     * @return string Результирующие значение формата.
     * 
     * @throws Exception\InvalidArgumentException Если значение не является числом или ошибка форматирования.
     */
    public function toScientific(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        if ($this->intlIsLoaded) {
            $formatter = $this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $decimals,
                $options, $textOptions);
            if (($result = $formatter->format($normValue)) === false) {
                if ($this->throwException) {
                    throw new Exception\InvalidArgumentException(sprintf('Scientific formatting error: %s (%s)',
                        $formatter->getErrorMessage(), $formatter->getErrorCode()));
                }
                $this->error = [$formatter->getErrorCode(), $formatter->getErrorMessage()];
                return $normValue;
            }
            return $result;
        }
        return $decimals === null ? sprintf('%.E', $value) : sprintf("%.{$decimals}E", $value);
    }

    /**
     * Форматирует значение в байтах, в легко воспринимаемой форме (например «270 килобайт»).
     * 
     * Форматирование только с расширением PHP intl {@link https://www.php.net/manual/ru/book.intl.php}.
     * 
     * В зависимости от того, какой формат едениц расчёта информации используется 
     * {@see Formatter::$sizeDataUnitFormat}, будет к результату добавлен 
     * префикс "kibibyte/KiB, mebibyte/MiB..." или "kilobyte/KB, megabyte/MiB...".
     * 
     * @param mixed $value Форматируемое значение.
     * @param int $decimals Количество цифр после запятой (если используется расширение 
     *     PHP intl, устанавливается свойствам класса: NumberFormatter::MAX_FRACTION_DIGITS, 
     *     ::MIN_FRACTION_DIGITS).
     * @param array $options Опции форматирования чисел (только для класса NumberFormatter 
     *     расширения PHP intl).
     * @param array $textOptions Опции текста в форматировании числа (только для класса 
     *     NumberFormatter расширения PHP intl).
     * 
     * @return string Результирующие значение формата.
     * 
     * @throws Exception\InvalidArgumentException Если форматирование без расширения PHP intl.
     */
    public function toSizeDataUnit(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        if ($this->intlIsLoaded) {
            // рассчёт размера и префикса
            $base = $this->sizeDataUnitFormat;
            $power = floor(log($normValue, $base));
            $prefix = (int)$power;
            // если число очень большое
            if (is_null($prefix) || $prefix > 4) {
                $prefix = -1;
            }
            if ($this->sizeDataUnitFormat == self::SIZE_DATA_UNIT_BINARY) {
                // self::SIZE_DATA_UNIT_BINARY
                switch ($prefix) {
                    case - 1:
                    case 0:
                        $unitName = '{n, plural, =1{byte} other{bytes}}';
                        break;
                    case 1:
                        $unitName = '{n, plural, =1{kibibyte} other{kibibytes}}';
                        break;
                    case 2:
                        $unitName = '{n, plural, =1{mebibyte} other{mebibytes}}';
                        break;
                    case 3:
                        $unitName = '{n, plural, =1{gibibyte} other{gibibytes}}';
                        break;
                    case 4:
                        $unitName = '{n, plural, =1{tebibyte} other{tebibytes}}';
                        break;
                    default:
                        $unitName = '{n, plural, =1{pebibyte} other{pebibytes}}';
                        break;
                }
            } else {
                // self::SIZE_DATA_UNIT_DECIMAL
                switch ($prefix) {
                    case - 1:
                    case 0:
                        $unitName = '{n, plural, =1{byte} other{bytes}}';
                        break;
                    case 1:
                        $unitName = '{n, plural, =1{kilobyte} other{kilobytes}}';
                        break;
                    case 2:
                        $unitName = '{n, plural, =1{megabyte} other{megabytes}}';
                        break;
                    case 3:
                        $unitName = '{n, plural, =1{gigabyte} other{gigabytes}}';
                        break;
                    case 4:
                        $unitName = '{n, plural, =1{terabyte} other{terabytes}}';
                        break;
                    default:
                        $unitName = '{n, plural, =1{petabyte} other{petabytes}}';
                        break;
                }
            }
            // если число очень большое
            if ($prefix === -1)
                $result = $normValue;
            else
                $result = ($normValue / pow($base, $power));
            return $this->toDecimal($result, $decimals, $options, $textOptions) . ' ' . Ge::
                t('app', $unitName, ['n' => $result], $this->locale);
        }
        if ($this->throwException) {
            throw new Exception\InvalidArgumentException('Format as Data Unit is only supported PHP intl extension.');
        }
        $this->error = [0, 'Format as Data Unit is only supported PHP intl extension.'];
        return $value;
    }

    /**
     * Форматирует значение в байтах, в легко воспринимаемой форме (например «270 КБ»).
     * 
     * В зависимости от того, какой формат едениц расчёта информации используется 
     * {@see Formatter::$sizeDataUnitFormat}, будет к результату добавлен префикс 
     * "KiB, MiB..." или "KB, MiB...".
     * 
     * @param mixed $value Форматируемое значение.
     * @param int $decimals Количество цифр после запятой (если используется расширение 
     *     PHP intl, устанавливается свойствам класса:  NumberFormatter::MAX_FRACTION_DIGITS, 
     *     ::MIN_FRACTION_DIGITS, по умолчанию `null`).
     * @param array $options Опции форматирования чисел (только для класса NumberFormatter 
     *      расширения PHP intl, по умолчанию `[]`).
     * @param array $textOptions Опции текста в форматировании числа (только для класса 
     *      NumberFormatter расширения PHP intl, по умолчанию `[]`).
     * 
     * @return string Результирующие значение формата.
     */
    public function toShortSizeDataUnit(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string
    {
        $normValue = $this->normalizeNumericValue($value);
        if ($normValue === null) {
            return $value;
        }

        // рассчёт размера и префикса
        $base = $this->sizeDataUnitFormat;
        $power = floor(log($normValue, $base));
        $prefix = (int)$power;
        // если число очень большое
        if (is_null($prefix) || $prefix > 4) {
            $prefix = -1;
        }
        if ($this->sizeDataUnitFormat == self::SIZE_DATA_UNIT_BINARY) {
            // self::SIZE_DATA_UNIT_BINARY
            switch ($prefix) {
                case - 1:
                case 0:
                    $unitName = 'B';
                    break;
                case 1:
                    $unitName = 'KiB';
                    break;
                case 2:
                    $unitName = 'MiB';
                    break;
                case 3:
                    $unitName = 'GiB';
                    break;
                case 4:
                    $unitName = 'TiB';
                    break;
                default:
                    $unitName = 'PiB';
                    break;
            }
        } else {
            // self::SIZE_DATA_UNIT_DECIMAL
            switch ($prefix) {
                case - 1:
                case 0:
                    $unitName = 'B';
                    break;
                case 1:
                    $unitName = 'KB';
                    break;
                case 2:
                    $unitName = 'MB';
                    break;
                case 3:
                    $unitName = 'GB';
                    break;
                case 4:
                    $unitName = 'TB';
                    break;
                default:
                    $unitName = 'PB';
                    break;
            }
        }

        // если число очень большое
        if ($prefix === -1)
            $result = $normValue;
        else
            $result = ($normValue / pow($base, $power));

        // добавить шаблоны перевода форматов
        $this->addFormatPatterns();

        return $this->toDecimal($result, $decimals, $options, $textOptions) 
             . ' ' . Ge::t('app', $unitName, [], $this->locale);
    }

    /**
     * Форматирует значение как логическое.
     * 
     * @param mixed $value Форматируемое значение.
     * 
     * @return string Результирующие значение формата.
     */
    public function toBoolean(mixed $value): string
    {
        if (!isset($this->booleanFormat)) {
            // добавить шаблоны перевода форматов
            $this->addFormatPatterns();

            $this->booleanFormat = [Ge::t('app', 'No'), Ge::t('app', 'Yes')];
        }
        return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
    }

    /**
     * Нормализует числовое значение.
     * 
     *    Если значение:
     *    - пустое {@link https://www.php.net/manual/ru/function.empty.php}, возвратит "0";
     *    - число {@link https://www.php.net/manual/ru/function.is-numeric.php} строка 
     *    будет преобразована в float;
     *    - не является числом, будет брошено исключение или запись ошибки {@see Formatter::$error}.
     *
     * @param mixed $value Значение.
     * 
     * @return float|int|null Нормализованное значение.
     * 
     * @throws Exception\InvalidArgumentException Если значение не является числом.
     */
    protected function normalizeNumericValue(mixed $value): float|int|null
    {
        if (empty($value)) {
            return 0;
        }

        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }

        if (!is_numeric($value)) {
            if ($this->throwException)
                throw new Exception\InvalidArgumentException(sprintf('The value "%s" cannot be a number.',
                    $value));
            else {
                $this->error = [0, sprintf('The value "%s" cannot be a number.', $value)];
                return null;
            }
        }
        return $value;
    }

    /**
     * Нормализует заданное значение как объект DateTime, который может быть использован 
     * различными методами форматирования даты и времени.
     * 
     * Полученное представление даты и времени будет в часовом поясе UTC.
     * 
     * @param DateTimeInterface|string|int $value Значение даты и времени, которое нужно нормализовать.
     *    Поддерживаются следующие типы значений:
     *    - integer, представлен как UNIX timestamp;
     *    - string, который разберается для создания объекта DateTime {@link http://php.net/manual/ru/datetime.formats.php};
     *    - объект PHP DateTime {@link http://php.net/manual/ru/class.datetime.php}.
     *    - объект PHP DateTimeImmutable {@link http://php.net/manual/ru/class.datetimeimmutable.php}.
     *
     * @return DateTimeInterface|int Нормализованное значение даты и времени в часовом поясе UTC.
     * 
     * @throws Exception\InvalidArgumentException Если входное значение не представлено как 
     *     значение даты и времени.
     */
    public function normalizeDateTimeValue(DateTimeInterface|string|int $value): DateTimeInterface|int
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (empty($value)) {
            $value = 0;
        }

        try {
            return new DateTime(is_numeric($value) ? '@' . (int) $value : $value, $this->timeZoneUTC);
        } catch (\Exception $e) {
            throw new Exception\InvalidArgumentException(
                sprintf("\"%s\" is not a valid date time value: %s\n",
                    $value,
                    $e->getMessage(),
                    print_r(DateTime::getLastErrors(), true)
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Форматирует значение в строку в виде даты и времени (даты или времени).
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp;  
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php};  
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php};  
     *    - PHP DateTimeImmutable объект {@see https://www.php.net/manual/ru/class.datetimeimmutable.php}.
     * @param string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * @param string $type Тип формата: 'date', 'time' или 'datetime'.
     * @param DateTimeZone|string|null $toTimeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Неправильно указано значение параметра.
     * @throws Exception\InvalidClassException Если форматтер не создан из-за неверного 
     *     формата времени.
     */
    protected function formatDateTimeValue(
        DateTimeInterface|string|int $value, 
        string $format, 
        string $type, 
        DateTimeZone|string|null $toTimeZone = null
    ): string
    {
        if ($toTimeZone === null)
            $toTimeZone = $this->timeZone;
        else {
            if (is_string($toTimeZone))
                $toTimeZone = new DateTimeZone($toTimeZone);
            else
            if (!$toTimeZone instanceof DateTimeZone)
                throw new Exception\InvalidArgumentException('The value of the $toTimeZone parameter is incorrect, has the wrong type');
        }

        /** @var null|DateTime|DateTimeImmutable $datetime */
        $datetime = $this->normalizeDatetimeValue($value);

        // если есть приставка в формате "php:"
        $hasPrefix = strncmp($format, 'php:', 4) === 0;
        // расширение PHP intl не работает с датами >=2038 или <=1901 на 32bit сервере
        $year = $datetime->format('Y');
        if ($this->intlIsLoaded && $this->useIntl && !(PHP_INT_SIZE === 4 && ($year <=1901 || $year >= 2038))) {
            // преобразование php даты в формат icu
            if ($hasPrefix) {
                $format = Converter::convertDatePhpToIcu(substr($format, 4));
            }
            // если указан формат intl (short, medium, long, full)
            if (isset($this->intlDateFormats[$format])) {
                if ($type === 'date')
                    $formatter = $this->createDateFormatter($format, IntlDateFormatter::NONE, $toTimeZone);
                else
                if ($type === 'time')
                    $formatter = $this->createDateFormatter(IntlDateFormatter::NONE, $format, $toTimeZone);
                else
                    $formatter = $this->createDateFormatter($format, $format, $toTimeZone);
            // если свой формат
            } else {
                $formatter = $this->createDateFormatter(IntlDateFormatter::NONE,
                    IntlDateFormatter::NONE, $toTimeZone, $this->calendar, $format);
            }
            // если не указан формат
            if ($formatter === null) {
                throw new Exception\InvalidClassException(intl_get_error_message());
            }
            //die($format);
            return $formatter->format($datetime);
        }

        // преобразование icu даты в формат php
        if ($hasPrefix)
            $format = substr($format, 4);
        else
            $format = Converter::convertDateIcuToPhp($format, $type, $this->locale);

        if ($datetime instanceof DateTimeImmutable)
            $datetime = $datetime->setTimezone($toTimeZone);
        else
            $datetime->setTimezone($toTimeZone);
        return $datetime->format($format);
    }

    /**
     * Форматирует значение в строку в виде даты и времени (даты или времени).
     * 
     * Упрощенное форматирование без нормализации и расширения PHP intl для текущего
     * часового пояса {@see Formatter::$timeZone}.
     * 
     * @param DateTimeInterface|string|int $value Значение для форматирования. 
     *    Поддерживаются следующие типы значений:
     *    - integer, представлено как UNIX timestamp (всегда в формате UTC).
     *    - string, может использоваться для создания DateTime объекта {@link https://www.php.net/manual/ru/datetime.formats.php}.
     *    Предполагается, что timestamp находится в {@see \Ge\I18n\Fromatter::$defaultTimeZone}, 
     *    если не указан часовой пояс;
     *    - PHP DateTime объект {@see https://www.php.net/manual/ru/class.datetime.php} 
     *    (возможность указать часовой пояс в самом объекте).
     * @param string $format Формат даты, используемый для преобразования значения в 
     *    строку в виде даты. Используется только формат даты и времени (даты или 
     *    времени) PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php} 
     *    с префиксом "php:" или без.
     * @param DateTimeZone|string|null $toTimeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется текущий часовой пояс (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Неправильно указано значение параметра.
     */
    protected function _formatDateTimeValue(
        DateTimeInterface|string|int $value, 
        string $format, 
        DateTimeZone|string|null $toTimeZone = null
    ): string
    {
        if ($toTimeZone !== null) {
            if (is_string($toTimeZone))
                $toTimeZone = new DateTimeZone($toTimeZone);
            else
            if (!$toTimeZone instanceof DateTimeZone)
                throw new Exception\InvalidArgumentException('The value of the $toTimeZone parameter is incorrect, has the wrong type');
        }

        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        }

        if ($value instanceof DateTimeImmutable) {
            if ($toTimeZone) {
                $value = $value->setTimezone($toTimeZone);
            }
            return $value->format($format);
        } else
        if ($value instanceof DateTime) {
            if ($toTimeZone) {
                $value->setTimezone($toTimeZone);
            }
            return $value->format($format);
        }

        if ($toTimeZone)
            return (new DateTime($value, $toTimeZone))->format($format);
        else
            return date($format, $value);
    }

    /**
     * Возвращает формат даты и времени без указанной приставки.
     * 
     * @param string $formatName Имя формата (свойство класса): 'dateFormat', 'timeFormat', 
     *    'dateTimeFormat' (по умолчанию 'dateFormat'). 
     * @param string $prefix Приставка формата (по умолчанию 'php:').
     * 
     * @return string
     */
    public function formatWithoutPrefix(string $formatName = 'dateFormat', string $prefix = 'php:'): string
    {
        $format = $this->{$formatName};
        if ($prefix) {
            if (strncmp($format, $prefix, mb_strlen($prefix)) === 0) {
                $format = substr($format, 4);
            }
        }
        return $format;
    }

    /**
     * Возвращает форматы даты и времени без указанной приставки.
     * 
     * @param string $prefix Приставка формата (по умолчанию 'php:').
 
     * @return array Форматы дат и времени в виде пар "название - формат".  
     *     Возвратит: `['dateFormat' => '...', 'timeFormat' => '...', 'dateTimeFormat' => '...']`.
     */
    public function formatsWithoutPrefix(string $prefix = 'php:'): array
    {
        return [
            'dateFormat'     => $this->formatWithoutPrefix('dateFormat', $prefix),
            'timeFormat'     => $this->formatWithoutPrefix('timeFormat', $prefix),
            'dateTimeFormat' => $this->formatWithoutPrefix('dateTimeFormat', $prefix)
        ];
    }

    /**
     * Проверяет, есть ли в указанном формате приставка.
     * 
     * @param string $formatName Имя формата (свойство класса): 'dateFormat', 'timeFormat', 
     *    'dateTimeFormat' (по умолчанию 'dateFormat'). 
     * @param string $prefix Приставка формата (по умолчанию 'php:').
     * 
     * @return bool Возвращаеи значение `true`, если приставка присутствует в формате.
     */
    public function formatHasPrefix(string $formatName = 'dateFormat', string $prefix = 'php:'): bool
    {
        return strncmp($this->{$formatName}, $prefix, mb_strlen($prefix)) === 0;
    }

    /**
     * Возвращает отформатированную строку с текущей датой в указанном формате.
     * 
     * Для возвращения текущей даты применяется часовой пояс {@see Formatter::$timeZone} 
     * (он должен быть установлен часовым поясом по умолчанию в `date_default_timezone_set()`).
     *
     * @param string $format Принятый формат DateTimeInterface::format() {@link https://www.php.net/manual/ru/datetime.format.php}.
     * @param DateTimeZone|string|null $timeZone Часовой пояс в который будет преобразовано 
     *    $value. Если значение `null`, применяется {@see Formatter::$timeZone} (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException Значение параметра $timeZone неверно, имеет неправильный тип.
     */
    public function makeDate(string $format, DateTimeZone|string|null $timeZone = null): string
    {
        if ($timeZone !== null) {
            if (is_string($timeZone))
                $timeZone = new DateTimeZone($timeZone);
            else
            if (!$timeZone instanceof DateTimeZone)
                throw new Exception\InvalidArgumentException('The value of the $timeZone parameter is incorrect, has the wrong type');
        } else
            $timeZone = $this->timeZone;

        return (new DateTime('now', $timeZone))->format($format);
    }

    /**
     * Использовать расширение intl PHP при форматирование (если расширение загружено).
     * 
     * @param bool $use Использовать расширение intl PHP.
     * 
     * @return $this
     */
    public function useIntl(bool $use): static
    {
        $this->useIntl = $use;
        return $this;
    }
}
