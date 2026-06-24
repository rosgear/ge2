<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n\ISO\Adapter;

/**
 * Адаптера обозначений языка (ISO 639).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO\Adapter
 * @since 2.0
 */
class Languages extends AbstractAdapter
{
    /**
     * @var string ISO 639-1 (1998) 2-буквенное сокращение.
     */
    public const  KEY_ISO639_ALPHA = 'iso639_1';

    /**
     * @var string ISO 639-2 (2002) 3-буквеннон сокращенин.
     */
    public const  KEY_ISO639_ALPHA2 = 'iso639_2';

    /**
     * @var string ISO 639-3 (2007) 3-буквеннон сокращенин.
     */
    public const  KEY_ISO639_ALPHA3 = 'iso639_3';

    /**
     * @var string Направления текста.
     */
    public const  KEY_DIRECTION = 'direction';

    /**
     * @var string Название языка (английский язык).
     */
    public const  KEY_NAME = 'name';

    /**
     * @var string Название языка (родной язык).
     */
    public const  KEY_NATIVE_NAME = 'nativeName';

    /**
     * @var string Название языка (русский язык).
     */
    public const  KEY_RUS_NAME = 'rusName';

    /**
     * @var string ГОСТ 7.75-97 «Коды наименований языков».
     */
    public const  KEY_GOST7_75 = 'gost7_75';

    /**
     * {@inheritdoc}
     */
    protected string $standard = 'languages';

    /**
     * Возвращает список всех имен (на английском языке).
     * 
     * @return array<string, array>
     */
    public function allNames(): array
    {
        return $this->getSomeValues(self::KEY_NAME);
    }

    /**
     * Возвращает список всех названий (на русском языке).
     * 
     * @return array<string, array>
     */
    public function allRusNames(): array
    {
        return $this->getSomeValues(self::KEY_RUS_NAME);
    }

    /**
     * Возвращает список всех названий (на родном языке).
     * 
     * @return array<string, array>
     */
    public function allNativeNames(): array
    {
        return $this->getSomeValues(self::KEY_NATIVE_NAME);
    }

    /**
     * Возвращает найденные записи по указанному коду ISO 639-1 (1998).
     * 
     * @param string $alpha1 2-буквенный код.
     * 
     * @return array|null
     */
    public function ISO639alpha1(string $alpha1): ?array
    {
        return $this->find($alpha1, self::KEY_ISO639_ALPHA, true);
    }

    /**
     * Возвращает найденные записи по указанному коду ISO 639-2 (2002).
     * 
     * @param string $alpha2 3-буквенный код.
     * 
     * @return array|null
     */
    public function ISO639alpha2(string $alpha2): ?array
    {
        return $this->find($alpha2, self::KEY_ISO639_ALPHA2, true);
    }

    /**
     * Возвращает найденные записи по указанному коду 639-3 (2007).
     * 
     * @param string $alpha3 3-буквенный код.
     * 
     * @return array|null
     */
    public function ISO639alpha3(string $alpha3): ?array
    {
        return $this->find($alpha3, self::KEY_ISO639_ALPHA3, true);
    }

    /**
     * Возвращает найденные записи по указанному ключу ГОСТа 7.75-97.
     * 
     * @param mixed $value Значение для поиска.
     * @param string $key Ключ.
     * 
     * @return array|null
     */
    public function GOST7_75(mixed $value, string $key): ?array
    {
        return $this->findIn($value, self::KEY_GOST7_75, $key, true);
    }

    /**
     * Возвращает все записи по указанному числовому коду.
     * 
     * @param string $numeric Числовой код.
     * 
     * @return array|null
     */
    public function numeric(string $numeric): ?array
    {
        return $this->GOST7_75($numeric, 'numeric');
    }

    /**
     * Возвращает все записи по указанному направлению текста.
     * 
     * @param string $direction Направление текста ("rtl", "ltr", "ltr-ttb").
     * 
     * @return array|null
     */
    public function direction(string $direction): ?array
    {
        return $this->find($direction, self::KEY_DIRECTION, true);
    }

    /**
     * Возвращает уникальный код языка.
     * 
     * @param string $language Язык, например: 'ru', 'be', 'en'...
     * 
     * @return string Возвращает значение '', если не удалось определить код.
     */
    public function getCode(string $language): string
    {
        /** @var null|array $info */
        $info = $this->get($language, []);
        return $info[self::KEY_GOST7_75]['numeric'] ?? '';
    }
}
