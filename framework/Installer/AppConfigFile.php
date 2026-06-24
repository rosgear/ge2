<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

/**
 * Класс создания файла конфигурации приложения из шаблона.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class AppConfigFile
{
    /**
     * Файл шаблона конфигурации приложения.
     * 
     * @see AppConfigFile::getSampleFilename()
     * 
     * @var string
     */
    protected string $smplFilename;

    /**
     * Имя файла конфигурации, включая путь к устанавливаемому приложению.
     * 
     * Путь должен исключать `BASE_PATH`.
     * Например: '/config/.sample.php'.
     * 
     * @var string
     */
    protected string $filename = '';

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла конфигурации, включая путь к устанавливаемому приложению.
     */
    public function __construct(string $filename)
    {
        $this->filename = BASE_PATH . $filename;
    }

    /**
     * Проверяет, существует ли файл конфигурации приложения.
     * 
     * @see AppConfigFile::$filename
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Проверяет, существует ли файл шаблона конфигурации приложения.
     * 
     * @see AppConfigFile::$smplFilename
     * 
     * @return bool
     */
    public function sampleExists(): bool
    {
        return file_exists($this->getSampleFilename());
    }

    /**
     * Возвращает название файла шаблона конфигурации приложения.
     * 
     * @see AppConfigFile::$smplFilename
     * 
     * @return string
     */
    public function getSampleFilename(): string
    {
        if (!isset($this->smplFilename)) {
            $info = pathinfo($this->filename);
            $filename = $info['filename'] . '.sample.' . $info['extension'];
            $dirname = rtrim($info['dirname'], DS);
            if ($dirname !== '.' && $dirname !== '..') {
                $filename = $dirname . DS . $filename;
            }
            $this->smplFilename = $filename;
        }
        return $this->smplFilename;
    }

    /**
     * Возвращает название файла конфигурации приложения.
     * 
     * @see AppConfigFile::$filename
     * 
     * @return string
     */
    public function getFilename() :string
    {
        return $this->filename;
    }

    /**
     * Создаёт файл конфигурации приложения из шаблона.
     * 
     * @param array $replace Параметры подставляемые в шаблон в виде пар "ключ - значение".
     * 
     * @return bool Возвращает значение `false`, если файл невозможно создать.
     */
    public function create(array $replace = []): bool
    {
        if (!$this->sampleExists()) {
            return false;
        }

        $_replace = [];
        foreach ($replace as $key => $value) {
            $_replace['{' . $key . '}'] = $value;
        }

        $content = file_get_contents($this->getSampleFilename(), true);
        if ($content === false) {
            return false;
        }
        $content = str_replace(array_keys($_replace), array_values($_replace), $content);

        if (file_put_contents($this->filename, $content) === false)
            return false;
        else
            return true;
    }

    /**
     * Выполняет тестовую запись в файл с последующем его удалении.
     * 
     * @return bool
     */
    public function testWrite(): bool
    {
        $filename = str_replace('.sample', '.test', $this->getSampleFilename());

        if (file_put_contents($filename, 'test') === false) {
            return false;
        }
        return @unlink($filename);
    }
}
