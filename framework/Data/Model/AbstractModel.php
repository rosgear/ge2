<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Data\Model;

use Ge;
use Ge\Exception;
use Ge\Stdlib\BaseObject;
use Ge\Db\Adapter\Adapter;
use Ge\Validator\Formatter;
use Ge\Validator\ValidatorManager;

/**
 * Абстрактный класс модели данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Data\Model
 * @since 2.0
 */
class AbstractModel extends BaseObject
{
    /**
     * @var string Событие, возникшее перед проверкой атрибутов.
     */
    public const EVENT_BEFORE_VALIDATE = 'beforeValidate';

    /**
     * @var string Событие, возникшее в конце проверки атрибутов.
     */
    public const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * Первоначальные атрибуты.
     * 
     * Устанавливаются при создании модели и используются для определения
     * изменений значений в атрибутах c помощью {@see AbstractModel::getDirtyAttributes()}.
     *
     * @var array
     */
    protected array $oldAttributes = [];

    /**
     * Атрибуты предназначены для формирования запросов к базе данных.
     * 
     * @var array
     */
    public array $attributes = [];

    /**
     * Небезопасные атрибуты.
     * 
     * Такие атрибуты формируются при накладывании маски атрибутов {@see AbstractModel::maskedAttributes()}.
     * Те атрибуты, которые не прошли маску, являются небезопасными.
     * 
     * @var array
     */
    public array $unsafeAttributes = [];

    /**
     * Формат вывода ошибки клиенту.
     *
     * @var string
     */
    protected string $errorFormat = '<span class="g-message-box__label-warning">{label}</span>: {message}';

    /**
     * Массив ошибок.
     * 
     * @var array<int, string>
     */
    protected array $errors = [];

    /**
     * Менеджер валидации значений атрибутов.
     * 
     * @see AbstractModel::getValidator()
     * 
     * @var ValidatorManager
     */
    protected ValidatorManager $validator;

    /**
     * Форматирование значений атрибутов.
     * 
     * Используется только на этапе загрузке значений в атрибуты {@see AbstractModel::load()}.
     * 
     * @see AbstractModel::setFormatter()
     * 
     * @var Formatter
     */
    protected Formatter $formatter;

    /**
     * Адаптер подключения к базе данных.
     * 
     * @see AbstractModel::setDb()
     * 
     * @var Adapter
     */
    protected Adapter $db;

    /**
     * Устанавливает адаптер подключения к базе данных.
     * 
     * @param Adapter|null $adapter Адаптер подключения к базе данных.
     * 
     * @return $this
     */
    public function setDb(?Adapter $adapter): static
    {
        if ($adapter === null) {
            $adapter = Ge::$services->getAs('db');
        }
        $this->db = $adapter;
        return $this;
    }

    /**
     * Возвращает адаптер подключения к базе данных.
     * 
     * @return Adapter
     */
    public function getDb(): Adapter
    {
        if (!isset($this->db)) {
            $this->setDb(null);
        }
        return $this->db;
    }

    /**
     * Возвращает имя первичного ключа.
     * 
     * Имя указываемого первичного ключа должно совподать с именем 
     * в таблице базы данных.
     * 
     * @return string Первичный ключ таблицы базы данных.
     */
    public function primaryKey(): string
    {
        return '';
    }

    /**
     * Возвращает имя таблицы базы данных.
     * 
     * @return string Имя таблицы базы данных.
     */
    public function tableName(): string
    {
        return '';
    }

    /**
     * Возвращает атрибуты с метками в виде пар "ключ - значение".
     * 
     * Например:
     * ```php
     * ['width' => 'Ширина', 'height' => 'Высота', ...]
     * ```
     * 
     * @return array<string, string>
     */
    public function attributeLabels(): array
    {
        return [];
    }

    /**
     * Возвращает атрибуты со значениями в виде пар "ключ - значение", которые  будут 
     * добавлены к текущем атрибутам.
     * 
     * @param bool $isInsert Если значение `true`, то значение атрибутов добавляются. 
     *     Иначе, значения обновляются.
     * 
     * @return array<string, mixed>
     */
    public function appendAttributes(bool $isInsert): array
    {
        return [];
    }

    /**
     * Создает метку из названия атрибута.
     * 
     * Выполняется путём замены знаков подчеркивания, тире и точек пробелами и 
     * заменой первой буквы каждого слова в верхнем регистре.
     * 
     * Например: 'big_car' или 'BigCar' => 'Big Car'.
     * 
     * @param string $attribute Атрибут.
     * 
     * @return string Метка атрибута.
     */
    public function generateAttributeLabel(string $attribute): string
    {
        return $attribute;
    }

    /**
     * Возвращает метку атрибута.
     * 
     * @see AbstractModel::generateAttributeLabel()
     * @see AbstractModel::attributeLabels()
     * 
     * @param string $attribute Атрибут.
     * 
     * @return string Метка атрибута.
     */
    public function getAttributeLabel(string $attribute): string
    {
        $labels = $this->attributeLabels();
        return $labels[$attribute] ?? $this->generateAttributeLabel($attribute);
    }

    /**
     * Сбрасывает все атрибуты и ошибки.
     * 
     * @see AbstractModel::clearErrors()
     * 
     * @return $this
     */
    public function reset(): static
    {
        $this->attributes = [];
        $this->oldAttributes = [];
        $this->unsafeAttributes = [];
        $this->clearErrors();
        return $this;
    }

    /**
     * Возвращает значение, указывающее, есть ли какая-либо ошибка проверки значений 
     * атрибутов.
     * 
     * @return bool Значение `true`, если есть ошибки проверки.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
 
    /**
     * Удаляет все ошибки полученные при проверки значений атрибутов.
     * 
     * @return $this
     */
    public function clearErrors(): static
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Добавляет новую ошибку.
     * 
     * @param string $error Текст ошибки.
     * 
     * @return $this
     */
    public function addError(string $error): static
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Устанавливает ошибку первую в очередь.
     * 
     * @param string $error Текст ошибки.
     * 
     * @return $this
     */
    public function setError(string $error): static
    {
        $this->errors[0] = $error;
        return $this;
    }

    /**
     * Возвращает первую из очереди ошибку.
     * 
     * @return string Текст ошибки.
     */
    public function getError(): string
    {
        return $this->getErrors(0);
    }

    /**
     * Возвращает ошибки при проверки значений атрибутов.
     * 
     * @param int|null $index Индекс ошибки в массиве. Если значение `null`, то все 
     *     ошибки (по умолчанию `null`).
     * 
     * @return string|array<int, string> Значение '', если нет ошибки по указанному индексу.
     */
    public function getErrors(?int $index = null): string|array
    {
        if ($index === null) {
            return $this->errors;
        }
        return $this->errors[$index] ?? '';
    }

    /**
     * Возвращает текст ошибки по указанному формату.
     * 
     * @param array<int, string> $error Ошибка в формате: `['ошибка', 'метка']`.
     * @param string $label Метка атрибута (по умолчанию `null`).
     * 
     * @return string Форматированный текст ошибки.
     */
    protected function errorFormat(array $error, ?string $label = null): string
    {
        if ($label !== null) {
            $error[1] = $this->t($label);
        }
        $from = ['{message}', '{label}'];
        $to   = $error;
        return str_replace($from, $to, $this->errorFormat);
    }

    /**
     * Возвращает форматированный текст ошибки.
     * 
     * @see AbstractModel::errorFormat()
     * 
     * @param string|array<int, string> $error Ошибка или формат: `['ошибка', 'метка']`.
     * @param string $label Метка атрибута.
     * 
     * @return string Форматированный текст ошибки.
     */
    protected function errorFormatMsg(string|array $error, string $label): string
    {
        return $this->errorFormat([$error, $label], $label);
    }

    /**
     * Возвращает ошибки после их форматирования.
     * 
     * @see AbstractModel::errorFormat()
     * @see AbstractModel::generateAttributeLabel()
     * 
     * @param array<int, array<int, string>> $errors Ошибки в формате: `['ошибка', 'метка']`.
     * @param array<string, string> $attributeLabels Атрибуты с метками в виде пар 
     *     "ключ - значение".
     * 
     * @return array<int, string> Ошибки c форматированием.
     */
    protected function errorsFormat(array $errors, array $attributeLabels): array
    {
        $messages = [];
        foreach ($errors as $error) {
            $attributeName = $error[1];
            $label = $attributeLabels[$attributeName] ?? $this->generateAttributeLabel($attributeName);
            $messages[] = $this->errorFormat($error, $label);
        }
        return $messages;
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * 
     * @return string|array
     */
    public function t(string|array $message)
    {
        return $message;
    }

    /**
     * Возвращает форматтер.
     * 
     * @return Formatter
     */
    public function getFormatter(): Formatter
    {
        if (!isset($this->formatter)) {
            $this->formatter = new Formatter();
        }
        return $this->formatter;
    }

    /**
     * Возвращает правила форматирования значений атрибутов.
     * 
     * Правила применяются форматтером {@see AbstractModel::$formatter}.
     * 
     * Например:
     * ```php
     * [
     *    ['атрибут', 'правило'],
     *    [['атрибут-1', 'атрибут-2', ...], 'правило'],
     *    ...
     * ]
     * ```
     * 
     * @return array Правила форматирования значений атрибутов.
     */
    public function formatterRules(): array
    {
        return [];
    }

    /**
     * Форматирует значения атрибутов с помощью Форматтера.
     * 
     * Форматирование должно выполняться сразу после загрузке {@see AbstractModel::load()} 
     * значений атрибутов.
     * 
     * @param array<string, mixed> $attributes Атрибуты с их значениями в виде пар 
     *     "ключ - значение".
     * 
     * @return void
     */
    public function formatAttributes(array &$attributes): void
    {
        $rules = $this->formatterRules();
        if ($rules) {
            $this->getFormatter()->format($rules, $attributes);
        }
    }

    /**
     * Возвращает менеджер проверки значений атрибутов.
     * 
     * @return ValidatorManager
     */
    public function getValidator(): ValidatorManager
    {
        if (!isset($this->validator)) {
            $this->validator = Ge::$services->get('validatorManager');
        }
        return $this->validator;
    }

    /**
     * Возвращает правила проверки значений атрибутов.
     * 
     * Правила применяются менеджером проверки {@see AbstractModel::$validator}.
     * 
     * Пример:
     * ```php
     * [
     *    [
     *        ['атрибут-1', 'атрибут-2', ...], 
     *        'правило', 
     *        'параметр-1' => 'значение', 
     *        'параметр-2' => 'значение',
     *        ...
     *    ],
     *    ...
     * ]
     * ```
     * 
     * @return array Правила валидации.
     */
    public function validationRules(): array
    {
        return [];
    }

    /**
     * Выполняет проверку значений атрибутов.
     * 
     * Если при проверки появились ошибки, то они будут в {@see AbstractModel::$errors}.
     * 
     * @param null|array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение" (по умолчанию `null`).
     * 
     * @return bool Значение `true`, если проверка всех атрибутов была успешна.
     */
    public function validate(?array $attributes = null): bool
    {
        if ($attributes === null) {
            $attributes = $this->getAttributes();
        }

        $validator = $this->getValidator();
        if (!$this->beforeValidate($attributes)) {
            return false;
        }

        if (!$validator->validate($this->validationRules(), $attributes)) {
            $this->errors = $this->errorsFormat(
                $validator->getMessages(),
                $this->attributeLabels()
            );

            $this->afterValidate(false);
            return false;
        }
        return $this->afterValidate(true);
    }

    /**
     * Событие перед проверкой атрибутов.
     * 
     * Возможность выполнить проверку атрибутов перед работой валидатора с указанными 
     * правилами проверки.
     *
     * @param array<string, mixed> $attributes Атрибуты со значениями, которые 
     *     будут проверены.
     * 
     * @return bool Значение `true` указывает на запуск валидатора, иначе проверка 
     *     будет прервана.
     */
    public function beforeValidate(array &$attributes): bool
    {
        return true;
    }

    /**
     * Событие после валидации атрибутов.
     * 
     * @see AbstractModel::validate()
     * 
     * @param bool $isValid Если значение `true`, то проверка атрибутов прошла успешно.
     * 
     * @return bool Значение `true`, если проверка прошла успешно.
     */
    public function afterValidate(bool $isValid): bool
    {
        return $isValid;
    }

    /**
     * Загружает (устанавливает) значение атрибутам.
     * 
     * @param array<string, mixed> $data Атрибуты со значениями в виде пар "ключ - значение".
     * 
     * @return bool Значение `true`, если атрибуты были установлены.
     */
    public function load(array $data): bool
    {
        $this->beforeLoad($data);
        $this->setAttributes($data);
        $this->formatAttributes($this->attributes);
        $this->afterLoad();
        return !empty($this->attributes);
    }

    /**
     * Событие после загрузки (установки) атрибутам значений.
     * 
     * @see AbstractModel::load()
     * 
     * @return void
     */
    public function afterLoad(): void
    {
    }

    /**
     * Событие до загрузки (установки) атрибутам значений.
     * 
     * @see AbstractModel::load()
     * 
     * @param array<string, mixed> $data Атрибуты со значениями в виде пар "ключ - значение".
     * 
     * @return void
     */
    public function beforeLoad(array &$data): void
    {
    }

    /**
     * Проверят, были ли установлены атрибутам значения.
     *
     * @return bool Значение `true`, если атрибутам были установлены значения.
     */
    public function isLoaded(): bool
    {
        return !empty($this->attributes);
    }

    /**
     * Помечает атрибут как "грязный".
     * 
     * @param string $attribute Атрибут.
     * 
     * @return $this
     */
    public function markAttributeDirty(string $attribute): static
    {
        unset($this->oldAttributes[$attribute]);
        return $this;
    }

   /**
     * Возвращает значения атрибутов, которые были изменены с момента их загрузки 
     * или сохранения в последний раз.
     *
     * Сравнение новых и старых значений выполняется с использованием '==='.
     *
     * @param null|array<int, string> $names Атрибуты, значения которых необходимо 
     *     проверить и вернуть если они были изменены. Если значение `null`, то вместо 
     *     `$names` будет применяться {@see AbstractModel::$attributes} (по умолчанию `null`).
     * 
     * @return array Атрибуты со значениями, которые были изменены.
     */
    public function getDirtyAttributes(?array $names = null): array
    {
        if ($names === null) {
            $names = array_keys($this->attributes);
        }

        $attributes = [];
        if (empty($this->oldAttributes)) {
            foreach ($names as $name) {
                // т.е. isset не проверяет на null
                if (array_key_exists($name, $this->attributes)) {
                     $attributes[$name] = $this->attributes[$name];
                }
            }
        } else {
            foreach ($names as $name) {
                // т.е. isset не проверяет на null
                if (array_key_exists($name, $this->attributes)) {
                    if (!isset($this->oldAttributes[$name]) || $this->oldAttributes[$name] !== $this->attributes[$name]) {
                        $attributes[$name] = $this->attributes[$name];
                    }
                }
            }
        }
        return $attributes;
    }

    /**
     * Проверяет, является ли указанный атрибут "грязным". 
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function isDirtyAttribute(string $name): bool
    {

        if (empty($this->oldAttributes)) {
            return array_key_exists($name, $this->attributes);
        } else {
            // т.е. isset не проверяет на null
            if (array_key_exists($name, $this->attributes)) {
                return !isset($this->oldAttributes[$name]) || $this->oldAttributes[$name] !== $this->attributes[$name];
            }
        }
        return false;
    }

   /**
     * Удаляет все атрибуты.
     *
     * @return $this
     */
    public function clearAttributes(): static
    {
        $this->attributes = [];
        return $this;
    }

    /**
     * Добавляет атрибуты.
     * 
     * @param array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение".
     * 
     * @return $this
     */
    public function addAttributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Добавляет атрибут.
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     */
    public function addAttribute(string $name, $value): static
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Устанавливает первоначальные атрибуты.
     * 
     * @see AbstractModel::setAttributes()
     * @see AbstractModel::setOldAttributes()
     * 
     * @param array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение".
     * @param bool $safeOnly Только безопасные атрибуты. Если значение `true`, то 
     *     каждый атрибут будет проверен с помощью {@see AbstractModel::onUnsafeAttribute()}.
     * 
     * @return $this
     */
    public function setPrimaryAttributes(array $attributes, bool $safeOnly = true): static
    {
        $this->setAttributes($attributes, $safeOnly);
        $this->setOldAttributes($this->attributes);
        return $this;
    }

    /**
     * Устанавливает атрибуты.
     * 
     * @see AbstractModel::maskedAttributes()
     * 
     * @param array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение".
     * @param bool $safeOnly Только безопасные атрибуты. Если значение `true`, то 
     *     каждый атрибут будет проверен с помощью {@see AbstractModel::onUnsafeAttribute()}.
     * 
     * @return $this
     */
    public function setAttributes(array $attributes, bool $safeOnly = true): static
    {
        $maskedAttributes = $this->maskedAttributes();
        if ($maskedAttributes) {
            foreach ($attributes as $name => $value) {
                if (isset($maskedAttributes[$name])) {
                    $this->attributes[$name] = $value;
                 } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                 }
            }
        } else {
            foreach ($attributes as $name => $value) {
                $this->attributes[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Устанавливает атрибуты с приминением "перевёрнутой" маски.
     * 
     * Например:
     * ```php
     * // исходные атрибуты
     * ['маска_1' => 'значение', 'маска_2' => 'значение', ...]
     * // результирующие атрибуты
     * ['атрибут_1' => 'значение', 'атрибут_2' => 'значение', ...]
     * ```
     * 
     * @param array<string, mixed> $attributes Атрибуты (с маской) со значениями в 
     *     виде пар "ключ - значение".
     * @param bool $safeOnly Если значение `false`, то применяются "небезопасные" 
     *     атрибуты, т.е. те атрибуты, которые не прошли через маску {@see AbstractModel::maskedAttributes()}.
     * 
     * @return $this
     */
    public function setPopulateAttributes(array $attributes, bool $safeOnly = true): static
    {
        $maskedAttributes = $this->maskedAttributes();
        if ($maskedAttributes) {
            $maskedAttributes = array_flip($maskedAttributes);
            foreach ($attributes as $name => $value) {
                if (isset($maskedAttributes[$name])) {
                    $this->attributes[$maskedAttributes[$name]] = $value;
                 } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                 }
            }
        } else {
            foreach ($attributes as $name => $value) {
                $this->attributes[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Устанавливает "старые" (с первоначальными значениями) атрибуты.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @param array<string, mixed> $attributes Атрибуты со значениями в виде пар 
     *     "ключ - значение".
     * 
     * @return $this
     */
    public function setOldAttributes(array $attributes): static
    {
        $this->oldAttributes = $attributes;
        return $this;
    }

    /**
     * Возвращает все атрибуты.
     * 
     * @see AbstractModel::$attributes
     * 
     * @return array<string, mixed> Все атрибуты в виде пар "ключ - значение".
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Указанный binder передаёт свои атрибуты текущему объекту.
     * 
     * @param AbstractModel|array $binder Тот, кто передаёт свои атрибуты.
     * 
     * @return void
     */
    public function bindAttributes(AbstractModel|array $binder): void
    {
        if ($binder) {
            if (is_array($binder))
                $this->attributes = $binder;
            else
            if ($binder instanceof self) {
                $this->attributes = $binder->attributes;
            }
        }
    }

    /**
     * Возвращает все "старые" (с первоначальными значениями) атрибуты.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @return array<string, mixed> Все "старые" (с первоначальными значениями) 
     *     атрибуты в виде пар "ключ - значение".
     */
    public function getOldAttributes(): array
    {
        return $this->oldAttributes;
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель атрибут.
     * 
     * @see AbstractModel::$attributes
     * 
     * @param string $name Название атрибута.
     * 
     * @return bool Значение `true`, если модель имеет указанный атрибут.
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель атрибуты.
     * 
     * @see AbstractModel::$attributes
     * 
     * @return bool Значение `true`, если модель имеет атрибуты.
     */
    public function hasAttributes(): bool
    {
        return !empty($this->attributes);
    }

    /**
     * Возвращает значение, указывающее на отсутствие атрибутов.
     * 
     * @return bool Значение `true`, если атрибуты отсутствуют.
     */
    public function isEmpty(): bool
    {
        return empty($this->attributes);
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель "старый" (с первоначальным 
     * значением) атрибут.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @param string $name Имя атрибута.
     * 
     * @return bool Значение `true`, если модель имеет "старый" с первоначальным 
     * значением) атрибут.
     */
    public function hasOldAttribute(string $name): bool
    {
        return isset($this->oldAttributes[$name]);
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель "старые" (с первоначальными 
     * значениями) атрибуты.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @return bool Значение `true`, если модель имеет "старые" (с первоначальными 
     * значениями) атрибуты.
     */
    public function hasOldAttributes(): bool
    {
        return !empty($this->oldAttributes);
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель "небезопасные" атрибуты.
     * 
     * @see AbstractModel::$unsafeAttributes
     * 
     * @return bool Значение `true`, если модель имеет "небезопасные" атрибуты.
     */
    public function hasUnsafeAttributes(): bool
    {
        return !empty($this->unsafeAttributes);
    }

    /**
     * Возвращает значение, указывающее, имеет ли модель "небезопасный" атрибут.
     * 
     * @see AbstractModel::$unsafeAttributes
     * 
     * @param string $name Имя атрибута.
     * 
     * @return bool Значение `true`, если модель имеет "небезопасный" атрибут.
     */
    public function hasUnsafeAttribute(string $name): bool
    {
        return isset($this->unsafeAttributes[$name]);
    }

    /**
     * Возвращает значение атрибута по указанному имени.
     * 
     * @see AbstractModel::$attributes
     * 
     * @param string $name Имя атрибута.
     * 
     * @return mixed Значение `null`, если атрибут не установлен.
     */
    public function getAttribute(string $name): mixed
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : null;
    }

    /**
     * Возвращает значение "старого" (с первоначальным значением) атрибута по указанному 
     * имени.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @param string $name Имя атрибута.
     * 
     * @return mixed Значение `null`, если атрибут не установлен.
     */
    public function getOldAttribute(string $name): mixed
    {
        return $this->hasOldAttribute($name) ? $this->oldAttributes[$name] : null;
    }

    /**
     * Устанавливает значение атрибуту.
     * 
     * @see AbstractModel::hasAttribute()
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException Название атрибута не существует.
     */
    public function setAttribute(string $name, mixed $value): static
    {
        if (!$this->hasAttribute($name)) {
            throw new Exception\InvalidArgumentException(
                get_class($this) . ' has no attribute named "' . $name . '".'
            );
        }
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Удаляет атрибут.
     * 
     * @see AbstractModel::$attributes
     * 
     * @param string $name Имя атрибута.
     * 
     * @return $this
     */
    public function unsetAttribute(string $name): static
    {
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Устанавливает значение "старому" атрибуту.
     * 
     * @see AbstractModel::$oldAttributes
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     */
    public function setOldAttribute(string $name, $value): static
    {
        $this->oldAttributes[$name] = $value;
        return $this;
    }

    /**
     * Возвращает маску атрибутов.
     * 
     * Маска необходима для безопасного формирования атрибутов с их значениями, те
     * атрибуты которые не прошли через маску, являются "небезопасными".
     * Маска не применяется к первичному полю таблицы базы данных, но указывается
     * для этого поля.
     * 
     * Например:
     * ```php
     * [
     *     'идент.' => 'идент.', // маска на идентификатор не накладывается
     *     'маска_1'  => 'поле_1',
     *     'маска_2'  => 'поле_2',
     *     ...
     * ]
     * ```
     * 
     * @return array<string, string>
     */
    public function maskedAttributes(): array
    {
        return [];
    }

    /**
     * Убирает маску из указанных атрибутов.
     * 
     * Если указанные атрибуты не имеют маску, то они не будут добавлены в результат.
     * 
     * Пример:
     * ```php
     * // исходный массив
     * ['маска_1' => 'значение', 'маска_2' => 'значение', ...]
     * // результирующий массив
     * ['атрибут_1' => 'значение', 'атрибут_2' => 'значение', ...]
     * ```
     * 
     * @see AbstractModel::maskedAttributes()
     * 
     * @param array<string, mixed> $attributes Атрибуты (c маской) со значениями в виде пар 
     *     "ключ - значенние".
     * 
     * @return array<string, mixed> Атрибуты (без маски) со значениями в виде пар
     *     "ключ - значенние".
     */
    public function unmaskedAttributes(array $attributes): array
    {
        $mask = $this->maskedAttributes();
        if ($mask) {
            $result = [];
            foreach ($attributes as $alias => $value) {
                if (isset($mask[$alias])) {
                    $result[$mask[$alias]] = $value;
                }
            }
            return $result;
        }
        return $attributes;
    }

    /**
     * Устанавливает маску для указанных атрибутов.
     * 
     * Пример:
     * ```php
     * // исходный массив
     * ['атрибут_1' => 'значение', 'атрибут_2' => 'значение', ...]
     * // результирующий массив
     * ['маска_1' => 'значение', 'маска_2' => 'значение', ...]
     * ```
     * 
     * @see AbstractModel::maskedAttributes()
     * 
     * @param array<string, mixed> $attributes Атрибуты (без маски) со значениями в виде пар 
     *     "ключ - значенние".
     * 
     * @return array<string, mixed> Атрибуты (с маской) со значениями в виде пар
     *     "ключ - значенние".
     */
    public function makeMaskedAttributes(array $attributes): array
    {
        $masked = $this->maskedAttributes();
        if ($masked) {
            $masked = array_flip($masked);
            $newAttributes = [];
            foreach ($attributes as $name => $value) {
                if (isset($masked[$name])) {
                    $newAttributes[$masked[$name]] = $value;
                }
            }
            return $newAttributes;
        }
        return $attributes;
    }

    /**
     * Устанавливает значение "небезопасному" атрибуту.
     * 
     * @see AbstractModel::$unsafeAttributes
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     */
    public function setUnsafeAttribute(string $name, mixed $value): static
    {
        $this->unsafeAttributes[$name] = $value;
        return $this;
    }

    /**
     * Устанавливает значение "небезопасному" атрибуту.
     * 
     * @see AbstractModel::$unsafeAttributes
     * 
     * @param string $name Имя атрибута.
     * @param mixed $value Значение атрибута.
     * 
     * @return $this
     */
    public function onUnsafeAttribute(string $name, mixed $value): static
    {
        $this->unsafeAttributes[$name] = $value;
        return $this;
    }

   /**
    * Возвращает значение "небезопасного" атрибута по указанному имени.
    * 
    * @see AbstractModel::$unsafeAttributes
    * 
    * @param string $name Имя атрибута.
    *
    * @return mixed Значение `null`, если атрибут не установлен.
    */
    public function getUnsafeAttribute(string $name): mixed
    {
        return $this->unsafeAttributes[$name] ?? null;
    }

   /**
    * Возвращает все "небезопасные" атрибуты в виде пар "ключ - значение".
    * 
    * @see AbstractModel::$unsafeAttributes
    * 
    * @return array<string, mixed> Все "небезопасные" атрибуты в виде пар 
    *     "ключ - значение".
    */
    public function getUnsafeAttributes(): array
    {
        return $this->unsafeAttributes;
    }
}
