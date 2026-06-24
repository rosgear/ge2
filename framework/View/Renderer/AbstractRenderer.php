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
 * Абстрактный класс визуализатора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Renderer
 * @since 2.0
 */
class AbstractRenderer
{
    /**
     * Обработчик содержимого перед его вывоводом.
     * 
     * @var mixed
     */
    protected mixed $contentHandler = null;

    /**
     * Массив переменных шаблона (для подстановки).
     * 
     * @var array
     */
    protected array $context = [];

    /**
     * Устанавливает обработчик содержимого.
     * 
     * @param object $handler Обработчик.
     * 
     * @return $this
     */
    public function setContentHandler(mixed $handler): static
    {
        $this->contentHandler = $handler;
        return $this;
    }

    /**
     * Возращает обработчик содержимого.
     * 
     * @return mixed
     */
    public function getContentHandler(): mixed
    {
        return $this->contentHandler;
    }

    /**
     * Добавление переменных в шаблон.
     * 
     * @param string $name Название переменной.
     * @param mixed $value Значение переменной.
     * 
     * @return $this
     */
    public function setContext(string $name, mixed $value = ''): static
    {
        if (is_array($name)) {
            $this->context = $name;
        } else {
            if ($value === null)
                unset($this->context[$name]);
            else
                $this->context[$name] = $value;
        }
        return $this;
    }

    /**
     * Возвращение значения переменной шаблона.
     * 
     * @param null|string $name Название переменной (по умолчанию `null`).
     * @param string $default Значение переменной по умолчанию (если не найдена).
     * 
     * @return mixed
     */
    public function getContext(?string $name = null, mixed $default = ''): mixed
    {
        if ($name === null) {
            return $this->context;
        }
        return isset($this->context[$name]) ? $this->context[$name] : $default;
    }

    /**
     * Замена содержимого после его формирования.
     * 
     * @param string $content Сформированное содержимое.
     * 
     * @return string
     */
    protected function replaceContext(mixed $content): mixed
    {
        return $content;
    }

    /**
     * Событие перед формированием содержимого.
     * 
     * @param array $variables Переменные в шаблона.
     * 
     * @return array
     */
    public function beforeRender(array $variables): array
    {
        return $variables;
    }

    /**
     * Событие после формирования содержимого.
     * 
     * @param mixed $content
     * 
     * @return mixed
     */
    public function afterRender(mixed $content): mixed
    {
        return $content;
    }
}
