<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Config;

/**
 * Класс определения MIME-тип содержимого файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Config
 * @since 2.0
 */
class Mimes extends Config
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $filename = 'config/.mimes.php', bool $useSerialize = false)
    {
        parent::__construct($filename, $useSerialize);
    }

    /**
     * Возвращает массив доступных расширений файлов из файла конфигурации MIME-тип.
     * 
     * @return array Результат: [["jpg", ".jpg"],...]
     */
    public function toList(): array
    {
        $items = [];
        foreach($this->container as $name => $info) {
            $items[] = [$name, '.' . strtoupper($name)];
        }
        return $items;
    }

    /**
     * Проверяет MIME-тип содержимое файла с его расширением.
     * 
     * @param string $filename Имя файла.
     * @param null|string $extension Расширение файла, которое необходимо проверить. 
     *    Если значение `null`, расширение определяется из имени файла.
     * 
     * @return bool Если значение `true`, расширение файла соответствует его MIME-типу.
     */
    public function checkMimeType(string $filename, ?string $extension = null): bool
    {
        if ($extension === null) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        }
        $info = $this->container[$extension] ?? null;
        // если не существует расширение файла
        if ($info === null) {
            return false;
        }
        $mime = mime_content_type($filename);
        // если mime-тип не найден
         if ($mime === false) {
            return false;
        }
        // проверка mime-типа
        if (is_array($info['mime'])) {
            return in_array($mime, $info['mime']);
        } else
            return $mime === $info['mime'];
    }

    /**
     * Возвращает расширение файла по указанному MIME-типу.
     * 
     * Расширение первое подходящие.
     * 
     * @param string $mime MIME-тип файла.
     * 
     * @return null|string Если значение `null`, расширение файла не найдено для указанного 
     *     MIME-типа.
     */
    public function getExtension(string $mime): ?string
    {
        foreach ($this->container as $extension => $info) {
            if (is_array($info['mime'])) {
                return in_array($mime, $info['mime']);
            } else
                return $mime === $info['mime'];
        }
        return null;
    }

    /**
     * Возвращает расширения файла по указанному MIME-типу.
     * 
     * @param string $mime MIME-тип файла.
     * 
     * @return array Если значение `null`, расширение файла не найдено для указанного 
     *    MIME-типа. Иначе, массив расширений файла.
     */
    public function getExtensions(string $mime): array
    {
        $extensions = [];
        foreach ($this->container as $extension => $info) {
            if (is_array($info['mime'])) {
                if (in_array($mime, $info['mime']))
                    $extensions[] = $extension;
            } else {
                if ($mime === $info['mime'])
                    $extensions[] = $extension;
            }
        }
        return $extensions;
    }

    /**
     * Проверяет, существует ли расширение с указанными параметрами (MIME-тип и/или тип).
     * 
     * @param string $extension Расширение файла.
     * @param null|string $mime MIME-тип, например 'application/zip' (по умолчанию `null`).
     * @param null|string $type Тип, например: 'archive', 'video', 'audio', 'script', 
     *     'ms', 'font', 'text' (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если расширение не проходит проверку.
     */
    public function exists(string $extension, ?string $mime = null, ?string $type = null): bool
    {
        if (isset($this->container[$extension])) {
            $row = $this->container[$extension];
            if ($mime && ($row['mime'] !== $mime)) {
                return false;
            }
            if ($type && ($row['type'] !== $type)) {
                return false;
            }
            return true;
        }
        return false;
    }
}