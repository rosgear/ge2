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
 * Валидатор "Filter" (проверка значений с использованием фильтра).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */

class Filter extends AbstractValidator
{
    public const NOT_FILTERED = 'notFiltered';

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::NOT_FILTERED => 'You have filled in the field incorrectly'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'filter' => '', 
        'params' => null
    ];

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): static
    {
        $options = array_merge($this->options, $options);

        if (empty($options['filter'])) {
            throw new Exception\InvalidArgumentException(Ge::t('app', "Missing option. 'filter' have to be given"));
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает фильтр.
     *
     * @return string
     */
    public function getFilter(): string
    {
        return $this->options['filter'];
    }

    /**
     * Устанавливает фильтр.
     *
     * @param string $filter
     * 
     * @return $this
     */
    public function setFilter(string $filter)
    {
        $this->options['filter'] = $filter;
        return $this;
    }

    /**
     * Возвращает параметры фильтра.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->options['params'] ?? [];
    }

    /**
     * Устанавливает параметры фильтра.
     *
     * @param null|array $params
     * 
     * @return $this
     */
    public function setParams(?array $params): static
    {
        $this->options['params'] = $params;
        return $this;
    }

    /**
     * Возвращает значние `true`, если и только если $value соответсвует фильтру.
     * 
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if (filter_var($value, $this->getFilter(), $this->getParams()) === false) {
            $this->error(self::NOT_FILTERED);
            return false;
        }
        return true;
    }
}
