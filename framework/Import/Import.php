<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Import;

use Ge;
use Ge\Stdlib\BaseObject;
use Ge\Db\ActiveRecord;
use Ge\Db\Adapter\Exception\CommandException;
use Ge\Import\Parser\AbstractParser;
use Ge\Filesystem\Filesystem as Fs;

/**
 * Класс импорта данных компонентов (модулей, расширений модулей).
 * 
 * Импорт данных выполняется из пакетного файла (package.xml), который содержит ссылки 
 * на импортируемые файлы, а так же из самих файлов с расширениями: .xml, .json.
 * 
 * Для каждого импортируемого файла применяется свой тип парсера {@see Import::$parserTypes}.
 * 
 * Класс должен быть обязательно наследован с указанием маски атрибутов {@see Import::maskedAttributes()}.
 * Маска атрибутов - это псевдонимы (атрибуты, название тегов, свойства), которые будут 
 * получены при разборе импортируемого файла и используемые при импорте данных в качестве полей 
 * таблицы с их значениями.
 * 
 * Пример:
 * ```php
 * $import = new \Ge\Import\Import();
 * $import->runPackage('/folder/package.xml');
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Import
 * @since 1.0
 */
class Import extends BaseObject
{
    /**
     * @var string Событие перед импортом данных.
     * 
     * @see Import::beforeImport()
     */
    public const EVENT_BEFORE_IMPORT = 'beforeImport';

    /**
     * @var string Событие после импорта данных.
     * 
     * @see Import::afterImport()
     */
    public const EVENT_AFTER_IMPORT = 'afterImport';

    /**
     * @var string Событие до начала выполнения импорта данных.
     * 
     * @see Import::beforeRun()
     */
    public const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @var string Событие после выполнения импорта данных.
     * 
     * @see Import::afterRun()
     */
    public const EVENT_AFTER_RUN = 'afterRun';

    /**
     * Информация о пакете импортируемых файлов.
     * 
     * @see Import::runPackage()
     * 
     * @var array
     */
    protected array $package = [];

    /**
     * Полный путь импортируемого файла или путь файла пакета, использованного при 
     * последнем выполненном импорте данных.
     * 
     * @see Import::runPackage()
     * @see Import::run()
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Полный путь и имя импортируемого файла или файла пакета, использованного при 
     * последнем выполненном импорте данных.
     * 
     * @see Import::runPackage()
     * @see Import::run()
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * Расширение импортируемого файла на основе значения атрибута.
     * 
     * @see Import::importFileAttribute()
     * 
     * @var string
     */
    public string $importFileExtension = 'dat';

    /**
     * Типы парсеров в виде пар "тип - класс парсера".
     * 
     * @var array
     */
    protected array $parserTypes = [
        'xml'  => '\Ge\Import\Parser\XmlParser',
        'dom'  => '\Ge\Import\Parser\DomParser',
        'json' => '\Ge\Import\Parser\JsonParser'
    ];

    /**
     * Парсер, применяемый для разбора импортируемого файла.
     * 
     * @var AbstractParser|null
     */
    protected ?AbstractParser $parser = null;

    /**
     * Класс модели данных или активной записи, применяемой для импорта данных 
     * полученных от парсера.
     * 
     * @var string
     */
    protected string $modelClass = '';

    /**
     * Модель данных или активная запись, применяемая для импорта данных полученных 
     * от парсера.
     * 
     * @var ActiveRecord|null
     */
    protected ?ActiveRecord $model = null;

    /**
     * Абсолютный (базовый) путь для копирования файлов.
     *
     * @var string
     */
    public string $filesPath = '@published/uploads';

    /**
     * Права доступа к созданной для копирования файлов директории.
     *
     * @var string
     */
    public string|int $filesPathPerms = '0755';

    /**
     * Допустимые расширения файлов пакета импорта для копирования.
     *
     * @var string
     */
    public string $allowedExtensions = 'mp3,mp4,webm,webp,jpg,jpeg,gif,tiff,png,svg,doc,docx,pdf,xls,xlsx,xml,json,zip,otf,ttf,woff';

    /**
     * Список компонентов, для которых были импортированы демонстрационные данные.
     * 
     * @see Import::addProcessedComponent()
     * 
     * @var array<string, array>
     */
    public array $processedComponents = [];

    /**
     * Возвращает маску атрибутов (свойств, тегов) импортирумего файла.
     * 
     * Маска необходима для безопасного формирования атрибутов с их значениями, те
     * атрибуты которые не прошли через маску, являются "небезопасными".
     * Маска позволяет указать тип возращаемого значения.
     * 
     * Например:
     * ```php
     * [
     *     'маска_1'  => [
     *         'field' => 'поле таблицы',
     *         'type'  => 'тип значения' // {@see https://www.php.net/manual/ru/function.settype.php}
     *     ],
     *     'маска_2'  => 'поле таблицы', // тогда тип будет string
     *     ...
     * ]
     * ```
     * 
     * @return array<string, string>|array<string, array>
     */
    public function maskedAttributes(): array
    {
        return [];
    }

    /**
     * Возвращает Парсер.
     * 
     * @see Import::$parser
     * 
     * @return AbstractParser|null
     */
    public function getParser(): ?AbstractParser
    {
        return $this->parser;
    }

    /**
     * Создаёт Парсер.
     * 
     * @param string $type Тип парсер {@see Import::$parserTypes}.
     * 
     * @return AbstractParser|null
     */
    public function createParser(string $type): ?AbstractParser 
    {
        if (isset($this->parserTypes[$type])) {
            return new $this->parserTypes[$type]();
        }
        return null;
    }

    /**
     * Определяет тип парсера по указанному расширению файла.
     * 
     * @param string $extension Расширение файла, например: 'xml', 'json'...
     * 
     * @return string|null Возвращает тип парсера. Если значение `null`, то его тип 
     *     не определен.
     */
    public function defineParserType(string $extension): ?string
    {
        $type = strtolower($extension);
        if ($type === 'xml') {
            if (extension_loaded('simplexml'))
                $type = 'dom';
            else
            if (extension_loaded('xml'))
                $type = 'xml';
            else
                throw new Exception\ParseTypeException(
                    'Unable to determine parser type for XML file format.'
                );
        }
        return isset($this->parserTypes[$type]) ? $type : null;
    }

    /**
     * Этот событие вызывается перед импортом данных.
     *
     * @param string $filename Имя импортируемого файла (включая путь).
     * @param array $data Данные полученные после разбора парсером.
     *
     * @return bool Возвращает значение `true`, если необходимо импортировать данные.
     */
    public function beforeImport(string $filename, array &$data): bool
    {
        /** @var bool $canImport возможность удаления записи определяет событие */
        $canImport = true;
        $this->trigger(
            self::EVENT_BEFORE_IMPORT,
            [
                'filename'  => $filename,
                'data'      => $data,
                'canImport' => &$canImport
            ]
        );
        return $canImport;
    }

    /**
     * Этот событие вызывается перед началом импорта данных.
     *
     * @param string $filename Имя импортируемого файла (включая путь).
     * @param string|null $parserType Тип парсера. Если значение `null`, то тип не определен.
     *
     * @return bool Возвращает значение `true`, если необходимо выполнить импорт данных.
     */
    public function beforeRun(string $filename, ?string $parserType, bool $isPackage): bool
    {
        /** @var bool $canRun возможность удаления записи определяет событие */
        $canRun = true;
        $this->trigger(
            self::EVENT_BEFORE_RUN,
            [
                'filename'   => $filename,
                'parserType' => $parserType,
                'isPackage'  => $isPackage,
                'canRun'     => &$canRun
            ]
        );
        return $canRun;
    }

    /**
     * Этот событие вызывается после импорта данных.
     *
     * @param string $filename Имя импортируемого файла (включая путь).
     *
     * @return void
     */
    public function afterImport(string $filename): void
    {
        $this->trigger(
            self::EVENT_AFTER_IMPORT,
            ['filename'  => $filename]
        );
    }

    /**
     * Этот событие вызывается после импорта данных.
     *
     * @see Import::run()
     * 
     * @param string $filename Имя импортируемого файла (включая путь).
     * @param bool $isPackage Значение `true` если импорт из пакетного файла.
     *
     * @return void
     */
    public function afterRun(string $filename, bool $isPackage): void
    {
        $this->trigger(
            self::EVENT_AFTER_RUN,
            [
                'filename'  => $filename,
                'isPackage' => $isPackage,
            ]
        );
    }

    /**
     * Выполняет импорт данных из указанного файла.
     * 
     * @param string $filename Имя импортируемого файла (включая путь).
     * @param string|null $parserType Тип парсера. Если значение `null`, то тип будет 
     *     определн из расширения файла (по умолчанию `null`).
     * 
     * @return void
     * 
     * @throws Exception\ParseFileException Ошибка полученная при разборе файла.
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function run(string $filename, ?string $parserType = null): void
    {
        $this->filename = $filename;
        $this->path = pathinfo($filename, PATHINFO_DIRNAME);

        if (!$this->beforeRun($filename, $parserType, false)) return;

        if ($parserType === null) {
            $parserType = pathinfo($filename, PATHINFO_EXTENSION);
        }
        /** @var string|null $parserType Тип парсера */
        $parserType = $this->defineParserType($parserType);
        /** @var AbstractParser $parser */
        $parser = $this->createParser($parserType);
        /** @var false|array $data Данные импортируемого файла */
        $data = $parser->parseFile($filename);
        if ($parser->hasErrors()) {
            throw new Exception\ParseFileException($parser->getError());
        }
        $this->parser = $parser;

        if (!$this->beforeImport($filename, $data)) return;

        $this->importProcess($data);
        $this->afterImport($filename);
        $this->afterRun($filename, false);
    }

    /**
     * Выполняет импорт данных из пакета файлов.
     * 
     * @param string $filename Имя файла пакета включая путь.
     * 
     * @return void
     * 
     * @throws Exception\ParseFileException Ошибка полученная при разборе файла.
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    public function runPackage(string $filename): void
    {
        $this->filename = $filename;
        $this->path = pathinfo($filename, PATHINFO_DIRNAME);
        $this->processedComponents = [];

        $parserType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($parserType !== 'xml') return;

        if (!$this->beforeRun($filename, 'xml', true)) return;

        // парсер
        $parser = $this->createParser($this->defineParserType('xml'));
        if ($parser === null) {
            throw new Exception\ParserNotDefinedException(
                'Unable to determine parser type for file extension "xml"'
            );
        }

        /** @var array|false $package Информация о пакета */
        $package = $parser->parseFile($filename, true);
        if ($parser->hasErrors()) {
            throw new Exception\ParseFileException($parser->getError());
        }
        $this->parser = $parser;
        $this->package = $package;

        /** @var string $path Абсолютный путь к файлу пакета */
        $path = dirname($filename);
        // добавляем абсолютный путь к файлу пакета
        $this->package['path'] = $path;

        /** @var string|false $filesPath Путь к файлам копирования */
        $filesPath = Ge::getAlias($this->filesPath);
        if (empty($filesPath) || !file_exists($filesPath)) {
            throw new Exception\FilesPathNotDefinedException(
                'Base files path "' . $filesPath . '" not extists.'
            );
        }
        $filesPath = rtrim($filesPath, '\\/');

        Fs::$throwException = true;
        $allowedExtensions = explode(',', $this->allowedExtensions);
        $files = $package['files'];
        if ($files) {
            foreach ($package['files'] as $file) {
                /** @var string $copyFile Копируемый файл */
                $copyFile = $path . $file['name'];
                if (!file_exists($copyFile)) {
                    throw new Exception\FileNotFoundException(
                        'File "' . $copyFile . '" missing from import package.'
                    );
                }
                /** @var string $filePath Локальный путь копируемого файла в пакете */
                $filePath = trim($file['path'], ' \\/');
                /** @var string $filename Имя копирумего файла */
                $filename = pathinfo($file['name'], PATHINFO_BASENAME);
                /** @var string $extension Расширение копирумего файла */
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                // если необходимо проверить допустимое расширение файла
                if ($allowedExtensions && !in_array($extension, $allowedExtensions)) {
                    throw new Exception\ExtensionException(
                        'The file "' . $file['name'] . '" contains an invalid file extension..'
                    );
                }
                /** @var string $copyTo Путь куда копировать файл */
                $copyTo = $filesPath . DS . ($filePath ? $filePath . DS : '');
                if (!file_exists($copyTo)) {
                    Fs::makeDirectory($copyTo, $this->filesPathPerms, true);
                }
                Fs::copy($copyFile, $copyTo . $filename);
            }
        }

        foreach ($package['components'] as $component) {
            $type = $component['type'];
            if ($type === 'module')
                $isInstalled = Ge::IsModuleInstalled($component['id']);
            else
            if ($type === 'extension')
                $isInstalled = Ge::IsExtensionInstalled($component['id']);

            if ($isInstalled) {
                /** @var \Ge\Import\Import $model */
                $model = $this->createComponentImport(
                    $type, $component['id'], $component['cls'] ?: 'Import'
                );
                if ($model) {
                    // чтобы модель знала о существовании пакета если он есть
                    $model->setPackage($this->package);
                    $model->run($path . DS . $component['file']);
                }

                $this->addProcessedComponent($component, $model ? true : false);
            } else
                $this->addProcessedComponent($component, $isInstalled);
        }
        $this->afterRun($filename, true);
    }

    /**
     * Создаёт объект импорта данных компонента (модуля, расширения модуля), указанного 
     * в пакета файла импорта.
     * 
     * @var string $type Тип компонента: 'module', 'extension'.
     * @var string $componentId Идентификатор установленного компонента.
     * @var string $importCls Класс импорта данных.
     * 
     * @return mixed Возвращает значение `null` если объект компонента не создан.
     */
    protected function createComponentImport(
        string $type, 
        string $componentId, 
        string $importCls = 'Import'
    ): mixed {
        if ($type === 'module')
            return Ge::getMModel($importCls, $componentId);
        else
        if ($type === 'extension')
            return Ge::getEModel($importCls, $componentId);
        return null;
    }

    /**
     * Возвращает информацию о пакете импортируемых файлов.
     * 
     * @see Import::$package
     * 
     * @return array<string, mixed>
     */
    public function getPackage(): array
    {
        return $this->package;
    }

    /**
     * Устанавливает информацию о пакете импортируемых файлов.
     * 
     * @param array<string, mixed> $package Информация о пакете импортируемых файлов.
     * 
     * @return void
     */
    public function setPackage(array $package): void
    {
        $this->package = $package;
    }

    /**
     * Выполняет загрузку содержимого файла на основе значения атрибута.
     * 
     * Формат значения: @file:<путь_к_файлу>. 
     * Пример: @file:path/sample.dat
     * 
     * @see Import::importAttributes()
     * 
     * @return string Возвращает содержимое файла. Если файл недоступен 
     *    или загрузка не выполнена, возвращается сообщение или значение 
     *    переменной $value.
     */
    protected function importFileAttribute(string $value): string
    {
        $value = trim($value);
        if (mb_strpos($value, '@file:') !== false) {
            $value    = mb_substr($value, 6);
            $filename = pathinfo($value, PATHINFO_FILENAME) . '.' . $this->importFileExtension;
            $dirname  = pathinfo($value, PATHINFO_DIRNAME);
            $dirname  = $dirname === '.' ? '' : $dirname . DS;
            
            $filename = $this->path . DS . $dirname . $filename;
            if (file_exists($filename)) {
                $content = file_get_contents($filename, true);
                if ($content === false) {
                    return sprintf('Unable to import file "%s".', $filename);
                }
                return $content;
            } else
                return sprintf('File "%s" does not exist.', $filename);
        }
        return $value;
    }

    /**
     * Импорт (разбор) атрибутов текущего элемента (строки записей).
     * 
     * @param array $row Импортируемая строка атрибутов. 
     * 
     * @return array
     */
    protected function importAttributes(array $mask, array $row): array
    {
        /** @var array $columns */
        $columns = [];
        foreach ($mask as $alias => $params) {
            if (isset($row[$alias])) {
                // если имя поля
                if (is_string($params)) {
                    $columns[$params] = $row[$alias];
                // если параметры
                } else {
                    if (isset($params['callback']))
                        $columns[$params['field']] = $params['callback']($row);
                    else {
                        $value = $row[$alias];
                        // проверяет, можно ли импортировать данные из файла для указанного поля
                        if (isset($params['importFile']) && ($params['importFile'] === true)) {
                            $value = $this->importFileAttribute($value);
                        }
                        if (isset($params['type'])) {
                            settype($value, $params['type']);
                        }
                        if (is_string($value)) {
                            if (isset($params['trim'])) {
                                $value = mb_trim($value, is_bool($params['trim']) ? null : $params['trim']);
                            }
                            if (isset($params['length'])) {
                                $value = mb_substr($value, 0, $params['length']);
                            }
                        }
                        $columns[$params['field']] = $value;
                    }
                }
            }
        }
        return $columns;
    }

    /**
     * Метод вызывается после импорта (разбора) атрибутов текущего элемента (строки 
     * записей).
     *
     * @param array $columns Имена полей с их значениями.
     *
     * @return array
     */
    protected function afterImportAttributes(array $columns): array
    {
        return $columns;
    }

    /**
     * Процесс импорта данных.
     * 
     * @param array $data Все данные (свойства, атрибуты) полученные при разборе 
     *     импортируемого файла.
     * 
     * @return void
     * 
     * @throws CommandException Ошибка выполнения инструкции SQL.
     */
    protected function importProcess(array $data): void
    {
        /** @var array $mask Маска атрибутов */
        $mask = $this->maskedAttributes();
        if (empty($mask)) return;

        /** @var ActiveRecord|null $model */
        $model = $this->getModel();
        if ($model === null) {
            throw new Exception\ModelNotDefinedException('Model not found.');
        }

        // если удалить все записи модели данных
        if ($data['clear']) {
            if (method_exists($model, 'deleteAll')) {
                $model->deleteAll();
            }
        }

        foreach ($data['items'] as $item) {
            /** @var array $columns */
            $columns = $this->importAttributes($mask, $item);
            $columns = $this->afterImportAttributes($columns);
            $model->insertRecord($columns);
        }
    }

    /**
     * Возвращает модель данных (активную запись) для импорта данных.
     * 
     * @see Import::$modelClass
     * 
     * @return ActiveRecord|null Возвращает значение `null`, если невозможно создать модель 
     *     данных или не указано имя класса.
     */
    public function getModel(): ?ActiveRecord
    {
        if ($this->model === null) {
            if ($this->modelClass) {
                $this->model = new $this->modelClass();
            }
        }
        return $this->model;
    }

    /**
     * Добавляет компонент в список компонентов, для которых были импортированы 
     * демонстрационные данные.
     * 
     * @param array<string, mixed> $component Информация о компоненте.
     * @param bool $success Значение `true` устанавливается при успешном импорте 
     *    демонстрационных данных компонента.
     * 
     * @return void
     */
    public function addProcessedComponent(array $component, bool $success): void
    {
        $component['success'] = $success;
        $this->processedComponents[$component['id']] = $component;
    }

    /**
     * Возвращает список компонент для которых не импортированы демонстрационные данные.
     * 
     * @see Import::$processedComponents
     * 
     * @return array
     */
    public function getUnprocessedComponents(): array
    {
        $components = [];
        foreach ($this->processedComponents as $component) {
            if (!$component['success']) {
                $components[] = $component;
            }
        }
        return $components;
    }
}
