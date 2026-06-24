<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Validator;

/**
 * Валидатор "Select" (проверка значений из выпадающего списка).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Select extends AbstractValidator
{
    public const NOT_SELECTED = 'notSelected';

    /**
     * {@inheritdoc}
     */
    protected array $messageTemplates = [
        self::NOT_SELECTED => 'You need to select an item from the list'
    ];

    /**
     * Возвращает значение `true`, если $value имеет тип integer.
     *
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if (!is_numeric($value)) {
            $this->error(self::NOT_SELECTED);
            return false;
        }
        return true;
    }
}
