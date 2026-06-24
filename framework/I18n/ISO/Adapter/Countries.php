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
 * Адаптера обозначений стран (ISO 3166).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO\Adapter
 * @since 2.0
 */
class Countries extends AbstractAdapter
{
    /**
     * @var string Название страны (английский язык).
     */
    public const KEY_NAME = 'name';

    /**
     * @var string Название страны (русский язык).
     */
    public const KEY_RUS_NAME = 'rusName';

    /**
     * @var string ISO 3166-1 2-буквенное сокращение.
     */
    public const KEY_ISO3166_1_ALPHA2 = 'iso3166_1_a2';

    /**
     * @var string ISO 3166-1 3-буквенное сокращение.
     */
    public const KEY_ISO3166_1_ALPHA3 = 'iso3166_1_a3';

    /**
     * @var string ISO 3166-1 числовой код.
     */
    public const KEY_ISO3166_1_NUMERIC = 'iso3166_1_n';

    /**
     * @var string ISO 3166-2 алфавитно-цифровой геокод.
     */
    public const KEY_ISO3166_2 = 'iso3166_2';

    /**
     * @var string ГОСТ 7.67 «Коды названий стран».
     */
    public const KEY_GOST7_67 = 'gost7_67';

    /**
     * @var string Флаг страны.
     */
    public const KEY_FLAG = 'flag';

    /**
     * {@inheritdoc}
     */
    protected string $standard = 'countries';

    /**
     * Возвращает список всех названий (на английском языке).
     * 
     * @return array<int, string>
     */
    public function allNames(): array
    {
        return $this->getSomeValues(self::KEY_NAME);
    }

    /**
     * Возвращает список всех названий (на русском языке).
     * 
     * @return array<int, string>
     */
    public function allRusNames(): array
    {
        return $this->getSomeValues(self::KEY_RUS_NAME);
    }

    /**
     * Возвращает найденные записи по указанному 2-буквенному коду ISO 3166-1.
     * 
     * @param string $alpha2 2-буквенный код.
     * 
     * @return array|null
     */
    public function ISO3166alpha2(string $alpha2): ?array
    {
        return $this->find($alpha2, self::KEY_ISO3166_1_ALPHA2, true);
    }

    /**
     * Возвращает найденные записи по указанному 3-буквенному коду ISO 3166-1.
     * 
     * @param string $alpha3 3-буквенный код.
     * 
     * @return array|null
     */
    public function ISO3166alpha3(string $alpha3): ?array
    {
        return $this->find($alpha3, self::KEY_ISO3166_1_ALPHA3, true);
    }

    /**
     * Возвращает найденные записи по указанному коду ISO 3166-1.
     * 
     * @param string $value код.
     * 
     * @return array|null
     */
    public function ISO3166_2(string $value): ?array
    {
        return $this->find($value, self::KEY_ISO3166_2, true);
    }

    /**
     * Возвращает найденные записи по указанному ключу ГОСТа 7.67.
     * 
     * @param mixed $value Значение для поиска.
     * @param string $key Ключ.
     * 
     * @return array|null
     */
    public function GOST7_67($value, string $key): ?array
    {
        return $this->findIn($value, self::KEY_GOST7_67, $key, true);
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
        return $this->GOST7_67($numeric, 'numeric');
    }

    /**
     * Возвращает уникальный код страны.
     * 
     * @param string $country Страна, например: 'RU', 'BE', 'EN'... Если значение '', 
     *     код страны не будет добавлен в результат (по умолчанию '').
     * 
     * @return string Возвращает значение '', если не удалось определить код.
     */
    public function getCode(string $country): ?string
    {
        /** @var null|array $info */
        $info = $this->get($country, []);
        return $info[self::KEY_GOST7_67]['numeric'] ?? '';
    }
}
