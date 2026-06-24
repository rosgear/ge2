<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Url;

/**
 * Класс PathSegments разбирает URL-путь на составные части (сегменты) и выполняет их последующею идентификацию.
 * 
 * URL-путь путь имеет ввид: "[/<сегмент-1>][/<сегмент-2>] /... [/<сегмент-n>]"
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Url
 * @since 2.0
 */
class PathSegments
{
    /**
     * Разделитель сегментов.
     *
     * @var string
     */
    protected string $delimiter = '/';

    /**
     * URL-путь.
     *
     * @var string
     */
    protected string $path = '';

    /**
     * Составные части (сегменты) URL-пути.
     *
     * @var array
     */
    protected array $segments = [];

    /**
     * Конструктор.
     * 
     * @param null|string $path URL-путь.
     * 
     * @return void
     */
    public function __construct(?string $path = null)
    {
        if ($path !== null) {
            $this->path = $path;
            $this->create($path);
        }
    }

    /**
     * Разбор URL-пути на сегменты.
     * 
     * @param string $path URL-путь.
     * 
     * @return $this
     */
    public function create(string $path): static
    {
        if (empty($path)) return $this;

        $this->segments = explode($this->delimiter, $path);
        return $this;
    }

    /**
     * Удаляет первый сегмент.
     * 
     * @return $this
     */
    public function shift(): static
    {
        array_shift($this->segments);
        return $this;
    }

    /**
     * Удаляет последний сегмент.
     * 
     * @return $this
     */
    public function pop(): static
    {
        array_pop($this->segments);
        return $this;
    }

    /**
     * Собирает сегменты с разделителем.
     * 
     * @return string
     */
    public function collect(): string
    {
        return implode($this->delimiter, $this->segments);
    }

    /**
     * Возвращает сегмент.
     * 
     * @param int $index Номер сегмента.
     * @param string $default Значение по умолчанию если сегмента нет.
     * 
     * @return string
     */
    public function get(int $index = 0, string $default = ''): string
    {
        if ($index == -1)
            $index = sizeof($this->segments) - 1;

        // если есть выбранный сегмент
        if (isset($this->segments[$index])) {
            return $this->segments[$index];
        }
        return $default;
    }

    /**
     * Возвращает сегменты.
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->segments;
    }

    /**
     * Проверяет сегмент.
     * 
     * @param int $index Номер сегмента (от 1 и до ...).
     * @param string $path Имя сегмента для проверки.
     * @param int $size Размер сегментов.
     * 
     * @return bool
     */
    public function check(int $index, string $path, int $size = 0): bool
    {
        if ($size) {
            return $this->get($index) === $path && $this->getSize() === $size;
        }
        return $this->get($index) === $path;
    }

    /**
     * Проверяет, есть ли сигменты.
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return sizeof($this->segments) === 0;
    }

    /**
     * Возвращает количество сегментов.
     * 
     * @return int
     */
    public function getSize(): int
    {
        return sizeof($this->segments);
    }

    /**
     * Возвращает первый сегмент.
     * 
     * @return string
     */
    public function first(): string
    {
        return $this->segments ? $this->segments[0] : '';
    }

    /**
     * Возвращает последний сегмент.
     * 
     * @return string
     */
    public function last(): string
    {
        return $this->segments ? $this->segments[sizeof($this->segments) - 1] : '';
    }

    /**
     * Возвращает все сегменты.
     * 
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Склеивает сегменты в строку.
     * 
     * @param null|string $delimiter Разделитель.
     * 
     * @return string
     */
    public function implode(?string $delimiter = null): string
    {
        if (empty($this->segments)) return '';
        
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }
        return implode($delimiter, $this->segments);
    }

    /**
     * Возвращает сегменты в виде строки.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->implode();
    }
}
