<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log\Writer;

use Ge;
use Ge\Filesystem\Filesystem;
use Ge\Log\Exception\LogException;
use Ge\Log\Exception\InvalidConfigException;
use Ge\Filesystem\Exception\MakeDirectoryException;

/**
 * Класс писателя журнала сообщений в файл.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
class FileWriter extends BaseWriter
{
    /**
     * Путь к файлу журнала или псевдоним пути.
     * 
     * Если не установлен, используется файл "@runtime::/logs/application.log".
     * Каталог, содержащий файлы журнала, будет создан автоматически, если он не существует.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@see AbstractWriter}.
     * 
     * @see FileWriter::initLogFile()
     * 
     * @var string
     */
    public string $logFile;

    /**
     * Максимальный размер файла журнала, в килобайтах.
     * По умолчанию 10240 килобайт ~ 10 МБ.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@see AbstractWriter}.
     * 
     * @var int
     */
    public int $maxFileSize = 10240;

    /**
     * Разрешение, которое будет установлено для созданного файла журнала.
     * 
     * Это значение будет установлено функцией PHP chmod().
     * Если разрешение не установлено, тогда будет определяться текущей средой.
     * 
     * @var string|int
     */
    public string|int $fileMode;

    /**
     * Разрешение, которое будет установлено для созданного каталога.
     * 
     * Это значение будет установлено функцией PHP chmod().
     * По умолчанию 0775, означает, что каталог доступен для чтения владельцу и группе, 
     * но для чтения только другими пользователей.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@see AbstractWriter}.
     * 
     * @var string|int
     */
    public string|int $dirMode = 0775;
 
    /**
     * Определение максимального размера файла журнала {@see FileWriter::$maxFileSize}.
     * 
     * @return $this
     */
    public function initMaxFileSize()
    {
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
        return $this;
    }
 
    /**
     * Определение имени файла журнала.
     * 
     * @see FileWriter::$logFile
     * 
     * @return $this
     */
    public function initLogFile(): static
    {
        if (!isset($this->logFile))
            $this->logFile = Ge::$app->getRuntimePath() . '/logs/application.log';
        else
            $this->logFile = Ge::getAlias($this->logFile);
        return $this;
    }
 
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->initMaxFileSize()
            ->initLogFile();
    }

    /**
     * @return bool
     */
    public function existsLogFile(): bool
    {
        return file_exists($this->logFile);
    }

    /**
     * Сохраняет текст в файл журнала.
     * 
     * @param string $filename Имя файла с путем.
     * @param mixed $text Содержимое файла.
     * 
     * @return void
     * 
     * @throws InvalidConfigException Невозможно открыть файл.
     * @throws LogException Невозможно экспортировать журнал через файл.
     */
    public function saveToFile(string $filename, mixed $text): void
    {
        if (($file = fopen($filename, 'a')) === false) {
            throw new InvalidConfigException(
                sprintf('Unable to append to log file "%s"', $filename)
            );
        }

        @flock($file, LOCK_EX);
        if (@filesize($filename) > $this->maxFileSize * 1024) {
            @flock($file, LOCK_UN);
            @fclose($file);
        } else {
            $writeResult = @fwrite($file, $text);
            if ($writeResult === false) {
                $error = error_get_last();
                throw new LogException(
                    sprintf('Unable to export log through file: %s', $error['message'])
                );
            }
            $textSize = strlen($text);
            if ($writeResult < $textSize) {
                throw new LogException(
                    sprintf('Unable to export whole log through file! Wrote %s out of %s bytes.', $writeResult, $textSize)
                );
            }
            @flock($file, LOCK_UN);
            @fclose($file);
        }

        if (isset($this->fileMode) && !empty($this->fileMode)) {
            $mode = is_string($this->fileMode) ? intval($this->fileMode, 8) : $this->fileMode;
            @chmod($filename, $mode);
        }
    }

    /**
     * {@inheritdoc}
     * 
     * @throws MakeDirectoryException
     */
    public function writeAll(): void
    {
        if (empty($this->messages)) {
            return;
        }

        $logPath = dirname($this->logFile);
        if (!Filesystem::makeDirectory($logPath, $this->dirMode, true)) {
            throw new MakeDirectoryException($logPath);
        }

        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        $this->saveToFile($this->logFile, $text);
    }
}
