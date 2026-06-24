<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Shortcode;

use Ge;
use Ge\Config\Config;
use Ge\Stdlib\Service;

/**
 * Менеджер шорткодов (shortcodes).
 * 
 * ShortcodeManager - это служба приложения, доступ к которой можно получить через `Ge::$app->shortcodes`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Shortcode
 * @since 2.0
 */
class ShortcodeManager extends Service
{
    /**
     * Имя обработчика шорткодов.
     *
     * @var string
     */
    protected string $handler = 'maiorano';

    /**
     * Обработчики шорткодов.
     *
     * @var array<string, string>
     */
    protected array $handlers = [
        'thunder'  => 'Ge\Shortcode\Handler\ThunderHandler',
        'maiorano' => 'Ge\Shortcode\Handler\MaioranoHandler'
    ];

    /**
     * Конфигуратор менеджера шорткодов.
     *
     * @var null|Config
     */
    public ?Config $config = null;

    /**
     * Имена шорткодов c контентом.
     * 
     * @var array
     */
    protected array $contentRender = [];

    /**
     * Имена шорткодов с атрибутами, которые будет заменены.
     * 
     * Имеет вид:
     * ```php
     *    [
     *        'заменяемый шорткод' => [
     *            'code'       => 'новый шорткод',
     *            'attributes' => [],
     *            'content'    => 'новый контент'
     *        ],
     *        // ...
     *    ]
     * ```
     * 
     * @var array
     */
    protected array $replaceTo = [];

    /**
     * Имена шорткодов с указателями на функции (методы) обрабатывающие их.
     * 
     * В отличии от остальных шорткодов, содержит только те, которые зарегистрированы без 
     * указания модуля в {@see \Ge\Shortcode\ShortcodeManager::add()}.
     * 
     * @var array
     */
    protected array $shortcodes = [];

    /**
     * Регистрирует шорткод.
     * 
     * Прямая регистрация без указания модуля, которому должен принадлежать шорткод.
     * 
     * @param string $tag Тег шорткода. Если был зарегистрирован ранее, будет замена.
     * @param mixed $func Функция обрабатывающая шорткод.
     *     Может иметь вид:
     *        - 'func_name'
     *        - `['Class', 'func_name']`
     *        - `function (array $attributes = []) {...}`
     * 
     * @return $this
     */
    public function add(string $tag, $func): static
    {
        $this->shortcodes[$tag] = $func;
        $this->config->setSubset('shortcodes', $tag, 'rg.app');
        return $this;
    }

    /**
     * Отменяет регистрацию  шорткода.
     * 
     * @param string $tag Тег шорткода.
     * 
     * @return $this
     */
    public function remove(string $tag): static
    {
        unset($this->shortcodes[$tag]);
        $this->config->setSubset('shortcodes', $tag, null);
        return $this;
    }

    /**
     * Отменяет регистрацию всех шорткодов указанного модуля.
     * 
     * @param string $moduleId Идентификатор модуля.
     * 
     * @return $this
     */
    public function removeByModuleId(string $moduleId): static
    {
        foreach ($this->shortcodes as $name => $shmoduleId) {
            if ($moduleId === $shmoduleId) {
                unset($this->shortcodes[$name]);
            }
        }
        $this->config->set('shortcodes', $this->shortcodes);
        return $this;
    }

    /**
     * Проверяет, зарегистрирован ли шорткод.
     * 
     * Только тот шорткод, который зарегистрирован без указания модуля в {@see ShortcodeManager::add()}.
     * 
     * @param string $codeName Имя шорткода.
     * 
     * @return bool Возвращает значение `true`, если шорткод зарегистрирован.
     */
    public function isExists(string $codeName): bool
    {
        return isset($this->shortcodes[$codeName]);
    }

    /**
     * Замена атрибутов и контента шорткода.
     * 
     * @param string $source Имя заменяемого шорткода.
     * @param string $destination Имя шорткода на который будет замена.
     * @param array $attributes Новые атрибуты шорткода.
     * @param string $content Новый контент шорткода.
     * 
     * @return $this
     */
    public function replaceTo(string $source, string $destination, array $attributes = [], string $content = ''): static
    {
        $this->replaceTo[$source] = [
            'code'       => $destination,
            'attributes' => $attributes,
            'content'    => $content
        ];
        return $this;
    }

    /**
     * Возвращает атрибуты шорткода, которые были заменены.
     * 
     * Замена с помощью {@see ShortcodeManager::replaceTo()}.
     * 
     * @param string $codeName Имя шорткода, атрибуты которого были заменены.
     * 
     * @return false|array Возвращает значение `false`, если не была замена шорткода.
     */
    public function replaceFrom(string $codeName): false|array
    {
        return $this->replaceTo[$codeName] ?? false;
    }

    /**
     * Устанавливает указанному шорткоду контент.
     * 
     * @param string $tag Тег шорткода.
     * @param string $content Контент.
     * 
     * @return $this
     */
    public function renderTo(string $tag, string $content): static
    {
        if (null === $content)
            unset($this->contentRender[$tag]);
        else
            $this->contentRender[$tag] = $content;
        return $this;
    }

    /**
     * Возвращает контент указанного шорткода. 
     * 
     * Если шорткоду ранее установили контент через {@see ShortcodeManager::renderTo()}.
     * 
     * @param string $tag Тег шорткода.
     * 
     * @return false|string Возвращает значение `false`, если шорткоду не устанавливали 
     *     контент.
     */
    public function renderFrom(string $tag): false|string
    {
        return $this->contentRender[$tag] ?? false;
    }

    /**
     * Устанавливает обработчик шорткодов.
     * 
     * @param string $name Имя обработчика {@see ShortcodeManager::$handlers}.
     * 
     * @return $this
     */
    public function setHandler(string $name): static
    {
        $this->handler = $name;
        return $this;
    }

    /**
     * Возвращает имя текущего обработчика шорткодов.
     * 
     * @param null|string $name Имя обработчика. Если значение `null`, использует 
     *     обработчик по умолчанию {@see ShortcodeManager::$handler}.
     * 
     * @return false|string Возвращает значение `false`, если обработчик не найден.
     */
    public function getHandler(?string $name = null): false|string
    {
        if ($name === null) {
            $name = $this->handler;
        }
        return $this->handlers[$name] ?? false;
    }

    /**
     * Возвращает настройки шорткода.
     * 
     * @param string $tag Тег шорткода.
     * 
     * @return string|array|null Возвращает значение `null`, если шорткод отсутствует.
     */
    public function getShortcodeOptions(string $tag): string|array|null
    {
        return $this->config->shortcodes[$tag] ?? null;
    }

    /**
     * Возвращает названия метода контроллера, вызывающий шорткод.
     * 
     * @param string $tag Тег шорткода.
     * @param string $pattern Шаблона имени шорткода.
     * 
     * @return string
     */
    public function getShortcodeMethod(string $tag, ?string $pattern = null): string
    {
        if ($pattern === null) {
            $pattern = $this->config->patternFunc;
        }
        return sprintf($pattern, $tag);
    }

    /**
     * Извлекает регулярное выражение короткого кода для поиска.
     *
     * Регулярное выражение объединяет теги шорткода в регулярном выражении в 
     * класс регулярного выражения.
     *
     * Регулярное выражение содержит 6 различных подсоответствий, облегчающих синтаксический анализ.
     *
     * 1 - Дополнительно [ для экранирования шорткодов с двойным [[]]
     * 2 - Имя шорткода
     * 3 - Список аргументов шорткода
     * 4 - Самозакрывающийся /
     * 5 - Содержимое шорткода, когда он оборачивает некоторый контент.
     * 6 - Дополнительно ] для экранирования шорткодов с двойным [[]]
     *
     * @param null|array $tagNames Список шорткодов для поиска.
     * 
     * @return string Регулярное выражение поиска шорткода.
     */
    public function getShortcodeRegex(?array $tagNames = null): string
    {
        static $default = null;

        if ($tagNames === null) {
            $default = $default ?: array_keys($this->config->shortcodes ?: []);
            $tagNames = $default;
        }

        $tagregexp = implode('|', array_map('preg_quote', $tagNames));
        return '\\['                             // Открывающаяся скобка.
            . '(\\[?)'                           // 1: Дополнительная вторая открывающая скобка для экранирования шорткодов: [[tag]].
            . "($tagregexp)"                     // 2: Имя короткого кода.
            . '(?![\\w-])'                       // Не сопровождается символом слова или дефисом.
            . '('                                // 3: Развернуть цикл: Внутри открывающего тега шорткода.
            .     '[^\\]\\/]*'                   // Не закрывающая скобка и не косая черта.
            .     '(?:'
            .         '\\/(?!\\])'               // Косая черта, за которой не следует закрывающая скобка.
            .         '[^\\]\\/]*'               // Не закрывающая скобка и не косая черта.
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Самозакрывающийся тег...
            .     '\\]'                          // ...и закрывающая скобка.
            . '|'
            .     '\\]'                          // Закрывающая скобка.
            .     '(?:'
            .         '('                        // 5: Развернуть цикл: по желанию, все, что находится между открывающим и закрывающим тегами шорткода.
            .             '[^\\[]*+'             // Не открывающаяся скобка.
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // Открывающаяся скобка, за которой не следует закрывающий тег шорткода.
            .                 '[^\\[]*+'         // Не открывающаяся скобка.
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Закрывающий тег шорткода.
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Необязательная вторая закрывающая скобка для экранирования шорткодов: [[tag]].
    }

    /**
     * Определяет, содержит ли переданный контент указанный шорткод.
     * 
     * @param string $content Контент для поиска шорткодов.
     * @param string $tag Тег шорткода для проверки.
     * 
     * @return bool Содержит ли переданный контент указанный шорткод.
     */
    public function hasShortcode(string $content, string $tag): bool
    {
        static $tags = null, $regex = null;

        if (!str_contains($content, '[')) return false;
    
        if ($tags === null) $tags = $this->config->shortcodes ?: [];
        
        if (isset($tags[$tag])) {
            if ($regex === null) $regex = $this->getShortcodeRegex(array_keys($tags));

            preg_match_all( '/' . $regex . '/', $content, $matches, PREG_SET_ORDER);
            if (empty( $matches)) return false;
    
            foreach ($matches as $shortcode) {
                if ($tag === $shortcode[2]) return true;
                if (!empty($shortcode[5]) && $this->hasShortcode($shortcode[5], $tag)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Возвращает список зарегистрированных имен шорткодов, найденных в указанном контенте.
     *
     * Например:
     * ```php
     * $this->getShortcodeTagsInContent('[video src="file.avi"][/video] [foo] [gallery ids="1,2,3"]');
     * // ['video', 'gallery']
     * ```
     *
     * @param string $content Содержание для проверки.
     * 
     * @return array<int, string> Массив зарегистрированных имен коротких кодов, найденных в контенте.
     */
    public function getShortcodeTagsInContent(string $content): array
    {
        static $regex = null;

        if (false === strpos($content, '[')) return [];

        if ($regex === null) $regex = $this->getShortcodeRegex();

        preg_match_all('/' . $regex . '/', $content, $matches, PREG_SET_ORDER );
        if (empty( $matches)) return [];

        $tags = [];
        foreach ($matches as $shortcode) {
            $tags[] = $shortcode[2];

            if (!empty($shortcode[5])) {
                $deepTags = $this->getShortcodeTagsInContent( $shortcode[5] );
                if (!empty($deepTags)) {
                    $tags = array_merge($tags, $deepTags);
                }
            }
        }
        return $tags;
    }

    /**
     * Возвращает контент шорткода.
     * 
     * @param string $tag Тег шорткода.
     * @param array $attributes Атрибуты шорткода.
     * @param string|null $content 
     * 
     * @return mixed
     */
    public function getContent(string $tag, array $attributes = [], $content = null): mixed
    {
        // если шорткоду раннее указали контент, то нет смысла определять кто он и откуда, 
        // возвращаем его контент
        /*
        if (isset($this->contentRender[$codeName])) {
            return $this->contentRender[$codeName];
        }
        */

        // проверяем шорткод, который зарегистрирован без указания модуля
        $shortcode = $this->shortcodes[$tag] ?? null;
        if ($shortcode) {
            if (is_string($shortcode) || is_array($shortcode))
                return call_user_func($shortcode, $attributes);
            else
            if ($shortcode instanceof \Closure)
                return $shortcode->call($this, $attributes);
            else {
                throw new Exception\NotDefinedException('Can\'t call shortcode.');
            }
        }

        /** @var array|null $option Параметры шорткода */
        $option = $this->getShortcodeOptions($tag);
        if ($option === null) {
            throw new Exception\NotDefinedException(Ge::t('app', 'Unknown shortcode "{0}"', [$tag]));
        }

        if (is_string($option)) {
            /** @var \Ge\ModuleManager\ModuleManager $manager Менеджер модулей */
            $manager = Ge::$app->modules;
            $componentId = $option;
            $type = 'module';
        } else {
            if (isset($option['module'])) {
                /** @var \Ge\ModuleManager\ModuleManager $manager Менеджер модулей */
                $manager = Ge::$app->modules;
                $componentId = $option['module'];
                $type = 'module';
            } else
            if (isset($option['extension'])) {
                /** @var \Ge\ExtensionManager\ExtensionManager $manager Менеджер расширений модулей */
                $manager = Ge::$app->extensions;
                $componentId = $option['extension'];
                $type = 'extension';
            } else
            if (isset($option['widget'])) {
                /** @var \Ge\WidgetManager\WidgetManager $manager Менеджер виджетов */
                $manager = Ge::$app->widgets;
                $componentId = $option['widget'];
                $type = 'widget';
            } else
                $type = '';
        }

        if (!isset($manager)) {
            throw new Exception\NotDefinedException(
                Ge::t('app', 'Shortcode "{0}" does not have option "{1}"', [$tag, 'module|extension|widget'])
            );
        }

        // если компонент не доступен `enabled = false`
        if (!$manager->getRegistry()->isEnabled($componentId)) {
            return '';
        }

        if ($type === 'widget') {
            /** @var \Ge\View\BaseWidget|null $component */
            $component = $manager->get($componentId, ['attributes' => $attributes]);
            if ($component === null) return '';

            if (isset($option['func']))
                return $component->do($option['func']);
            else {
                return $component->renderMe();
            }
        // module, extension
        } else {
            /** @var \Ge\Mvc\Module\BaseModule|\Ge\Mvc\Extension\BaseExtension|null $component */
            $component = $manager->get($componentId);
            if ($component === null) return '';

            $method = isset($option['func']) ? $option['func'] : $this->getShortcodeMethod($tag);
            return $component->do($method, $attributes);
        }
    }

    /**
     * Возвращает текст содержащий контент шорткодов.
     * 
     * @param string $text Текст с разметкой шорткодов.
     * 
     * @return string
     */
    public function process(string $text): string
    {
        $class = $this->getHandler();
        if (!$class) {
            return '[unknow handler to processings shortcodes]';
        }
        return $class::factory($this, $this->config->shortcodes)->process($text);
    }
}
