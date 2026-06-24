<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Updates\Formatter;

/**
 * Абстрактный класс форматировщика данных (контента) пакета обновлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Updates
 * @since 2.0
 */
class AbstractFormatter
{
    /**
     * Данные пакета обновления.
     * 
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * Ошибки полученные при разборе данных пакета обновлений.
     * 
     * @var array
     */
    public $errors = [];

    /**
     * Имя файла (информация) пакета обновлений (включает путь).
     * Только для чтения.
     * 
     * @var string
     */
    public $filename;

    /**
     * Конструктор класса.
     * 
     * @param string $filename
     * 
     * @return void
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Проверяет, существует ли файл с информацией о пакете обновлений.
     * 
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filename);
    }

    /**
     * Загружает файл данных пакета обновлений.
     * 
     * @return bool
     */
    public function load(): bool
    {
        return false;
    }

    /**
     * Записывает данные пакета в файл.
     *
     * @return int|false Возвращает количество байтов, которые процесс записал в файл, 
     *     или `false` в случае ошибки. 
     */
    public function save(): int|false
    {
        return false;
    }

    /**
     * Проверяет данные пакета обновлений.
     * 
     * @return bool Возвращает значение `false`, если отсутствует основная информация 
     *     пакета обновлений.
     */
    public function validate(): bool
    {
        return false;
    }

    /**
     * Проверяет установочные (обновляемые) файлы.
     * 
     * @param string $path Путь к файлам пакета.
     * 
     * @return false|array Имена файлов с указанными для них действиями.
     * Имеет вид:
     * ```php
     *    [
     *        [
     *            "action"      => "type" // тип действия
     *            "source"      => "filename" // исходный файл
     *            "destination" => "filename" // файл назначения
     *        ],
     *        // ...
     *    ]
     * ```
     */
    public function validateInstallFiles(string $path): false|array
    {
        return [];
    }

    /**
     * Проверяет файлы демонтажа.
     * 
     * @return false|array Имена файлов с указанными для них действиями.
     * Имеет вид:
     * ```php
     *    [
     *        [
     *            "action"      => "type" // тип действия
     *            "source"      => "filename" // исходный файл
     *            "destination" => "filename" // файл назначения
     *        ],
     *        // ...
     *    ]
     * ```
     */
    public function validateUninstallFiles(): false|array
    {
        return false;
    }

    /**
     * Проверяет SQL-запросы к базе данных.
     * 
     * @return array SQL-запросы к базе данных.
     */
    public function validateInstallQueries(): array
    {
        return [];
    }

    /**
     * Проверяет SQL-запросы демонтажа к базе данных.
     * 
     * @return array SQL-запросы к базе данных.
     */
    public function validateUninstallQueries(): array
    {
        return [];
    }

    /**
     * Проверяет совместимость версий с текущей версией приложения 
     * или версией редакции приложения.
     * 
     * @return false|array Возвращает значение `false`, если ошибка при проверки 
     *     версий совместимости.
     */
    public function validateCompatibleVersions(): false|array
    {
        return false;
    }

    /**
     * Проверяет зависемость пакета обновлений от других пакетов.
     * 
     * @return false|array Возвращает значение false, если ошибка при проверки 
     *     зависемостей.
     */
    public function validateDependencies(): false|array
    {
        return false;
    }

    /**
     * Возвращает элементы, которые должны присутствовать в контенте пакета обновлений.
     * 
     * @return array
     */
    public function getValidateElements(): array
    {
        return [
            'id', 'date', 'name', 'notes', 'purpose', 'importance', 'category', 'copyright', 'license', 
            'version/application/name', 'version/application/number', 'install'
        ];
    }

    /**
     * Проверяет, емеет ли пакет обновлений информацию о устанавливаемых файлах.
     * 
     * @return bool Возвращает значение `false`, если информация о устанавливаемых 
     *     файлах отсутствует.
     */
    public function hasInstallFiles(): bool
    {
        return false;
    }

    /**
     * Проверяет, имеет ли пакет обновлений информацию о SQL-запросах к базе данных, 
     * предназначенных для установки.
     * 
     * @return bool Возвращает значение `false`, если информация о SQL-запросах отсутствует.
     */
    public function hasInstallQueries(): bool
    {
        return false;
    }

    /**
     * Проверяет, емеет ли пакет обновлений информацию о демонтированных файлах.
     * 
     * @return bool Возвращает значение `false`, если информация о демонтированных 
     *     файлах отсутствует.
     */
    public function hasUninstallFiles(): bool
    {
        return false;
    }

    /**
     * Проверяет, имеет ли пакет обновлений информацию о SQL-запросах к базе данных, 
     * предназначенных для демонтажа пакета.
     * 
     * @return bool Возвращает значение `false`, информация о SQL-запросах отсутствует.
     */
    public function hasUninstallQueries(): bool
    {
        return false;
    }

    /**
     * Проверяет, есть ли совместимые версии для проверки.
     * 
     * @return bool Возвращает значение `false`, совместимых версий для проверки нет.
     */
    public function hasCompatibleVersions(): bool
    {
        return false;
    }

    /**
     * Проверяет, есть ли зависемость пакета обновлений от других пакетов.
     * 
     * @return bool Возвращает значение `false`, зависемостей для проверки нет.
     */
    public function hasDependencies(): bool
    {
        return false;
    }

    /**
     * Устанавливает данные пакета обновлений.
     * 
     * @param mixed $data Данные пакета обновлений.
     * 
     * @return mixed
     */
    public function setData(mixed $data): mixed
    {
        return $this->data = $data;
    }

    /**
     * Возвращает данные пакета обновлений.
     * 
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Проверяет наличие ошибок полученных при разборе данных пакета обновлений.
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
    public function setError($error): static
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
    public function getError()
    {
        return $this->getErrors(0);
    }

    /**
     * Возвращает ошибку(и) по указанному индексу очереди ошибок.
     * 
     * @param null|int $index Индекс очереди ошибки. Если null, возвратит все ошибки.
     * 
     * @return array|string Если указан идекс и нет ошибки, то "".
     */
    public function getErrors(?int $index = null): array|string
    {
        return $index === null ? $this->errors : ($this->errors[$index] ?? '');
    }

    /**
     * Передаёт все ошибку получателю.
     * 
     * @param mixed $reciever Получатель ошибок должен иметь метод "setError".
     * 
     * @return $this
     */
    public function flashErrors(mixed $reciever): static
    {
        if (method_exists($reciever, 'setError')) {
            $reciever->setError($this->errors);
        }
        return $this;
    }

    /**
     * Проверяет, имеет ли пакет обновлений данные.
     * 
     * @return bool
     */
    public function hasData(): bool
    {
        return $this->data !== null;
    }

    /**
     * Конвертирует данные пакета обновлений в формат ("ключ" => "значение") для 
     * добавления в базу данных.
     * 
     * Где, данные пакета обновлений {@see AbstractFormatter::$data}.
     * 
     * @return false|array 
     */
    public function dataToColumns(): false|array
    {
        return [];
    }

    /**
     * Конвертирует массив в формат данных пакета обновлений.
     * 
     * @param array $array Массив данных пакет обновлений.
     * 
     * @return mixed
     */
    public function arrayToData(array $array): mixed
    {
        return null;
    }

    /**
     * Добавление данных пакета обновлений в указанную модель данных.
     * 
     * @param object $model Модель данных.
     * 
     * @return bool
     */
    public function appendData(object $model): bool
    {
        return false;
    }

    /**
     * Преобразует данные (контента) пакета в строку.
     * 
     * @return string
     */
    public function toString(): string
    {
        return '';
    }
}
