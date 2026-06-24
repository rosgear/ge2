<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\Source;

use Ge;
use Ge\I18n\Exception;
use Ge\Language\Language;

/**
 * Базовый класс источника сообщений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\Source
 * @since 2.0
 */
class BaseSource
{
    /**
     * Шаблонны сообщений.
     * 
     * Устанавливается параметром конфигурации "patterns".
     * 
     * Имеет вид:
     *    [
     *        "patternName" => [
     *            "basePath" => "pattern/path",
     *            "pattern"  => "name-%s.php"
     *        ]
     *        ...
     *     ]
     * 
     * @see BaseSource::autoloadPatterns()
     * 
     * @var array<string, array{basePath:string, pattern:string}>
     */
    public array $patterns = [];

    /**
     * Автоподключение шаблонов после создания источника сообщений.
     * 
     * Устанавливается параметром конфигурации "autoload".
     * 
     * Имеет вид: ["patterName1", "patterName2"...]
     * 
     * @var array<int, string>
     */
    public array $autoload = [];

    /**
     * Добавление в источник сообщений из шаблонов транслятора (локализатора сообщений)
     * указанных в его конфигурации.
     * 
     * Используется для удобной локализации сообщений без указания необходимых 
     * категорий ($external) в трансляторе.
     * 
     * Устанавливается параметром конфигурации "external".
     * 
     * Имеет вид: ["patterName1", "patterName2"...]
     * 
     * @see BaseSource::addExternalPatterns()
     * 
     * @var array<int, string>
     */
    public array $external = [];

    /**
     * Язык по умолчанию.
     *
     * @var string
     */
    protected string $defLocale = '';

    /**
     * Язык.
     * 
     * @var Language
     */
    protected Language $language;

    /**
     * Разделитель строки, где левая часть строки отбрасывается, 
     * а с правой осуществляется сопоставление в массиве шаблонных строк
     *
     * @var string
     */
    protected string $formatChar = '#';

    /**
     * Название функции для локализации сообщений в моделе представления.
     * 
     * Устанавливается параметром конфигурации "funcname_translate".
     *
     * @var string
     */
    protected string $funcNameTranslate = 'translate';

    /**
     * Сообщения локализации.
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * Базовая конфигурация.
     *
     * @var array
     */
    protected array $baseConfig = [];

    /**
     * Значение параметра службы языка.
     * 
     * Используется для формирования имени подключаемого файла шаблона локализации
     * сообщений.
     * 
     * Если $localeDefault = auto, определяется 
     * из Ge::$app->language->{$this->filePatternReplaceBy},
     * иначе $localeDefault.
     * 
     * @var string
     */
    protected string $filePatternReplace = '';

    /**
     * Имя параметра службы языка.
     * 
     * Используется для формирования имени подключаемого файла шаблона локализации
     * сообщений.
     * 
     * @see Language::$parameters
     * 
     * @var string
     */
    protected string $filePatternReplaceBy = 'locale';

    /**
     * Имена подключенных шаблонов локализации.
     *
     * @var array<int, string>
     */
    protected array $included = [];

    /**
     * Код локали для загрузки сообщений локализации из 
     * файла шаблона.
     * 
     * Устанавливается параметром конфигурации "locale".
     * 
     * Пример: "ru_RU", "en_GB"..., "auto" (код локали определяется из 
     * выбранного языка {@see \Ge\Language\Language}).
     *
     * @var string
     */
    protected string $localeDefault = 'auto';

    /**
     * Код ошибки при попытке локализации сообщения.
     *
     * @var int
     */
    protected int $errorCode = 0;

    /**
     * Текст ошибки при попытке локализации сообщения.
     *
     * @var string
     */
    protected string $errorMessage = '';

    /**
     * Конструктор класса.
     * 
     * @param array $baseConfig Базовая конфигурация источника сообщений.
     * @param null|Language $language Используемый язык.
     * 
     * @return void
     */
    public function __construct(array $baseConfig, ?Language $language = null)
    {
        $this->language = $language ?: Ge::$app->language;
        $this->baseConfig = $baseConfig;

        $this->init();
    }

    /**
     * Возвращает код ошибки при попытке локализации сообщения.
     * 
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Возвращает текст ошибки при попытке локализации сообщения.
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Проверяет, была ли ошибка при попытке локализации сообщения.
     * 
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->errorCode > 0;
    }

    /**
     * Возвращает ошибку (код и текст) при попытке локализации сообщения.
     * 
     * @return array
     */
    public function getError(): array
    {
        return [$this->errorCode, $this->errorMessage];
    }

    /**
     * Устанавливает ошибку локализации сообщения.
     * 
     * @param int $code Код.
     * @param string $message Текст.
     * 
     * @return void
     */
    public function setError(int $code, string $message): void
    {
        $this->errorCode = $code;
        $this->errorMessage = $message;
    }

    /**
     * Инициализация.
     * 
     * @return void
     */
    protected function init(): void
    {
        $this->localeDefault = $this->baseConfig['locale'] ?? $this->localeDefault;
        $this->setFilePatternReplace($this->language);
        $this->funcNameTranslate = $this->baseConfig['funcname_translate'] ?? $this->funcNameTranslate;
        $this->patterns = $this->baseConfig['patterns'] ?? [];
        $this->autoload = $this->baseConfig['autoload'] ?? [];
        $this->external = $this->baseConfig['external'] ?? [];
        // попытка автозагрузки шаблонов
        $this->autoloadPatterns();
        // если указаны шаблоны транслятора
        if ($this->external) {
            $this->addExternalPatterns($this->external);
        }
    }

    /**
     * Устанавливает значение (параметра или свойства) используемого для формирования имени 
     * подключаемого файла шаблона локализации сообщений.
     * 
     * Значение может принадлежать одному из свойств объекта {@see \Ge\Language\Language} или 
     * одному из указанных параметров `$params`.
     * 
     * Полученное значение будет принадлежать {@see BaseSource::$filePatternReplace}.
     * 
     * @param array|object $params Параметры или свойства:
     *     - свойства {@see \Ge\Language\Language};
     *     - параметры полученные {@see \Ge\Language\AvailableLanguage::getBy()}.
     * 
     * @return void
     */
    protected function setFilePatternReplace(array|object $params): void
    {
        if ($this->localeDefault === 'auto') {
            if (is_array($params)) {
                $this->filePatternReplace = $params[$this->filePatternReplaceBy];
            } else
                $this->filePatternReplace = $params->{$this->filePatternReplaceBy};
        } else
            $this->filePatternReplace = $this->localeDefault;
    }

    /**
     * Проверяет, подключен ли шаблон.
     * 
     * @param string $patterName Имя шаблона.
     * 
     * @return bool
     */
    public function isIncluded(string $patterName): bool
    {
        return isset($this->included[$patterName]);
    }

    /**
     * Автоподключение шаблонов указанных в конфигурации источника сообщений.
     * 
     * Шаблоны указываются параметром конфигурации "autoload" {@see BaseSource::$autoload}.
     * 
     * @return $this
     */
    protected function autoloadPatterns(): static
    {
        if ($this->autoload)
            $this->addPatterns($this->autoload);
        else {
            if ($this->patterns)
                $this->addPatterns(array_keys($this->patterns));
        }
        return $this;
    }

    /**
     * Автоподключение шаблонов указанных в конфигурации службы языка.
     * 
     * @return $this
     */
    public function autoloadLocalePatterns(): static
    {
        $autoload = $this->language->autoload;
        if (empty($autoload)) return $this;

        $this->addLocalePatterns($autoload);
        return $this;
    }

    /**
     * Добавляет шаблон сообщений.
     * 
     * @see BaseSource::getPattern()
     * @see BaseSource::loadPattern()
     * 
     * @param string $patternName Имя шаблона.
     * 
     * @return $this
     */
    public function addPattern(string $patternName): static
    {
        if ($this->isIncluded($patternName)) return $this;

        $pattern = $this->getPattern($patternName);
        if ($pattern === false) {
            throw new Exception\PatternNotExistsException(
                sprintf('Could not load pattern, pattern  "%s" not exists', $patternName),
                $patternName
            );
        }
        /**
         * Можно так:
         * $filename = $pattern['basePath'] . DS . sprintf($pattern['pattern'], $this->filePatternReplace);
         * this->loadPattern($pattern);
         * но так не будет задействовано альтернативное подключение (если оно есть) при ошибке подключение
         * текущей локализации
         */
        $this->loadPattern($pattern);
        return $this;
    }

    /**
     * Возвращает шаблон сообщений.
     * 
     * @see BaseSource::getPattern()
     * @see BaseSource::loadPattern()
     * 
     * @param string $patternName Имя шаблона.
     * @param array $filePatternReplace
     * 
     * @return array
     * 
     * @throws Exception\PatternNotExistsException Не удалось загрузить шаблон локализации.
     */
    public function getPatternMessages(string $patternName, array $filePatternReplace): array
    {
        $pattern = $this->getPattern($patternName);
        if ($pattern === false) {
            throw new Exception\PatternNotExistsException(
                sprintf('Could not load pattern, pattern  "%s" not exists', $patternName),
                $patternName
            );
        }
        return $this->loadPattern($pattern, false);
    }

    /**
     * Подключает шаблон службы языка.
     * 
     * @param string $patternName Имя шаблона.
     * 
     * @return $this
     * 
     * @throws Exception\PatternNotExistsException Не удалось загрузить шаблон локализации.
     */
    public function addLocalePattern(string $patternName): static
    {
        // если не указано (установлено '')
        if (empty($patternName)) return $this;

        if ($this->isIncluded($patternName)) return $this;

        $pattern = $this->language->getPattern($patternName);
        if ($pattern === false) {
            throw new Exception\PatternNotExistsException(
                sprintf('Could not load locale pattern, pattern  "%s" not exists', $patternName),
                $patternName
            );
        }
        $filePatternReplace = $this->language->{$this->filePatternReplaceBy};
        $filename = $pattern['basePath'] . DS . $filePatternReplace . DS . sprintf($pattern['pattern'], $filePatternReplace);
        $this->loadPattern($filename);
        return $this;
    }

    /**
     * Подключает шаблон источника сообщений.
     * 
     * @param mixed $patternNames Имена шаблонов.
     *    Имеет вид:
     *        - `['pattern_1', 'pattern_2'...]`;
     *        - 'pattern_1', 'pattern_2'...
     * 
     * @return $this
     */
    public function addPatterns(mixed $patternNames = null): static
    {
        if ($patternNames === null) {
            $patternNames = func_get_args();
        }

        foreach ($patternNames as $name) {
            $this->addPattern($name);
        }
        return $this;
    }

    /**
     * Подключает шаблоны службы языка.
     * 
     * @param mixed $patternNames Имена шаблонов.
     *    Имеет вид:
     *        - `['pattern_1', 'pattern_2'...]`;
     *        - 'pattern_1', 'pattern_2'...
     * 
     * @return $this
     */
    public function addLocalePatterns(mixed $patternNames = null): static
    {
        if ($patternNames === null) {
            $patternNames = func_get_args();
        }

        $patternNames = (array) $patternNames;
        foreach ($patternNames as $name) {
            $this->addLocalePattern($name);
        }
        return $this;
    }

    /**
     * Подключает шаблоны службы транслятора (локализатора сообщений).
     * 
     * @param array<int, string> $external Имена шаблонов.
     * 
     * @return $this
     */
    public function addExternalPatterns(array $external): static
    {
        $translator = Ge::$app->translator;
        foreach ($external as $name) {
            if (!$translator->categoryExists($name)) {
                $translator->addCategory($name)->addLocalePattern($name);
            }
        }
        return $this;
    }

    /**
     * Загружает сообщения локализации из указанного шаблона.
     * 
     * @param string|array $paramsOrFilename Имя файла шаблона или параметры шаблона.
     * 
     *     Пример параметров шаблона: 
     *     `['basePath' => '.../Backend/Signin/config/language', 'pattern' => 'text-%s.php']`.
     * 
     * @param bool $appendMessages Если `true`, добавить загруженные сообщения к сообщениям 
     *     локализации (по умолчанмю `true`).
     * 
     * @return array
     * 
     * @throws Exception\PatternNotLoadException Невозможно использовать альтернативный язык.
     * @throws Exception\PatternNotLoadException Невозможно подключить файл языка.
     */
    public function loadPattern($paramsOrFilename, bool $appendMessages = true): array
    {
        if (is_array($paramsOrFilename)) {
            $filename = $paramsOrFilename['basePath'] . DS . sprintf($paramsOrFilename['pattern'], $this->filePatternReplace);
            // если файл локализации не существует
            if (!file_exists($filename)) {
                // попытка подключить альтернативную локализацию, где
                // alternative - локаль установленного языка, например: en_GB, ru_RU
                if ($this->language->alternative) {
                    $params = $this->language->available->getBy($this->language->alternative, 'locale');
                    if (empty($params)) {
                        throw new Exception\PatternNotLoadException(
                            sprintf('Can not use alternative language "%s" included by "%s"', $this->language->alternative, $this->language->locale)
                        );
                    }
                    $this->setFilePatternReplace($params);
                    $filename = $paramsOrFilename['basePath'] . DS . sprintf($paramsOrFilename['pattern'], $this->filePatternReplace);
                    $this->loadPattern($filename, $appendMessages);
                // попытка подключить локализацию по умолчанию, где
                // default - тег установленного языка
                } else {
                    $params = $this->language->available->getBy($this->language->default, 'tag');
                    if (empty($params)) {
                        throw new Exception\PatternNotLoadException(
                            sprintf('Can not use default language "%s" included by "%s"', $this->language->default, $this->language->tag)
                        );
                    }
                    $this->setFilePatternReplace($params);
                    $filename = $paramsOrFilename['basePath'] . DS . sprintf($paramsOrFilename['pattern'], $this->filePatternReplace);
                    $this->loadPattern($filename, $appendMessages);
                }
            }
        } else 
            $filename = $paramsOrFilename;

        // если файл локализации не существует
        if (!file_exists($filename)) {
            throw new Exception\PatternNotLoadException(
                sprintf('Can not include language file "%s"', $filename)
            );
        }

        /** @var false|array $messages */
        $messages = include($filename);
        if ($messages === false) {
            throw new Exception\PatternNotLoadException(
                sprintf('Can not include language file "%s"', $filename)
            );
        }

        if ($appendMessages) {
            return $this->messages = $this->messages + $messages;
        }
        return $messages;
    }

    /**
     * Возвращает шаблон.
     * 
     * Возвращаемый шаблон имеет вид:
     * ```php
     * [
     *     'basePath' => 'pattern/path',
     *     'pattern'  => 'name-%s.php'
     * ]
     * ```
     * 
     * @param string $name Имя шаблона.
     * 
     * @return false|array{basePath:string, pattern:string} Возвращает значение `false`, 
     *     если шаблон не найден.
     */
    public function getPattern(string $name): false|array
    {
        return $this->patterns[$name] ?? false;
    }

    /** 
     * Возвращает все шаблоны.
     * 
     * @return array<string, array{basePath:string, pattern:string}>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Возвращает все сообщения локализации.
     * 
     * @return array<int, string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function translate(string|array $message, array $params = [], string $locale = ''): string|array
    {
        return $message;
    }
}
