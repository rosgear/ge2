<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Language;

use Ge;
use Ge\Stdlib\Service;
use Ge\Exception\BootstrapException;

/**
 * Язык локализации.
 * 
 * Language - это служба приложения, доступ к которой можно получить через `Ge::$app->language`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Language
 * @since 2.0
 */
class Language extends Service
{
    /**
     * @var string Положение слага языка в начале URL-адреса.
     */
    public const POS_PREFIX = 'prefix';

    /**
     * @var string Положение слага языка в конце URL-адреса.
     */
    public const POS_SUFFIX = 'suffix';

    /**
     * @var string События
     */
    public const EVENT_SET_LANGUAGE = 'set';

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Положение слага языка в URL-адреса.
     * 
     * Если положение:
     * - 'prefix', слаг в начале URL-адреса, например: 'https://domain/ru-RU/foo/bar';
     * - 'suffix', слаг в конце URL-адреса, например: 'https://domain/foo/bar/ru-RU';
     * 
     * @var string
     */
    public string $position = self::POS_PREFIX;

    /**
     * Параметр URL-адреса определяющий язык.
     * 
     * Такой параметр добавляется при формировании URL-адреса если отключен ЧПУ.
     * 
     * @see Language::toUrl()
     * 
     * @var string
     */
    public string $slugParam = 'ln';

    /**
     * Шаблоны файлов локализации.
     * 
     * @var array<string, array{basePath:string, pattern:string}>
     */
    public array $patterns = [];

    /**
     * Имена шаблонов, которые будут подключены при определении языка.
     * 
     * Имена шаблонов должны быть определены в {@see Language::$patterns}.
     * 
     * @var array<int, string>
     */
    public array $autoload = [];

    /**
     * Абсолютный путь к языкам.
     * 
     * Например: __DIR__ . '/../lang'.
     * 
     * @var string
     */
    public string $path = '';

    /**
     * Язык по умолчанию.
     * 
     * Слаг языка в URL-адресе, например: 'ru', 'en'.
     * Указанный слаг должен присутствовать в параметрах доступных языков.
     *
     * @var string
     */
    public string $default = '';

    /**
     * Доступные языки или параметры объекта, определяющий класс доступных языков.
     * 
     * @see Language::setAvailable()
     * 
     * @var array|AvailableLanguage
     */
    public array|AvailableLanguage|null $available;

    /**
     * Если положение слага в начале URL-адреса.
     * 
     * @see Language::init()
     * 
     * @var bool
     */
    public bool $isPosPrefix = false;

    /**
     * Если положение слага в конце URL-адреса.
     * 
     * @see Language::init()
     * 
     * @var bool
     */
    public bool $isPosSuffix = false;

    /**
     * Параметры языка.
     * 
     * @see Language::set()
     * 
     * @var array
     */
    protected array $parameters = [];

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'language';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->isPosPrefix = $this->position === self::POS_PREFIX;
        $this->isPosSuffix = !$this->isPosPrefix;

        $this->setAvailable(isset($this->available) ? $this->available : []);
    }

    /**
     * Устанавливает доступные языки.
     * 
     * @param array|null $available Параметры объекта, определяющий класс доступных языков.
     * 
     * @return void
     */
    public function setAvailable(?array $available): void
    {
        if ($available) {
            // если заданы параметры объекта
            if (isset($available['class']))
                $this->available = Ge::createConfig($available);
            else {
                $this->available = new AvailableLanguage();
                $this->available->setAll($available);
            }
        } else
            $this->available = null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function initVariables(): void
    {
        if ($this->parameters) {
            Ge::$app->params->language = $this->slug;
        }
    }

    /**
     * Возвращает параметры шаблона файла локализации.
     * 
     * @param string $name Название шаблона.
     * 
     * @return false|array{basePath:string, pattern:string}
     */
    public function getPattern(string $name): array|false
    {
        return $this->patterns[$name] ?? false;
    }

    /**
     * Возвращает метаинформацию о указанном языке.
     * 
     * @param string $localeName Имя локализации, например: 'ru_RU', 'en_GB'.
     * 
     * @return array{
     *     code:int,
     *     languageCode: int,
     *     countryCode: int,
     *     name: string,
     *     shortName: string,
     *     country: string,
     *     slug: string,
     *     tag: string,
     *     locale: string,
     *     alternative: string,
     *     translated: string,
     *     translatedFor: string,
     *     versionNumber: string,
     *     versionDate: string,
     *     versionAuthor: string,
     *     FRONTEND: bool,
     *     BACKEND: bool}
     * 
     * @throws Exception\MetaNotFoundException Невозможно получить метаинформацию.
     */
    public function loadMeta(string $localeName): array
    {
        $filename = $this->path . DS . $localeName . DS . '.language.php';
        if (!file_exists($filename)) {
            throw new Exception\MetaNotFoundException('', $filename);
        }
        return @include($filename);
    }

    /**
     * Определяет параметры язык из URL-адреса.
     * 
     * @return array Параметры языка.
     */
    public function define(): ?array
    {
        $language = null;
        $url = Ge::$app->urlManager;
        // если ЧПУ
        if ($url->enablePrettyUrl) {
            if ($this->isPosPrefix)
                $slug = $url->segments->first();
            else
                $slug = $url->segments->last();
            // если указан слаг
            if ($slug) {
                // если слаг совпадает с псевдонимом языка
                if (($language = $this->available->getBy($slug, 'slug')) !== null) {
                    if ($language) {
                        if ($this->isPosPrefix)
                            $url->segments->shift();
                        else
                            $url->segments->pop();
                        $url->setRoute($url->segments->collect());
                    }
                }
            }
        // без ЧПУ
        } else {
            $request = Ge::$app->request;
            $slug = $request->getQuery($this->slugParam, false);
            // если указан язык
            if ($slug !== false) {
                // если язык совпадает с псевдонимом языка
                $language = $this->available->getBy($slug, 'slug');
            }
        }

        // если язык не указан в запросе, используется язык по умолчанию
        if ($language === null) {
            $language = $this->available->getBy($this->default, 'slug');
            if ($language === null)
                throw new BootstrapException(
                    sprintf('Could not set language, language "%s" not defined', $this->default)
                );
        }
        return $language;
    }

    /**
     * Возвращает массив языков, параметры которых имееют указанное значение.
     * 
     * @param mixed $value Значение параметра.
     * @param string $parameter Параметры языка.
     * 
     * @return array<int, array>
     */
    public function getDependencies(mixed $value, string $parameter): array
    {
        $result = [];
        /** @var array $languages Все доступные (установленные) языки */
        $languages = $this->available->getAll();
        foreach ($languages as $language) {
            if ($language[$parameter] === $value) {
                $result[] = $language;
            }
        }
        return $result;
    }

    /**
     * Возвращает короткие имена языков через разделитель.
     * 
     * @param string $separator Разделитель, например ','.
     * @param array<int|string, array> $languages Языки.
     * 
     * @return string
     */
    public function getShortNames(string $separator, array $languages): string
    {
        $result = [];
        foreach ($languages as $language) {
            if (isset($language['shortName'])) {
                $result[] = $language['shortName'];
            }
        }
        return implode($separator, $result);
    }

    /**
     * Выбранный язык Русский.
     * 
     * @see Language::isRu()
     * 
     * @var bool
     */
    protected bool $_isRu;

    /**
     * Проверяет, является ли выбранный язык Русским.
     * 
     * @return bool
     */
    public function isRu(): bool
    {
        if (!isset($this->_isRu)) {
            if ($this->parameters['slug'] === 'ru') {
                return $this->_isRu = true;
            }

            $tag = explode('-', $this->parameters['tag']);
            // язык
            if ($tag[0] === 'ru') {
                return $this->_isRu = true;
            }
            // страна
            if (isset($tag[1]) && $tag[1] === 'RU') {
                return $this->_isRu = true;
            }
            $this->_isRu = false;
        }
        return $this->_isRu;
    }

    /**
     * Выбранный язык Английский.
     * 
     * @see Language::isEn()
     * 
     * @var bool
     */
    protected bool $_isEn;

    /**
     * Проверяет, является ли выбранный язык Английским.
     * 
     * @return bool
     */
    public function isEn(): bool
    {
        if (!isset($this->_isEn)) {
            if ($this->parameters['slug'] === 'en') {
                return $this->_isRU = true;
            }

            $tag = explode('-', $this->parameters['tag']);
            // язык
            if ($tag[0] === 'en') {
                return $this->_isEn = true;
            }
            $this->_isEn = false;
        }
        return $this->_isEn;
    }

    /**
     * Проверяет, является ли выбранный язык, языком по умолчанию.
     * 
     * @param null|string $slug Слаг языка, например: 'ru', 'en'.
     * 
     * @return bool Возвращает значение `true`, если язык по умолчанию является текущем.
     */
    public function isDefault(?string $slug = null): bool
    {
        if ($slug === null) {
            return $this->default === $this->slug;
        }
        return $this->default === $slug;
    }

    /**
     * Возвращает параметры языка по умолчанию.
     * 
     * @return array
     */
    public function getDefault(): array
    {
        static $parameters;

        if ($parameters === null && $this->default) {
            $parameters = $this->available->getBy($this->default, 'slug');
        }
        return $parameters;
    }

    /**
     * Возвращает слаг языка по указанном коду.
     * 
     * @param int $code Код языка.
     * 
     * @return string|null Возвращает значение `null` если язык не найден по указанному коду.
     */
    public function getSlugByCode(int $code): ?string
    {
        static $codes = null;

        if ($code) {
            if ($codes === null) {
                $codes = $this->available->getAllBy('code');
            }
            return isset($codes[$code]) ? $codes[$code]['slug'] : null;
        }
        return null;
    }

    /**
     * Устанавливает язык.
     * 
     * @see Language::initVariables()
     * 
     * @param string|int|array $language Слаг, код или параметры языка, например:
     *     'ru', 570643, `['slug' => 'ru-RU', 'tag' => 'ru-RU', ...]`.
     * 
     * @return $this
     */
     public function set(string|int|array $language): static
     {
         // если параметры языка
        if (is_array($language))
            $this->parameters = $language;
        else
        // если код языка
        if (is_numeric($language))
            $this->parameters = $this->available->getBy($language, 'code');
        // если слаг языка
        else
            $this->parameters = $this->available->getBy($language, 'slug');

        $this->initVariables();
        return $this;
     }

    /**
     * Возвращает параметр или параметры текущего языка.
     * 
     * @param null|string $name Имя параметра, например: 'tag', 'code', 'name', 
     *     'shortName', 'country', 'slug', 'locale', 'alternative'. Если значение
     *     `null`, то возвратит все параметры (по умолчанию `null`).
     * @param mixed $default Значение по умолчанию, если параметр отсутстсвует.
     * 
     * @return mixed Параметры или значение параметра языка.
     */
    public function get(?string $name = null, mixed $default = null): mixed
    {
        if ($name !== null) {
            return $this->parameters[$name] ?? $default;
        }
        return $this->parameters;
    }

    /**
     * Возвращает слаг URL-адреса текущего языка.
     * 
     * @param bool $addSlash Добавит "/" к возвравщаемому слагу. Если язык - текущий, 
     *     то "/" не будет добавлен, а слаг будет '' (по умолчанию `true`).
     * 
     * @return string
     */
    public function getUrlSlug(bool $addSlash = true): string
    {
        $slug = $this->isDefault() ? '' : $this->slug;
        return $addSlash ? ($slug ? '/' . $slug : '') : $slug;
    }

    /**
     * Добавляет или устанавливает слаг языка в указанные параметры.
     * 
     * @param array|string $parameter URL-адрес или HTTP-запрос.
     * @param string|null $slug Слаг из URL-адреса, определяющий язык, 
     *     например: 'ru', 'en'.
     * 
     * @return void
     */
    public function toUrl(array|string &$parameter, ?string $slug = null)
    {
        if ($slug === null) {
            // если язык текущий
            if ($this->isDefault()) return;

            $localSlug = $this->slug;
        } else {
            // если язык текущий
            if ($slug === $this->default) return;

            /** @var array|null $language */
            $language = $this->available->getBy($slug, 'slug');
            // если язык не определён
            if ($language === null) return;

            $localSlug = $language['slug'];
        }

        // если указаны параметры HTTP-запроса
        if (is_array($parameter)) {
            if (!isset($parameter[$this->slugParam])) {
                $parameter[$this->slugParam] = $localSlug;
            }
        // если указан URL-адрес
        } else {
            // если слаг в начале URL-адреса
            if ($this->isPosPrefix)
                $parameter = $localSlug . '/' . $parameter;
            // если слаг в конце URL-адреса
            else
                $parameter = $parameter . '/' . $localSlug;
        }
    }

    /**
     * Возвращает информацию о локализации языка.
     * 
     * @see \Ge\I18n\ISO\Adapter\Locales::get()
     * 
     * @return null|array{
     *    name: array,
     *    rusName: array,
     *    nativeName: array,
     *    language: string,
     *    region: string,
     *    script: string,
     *    windows: bool,
     *    numeric: string
     * }
     */
    public function getISOInfo(): ?array
    {
        return $this->locale ? Ge::$app->locale->getISO()->locales->get($this->locale) : null;
    }

    /**
     * Возвращение значение параметра языка.
     * 
     * @param string $name Название параметра.
     * 
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * Проверяет существование параметра языка.
     * 
     * @param string $name Название параметра.
     * 
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Устаналвивает значение параметру языка.
     * 
     * @param string $name Название параметра.
     * @param mixed $value Значение параметра.
     * 
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Удаляет значение параметра языка.
     * 
     * @param string $name Название параметра.
     * 
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->parameters[$name]);
    }
}
