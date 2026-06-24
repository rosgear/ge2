<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\FilePackager;

use ZipArchive;
use Ge\Stdlib\Component;
use Ge\Stdlib\ErrorTrait;

/**
 * Упаковщик файлов.
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
 * Пример создания пакета файлов:
 * ```php
 * // упаковщик файлов
 * $packager = new \Ge\FilePackager\FilePackager([
 *     'filename' => Ge::alias('@runtime') . DS . 'packages/foo-bar/foo-bar.gpk',
 * ]);
 * // пакет файлов
 * $package = $packager->getPackage(['format' => 'json']);
 * // добавление файлов в пакет
 * $package->addFiles(Ge::getAlias('@module' . '/foo-bar'), '@module' . '/foo-bar');
 * // проверка и сохранение файла пакета
 * if (!$package->save(true)) { die($package->getError()); }
 * // архивация пакета
 * if (!$packager->pack($package)) { die($packager->getError()); }
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FilePackager
 * @since 2.0
 */
class FilePackager extends Component
{
    use ErrorTrait;

    /**
     * Имя файла (архива) пакета с указаним пути.
     * 
     * Файл пакета имеет расширение ".gpk".
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * @param string $filename
     * 
     * @return string
     */
    public function definePath(string $filename): string
    {
        $info = pathinfo($filename);
        return $info['dirname'] . DS . $info['filename'];
    }

    /**
     * Возвращает ошибку по указанному коду.
     * 
     * @param int $code Код ошибки.
     * 
     * @return null|string
     */
    public function getErrorFromCode(int $code): ?string
    {
        switch ($code) {
            case ZipArchive::ER_EXISTS: return 'File already exists'; // Файл уже существует
            case ZipArchive::ER_INCONS: return 'Zip archive inconsistent'; // Несовместимый ZIP-архив
            case ZipArchive::ER_INVAL: return 'Invalid argument'; // Недопустимый аргумент
            case ZipArchive::ER_MEMORY: return 'Malloc failure'; // Ошибка динамического выделения памяти
            case ZipArchive::ER_NOENT: return 'No such file'; // Нет такого файла
            case ZipArchive::ER_NOZIP: return 'Not a zip archive'; // Не является ZIP-архивом
            case ZipArchive::ER_OPEN: return 'Can\'t open file'; // Невозможно открыть файл
            case ZipArchive::ER_READ: return 'Read error'; // Ошибка чтения
            case ZipArchive::ER_SEEK: return 'Seek error'; // Ошибка поиск.
        }
        return null;
    }

    /**
     * Создаёт пакет файлов.
     * 
     * @param array{format:string, path:string} $params Параметры пакета.
     * 
     * @return Package
     */
    public function getPackage(array $params = []): Package
    {
        return new Package($params);
    }

    /**
     * Распаковывает файлы пакета в его директорию.
     * 
     * @return bool Если значение `false`, то ошибка:
     *    - не может открыть архив;
     *    - не может извлечь файлы архива или его части в указанное место назначения.
     */
    public function unpack(Package $package): bool
    {
        $archive = new ZipArchive;
        $result = $archive->open($this->filename);
        if ($result === true) {
            if ($archive->extractTo($package->path) === true) {
                return true;
            }
            $this->addError('Can\'t extract file');
            return false;
        }
        $this->addErrorByCode($result, 'Can\'t open file');
        return false;
    }

    /**
     * Архивирует файлы из директории пакета.
     * Если архив существует, он будет заменен.
     * 
     * @return bool Если значение `false`, то ошибка:
     *    - не может создать архив;
     *    - не может сохранить изменения в созданном архиве;
     *    - нет файлов для архивирования.
     */
    public function pack(Package $package): bool
    {
        $archive = new ZipArchive;
        $result = $archive->open($this->filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result === true) {
            $files = $package->getPackFiles();
            foreach ($files as $file) {
                $archive->addFile($file[0], $file[1]);
            }

            // если не добавились файлы
            if ($archive->count() == 0) {
                $this->addError('The package has no files');
                return false;
            }
            $archive->close();
            return true;
        }
        $this->addErrorByCode($result, 'Can\'t open file');
        return false;
    }

    /**
     * Генерирует имя файла пакета.
     * 
     * @param string $baseName Базовое название файла.
     * @param string $version Номер версии файла.
     * 
     * @return string
     */
    public static function generateFilename(string $baseName, string $version = ''): string
    {
        $baseName = str_replace('.', '-', $baseName);
        if ($version) {
            $baseName  .= '_v' . str_replace('.', '_', $version);
        }
        return $baseName . '.gpk';
    }
}
