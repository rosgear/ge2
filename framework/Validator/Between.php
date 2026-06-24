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

/**
 * Валидатор "Between" (проверка диапазона значений).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Between extends AbstractValidator
{
    public const NOT_BETWEEN        = 'notBetween';
    public const NOT_BETWEEN_STRICT = 'notBetweenStrict';
    public const BE_LESS            = 'beLess';
    public const BE_GREATER         = 'beGreater';
    public const BE_LESS_STRICT     = 'beLessStrict';
    public const BE_GREATER_STRICT  = 'beGreaterStrict';

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::NOT_BETWEEN        => 'The input is not between "%min%" and "%max%", inclusively',
        self::NOT_BETWEEN_STRICT => 'The input is not strictly between "%min%" and "%max%"',
        self::BE_LESS            => 'The input must be less "%max%", inclusively',
        self::BE_GREATER         => 'The input must be greater "%min%", inclusively',
        self::BE_LESS_STRICT     => 'The input must be less "%max%"',
        self::BE_GREATER_STRICT  => 'The input must be greater "%min%"',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $messageVariables = [
        'min' => ['options' => 'min'],
        'max' => ['options' => 'max'],
    ];

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'inclusive' => true,  // Выполнять ли инклюзивные сравнения, позволяя эквивалентность min и / или max
        'min'       => 0,
        'max'       => PHP_INT_MAX,
        'type'      => 'integer',
        'useString' => false
    ];

    /**
     * Позволяет не проверять значение если оно "пустое".
     * 
     * Если `true`, значение будет обязательно проверено.
     *
     * @var bool
     */
    protected bool $required = true;

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): static
    {
        $options = array_merge($this->options, $options);

        if (!isset($options['min']) && !isset($options['max'])) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Missing option. "min" and "max" have to be given')
            );
        }
        if (isset($options['required'])) {
            $this->required = $options['required'];
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает опцию минмального значения.
     *
     * @return int
     */
    public function getMin(): int
    {
        return $this->options['min'];
    }

    /**
     * Устанавливает опцию минмального значения.
     *
     * @param int $min Минимальное значение.
     * 
     * @return $this
     */
    public function setMin(int $min): static
    {
        $this->options['min'] = $min;
        return $this;
    }

    /**
     * Возвращает опцию максимального значения.
     *
     * @return int
     */
    public function getMax(): int
    {
        return $this->options['max'];
    }

    /**
     * Устанавливает опцию максимального значения.
     *
     * @param mixed $max Максимальное значение.
     * 
     * @return $this
     */
    public function setMax(int $max): static
    {
        $this->options['max'] = $max;
        return $this;
    }

    /**
     * Возвращает опцию включительного сравнения.
     *
     * @return bool
     */
    public function getInclusive(): bool
    {
        return $this->options['inclusive'];
    }

    /**
     * Устанавливает опцию включительного сравнения.
     *
     * @param bool $inclusive
     * 
     * @return $this
     */
    public function setInclusive(bool $inclusive): static
    {
        $this->options['inclusive'] = $inclusive;
        return $this;
    }

    /**
     * Возвращает опцию типа сравнении.
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->options['type'];
    }

    /**
     * Устанавливает опцию типа при сравнении.
     * 
     * @param string $value
     * 
     * @return $this
     */
    public function setType(string $value): static
    {
        $this->options['type'] = $value;
        return $this;
    }


    /**
     * Возвращает проверяемое значение.
     * 
     * @param mixed $value Проверяемое значение.
     * 
     * @return mixed
     */
    protected function getValidateValue($value): mixed
    {
        $type = $this->getType();
        if ($type === 'string') {
            return mb_strlen((string) $value);
        } else
            return $value;
    }

    /**
     * Возвращает значние `true`, если и только если $value находится между параметрами min и max, 
     * включительно, если включена опция inclusive.
     * 
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);
        // если нет необходимости проверять значение и оно "пустое"
        if (!$this->required && empty($value)) {
            return true;
        }
        $value = $this->getValidateValue($value);
        
        if ($this->getInclusive()) {
            if (!isset($this->options['min'])) {
                if (!($value <= $this->getMax())) {
                    $this->error(self::BE_LESS_STRICT);
                    return false;
                }
            } else
            if (!isset($this->options['max'])) {
                if (!($value >= $this->getMin())) {
                    $this->error(self::BE_GREATER_STRICT);
                    return false;
                }
            } else
            if (!($value >= $this->getMin() && $value <= $this->getMax())) {
                $this->error(self::NOT_BETWEEN_STRICT);
                return false;
            }
        } else {
            if (!isset($this->options['min'])) {
                if ($value > $this->getMax()) {
                    $this->error(self::BE_LESS);
                    return false;
                }
            } else
            if (!isset($this->options['max'])) {
                if ($this->getMin() > $value) {
                    $this->error(self::BE_GREATER);
                    return false;
                }
            } else
            if (!($value > $this->getMin() && $value < $this->getMax())) {
                $this->error(self::NOT_BETWEEN);
                return false;
            }
        }
        return true;
    }
}
