<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Renderer;

use Ge;
use Ge\View\Exception;

/**
 * Визуализатор в PHP сценарии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Renderer
 * @since 2.0
 */
class PhpRenderer extends AbstractRenderer
{
    /**
     * Возвращение текста шаблона ввиде HTML-кода с подставлеными в него переменными.
     * 
     * @see PhpRenderer::renderFile()
     * 
     * @param array $variables Переменные шаблона.
     * @param string $filename Название файла шаблона.
     * 
     * @return string
     * 
     * @throws Exception\TemplateNotFoundException Файл шаблона не найден.
     */
    public function render(array $variables, string $filename): string
    {
        if (!file_exists($filename)) {
            throw new Exception\TemplateNotFoundException(
                Ge::t('app', 'Could not render by PhpRenderer, template "{0}" not exists', [$filename]), $filename
            );
        }
        return $this->renderFile($variables, $filename);
    }

    /**
     * Возвращает текст шаблона ввиде HTML-кода с подставлеными в него переменными.
     * 
     * @param mixed $variables Переменные в шаблоне.
     * @param mixed $filename Название файла шаблона.
     * 
     * @return string
     */
    protected function renderFile(array $variables, string $filename): string
    {
        ob_start();
       
        $variables = $this->beforeRender($variables);

        extract($variables);

        require $filename;
        
        $content = ob_get_clean();
        if ($this->contentHandler !== null) {
            $content = $this->contentHandler->process($content);
        }
        // если есть чем заменять
        if ($this->context) {
            $content = $this->replaceContext($content);
        }
        $content = $this->afterRender($content);
        return $content;
    }
}