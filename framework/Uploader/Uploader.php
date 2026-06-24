<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Uploader;

use Ge;
use Ge\Config\Mimes;
use Ge\Stdlib\Service;
use Ge\Filesystem\Filesystem;

/**
 * Загрузчик предназначен для загрузки ресурсов на сервер.
 * 
 * Uploader - это служба приложения, доступ к которой можно получить через `Ge::$app->uploader`.
 * 
 * В отличии от других служб её параметры указываются в свойстве `$options`, кроме 
 * параметров 'localPath' и 'baseUrl'. Параметры `$options` будут переданы классу файла 
 * загрузки через его конструктор {@see \Ge\Uploader\Uploader::setFile()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Uploader
 * @since 2.0
 */
class Uploader extends Service
{
    /**
     * {@inheritdoc}
     */
     protected bool $useUnifiedConfig = true;

    /**
     * Базовый (локальный) путь загрузки.
     *
     * Указывается параметром "localPath" конфигурации сервиса "upload".
     * Пример: "/uploads".
     * 
     * @var string
     */
    public string $localPath = '';

    /**
     * Базовый (локальный) путь загрузки данных пользователей.
     *
     * Указывается параметром "localUserPath " конфигурации сервиса "upload" относительно 
     * базового (локального) пути загрузки {@see $localPath}.
     * Пример: если путь "/uploads/users", то $localUserPath = "/users".
     * 
     * @var string
     */
    public string $localUserPath = '/profile';

    /**
     * Абсолютный путь загрузки.
     * 
     * Имеет вид: "<абсолютный общедоступный путь/> <базовый (локальный) путь загрузки/>".
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Базовый URL-путь загрузки.
     * 
     * Указывается параметром "baseUrl" конфигурации сервиса "upload".
     * Пример: "/uploads".
     * 
     * @var string
     */
    public string $baseUrl = '';

    /**
     * Абсолютный URL-адрес загрузки.
     * 
     * Имеет вид: "<абсолютный общедоступный URL-адрес/> <базовый URL-путь загрузки/>".
     * 
     * @var string
     */
    public string $url = '';

    /**
     * Классы загружаемего файла на сервер в виде пар "тип - класс".
     *
     * @var array
     */
    public array $uploadClasses = [
        'default' => '\Ge\Uploader\UploadedFile',
        'empty'   => '\Ge\Uploader\UploadedEmptyFile',
        'image'   => '\Ge\Uploader\UploadedImageFile'
    ];

    /**
     * Определитель MIME-тип содержимого файла.
     * 
     * @var Mimes
     */
    protected Mimes $mimes;

    /**
     * Файлы загрузки.
     * 
     * @var array<string, UploadedFile>
     */
    protected array $files = [];

    /**
     * Параметры загрузки ресурсов.
     * 
     * Параметры передаются для каждого загруженного файла {@see \Ge\Uploader\UploadedFile}.
     * 
     * @see Uploader::setOptions()
     * 
     * @var array<string, mixed>
     */
    protected array $options = [
        // Правила загрузки файла
        'checkFileExtension' => false, // проверять расширение файла
        'checkMimeType'      => false, // проверять MIME-тип
        'allowedRoles'       => [], // без правил для ролей пользователей
        'allowedExtensions'  => [], // проверяемые расширения файлов
        // Новое имя файла после его загрузки
        'transliterateFilename' => false, // транслитерация имени файла
        'uniqueFilename'        => false, // формирование уникально имени файлу
        'escapeFilename'        => false, // исключить спец-е символы из имени файла
        'lowercaseFilename'     => false, // имя файла в нижнем регистре 
        'replaceFilenameChars'  => '-', // заменить специальные символы на
        'maxFilenameLength'     => 255, // максимальная длина имени файла
        // Установка значений директивам PHP при каждой загрузке файла
        'manuallySetDirectives' => false, // устанавливать значения директивам PHP
        'maxFileSize'           => 0, // максимальный размер загружаемого файла
        'maxFileUploads'        => 0 // максимальное количество загружаемых файлов
    ];

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'upload';
    }

    /**
     * {@inheritdoc}
     * 
     * @see \Ge\Stdlib\BaseObject::configure()
     */
    public function configure(array $config): void
    {
        /** @var array|null $params Парамеры из Унифицированного конфигуратора для 
         * загрузчика указываем вручную */
        $params = Ge::getUnified($this);
        if ($params) {
            // базовый (локальный) путь загрузки
            if (isset($params['localPath'])) {
                $this->localPath = $params['localPath'];
            }
            // базовый URL-путь загрузки
            if (isset($params['baseUrl'])) {
                $this->baseUrl = $params['baseUrl'];
            }
            // параметры загрузки ресурсов
            if (isset($params['options'])) {
                $this->setOptions($params['options']);
            }
        }

        if ($config) {
            // аргумент `$useUnifiedConfig = false`, т.к. выше установили вручную
            Ge::configure($this, $config, false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // абсолютный путь загрузки
        $this->path = Ge::$app->clientScript->publishedPath . $this->localPath;
        // абсолютный URL-адрес загрузки
        $this->url = Ge::$app->clientScript->publishedUrl . $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        Ge::setAlias('@upload', $this->path);
        Ge::setAlias('@upload::',   $this->url);
    }

    /**
     * Устанавливает параметры загрузки ресурсов.
     * 
     * @param array<string, mixed>|null $options Параметры загрузки ресурсов.
     * 
     * @return void
     */
    public function setOptions(?array $options): void
    {
        if ($options === null) return;

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Возвращает параметры загрузки ресурсов.
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Устанавливает локальный путь загрузки.
     * 
     * @param string $localPath Локальный путь загрузки.
     * 
     * @return void
     */
    public function setLocalPath(string $localPath): void
    {
        $this->localPath = $localPath;
        $this->path = Ge::$app->clientScript->publishedPath . $localPath;
    }

    /**
     * Устанавливает путь загрузки.
     * 
     * @param string $path Путь загрузки.
     * 
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
        Ge::setAlias('@upload', $this->path);
    }

    /**
     * Проверяет, существуе ли путь загрузки.
     * 
     * @return bool
     */
    public function pathExists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Создаёт путь загрузки.
     * 
     * @return bool
     */
    public function makePath(): bool
    {
        return Filesystem::makeDirectory($this->path, true);
    }

    /**
     * Возвращает определитель MIME-тип содержимого файла.
     *
     * @return Mimes
     */
    public function getMimes(): Mimes
    {
        if (!isset($this->mimes)) {
            $this->mimes = Ge::$services->getAs('mimes');
        }
        return $this->mimes;
    }

    /**
     * Устанавливает (создаёт) файл загрузки по указанной переменной файла в $_FILES.
     * 
     * @param string $inputName Переменная файла в $_FILES.
     * @param string $type Тип загружаемого файла, например: 'empty', 'image'. Если 
     *     значение '', то будет применятся класс `UploadedFile`  (по умолчанию 'default').
     * 
     * @return UploadedFile
     */
    public function setFile(string $inputName, string $type = 'default'): UploadedFile
    {
        /** @var string $className Класс загружаемего файла на сервер */
        $className = $this->uploadClasses[$type ?: 'default'] ?? $this->uploadClasses['default'];
        /** @var array $config Параметры конфигурации конструктора класса */
        $config = $this->options;
        $config['inputName'] = $inputName;
        $config['uploader']  = $this;
        return $this->files[$inputName] = new $className($config);
    }

    /**
     * Возвращает файл загрузки по указанной переменной файла в $_FILES.
     * 
     * @param string $inputName Переменная файла в $_FILES.
     * @param string $type Тип загружаемого файла, например: 'empty', 'image'. Если 
     *     значение '', то будет применятся класс `UploadedFile`  (по умолчанию 'default').
     * 
     * @return null|UploadedFile
     */
    public function getFile(string $inputName, string $type = 'default'): ?UploadedFile
    {
        if (isset($_FILES[$inputName])) {
            if (isset($this->files[$inputName]))
                return $this->files[$inputName];
            else {
                return $this->setFile($inputName, $type);
            }
        }
        return null;
    }

    /**
     *  Проверяет существование файла загрузки или переменной файла в $_FILES.
     * 
     * @param string $inputName Переменная файла в $_FILES.
     * @param bool $created Если значение `true`, то проверит существование файла 
     *     загрузки {@see Uploader::$files}. Иначе проверит $_FILES (по умолчанию `true`).
     * 
     * @return bool
     */
    public function hasFile(string $inputName, bool $created = true): bool
    {
        if ($this->created)
            return isset($this->files[$inputName]);
        else
            return isset($_FILES[$inputName]);
    }

    /**
     * Возвращает абсолютный путь к ресурсам пользователей.
     *
     * @return string
     */
    public function getUserPath(): string
    {
        return $this->path . $this->localUserPath;
    }

    /**
     * Возвращает абсолютный URL-адрес к ресурсам пользователей.
     *
     * @return string
     */
    public function getUserUrl(): string
    {
        return $this->url . $this->localUserPath;
    }
}
