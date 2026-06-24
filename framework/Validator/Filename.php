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
 * Валидатор "Filename" (проверка имени файла или директории).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class Filename extends PregMatch
{
    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'format'  => '/^[a-zA-Z0-9_\-\.]+$/', 
        'message' => ''
    ];
}
