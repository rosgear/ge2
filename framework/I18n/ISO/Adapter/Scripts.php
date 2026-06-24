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
 * Адаптера обозначений названий письменностей (ISO 15924).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n\ISO\Adapter
 * @since 2.0
 */
class Scripts extends AbstractAdapter
{
    /**
     * @var string Псевдоним.
     */
    public const KEY_ALIAS = 'alias';

    /**
     * @var string Имя (русский язык).
     */
    public const KEY_RUS_NAME = 'rusName';

    /**
     * @var string Числовой код.
     */
    public const KEY_NUMERIC = 'numeric';

    /**
     * @var string Версия.
     */
    public const KEY_VERSION = 'version';

    /**
     * @var string Количество символов.
     */
    public const KEY_SYMBOLS = 'symbols';

    /**
     * {@inheritdoc}
     */
    protected string $standard = 'scripts';

    /**
     * Возвращает все псевдонимы письменностей.
     * 
     * @return array<string, array>
     */
    public function allAliases(): array
    {
        return $this->getSomeValues(self::KEY_ALIAS);
    }

    /**
     * Возвращает все названия письменностей (на русском языке).
     * 
     * @return array<string, array>
     */
    public function allRusNames(): array
    {
        return $this->getSomeValues(self::KEY_ALIAS);
    }

    /**
     * Возвращает найденные записи по указанному псевдониму.
     * 
     * @param string $alias Псевдоним.
     * 
     * @return array|null
     */
    public function alias(string $alias): ?array
    {
        return $this->find($alias, self::KEY_ALIAS, true);
    }

    /**
     * Возвращает найденные записи по числовому коду.
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
     * Возвращает найденные записи по номеру версии.
     * 
     * @param string $version Номер версии.
     * 
     * @return array|null
     */
    public function version(string $version): ?array
    {
        return $this->find($version, self::KEY_VERSION, true);
    }

    /**
     * Возвращает уникальный код письменности.
     * 
     * @param string $script Письменность, например: 'Arab', 'Grek', 'Cyrl'...
     * 
     * @return string Возвращает значение '', если не удалось определить код.
     */
    public function getCode(string $script): string
    {
        /** @var null|array $info */
        $info = $this->get($script, []);
        return $info[self::KEY_NUMERIC] ?? '';
    }
}
