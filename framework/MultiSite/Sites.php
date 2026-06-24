<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\MultiSite;

use Ge\Stdlib\Collection;

/**
 * Класс коллекции сайтов.
 * 
 * В коллекции сайты представлены в виде пар "ключ - значение". Где ключ - уникальный 
 * идентификатор сайта, а значение - атрибуты сайта.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\MultiSite
 * @since 2.0
 */
class Sites extends Collection
{
    /**
     * Создаёт коллекцию (карту) доменов из коллекции сайтов или из указанных атрибутов 
     * сайта.
     *
     * @param array|null $site Атрибуты сайта (один из которых 'domain').
     * 
     * @return array Коллекция (карта) доменов в виде пар "ключ - значение".
     */
    public function makeDomainsMap(?array $site = null): array
    {
        $map = [];

        if ($site) {
            if ($site['domain']) {
                $siteDomains = explode(',', $site['domain'] );
                foreach ($siteDomains as $domain) {
                    $map[$domain] = $site['id'];
                }
            }
            return $map;
        }

        foreach ($this->container as $id => $item) {
            if (!empty($item['domain'])) {
                $itemDomains = explode(',', $item['domain']);
                foreach ($itemDomains as $domain) {
                    $map[$domain] = $id;
                }
            }
        }
        return $map;
    }

    /**
     * Возвращает шаблон атрибутов сайта.
     *
     * @param array $item Атрибуты элемента, которые преобразуются в атрибуты сайта.
     * 
     * @return array
     */
    public function getPattern(array $item): array
    {
        return array_merge([
            'id'            => '',
            'name'          => '',
            'domain'        => '',
            'backendTheme'  => '',
            'frontendTheme' => '',
            'textPowered'   => '',
            'titlePattern'  => '%s - ...',
            'title'         => '',
            'author'        => '',
            'keywords'      => '',
            'description'   => '',
            'image'         => '',
            'robots'        => '',
            'meta'          => '',
            'active'        => true,
            'useOpenGraph'     => false,
            'useTwitterCard'   => false,
            'useSchemaOrg'     => false,
            'useVKSchema'      => false,
            'useMetaGenerator' => false,
            'useHeaderPowered' => false,
        ], $item);
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $key, mixed $value): static
    {
        if ($value === null) {
            if (isset($this->container[$key]))
                unset($this->container[$key]);
        } else 
            $this->container[$key] = $this->getPattern($value);
        return $this;
    }

    /**
     * Устанавливает сайту активность.
     *
     * @param string $id Уникальный идентификатор сайта.
     * @param bool $active Если значение `true`, установит активность сайту.
     * 
     * @return static
     */
    public function activate(string $id, bool $active): static
    {
        if (isset($this->container[$id])) {
            $this->container[$id]['active'] = $active;
        }
        return $this;
    }

    /**
     * Получает следующий уникальный идентификатор данных сайта.
     *
     * @return int
     */
    public function getNextDataId(): int
    {
        $id = [];
        foreach ($this->container as $key => $params) {
            if (isset($params['dataId'])) {
                $id[] = (int) $params['dataId'];
            }
        }
        return $id ? (max($id) + 1) : 1;
    }

    /**
     * Возвращает метаданные сайта для указанного языка.
     * 
     * @param string $id Уникальный идентификатор сайта.
     * @param null|string $languageTag Тег языка, например: "ru-RU", "en-GB"... 
     *     Если значение `null`, то результатом будут значения указанные по умолчанию.
     *
     * @return array|null Возвращает значение `null`, если метаданные сайта отсутствуют.
     */
    public function getDefaultMeta(string $id, ?string $languageTag = null): ?array
    {
        /** @var array|null $site */
        $site = $this->get($id);
        if ($site === null) return null;

        $default = [
            'titlePattern' => $site['titlePattern'],
            'title'        => $site['title'],
            'author'       => $site['author'],
            'keywords'     => $site['keywords'],
            'description'  => $site['description'],
            'image'        => $site['image'],
            'robots'       => $site['robots']
        ];
        if ($languageTag === null)
            return $default;
        else
            return $site['meta'][$languageTag] ?? $default;
    }
}
