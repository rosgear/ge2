<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @see https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Config;

use Ge;

/**
 * Класс возвращает информацию из подключаемых файлов конфигурации расширений (модулей, плагинов).
 * 
 * Каждое значение параметра конфигурации будет отформатировано соответствующим методом.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Config
 * @since 2.0
 */
class ExtensionConfigInfo extends ConfigInfo
{
    /**
     * Полный путь к файлам конфигурации.
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Параметры конфигурации версии расширения по умолчанию.
     * 
     * @var array
     */
    protected array $versionConfigDefault = [
        'names'       => [], // имя и описание
        'version'     => '', // номер версии
        'versionDate' => '', // дата версии
        'author'      => '', // имя автора
        'authorUrl'   => '', // URL-адрес страницы автора
        'url'         => ''  // URL-адрес расширения
    ];

    /**
     * Имя файла конфигурации версии расширения (без указания пути).
     * 
     * @var array
     */
    protected string $versionFilename = '.version.php';

    /**
     * Параметры конфигурации установки расширения по умолчанию.
     * 
     * @var array
     */
    protected array $installConfigDefault = [
        'type'      => '', // тип ('plugin')
        'names'     => [], // имя и описание
        'required'  => [], // требования к установке
    ];

    /**
     * Имя файла конфигурации установки (без указания пути).
     * 
     * @var array
     */
    protected string $installFilename = '.install.php';

    /**
     * Параметры настроек расширения по умолчанию.
     * 
     * @var array
     */
    protected array $settingsConfigDefault = [];

    /**
     * Имя файла настроек расширения (без указания пути).
     * 
     * @var array
     */
    protected string $settingsFilename = '.settings.php';

    /**
     * Возвращает параметры конфигурации версии расширения с указанием имени файла.
     * 
     * @param string $filename Имя файла конфигурации расширения (включает путь).
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getVersionConfig(string $filename, bool $format = true): array
    {
        $config = $this->loadFileInfo($filename, $this->versionConfigDefault, $format);
        if ($format) {
            // имя и описание
            $names = $this->formatName($config['names'], $config);
            $config['name']        = $names[0];
            $config['description'] = $names[1];
        }
        return $config;
    }

    /**
     * Возвращает параметры конфигурации версии расширения.
     * 
     * @see ExtensionConfigInfo::getVersionConfig()
     * 
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getVersion(bool $format = true): array
    {
        return $this->getVersionConfig($this->path . DS . $this->versionFilename, $format);
    }

    /**
     * Возвращает параметры конфигурации установки расширения с указанием имени файла.
     * 
     * @param string $filename Имя файла конфигурации расширения (включает путь).
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getInstalledConfig(string $filename, bool $format = true): array
    {
        $config = $this->loadFileInfo($filename, $this->installConfigDefault, $format);
        if ($format) {
            // имя и описание
            $names = $this->formatName($config['names'], $config);
            $config['name']        = $names[0];
            $config['description'] = $names[1];
        }
        return $config;
    }

    /**
     * Возвращает параметры конфигурации установки.
     * 
     * @see ExtensionConfigInfo::getInstalledConfig()
     * 
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getInstalled(bool $format = true): array
    {
        return $this->getInstalledConfig($this->path . DS . $this->installFilename, $format);
    }

    /**
     * Возвращает параметры конфигурации настроек расширения с указанием имени файла.
     * 
     * @param string $filename Имя файла конфигурации расширения (включает путь).
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getSettingsConfig(string $filename, bool $format = true): array
    {
        return $this->loadFileInfo($filename, $this->settingsConfigDefault, $format);
    }

    /**
     * Возвращает параметры конфигурации установки.
     * 
     * @see ExtensionConfigInfo::getSettingsConfig()
     * 
     * @param bool $format Если `true`, форматировать значения параметров конфигурации (по умолчанию `true`).
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getSettings(bool $format = true): array
    {
        return $this->getSettingsConfig($this->path . DS . $this->settingsFilename, $format);
    }

    /**
     * Возвращает параметры конфигурации версии и установки расширения.
     * 
     * @see ExtensionConfigInfo::getInstalled()
     * @see ExtensionConfigInfo::getVersion()
     * 
     * @throws \Ge\Config\Exception\FileNotFoundException
     * 
     * @return array
     */
    public function getInfo(): array
    {
        return [
            'installed' => $this->getInstalled(),
            'version'   => $this->getVersion()
        ];
    }

    /**
     * Форматирование имени и описания.
     * 
     * @param mixed $value Имя и описание в разных локализациях.
     * @param array $options Параметры конфигурации.
     * 
     * @return array Результат: `['имя', 'описание']`.
     */
    protected function formatName(mixed $value, array $options): array
    {
        $name = ''; // имя
        $desc = ''; // описание
        if (is_array($value)) {
            // если название имеет текущую локаль приложения
            if (isset($value[Ge::$app->language->tag])) {
                $value = $value[Ge::$app->language->tag];
                $name = $value[0] ?? '';
                $desc = $value[1] ?? '';
            }
        } else {
            $name = is_string($value) ? $value : '';
        }
        return [$name, $desc];
    }

    /**
     * Форматирование даты релиза версии.
     * 
     * @param string $value Дата.
     * @param array $options Параметры конфигурации.
     * 
     * @return string
     */
    protected function formatVersionDate(string $value, array $options): string
    {
        return $value ? Ge::$app->formatter->toDate($value) : '';
    }
}