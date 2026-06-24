<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n;

use Ge;
use Ge\I18n\ISO\ISO;
use Ge\I18n\ISO\Adapter\AbstractAdapter as AdapterISO;
use Ge\Stdlib\Service;

/**
 * Служба локализации.
 * 
 * Locale - это служба приложения, доступ к которой можно получить через `Ge::$app->locale`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Locale
 * @since 2.0
 */
class Locale extends Service
{
    /**
     * Менеджер обозначений ISO.
     * 
     * @see Locale::getISO()
     * 
     * @var ISO
     */
    protected ISO $iso;

    /**
     * Текущей код локализации.
     * 
     * @see \Ge\Language\Language::$locale
     * @see Locale::init()
     * 
     * @var string
     */
    protected string $locale;

    /**
     * Загружено ли расширение intl PHP.
     * 
     * @link http://php.net/manual/ru/book.intl.php
     * 
     * @var bool
     */
    public bool $intlIsLoaded = false;

    /**
     * Брость исключение если расширение intl PHP не загружено.
     * 
     * Если необходимо обойтись без расширения, тогда false.
     * 
     * @var bool
     */
    public bool $throwIntlException = true;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'locale';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->intlIsLoaded = extension_loaded('intl');
        if ($this->throwIntlException && !$this->intlIsLoaded) {
            throw new Exception\ExtensionNotLoadedException(sprintf(
                '%s service requires the intl PHP extension',
                $this->getClass()
            ));
        }
        $this->locale = Ge::$app->language->get('locale', '');
    }

    /**
     * Возвращает список всех локалей, доступных в библиотеке ICU (через расширение PHP intl).
     * 
     * @param bool $withISO Если значение `true`, будет добавлена информации о локали из
     *     {@see \Ge\I18n\ISO\ISO}.
     * 
     * @return array<int, string>
     */
    public function getSupportedLocales(bool $withISO = false): array
    {
        $locales = $this->intlIsLoaded ? \ResourceBundle::getLocales('') : [];

        if ($locales && $withISO) {
            $supported = [];
            $ilocales  = $this->getISO()->locales->getAll();
            foreach ($locales as $name) {
                if (isset($ilocales[$name]))
                    $supported[$name] = $ilocales[$name];
                else
                    $supported[$name] = [];
            }
            return $supported;
        } else
            return $locales;
    }

    /**
     * Возвращает обозначений ISO или его адаптер.
     * 
     * @return ISO|AdapterISO
     */
    public function getISO(string $adapterName = ''): ISO|AdapterISO
    {
        if (!isset($this->iso)) {
            $this->iso = new ISO();
        }
        return $adapterName ? $this->iso->$adapterName : $this->iso;
    }

    /**
     * Определяет язык, страну и письменность из строки.
     * 
     * Строка может иметь вид:
     * - 'ru' => 'язык',
     * - 'ru_KG' => 'язык_страна',
     * - 'kk_Cyrl_KZ' => 'язык_письменность_страна',
     * 
     * @param string $str Имя локализации, например: 'ru', 'ru_KG', 'kk_Cyrl_KZ'.
     * 
     * @return array
     */
    public function defineFromStr(string $str): array
    {
        $chunks = explode('_', $str);
        // если 'kk_Cyrl_KZ'
        if (sizeof($chunks) > 2) {
                $language = $this->getISO('languages')->get($chunks[0]);
                $script   = $this->getISO('scripts')->get($chunks[1]);
                $country  = $this->getISO('countries')->get($chunks[2]);
        // если 'ru_KG'
        } else {
            $language = $this->getISO('languages')->get($chunks[0]);
            if (isset($chunks[1])) {
                /** @var null|array $info */
                $info = $this->getISO('scripts')->get($chunks[1]);
                if ($info) {
                    $country = null;
                    $script  = $info;
                } else {
                    $country = $this->getISO('countries')->get($chunks[1]);
                    $script  = null;
                }
            } else {
                $country = $script = null;
            }
        }
        return [
            'language' => $language,
            'script'   => $script,
            'country'  => $country
        ];
    }

    /**
     * Возвращает код локализации из строки.
     * 
     * Строка может иметь вид:
     * - 'ru' => '{language}' => '570',
     * - 'ru_KG' => '{language}_{country}' => '570_417' => '570417',
     * - 'kk_Cyrl_KZ' => '{language}_{script}_{country}' => '255_220_398' => '255220398',
     * 
     * @param string $str Имя локализации, например: 'ru', 'ru_KG', 'kk_Cyrl_KZ'.
     * 
     * @return string|null Возвращает значение `null`, если не удалось определить код.
     */
    public function getCodeFromStr(string $str): ?string
    {
        $chunks = explode('_', $str);
        // если 'kk_Cyrl_KZ'
        if (sizeof($chunks) > 2)
            return $this->getCode($chunks[0], $chunks[2], $chunks[1]);
        // если 'ru_KG'
        else {
            if (isset($chunks[1])) {
                /** @var null|array $info */
                $info = $this->getISO('scripts')->get($chunks[1]);
                if ($info)
                    return $this->getCode($chunks[0], '', $chunks[1]);
                else
                    return $this->getCode($chunks[0], $chunks[1], '');
            } else
                return $this->getCode($chunks[0], '', '');
        }
    }

    /**
     * Возвращает уникальный код локализации.
     * 
     * @param string $language Язык, например: 'ru', 'be', 'en'...
     * @param string $country Страна, например: 'RU', 'BE', 'EN'... Если значение '', 
     *     код страны не будет добавлен в результат (по умолчанию '').
     * @param string $script Письменность, например: 'Arab', 'Grek', 'Cyrl'... Если значение '', 
     *     код письменности не будет добавлен в результат (по умолчанию '').
     * 
     * @return string|null Возвращает значение `null`, если не удалось определить код.
     */
    public function getCode(string $language, string $country = '', string $script = ''): ?string
    {
        /** @var \Ge\I18n\ISO\ISO $iso */
        $iso = $this->getISO();
        /** @var null|string $languageCode */
        $languageCode = $iso->languages->getCode($language);
        if ($languageCode) {
            /** @var null|string $countryCode */
            if ($country)
                $countryCode = $iso->countries->getCode($country);
            else
                $countryCode = '';
            if ($script)
                $scriptCode = $iso->scripts->getCode($script);
            else
                $scriptCode = '';
            return $languageCode . $scriptCode . $countryCode;
        }
        return null;
    }

    /**
     * Возвращает информацию о записи обозначения из адаптера ISO по коду.
     * 
     * @param string $adapterName Имя адаптера ISO.
     * @param string $code Код записи.
     * 
     * @return mixed Если значение `null`, информация не доступна.
     */
    public function getISOInfo(string $adapterName, string $code): mixed
    {
        $adapter = $this->getISO($adapterName);
        if ($adapter) {
            return $adapter->get($code);
        }
        return null;
    }

    /**
     * Возвращает директорию локалей.
     * 
     * @param string $locale Код локализации.
     * 
     * @return string
     */
    public function getLocalesPath(string $locale = ''): string
    {
        $path = __DIR__ . DS . 'locales';
        if ($locale)
            $path .= DS . $locale . '.php';
        return $path;
    }

    /**
     * Возвращает информацию о типах для всех доступных языков.
     * 
     * Где тип: countries (страны), languages (языки), currency (валюта).
     * 
     * @param string $type Тип.
     * @param string $locale Код локали.
     * @param null|string|array<int, string> $code Код записи.
     * 
     * @return null|string|array<int, string>
     */
    public function getTranslation(string $type, string $locale, null|string|array $code = null): null|string|array
    {
        $filename = $this->getLocalesPath($locale);
        if (!file_exists($filename)) {
            return null;
        }
        $data = require($filename);
        if (!isset($data[$type])) null;

        if (is_string($code)) {
            return $data[$type][$code] ?? null;
        } else
        if (is_array($code)) {
            $result = [];
            foreach ($code as $value) {
                if (isset($data[$type][$value]))
                    $result[$value] = $data[$type][$value];
            }
            return $result;
        }
        return $data;
    }

    /**
     * Возвращает код локали.
     * 
     * @return string
     */
    public function toString(): string
    {
        return $this->locale;
    }

    /**
     * Преобразует класс в строку магический метод).
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
