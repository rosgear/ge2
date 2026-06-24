<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Updates\Formatter;

use SimpleXMLElement;
use Ge;
use Ge\Stdlib\BaseObject;
use Ge\Exception\XMLFormatException;
use Ge\Helper\Converter\ArrayToXml;
use Ge\Filesystem\Filesystem;

/**
 * Класс XML-форматировщика данных (контента) пакета обновлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Updates
 * @since 2.0
 */
class XmlFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function arrayToData(array $array): mixed
    {
        /** @var string $xml */
        $xml = ArrayToXml::convert($array, 'package');

        libxml_use_internal_errors(true);

        /** @var SimpleXMLElement|false $data */
        $data = simplexml_load_string($xml);

        $this->applyXmlErrors(libxml_get_errors());
        libxml_use_internal_errors(false);
        return $data;
    }

    /**
     * Добавляет ошибки XML форматирования.
     * 
     * @param array<int, string> $errors 
     * 
     * @return $this
     */
    protected function applyXmlErrors(array $errors): static
    {
        $exception = null;
        foreach ($errors as $error) {
            if ($exception === null)
                $exception = new XMLFormatException($error);
            else
                $exception->setError($error);
            $this->addError($exception->getDispatchMessage());
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load(): bool
    {
        if (!file_exists($this->filename)) {
            $this->setError(Ge::t('app', 'File does not exist at path "{0}"', [$this->filename]));
            return false;
        }

        libxml_use_internal_errors(true);

        /** @var SimpleXMLElement|false $xml */
        $xml = simplexml_load_file($this->filename);
        if ($xml === false)
            $this->applyXmlErrors(libxml_get_errors());
        else
            $this->data = $xml;

        libxml_use_internal_errors(false);
        return $xml !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): int|false
    {
        if ($this->data === null) {
            return false;
        }
        return Filesystem::put($this->filename, $this->data->asXML());
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        if ($this->data === null || $this->errors) return false;

    /** @var SimpleXMLElement $xml */
        $xml = $this->data;
        $elements = $this->getValidateElements();
        foreach ($elements as $element) {
            if (!BaseObject::hasPropertyRecursive($xml, $element, '/')) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', [$element]));
                return false;
            }
        }

        // если не указаны файлы установкии и SQL-запросы
        $countFiles   = isset($xml->install->files->file) ? sizeof($xml->install->files->file) : 0;
        $countQueries = isset($xml->install->queries->query) ? sizeof($xml->install->queries->query) : 0;
        if ($countFiles == 0 && $countQueries == 0) {
            $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['install']));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateInstallFiles(string $path): false|array
    {
        $rows = [];
        foreach ($this->data->install->files->file as $index => $file) {
            // тег "action"
            $action = isset($file->action) ? (string) $file->action : '';
            if (empty($action)) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['action']));
                return false;
            }
            // тег "source"
            $source      = isset($file->source) ? (string) $file->source : '';
            // тег "destination"
            $destination = isset($file->destination) ? (string) $file->destination : '';
            // если копирование файла
            if ($action == 'copy') {
                if (empty($source)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['source']));
                    return false;
                }
                if (empty($destination)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['destination']));
                    return false;
                }
                $sourceFilename = $path . DS . $source;
                $destFilename = Ge::getAlias($destination);
                $rows[] = [
                    'action'      => $action,
                    'install' => [
                        'source'      => $sourceFilename,
                        'destination' => $destFilename
                    ]
                ];
            } else
            // если замена файла
            if ($action == 'replace') {
                if (empty($source)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['source']));
                    return false;
                }
                if (empty($destination)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['destination']));
                    return false;
                }
                $sourceFilename = $path . DS .$source;
                $destFilename = Ge::getAlias($destination);
                $rows[] = [
                    'action'  => $action,
                    'install' => ['source' => $sourceFilename, 'destination' => $destFilename],
                    'backup'  => ['source' => $destFilename, 'destination' => $sourceFilename]
                ];
            } else
            // если удаление файла
            if ($action == 'delete') {
                if (empty($source)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['source']));
                    return false;
                }
                $sourceFilename = Ge::getAlias($source);
                $rows[] = [
                    'action'  => $action,
                    'install' => ['source' => $sourceFilename]
                ];
            } else {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['action']));
                return false;
            }
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function validateUninstallFiles(): false|array
    {
        $rows = [];
        foreach ($this->data->uninstall->files->file as $index => $file) {
            // тег "action"
            $action = isset($file->action) ? (string) $file->action : '';
            if (empty($action)) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['action']));
                return false;
            }
            // тег "source"
            $source = isset($file->source) ? (string) $file->source : '';
            // тег "destination"
            $destination = isset($file->destination) ? (string) $file->destination : '';
            // если удаление файла
            if ($action == 'delete') {
                if (empty($source)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['source']));
                    return false;
                }
                $sourceFilename = Ge::getAlias($source);
                $rows[] = [
                    'action'    => $action,
                    'uninstall' => ['source' => $sourceFilename]
                ];
            } else {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['action']));
                return false;
            }
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function validateInstallQueries(): array
    {
        $rows = [];
        foreach ($this->data->install->queries->query as $query) {
            $rows[] = $query;
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function validateUninstallQueries(): array
    {
        $rows = [];
        foreach ($this->data->uninstall->queries->query as $query) {
            $rows[] = $query;
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function validateCompatibleVersions(): false|array
    {
        $rows = [];
        foreach ($this->data->compatible->version as $version) {
            $row = [];
            // тег "application/name"
            $appName = isset($version->application->name) ? (string) $version->application->name : '';
            if (empty($appName)) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['application/name']));
                return false;
            }
            // тег "application/number"
            $appNumber = isset($version->application->number) ? (string) $version->application->number : '';
            if (empty($appNumber)) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['application/number']));
                return false;
            }
            $row['application'] = [
                'name'   => $appName,
                'number' => $appNumber
            ];
            // тег "edition/name"
            $editionName = isset($version->edition->name) ? (string) $version->edition->name : '';
            if ($editionName) {
                // тег "edition/number"
                $editionNumber = isset($version->edition->number) ? (string) $version->edition->number : '';
                if (empty($editionNumber)) {
                    $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['edition/number']));
                    return false;
                }
                $row['edition'] = [
                    'name'   => $editionName,
                    'number' => $editionNumber
                ];
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
     public function validateDependencies(): false|array
    {
        $rows = [];
        foreach ($this->data->dependencies->package as $package) {
            $package = (string) $package;
            if (empty($package)) {
                $this->addError(Ge::t('app', 'Invalid tag name or tag "{0}" does not exist', ['package']));
                return false;
            }
            $rows[] = $package;
        }
         return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCompatibleVersions(): bool
    {
        return isset($this->data->compatible->version) ? sizeof($this->data->compatible->version) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDependencies(): bool
    {
        return isset($this->data->dependencies->package) ? sizeof($this->data->dependencies->package) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInstallFiles(): bool
    {
        return isset($this->data->install->files->file) ? sizeof($this->data->install->files->file) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInstallQueries(): bool
    {
        return isset($this->data->install->queries->query) ? sizeof($this->data->install->queries->query) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUninstallFiles(): bool
    {
        return isset($this->data->uninstall->files->file) ? sizeof($this->data->uninstall->files->file) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUninstallQueries(): bool
    {
        return isset($this->data->uninstall->queries->query) ? sizeof($this->data->uninstall->queries->query) > 0 : false;
    }

    /**
     * {@inheritdoc}
     */
    public function appendData(object $model): bool
    {
        if ($this->data === null || $this->errors) return false;
        $xml = $this->data;

        // идентификатор пакета
        $model->packageId = (string) $xml->id;
        // дата выпуска пакета
        $model->date = (string) $xml->date;
        // название
        $model->name = (string) $xml->name;
        // описание
        $model->notes = (string) $xml->notes;
        // категория
        $model->category = (string) $xml->category;
        // назначение
        $model->purpose = (string) $xml->purpose;
        // важность
        $model->importance = (string) $xml->importance;
        // авторcкое право
        $model->copyright = (string) $xml->copyright;
        // лицензия
        $model->license = (string) $xml->license;
        // имя приложения
        $model->appName = (string) $xml->version->application->name;
        // номер версии приложения
        $model->appNumber = (string) $xml->version->application->number;
        // имя редакции приложения
        if (isset($xml->version->edition->name)) {
            $model->editionName = (string) $xml->version->edition->name;
            // номер версии редакции приложения
            if (isset($xml->version->edition->number)) {
                $model->editionNumber = (string) $xml->version->edition->number;
            }
        }
        // зависимость от других пакетов
        if (isset($xml->dependencies->package)) {
            $dependencies = [];
            foreach ($xml->dependencies->package as $index => $id) {
                $dependencies[] = trim($id);
            }
            $model->dependencies = implode('<br>', $dependencies);
        }
        // совместимость с версиями
        if (isset($xml->compatible->version)) {
            $compatible = [];
            foreach ($xml->compatible->version as $index => $version) {
                $str = '';
                $str .= $version->application->name;
                if (isset($version->application->number)) {
                    $str .= ' ' . $version->application->number;
                }
                if (isset($version->edition->name)) {
                    $str .= ' ' . $version->edition->name;
                    if (isset($version->edition->number)) {
                        $str .= ' ' . $version->edition->number;
                    }
                }
                $compatible[] = $str;
            }
            $model->compatible = implode('<br>', $compatible);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function dataToColumns(): false|array
    {
        if ($this->data === null || $this->errors) return false;
        $xml = $this->data;

        $columns = [];
        // идентификатор пакета
        $columns['package_id'] = (string) $xml->id;
        // дата выпуска пакета
        $columns['date'] = trim($xml->date);
        $columns['date'] = date('Y-m-d', strtotime($columns['date']));
        // название
        $columns['name'] = (string) $xml->name;
        // описание
        $columns['notes'] = (string) $xml->notes;
        // категория
        $columns['category'] = (string) $xml->category;
        // назначение
        $columns['purpose'] = (string) $xml->purpose;
        // важность
        $columns['importance'] = (string) $xml->importance;
        // авторcкое право
        $columns['copyright'] = (string) $xml->copyright;
        // лицензия
        $columns['license'] = (string) $xml->license;
        // имя приложения
        $columns['app_name'] = (string) $xml->version->application->name;
        // номер версии приложения
        $columns['app_number'] = (string) $xml->version->application->number;
        // имя редакции приложения
        if (isset($xml->version->edition->name)) {
            $columns['edition_name'] = (string) $xml->version->edition->name;
            // номер версии редакции приложения
            if (isset($xml->version->edition->number)) {
                $columns['edition_number'] = (string) $xml->version->edition->number;
            }
        }
        // зависимость от других пакетов
        if (isset($xml->dependencies->package)) {
            $dependencies = [];
            foreach ($xml->dependencies->package as $index => $id) {
                $dependencies[] = trim($id);
            }
            $columns['dependencies'] = implode(', ', $dependencies);
        }
        // совместимость с версиями
        if (isset($xml->compatible->version)) {
            $compatible = [];
            foreach ($xml->compatible->version as $index => $version) {
                $str = '';
                $str .= $version->application->name;
                if (isset($version->application->number)) {
                    $str .= ' ' . $version->application->number;
                }
                if (isset($version->edition->name)) {
                    $str .= ' ' . $version->edition->name;
                    if (isset($version->edition->number)) {
                        $str .= ' ' . $version->edition->number;
                    }
                }
                $compatible[] = $str;
            }
            $columns['compatible'] = implode(', ', $compatible);
        }
        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->data ? $this->data->asXML() : '';
    }
}
