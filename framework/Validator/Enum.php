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
 * Валидатор "Enum" (проверка вхождения значения в множество).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Enum extends AbstractValidator
{
    public const NOT_MATCH = 'notMatch';

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::NOT_MATCH => 'You have filled in the field incorrectly'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'enum'    => [], 
        'message' => '',
        'strict'  => false
    ];

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): static
    {
        $options = array_merge($this->options, $options);
        if (empty($options['enum'])) {
            throw new Exception\InvalidArgumentException(Ge::t('app', "Missing option. 'enum' or 'message' have to be given"));
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает вхождение.
     *
     * @return array
     */
    public function getEnum(): array
    {
        return $this->options['enum'];
    }

    /**
     * Устанавливает вхождение.
     *
     * @param array $enum
     * 
     * @return $this
     */
    public function setEnum(array $enum): static
    {
        $this->options['enum'] = $enum;
        return $this;
    }

    /**
     * Устанавливает строгость сравнения.
     *
     * @param bool $value
     * 
     * @return $this
     */
    public function setStrict(bool $value): static
    {
        $this->options['strict'] = $value;
        return $this;
    }

    /**
     * Возвращает строгость сравнения.
     *
     * @return bool
     */
    public function getStrict(): bool
    {
        return $this->options['strict'];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if (!in_array($value, $this->getEnum(), $this->getStrict())) {
            $this->error(self::NOT_MATCH);
            return false;
        }
        return true;
    }
}