<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Shortcode\Handler;

use Ge\Shortcode\ShortcodeManager;
use Maiorano\Shortcodes\Library\SimpleShortcode;
use Maiorano\Shortcodes\Manager\ShortcodeManager as Processor;

/**
 * Обработчик шорткодов Maiorano.
 * 
 * @see https://github.com/maiorano84/shortcodes
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Shortcode\Handler
 * @since 2.0
 */
class MaioranoHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    protected function createProcessor(): static
    {
        $this->processor = new Processor;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultShortcodes(): static
    {
        /** @var ShortcodeManager $manager */
        $manager = $this->manager;
        /** @var Processor $processor */
        $processor = $this->processor;
        // preprocessor
        $this->processor->register(
            new SimpleShortcode('preprocessor',
                [],
                function ($content = null, array $attr = []) use ($manager, $processor) {
                    $manager->renderTo(
                        'html-preprocessor',
                        $processor->doShortcode($content)
                    );
                    return '';
                }
            )
        );
        // html-preprocessor
        $this->processor->register(
            new SimpleShortcode('html-preprocessor',
                [],
                function ($content = null, array $attr = []) use ($manager, $processor) {
                     $content = $manager->renderFrom('html-preprocessor');
                     return $content ?: '';
                }
            )
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerShortcodes(array $shortcodes): static
    {
        /** @var ShortcodeManager $manager */
        $manager = $this->manager;
        /** @var Processor $processor */
        $processor = $this->processor;

        foreach ($shortcodes as $name => $options) {
            // исключение для шорткода "if"
            if ($name === 'if') continue;
            $this->processor->register(
                new SimpleShortcode(
                    $name,
                    ['code' => $name], 
                    function($content = null, array $attr = []) use ($manager) {
                        if ($replace = $manager->replaceFrom($attr['code'])) {
                            return $manager->getContent($replace['code'], $replace['attributes'], $replace['content']);
                        } else {
                            return $manager->getContent($attr['code'], $attr, $content);
                        }
                    }
                )
            );
        }
        // регистрация шорткода "if"
        $this->processor->register(
            new SimpleShortcode(
                'if',
                [],
                function($content = null, array $attr = []) use ($processor, $manager) {
                    // если условие выполняется 
                    if ($manager->getContent('if', $attr, $content))
                        return $processor->doShortcode($content);
                    else
                        return '';
                }
            )
        );
        // регистрация шорткода по умолчанию
        $this->defaultShortcodes();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $text): string
    {
        return $this->processor->doShortcode($text);
    }
}
