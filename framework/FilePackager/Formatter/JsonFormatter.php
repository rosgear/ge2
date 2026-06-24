<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\FilePackager\Formatter;

use Ge\Helper\Json;

/**
 * Форматирование свойств пакета файлов в формат JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FilePackager\Formatter
 * @since 2.0
 */
class JsonFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $str): ?array
    {
        $properties = Json::decode($str);
        if ($error = Json::error()) {
            // Невозможно получить данные из файла пакета
            $this->addError('Can\'t to get data from package file' . (GE_DEBUG ? " ($error)" : ''));
            return null;
        }
        return $this->applyIf($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return Json::encode($this->applyIf($this->properties));
    }
}
