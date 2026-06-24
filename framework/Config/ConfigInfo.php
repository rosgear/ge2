<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Config;

use Ge\Stdlib\BaseObject;

/**
 * Класс возвращает информацию из подключаемых файлов конфигурации.
 * 
 * Каждое значение параметра конфигурации будет отформатировано соответствующим методом.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Config
 * @since 2.0
 */
class ConfigInfo extends BaseObject
{
    /**
     * Возвращает параметры из указанного файла конфигурации.
     * 
     * Перед возвращением, каждый параметр конфигурации будет отформатирован если 
     * класс имеет соответствующий метод.
     * 
     * Имя метода определяется именем параметра и имеет вид: `format<parameterName>($value, $options)`.
     * Где, `$value` значение параметра, а `$options` - имена и значения остальных параметров.
     * 
     * @param string $filename Имя файла конфигурации (включает путь).
     * @param array $default Параметры конфигурации по умолчанию.
     * @param bool $format Если `true`, форматировать значения параметров конфигурации.
     * 
     * @return array
     * 
     * @throws Exception\FileNotFoundException Файл не найден или не имеет параметров.
     */
    public function loadFileInfo(string $filename, array $default = [], bool $format = true): array
    {
        if (!file_exists($filename)) {
            throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" not found.', $filename));
        }
        $config = require($filename);
        if (empty($config) || !is_array($config)) {
            throw new Exception\FileNotFoundException(sprintf('Configuration file "%s" has no parameters.', $filename));
        }
        // форматировать значения параметров
        if ($format) {
            foreach ($config as $name => &$value) {
                $method = 'format' . $name;
                if (method_exists($this, $method)) {
                    $value = $this->{$method}($value, $config);
                }
            }
        }
        if ($default) {
            return array_merge($default, $config);
        }
        return $config;
    }
}