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
 * Валидатор "Compare" (сравнение значений).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Compare extends AbstractValidator
{
    public const NOT_SET_CONDITION = 'notSetCondition';
    public const WRONG_VALUE = 'wrongValue';

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::NOT_SET_CONDITION => 'You have incorrectly set the comparison condition',
        self::WRONG_VALUE => 'Wrong value selected'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'condition' => '=',
        'message'   => '',
        'with'      => ''
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

        if (!isset($options['condition']) || !isset($options['with'])) {
            throw new Exception\InvalidArgumentException(
                Ge::t('app', 'Missing option. "condition" and "with" have to be given')
            );
        }
        if (isset($options['required'])) {
            $this->required = $options['required'];
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает опцию условия.
     *
     * @return string
     */
    public function getCondition(): string
    {
        return $this->options['condition'];
    }

    /**
     * Возвращает cверяемое значение.
     *
     * @return mixed
     */
    public function getWith(): mixed
    {
        return $this->options['with'];
    }

    /**
     * Устанавливает опцию условия.
     *
     * @param string $condition Условие.
     * 
     * @return $this
     */
    public function setCondition(string $condition): static
    {
        $this->options['condition'] = $condition;
        return $this;
    }

    /**
     * Устанавливает опции сверяемого значения.
     *
     * @param mixed $value Сверяемое значение.
     * 
     * @return $this
     */
    public function setWith(mixed $value): static
    {
        $this->options['with'] = $value;
        return $this;
    }

    /**
     * Возвращает значние `true`, если $value удолетворяет условию.
     * 
     * {@inheritdoc}
     */
    public function isValid($value): bool
    {
        $this->setValue($value);
        // если нет необходимости проверять значение и оно "пустое"
        if (!$this->required && empty($value)) {
            return true;
        }
        $compare = $this->getWith();

        // приводим к одному типу сравнения
        $result = null;
        switch ($this->getCondition()) {
            case '=' :
                settype($value, gettype($compare));
                $result = $compare == $value;
                break;

            case '=length' : $result = $compare == mb_strlen($value); break;

            case '>' :
                settype($value, gettype($compare));
                $result = $compare > $value;
                break;

            case '>length' : $result = $compare > mb_strlen($value); break;

            case '>=' :
                settype($value, gettype($compare));
                $result = $compare >= $value;
                break;

            case '>=length' : $result = $compare >= mb_strlen($value); break;

            case '<' :
                settype($value, gettype($compare));
                $result = $compare < $value;
                break;

            case '<length' : $result = $compare < mb_strlen($value); break;

            case '<=' :
                settype($value, gettype($compare));
                $result = $compare <= $value;
                break;

            case '<=length' : $result = $compare <= mb_strlen($value); break;

        }
        if ($result === null) {
            $this->error(self::NOT_SET_CONDITION);
            return false;
        } else
            if (!$result) {
                $this->error(self::WRONG_VALUE);
            }
        return $result;
    }
}
