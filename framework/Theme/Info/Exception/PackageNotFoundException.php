<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme\Info\Exception;

use Throwable;
use Ge\Exception\NotFoundException;

/**
 * Исключение возникающие при отсутствии файла с версией темы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme\Info\Exception
 * @since 2.0
 */
class PackageNotFoundException extends NotFoundException
{
    /**
     * Имя пакета тымы (файл версии темы).
     * 
     * @var string
     */
    public string $packagefile = '';

    /**
     * {@inheritdoc}
     * 
     * @param string $packagefile Имя пакета тымы (файл версии темы).
     */
    public function __construct(string $packagefile = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->packagefile = $packagefile;

        parent::__construct(200, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('The file theme package "%s" not found', $this->packagefile ?: 'unknow');
    }
}
