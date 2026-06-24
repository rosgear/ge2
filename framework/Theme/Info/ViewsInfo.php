<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Theme\Info;

use Ge;
use Ge\Theme\Theme;
use Ge\Helper\Json;
use Ge\I18n\ISO\ISO;
use Ge\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Класс формирования описаний шаблонов темы.
 * 
 * Каждая тема приложения должна иметь файл "views.json" с описанием шаблонов в 
 * формате JSON. Если описание шаблонов отсутствует, то файл содержит '[]'.
 * 
 * Каждый компонент (модуль, расширение модуля, виджет) может иметь описание шаблона,
 * например:
 *  
 * ```php
 * [
 *      '/rg/rg.be.recovery/mails/recovery-ru_RU.phtml' => [
 *            'type'          => 'form', // вид шаблона
 *            'name'          => 'Recovery', // название шаблона
 *            'description'   => 'Form "Recovery"', // описание
 *            'view'          => 'mails/recovery-ru_RU', // название представления для вызова шаблона
 *            'language'      => 'Русский (ru-RU)', // язык шаблона
 *            'locale'        => 'ru_RU', // имя локализации шаблона
 *            'use'           => 'backend' // назначение шаблона для стороны клиента: `BACKEND`, `FRONTEND`
 *            'component'     => 'rg.be.recovery', // идентификатор компонента
 *            'componentType' => 'module' // вид компонента: 'widget', 'module', 'extension'
 *       ],
 *       // ...
 *  ]
 * ```
 * 
 * Пример описания каталога шаблона:
 * 
 * ```php
 * [
 *     '/rg/rg.be.recovery/mails' => [
 *         'type'        => 'folder', // для каталога всегда вид шаблона 'folder'
 *         'name'        => 'Mails', // название каталога
 *         'description' => 'Folder "Mails"' // описание
 *      ],
 *      // ...
 * ]
 * ```
 * Такое свойства, как 'type' применяется для фильтрации шаблонов по виду.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Theme\Info
 * @since 2.0
 */
class ViewsInfo
{
    use Ge\Stdlib\CollectionTrait;

    /**
     * Тема.
     * 
     * @var Theme
     */
    protected Theme $theme;

    /**
     * Имя файла с описанием шаблона темы.
     * 
     * @var null|string
     */
    protected ?string $filename = null;

    /**
     * Если файл с описанием был загружен.
     * 
     * @var bool
     */
    public bool $isLoaded = false;

    /**
     * Виды шаблонов.
     *
     * @var array
     */
    protected array $types = [
        'folder'  => ['name' => 'Folder'],
        'error'   => ['name' => 'Error', 'folder' => ['errors', 'error']],
        'page'    => ['name' => 'Page', 'folder' => ['pages', 'page']],
        'article' => ['name' => 'Article', 'folder' => ['articles', 'article']],
        'layout'  => ['name' => 'Layout', 'folder' => ['layouts', 'layout']],
        'form'    => ['name' => 'Form'],
        'grid'    => ['name' => 'Grid'],
        'widget'  => ['name' => 'Widget'],
        'view'    => ['name' => 'View'],
        'mail'    => ['name' => 'Mail', 'folder' => ['mails', 'mail']]
    ];

    /**
     * Менеджер обозначений ISO.
     *
     * @var ISO
     */
    protected ISO $iso;

    /**
     * Транслятор (переводчик) выполняет локализацию описания шаблонов.
     * 
     * @see ViewsInfo::getTranslator()
     * 
     * @var Translator
     */
    protected Translator $translator;

    /**
     * Конструктор класса.
     * 
     * @param Theme $theme Тема.
     * 
     * @return void
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        $this->iso = Ge::$app->locale->getISO();
    }

    /**
     * Возвращает имя файл - описания шаблона темы.
     * 
     * @param null|string $themeName Название темы. Если значение `null`, 
     *     используется текущая тема (по умолчанию `null`).
     * 
     * @return string|null Если значение `null`, указанная тема не существует.
     */
    public function getFilename(?string $themeName = null): ?string
    {
        $themeParams = $this->theme->get($themeName);
        if (empty($themeParams)) {
            return null;
        }
        return $this->theme->themesPath . $themeParams['localPath'] . DS . $this->theme::VIEW_DESC_FILE;
    }

    /**
     * Проверяет, существует ли файл описания шаблонов темы.
     * 
     * @param null|string $themeName Название темы. Если значение `null`, 
     *     используется текущая тема (по умолчанию `null`).
     * 
     * @return bool Если значение `false`, файл с описанием отсутствует.
     */
    public function exists(?string $themeName = null): bool
    {
        $filename = $this->getFilename($themeName);
        if ($filename === null) {
            return false;
        }
        return Filesystem::exists($filename);
    }

    /**
     * Возвращает описание файлов шаблонов расположенных в каталоге указанной темы.
     * 
     * @param null|string $themeName Название темы. Если значение `null`, используется 
     *     текущая тема (по умолчанию `null`).
     * @param null|string $side Сторона клиента: `BACKEND`, `FRONTEND` в описании файла шаблона.
     *     Если значение `null`, сторона определяется из текущей темы (по умолчанию `null`).
     * 
     * @return array
     */
    public function themeFilesDescription(?string $themeName = null, ?string $side = null): array
    {
        // поиск всех файлов (шаблонов) кроме backend
        /** @var \Symfony\Component\Finder\Finder $finder  */
        $finder = Filesystem::finder();
        if ($side === null) {
            $side = $this->theme->side;
        }

        $finder->files()->in($this->theme->getViewPath($themeName));
        $rows = $folders = [];
        foreach ($finder as $info) {
            /** @var string $relativePath Относительный путь к файлу шаблона */
            $relativePath = $info->getRelativePath();
            if ($relativePath) {
                $relativePath = str_replace('\\', '/', $relativePath);
            }
            
            // имя файла
            $filename = $info->getFilename();
            /** @var string $basename Имя файла без расширения */
            $basename = pathinfo($filename, PATHINFO_FILENAME);

            /** @var array $locale Информацияо локализации: `['имя шаблона', 'имя языка', 'информация о локали']` */
            $locale = $this->defineDescriptionLocale($basename);

            /** @var string $key Ключ описания шаблона */
            $key = ($relativePath ? '/' . $relativePath . '/' : '/') . $filename;

            $rows[$key] = [
                'type'          => $this->defineType($basename, $relativePath),
                'name'          => $locale[0],
                'description'   => '',
                'view'          => trim($relativePath . '/' . $basename, '/'),
                'language'      => $locale[1],
                'locale'        => $locale[2],
                'use'           => $side,
                'component'     => '',
                'componentType' => ''
            ];

            // т.к. каталоги могут повторяться, то:
            $folders[$relativePath] = '';
        }

        // описание каталогов в каторых находятся шаблоны
        foreach ($folders as $folder => $value) {
            /** @var string $name Имя каталога  */
            // если каталог не имеет формат идентификатора компонента (например, 'rg.foobar')
            if (mb_strpos($folder, '.') === false) {
                $name = ucfirst(str_replace('/', ' ', $folder));
            } else {
                /** @var array $chunks Название каталогов */
                $chunks = explode('/', $folder);
                if ($chunks)
                    $name = $chunks[sizeof($chunks) - 1] ?? '';
                else
                    $name = $folder;
                $name = ucfirst($name);
            }
            $rows['/' . $folder] = [
                'type'        => 'folder',
                'name'        => $name,
                'description' => ''
            ];
        }
        return $rows;
    }

    /**
     * Определяет свойство "вид" (type) в описании шаблона по его имени.
     * 
     * @param string $name Имя шаблона.
     * @param null|string $path Локальный путь к файлу шаблона (по умолчанию `null`).
     * 
     * @return string
     */
    protected function defineType(string $name, ?string $path = null): string
    {
        foreach ($this->types as $type => $params) {
            if (empty($params)) continue;

            // определение по каталогу шаблона
            if ($path && isset($params['folder'])) {
                $folders = (array) $params['folder'];
                $path = trim($path, '/');
                $s[] = $path;
                if (in_array($path, $folders)) {
                    return $type;
                }
            }

            // определение по имени шаблона
            if (isset($params['name'])) {
                $names = (array) $params['name'];
                foreach ($names as $pname) {
                    if (mb_stripos($name, $pname) !== false) {
                        return $type;
                    }
                }
            }
        }
        return 'view';
    }

    /**
     * Устанавливает виды шаблонов.
     * 
     * @param array $types Виды шаблонов.
     * 
     * @return $this
     */
    public function setTypes(array $types): static
    {
        $this->types = $types;
        return $this;
    }

    /**
     * Возрващает виды шаблонов.
     * 
     * @param bool $translated Выполнять перевод видов шаблонов (по умолчанию `false`).
     * 
     * @return array
     */
    public function getTypes(bool $translated = false): array
    {
        if ($translated) {
            $types = [];
            /** @var \Ge\Theme\Info\Translator $translator */
            $translator = $this->getTranslator();
            foreach ($this->types as $type => $params) {
                $types[$type] = $translator->translate($params['name']);
            }
            return $types;
        } else
            return $this->types;
    }

    /**
     * Проверяет, существует ли вид шаблона.
     * 
     * @param string $type Вид шаблона.
     * 
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return key_exists($type, $this->types);
    }

    /**
     * Определяет локализацию (locale) шаблона из имени файла шаблона.
     * 
     * Где, имя файла имеет вид: `{name}-{locale}`, например, 'info-en_GB.phtml'.
     * Если язык определен, имя файла будет преобразовано в имя шаблона.
     * 
     * @param string $name Имя файла шаблона.
     * 
     * @return array Результат, может иметь вид, наприме:
     * ```php
     *    [
     *       'info', // имя шаблона
     *       'English (en-GB)', // имя языка
     *       'en_GB' // имя локали
     *    ]
     * ```
     */
    protected function defineDescriptionLocale(string $name) :array
    {
        $locale = '';
        $language = '';
        $localeinfo = null;
        if (($index = mb_strrpos($name, '-')) !== false) {
            $locale = mb_substr($name, $index + 1);
            $localeinfo = $this->iso->locales->get($locale);
            if ($localeinfo) {
                if (isset($localeinfo['nativeName']['language'])) {
                    $language = $localeinfo['nativeName']['language'] . ' (' . str_replace('_', '-', $locale) . ')';
                } else
                $language = str_replace('_', '-', $locale);
            } else
            $language = '';
        }
        if ($localeinfo !== null) {
            $name = mb_substr($name, 0, $index);
        }
        $name = ucfirst(trim(str_replace(['-', '_'], ' ', $name)));
        return [$name, $language, $localeinfo ? $locale : ''];
    }

    /**
     * Возвращает описание файлов шаблонов компонентов (модулей, расширений модулей, 
     * виджетов) расположенных в каталоге указанной темы.
     * 
     * @param array $items Параметры компонентов, полученных {@see \Ge\ModuleManager\BaseRegistry::getAll()}.
     * @param string $themeViewPath Абсолютный путь к шаблонам темы.
     * @param string $componentType Вид компонента: 'widget', 'module', 'extension'.
     * 
     * @return array
     */
    public function getComponentItemsDescription(array $items, string $themeViewPath, string $componentType): array
    {
        $rows = [];
        foreach ($items as $itemId => $item) {
            /** @var string $itemPath Абсолютный путь к шаблонам компонента указанной темы */
            $itemPath = $themeViewPath . $item['path'];
            // если шаблонов у компонента нет
            if (!file_exists($itemPath)) continue;

            // поиск всех файлов (шаблонов)
            $finder = Finder::create();
            $finder->files()->in($itemPath);
            foreach ($finder as $info) {
                /** @var string $relativePath Относительный путь к файлу шаблона */
                $relativePath = $info->getRelativePath();
                if ($relativePath) {
                    $relativePath = str_replace('\\', '/', $relativePath);
                }

                // имя файла
                $filename = $info->getFilename();
                /** @var string $basename Имя файла без расширения */
                $basename = pathinfo($filename, PATHINFO_FILENAME);

                /** @var array $locale Информацияо локализации: `['имя шаблона', 'имя языка', 'информация о локали']` */
                $locale = $this->defineDescriptionLocale($basename);

                /** @var string $key Ключ описания шаблона */
                $key = $item['path'] . ($relativePath ? '/' . $relativePath . '/' : '/') . $filename;

                  /** @var string $folder Имя каталога шаблона */
                if ($relativePath) {
                    $folder = explode('/', $relativePath);
                    if ($folder) {
                        $folder = $folder[sizeof($folder) - 1] ?? '';
                    }
                    else
                        $folder = $relativePath;
                    $folder = ucfirst($folder);
                } else
                    $folder = $itemId;

                $rows[$key] = [
                    'type'          => $this->defineType($basename, $relativePath),
                    'name'          => $locale[0],
                    'description'   => '',
                    'view'          => trim($relativePath . '/' . $basename, '/'),
                    'language'      => $locale[1],
                    'locale'        => $locale[2],
                    'use'           => $item['use'],
                    'component'     => $itemId,
                    'componentType' => $componentType
                ];

                // добавление описания каталогов
                $path = trim($item['path'] . ($relativePath ? '/' . $relativePath : ''), '/');
                $chunks = explode('/', $path);
                $key = '';
                foreach ($chunks as $chunk) {
                    $key .= '/' . $chunk;
                    // если каталог не идентификатор компонента
                    if ($itemId !== $chunk)
                        $name = ucfirst($chunk);
                    else
                        $name = $chunk;
                    $rows[$key] = [
                        'type'        => 'folder',
                        'name'        => $name,
                        'description' => '',
                    ];
                }
            }
        }
        return $rows;
    }

    /**
     * Возвращает описание файлов шаблонов компонентов (модулей, расширений модулей, 
     * виджетов) расположенных в каталоге указанной темы.
     * 
     * @param null|string $themeName Название темы. Если значение `null`, используется 
     *     текущая тема (по умолчанию `null`).
     * 
     * @return array
     */
    public function componentFilesDescription(?string $themeName = null): array
    {
        $result = [];
        /** @var string $viewPath Абсолютный путь к шаблонам указанной темы */
        $viewPath = $this->theme->getViewPath($themeName);

        // конфигурации установленных модулей
        $modules = Ge::$app->modules->getRegistry()->getAll();
        if ($modules) {
            $result = $this->getComponentItemsDescription($modules, $viewPath, 'module');
        }

        // конфигурации установленных расширений модулей
        $extensions = Ge::$app->extensions->getRegistry()->getAll();
        if ($extensions) {
            $result = array_merge(
                $result, 
                $this->getComponentItemsDescription($extensions, $viewPath, 'extension')
            );
        }

        // конфигурации установленных виджетов
        $widgets = Ge::$app->widgets->getRegistry()->getAll();
        if ($widgets) {
            $result = array_merge(
                $result, 
                $this->getComponentItemsDescription($widgets, $viewPath, 'widget')
            );
        }
        return $result;
    }

    /**
     * Создаёт описание файлов (шаблонов).
     * 
     * @param string $type Вид описания (по умолчанию 'components'):
     *     - 'components', файлы шаблонов принадлежащих компонентам;
     *     - 'files', файлы шаблонов не принадлежащих компонентам;
     *     - 'all', все файлы шаблонов.
     * @param null|string $themeName Название темы. Если значение `null`, используется 
     *     текущая тема (по умолчанию `null`).
     * @param null|string $side Сторона клиента: `BACKEND`, `FRONTEND` в описании файла шаблона.
     *     Если значение `null`, сторона определяется из текущей темы (по умолчанию `null`).
     * 
     * @return $this
     */
    public function generateDescription(string $type = 'components', ?string $themeName = null, ?string $side = null)
    {
        $description = [];
        switch ($type) {
            case 'components': $description = $this->componentFilesDescription($themeName); break;

            case 'files': $description = $this->themeFilesDescription($themeName, $side); break;

            case 'all':
                $description = array_merge(
                    $this->themeFilesDescription($themeName, $side),
                    $this->componentFilesDescription($themeName)
                );
                break;
        }
        $this->setAll($description);
        return $this;
    }

    /**
     * Загружает описание файлов шаблонов и их директорий указанной темы.
     * 
     * @param null|string $themeName Название темы. Если знаяение `null`, 
     *     используется текущая тема (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если описание загрузить невозможно.
     * 
     * @throws \Ge\Theme\Info\Exception\ViewsNotFoundException
     * @throws \Ge\Theme\Info\Exception\FileNotReadException
     */
    public function load(?string $themeName = null): bool
    {
        $this->filename = $this->getFilename($themeName);
        if ($this->filename === null) return false;

        // если файл не существует
        if (!Filesystem::exists($this->filename))
            throw new Exception\ViewsNotFoundException($this->filename);

        // если файл не доступен для чтения
        if (!Filesystem::isReadable($this->filename))
            throw new Exception\FileNotReadException($this->filename);

        // чтение файла в ассоциа-й массив
        $this->container = Json::loadFromFile($this->filename);
        if ($this->container === false) {
            Ge::debug('Error', ['themeName' => $themeName, 'filename' => $this->filename]);
            $this->container = [];
        }
        return $this->isLoaded = true;
    }

    /**
     * Сохраняет описание шаблонов в каталоге указанной темы.
     * 
     * @param null|string $themeName Название темы. Если значение `null`, используется 
     *     текущая тема (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если описание сохранить невозможно.
     * 
     * @throws \Ge\Theme\Info\Exception\FileNotWriteException
     */
    public function save(?string $themeName = null): bool
    {
        if ($themeName !== null) {
            $filename = $this->getFilename($themeName);
            if ($filename === null) return false;
        } else
            $filename = $this->filename;

        // если невозможно выполнить запись в файл
        if (!Filesystem::isWritable($filename)) {
            throw new Exception\FileNotWriteException($filename);
        }

        $json = Json::encode($this->container, false, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (Filesystem::put($filename, $json) === false) {
            throw new Exception\FileNotWriteException($filename);
        }
        return true;
    }

    /**
     * Возвращает переводчик шаблонов.
     * 
     * @param null|string $locale Имя локали, например: 'ru_RU', 'en_GB.
     * 
     * @return Translator
     */
    public function getTranslator(?string $locale = null): Translator
    {
        if (!isset($this->translator)) {
            $this->translator = new Translator($locale);
        }
        return $this->translator;
    }

    /**
     * Выполняет перевод (локализацию) сообщения.
     * 
     * @see \Ge\Theme\Info\Translator::translate()
     * 
     * @param string $message Текст сообщения.
     * 
     * @return string|array
     */
    public function translate(string $message)
    {
        return $this->getTranslator()->translate($message);
    }

    /**
     * Перевод (локализация) сообщения из источника.
     * 
     * @param string $source Источник, ключ в массиве сообщений.
     * @param string $message Текст сообщения.
     * 
     * @return string|array
     */
    public function translateFrom(string $source, string $message)
    {
        return $this->getTranslator()->translateFrom($source, $message);
    }

    /**
     * Возвращает описание шаблона по указанному свойству и его значению.
     * 
     * @param string $property Свойство в описании шаблона.
     * @param string $value Значение свойства.
     * 
     * @return array|null Возвращает значение `null` если описание не найдено.
     */
    public function getBy(string $property, string $value): ?array
    {
        foreach ($this->container as $key => $desc) {
            if (isset($desc[$property]) && $desc[$property] === $value) {
                $desc['filename'] = $key;
                return $desc;
            }
        }
        return null;
    }

    /**
     * Поиск описаний шаблонов.
     * 
     * @param array $needle Свойства в описании шаблона, которые необходимо найти,
     *     например: `['type' => 'page', ...]`.
     * @param bool $translate Выполнять перевод и формирование описания шаблона если 
     *     описание отсутствует (по умолчанию `false`).
     * @param array $properties Свойства, значение которых необходимо вернуть. 
     *     Если значение `null`, вернёт все свойства с их значениями в виде пар "ключ - значение" 
     *     (по умолчанию `null`).
     * @param bool $indexes (по умолчанию `false`).
     * 
     * @return array
     */
    public function find(array $needle, bool $translate = false, array $properties = ['*'], bool $indexes = false): array
    {
        if ($translate) {
            /** @var \Ge\Theme\Info\Translator $translator */
            $translator = $this->getTranslator();
        }

        $items = [];
        foreach ($this->container as $key => $desc) {
            $found = false;
            foreach ($needle as $name => $value) {
                if ($value === '*')
                    $check = true;
                else
                    $check = $desc[$name] === $value;
                if (isset($desc[$name]) && $check) {
                    if ($translate) {
                        if (empty($desc['description'])) {
                            /** @var string $typeName Вид шаблона */
                            $typeName = $translator->translate(
                                ucfirst(empty($desc['type']) ? 'file' : $desc['type'])
                            );

                            /** @var string $description Описание шаблона */
                            if (empty($desc['name']))
                                $description = $typeName;
                            else
                                $description = $typeName . ' "' . $translator->translate($desc['name']) . '"';

                            // добавления к описанию имени языка
                            if ($desc['locale']) {
                                $description .= ', ' . $desc['locale'];
                            }
                            $desc['description'] = $description;
                        }
                    }
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $desc['id'] = $key; // чтобы можно было указать в $withIndexes
                if ($properties[0] === '*')
                    $items[$key] = $desc;
                else {
                    $itemsIndexes = [];
                    if ($indexes)
                        foreach ($properties as $property) 
                            $itemsIndexes[] = $desc[$property] ?? null;
                    else
                        foreach ($properties as $property) 
                            $itemsIndexes[$property] = $desc[$property] ?? null;

                    $items[] = $itemsIndexes;
                }
                    
            }
        }
        return $items;
    }
}
