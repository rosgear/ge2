<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Stdlib;

use Ge;
use Ge\Exception;

/**
 * Класс сериализации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Stdlib
 * @since 2.0
 */
class Serializer
{
    /**
     * Имя файла сериализации.
     * 
     * @see Serializer::setFilename()
     * 
     * @var string
     */
    protected string $filename = '';

    /**
     * Аннулирует закешированный скрипт (файл сериализации).
     * 
     * @see Serializer::load()
     * @link https://www.php.net/manual/ru/function.opcache-invalidate.php
     * 
     * @var bool
     */
    public bool $opcacheInvalidate = true;

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла.
     * 
     * @return void
     */
    public function __construct(string $filename)
    {
        $this->setFilename($filename);
    }

    /**
     * Возвращает сгенерированное имя файла сериализации (с приставкой ".so.php").
     * 
     * @param string $str Имя файла.
     * 
     * @return string
     */
    public function genFilename(string $str): string
    {
        return str_replace('.php', '.so.php', $str);
    }

    /**
     * Возвращает имя файла сериализации.
     * 
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Устанавливает имя файла сериализации.
     * 
     * @param string $filename Имя файла сериализации.
     * 
     * @return $this
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $this->genFilename($filename);
        return $this;
    }

    /**
     * Уберает символы для сериализации файла.
     * 
     * Между `'` и `'` не должно быть кавычек.
     * Т.к. `return ' foo\'bar ';` возвратит `' foo'bar '` (сам уберает символ `\`).
     * 
     * @param string $str
     * 
     * @return string
     */
    public function escapeStr(string $str): string
    {
        return strtr($str, ['\'' => '`']);
    }

    /**
     * Сохраняет параметры в файл.
     * 
     * @param mixed $data Данные файла.
     * 
     * @return bool
     * 
     * @throws Exception\FormatException Невозможно сохранить данные в файл.
     * @throws \Exception
     */
    public function save(mixed $data): bool
    {
        $put = true;
        try {
            $str = serialize($data);
            $str = "<?php return '" . $this->escapeStr($str) . "' ?>";
            if (file_put_contents($this->filename, $str) === false) {
                $put = false;
                throw new Exception\FormatException(Ge::t('app', 'Unable to save config file'));
            }
        }  catch (\Exception $e) {
            Ge::error($e->getMessage());
        }
        return $put;
    }

    /**
     * Проверяет существование файла.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Загружает из файла.
     * 
     * @return mixed Данные файла сериализации. Если значение `false`, ошибка загрузки.
     * 
     * @throws Exception\FormatException Невозможно выполнить unserialize из загружаемого файла.
     */
    public function load(): mixed
    {
        if (!$this->exists()) {
            return false;
        }

        if ($this->opcacheInvalidate) {
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($this->filename, true);
            }
        }

        $str = require($this->filename);
        $data = unserialize($str);
        if ($data === false) {
            throw new Exception\FormatException(sprintf('Exception unserialize file "%s"', $this->filename));
        }
        return $data;
    }

    /**
     * Удаляет файл сериализации.
     * 
     * @return bool
     */
    public function delete(): bool
    {
        if (file_exists($this->filename))
            return unlink($this->filename);
        else
            return true;
    }
}