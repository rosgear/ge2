<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Validator;

use Ge;
use Ge\I18n\Translator;
use Ge\I18n\Source\MessageSource;

/**
 * Абстрактный класс валидатора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
abstract class AbstractValidator {
    /**
     * Дополнительные переменные, доступные для сообщений об ошибках проверки.
     *
     * @var array
     */
    protected array $messageVariables = [];

    /**
     * Определения шаблонов сообщений об ошибках проверки.
     *
     * @var array
     */
    protected array $messageTemplates = [];

    /**
     * Параметры настроек валидатора.
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Значение, подлежащее проверке.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Ограничивает максимальную возвращаемую длину сообщения об ошибке.
     *
     * @var int
     */
    protected static $messageLength = -1;

    /**
     * Абстрактные (шаблон) параметры настроек валидатора.
     *
     * @var array
     */
    protected $abstractOptions = [
        'messages'          => [], // массив сообщений об ошибках проверки
        'messageTemplates'  => [], // массив шаблонов сообщений об отказе валидации
        'messageVariables'  => [], // массив дополнительных переменных, доступных для сообщений об ошибках проверки
        'translatorEnabled' => true, // включает перевод
    ];

    /**
     * Транслятор (локализатор сообщений).
     *
     * @var null|Translator
     */
    protected $translator;

    /**
     * Конструктор класса.
     *
     * @param array $options Параметры настроек валидатора.
     */
    public function __construct(array $options = [])
    {
        if ($this->messageTemplates) {
            $this->abstractOptions['messageTemplates'] = $this->messageTemplates;
        }
        if ($this->messageVariables) {
            $this->abstractOptions['messageVariables'] = $this->messageVariables;
        }
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Возвращает значение параметра настроек валидатора.
     *
     * @param string $option Название параметра настроек валидатора.
     * 
     * @return mixed
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function getOption(string $option): mixed
    {
        if (array_key_exists($option, $this->abstractOptions)) {
            return $this->abstractOptions[$option];
        }

        if (isset($this->options) && array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        throw new Exception\InvalidArgumentException(Ge::t('app', 'Invalid option "{0}"', [$option]));
    }

    /**
     * Возвращает все параметры настроек валидатора.
     *
     * @return array
     */
    public function getOptions(): array
    {
        $options = $this->abstractOptions;
        if ($this->options) {
            $options = array_merge($options, $this->options);
        }
        return $options;
    }

    /**
     * Устанавливает параметры настроек валидатора.
     *
     * @param array $options Параметры настроек валидатора.
     * 
     * @return $this
     */
    public function setOptions(array $options): static
    {
        foreach ($options as $name => $option) {
            $fname = 'set' . ucfirst($name);
            $fname2 = 'is' . ucfirst($name);
            if (($name != 'setOptions') && method_exists($this, $name)) {
                $this->{$name}($option);
            } elseif (($fname != 'setOptions') && method_exists($this, $fname)) {
                $this->{$fname}($option);
            } elseif (method_exists($this, $fname2)) {
                $this->{$fname2}($option);
            } elseif (isset($this->options)) {
                $this->options[$name] = $option;
            } else {
                $this->abstractOptions[$name] = $option;
            }
        }
        return $this;
    }

    /**
     * Возвращает сообщения (ошибках проверки).
     * 
     * @param bool $justText Возвращать только сообщения.
     * 
     * @return array
     */
    public function getMessages(bool $justText = true): array
    {
        $arr = array_unique($this->abstractOptions['messages'], SORT_REGULAR);
        if ($justText) {
            return array_values($arr);
        }
        return $arr;
    }

    /**
     * Выполняет проверку значения.
     *
     * @param mixed $value Проверяемое значение.
     * 
     * @return bool
     */
    public function isValid(mixed $value): bool
    {
        return true;
    }

    /**
     * Вызывается, когда выполняют экземпляра класса как функцию.
     *
     * @param mixed $value Значение.
     * 
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Возвращает имена (ключи), которые применялись при построении сообщений об 
     * ошибках.
     *
     * @return array
     */
    public function getMessageVariables(): array
    {
        return array_keys($this->abstractOptions['messageVariables']);
    }

    /**
     * Возвращает шаблоны сообщений валидатора.
     *
     * @return array
     */
    public function getMessageTemplates(): array
    {
        return $this->abstractOptions['messageTemplates'];
    }

    /**
     * Устанавливает шаблон сообщения об ошибке указанному ключу.
     *
     * @param string $messageString Шаблон сообщения.
     * @param null|string $messageKey Ключ сообщения.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function setMessage(string $messageString, ?string $messageKey = null): static
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            foreach ($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            throw new Exception\InvalidArgumentException(Ge::t('app','No message template exists for key "{0}"', [$messageKey]));
        }

        $this->abstractOptions['messageTemplates'][$messageKey] = $messageString;
        return $this;
    }

    /**
     * Устанавливает шаблоны сообщений об ошибках проверки.
     *
     * @param array $messages Шаблоны сообщений в виде "ключ - сообщение".
     * 
     * @return $this
     */
    public function setMessages(array $messages): static
    {
        foreach ($messages as $key => $message) {
            $this->setMessage($message, $key);
        }
        return $this;
    }

    /**
     * Возвращение значения запрашиваемого свойства, если и только если 
     * это значение или переменная сообщения
     * (магический метод)
     * 
     * @param string $property
     * 
     * @return mixed
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __get($property)
    {
        if ($property == 'value') {
            return $this->value;
        }
        throw new Exception\InvalidArgumentException(
            Ge::t('app','No property exists by the name "{0}"', [$property])
        );
    }

    /**
     * Создает и возвращает сообщение об ошибке проверки с заданным ключом и значением.
     * Возвращает null тогда и только тогда, когда $messageKey не соответствует существующему шаблону.
     * Если переводчик доступен и существует перевод для $ messageKey, будет использоваться перевод.
     *
     * @param string $messageKey Ключ сообщения.
     * @param mixed $value Значение подставляемое в сообщение.
     * 
     * @return string
     */
    protected function createMessage(string $messageKey, mixed $value): string
    {
        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return '';
        }

        $message = $this->abstractOptions['messageTemplates'][$messageKey];

        $message = Ge::t('app', $message);

        $message = str_replace('%value%', (string) $value, $message);
        foreach ($this->abstractOptions['messageVariables'] as $ident => $property) {
            if (is_array($property)) {
                $value = $this->{key($property)}[current($property)] ?? null;
                if ($value !== null) {
                    if (is_array($value)) {
                        $value = '[' . implode(', ', $value) . ']';
                    }
                }
            } else {
                // deprecated PHP 8.2 (creation of dynamic property)
                $value = @$this->$property;
            }
            $message = str_replace("%$ident%", (string) $value, $message);
        }

        $length = self::getMessageLength();
        if (($length > -1) && (strlen($message) > $length)) {
            $message = substr($message, 0, ($length - 3)) . '...';
        }
        return $message;
    }

    /**
     * Возвращает транслятор.
     *
     * @return Translator|null
     */
    public function getTranslator(): ?Translator
    {
        if (!$this->isTranslatorEnabled()) {
            return null;
        }
        return $this->translator;
    }

    /**
     * Устанавливает сообщение об ошибке.
     * 
     * @param string $messageKey Ключ сообщения.
     * @param mixed $value Значение подставляемое в сообщение.
     * 
     * @return $this
     */
    protected function error(string $messageKey, mixed $value = null): static
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            $messageKey = current($keys);
        }

        if ($value === null) {
            $value = $this->value;
        }
        $this->abstractOptions['messages'][$messageKey] = $this->createMessage($messageKey, $value);
        return $this;
    }

    /**
     * Возвращает значение подлежащее проверке.
     *
     * @return mixed
     */
    protected function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Устанавливает значение подлежащее проверке и удаляет сообщений и об ошибках.
     *
     * @param mixed $value значение
     * 
     * @return $this
     */
    protected function setValue(mixed $value): static
    {
        $this->value = $value;
        $this->abstractOptions['messages'] = [];
        return $this;
    }

    /**
     * Устанавливает транслятор (локализатор сообщений).
     *
     * @param MessageSource $translator Транслятор (локализатор сообщений).
     * 
     * @return $this
     */
    public function setTranslator(MessageSource $translator): static
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Устанавливает доступность транслятору (локализатору сообщений).
     *
     * @param bool $flag флаг. Если значение `true`, то транслятор доступен.
     * 
     * @return $this
     */
    public function setTranslatorEnabled(bool $flag = true): static
    {
        $this->abstractOptions['translatorEnabled'] = $flag;
        return $this;
    }

    /**
     * Проверяет, доступен ли транслятор (локализатор сообщений).
     *
     * @return bool
     */
    public function isTranslatorEnabled(): bool
    {
        return $this->abstractOptions['translatorEnabled'] ?? false;
    }

    /**
     * Возвращает максимальную допустимую длину сообщения.
     *
     * @return int
     */
    public static function getMessageLength(): int
    {
        return static::$messageLength;
    }

    /**
     * Устанавливает максимальную допустимую длину сообщения.
     *
     * @param int $length Максимальная допустимая длина сообщения.
     * 
     * @return void
     */
    public static function setMessageLength(int $length = -1): void
    {
        static::$messageLength = $length;
    }
}
