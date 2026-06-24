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
 * Валидатор "NotEmpty" (проверка значений на пустоту).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Ge\Validator
 * @since 2.0
 */
class NotEmpty extends AbstractValidator
{
    public const BOOLEAN       = 0x001;
    public const INTEGER       = 0x002;
    public const FLOAT         = 0x004;
    public const STRING        = 0x008;
    public const ZERO          = 0x010;
    public const EMPTY_ARRAY   = 0x020;
    public const NULL          = 0x040;
    public const PHP           = 0x07F;
    public const SPACE         = 0x080;
    public const OBJECT        = 0x100;
    public const OBJECT_STRING = 0x200;
    public const OBJECT_COUNT  = 0x400;
    public const ALL           = 0x7FF;

    public const INVALID  = 'notEmptyInvalid';
    public const IS_EMPTY = 'isEmpty';

    protected array $constants = [
        self::BOOLEAN       => 'boolean',
        self::INTEGER       => 'integer',
        self::FLOAT         => 'float',
        self::STRING        => 'string',
        self::ZERO          => 'zero',
        self::EMPTY_ARRAY   => 'array',
        self::NULL          => 'null',
        self::PHP           => 'php',
        self::SPACE         => 'space',
        self::OBJECT        => 'object',
        self::OBJECT_STRING => 'objectstring',
        self::OBJECT_COUNT  => 'objectcount',
        self::ALL           => 'all',
    ];

    /**
     * Значение по умолчанию для типов; value = 493
     *
     * @var array
     */
    protected array $defaultType = [
        self::INTEGER,
        self::OBJECT,
        self::SPACE,
        self::NULL,
        self::EMPTY_ARRAY,
        self::STRING,
        self::BOOLEAN
    ];

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::IS_EMPTY => "Value is required and can't be empty",
        self::INVALID  => "Invalid type given. String, integer, float, boolean or array expected",
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        $this->setType($this->defaultType);

        if (!array_key_exists('type', $options)) {
            $detected = 0;
            $found    = false;
            foreach ($options as $option) {
                if (in_array($option, $this->constants, true)) {
                    $found = true;
                    $detected += array_search($option, $this->constants);
                }
            }

            if ($found) {
                $options['type'] = $detected;
            }
        }

        parent::__construct($options);
    }

    /**
     * Возвращает тип.
     *
     * @return mixed
     */
    public function getType(): mixed
    {
        return $this->options['type'];
    }

    /**
     * Возвращаеь тип по умолчанию.
     * 
     * @return int
     */
    public function getDefaultType(): mixed
    {
        return $this->calculateTypeValue($this->defaultType);
    }

    /**
     * Определяет тип значения.
     * 
     * @param mixed $type Тип.
     * 
     * @return mixed
     */
    protected function calculateTypeValue(mixed $type): mixed
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected |= array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }
        return $type;
    }

    /**
     * Устанавливает тип.
     *
     * @param int|array $type Тип.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function setType(mixed $type = null): static
    {
        $type = $this->calculateTypeValue($type);

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            throw new Exception\InvalidArgumentException(Ge::t('app', 'Unknown type'));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Возвращает знгачение `true` тогда и только тогда, когда $value не является пустым 
     * значением.
     * 
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value) &&
            !is_bool($value) && !is_array($value) && !is_object($value)
        ) {
            $this->error(self::INVALID);
            return false;
        }

        $type = $this->getType();
        $this->setValue($value);
        $object = false;

        // OBJECT_COUNT (countable object)
        if ($type & self::OBJECT_COUNT) {
            $object = true;

            if (is_object($value) && ($value instanceof \Countable) && (count($value) == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT_STRING (object's toString)
        if ($type & self::OBJECT_STRING) {
            $object = true;

            if ((is_object($value) && (!method_exists($value, '__toString'))) ||
                (is_object($value) && (method_exists($value, '__toString')) && (((string) $value) == ""))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT (object)
        if ($type & self::OBJECT) {
            // fall trough, objects are always not empty
        } elseif ($object === false) {
            // object not allowed but object given -> return false
            if (is_object($value)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // SPACE ('   ')
        if ($type & self::SPACE) {
            if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // NULL (null)
        if ($type & self::NULL) {
            if (($value === null) || (is_string($value) && ($value === 'null'))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::EMPTY_ARRAY) {
            if (is_array($value) && ($value == array())) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // ZERO ('0')
        if ($type & self::ZERO) {
            if (is_string($value) && ($value == '0')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // STRING ('')
        if ($type & self::STRING) {
            if (is_string($value) && ($value == '')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type & self::FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // INTEGER (0)
        if ($type & self::INTEGER) {
            if (is_int($value) && ($value == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // BOOLEAN (false)
        if ($type & self::BOOLEAN) {
            if (is_bool($value) && ($value == false)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }
        return true;
    }
}
