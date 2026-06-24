<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Language\Exception;

use Throwable;
use Ge\Exception\NotFoundException;

/**
 * Исключение возникающие при отсутствии метаданных языка.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Language\Exception
 * @since 2.0
 */
class MetaNotFoundException extends NotFoundException
{
    /**
     * Формирование отчёта на основе исключения
     * 
     * @var bool
     */
    public bool $report = true;

    /**
     * Имя файла метаданных языка.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * {@inheritdoc}
     * 
     * @param string $filename Имя файла метаданных языка.
     */
    public function __construct(string $message = '', string $filename = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;

        parent::__construct(200, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Could not load language metafile, file with name "%s" not exists or not accessible', $this->filename ?: 'unknow');
    }
}
