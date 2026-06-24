<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ExtensionManager;

use Ge;
use Ge\Db\ActiveRecord;
use Ge\ModuleManager\BaseManager;

/**
 * Менеджер расширений модулей предоставляет возможность создавать и обращаться к экземплярам 
 * классов расширений.
 * 
 * ExtensionManager - это служба приложения, доступ к которой можно получить через 
 * `Ge::$app->extensions`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ExtensionManager
 * @since 2.0
 */
class ExtensionManager extends BaseManager
{
    /**
     * {@inheritdoc}
     */
    public string $callableClassName = 'Extension';

    /**
     * {@inheritdoc}
     */
    public function getVersionPattern($params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'name'         => '', // название расширения
            'description'  => '', // описание расширения
            'version'      => '', // номер версии
            'versionDate'  => '', // дата версии
            'author'       => '', // имя или email автора
            'authorUrl'    => '', // URL-адрес страницы автора
            'email'        => '', // E-mail автора
            'url'          => '', // URL-адрес страницы расширения
            'license'      => '', // вид лицензии
            'licenseUrl'   => '' // URL-адрес текста лицензии
        ], $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallPattern($params): array
    {
        if (!is_array($params)) {
            $params = [];
        }
        return array_merge([
            'id'           => '', // идентификатор расширения
            'name'         => '', // имя расширения
            'description'  => '', // описание расширения
            'namespace'    => '', // пространство имён расширения
            'path'         => '', // каталог расширения
            'route'        => '', // маршрут
            'shortcodes'   => [], // подключаемые шорткоды
            'locales'      => [], // поддерживаемые локализации
            'permissions'  => [], // разрешения (права доступа)
            'required'     => []  // требования к версии расширения
        ], $params);
    }

    /**
     * Возвращает репозиторий расширений модулей.
     *
     * @return ExtensionRepository
     */
    public function getRepository(): ExtensionRepository
    {
        if (!isset($this->repository)) {
            $this->repository = new ExtensionRepository($this);
        }
        return $this->repository;
    }

    /**
     * Возвращает реестр установленных расширений модулей.
     * 
     * @return ExtensionRegistry
     */
    public function getRegistry(): ExtensionRegistry
    {
        if (!isset($this->registry)) {
            $this->registry = new ExtensionRegistry(Ge::alias('@config', DS . '.extensions.php'), true, $this);
        }
        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(array $params, $include, string $name = 'extension'): ?array
    {
        return parent::getInfo($params, $include, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function selectOne(string|int $id, bool $assoc = false): ActiveRecord|array|null
    {
        $extension = new Model\Extension();
        if (is_numeric($id))
            $extension = $extension->selectOne(['id' => $id]);
        else
            $extension = $extension->selectOne(['extension_id' => $id]);
        if ($extension) {
            return $assoc ? $extension->getAttributes() : $extension;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function selectAll(?string $key = null, string|array $where = ''): array
    {
        $extension = new Model\Extension();
        return $extension->fetchAll($key, $extension->maskedAttributes(), $where ?: null);
    }

    /**
     * {@inheritdoc}
     */
    public function selectName(int $id): ?array
    {
        return (new Model\ExtensionLocale())->fetchLocale($id);
    }

    /**
     * {@inheritdoc}
     */
    public function selectNames(?string $attribute = null, ?int $languageCode = null): ?array
    {
        return (new Model\ExtensionLocale())->fetchNames($attribute, $languageCode);
    }

    /**
     * Вызывает триггер указанного расширения модуля.
     * 
     * Если расширение доступно и имеет событие, то оно будет обработано им.
     * 
     * @param string|null $id Идентификатор установленного расширения, например, 'rg.be.foobar'.
     * @param string $event Название события.
     * @param array $args Параметры передаваемые событием.
     * 
     * @return void
     */
    public function doEvent(string $id, string $event, array $args = [])
    {
        /** @var array|null $extensionParams */
        $extensionParams = $this->getRegistry()->getAt($id);
        // если модуль доступен
        if ($extensionParams && $extensionParams['enabled']) {
            /** @var null|\Ge\Mvc\Extension\BaseExtension $extension */
            $extension = $this->get($id);
            if ($extension) {
                $extension->trigger($event, $args);
            }
        }
    }

    /**
     * Возвращает маршруты доступных расширений для указанного модуля.
     * 
     * Результат имеет вид:
     * ```php
     * [
     *     'countries' => [
     *         'id'        => 'rg.references.countries',
     *         'namespace' => 'Ge\Extension\References\Countries',
     *         'path       => '/rg/extension-references/countries'
     *     ],
     *     // ...
     * ]
     * ```
     * или
     * ```php
     * [
     *     1 => [
     *         'countries' => [
     *             'id'        => 'rg.references.countries',
     *             'namespace' => 'Ge\Extension\References\Countries',
     *             'path       => '/rg/extension-references/countries'
     *         ],
     *         // ...
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @param null|int $moduleId Идентификатор записи модуля в базе данных. Модуль 
     *     для которого собираются маршруты. Если значение `null`, то для всех 
     *     установленных модулей (по умолчанию `null`).
     * 
     * @return array
     */
    public function collectRoutes(?int $moduleId = null): array
    {
        $result = [];
        if ($moduleId) {
            $rows = $this->selectAll(null, ['module_id' => $moduleId]);
            if ($rows) {
                foreach ($rows as $row) {
                    if ((bool) $row['enabled']) {
                        $result[$row['route']] = [
                            'id'        => $row['extension_id'],
                            'namespace' => $row['namespace'],
                            'path'      => $row['path']
                        ];
                    }
                }
            }
        } else  {
            $rows = $this->selectAll();
            if ($rows) {
                foreach ($rows as $row) {
                    $moduleId = $row['moduleId'] ?? null;
                    $enabled  = (bool) $row['enabled'];
                    if ($moduleId && $enabled) {
                        if (!isset($result[$moduleId])) {
                            $result[$moduleId] = [];
                        }
                        $result[$moduleId][$row['route']] = [
                            'id'        => $row['extensionId'],
                            'namespace' => $row['namespace'],
                            'path'      => $row['path']
                        ];
                    }
                }
            }
        }
        return $result;
    }
}
