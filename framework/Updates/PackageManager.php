<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Updates;

use Ge;
use Ge\Stdlib\Service;
use Ge\Filesystem\Filesystem;

/**
 * Менеджер пакетов обновлений.
 * 
 * PackageManager - это служба приложения, доступ к которой можно получить через `Ge::$app->updates`.
 * 
 * Файл пакета обновлений может имееть вид:
 *    - <application name>_<application version>.package-<package id>.gpk
 *    - <application name>_<application version>_<edition name>_<edition version>.package-<package id>.gpk
 * где: 
 *    - "application name" - имя приложения;
 *    - "application version" - версия приложения;
 *    - "edition name" - имя релиза (редакции);
 *    - "edition version" - версия релиза (редакции);
 *    - "package id" - идентификатор пакета обновлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Updates
 * @since 2.0
 */
class PackageManager extends Service
{
    /**
     * {@inheritdoc}
     */
    protected string $_name = 'updates';

    /**
     * {@inheritdoc}
     */
     protected bool $useUnifiedConfig = true;

    /**
     * Базовый (локальный) путь обновления.
     *
     * Указывается параметром "localPath" конфигурации сервиса "updates".
     * Пример: "/updates".
     * 
     * @var string
     */
    public string $localPath = '/updates';

    /**
     * Абсолютный путь загрузки.
     * 
     * Имеет вид: "<абсолютный общедоступный путь/> <базовый (локальный) путь загрузки/>".
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Имя таблицы с пакетами обновлений.
     * 
     * @var string
     */
    public string $tableName  = '{{updates}}';

    /**
     * Формат пакета обновлений (xml, json).
     * 
     * @var string
     */
    public string $packageFormat = 'xml';

    /**
     * Расширение файта пакета обновлений.
     * 
     * @var string
     */
    public string $packageExtension = '.gpk';

    /**
     * Суффикс в имени файла пакета обновлений.
     * 
     * @var string
     */
    protected string $suffixPackageName = 'package-';

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return @get_class($this);
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // абсолютный путь загрузки
        $this->path = Ge::$app->runtimePath . $this->localPath;
    }

    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        Ge::setAlias('@updates', $this->path);
    }

    /**
     * Возвращает идентификатор пакета обновлений из имени файла.
     * 
     * @param string $filename Имя файла пакета обновлений.
     * 
     * @return string|null Если значение `null`, то идентификатор не найден.
     */
    public function getPackageIdFromFilename(string $filename): ?string
    {
        $name = basename($filename, $this->packageExtension);
        $names = explode($this->suffixPackageName, $name);
        return $names[1] ?? null;
    }

    /**
     * Создаёт уникальный идентификатор пакета обновлений.
     * 
     * @return string
     */
    public function genPackageId(): string
    {
         return str_replace('.', '-', uniqid('', true));
    }

    /**
     * Возвращает имя файла пакета (архива) обновлений по указанным параметрам.
     * 
     * @param array $params Параметры создания файла пакета (архива) обновлений.
     * Имеет вид:
     * ```php
     *    [
     *        "id"      => "string", // идентификатор пакета
     *        "name"    => "string", // имя версии приложения
     *        "number"  => "string", // номер версии приложения
     *        "edition" => [ // версия редакции приложения
     *            "name"   => "string"
     *            "number" => "string"
     *        ]
     *    ]
     * ```
     * 
     * @return string
     */
    public function definePackageFileName(array $params = []): string
    {
        // идентификатор пакета
        $id = $params['id'] ?? $this->genPackageId();
        // имя приложения
        $name = $params['name'] ?? null;
        if ($name === null) return '';

        $name = strtolower($name);
        $name = str_replace(' ', '.', $name);
        // номер версии приложения
        $number = $params['number'] ?? null;
        if ($number) {
            $name .= '_' . $number;
        }
        // если указана версия редакции
        $edition = $params['edition'] ?? null;
        if ($edition) {
            // если указано имя версии редакции
            if (!empty($edition['name'])) {
                $name  .= '_' . str_replace(' ', '-', strtolower($edition['name']));
                // если указан номер версии редакции
                if (!empty($edition['number'])) {
                    $name .= '_' . $edition['number'];
                }
            }
        }
        return $name . '.' . $this->suffixPackageName . $id . $this->packageExtension;
    }

    /**
     * Возвращает пакет обновлений.
     * 
     * @param string $filename Имя файла пакета обновлений.
     * 
     * @return Package
     */
    public function getPackage(string $filename): Package
    {
        return new Package($filename, $this);
    }

    /**
     * Проверяет, загружен ли файл пакета обновлений на сервер.
     * 
     * @param string $filename Имя файла пакета обновлений.
     * 
     * @return bool
     */
    public function isUploadedPackage(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * Проверяет, добавлен ли пакет обновлений (в базу данных).
     * 
     * @param string $filename Имя файла пакета обновлений.
     * 
     * @return bool Если значение `true`, то запись в базе данных о пакете обновлений 
     *     существует.
     */
    public function packageExists(string $filename): bool
    {
        /** @var \Ge\Db\Sql\Select $select */
        $select = Ge::$app->db->select($this->tableName);
        $select
            ->columns(['*'])
            ->where(['package_id' => $this->getPackageIdFromFilename($filename)]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand($select);
        return $command->queryOne() !== null;
    }

    /**
     * Возвращает информацию пакета обновлений (из базы данных).
     * 
     * @param string|array $condition Условие запроса, в виде пары "ключ - значение".
     * 
     * @return array|null Если значение `null`, информация о пакете не найдена.
     */
    public function selectPackage(string|array $condition): ?array
    {
        /** @var \Ge\Db\Sql\Select $select */
        $select = Ge::$app->db->select($this->tableName);
        $select
            ->columns(['*'])
            ->where($condition);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand($select);
        return $command->queryOne();
    }

    /**
     * Возвращает количество пакетов обновлений (из базы данных) с указанным статусом.
     * 
     * @param null|string $status Статус пакета: "installed", "uploaded".
     *    Если null, все пакеты обновлений.
     * 
     * @return int Если значение `null`, информация о пакете не найдена.
     */
    public function getCountPackages(?string $status = null): int
    {
        /** @var \Ge\Db\Sql\Select $select */
        $select = Ge::$app->db->select($this->tableName);
        $select
            ->columns(['total' => new \Ge\Db\Sql\Expression('COUNT(*)')]);
        if ($status)
            $select->where(['status' => $status]);

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand($select);
        $row = $command->queryOne();
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Проверяет, существуе ли директория пакетов обновлений.
     * 
     * @return bool
     */
    public function pathExists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Создаёт директорию пакетов обновлений.
     * 
     * @return bool
     */
    public function makePath(): bool
    {
        return Filesystem::makeDirectory($this->path);
    }
}
