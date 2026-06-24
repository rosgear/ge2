<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Updates;

use ZipArchive;
use Ge;
use Ge\Data\DataManager;
use Ge\Filesystem\Filesystem;
use Ge\Updates\Formatter\AbstractFormatter;

/**
 * Пакет обновлений.
 * 
 * Пакет обновлений предназначен для обновления расширений, плагинов, модулей приложения и файлов фреймворка.
 * Все файлы пакета обновлений находятся в архиве с расширением ".gpk".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Updates
 * @since 2.0
 */
class Package
{
    /**
     * Менеджер пакетов обновлений.
     * 
     * @var PackageManager
     */
    protected PackageManager $manager;

    /**
     * Имя файла (архива) пакета обновлений (без директории).
     * Только для чтения.
     * 
     * @var string
     */
    public string $filename;

    /**
     * Идентификатор пакета обновлений.
     * 
     * @var string
     */
    public string $id;

    /**
     * Директория пакета обновлений.
     * 
     * @var string
     */
    public string $path;

    /**
     * Директория резервной копии пакета обновлений.
     * 
     * @var string
     */
    public string $backupPath;

    /**
     * Формат информации пакета обновлений (xml, json,...).
     * 
     * @var string
     */
    public string $format = 'xml';

    /**
     * Форматировщик информации пакета обновлений.
     * 
     * @var AbstractFormatter
     */
    protected AbstractFormatter $formatter;

    /**
     * Классы форматировщика информации пакета обновлений.
     * 
     * @var array<string, string>
     */
    protected array $invokableFormatters = [
        'xml'  => 'Ge\Updates\Formatter\XmlFormatter',
        'json' => 'Ge\Updates\Formatter\JsonFormatter'
    ];

    /**
     * Ошибки полученные при разборе информации пакета обновлений.
     * 
     * @var array
     */
    public array $errors = [];

    /**
     * Конструктор класса.
     * 
     * @param string $filename Имя файла (с расширением '.gpk') пакета обновлений (с указанием пути).
     * @param PackageManager $manager Менеджер пакетов обновлений.
     * 
     * @return void
     * 
     * @throws Exception\FormatException Неверный формат идентификатора пакета обновления.
     */
    public function __construct(string $filename, PackageManager $manager)
    {
        $this->manager  = $manager;
        $this->format   = $manager->packageFormat;
        $this->filename = $filename;
        $this->id       = $manager->getPackageIdFromFilename($filename);
        if ($this->id === null) {
            throw new Exception\FormatException(Ge::t('app', 'Invalid update package identifier format'));
        }
        $this->path       = $manager->path . DS . $this->id;
        $this->backupPath = $this->path . DS . 'backup';
    }

    /**
     * Создаёт форматировщик информации пакета обновлений.
     * 
     * @param string $format Формат данных пакета обновлений (xml, json,...).
     * 
     * @return AbstractFormatter
     * 
     * @throws Exception\FormatException Указанный формат данных пакета обновления не существует.
     */
    public function createFormatter(string $format): AbstractFormatter
    {
        $className = $this->invokableFormatters[$format] ?? null;
        if (!$className) {
            throw new Exception\FormatException(Ge::t('app', 'The specified update package data format "{0}" does not exist', [$format]));
        }
        return new $className($this->path . '/package.' . $this->format);
    }

    /**
     * Возвращает форматировщик информации пакета обновлений.
     * 
     * Если он не создан, создаёт его {@see Package::createFormatter()}.
     * 
     * @return \Ge\Updates\Formatter\AbstractFormatter
     * 
     * @throws Exception\FormatException Указанный формат данных пакета обновления не существует.
     */
    public function getFormatter(): AbstractFormatter
    {
        if (!isset($this->formatter)) {
            $this->formatter = $this->createFormatter($this->format);
        }
        return $this->formatter;
    }

    /**
     * Добавление информации пакета обновлений в базу данных.
     * 
     * @param null|array $columns Информации о пакете обновлений. 
     * 
     * @return int Идентификатор записи пакета обновлений в базе данных.
     */
    public function add(?array $columns = null): int
    {
        if ($columns === null) {
            $columns = $this->getFormatter()->dataToColumns();
        }

        // если не указан файл пакета
        if (!isset($columns['filename'])) {
            $columns['filename'] = pathinfo($this->filename, PATHINFO_BASENAME);
        }

        // если не указан статус пакета
        if (!isset($columns['status'])) {
            // т.к. паент загружен для добавления, то:
            $columns['status'] = 'uploaded';
        }

        // поля аудиа записи
        $columns[DataManager::AR_CREATED_DATE] = gmdate('Y-m-d H:i:s');
        $columns[DataManager::AR_CREATED_USER] = Ge::$app->user->getId();
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        $command->insert($this->manager->tableName, $columns)->execute();
        return (int) Ge::$app->db->getConnection()->getLastGeneratedValue();
    }

    /**
     * Обновление информации пакета обновлений в базе данных.
     * 
     * @param array $columns Имена полей с их значениями.
     * @param null|string $packageId Идентификатор пакета обновлений. Если `null`,
     *    идентификатор соответсвует {@see Package::$id}.
     * 
     * @return bool Возвращает значение `false`, если информация не обновлена.
     */
    public function update(array $columns, ?string $packageId = null): bool
    {
        if ($packageId === null) {
            $packageId = $this->id;
        }
        if (empty($packageId)) return false;

        // поля аудиа записи
        $columns[DataManager::AR_UPDATED_DATE] = gmdate('Y-m-d H:i:s');
        $columns[DataManager::AR_UPDATED_USER] = Ge::$app->user->getId();

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        $command->update(
            $this->manager->tableName,
            $columns,
            ['package_id' => $packageId]
        );
        $command->execute();
        return $command->getResult() === true ? true : false;
    }

    /**
     * Возвращает информацию пакета обновлений из базы данных.
     * 
     * @param null|string $packageId Идентификатор пакета обновлений. Если `null`,
     *    идентификатор соответсвует {@see Package::$id}.
     * 
     * @return array|null Возвращает значение `null`, если информация о пакете не 
     *     найдена.
     */
    public function select(?string $packageId = null): ?array
    {
        if ($packageId === null) {
            $packageId = $this->id;
        }
        if (empty($packageId)) return null;

        $select = new \Ge\Db\Sql\Select($this->manager->tableName);
        $select->columns(['*']);
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand($select);
        return $command->queryOne();
    }

    /**
     * Удаление пакета обновлений и его информации.
     * 
     * @param null|string $packageId Идентификатор пакета обновлений. Если `null`,
     *    идентификатор соответсвует {@see Package::$id}.

     * @return int|false Возвращает значение `false` или '0', то пакета обновлений 
     *     не удалён.
     */
    public function remove(?string $packageId = null): int|false
    {
        if ($packageId === null) {
            $packageId = $this->id;
        }
        if (empty($packageId)) return false;

        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        $command->delete($this->manager->tableName, ['package_id' => $packageId]);
        return $command->getResult() === true ? $command->getAffectedRows() : false;
    }

    /**
     * Проверяет, установлен ли пакет обновлений (имеет статус "installed").
     * 
     * @param null|string $packageId Идентификатор пакет обновлений.
     * 
     * @return bool Возвращает значение `true`, если пакет установлен.
     */
    public function isInstalled(?string $packageId = null): bool
    {
        if ($packageId === null) {
            $packageId = $this->id;
        }
        if (empty($packageId)) return false;

        /** @var array|null $package */
        $package = $this->select($packageId);
        return ($package['status'] ?? '') === 'installed';
    }

    /**
     * Устанавливает статус пакету обновлений (в базе данных).
     * 
     * @param string $status Статус пакета обновлений: 'uploaded', 'installed'.
     * 
     * @return bool Возвращает значение `true`, если статус установлен.
     */
    public function setStatus(string $status): bool
    {
        return $this->update(['status' => $status]);
    }

    /**
     * Выполняет установку пакета обновлений.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка в процессе 
     *     установки пакета обновлений.
     */
    public function install(): bool
    {
        // получить информацию о пакете
        $formatter = $this->getFormatter();
        if (!$formatter->hasData()) {
            if ($formatter->load()) {
                $formatter->validate();
            }
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }

        // если указаны SQL-запросы
        $hasQueries = $formatter->hasInstallQueries();
        if ($hasQueries) {
            $queries = $formatter->validateInstallQueries();
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }

        // если указаны файлы
        $hasFiles = $formatter->hasInstallFiles();
        if ($hasFiles) {
            $files = $formatter->validateInstallFiles($this->path . DS);
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }
        if (!($hasFiles || $hasQueries)) return false;

        // если необходимо проверить совместимость версий
        if ($formatter->hasCompatibleVersions()) {
            $versions = $formatter->validateCompatibleVersions();
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->installVersions($versions)) {
                $this->addError(Ge::t('app', 'Your app version is not compatible with the update packages version'));
                return false;
            }
        }

        // если необходимо проверить зависимость пакетов обнволений
        if ($formatter->hasDependencies()) {
            $dependencies= $formatter->validateDependencies();
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->installDependencies($dependencies)) return false;
        }

        if ($hasQueries) {
            if (!$this->installQueries($queries)) return false;
        }

        if ($hasFiles) {
            if (!$this->installFiles($files)) return false;
        }
        // установка статуса пакету
        $this->setStatus('installed');
        return true;
    }

    /**
     * Установка файлов пакета обновлений.
     * 
     * @param array $files Действия над (исходными) файлами пакета и файлами (назначения) приложения.
     * Имеет вид:
     * ```php
     *    [
     *        [
     *            "action"      => "type" // тип действия: "copy", "replace", "delete"
     *             "install" => [
     *                "source"      => "filename" // исходный файл
     *                "destination" => "filename" // файл назначения
     *             ]
     *        ],
     *        // ...
     *    ]
     * ```
     * 
     * @return bool Возвращает значение `false`, если ошибка при выполнении действия 
     *     над файлами.
     */
    public function installFiles(array $files): bool
    {
        Filesystem::$throwException = true;
        foreach ($files as $file) {
            $action      = $file['action'];
            $source      = $file['install']['source'] ?? '';
            $destination = $file['install']['destination'] ?? '';
            // если копирование файлов
            if ($action === 'copy') {
                // если нет файла пакета
                if (!file_exists($source)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$source]));
                    return false;
                }
                // если нет директории получателя
                $path = pathinfo($destination, PATHINFO_DIRNAME);
                if (!file_exists($path)) {
                     Filesystem::makeDirectory($path, 0755, true);
                }
                Filesystem::copy($source, $destination);
            } else
            // если замена файлов
            if ($action === 'replace') {
                // если нет файла пакета
                if (!file_exists($source)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$source]));
                    return false;
                }
                // если нет получателя
                if (!file_exists($destination)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$destination]));
                    return false;
                }
                Filesystem::copy($source, $destination);
            } else
            // если удаление файлов
            if ($action === 'delete') {
                // если нет удаляемого файла
                if (!file_exists($source)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$source]));
                    return false;
                }
                Filesystem::delete($source);
            }
        }
        return true;
    }

    /**
     * Выполняет SQL-запросы к базе данных предназначенных для установки пакета 
     * обновлений.
     * 
     * @param array $queries SQL-запросы к базе данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка в процессе 
     *     выполнения SQL-запроса.
     */
    public function installQueries(array $queries): bool
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        foreach ($queries as $query) {
            $command->setSql(trim($query));
            $command->execute();
        }
        return true;
    }

    /**
     * Проверяет совместимость версий с текущей версией приложения или версией редакции 
     * приложения.
     * 
     * @param array $versions Версии совместимости.
     * Имеет вид:
     * ```php
     *    [
     *        [
     *            "application" => [ // версия приложения
     *                "name"   => "name",
     *                "number" => "number"
     *             ],
     *             "edition" => [ // версия редакции
     *                "name"   => "name",
     *                "number" => "number"
     *             ]
     *        ],
     *        // ...
     *    ]
     * ```
     * 
     * @return bool|array Возвращает значение `false`, если ошибка при проверки версий 
     *     совместимости.
     */
    public function installVersions(array $versions): bool
    {
        $isCompatible = false;
        $gversion = Ge::$app->version;
        $gedition = Ge::$app->version->getEdition();
        foreach ($versions as $version) {
            // если имена приложений совпадают
            if ($version['application']['name'] === $gversion->name) {
                // если устанавливаемая версия приложения старше или равна текущей версии (==0 или ==1)
                if ($gversion->compareNumber($version['application']['number']) !== -1) {
                    if (isset($version['edition'])) {
                        // если текущая версия имеет редакцию
                        if ($gedition) {
                            // если имена редакций совпадают
                            if ($version['edition']['name'] === $gedition->name) {
                                // если устанавливаемая версия редакции старше или равна текущей версии (==0 или ==1)
                                if ($gedition->compareNumber($version['edition']['number']) !== -1) {
                                    $isCompatible = true;
                                }
                            }
                        }
                    } else
                        $isCompatible = true;
                }
            }
        }
        return $isCompatible;
    }

    /**
     * Проверяет зависемость пакета обновлений от других пакетов.
     * 
     * @param array $dependencies Идентификаторы зависимых пакетов обновлений.
     * 
     * @return bool Возвращает значение `false`, если зависимые пакеты обновлений не установлены.
     */
    public function installDependencies(array $dependencies): bool
    {
        if ($dependencies) {
            $select = new \Ge\Db\Sql\Select($this->manager->tableName);
            $select->columns(['package_id']);
            $select->where(['package_id' => $dependencies, 'status' => 'installed']);
            /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
            $command = Ge::$app->db->createCommand($select);
            $packages = $command->queryAll('package_id');
            foreach ($dependencies as $package) {
                if (!isset($packages[$package])) {
                    $this->addError(Ge::t('app', 'Update package with ID "{0}" is missing', [$package]));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Выполняет демонтаж пакета обновлений.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка в процессе демонтажа 
     *    пакета обновлений или нет информации для его демонтажа.
     */
    public function uninstall(): bool
    {
        // получить информацию о пакете
        $formatter = $this->getFormatter();
        if (!$formatter->hasData()) {
            if ($formatter->load()) {
                $formatter->validate();
            }
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }

        // если указаны SQL-запросы
        $hasQueries = $formatter->hasUninstallQueries();
        if ($hasQueries) {
            $queries = $formatter->validateUninstallQueries();
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->uninstallQueries($queries)) return false;
        }

        // если указаны файлы
        $hasFiles = $formatter->hasUninstallFiles();
        if ($hasFiles) {
            $files = $formatter->validateUninstallFiles();
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->uninstallFiles($files)) return false;
        }

        // установка статуса пакету
        $this->setStatus('uploaded');
        return true;
    }

    /**
     * Демонтаж файлов пакета обновлений.
     * 
     * @param array $files Действия над (исходными) файлами пакета и файлами (назначения) приложения.
     *    Имеет вид:
     *    [
     *        [
     *            "action"      => "type" // тип действия: "delete"
     *             "install" => [
     *                "source"      => "filename" // исходный файл
     *                "destination" => "filename" // файл назначения
     *             ]
     *        ],...
     *    ]
     * 
     * @return bool Возвращает значение `false`, если ошибка при выполнении действия 
     *     над файлами.
     */
    public function uninstallFiles(array $files): bool
    {
        Filesystem::$throwException = true;
        foreach ($files as $file) {
            $action  = $file['action'];
            $install = $file['install'] ?? null;
            if ($install) {
                $source      = $install['source'];
                $destination = $install['destination'];
                // если удаление файлов
                if ($action === 'delete') {
                    // если есть что удалять
                    if (file_exists($source)) {
                        Filesystem::delete($source);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Выполняет SQL-запросы к базе данных предназначенных для демонтажа пакета обновлений.
     * 
     * @param array $queries SQL-запросы к базе данных.
     * 
     * @return bool Возвращает значение `false`, если возникла ошибка в процессе 
     *     выполнения SQL-запроса.
     */
    public function uninstallQueries(array $queries): bool
    {
        /** @var \Ge\Db\Adapter\Driver\AbstractCommand $command */
        $command = Ge::$app->db->createCommand();
        foreach ($queries as $query) {
            $command->setSql(trim($query));
            $command->execute();
        }
        return true;
    }

    /**
     * Выполняет резервное копирование обновляемых файлов пакета обновлений.
     * 
     * @return bool Возвращает значение `true, если обновляемые файлы есть и были 
     *     сделаны их резервные копии.
     */
     public function backup(): bool
    {
        if (!file_exists($this->backupPath)) {
             Filesystem::makeDirectory($this->backupPath);
        }

        // получить информацию о пакете обновлений
        $formatter = $this->getFormatter();
        if (!$formatter->hasData()) {
            if ($formatter->load()) {
                $formatter->validate();
            }
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }

        // если указаны файлы
        $hasFiles = $formatter->hasInstallFiles();
        if ($hasFiles) {
            $files = $formatter->validateInstallFiles($this->backupPath);
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->backupFiles($files)) return false;
        }
        return $hasFiles;
    }

    /**
     * Резервное копирование файлов пакета обновлений.
     * 
     * @param array $files Действия над (исходными) файлами пакета и файлами (назначения) приложения.
     * Имеет вид:
     * ```php
     *    [
     *        [
     *             "backup" => [
     *                "source"      => "filename" // исходный файл
     *                "destination" => "filename" // файл назначения
     *             ]
     *        ],
     *        // ...
     *    ]
     * ```
     * 
     * @return bool Возвращает значение `false`, если ошибка при выполнении действия 
     *     над файлами.
     */
    protected function backupFiles(array $files): bool
    {
        Filesystem::$throwException = true;
        foreach ($files as $file) {
            $backup = $file['backup'] ?? null;
            if ($backup) {
                $source      = $backup['source'];
                $destination = $backup['destination'];
                // если нет копируемого файла
                if (!file_exists($source)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$source]));
                    return false;
                }
                Filesystem::copy($source, $destination);
            }
        }
        return true;
    }

    /**
     * Проверяет, существует ли директория резервной копии файлов пакета обнавлений.
     * 
     * @return bool Возвращает значение `true`, если директория существует.
     */
    public function backupExists(): bool
    {
        return file_exists($this->backupPath);
    }

    /**
     * Выполняет восстановление резервной копии файлов (если она есть) пакета обновлений.
     * 
     * @return bool Возвращает значение `false`, если ошибка при выполнении восстановления 
     *     резервной копии файлов или резервная копия файлов отсутствует.
     */
    public function recovery(): bool
    {
        // получить информацию о пакете обновлений
        $formatter = $this->getFormatter();
        if (!$formatter->hasData()) {
            if ($formatter->load()) {
                $formatter->validate();
            }
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
        }

        // если указаны файлы
        $hasFiles = $formatter->hasInstallFiles();
        if ($hasFiles) {
            $files = $formatter->validateInstallFiles($this->backupPath);
            if ($formatter->hasErrors()) {
                $formatter->flashErrors($this);
                return false;
            }
            if (!$this->recoveryFiles($files)) return false;
        }

        // установка статуса пакету
        $this->setStatus('installed');
        return true;
    }

    /**
     * Восстанавливает файлы из резервной копии пакета обновлений.
     * 
     * @param array $files Действия над (исходными) файлами пакета и файлами (назначения) 
     * приложения. Имеет вид:
     * ```php
     * [
     *        [
     *             "backup" => [
     *                "source"      => "filename" // исходный файл
     *                "destination" => "filename" // файл назначения
     *             ]
     *        ],
     *        //...
     * ]
     * ```
     * 
     * @return bool Возвращает значение `false`, если ошибка при выполнении действия 
     *     над файлами.
     */
    public function recoveryFiles(array $files): bool
    {
        Filesystem::$throwException = true;
        foreach ($files as $file) {
            $backup = $file['backup'] ?? null;
            if ($backup) {
                $source      = $backup['source'];
                $destination = $backup['destination'];
                // если нет резервной копии файла
                if (!file_exists($destination)) {
                    $this->addError(Ge::t('app', 'File does not exist at path "{0}"', [$source]));
                    return false;
                }
                Filesystem::copy($destination, $source);
            }
        }
        return true;
    }

    /**
     * Проверяет, добавлена ли информация о пакете обновлений в базу данных.
     * 
     * @param null|string $packageId Идентификатор пакета обновлений.
     * 
     * @return bool Возвращает значение `true`, если информация добавлена.
     */
    public function exists(?string $packageId = null)
    {
        if ($packageId === null) {
            $packageId = $this->id;
        }

        $package = $this->select($packageId);
        return !empty($package['package_id']);
    }

    /**
     * Проверяет, существует ли файл (информация) пакета обнавлений в директории пакета.
     * 
     * @return bool Возвращает значение `true`, если файл существует.
     */
    public function fileExists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Проверяет, существует ли директория пакета обнавлений.
     * 
     * Директория соответствует идентификатору пакета.
     * 
     * @return bool Возвращает значение `true`, если директория существует.
     */
    public function pathExists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Создаёт директорию пакета обнавлений.
     * 
     * Директория соответствует идентификатору пакета.
     * 
     * @param null|string $packageId Идентификатор пакета обновлений.
     * 
     * @return void
     */
    public function makePath(?string $packageId = null): void
    {
        if ($packageId === null)
            $path = $this->path;
        else
            $path = $this->manager->path . DS . $packageId;

        // если директория пакетов обновлений не существует
        if (!$this->manager->pathExists()) {
            $this->manager->makePath();
        }

        // если директория пакета не существует
        if (!file_exists($path)) {
             Filesystem::makeDirectory($path);
        }
    }

    /**
     * Распаковывает файлы пакета обновлений в его директорию.
     * 
     * Директория пакета {@see \Ge\Updates\Package::$path}.
     * Файлы пакета (архив) {@see \Ge\Updates\Package::$filename}.
     * 
     * @return bool Если значение `false`, то ошибка:
     *    - не можеть открыть архив;
     *    - не может извлечь файлы архива или его части в указанное место назначения.
     */
    public function unpack(): bool
    {
        $archive = new ZipArchive;
        if ($archive->open($this->filename) !== true) return false;
        if ($archive->extractTo($this->path) === false) return false;
        return $archive->close();
    }

    /**
     * Архивирует файлы из директории пакета обновлений.
     * Если архив существует, он будет заменен.
     * 
     * Директория пакета {@see \Ge\Updates\Package::$path}.
     * Файлы пакета (архив) {@see \Ge\Updates\Package::$filename}.
     * 
     * @return bool Если значение `false`, то ошибка:
     *    - не можеть создать архив;
     *    - не может сохранить изменения в созданном архиве;
     *    - нет файлов для архивирования.
     */
    public function pack(): bool
    {
        $archive = new ZipArchive;
        if ($archive->open($this->filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;

        $finder = Filesystem::finder();
        $finder->files()->in($this->path);
        foreach ($finder as $info) {
            $archive->addFile($this->path . DS . $info->getRelativePathname(), $info->getFilename());
        }
        // если не добавились файлы
        if ($archive->count() == 0) return false;
        return $archive->close();
    }

    /**
     * Создаёт пакет обнавлений.
     * 
     * @param array $package Информация о пакете обновлений, 
     * где информация имеет вид:
     * ```php
     * [
     *     [
     *         "install"      => [
     *            "files"   => ["file"  => [...]]
     *            "queries" => ["query" => [...]]
     *         ],
     *         "dependencies" => ["package" => [...]],
     *         "compatible"   => ["version" => [...]],
     *     ]
     * ]
     * ```
     * 
     * @return bool Возвращает значение `false`, если отсутствуют файлы пакета обновлений 
     *     или основные параметры.
     */
    public function create(array $package): bool
    {
        // если указаны файлы
        $hasFiles = !empty($package['install']['files']['file']);
        // если указаны SQL-запросы
        $hasQueries = !empty($package['install']['queries']['query']);
        if (!($hasFiles || $hasQueries)) return false;

        // удалить содержимое директории пакета (если есть)
        Filesystem::$throwException = true;
        Filesystem::deleteDirectory($this->path);
        // создать директорию пакета (если нет)
        $this->makePath();
        // если указаны файлы
        if ($hasFiles) {
            $items  = &$package['install']['files']['file'];
            $files  = [];
            $backup = [];
            $copies = [];
            $index  = 1;
            foreach ($items as $item) {
                $action = $item['action'] ?? null;
                // если действие не предназначено для копии файлов
                if ($action === 'delete') {
                    $files[] = $item;
                    continue;
                }
                // если нет параметра "source"
                if (empty($item['source'])) {
                    $this->addError(Ge::t('app', 'Update pckage option "{0}" not specified or specified incorrectly', ['source']));
                    return false;
                }
                $sourcePath = $item['source'];
                $source     = Ge::getAlias($sourcePath);
                // если указанное имя это директория
                if (Filesystem::isDirectory($source)) {
                    $finder = Filesystem::finder();
                    $finder->files()->in($source);
                    foreach ($finder as $info) {
                        $filename = $this->id . '-' . ($index++) . '.dat';
                        $copies[] = [
                            $source . DS . $info->getRelativePathname(),
                            $this->path . DS . $filename
                        ];
                        $files[] = [
                            'action'      => $action,
                            'source'      => $filename,
                            'destination' => str_replace('\\', '/', $sourcePath . DS . $info->getRelativePathname())
                        ];
                    } // end foreach
                } 
                // если указанное имя это файл
                else {
                    $filename = $this->id . '-' . ($index++) . '.dat';
                    $copies[] = [
                        $source,
                        $this->path . DS . $filename
                    ];
                    $files[] = [
                        'action'      => $action,
                        'source'      => $filename,
                        'destination' => str_replace('\\', '/', $sourcePath)
                    ];
                }
            } // end foreach

            // файлы которые буде иметь пакет
            if ($copies) {
                foreach ($copies as $copy) {
                    Filesystem::copy($copy[0], $copy[1]);
                }
            }
            $items = $files;
        } // end if
        // добавление информации в пакет обновлений
        $formatter = $this->getFormatter();
        $data = $formatter->arrayToData($package);
        if ($formatter->hasErrors()) {
            $formatter->flashErrors($this);
            return false;
        }
        $formatter->setData($data);
        // сохранение информации о пакете обновлений
        if ($formatter->save() === false) {
            $this->addError(Ge::t('app', 'Unable to create update package file "{0}"', [$formatter->filename]));
            return false;
        }
        // архивирование файлов пакета обновлений
        if ($this->pack() === false) {
            $this->addError(Ge::t('app', 'Unable to package update package files "{0}"', [$formatter->filename]));
            return false;
        }
        return true;
    }

    /**
     * Проверяет наличие ошибок, полученных при разборе информации пакета обновлений.
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return sizeof($this->errors) > 0;
    }

    /**
     * Добавляет оишбку.
     * 
     * @param mixed $error Ошибка.
     * 
     * @return $this
     */
    public function addError(mixed $error): static
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Устанавливает ошибку первой в очередь ошибок.
     * 
     * @param mixed $error Ошибка.
     * 
     * @return $this
     */
    public function setError(mixed $error): static
    {
        if (is_array($error))
            $this->errors = $error;
        else
            $this->errors[0] = $error;
        return $this;
    }

    /**
     * Возвращает первую ошибку из очереди ошибок.
     * 
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->getErrors(0);
    }

    /**
     * Возвращает ошибку(и) по указанному индексу очереди ошибок.
     * 
     * @param null|int $index Порядковы номер ошибки. Если значение `null`, то возвратит 
     *     все ошибки.
     * 
     * @return mixed
     */
    public function getErrors(?int $index = null): mixed
    {
        return $index === null ? $this->errors : ($this->errors[$index] ?? '');
    }
}
