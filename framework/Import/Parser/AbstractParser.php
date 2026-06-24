<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Import\Parser;

use Ge\Stdlib\ErrorTrait;

/**
 * Абстрактный класс форматирования свойств пакета файлов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FilePackager\Formatter
 * @since 2.0
 */
class AbstractParser
{
    use ErrorTrait;

    /**
     * Имя файла пакета (включает путь).
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла пакета.
     */
    public function __construct(string $filename = '')
    {
        $this->filename = $filename;
    }

    /**
     * Проверяет, существует ли файл пакета.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Выполняет разбор строки в свойства пакета.
     * 
     * @param string $filename
     * @param bool $package
     * 
     * @return array|false Возвращает `false`, если была ошибка в разборе строки.
     */
    public function parseFile(string $filename = '', bool $package = false): array|false
    {

        if ($filename !== '') {
            $this->filename = $filename;
        }

        if (!$this->exists()) {
            $this->addError('File "' . $filename . '" not found');
            return false;
        }

        $content = file_get_contents($this->filename, true);
        if ($content === false) {
            // Невозможно прочитать данные из пакета файлов
            $this->addError('Can\'t to read data from package file');
            return false;
        }

        if ($package)
            $data = $this->parsePackage($content);
        else
            $data = $this->parse($content);
        if ($data === null) return false;

        return $data;
    }

    /**
     * Выполняет разбор строки в свойства пакета.
     * 
     * @param string $str
     * 
     * @return array|null Возвращает `null`, если была ошибка в разборе строки.
     */
    public function parse(string $str): ?array
    {
        return null;
    }

    /**
     * Выполняет разбор строки в свойства пакета.
     * 
     * @param string $str
     * 
     * @return array|null Возвращает `null`, если была ошибка в разборе строки.
     */
    public function parsePackage(string $str): ?array
    {
        return null;
    }
}
