<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Filesystem;

use Ge;
use SplFileInfo;

/**
 * Вспомогательный класс для работы с файловой системой.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Taylor Otwell <taylor@laravel.com>
 * @package Ge\Filesystem
 * @since 2.0
 */
class Filesystem
{
    /**
     * Использовать исключения при неуспешном выполнении метода (по умолчанию false).
     * 
     * @var bool
     */
    public static bool $throwException = false;

    /**
     * Возвращает базовую директорию приложения.
     * 
     * @return string
     */
    public static function home(): string
    {
        return BASE_PATH;
    }

    /**
     * Возвращает директорию модуля (с суффиксом).
     * 
     * @param string $path Суффикс директории.
     * 
     * @return string
     */
    public static function module(string $path = ''): string
    {
        static $module = null;
        if ($module !== null) return $module . $path;

        $module = BASE_PATH . MODULE_PATH;
        return $module . $path;
    }

    /**
     * Возвращает базовую директорию приложения.
     * 
     * @see \Ge\Filesystem\Filesystem::home()
     * 
     * @return string
     */
    public static function base(): string
    {
        return BASE_PATH;
    }

    /**
     * Возвращает директории загрузки файлов (с суффиксом).
     * 
     * @param string $path Cуффикс директории.
     * 
     * @return string
     */
    public static function uploads(string $path = ''):  string
    {
        static $uploads = null;

        if ($uploads === null) {
            $uploads = Ge::$app->uploader->path;
        }
        return $uploads . $path;
    }

    /**
     * Определяет, существует ли файл или каталог.
     *
     * @param string $path Директория или файл.
     * 
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Определяет, отсутствует ли файл или каталог.
     *
     * @param string $path Директория или файл.
     * 
     * @return bool
     */
    public static function missing(string $path): bool
    {
        return !self::exists($path);
    }

    /**
     * Возвращает содержимое файла.
     *
     * @param string $filename Имя файла.
     * @param bool $lock Блокировать файл перед чтением его содержимого.
     * 
     * @return string
     *
     * @throws \Ge\Filesystem\Exception\FileNotFoundException
     */
    public static function get(string $filename, bool $lock = false): string
    {
        if (self::isFile($filename)) {
            return $lock ? self::sharedGet($filename) : file_get_contents($filename);
        }
        throw new Exception\FileNotFoundException($filename);
    }

    /**
     * Возвращает содержимое файла с общим доступом.
     *
     * @param string $filename Имя файла.
     * 
     * @return string
     */
    public static function sharedGet(string $filename): string
    {
        $contents = '';
        $handle = fopen($filename, 'rb');
        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $filename);
                    $contents = fread($handle, self::size($filename) ?: 1);
                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }
        return $contents;
    }

    /**
     * Возвращает значение подключаемого файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return mixed
     *
     * @throws \Ge\Filesystem\Exception\FileNotFoundException
     */
    public static function getRequire(string $filename): mixed
    {
        if (self::isFile($filename)) {
            return require $filename;
        }
        throw new Exception\FileNotFoundException($filename);
    }

    /**
     * Подключает указанный файл один раз.
     *
     * @param string $filename Имя файла.
     * 
     * @return void
     */
    public static function requireOnce(string $filename): void
    {
        require_once $filename;
    }

    /**
     * Возвращает MD5-хеш указанного файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return string|false Возвращает строку в случае успешного выполнения, иначе `false`.
     */
    public static function hash(string $filename): string|false
    {
        return md5_file($filename);
    }

    /**
     * Записывает данные в файл.
     *
     * @param string $filename Имя файла.
     * @param string $contents Записываемые данные.
     * @param bool $lock Получает эксклюзивную блокировку файла на время записи.
     * 
     * @return int|false Возвращает количество байтов, которые процесс записал в файл, 
     *     или `false` в случае ошибки. 
     */
    public static function put(string $filename, string $contents, bool $lock = false): int|false
    {
        return file_put_contents($filename, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Записывает данные в файл, заменив их автоматически, если файд уже существует.
     *
     * @param string $filename Имя файла.
     * @param string $content Содержимое.
     * 
     * @return void
     */
    public static function replace(string $filename, string $content): void
    {
        // Если путь уже существует и является символической ссылкой, получает реальный путь...
        clearstatcache(true, $filename);

        $filename = realpath($filename) ?: $filename;

        $tempPath = tempnam(dirname($filename), basename($filename));

        // Исправляет разрешения для tempPath, потому что `tempnam ()` создает его с разрешениями, установленными на 0600...
        self::chmod($tempPath, 0777 - umask());

        file_put_contents($tempPath, $content);

        self::move($tempPath, $filename);
    }

    /**
     * Записывает содержимое файла в его начало.
     *
     * @param string $filename Имя файла.
     * @param string $data Данные для записи.
     * 
     * @return int|false Возвращает количество байтов, которые процесс записал в файл, 
     *     или `false` в случае ошибки. 
     */
    public static function prepend(string $filename, string $data): int|false
    {
        if (self::exists($filename)) {
            return self::put($filename, $data . self::get($filename));
        }
        return self::put($filename, $data);
    }

    /**
     * Добавляет данные в конец файла.
     *
     * @param string $filename Имя файла.
     * @param mixed $data Данные для записи.
     * 
     * @return int|false Возвращает количество байтов, которые процесс записал в файл, 
     *     или `false` в случае ошибки. 
     */
    public static function append(string $filename, mixed $data): int|false
    {
        return file_put_contents($filename, $data, FILE_APPEND);
    }

    /**
     * Возвращает или устанавливает режим доступа к файлу или директории UNIX.
     *
     * @param string $path Путь к файлу или директория.
     * @param mixed $mode Разрешение. Если null, возвращает разрешение или 
     *    устанавливает. Имеет вид: 0755, '0755'.
     * 
     * @return mixed
     */
    public static function chmod(string $path, $mode = null): mixed
    {
        if ($mode) {
            if (is_string($mode)) {
                $mode = intval($mode, 8);
            }
            $success = chmod($path, $mode);
            // если есть исключение и ошибка
            if (self::$throwException && !$success) {
                throw new Exception\ChangeModeException($path, $mode);
            }
            return $success;
        }
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Удаляет файлы по указанному пути.
     *
     * @param string|array $paths Пути к файлу.
     * 
     * @return bool Возвращает значение `true` в случае успеха, иначе `false`.
     * 
     * @throws Exception\ErrorException
     * @throws Exception\DeleteException
     */
    public static function delete($paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $success = false;
                }
            } catch (Exception\ErrorException $e) {
                $success = false;
            }
        }
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\DeleteException($path, Ge::t('app', 'Could not perform file deletion "{0}"', [$path]));
        }
        return $success;
    }

    /**
     * Выполняет удаление файла.
     * 
     * @param string $filename Имя файла.
     * 
     * @return bool Возвращает значение `true` в случае успеха, иначе `false`.
     * 
     * @throws Exception\DeleteException Невозможно удалить файл.
     */
    public static function deleteFile(string $filename): bool
    {
        $success = true;
        try {
            if (!@unlink($filename)) {
                $success = false;
            }
        } catch (Exception\ErrorException $e) {
            $success = false;
        }
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\DeleteException($filename, Ge::t('app', 'Could not perform file deletion "{0}"', [$filename]));
        }
        return $success;
    }

    /**
     * Выполняет удаление файлов.
     * 
     * @param string $path Путь к файлам.
     * @param array<int, string> $exclude Исключить имена файлов, которые не надо удалять.
     * 
     * @return bool Возвращает значение `true` в случае успеха, иначе `false`.
     * 
     * @throws Exception\DeleteException Невозможно удалить файл.
     */
    public static function deleteFiles(string $path, array $exclude = []): bool
    {
        if (!is_dir($path)) {
            // если есть исключение
            if (self::$throwException) {
                throw new Exception\DeleteException($path, Ge::t('app', 'File name "{0}" is not a directory', [$path]));
            }
            return false;
        }
        // если есть исключение
        if ($exclude) {
            $exclude = array_fill_keys($exclude, true);
        }
        $it = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS); 
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile() || $fileInfo->isLink()) {
                if ($exclude && isset($exclude[$fileInfo->getFilename()])) {
                    continue;
                }
                $filename = $fileInfo->getPathname();
                $success = @unlink($filename);
                // если есть исключение и ошибка
                if (self::$throwException && !$success) {
                    throw new Exception\DeleteException($filename, Ge::t('app', 'Could not perform file deletion "{0}"', [$filename]));
                }
                if (!$success)
                    return false;
            }
        }
        return true;
    }

    /**
     * Переименовывает файл или директорию.
     *
     * @param string $oldname Старое имя.
     * @param string $newname Новое имя.
     * 
     * @return bool Возвращает значение `true` в случае успеха, иначе `false`.
     */
    public static function move(string $oldname, string $newname): bool
    {
        $success = rename($oldname, $newname);
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\RenameException($oldname, $newname);
        }
        return $success;
    }

    /**
     * Копирует файл из указанного в новое место.
     *
     * @param string $path Путь к исходному файлу.
     * @param string $target Путь назначения.
     * 
     * @return bool Возвращает значение `true` в случае успеха, иначе `false`.
     */
    public static function copy(string $path, string $target): bool
    {
        $success = copy($path, $target);
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\FileCopyException($path, $target);
        }
        return $success;
    }

    /**
     * Создаёт символическую ссылку на файл или директорию. 
     * 
     * В Windows жесткая ссылка создается, если целью является файл.
     *
     * @param string $target Файл или директория.
     * @param string $link Ссылка.
     * 
     * @return bool
     */
    public static function link(string $target, string $link): bool
    {
        if (!Ge::isWindowsOs()) {
            return symlink($target, $link);
        }

        $mode = self::isDirectory($target) ? 'J' : 'H';
        exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
        return true;
    }

    /**
     * Возвращает имя файла из его пути.
     *
     * @param string $path Путь к файлу.
     * 
     * @return string
     */
    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Возвращает базовое имя файла из его пути.
     *
     * @param string $path Путь к файлу.
     * 
     * @return string
     */
    public static function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Возвращает директорию файла.
     *
     * @param string $path Путь к файлу.
     * 
     * @return string
     */
    public static function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Возвращает расширение файла из его пути.
     *
     * @param string $path Путь к файлу.
     * 
     * @return string
     */
    public static function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Угадывает расширение файла по mime-типу указанного файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return string|null
     */
    public static function guessExtension(string $filename): ?string
    {
        if (!Ge::$app->services->hasInvokableClass('mimes')) {
            throw new Exception\RuntimeException(
                'To enable support for guessing extensions, please install the gear/mime service.'
            );
        }
        return Ge::$app->mimes->getExtensions(self::mimeType($filename))[0] ?? null;
    }

    /**
     * Возвращает тип указанного файла.
     *
     * @param string $path Имя файла.
     * 
     * @return string|false Возвращает `false` в случае возникновения ошибки.
     */
    public static function type(string $path): string|false
    {
        return filetype($path);
    }

    /**
     * Возвращает MIME-тип указанного файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return string|false Возвращает текстовое описание содержимого файла filename 
     *     или `false` в случае возникновения ошибки. 
     */
    public static function mimeType(string $filename): string|false
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);
    }

    /**
     * Возвращает размер указанного файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return int|false Возвращает размер указанного файла в байтах или `false` в 
     *     случае возникновения ошибки. 
     */
    public static function size(string $filename): int|false
    {
        return filesize($filename);
    }

    /**
     * Возвращает время записи блоков данных файла, то есть время, когда содержимое 
     * файла было изменено.
     *
     * @param string $filename Имя файла.
     * 
     * @return int|false Возвращает время последнего изменения указанного файла или 
     *     `false` в случае возникновения ошибки. 
     */
    public static function lastModified(string $filename): int|false
    {
        return filemtime($filename);
    }

    /**
     * Определяет, является ли имя файла директорией.
     *
     * @param string $filename Имя файла.
     * 
     * @return bool
     */
    public static function isDirectory(string $filename): bool
    {
        return is_dir($filename);
    }

    /**
     * Определяет, можно ли прочитать имя файла.
     *
     * @param string $filename Имя файла.
     * 
     * @return bool
     */
    public static function isReadable(string $filename): bool
    {
        return is_readable($filename);
    }

    /**
     * Определяет, доступно ли имя файла или директория для записи.
     *
     * @param string $filename Имя файла или директория.
     * 
     * @return bool
     */
    public static function isWritable(string $filename): bool
    {
        return is_writable($filename);
    }

    /**
     * Определяет, является ли имя файла обычным файлом.
     *
     * @param string $filename Имя файла.
     * 
     * @return bool
     */
    public static function isFile(string $filename): bool
    {
        return is_file($filename);
    }

    /**
     * Поиск пути, соответствующие шаблону.
     *
     * @param string $pattern Шаблон.
     * @param int $flags Флаг (GLOB_MARK, GLOB_NOSORT, GLOB_NOCHECK, 
     *    GLOB_NOESCAPE, GLOB_BRACE, GLOB_ONLYDIR , GLOB_ERR).
     * 
     * @return array|false Возвращает массив с совпавшими путями файлов и директорий, 
     *     пустой массив, если файл не найден, или `false` при ошибке. 
     */
    public static function glob(string $pattern, int $flags = 0): array|false
    {
        return glob($pattern, $flags);
    }

    /**
     * Возвращает массив всех файлов в директории.
     *
     * @param string $directory Директория.
     * @param bool $hidden Игнорировать файлы с точкой / скрытые (по умолчанию false).
     * @param null|callable $callback(splFileInfo $fileInfo) Функция обратного вызова.
     * 
     * @return array<int, string|SplFileInfo>
     */
    public static function files(string $directory, bool $hidden = false, $callback = null): array
    {
        $rows = iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->depth(0)->sortByName(),
            false
        );
        if ($callback !== null) {
            $result = [];
            foreach($rows as $fileInfo) {
                $value = $callback($fileInfo);
                if ($value)
                    $result[] = $value;
            }
            return $result;
        }
        return $rows;
    }

    /**
     * Возвращает все файлы из данной директории (рекурсивно).
     *
     * @param string $directory Директория.
     * @param bool $hidden Игнорировать файлы с точкой / скрытые (по умолчанию false).
     * @param null|callable $callback(splFileInfo $fileInfo) Функция обратного вызова.
     * 
     * @return array<int, string|SplFileInfo>
     */
    public static function allFiles(string $directory, bool $hidden = false, $callback = null)
    {
        $rows = iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->sortByName(),
            false
        );
        if ($callback !== null) {
            $result = [];
            foreach($rows as $fileInfo) {
                $value = $callback($fileInfo);
                if ($value)
                    $result[] = $value;
            }
            return $result;
        }
        return $rows;
    }

    /**
     * Возвращает SymfonyFinder, позволяющий создавать правила для поиска файлов и 
     * каталогов.
     * 
     * @return Finder
     */
    public static function finder(): Finder
    {
        return Finder::create();
    }

    /**
     * Возвращает все директории в указанной директории.
     *
     * @param string $directory Директория.
     * 
     * @return array
     */
    public static function directories(string $directory): array
    {
        $directories = [];
        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }
        return $directories;
    }

    /**
     * Возвращает все директории в указанной директории (рекурсивно).
     *
     * @param string $directory Директория.
     * 
     * @return array
     */
    public static function allDirectories(string $directory): array
    {
        $directories = [];
        foreach (Finder::create()->in($directory)->directories()->depth('> 0')->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }
        return $directories;
    }

    /**
     * Убедиться, что директория существует. Если директория не существует, 
     * тогда она будет создана.
     *
     * @param string $path Директория.
     * @param string|int $mode Разрешение на директорию (по умолчанию 0755).
     * @param bool $recursive Рекурсивное создание директории (по умолчанию true).
     * 
     * @return void
     */
    public static function ensureDirectoryExists(string $path, $mode = 0755, bool $recursive = true): void
    {
        if (!self::isDirectory($path)) {
            self::makeDirectory($path, $mode, $recursive);
        }
    }

    /**
     * Создаёт директорию.
     *
     * @param string $path Директория.
     * @param string|int $mode Разрешение на директорию (по умолчанию 0755).
     * @param bool $recursive Рекурсивное создание директории.
     * @param bool $force Игнорировать ошибки.
     * 
     * @return bool
     */
    public static function makeDirectory(string $path, $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if (file_exists($path)) return true;

        if (is_string($mode)) {
            $mode = intval($mode, 8);
        }

        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }
        $success = @mkdir($path, $mode, $recursive);
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\MakeDirectoryException($path);
        }
        return $success;
    }

    /**
     * Перемещает директорию.
     *
     * @param string $from Откуда переместить.
     * @param string $to Куда переместить.
     * @param bool $overwrite Перезаписать директорию куда 
     *    идёт перемещение (по умолчанию false)
     * 
     * @return bool
     */
    public static function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        if ($overwrite && self::isDirectory($to) && ! self::deleteDirectory($to)) {
            return false;
        }
        $success = @rename($from, $to) === true;
        // если есть исключение и ошибка
        if (self::$throwException && !$success) {
            throw new Exception\RenameException($from, $to);
        }
        return $success;
    }

    /**
     * Копирует директорию из одного места в указанное.
     *
     * @param string $directory Директория, которую необходимо скопировать.
     * @param string $destination Место копирования.
     * @param null|int $options Опции итератора файловой системы {@see \FilesystemIterator}.
     * 
     * @return bool
     */
    public static function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        if (!self::isDirectory($directory)) {
            return false;
        }
        $options = $options ?: \FilesystemIterator::SKIP_DOTS;

        // Если директория назначения не существует, мы идём дальше и создаём его рекурсивно, что подготовит место 
        // для копирования файлов. Как только создаться директория, копирование продолжится.
        self::ensureDirectoryExists($destination, 0777);

        $items = new \FilesystemIterator($directory, $options);
        foreach ($items as $item) {
            // По мере прохода элементов, проверяем, является ли текущий файл директорией или файлом. 
            // Если это директория, тогда необходимо рекурсивно вызывать эту функцию для продолжения 
            // копирования вложенных директорий.
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!self::copyDirectory($path, $target, $options)) {
                    return false;
                }
            }
            // Если текущий элемент - это обычный файл, тогда копируем его в новое место и продолжаем цикл. 
            // Если по какой-либо причине копирование не удастся, мы вернем false.
            else {
                if (!self::copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Рекурсивно удаляет директорию.
     *
     * Сама директория может быть сохранена после удаления её содержимого.
     *
     * @param string $directory Директория.
     * @param bool $preserve Удалить только содержимое директории.
     * @param int $counter Счётчик удалённых файлов и папок.
     * 
     * @return bool
     */
    public static function deleteDirectory(string $directory, bool $preserve = false, int &$counter = 0): bool
    {
        if (!self::isDirectory($directory)) {
            return false;
        }
        $items = new \FilesystemIterator($directory);
        foreach ($items as $item) {
            $counter++;
            // Если элемент является директорией, тогда вызываем функцию и удаляем этот подкаталог, 
            // в противном случае просто удалим файл и будем повторять до тех пор, 
            // пока каталог не будет очищен.
            if ($item->isDir() && ! $item->isLink()) {
                self::deleteDirectory($item->getPathname(), $preserve, $counter);
            }
            // Если элемент является просто файлом, удаляем его, так как перебираем все файлы 
            // в этой директории и снова рекурсивно вызываем, поэтому удаляем реальный путь.
            else {
                self::delete($item->getPathname());
            }
        }
        if (!$preserve) {
            $success = @rmdir($directory);
            // если есть исключение и ошибка
            if (self::$throwException && !$success) {
                throw new Exception\RemoveDirectoryException($directory);
            }
        }
        return true;
    }

    /**
     * Удаляет все директории в указанной директории.
     *
     * @param string $directory Директория.
     * 
     * @return bool
     */
    public static function deleteDirectories(string $directory): bool
    {
        $allDirectories = self::directories($directory);

        if (!empty($allDirectories)) {
            foreach ($allDirectories as $directoryName) {
                self::deleteDirectory($directoryName);
            }
            return true;
        }
        return false;
    }

    /**
     * Очищает указанную директорию от всех файлов и папок.
     *
     * @param string $directory Директория.
     * 
     * @return bool
     */
    public static function cleanDirectory(string $directory): bool
    {
        return self::deleteDirectory($directory, true);
    }

    /**
     * Возвращает права доступа на указанный файл.
     *
     * @param string $filename Имя файла с полным путём.
     * @param bool $digit  Отображение прав доступа в виде восьмеричного числа
     * @param bool $fullAccess Отображение полных прав доступа.
     * 
     * @return false|string Возвращает значение `false`, если невозможно определить права доступа.
     */
    public static function permissions(string $filename, bool $digit = true, bool $fullAccess = true): false|string
    {
        $perms = fileperms($filename);
        if ($perms === false) return false;

        $result = '';
        if ($digit)
            $result = substr(sprintf('%o', $perms), -4);
        if ($fullAccess) {
            if ($result)
                $result .= ' ';
            $result .= static::permissionsToStr($perms);
        }
        return $result;
    }

    /**
     * Приводит информацию о режиме доступа к файлу в cтроку.
     * 
     * Строка может иметь вид: "dr-xrwxr--", где 1-й символ:
     *     - "s" - сокет;
     *     - "l" - символическая ссылка;
     *     - "r" - обычный;
     *     - "b" - файл блочного устройства;
     *     - "d" - каталог;
     *     - "c" - файл символьного устройства;
     *     - "p" - FIFO канал;
     *     - "u" - неизвестный.
     * 
     * @param int $permissions Значение не переводится автоматически в восьмеричную систему счисления, 
     *     поэтому, необходимо предварять нулём (0) передаваемое значение (например: 0777).
     * 
     * @return string
     */
    public static function permissionsToStr(int $permissions): string
    {
        switch ($permissions & 0xF000) {
            case 0xC000: $result = 's'; break;
            case 0xA000: $result = 'l'; break;
            case 0x8000: $result = 'r'; break;
            case 0x6000: $result = 'b'; break;
            case 0x4000: $result = 'd'; break;
            case 0x2000: $result = 'c'; break;
            case 0x1000: $result = 'p'; break;
            default:
                $result = 'u';
        }
        
        // права владельца
        $result .= (($permissions & 0x0100) ? 'r' : '-');
        $result .= (($permissions & 0x0080) ? 'w' : '-');
        $result .= (($permissions & 0x0040) ?
                    (($permissions & 0x0800) ? 's' : 'x' ) :
                    (($permissions & 0x0800) ? 'S' : '-'));
        // групповые права
        $result .= (($permissions & 0x0020) ? 'r' : '-');
        $result .= (($permissions & 0x0010) ? 'w' : '-');
        $result .= (($permissions & 0x0008) ?
                    (($permissions & 0x0400) ? 's' : 'x' ) :
                    (($permissions & 0x0400) ? 'S' : '-'));
        // публичные права
        $result .= (($permissions & 0x0004) ? 'r' : '-');
        $result .= (($permissions & 0x0002) ? 'w' : '-');
        $result .= (($permissions & 0x0001) ?
                    (($permissions & 0x0200) ? 't' : 'x' ) :
                    (($permissions & 0x0200) ? 'T' : '-'));
        return $result;
    }

    /**
     * Приводит информацию о режиме доступа к файлу в массив значений.
     * 
     * Массив имеет вид:
     *    [
     *        "owner" => ["r" => true, "w" => true, "x" => true], // права владельца
     *        "group" => ["r" => true, "w" => true, "x" => true], // групповые права
     *        "world" => ["r" => true, "w" => true, "x" => true]  // публичные права
     *     ]
     * 
     * @param int $permissions Значение не переводится автоматически в восьмеричную систему счисления, 
     *     поэтому, необходимо предварять нулём (0) передаваемое значение (например: 0777).
     * 
     * @return array
     */
    public static function permissionsToArray(int $permissions): array
    {
        $result = ['owner' => [], 'group' => [], 'world' => []];
        $result['owner']['r'] = ($permissions & 0x0100) ? true : false;
        $result['owner']['w'] = ($permissions & 0x0080) ? true : false;
        $result['owner']['x'] = ($permissions & 0x0040) ? true : false;
        $result['group']['r'] = ($permissions & 0x0020 ) ? true : false;
        $result['group']['w'] = ($permissions & 0x0010 ) ? true : false;
        $result['group']['x'] = ($permissions & 0x0008 ) ? true : false;
        $result['world']['r'] = ($permissions & 0x0004  ) ? true : false;
        $result['world']['w'] = ($permissions & 0x0002 ) ? true : false;
        $result['world']['x'] = ($permissions & 0x0001) ? true : false;
        return $result;
    }
}
