<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Renderer;

/**
 * Визуализатор по умолчанию.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Renderer
 * @since 2.0
 */
class DefaultRenderer extends AbstractRenderer
{
    /**
     * Вывод данных в JSON-представлении.
     * 
     * @param mixed $variables Данные для вывода.
     * 
     * @return void
     */
    public function render(mixed $variables): void
    {
        echo json_encode($variables);
    }
}
