<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\MultiSite;

use Ge;
use Ge\Exception;
use Ge\Stdlib\Service;
use Ge\Config\Config;

/**
 * Мультисайт.
 * 
 * MultiSite - это служба приложения, доступ к которой можно получить через `Ge::$app->multiSite`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\MultiSite
 * @since 2.0
 */
class MultiSite extends Service
{
    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Унифицированный конфигуратор приложения.
     *
     * @var Config
     */
    public Config $unifiedConfig;

    /**
     * Карта доменов сайтов.
     * 
     * @var Domains|array $domains
     */
    public Domains|array $domains = [];

    /**
     * Сайты.
     * 
     * @var Sites|array $domains
     */
    public Sites|array $items = [];

    /**
     * Атрибуты текущего сайта.
     * 
     * @see MultiSite::initSite()
     * 
     * @var array|null
     */
    public ?array $site = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        Ge::configure($this, $config, $this->useUnifiedConfig);

        if (!isset($this->unifiedConfig)) {
            $this->unifiedConfig = Ge::$app->unifiedConfig;
        }
        // если карта доменов указана в конфигурации
        if (is_array($this->domains)) {
            $this->domains = Domains::createInstance($this->domains);
        }
        // если атрибуты сайтов указаны в конфигурации
        if (is_array($this->items)) {
            $this->items = Sites::createInstance($this->items);
        }

        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'multiSite';
    }

    /**
     * Инициализация текущего сайта.
     *
     * @return array|null Если сайт определён, то возвратит атрибуты сайта, иначе `null`.
     */
    protected function initSite(): ?array
    {
        /** @var array|null $site */
        return $this->site = $this->getByDomain();
    }

    /**
     * Инициализация темы указанного сайта.
     *
     * @param \Ge\Mvc\Application $app Веб-приложение.
     * @param array|null $site Атрибуты сайта.
     * 
     * @return \Ge\Theme\Theme Возвращает текущую тему.
     */
    protected function initSiteTheme(\Ge\Mvc\Application $app, ?array $site): \Ge\Theme\Theme
    {
        if ($site) {
            if (IS_BACKEND) {
                $app->backendTheme->default = $site['backendTheme'];
                $app->theme = $app->backendTheme;
            } else
            if (IS_FRONTEND) {
                $app->frontendTheme->default = $site['frontendTheme'];
                $app->theme = $app->frontendTheme;
            }
        } else {
            // т.к. инициализация тема еще не выполнена, а исключение делать надо
            // (исключение содержит установленную тему), то делаем её здесь
            $app->theme = IS_BACKEND ? $app->backendTheme : $app->frontendTheme;
        }
        // устанавливаем тему по умолчанию
        $app->theme->set();
        return $app->theme;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app): void
    {
        /** @var array|null $site */
        $site = $this->initSite();
        $this->initSiteTheme($app, $site);
        if ($site) {
            // если сайт не активен
            if (!$site['active'] && IS_FRONTEND) {
                throw new Exception\PageUnavailableException();
            }
        } else
            throw new Exception\PageUnavailableException();
    }

    /**
     * Проверяет, добавлены ли атрибуты сайт с указанным идентификатором.
     *
     * @param string $id Уникальный идентификатор сайта.
     * 
     * @return bool
     */
    public function hasSite(string $id): bool
    {
        return $this->items->has($id);
    }

    /**
     * Активирует или деактивирует работу указанного сайта.
     *
     * @param string $id Уникальный идентификатор сайта.
     * @param bool $active Активность сайта.
     * 
     * @return static
     */
    public function activate(string $id, bool $active): static
    {
        $this->items->activate($id, $active);
        return $this;
    }

    /**
     * Удаляет атрибуты сайта или сайтов по указанным идентификаторам.
     *
     * @param string|array $id Уникальный идентификатор(ы) сайта.
     * 
     * @return static
     */
    public function removeSite(string|array $id): static
    {
        $id = (array) $id;
        foreach ($id as $key) {
            $this->items->remove($key);
            $this->domains->removeBySiteId($key);
        }
        return $this;
    }

    /**
     * Добавляет атрибуты сайта.
     *
     * @param string $id Уникальный идентификатор сайта.
     * @param array|null $site Атрибуты сайта. Если значение `null`, то атрибуты 
     *     будут удалены.
     * 
     * @return static
     */
    public function setSite(string $id, ?array $site): static
    {
        $this->items->set($id, $site);
        $this->refreshDomains();
        return $this;
    }

    /**
     * Возвращает атрибуты сайта по указанному идентификатору.
     *
     * @param string $id Уникальный идентификатор сайта.
     * 
     * @return array|null Возвращает значение `null`, если атрибуты сайта не найдены.
     */
    public function getSite(string $id): ?array
    {
        return $this->items->get($id);
    }

    /**
     * Возвращает метаданные для указанного языка.
     * 
     * @param string $id Уникальный идентификатор сайта.
     * @param null|string $languageTag Тег языка, например: "ru-RU", "en-GB"... 
     *     Если значение `null`, то результатом будут значения указанные по умолчанию.
     *
     * @return array|null Возвращает значение `null`, если метаданные сайта отсутствуют.
     */
    public function getDefaultSiteMeta(string $id, ?string $languageTag = null): ?array
    {
        return $this->items->getDefaultMeta($id, $languageTag);
    }

    /**
     * Возвращает атрибуты сайта по указанному домену.
     *
     * @param string|null $domain Имя домена. Если значение `null`, то применяется 
     *     текущее имя домена (по умолчанию `null`).
     * 
     * @return array|null Возвращает значение `null`, если атрибуты сайта не найдены.
     * 
     */
    public function getByDomain(?string $domain = null): ?array
    {
        if ($domain === null) {
            $domain = $_SERVER['SERVER_NAME'];
        }

        /** @var string|null $siteId */
        $siteId = $this->domains->get($domain);
        return $siteId ? $this->items->get($siteId) : null;
    }

    /**
     * Обновляет карту доменов сайтов.
     *
     * @return static
     */
    public function refreshDomains(): static
    {
        $map = $this->items->makeDomainsMap();
        $this->domains->setAll($map);
        return $this;
    }

    /**
     * Подсчитывает количество сайтов.
     * 
     * @see Collection::getCount()
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->getCount();
    }

    /**
     * Подсчитывает количество сайтов.
     * 
     * @return int
     */
    public function getCount(): int
    {
        return $this->items->getCount();
    }

    /**
     * Удаляет атрибуты всех сайтов.
     * 
     * @see Collection::removeAll()
     * 
     * @return $this
     */
    public function clear(): static
    {
        $this->items->clear();
        $this->domains->clear();
        return $this;
    }

    /**
     * Сохраняет атрибуты сайтов.
     *
     * @return static
     */
    public function save(): static
    {
        $this->unifiedConfig->{$this->getObjectName()} = [
            'domains' => $this->domains->getAll(),
            'items'   => $this->items->getAll()
        ];
        $this->unifiedConfig->save();
        return $this;
    }
}
