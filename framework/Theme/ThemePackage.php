<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme;

use Ge\Helper\Json;

/**
 * ThemePackage класс пакета информации темы.
 * 
 * Пакет информации темы представлен в виде файла "package.json" и находится в каталоге 
 * темы. Файл хранит информацию о теме в виде пар "ключ - значение". Пакет может иметь
 * следующие атрибуты (ключи):
 * - 'name', название темы, например: 'Good theme';
 * - 'description', описание темы, например: 'This theme is very good';
 * - 'author', автор темы, например: 'autho@mail.ru';
 * - 'license', лицензия, например: 'GPL-2.0-or-later';
 * - 'version', версия, например: '1.0';
 * - 'keywords', ключевые слова, например: `['good', 'theme']`;
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme
 * @since 1.0
 */
class ThemePackage
{
    /**
     * @var string Название файла пакета информации.
     */
    public const PACKAGE_FILE = 'package.json';

    /**
     * Название файла пакета информации (включает путь).
     * 
     * @var null|string
     */
    public ?string $filename = null;

    /**
     * Тема, которой принадлежит пакет информации.
     * 
     * Если тема не указана, полученный пакет информации будет иметь ограниченные 
     * свойства.
     * 
     * @var Theme
     */
    protected ?Theme $theme;

    /**
     * Конструктор класса.
     * 
     * @param string $path Полный путь (к теме) к файлу пакета информации.
     * @param null|Theme $theme Тема, которой принадлежит пакет информации (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(string $path, ?Theme $theme = null)
    {
        $this->theme = $theme;
        $this->setFilenameFromPath($path);
    }

    /**
     * Устанавливает файл пакета информации.
     * 
     * @param string $filename Файл пакета информации.
     * 
     * @return $this
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Устанавливает файл пакета информации по указанному пути.
     * 
     * @param string $path Полный путь к файлу пакета информации.
     * 
     * @return $this
     */
    public function setFilenameFromPath(string $path): static
    {
        $this->filename = rtrim($path, DS) . DS . self::PACKAGE_FILE;
        return $this;
    }

    /**
     * Возвращает файл (включает путь) пакета информации.
     * 
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Проверяет, существует ли файла пакета информации.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return $this->filename ? file_exists($this->filename) : false;
    }

    /**
     * Возвращает шаблон атрибутов пакета информации.
     * 
     * @return array
     */
    public function getInfoPattern(): array
    {
        return [
            'name'        => '',
            'description' => '',
            'author'      => '',
            'license'     => '',
            'version'     => '',
            'keywords'    => []
        ];
    }

    /**
     * Выполняет фильтрацию атрибутов пакета информации.
     * 
     * Если атрибуты попадают в фильтр, то возвратит `true`, иначе `false`.
     * 
     * @param array<string, mixed> $filter Фильтр в виде пар "ключ - значение".
     * @param array<string, mixed> $properties Атрибуты пакета информации в виде пар "ключ - значение".
     * 
     * @return bool
     */
    public function filter(array $filter, array $properties): bool
    {
        foreach ($filter as $property => $value) {
            if (isset($properties[$property])) {
                $cmpvalue = $properties[$property];

                if (is_array($cmpvalue)) {
                    if (in_array($value, $cmpvalue)) return true;
                } else
                if ($cmpvalue === $value) return true;
            }
        }
        return false;
    }

    /**
     * Сохраняет атрибуты пакета информации.
     * 
     * @param array<string, mixed> $properties Атрибуты пакета информации в виде 
     *     пар "ключ - значение".
     * 
     * @return bool
     */
    public function save(array $properties): bool
    {
        $properties = array_merge($this->getInfoPattern(), $properties);
        return Json::saveToFile(
            $this->filename, 
            $properties, 
            0, 
            null, 
            JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
        );
    }

    /**
     * Возвращает пакет информации темы.
     * 
     * @param array $filter Фильтр атрибутов в виде пар "ключ - значение".
     * 
     * @return array|null Возвращает значение `null` если не удалось прочитать пакет 
     *     или был применён фильтр. Иначе, информация о теме в виде пар атрибутов "ключ - значение".
     */
    public function getInfo(array $filter = []): ?array
    {
        if (empty($this->filename)) {
            return null;
        }

        Json::$throwException = false;

        /** @var false|array $info Информация о теме */
        $info = Json::loadFromFile($this->filename, true);
        if ($info) {
            $info = array_merge($this->getInfoPattern(), $info);
            // если используется фильтр
            if ($filter) {
                if ($this->filter($filter, $info))
                    return $info;
            } else
                return $info;
        }

        Json::$throwException = true;
        return null;
    }
}
