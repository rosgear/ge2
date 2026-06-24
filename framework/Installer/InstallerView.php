<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge\Stdlib\BaseObject;

/**
 * Представление установщика.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerView extends BaseObject
{
    /**
     * Абсолютный путь к файлам шаблона.
     *
     * @var string
     */
    public string $path = '';

    /**
     * Параметры в виде пар "ключ - значение" подставляемые в шаблон.
     *
     * @var array
     */
    public array $params = [];

    /**
     * Возвращает файл шаблона представления.
     *
     * @param string $name Имя представления, например 'welcome'.
     * 
     * @return string
     */
    public function getViewFile(string $name): string
    {
        return $this->path . DS . $name . '.phtml';
    }

    /**
     * Возвращает содержимое шаблона представления.
     *
     * @param string $name Имя представления, например 'welcome'.
     * @param array $params Параметры передаваемые в шаблон в виде пар "имя - значение".
     * 
     * @return string
     */
    public function render(string $name, array $params = []): string
    {
        return $this->renderPhpFile(
            $this->getViewFile($name),
            array_merge($this->params, $params)
        );
    }

    /**
     * Возвращает содержимое шаблона PHP-файла.
     *
     * @param string $filename Имя файла (включая путь).
     * @param array $params Параметры передаваемые в шаблон в виде пар "имя - значение".
     * 
     * @return string
     */
    public function renderPhpFile(string $filename, array $params = []): string
    {
        $obInitialLevel = ob_get_level();

        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        try {
            require $filename;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }
}
