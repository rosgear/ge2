<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\Source;

use Exception;
use IntlException;
use MessageFormatter;

/**
 * Источника локализации сообщений.
 * 
 * Для сообщений формата "plural" (cклонение количественных числительных):
 *    - translate("Сегодня температура, {0} {{0},грудас,градуса,грудусов}", ["@plural", 20]), 
 * результат "Сегодня температура, 20 градусов";
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\Source
 * @since 2.0
 */
class MessageSource extends BaseSource
{
    /**
     * Методы форматирования сообщения по умолчанию.
     *
     * @var string
     */
    protected string $defaultFormatter = '@message';

    /**
     * Методы форматирования сообщений.
     *
     * @var array
     */
    protected array $formatters = [
        '@message' => 'messageFormatter',
        '@string'  => 'stringFormatter',
        '@incut'   => 'incutFormmater',
        '@plural'  => 'pluralFormmater'
    ];

    /**
     * Создаёт функцию перевода (локализацию) сообщений в указанный массив.
     * 
     * @param array<string, callable> $arr Массив, где ключ {@see MessageSource::$funcNameTranslate}
     *     это функция осуществляющая локализацию сообщений.
     * 
     * @return $this
     */
    public function createFuncTranslate(array &$arr): static
    {
        $self = $this;
        $arr[$this->funcNameTranslate] = function ($str) use ($self) {
            return $self->translate($str);
        };
        return $this;
    }

    /**
     * Проверяет, имеют ли параметры метод форматирования сообщений.
     * 
     * @param array $params
     * 
     * @return bool
     */
    protected function hasFormatter(array $params) :bool
    {
        if ($params) {
            $key = key($params);
            if (is_int($key) && $key === 0) {
                if (!empty($params[0])) {
                    return is_string($params[0]) && $params[0][0] == '@';
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function translate(string|array $message, array $params = [], string $locale = ''): string|array
    {
        if (empty($locale)) {
            $locale = $this->language->locale;
        }

        // если массив сообщений
        if (is_array($message)) {
            return $this->translateArray($message);
        }

        $message = $this->messages[$message] ?? $message;
        if ($params) {
            $key = key($params);
            // определение метода форматирования
            if ($this->hasFormatter($params)) {
                $formatterName = $this->formatters[$params[0]] ?? $this->formatters[$this->defaultFormatter];
                array_shift($params);
            } else
                $formatterName = $this->formatters[$this->defaultFormatter];
            return $this->{$formatterName}($message, $params, $locale);
        }
        return $message;
    }

    /**
     * Форматирует сообщение, подставляя данные в строку формата в соответствии с правилами локали.
     * 
     * Формат '@message'.
     * 
     * @param string $message Текст сообщения.
     * @param array $params Аргументы для вставки в строку формата.
     * @param string $locale Локаль, используемая при форматировании аргументов.
     * 
     * @return string|false Отформатированная строка или `false` в случае возникновения ошибки. 
     * 
     * @throws IntlException Средство форматирования сообщения недействительны.
     * @throws Exception Средство форматирования сообщения недействительны.
     */
    public function messageFormatter(string $message, array $params, string $locale): string|false
    {
         if (!class_exists('MessageFormatter', false)) {
            return $this->defaultFormatter($message, $params, $locale);
         }

         try {
            $formatter = new MessageFormatter($locale, $message);
         // IntlException брошен с PHP 7
         } catch (IntlException $e) {
            $this->setError($e->getCode(), sprintf('Message formatter is invalid: %s', $e->getMessage()));
            return '';
         // Exception брошен с HHVM
        } catch (Exception $e) {
            $this->setError($e->getCode(), sprintf('Message formatter is invalid: %s', $e->getMessage()));
            return '';
        }

        $result = $formatter->format($params);
        if ($result === false) {
            $this->setError($formatter->getErrorCode(), sprintf('Message formatter is invalid: %s', $formatter->getErrorMessage()));
            return '';
        }
        return $result;
    }

    /**
     * Форматирует сообщение, подставляя данные в строку формата в соответствии с правилами локали.
     * 
     * Применяется, если не один из форматов невозможно вызвать.
     * 
     * @param string $message Текст сообщения.
     * @param array $params Аргументы для вставки в строку формата.
     * @param string $locale Локаль, используемая при форматировании аргументов (не 
     *     применяется, только для совместимости с другими методами).
     * 
     * @return string
     */
    public function defaultFormatter(string $message, array $params, string $locale): string
    {
         $params = array_combine(
            array_map(function ($key) { return '{'. $key .'}'; }, array_keys($params)),
            array_values($params)
         );
         return strtr($message, $params);
    }

    /**
     * Форматирует строку, подставляя данные в строку формата в соответствии с правилами локали.
     * 
     * Формат '@string'.
     * 
     * @param string $str Строка.
     * @param array $params Аргументы для вставки в строку формата.
     * @param string $locale Локаль, используемая при форматировании аргументов (не 
     *     применяется, только для совместимости с другими методами).
     * 
     * @return string
     */
    public function stringFormatter(string $str, array $params, string $locale): string
    {
        return vsprintf($str, $params);
    }

    /**
     * Врезка в строку аргументов.
     * 
     * Формат '@incut'.
     * 
     * Где строка имеет вид: 'is {foo} sample {bar}', с параметрами форматирования: 
     * `['foo' => 'new foo', 'bar' => 'new bar']`.
     * 
     * @param string $str Строка.
     * @param array $params Аргументы для вставки.
     * @param string $locale Локаль, используемая при форматировании аргументов (не 
     *     применяется, только для совместимости с другими методами).
     * 
     * @return string
     */
    public function incutFormmater(string $str, array $params, string $locale): string
    {
         $params = array_combine(
            array_map(function ($key) { return '{'. $key .'}'; }, array_keys($params)),
            array_values($params)
         );
         return strtr($str, $params);
    }

    /**
     * Cклонение количественных числительных.
     * 
     * Формат '@plural'.
     * 
     * @see MessageSource::incutFormmater()
     * 
     * @param string $message Текст сообщения.
     * @param array $params Аргументы для вставки в строку формата.
     * @param string $locale Локаль, используемая при форматировании аргументов.
     * 
     * @return string
     */
    public function pluralFormmater(string $message, array $params, string $locale): string
    {
         $message = $this->incutFormmater($message, $params, $locale);
         // поиск и разбор выражений в сообщении
         $self = $this;
         $message = preg_replace_callback('/{.*?}/',
            function ($matches) use ($self) {
                // удаление экрана их выражения
                $name = trim($matches[0], '},{');
                if (empty($name))
                    return $matches[0];
                // параметры выражения
                $params = explode(',', $name);
                if (empty($params))
                    return $matches[0];
                // создание формы слова
                return $self->wordFormArray($params);
            },
            $message
        );
        return $message;
    }

    /**
     * Возвращает одну из форм множественного числа.
     * 
     * Аргументы функции задаются ввиде массива,
     * где:
     *    - 1-й элемент, количество;
     *    - 2-й элемент, ед. число, им. падеж (пример: стул);
     *    - 3-й элемент, ед. число, род. падеж (пример: стула);
     *    - 4-й элемент, мн. число, род. падеж (пример: стульев).
     * 
     * @param array $args Аргументы функции {@see MessageSource::wordForm()}.
     * 
     * @return string Форма склонения.
     */
    public function wordFormArray(array $args): string
    {
        $number = (int) $args[0];
        $form1  = isset($args[1]) ? $args[1] : '';
        $form2  = isset($args[2]) ? $args[2] : $form1;
        $form3  = isset($args[3]) ? $args[3] : $form2;
        return $this->wordForm($number, $form1, $form2, $form3);
    }

    /**
     * Возвращает одну из форм множественного числа.
     * 
     * Выводит количество чего-либо во множественном склонении.
     * Примечание: этот метод используется только для русской локализации.
     * 
     * @param int|float $number Число.
     * @param string $form1 Первая форма склонения (пример: "стул").
     * @param string $form2 Вторая форма склонения (пример: "стула").
     * @param string $form3 Третья форма склонения (пример: "стульев").
     * 
     * @return string Форма склонения.
     */
    public function wordForm(int|float $number, string $form1, string $form2, string $form3): string
    {
        $number = abs($number) % 100;
        $part = $number % 10;

        if ($number > 10 && $number < 20) return $form3;
        if ($part > 1 && $part < 5) return $form2;
        if ($part == 1) return $form1;
        return $form3;
    }

    /**
     * Форматирует строку и заменяет ее на строку шаблона.
     * 
     * Например: '#Hello' => 'Привет'.
     * 
     * @param string $str Cтрока.
     * 
     * @return string
     */
    public function format(string $str): string
    {
        if ($str[0] != $this->formatChar) return $str;

        $str = ltrim($str, $this->formatChar);
        if (!isset($this->messages[$str]))
            return $str;
        return $this->messages[$str];
    }

    /**
     * Форматирует строки массива с заменой на строки шаблона.
     * 
     * Форматирование применяется к массиву с рекурсией.
     * 
     * Например: `['foo' => '#Hello', 'bar' => ['foo' => '#Width']]`.
     * Результат: `['foo' => 'Привет', 'bar' => ['foo' => 'Ширина']]`.
     * 
     * @param array $arr Массив строк.
     * 
     * @return array
     */
    public function translateArray(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (empty($value)) {
                $arr[$key] = $value;
                continue;
            }

            if (is_string($value)) {
                $arr[$key] = $this->format($value);
            } else
            if (is_array($value)) {
                $arr[$key] = $this->translateArray($value);
            }
        }
        return $arr;
    }
}
