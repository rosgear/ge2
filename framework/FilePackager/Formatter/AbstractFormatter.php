<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\FilePackager\Formatter;

use Ge;
use Ge\Stdlib\ErrorTrait;
use Symfony\Component\Finder\Finder;

/**
 * Абстрактный класс форматирования свойств пакета файлов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FilePackager\Formatter
 * @since 2.0
 */
class AbstractFormatter
{
    use ErrorTrait;

    /**
     * Имя файла пакета (включает путь).
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Данные пакета файлов.
     * 
     * @var array<string, mixed>
     */
    protected array $properties = [];

    /**
     * Имена файлов (включая путь) с их псевдонимами.
     * 
     * Использует упаковщик {@see \Ge\FilePackager\FilePackager} для создания 
     * архива.
     * Имеет вид: `[['.../foobar/foobar.txt', 'f97fcecd31b78399a9662c8cdefbfc40.dat'], ...]`.
     * 
     * @see AbstractFormatter::addFiles()
     * @see AbstractFormatter::getFiles()
     * 
     * @var array<int, array>
     */
    protected array $packFiles = [];

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла пакета.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Возвращает свойства пакета файлов с добавлением свойств если они не были указаны.
     * 
     * @param array<string, mixed> $properties
     * 
     * @return array<string, mixed>
     */
    public function applyIf(array $properties): array
    {
        return array_merge([
            'id'      => '', // уникальный идентификатор содержимого пакета (можно не указывать)
            'type'    => '', // тип содержимого пакета, например: 'component', 'widget', ... (можно не указывать)
            'author'  => '', // автор пакета
            'name'    => '', // название пакета
            'note'    => '', // описание пакета
            'date'    => date('Y-m-d H:i:s'), // дата создания пакета
            'files'   => []
        ], $properties);
    }

    /**
     * Проверяет, существует ли файл пакета.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Загружает (читает) свойства пакета из файла.
     * 
     * @param bool $validate Если значение `true`, то будут проверены свойства пакета 
     *     после его чтения (по умолчанию `false`).
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка при чтении пакета 
     *     файлов.
     */
    public function load(bool $validate = false): bool
    {
        if (!$this->exists()) {
            $this->addError('File package not found');
            return false;
        }

        $content = file_get_contents($this->filename, true);
        if ($content === false) {
            // Невозможно прочитать данные из пакета файлов
            $this->addError('Can\'t to read data from package file');
            return false;
        }

        $properties = $this->parse($content);
        if ($properties === null) return false;

        $this->properties = $properties;
        if ($validate) {
            if (!$this->validate()) return false;
        }
        return true;
    }

    /**
     * Выполняет разбор строки в свойства пакета.
     * 
     * @param string $str
     * 
     * @return array|null Возвращает `null`, если была ошибка в разборе строки.
     */
    public function parse(string $str): ?array
    {
        return null;
    }

    /**
     * Событие перед записью свойств пакета.
     * 
     * @return bool
     */
    public function beforeSave(): bool
    {
        if (!$this->validate()) return false;

        if (empty($this->properties['date'])) {
            $this->properties['date'] = date('Y-m-d H:i:s');
        }
        return true;
    }

    /**
     * Записывает свойства пакета в файл.
     * 
     * @param bool $validate Выполнять проверку свойств пакета перед записью в файл.
     * 
     * @return bool Возвращает `false`, если была ошибка записи.
     */
    public function save(bool $validate = false): bool
    {
        if (!$this->beforeSave()) {
            return false;
        }

        $result = file_put_contents($this->filename, $this->toString());
        if ($result === false) {
            $this->addError('Can\'t to save package file' . (GE_DEBUG ? ' "' . $this->filename . '"' : ''));
            return false;
        }
        return true;
    }

    /**
     * Проверяет свойства пакета.
     * 
     * @return bool Возвращает значение `false`, если отсутствует одно из свойств пакета.
     */
    public function validate(): bool
    {
        $properties = ['files'];
        foreach ($properties as $name) {
            if (empty($this->properties[$name])) {
                $this->addError('The package does not have the property "' . $name . '"');
                return false;
            }    
        }
        return true;
    }

    /**
     * Устанавливает свойство пакета.
     * 
     * @param string $name Имя свойства.
     * @param mixed $value Значение свойства.
     * 
     * @return $this
     */
    public function set(string $name, mixed $value): static
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Устанавливает свойства пакету.
     * 
     * @param array $properties Имена свойств с их значениями.
     * 
     * @return $this
     */
    public function setProperties(array $properties): static
    {
        $this->properties = $this->applyIf($properties);
        return $this;
    }

    /**
     * Возвращает все свойства пакета.
     * 
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Возвращает значение свойства пакета.
     * 
     * @param string $name Имя свойства пакета.
     * @param mixed $default Значение по умолчанию.
     * 
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->properties[$name] ?? $default;
    }

    /**
     * Проверяет, имеет ли пакет свойства.
     * 
     * @return bool
     */
    public function hasProperties(): bool
    {
        return !empty($this->properties);
    }

    /**
     * Проверяет, имеет ли пакет свойство.
     * 
     * @param string $name Имя свойства пакета.
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * Удаляет свойство пакета.
     * 
     * @param string $name Имя свойства пакета.
     * 
     * @return $this
     */
    public function remove(string $name): static
    {
        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
        }
        return $this;
    }

    /**
     * Удаляет все свойства пакета.
     * 
     * @return $this
     */
    public function removeAll(): static
    {
        $this->properties = [];
        return $this;
    }

    /**
     * Возвращает все каталоги, которые указаны в файлах.
     * 
     * Применяется для создания каталогов перед извлечением файлов из пакета.
     * 
     * @return array
     */
    public function getFileDirectories(): array
    {
        $dirs = [];
        $files = $this->get('files', []);
        foreach ($files as $file) {
            $filename = pathinfo(Ge::getAlias($file['name']), PATHINFO_DIRNAME);
            $dirs[$filename] = true;
        }
        return array_keys($dirs);
    }

    /**
     * Добавляет имена файлов в свойство (files) пакета для дальнейшей упаковки.
     * 
     * @param string $searchPath Путь где находятся файлы для добавления.
     * @param string $toPath Путь, куда будут извлечены файлы.
     * 
     * @return void
     */
    public function addFiles(string $searchPath, string $toPath): void
    {
        if (!isset($this->properties['files'])) {
            $this->properties['files'] = [];
        }

        $finder = Finder::create();
        $finder->files()->in($searchPath)->ignoreDotFiles(false);
        foreach ($finder as $info) {
            $name = str_replace('\\', '/', $info->getRelativePath()) . '/' . $info->getFilename();
            $name = '/' . trim($name, '/');
            $id   =  md5($name);
            $this->properties['files'][] = [
                'id'   => $id,
                'hash' => md5_file($searchPath . $name),
                'name' => $toPath . $name
            ];

            $this->packFiles[] = [$searchPath . $name, $id . '.dat'];
        }
    }

    /**
     * Добавляет имя файлоа в свойство (files) пакета для дальнейшей упаковки.
     * 
     * @param string $realFilename Добавляемое имя файла (включает путь).
     * @param string $toFilename Имя файла, которое будет извлечено из пакета файлов 
     *     (может включать путь).
     * 
     * @return void
     */
    public function addFile(string $realFilename, string $toFilename): void
    {
        $id = md5($realFilename);
        $this->properties['files'][] = [
            'id'   => $id,
            'hash' => md5_file($realFilename),
            'name' => $toFilename
        ];
        $this->packFiles[] = [$realFilename, $id . '.dat'];
    }

    /**
     * Возвращает имена файлов предназначенных для архивирования.
     * 
     * @return array
     */
    public function getPackFiles(): array
    {
        $files = $this->packFiles;
        $files[] = [$this->filename, pathinfo($this->filename, PATHINFO_BASENAME)];
        return $files;
    }

    /**
     * Преобразует свойства пакета файлов в строку.
     * 
     * @return string
     */
    public function toString(): string
    {
        return '';
    }
}
