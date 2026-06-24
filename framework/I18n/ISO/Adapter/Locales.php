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
 * Адаптера обозначений локалей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO\Adapter
 * @since 2.0
 */
class Locales extends AbstractAdapter
{
    /**
     * @var string Название языка и территории (английский язык).
     */
    public const KEY_NAME = 'name';

    /**
     * @var string Название языка и территории (родной язык).
     */
    public const KEY_NATIVE_NAME = 'nativeName';

    /**
     * @var string Название языка и территории (русский язык).
     */
    public const KEY_RUS_NAME = 'rusName';

    /**
     * @var string Код языка (ISO 639-1).
     */
    public const KEY_LANGUAGE = 'language';

    /**
     * @var string Код территории (ISO 3166-1).
     */
    public const KEY_REGION = 'region';

    /**
     * @var string Обозначение письменности (ISO 15924).
     */
    public const KEY_SCRIPT = 'script';

    /**
     * @var string Числовой код.
     */
    public const KEY_NUMERIC = 'numeric';

    /**
     * {@inheritdoc}
     */
    protected string $standard = 'locales';

    /**
     * Возвращает все названия языка и территорий (на английском языке).
     * 
     * @return array<string, array>
     */
    public function allNames(): array
    {
        return $this->getSomeValues(self::KEY_NAME);
    }

    /**
     * Возвращает все названия языка и территорий (на русском языке).
     * 
     * @return array<string, array>
     */
    public function allRusNames(): array
    {
        return $this->getSomeValues(self::KEY_RUS_NAME);
    }

    /**
     *Возвращает все названия языка и территорий (на родном языке).
     * 
     * @return array<string, array>
     */
    public function allNativeNames(): array
    {
        return $this->getSomeValues(self::KEY_NATIVE_NAME);
    }

    /**
     * Возвращает все идентификаторы (LCID Windows).
     * 
     * @return array<int, string>
     */
    public function allLCID(): array
    {
        $result = [];
        foreach ($this->map as $index => $item) {
            if ($item['windows'] && $item[self::KEY_NUMERIC]) {
                $item['code'] = $index;
                $result[] = $item[self::KEY_NUMERIC];
            }
        }
        return $result;
    }

    /**
     * Возвращает все локали не имеющие территорию.
     * 
     * @return array<string, array>
     */
    public function allWithoutRegion(): array
    {
        $result = [];
        foreach ($this->map as $locale => $info) {
            if (empty($info[self::KEY_REGION])) {
                $info['code'] = $locale;
                $result[] = $info;
            }
        }
        return $result;
    } 

    /**
     * Возвращает найденные записи по указанному коду языка (ISO 639-1).
     * 
     * @param string $language Кода языка (ISO 639-1).
     * 
     * @return array|null
     */
    public function language(string $language): ?array
    {
        return $this->find($language, self::KEY_LANGUAGE, true);
    }

    /**
     * Возвращает найденные записи по указанному коду страны (ISO 3166-1).
     * 
     * @param string $region Код страны (ISO 3166-1).
     * 
     * @return array|null
     */
    public function region(string $region): ?array
    {
        return $this->find($region, self::KEY_REGION, true);
    }

    /**
     * Возвращает найденные записи по указанному обозначению письменности (ISO 15924).
     * 
     * @param string $script Обозначению письменности (ISO 15924).
     * 
     * @return array|null
     */
    public function script(string $script): ?array
    {
        return $this->find($script, self::KEY_SCRIPT, true);
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
       return $this->find($numeric, self::KEY_NUMERIC, true);
    }

    /**
     * Возвращает локаль по указанному идентификатору.
     * 
     * @param string $id Идентификатор локали (LCID Windows).
     * 
     * @return array|null
     */
    public function lcid(string $id): ?array
    {
       $row = $this->find($id, self::KEY_NUMERIC, false);
       return $row !== null && $row['windows'] === true ? $row : null;
    }
}
