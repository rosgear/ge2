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
use URLify;
use Ge\Stdlib\BaseObject;
use Ge\Filesystem\Filesystem;

/**
 * Класс загружаемего файла на сервер.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Uploader
 * @since 2.0
 */
class UploadedFile extends BaseObject
{
    /**
     * @var string Событие, возникшее после загрузке файла.
     */
    public const EVENT_AFTER_UPLOAD = 'afterUpload';

    /**
     * @var string Событие, возникшее после проверки параметров загружаемого файла.
     */
    public const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * @var int Ошибка превышения размера загруженного файла установленного в настройках.
     * 
     * @see UploadedFile::validate()
     */
    public const UPLOAD_ERR_FILE_EXTENSION = 9;

    /**
     * @var int Ошибка превышения размера загруженного файла установленного в настройках.
     * 
     * @see UploadedFile::validate()
     */
    public const UPLOAD_ERR_MAX_SIZE = 10;

    /**
     * @var int Ошибка неверного формата файла.
     * 
     * @see UploadedFile::validate()
     */
    public const UPLOAD_ERR_MIME_TYPE = 11;

    /**
     * @var int Ошибка перемещения загруженного файла.
     * 
     * @see UploadedFile::validate()
     */
    public const UPLOAD_ERR_MOVE = 12;

    /**
     * Имя файла загружаемого на сервер.
     * 
     * @var string
     */
    public string $name = '';

    /**
     * Базовое имя загружаемого файла.
     * 
     * @see UploadedFile::getBaseName()
     * 
     * @var string
     */
    public string $basename;

   /**
     * Имя загруженного файла, включая полный путь.
     * 
     * Значение устанавливается после успешной загрузки файла, если файл не был загружен 
     * или была ошибка загрузки, то значение ''.
     * 
     * @see UploadedFile::upload()
     * 
     * @var string
     */
    public string $uploadedFilename = '';

   /**
     * Имя временного, загруженного файла на сервере.
     * Временный файл, будет автоматически удален PHP после обработки текущего запроса.
     * 
     * @var string
     */
    public string $tempName = '';

    /**
     * Код ошибки, описывающий статус загрузки этого файла.
     * 
     * @link https://secure.php.net/manual/en/features.file-upload.errors.php.
     * 
     * @var int
     */
    public int $error = 0;

    /**
     *  Размер загруженного файла в байтах.
     * 
     * @var int
     */
    public int $size = 0;

    /**
     * MIME-тип загруженного файла (например, "image/gif"). Поскольку MIME-тип 
     * не проверяется на стороне сервера, то его необходимо проверять через 
     * {@see \Ge\Config\Mimes::checkMimeType()}.
     * 
     * @var string
     */
    public string $type = '';

    /**
     *  Имя указанное в $_FILES.
     * 
     * @var string
     */
    public string $inputName = '';

    /**
     * Загрузчик.
     * 
     * @var Uploader
     */
    public Uploader $uploader;

    /**
     * Доступные расширения файла.
     * 
     * Имеет вид: ['doc', 'pdf'...].
     * 
     * @var array
     */
    public array $allowedExtensions = [];

    /**
     * Роли пользователей для которых нет ограничений.
     * 
     * @var array
     */
    public array $allowedRoles = [];

    /**
     * Максимальный размер загружаемого файла в Мб.
     * 
     * Если значение "0" - определяются из опции PHP "upload_max_filesize".
     *  
     * @var int
     */
    public int $maxFileSize = 0;

    /**
     * Максимальное количество загружаемых файлов в течение одного запроса.
     * 
     * Если значение "0" - определяются из опции PHP "max_file_uploads".
     *  
     * @var int
     */
    public int $maxFileUploads = 0;

    /**
     * Установить вручную значения директивам PHP.
     * 
     * Для директив: upload_max_filesize, max_file_uploads, post_max_size.
     * 
     * @var bool
     */
    public bool $manuallySetDirectives = false;

    /**
     * Транслитерация имени файла с исходного языка на латиницу.
     * 
     * @var bool
     */
    public bool $transliterateFilename = true;

    /**
     * Формирование уникального имени файла с помощью 
     * хеш-функции.
     * 
     * @var bool
     */
    public bool $uniqueFilename = false;

    /**
     * Исключить специальные символы из имени файла.
     * 
     * @var bool
     */
    public bool $escapeFilename = false;

    /**
     * Имя файла в нижнем регистре.
     * 
     * @var bool
     */
    public bool $lowercaseFilename = false;

    /**
     * Замена специальных символов в имени файла на указанный символ.
     * 
     * @var string
     */
    public string $replaceFilenameChars = '-';

    /**
     * Максимальная длина имени загружаемого файла.
     * 
     * Если значение "0" - исходная длина имени файла.
     * 
     * @var int
     */
    public int $maxFilenameLength = 255;

    /**
     * Проверить расширение загруженного файла.
     * 
     * @var bool
     */
    public bool $checkFileExtension = true;

    /**
     * Проверить MIME-тип содержимого файла.
     * 
     * @var bool
     */
    public bool $checkMimeType = true;

    /**
     * Шаблон локального пути (папки) к загруженным файлам.
     * 
     * @see UploadedFile::getPathTemplate()
     * 
     * @var string
     */
    public string $pathTemplate = '';

    /**
     * Параметры передаваемые в шаблон локального пути (папки) в виде пар "ключ - значение".
     * 
     * @see UploadedFile::getPathTemplate()
     * 
     * @var array
     */
    public array $pathTemplateParams = [];

    /**
     * Имя файла.
     * 
     * @see UploadedFile::getFileName()
     * 
     * @var string
     */
    protected string $filename;

    /**
     * Расширение загружаемого файла.
     * 
     * @see UploadedFile::getExtension()
     * 
     * @var string
     */
    protected string $extension;

    /**
     * Результат загрузки файла.
     * 
     * Если ошибка загрузки файла, то значение `false`, иначе возвращает информацию 
     * с какими параметрами был загружен файл.
     * 
     * @see UploadedFile::uploaded()
     * @see UploadedFile::getResult()
     * 
     * @var array|false
     */
    protected array|false $result = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->defineParams();
    }

    /**
     * Определяет параметры загружаемго файла.
     * 
     * @return void
     */
    public function defineParams(): void
    {
        $file = $_FILES[$this->inputName] ?? null;
        if ($file) {
            $this->name     = $file['name'];
            $this->type     = $file['type'];
            $this->tempName = $file['tmp_name'];
            $this->filename = $this->uploader->path . DS . $this->name;
            $this->error    = $file['error'];
            $this->size     = $file['size'];
        } else
            $this->error = UPLOAD_ERR_NO_FILE;
    }

    /**
     * Перемещает загруженный файл в новое место.
     * 
     * Проверяет, является ли файл загруженным на сервер (переданным по протоколу 
     * HTTP POST). Если файл действительно загружен на сервер, он будет перемещён в место, 
     * указанное в аргументе to. 
     * 
     * @param null|string $to Путь (или новое имя файла) куда необходимо переместить 
     *     файл. Если значение `null`, то будет получено новое имя файла {@see UploadedFile::makeFilename()}.
     * 
     * @return bool Если значение `false`, ошибка перемещения файла.
     */
    public function upload(?string $to = null): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // перемещение загруженного файла
        if ($to === null) {
            $to = $this->makeFilenameByRule($this->name, true, true);
        }

        if (!@move_uploaded_file($this->tempName, $to)) {
            $this->afterUpload($this->tempName, $to, false);
            $this->error = self::UPLOAD_ERR_MOVE;
            return false;
        }

        $this->uploadedFilename = $to;

        if (!$this->uploaded($to)) {
            $this->afterUpload($this->tempName, $to, false);
            return false;
        }

        $this->afterUpload($this->tempName, $to, true);
        return true;
    }

    /**
     * Cобытие вызываемое после успешной загрузки файла.
     * 
     * @see UploadedFile::upload()
     * 
     * @param string $filename Имя файла, который будет загружен.
     * 
     * @return bool Возвращает значение `false`, если следующие действия после загрузки 
     *     файла завершились ошибкой.
     */
    protected function uploaded(string $filename): bool
    {
        $this->result = ['uploaded'  => $this->name, 'filename' => $filename];
        return true;
    }

    /**
     * Cобытие вызываемое после загрузки файла.
     * 
     * @see UploadedFile::upload()
     * 
     * @param string $filetemp Имя временного файла загруженного на сервер.
     * @param string $filename Имя файла, который будет размещен на сервере.
     * @param bool $success Значение `true`, если файл успешно загружен.
     * 
     * @return void
     */
    public function afterUpload(string $filetemp, string $filename, bool $success): void
    {
        $this->trigger(
            self::EVENT_AFTER_UPLOAD,
            [
                'filetemp' => $filetemp,
                'filename' => $filename,
                'success'  => $success
            ]
        );
    }

    /**
     * Перемещает загруженный файл в новое место.
     * 
     * Синоним @see UploadedFile::upload()
     * 
     * Проверяет, является ли файл загруженным на сервер (переданным по протоколу 
     * HTTP POST). Если файл действительно загружен на сервер, он будет перемещён в место, 
     * указанное в аргументе to. 
     * 
     * @param null|string $to Путь (или новое имя файла) куда необходимо переместить 
     *     файл. Если значение `null`, имя файла будет совпадать с именем загружаемого файла, 
     *     а путь {@see \Ge\Uploader\Uploader::$path}.
     * 
     * @return bool Если значение `false`, ошибка перемещения файла.
     */
    public function move(?string $to = null): bool
    {
        return $this->upload($to);
    }

    /**
     * Проверяет, был ли отправлен файл на сервер.
     * 
     * @return bool
     */
    public function hasUpload(): bool
    {
        return $this->error !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Возвращает результат загрузки файла.
     * 
     * @return array Если ошибка загрузки файла, то значение `false`, иначе возвращает 
     *     информацию с какими параметрами был загружен файл.
     */
    public function getResult(): array|false
    {
        return $this->result;
    }

    /**
     * @see UploadedFile::hasNoRules()
     * 
     * @var bool
     */
    protected bool $_noRules;

    /**
     * Проверяет, применяются ли правила при формировании имени файла.
     * 
     * Правила не применяются только для указанных ролей пользователей {@see UploadedFile::$allowedRoles}.
     * 
     * @return bool
     */
    public function hasNoRules(): bool
    {
        if (isset($this->_noRules)) return $this->_noRules;

        if ($this->allowedRoles) {
            /** @var \Ge\User\UserIdentity|null $identity  */
            $identity = Ge::userIdentity();
            // если пальзователь авторизован
            if ($identity) {
                /** @var mixed $roles Роли пользователя */
                $roles = $identity->getRoles();
                return $this->_noRules = $roles ? $roles->has($this->allowedRoles) : false;
            }
        }
        return $this->_noRules = false;
    }

    /**
     * Проверяет параметры загружаемого файла.
     * 
     * @return bool Если значение `false`, параметры файла не удовлетворяют условию 
     *     проверки.
     */
    public function validate(): bool
    {
        // если были ошибки при загрузке
        if ($this->hasError()) {
            return false;
        }

        // проверка размера файла
        if ($this->size < 1) {
            $this->afterValidate(false);
            $this->error = self::UPLOAD_ERR_MAX_SIZE;
            return false;
        }

        // без правил для ролей пользователей (из конфигурации загрузчика)
        if (!$this->hasNoRules()) {
            // проверка MIME-типа файла (из конфигурации загрузчика)
            if ($this->checkMimeType) {
                $mimes = $this->uploader->getMimes();
                if (!$mimes->checkMimeType($this->tempName, $this->getExtension())) {
                    $this->afterValidate(false);
                    $this->error = self::UPLOAD_ERR_MIME_TYPE;
                    return false;
                }
            }
            // проверка расширения загружаемого файла (из конфигурации загрузчика)
            if ($this->checkFileExtension) {
                // доступное расширение для загрузки (из конфигурации загрузчика)
                if ($this->allowedExtensions) {
                     if (!in_array($this->getExtension(), $this->allowedExtensions)) {
                        $this->afterValidate(false);
                        $this->error = self::UPLOAD_ERR_FILE_EXTENSION;
                        return false;
                     }
                }
            }
        }
        $this->afterValidate(true);
        return true;
    }

    /**
     * Cобытие вызываемое после проверки параметров загружаемого файла.
     * 
     * @see UploadedFile::validate()
     * 
     * @param bool $success Значение `true`, если параметры загружаемого файла успешно проверены.
     * 
     * @return void
     */
    public function afterValidate(bool $success): void
    {
        $this->trigger(
            self::EVENT_AFTER_VALIDATE,
            ['success'  => $success]
        );
    }

    /**
     * Устанавливает параметры загрузки.
     * 
     * @param array $options Параметры в виде пар "ключ - значение".
     * 
     * @return void
     */
    public function setOptions(array $options): void
    {
        // исключение
        if (!empty($options['allowedExtensions'])) {
            if (is_string($options['allowedExtensions'])) {
                $options['allowedExtensions'] = explode(',', $options['allowedExtensions']);
            }
        }

        foreach ($options as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Создаёт уникальное имя загружаемого файла.
     * 
     * @return string
     */
    public function makeUniqueFilename(string $filename): string
    {
        return md5($filename . time());
    }

    /**
     * Создаёт название файла по правилам преобразования, указанных в загрузчике.
     * 
     * @see UploadedFile::$name
     * @see UploadedFile::$filename
     * 
     * @return string
     */
    public function makeFilenameByRule(string $filename, bool $includePath = true, bool $makePath = false): string
    {
        // без правил для ролей пользователей (из конфигурации загрузчика)
        if (!$this->hasNoRules()) {
            // уникальное имя загружаемого файла (из конфигурации загрузчика)
            if ($this->uniqueFilename) {
                $basename = $this->makeUniqueFilename($filename);
            } else {
                $replaceChars = $this->replaceFilenameChars ?: '-';
                $basename     = pathinfo($filename, PATHINFO_FILENAME);;
                // максимальная длина имени загружаемого файла (из конфигурации загрузчика)
                $maxNameLength = $this->maxFilenameLength ?: 255;
                // имя файла в нижнем регистре
                $lowercase = $this->lowercaseFilename;
                // исключить специальные символы из имени файла (из конфигурации загрузчика)
                if ($this->escapeFilename) {
                    $basename = URLify::downcode($basename);
                    $basename = preg_replace('/\W+/',$replaceChars, $basename);
                } 
                // транслитерация имени файла с исходного языка на латиницу (из конфигурации загрузчика)
                if ($this->transliterateFilename) {
                    $basename = URLify::filter($basename, $maxNameLength, '', true, false, $lowercase, $replaceChars);
                } else {
                    $basename = mb_substr($basename, 0, $maxNameLength);
                    if ($lowercase)
                        $basename = strtolower($basename);
                }
            }
        } else {
            $basename = pathinfo($filename, PATHINFO_FILENAME);
        }

        $filename = $basename . '.' . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($includePath) {
            // абсолютный путь загрузки
            $path = $this->uploader->path;
            if (!file_exists($path)) {
                Filesystem::makeDirectory($path, '0755', true);
            }

            // если указан шаблон локального пути
            if ($this->pathTemplate) {
                $path = rtrim($path, '\/') . DS . $this->getPathTemplate();
                if ($makePath) {
                    if (!file_exists($path)) {
                        Filesystem::makeDirectory($path, '0755', true);
                    }
                }
            }
            return $path . DS . $filename;
        }
        return $filename;
    }

    /**
     * Добавляег слаг в имя файла.
     * 
     * @param string $slug Слаг в имени файла.
     * @param string $filename Имя файла.
     * @param string $separator Разделитель имени файла и слага.
     * 
     * @return string
     */
    public function addSlugToFilename(string $slug, string $filename, string $separator = '_'): string
    {
        $parts = pathinfo($filename);
        return $parts['dirname'] . DS . $parts['filename'] . $separator . $slug . '.' . $parts['extension'];
    }

    /**
     * Шаблон локального пути (папки).
     * 
     * @see UploadedFile::getPathTemplate()
     * 
     * @var PathTemplate
     */
    protected PathTemplate $_pathTemplate;

    /**
     * Возвращает шаблон локального пути (папки).
     * 
     * @return PathTemplate
     */
    public function getPathTemplate(): PathTemplate
    {
        if (!isset($this->_pathTemplate)) {
            $this->_pathTemplate = new PathTemplate(
                $this->pathTemplate,
                $this->pathTemplateParams
            );
        }
        return $this->_pathTemplate;
    }

    /**
     * Возвращает строку.
     * 
     * Это магический метод PHP, который возвращает строковое представление объекта.
     * Реализация здесь возвращает имя загруженного файла.
     * 
     * @return string Имя загруженного файла (не включает путь).
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Возвращает расширение загруженного файла.
     * 
     * @return string
     */
    public function getExtension(): string
    {
        if (!isset($this->extension)) {
            $this->extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        }
        return $this->extension;
    }

    /**
     * Возвращает имя файла загруженного сервер (включает путь загрузки).
     * 
     * @return string
     */
    public function getFileName(): string
    {
        if (!isset($this->filename)) {
            $this->filename = $this->uploader->path . DS . $this->name;
        }
        return $this->filename;
    }

    /**
     * Возвращает базовое имя из имени загружаемого файла.
     * 
     * @return string
     */
    public function getBaseName() :string
    {
        if (!isset($this->basename)) {
            $this->basename = pathinfo($this->name, PATHINFO_FILENAME);
        }
        return $this->basename;
    }

    /**
     * Проверяет, была ли ошибку при загрузке файла.
     * 
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error != UPLOAD_ERR_OK;
    }

    /**
     * Возвращает строковое представление ошибки, полученной при загрузке файла.
     * 
     * @return string
     */
    public function getErrorMessage() :string
    {
        return $this->hasError() ? $this->codeToMessage($this->error) : '';
    }

    /**
     * Возвращает строковое представление ошибки по указанному коду.
     * 
     * @param int $code Код ошибки.
     * 
     * @return string
     */
    protected function codeToMessage(int $code): string
    {
        switch ($code) {
            case self::UPLOAD_ERR_FILE_EXTENSION: 
                return 'Invalid file extension';

            case self::UPLOAD_ERR_MIME_TYPE: 
                return 'Invalid file format';

            case self::UPLOAD_ERR_MAX_SIZE: 
                return 'The uploaded file exceeds the size set in the settings';

            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';

            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';

            case UPLOAD_ERR_PARTIAL: 
                return 'The uploaded file was only partially uploaded';

            case UPLOAD_ERR_NO_FILE: 
                return 'No file was uploaded';

            case UPLOAD_ERR_NO_TMP_DIR: 
                return 'Missing a temporary folder';

            case UPLOAD_ERR_CANT_WRITE: 
                return 'Failed to write file to disk';

            case UPLOAD_ERR_EXTENSION: 
                return 'File upload stopped by extension';
        }
        return 'Unknown upload error';
    }
}
