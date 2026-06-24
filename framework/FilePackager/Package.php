<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\FilePackager;

use Ge;
use Ge\Stdlib\BaseObject;
use Ge\Stdlib\ErrorTrait;
use Ge\Filesystem\Filesystem;
use Ge\FilePackager\Formatter\AbstractFormatter;

/**
 * Пакет файлов.
 * 
 * Пример извлечения файлов из пакета:
 * ```php
 * // упаковщик файлов
 * $packager = new \Ge\FilePackager\FilePackager([
 *     'filename' => Ge::alias('@runtime') . DS . 'packages\foo-bar\foo-bar.gpk',
 * ]);
 * // пакет файлов
 * $package = $packager->getPackage([
 *     'path'   => Ge::alias('@runtime') . DS . 'packages\foo-bar',
 *     'format' => 'json'
 * ]);
 * // извлечение файлов из архива
 * if (!$packager->unpack($package)) { die($packager->getError()); }
 * // получение информации о пакете файлов с их проверкой
 * if (!$package->load(true)) { die($package->getError()); }
 * // перемещение файлов пакета в место их расположения
 * if (!$package->extract()) { die($package->getError()); }
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FilePackager
 * @since 2.0
 */
class Package extends BaseObject
{
    use ErrorTrait;

    /**
     * Абсолютный (временный) путь для извлечения файлов пакета из архива.
     * 
     * В таком каталоге будут находиться извлеченные файлы архива с расширением ".dat" и 
     * пакет файлов ("package.json", "package.xml"...).
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Формат пакета файлов ('xml', 'json'...).
     * 
     * @var null|string
     */
    public ?string $format = null;

    /**
     * Классы форматеров пакетов файлов.
     * 
     * @var array
     */
    protected array $invokableFormatters = [
        'json' => 'Ge\FilePackager\Formatter\JsonFormatter'
    ];

    /**
     * Форматер пакета файлов.
     * 
     * @see Package::getFromatter()
     * 
     * @var AbstractFormatter
     */
    protected AbstractFormatter $formatter;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->format === null || !isset($this->invokableFormatters[$this->format])) {
            $this->addError('Property "format" not specified');
        }
        $this->formatter = $this->getFormatter();
    }

    /**
     * @param string $name Имя вызываемого метода.
     * @param array $arguments Массив аргументов для передачи методу.
     * 
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->formatter, $name], $arguments);
    }

    /**
     * Проверяет, существует ли свойство пакета, когда обращаются как к свойству объекта. 
     * 
     * @param string $name Имя свойства пакета.
     * 
     * @return bool Если значение `true`, свойство пакета существует.
     */
    public function __isset(string $name)
    {
        return $this->formatter->has($name);
    }

    /**
     * Устанавливает значение свойству пакету, когда к свойству обращаются как к 
     * свойству объекта. 
     *
     * @param string $name Имя свойства пакета.
     * @param mixed $value Значение свойства.
     * 
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->formatter->set($name, $value);
    }

    /**
     * Возвращает значение свойства пакета, когда к свойству обращаются как к 
     * свойству объекта. 
     *
     *  @param string $name Имя свойства пакета.
     * 
     * @return mixed Если `null`, ключ свойство пакета не существует.
     */
    public function __get(mixed $name)
    {
        return $this->formatter->get($name);
    }

    /**
     * Удаляет свойство из пакета, когда обращаются как к свойству объекта. 
     *
     * @param string $name Имя свойства пакета.
     * 
     * @return void
     */
    public function __unset(string $name)
    {
        $this->formatter->remove($name);
    }

    /**
     * Проверяет, есть ли ошибка.
     * 
     * @return bool Возвращает значение `true`, если есть ошибка.
     */
    public function hasErrors(): bool
    {
        return empty($this->errors) ? $this->formatter->hasErrors() : true;
    }

    /**
     * Возвращает первую в очереди ошибку.
     * 
     * @return string Текст ошибки.
     */
    public function getError(): string
    {
        return $this->errors[0] ?? $this->formatter->getError();
    }

    /**
     * Создаёт форматер пакета файлов.
     * 
     * @param string $format Формат данных пакета файлов ('xml', 'json',...).
     * 
     * @return AbstractFormatter
     * 
     * @throws Exception\FormatException Формат пакета файлов отсутствует.
     */
    public function createFormatter(string $format): AbstractFormatter
    {
        $className = $this->invokableFormatters[$format] ?? null;
        if ($className === null) {
            throw new Exception\FormatException(Ge::t('app', 'The specified file package data format "{0}" does not exist', [$format]));
        }
        return Ge::createObject($className,  $this->getFilename());
    }

    /**
     * Возвращает форматер пакета файлов.
     * 
     * @see Package::createFormatter()
     * 
     * @return AbstractFormatter
     * 
     * @throws Exception\FormatException Формат пакета файлов отсутствует.
     */
    public function getFormatter(): AbstractFormatter
    {
        if (!isset($this->formatter)) {
            $this->formatter = $this->createFormatter($this->format);
        }
        return $this->formatter;
    }

    /**
     * Определяет формат пакета файлов из указанного пути.
     * 
     * @return string|null Возвращает значение `null`, если формат пакета файлов не определён.
     */
    public function defineFormat(): ?string
    {
        $filename = $this->path . DS . 'package.';
        foreach ($this->invokableFormatters as $format => $className) {
            if (file_exists($filename . $format)) {
                return $format;
            }
        }
        return null;
    }

    /**
     * Возвращает название пакета файла.
     * 
     * @return string
     */
    public function getFilename(): string
    {
        return $this->path . DS . 'package.' . $this->format;
    }

    /**
     * Извлекает все файла указанные в пакете в место их расположения.
     * 
     * @return bool Возвращает `false`, если возникла ошибка.
     */
    public function extract(): bool
    {
        // создание каталогов для файлов пакета
        $directories = $this->formatter->getFileDirectories();
        foreach ($directories as $path) {
            if (!file_exists($path)) {
                Filesystem::makeDirectory($path, 0755, true);
            }
        }

        // копирование файлов пакета в место их расположения
        $files = $this->formatter->get('files', []);
        foreach ($files as $file) {
            $from = $this->path . DS . $file['id']  . '.dat';
            $to   = Ge::getAlias($file['name']);
            if (!Filesystem::copy($from, $to)) {
                $this->addError('Could not copy file' . (GE_DEBUG ? ' "' . $from . '"' : ''));
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет, существуют ли файлы указанные в пакете.
     * 
     * @return false|array Возвращает `false`, если файлы указанные в пакете не 
     *     существуют. Иначи, массив файлов, которые уже существуют.
     */
    public function fileExists(): false|array
    {
        $exists = [];

        $files = $this->formatter->get('files', []);
        foreach ($files as $file) {
            $filename = Ge::getAlias($file['name']);
            if (file_exists($filename)) {
                $exists[] = $filename;
            }
        }
        return $exists ? $exists : false;
    }
}
