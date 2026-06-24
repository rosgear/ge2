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
 * Валидатор "PregMatch" (проверка значений регулярным выражением).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class PregMatch extends AbstractValidator
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
        'format'  => '', 
        'message' => ''
    ];

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = []): static
    {
        $options = array_merge($this->options, $options);

        if (empty($options['format'])) {
            throw new Exception\InvalidArgumentException(Ge::t('app', "Missing option. 'format' or 'message' have to be given"));
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает формат.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->options['format'];
    }

    /**
     * Устанавливает формат.
     *
     * @param string $format
     * 
     * @return $this
     */
    public function setFormat(string $format): static
    {
        $this->options['format'] = $format;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if (!preg_match($this->getFormat(), $value)) {
            $this->error(self::NOT_MATCH);
            return false;
        }
        return true;
    }
}
