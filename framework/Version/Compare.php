<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Version;

use Ge;
use Ge\Stdlib\BaseObject;

/**
 * Сравнение версий.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Version
 * @since 2.0
 */
class Compare extends BaseObject
{
    /**
     * Версия.
     * 
     * @var Version|null
     */
    public ?Version $version = null;

    /**
     * Версия редакции.
     * 
     * @var Edition|null
     */
    public ?Edition $edition = null;

    /**
     * Виды сравнений в последнем сопоставлении версий.
     * 
     * @see Compare::with()
     * 
     * @var array
     */
    public array $compareTypes = [];

    /**
     * Параметры последнего не успешного сравнения версии.
     * 
     * @see Compare::with()
     * 
     * @return array
     */
    protected array $lastCompare = [];

    /**
     * Шаблон сообщения, полученный при не успешном сравнения версии.
     * 
     * @var array<string, array>
     */
    protected array $messageTemplate = [];

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        if (!$this->edition !== null) {
            if (method_exists($this->version, 'getEdition')) {
                $this->edition = $this->version->getEdition();
            }
        }
    }

    /**
     * Возвращает параметры последнего не успешного сравнения версии.
     * 
     * @return array
     */
    public function getLastCompare(): array
    {
        return $this->lastCompare;
    }

    /**
     * Возвращает сообщение, полученное в последнем не успешном сравнении версии.
     * 
     * @return string
     */
    public function getMessage(): string
    {
        if ($this->messageTemplate) {
            reset($this->messageTemplate);
            $message = current($this->messageTemplate);
            return Ge::t('app', $message[0], $message[1]);
        } else
            return '';
    }

    /**
     * Возвращает шаблон сообщений, полученный при не успешном сравнения версии.
     * 
     * @return array
     */
    public function getMessageTemplate(): array
    {
        return $this->messageTemplate;
    }

    /**
     * Устанавливает шаблон сообщения для отправителя.
     * 
     * @param string $message Шаблон сообщения.
     * @param array $args Агрументы подставляемые в шаблон сообщения.
     * @param string $sender Отправитель, например: 'app', 'edition'.
     * 
     * @return void
     */
    public function setMessageTemplate(string $message, array $args, string $sender): void
    {
        $this->messageTemplate[$sender] = [$message, $args];
    }

    /**
     * Удаляет шаблоны сообщений.
     * 
     * @param string|null $sender Отправитель, если значение `null`, то будут удалены 
     *     все сообщения например: 'app', 'edition' (по умолчанию `null`).
     * 
     * @return void
     */
    public function clearMessage(?string $sender = null): void
    {
        if ($sender)
            unset($this->messageTemplate[$sender]);
        else
            $this->messageTemplate = [];
    }

    /**
     * Добавляет значение указанного аргумента в шаблон сообщения.
     * 
     * Например, если было сообщение 'Вам необходимо приложение: Foo' от отправителя 'app',
     * а указано новое значение аргумента `$arg = 'Bar'`, то сообщение будет иметь вид
     * 'Вам необходимо приложение: Foo, Bar'.
     * 
     * @param string $sender Отправитель, например: 'app', 'edition'.
     * @param string $arg Значение аргумента подставляемого в шаблон сообщения.
     * @param string $separator Разделитель значений аргументов (по умолчанию ',').
     * 
     * @return bool
     */
    public function addMessageTemplateArg(string $sender, string $arg, string $separator = ', '): bool
    {
        if (isset($this->messageTemplate[$sender])) {
            $args = &$this->messageTemplate[$sender][1];
            $args[1] = $args[1] . $separator . $arg;
            return true;
        }
        return false;
    }

    /**
     * Сравнивает с названием и версией приложения.
     * 
     * @param string $code $name Название приложения.
     * @param null|string $version Номер версии приложения (по умолчанию `null`).
     * 
     * @return bool
     */
    public function withApp(string $code, ?string $version = null): bool
    {
        if ($code) {
            if ($this->version) {
                if ($code === $this->version->code) {
                    if (version_compare($version, $this->version->number, '<='))
                        return true;
                    else
                        $this->setMessageTemplate(
                            'Уou need a {0} version of at least {1}, current version: {2}',
                            [Ge::t('app', 'Application'), $code . ' ' . $version, $code . ' ' . $this->version->number],
                            'app'
                        );
                } else {
                    // т.к. может быть несколько сравнений с приложением, то добавляем ещё
                    // код к текущей ошибке
                    if (!$this->addMessageTemplateArg('app', $code)) {
                        $this->setMessageTemplate('Used for {0} with code {1}', [Ge::t('app', 'Application'), $code], 'app');
                    }
                }
            }
        } else
            $this->setMessageTemplate('Parameters for {0} comparison are incorrect', [Ge::t('app', 'Application')], 'app');
        return false;
    }

    /**
     * Сравнивает с кодом и версией редакции приложения.
     * 
     * @param string $code Код редакции приложения.
     * @param null|string $version Номер версии редакции приложения (по умолчанию `null`).
     * 
     * @return bool
     */
    public function withEdition(string $code, ?string $version = null): bool
    {
        if ($code) {
            if ($this->edition) {
                if ($code === $this->edition->code) {
                    if (version_compare($version, $this->edition->number, '<='))
                        return true;
                    else
                        $this->setMessageTemplate(
                            'Уou need a {0} version of at least {1}, current version: {2}',
                            [Ge::t('app', 'Application'), $code . ' ' . $version, $code . ' ' . $this->version->number],
                            'app'
                        );
                } else {
                    // т.к. может быть несколько сравнений с редакцией, то добавляем ещё
                    // код к текущей ошибке
                    if (!$this->addMessageTemplateArg('edition', $code)) {
                        $this->setMessageTemplate('Used for {0} with code {1}', [Ge::t('app', 'Edition'), $code], 'edition');
                    }
                }
            }
        } else
            $this->setMessageTemplate(
                'Parameters for {0} comparison are incorrect', 
                [Ge::t('app', 'Edition')], 
                'edition'
            );
        return false;
    }

    /**
     * Проверят загружен ли указанный модуль PHP. 
     * 
     * @param string $name Имя модуля PHP.
     * 
     * @return bool
     */
    public function withPhpExt(string $name): bool
    {
        $extensions = explode(',', $name);
        foreach ($extensions as $extension) {
            if (!extension_loaded($name)) {
                $this->setMessageTemplate('Уou need a {0} for {1}', [$name, 'PHP'], 'phpExt');
                return false;
            }
        }
        return true;
    }

    /**
     * Сравнивает с версией PHP.
     * 
     * @param string $version Номер версии PHP.
     * 
     * @return bool
     */
    public function withPhp(string $version): bool
    {
        if (version_compare(PHP_VERSION, $version) >= 0)
            return true;
        else
            $this->setMessageTemplate(
                'Уou need a {0} version of at least {1}, current version: {2}',
                ['PHP', $version, PHP_VERSION],
                'php'
            );
        return false;
    }

    /**
     * Сравнивает с идентификатор и версией расширения модуля.
     * 
     * @param string $id Идентификатор расширения модуля.
     * @param null|string $version Номер версии расширения модуля (по умолчанию `null`).
     * @param null|string $name Возвращаемое имя расширения модуля после сравнения. 
     *     Если имя расширения невозможно получить, результатом будет $id (по умолчанию `null`).
     * 
     * @return bool
     */
    public function withExtension(string $id, ?string $version = null, ?string &$name = null): bool
    {
        static $registry;

        $name = $id;
        if ($registry === null) {
            /** @var \Ge\ExtensionManager\ExtensionRegistry $registry */
            $registry = Ge::$app->extensions->getRegistry();
        }
        $extension = $registry->getAt($id);
        if ($extension) {
            if (!empty($extension['name'])) {
                $name = $extension['name'] . ' (' . $id . ')';
            }
            $eversion = $extension['version'] ?? '';
            if ($version && $eversion) {
                if (version_compare($version, $eversion, '<='))
                    return true;
                else {
                    $this->setMessageTemplate(
                        'Уou need a {0} for {1} version of at least {2}, current version: {3}',
                        [Ge::t('app', 'Extension'), $name, $version, $eversion],
                        'extension'
                    );
                }
            } else
                return true;
        } else
            $this->setMessageTemplate('Used for {0} with id {1}', [Ge::t('app', 'Extension'), $id], 'extension');
        return false;
    }

    /**
     * Сравнивает с идентификатор и версией модуля.
     * 
     * @param string $id Идентификатор модуля.
     * @param null|string $version Номер версии модуля.
     * @param null|string $name Возвращаемое имя модуля после сравнения. Если 
     *     имя модуля невозможно получить, результатом будет $id (по умолчанию `null`).
     * 
     * @return bool
     */
    public function withModule(string $id, ?string $version = null, ?string &$name = null): bool
    {
        static $registry;

        $name = $id;
        if ($registry === null) {
            /** @var \Ge\ModuleManager\ModuleRegistry $registry */
            $registry = Ge::$app->modules->getRegistry();
        }
        $module = $registry->getAt($id);
        if ($module) {
            if (!empty($module['name'])) {
                $name = $module['name'] . ' (' . $id . ')';
            }
            $mversion = $module['version'] ?? '';
            if ($version && $mversion) {
                if (version_compare($version, $mversion, '<='))
                    return true;
                else {
                    $this->setMessageTemplate(
                        'Уou need a {0} for {1} version of at least {2}, current version: {3}',
                        [Ge::t('app', 'Module'), $name, $version, $mversion],
                        'module'
                    );
                }
            } else
                return true;
        } else
            $this->setMessageTemplate('Used for {0} with id {1}', [Ge::t('app', 'Module'), $id], 'module');
        return false;
    }

    /**
     * Сравнивает с идентификатор и версией виджета.
     * 
     * @param string $id Идентификатор виджета.
     * @param null|string $version Номер версии виджета.
     * @param null|string $name Возвращаемое имя виджета после сравнения. Если 
     *     имя виджета невозможно получить, результатом будет $id (по умолчанию `null`).
     * 
     * @return bool
     */
    public function withWidget(string $id, ?string $version = null, ?string &$name = null): bool
    {
        static $registry;

        $name = $id;
        if ($registry === null) {
            /** @var \Ge\WidgetManager\WidgetRegistry $registry */
            $registry = Ge::$app->modules->getRegistry();
        }
        $widget = $registry->getAt($id);
        if ($widget) {
            if (!empty($widget['name'])) {
                $name = $widget['name'] . ' (' . $id . ')';
            }
            $wversion = $widget['version'] ?? '';
            if ($version && $wversion) {
                if (version_compare($version, $wversion, '<='))
                    return true;
                else {
                    $this->setMessageTemplate(
                        'Уou need a {0} for {1} version of at least {2}, current version: {3}',
                        [Ge::t('app', 'Widget'), $name, $version, $wversion],
                        'widget'
                    );
                }
            } else
                return true;
        } else
            $this->setMessageTemplate('Used for {0} with id {1}', [Ge::t('app', 'Widget'), $id], 'widget');
        return false;
    }

    /**
     * Проверяет версию по указанным параметрам сравнения.
     * 
     * Пример:
     * ```php
     * with([
     *     [
     *         'php',
     *         'version' => '8.2'
     *     ],
     *     [
     *         'phpExt',
     *         'name' => 'xml,tokenizer,sockets'
     *     ],
     *     [
     *         'app',
     *         'version' => '1.0',
     *         'code'    => 'RG CMS'
     *     ],
     *     [
     *         'edition',
     *         'version' => '1.0',
     *         'code'    => 'RG CMS:STD' // Standart
     *     ],
     *     [
     *         'module',
     *         'id'      => 'rg.be.mp',
     *         'version' => '2.0'
     *     ],
     *     [
     *         'extension',
     *         'id'      => 'rg.be.mp.catalog',
     *         'version' => '1.0'
     *     ],
     *     [
     *         'widget',
     *         'id'      => 'rg.wd.articles',
     *         'version' => '1.0'
     *     ]
     * ]);
     * ```
     * 
     * @param array $params Параметры сравнения.
     * 
     * @return bool Возвращает значение `false`, если одна из групп параметров 
     *     сравнения не проходит проверку.
     */
    public function with(array $params): bool
    {
        $this->compareTypes = [];
        $this->clearMessage();
        if (empty($params)) return true;

        $types = [];
        // определенрие групп для проверки
        foreach ($params as $compare) {
            if (isset($compare[0])) {
                $types[$compare[0]] = false;
            }
        }
    
        $this->compareTypes = array_keys($types);

        foreach ($params as $compare) {
            $type = $compare[0] ?? '';
            if (empty($type)) continue;

            // проверка версии PHP
            if ($type === 'php') {
                if ($this->withPhp($compare['version'] ?? ''))
                    $types[$type] = true;
                else
                    $types[$type] = $compare;
            } else
            // проверка расширения PHP
            if ($type === 'phpExt') {
                if ($this->withPhpExt($compare['name'] ?? ''))
                    $types[$type] = true;
                else
                    $types[$type] = $compare;
            } else
            // проверка версии приложения
            if ($type === 'app') {
                // проверка для нескольких типов 'app', если хотя бы один `true`, то
                // остальные нет смысла проверять
                $isSuccess = isset($types[$type]) ? $types[$type] === true : false;
                if (!$isSuccess) {
                    if ($this->withApp($compare['code'] ?? '',  $compare['version'] ?? ''))
                        $types[$type] = true;
                    else
                        $types[$type] = $compare;
                }
            } else
            // проверка версии редакции
            if ($type === 'edition') {
                // проверка для нескольких типов 'edition', если хотя бы один `true`, то
                // остальные нет смысла проверять
                $isSuccess = isset($types[$type]) ? $types[$type] === true : false;
                if (!$isSuccess) {
                    if ($this->withEdition($compare['code'] ?? '',  $compare['version'] ?? ''))
                        $types[$type] = true;
                    else
                        $types[$type] = $compare;
                }
            } else
            // проверка версии модуля
            if ($type === 'module') {
                if ($this->withModule($compare['id'] ?? '',  $compare['version'] ?? ''))
                    $types[$type] = true;
                else
                    $types[$type] = $compare;
            } else
            // проверка версии расширения
            if ($type === 'extension') {
                if ($this->withExtension($compare['id'] ?? '',  $compare['version'] ?? ''))
                    $types[$type] = true;
                else
                    $types[$type] = $compare;
            } else
            // проверка версии виджета
            if ($type === 'widget') {
                if ($this->withWidget($compare['id'] ?? '',  $compare['version'] ?? ''))
                    $types[$type] = true;
                else
                    $types[$type] = $compare;
            // проверка пользовательских параметров
            } else {
                $compareName = 'with' . $type;
                if (method_exists($this, $compareName)) {
                    if ($this->$compareName($compare))
                        $types[$type] = true;
                    else
                        $types[$type] = $compare;
                }
            }
        }

        // проверяем типы, которых может быть несколько
        if (isset($types['app']) && $types['app'] === true) {
            $this->clearMessage('app');
        }
        if (isset($types['edition']) && $types['edition'] === true) {
            $this->clearMessage('edition');
        }

        foreach ($types as $type => $value) {
            if ($value !== true) {
                $this->lastCompare = $value;
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет требования к текущей версии.
     * 
     * Текущая версия {@see Compare::$version}.
     * Требования, которые не удолетворяют текущую версию, помечаются шаблоном `$warningPattern`.
     * 
     * @param array $params Требования (параметры сравнения) к текущей версии {@see Compare::with()}.
     * @param string $paramGlue Разделитель параметров требования, например: 'FooBar 1.0' (по умолчанию ' ').
     * @param string $glue Разделитель групп параметров требования, например: 'Foo 1.0, Bar 2.0' (по умолчанию ', ').
     * @param string $warningPattern Шаблон (строка), указывающий на параметр, который не соответствует требованиям.
     *     Например: 'FooBar 1.0 (-)' (по умолчанию '(-)').
     * 
     * @return array
     */
    public function requirement(array $params, string $paramGlue = ' ', string $glue = ', ', string $warningPattern = ' (-)'): array
    {
        if (empty($params)) return [];

        $types = [];
        // определенрие групп для проверки
        foreach ($params as $compare) {
            if (isset($compare[0])) {
                $types[$compare[0]] = [];
            }
        }
    
        foreach ($params as $compare) {
            $type = $compare[0] ?? '';
            if (empty($type)) continue;

            // проверка версии PHP
            if ($type === 'php') {
                $version = $compare['version'] ?? '';
                $types[$type][] = $version
                    . ($this->withPhp($version) ? '' : $warningPattern);
            } else
            // проверка расширения PHP
            if ($type === 'phpExt') {
                $name = $compare['name'] ?? '';
                $types[$type][] = $name
                    . ($this->withPhpExt($name) ? '' : $warningPattern);
            } else
            // проверка версии приложения
            if ($type === 'app') {
                $version = $compare['version'] ?? '';
                $code    = $compare['code'] ?? '';
                $types[$type][] = $code 
                    . ($version ? $paramGlue . $version : '') 
                    . ($this->withApp($code, $version) ? '' : $warningPattern);
            } else
            // проверка версии редакции
            if ($type === 'edition') {
                $version = $compare['version'] ?? '';
                $code    = $compare['code'] ?? '';
                $types[$type][] = $code 
                    . ($version ? $paramGlue . $version : '')
                    . ($this->withEdition($code, $version) ? '' : $warningPattern);
            } else
            // проверка версии модуля
            if ($type === 'module') {
                $name    = '';
                $version = $compare['version'] ?? '';
                $id      = $compare['id'] ?? '';
                $with    = $this->withModule($id, $version, $name);
                $types[$type][] = $name
                    . ($version ? $paramGlue . $version : '')
                    . ($with ? '' : $warningPattern);
            } else
            // проверка версии расширения
            if ($type === 'extension') {
                $name    = '';
                $version = $compare['version'] ?? '';
                $id      = $compare['id'] ?? '';
                $with    = $this->withExtension($id, $version, $name);
                $types[$type][] = $name
                    . ($version ? $paramGlue . $version : '')
                    . ($with ? '' : $warningPattern);
            } else
            // проверка версии виджета
            if ($type === 'widget') {
                $version = $compare['version'] ?? '';
                $id      = $compare['id'] ?? '';
                $with    = $this->withWidget($id, $version, $name);
                $types[$type][] = $name
                    . ($version ? $paramGlue . $version : '')
                    . ($with ? '' : $warningPattern);
            // проверка пользовательских параметров
            } else {
                $compareName = 'with' . $type;
                if (method_exists($this, $compareName)) {
                    $types[$type][] = implode($paramGlue, array_keys($compare))
                        . ($this->$compareName($compare) ? '' : $warningPattern);
                }
            }
        }
    
        foreach ($types as $type => &$items) {
            $items = implode($glue, $items);
        }
        return $types;
    }
}
