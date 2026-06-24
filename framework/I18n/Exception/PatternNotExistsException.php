<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\Exception;

use Throwable;
use Ge\Exception\NotDefinedException;

/**
 * Исключение возникающие при отсутствии вызываемого шаблона локализации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\Exception
 * @since 2.0
 */
class PatternNotExistsException extends NotDefinedException
{
    /**
     * @var string Имя шаблона.
     */
    public string $patternName = '';

    /**
     * {@inheritdoc}
     * 
     * @param string $patternName Имя шаблона.
     */
    public function __construct(string $message = '',  string $patternName = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->patternName = $patternName;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Could not load locale pattern, pattern "%s" not exists', $this->patternName ? $this->patternName : 'unknow');
    }
}
