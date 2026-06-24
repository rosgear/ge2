<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\FontAwesome;

use Ge\Stdlib\Service;

/**
 * Сервис иконочного шрифта Font Awesome.
 * 
 * FontAwesome - это служба приложения, доступ к которой можно получить через `Ge::$app->fontAwesome`.
 * 
 * Для создания карты css классов шрифта, необходимо выполнить:
 * 
 * * Создание массива css классов шрифта на основе css файла.
 *   $map = FontAwesome::parseCssToArray('all.css'); // карта парсинга css файла
 * * Поиск файлов в каталоге стилей Font Awesome, для определения стилей к css классам шрифта.
 *   $styles = FontAwesome::searchStylesFromFilenames([
 *          FontAwesome::STYLE_SOLID   => '.../solid', 
 *          FontAwesome::STYLE_REGULAR => '.../regular',
 *          FontAwesome::STYLE_LIGHT   => '.../light',
 *          FontAwesome::STYLE_BRANDS  => '.../brands'
 *   ]);
 * * Добавление стилей в массив css классов шрифта.
 *   $map = FontAwesome::addStylesToMap($styles, $map);
 * * Парсинг массива css классов шрифта в контент файла карты.
 *   $mapText = FontAwesome::parseArrayToFileMap($map);
 * );
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\FontAwesome\FontAwesome
 * @since 2.0
 */
class FontAwesome extends Service
{
    /**
     * @var string Локальный путь к файлам карт css классов шрифта.
     */
    public const PATH_MAP = '/Map';

    /**
     * @var string Префикс fa в версии 5 устарел. Новое значение по 
     * умолчанию - стиль fast solid и стиль fab для брендов.
     */
    public const STYLE_DEFAULT = 'fa';

    /**
     * @var string Cтиль Solid для "font-weight" 900.
     * 
     * Пример: <i class="fas fa-camera"></i>
     */
    public const STYLE_SOLID = 'solid';

    /**
     * @var string Cтиль Regular для "font-weight" 400.
     * 
     * Пример: <i class="far fa-camera"></i>
     */
    public const STYLE_REGULAR = 'regular';

    /**
     * @var string Cтиль Light для "font-weight" 300.
     * 
     * Пример: <i class="fal fa-camera"></i>
     */
    public const STYLE_LIGHT = 'light';

    /**
     * @var string Cтиль Brands для "font-weight" 400.
     * 
     * Пример: <i class="fab fa-font-awesome"></i>
     */
    public const STYLE_BRANDS = 'brands';

    /**
     * Префиксы css классов шрифта.
     * 
     * @var array
     */
    protected array $prefixes = [
        self::STYLE_DEFAULT => 'fa ',
        self::STYLE_SOLID   => 'fas ',
        self::STYLE_REGULAR => 'far ',
        self::STYLE_LIGHT   => 'fal ',
        self::STYLE_BRANDS  => 'fab '
    ];

    /**
     * Карта CSS классов шрифта.
     * 
     * @var array
     */
    protected array $map = [];

    /**
     * Имя загруженной карты.
     * 
     * @see FontAwesome::loadMap()
     * 
     * @var string
     */
    protected string $mapName = '';

    /**
     * Ассоциация имен с файлами карт.
     * 
     * @var array
     */
    protected array $mapNames = [
        'v5.0'     => 'v5_0.php',
        'v5.8 pro' => 'v5_8pro.php'
    ];

    /**
     * Возвращает имя файла по имени карты.
     * 
     * @param string $name Имя карты {@see $mapNames}.
     * @param bool $usePath Подставлять в имя файла путь.
     * 
     * @return false|string Если false, невозможно получить имя файла карты.
     */
    public function getFilename(string $name, bool $usePath = true): false|string
    {
        if ($usePath)
            $path = __DIR__ . self::PATH_MAP . '/';
        else
            $path = '';
        if (isset($this->mapNames[$name]))
            return $path . $this->mapNames[$name];
        else
            return false;
    }

    /**
     * Поиск файлов в каталоге стилей Font Awesome, для определения стилей к css классам шрифта.
     * 
     * @param array $dirs Название директорий с файлами иконок.
     *    Имеет вид:
     *    [
     *        FontAwesome::STYLE_SOLID   => '/path_to/solid',
     *        FontAwesome::STYLE_REGULAR => '/path_to/regular',
     *        FontAwesome::STYLE_LIGHT   => '/path_to/light',
     *        FontAwesome::STYLE_BRANDS  => '/path_to/brands'
     *    ]
     * 
     * @return array Возвратит все найденные стили для css классов шрифта.
     *    Имеет вид: ["icon_class" => ["solid",...],..."]
     */
    public function searchStylesFromFilenames(array $dirs): array
    {
        $iconClasses = [];
        foreach($dirs as $style => $dir) {
            $styles = $this->defineStyleFromFilenames($dir, $style);
            foreach($styles as $iconClass => $value) {
                if (!isset($iconClasses[$iconClass]))
                    $iconClasses[$iconClass] = [];
                $iconClasses[$iconClass][] = $style;
            }
        }
        return $iconClasses;
    }

    /**
     * Определение стилей из имен файлов.
     * 
     * @param string $path Директория с файлами иконок.
     * @param string $styleName Название стиля.
     * @param string $prefix Префикс стиля.
     * 
     * @return array Возвратит все найденные стили для css классов шрифта.
     *    Имеет вид: ["icon_class" => ["solid",...],..."]
     */
    public function defineStyleFromFilenames(string $path, string $styleName = '', string $prefix = 'fa-'): array
    {
        $iterator = new \FilesystemIterator($path,  \FilesystemIterator::SKIP_DOTS);
        $result = [];
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDir()) continue;
            $name = pathinfo ($fileinfo->getFilename(), PATHINFO_FILENAME);
            $result[$prefix . $name] = $styleName;
        }
        return $result;
    }

    /**
     * Добавление стилей для каждого ccs класса шрифта в карту {@see $map}.
     * 
     * @param array $styles Классы css шрифта со стилями.
     *    Имеет вид: ["icon_class" => ["solid",...],..."]
     * @param array $map Карта.
     * 
     * @return array Карта ccs классов шрифта.
     */
    public function addStylesToMap(array $styles, array $map): array
    {
        foreach ($map as $iconClass => $icon) {
            if (isset($styles[$iconClass]))
                $map[$iconClass]['style'] = $styles[$iconClass];
        }
        return $map;
    }

    /**
     * Выполняет загрузку файла карты в {@see $map}.
     * 
     * @param string $mapName Имя карты {@see $mapNames}.
     * 
     * @return bool Если false, невозможно получить имя файла карты.
     */
    public function loadMap(string $mapName): array|bool
    {
        if ($mapName === $this->mapName) {
            return $this->map;
        }
        if (($filename = $this->getFilename($mapName)) === false) {
            return false;
        }
        // проверка существования файла карты
        if (!file_exists($filename)) {
            return false;
        }
        $this->map = require($filename);
        return true;
    }

    /**
     * Сбросить загруженную карту {@see $map} и имя карты {@see mapName}.
     * 
     * @return $this
     */
    public function reset(): static
    {
        $this->map = [];
        $this->mapName = '';
        return $this;
    }

    /**
     * Возвращает карту {@see $map}.
     * 
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Возвращает параметры css класса шрифта.
     * 
     * @param string $iconClass Имя css класса шрифта.
     * 
     * @return null|array Если null, параметры css класса отсутствуют.
     */
    public function getIcon(string $iconClass): ?array
    {
        return $this->map[$iconClass] ?? null;
    }

    /**
     * Возвращает полное имя (как параметр) css класса шрифта.
     * 
     * @param string $iconClass Имя css класса шрифта.
     * 
     * @return null|string Если null, параметры css класса отсутствуют.
     */
    public function getIconName(string $iconClass): ?string
    {
        return $this->map[$iconClass]['name'] ?? null;
    }

    /**
     * Возвращает количество css классов шрифта.
     * 
     * @return int
     */
    public function total(): int
    {
        return sizeof($this->map);
    }

    /**
     * Возвращает css классы шрифта со значениями ключа $key.
     * 
     * @return array
     */
    public function getKeyValues(string $key): array
    {
        $result = [];
        foreach($this->map as $iconClass => $icon) {
            $result[$iconClass] = $icon[$key];
        }
        return $result;
    }

    /**
     * Возвращает полные имена css классов шрифта.
     * 
     * @return array
     */
    public function getReadableNames(): array
    {
        return $this->getKeyValues('name');
    }

    /**
     * Возвращает коды css классов шрифта.
     * 
     * @return array
     */
    public function getUnicodeKeys(): array
    {
        return $this->getKeyValues('code');
    }

    /**
     * Возвращает css классы шрифта.
     * 
     * @return array
     */
    public function getCssClasses(): array
    {
        return $this->getKeyValues('class');
    }

    /**
     * Выполняет сортировку карты {@see $map} по css классу шрифта.
     * 
     * @param bool $reverseOrder Обратный порядок сортировки.
     * 
     * @return $this
     */
    public function sortByIconClass(bool $reverseOrder = false): static
    {
        if ($reverseOrder)
            krsort($this->map);
        else
            ksort($this->map);
        return $this;
    }

    /**
     * Возвращает префикс ccs класса шрифта.
     * 
     * @param string $style Название стиля шрифта.
     * 
     * @return string
     */
    public function getStylePrefix(string $style): string
    {
        return $this->prefixes[$style] ?? $style;
    }

    /**
     * Возвращает тег с указанным css классом шрифта.
     * 
     * @param string $prefix Префикс css класса шрифта.
     * @param string $iconClass css класс шрифта.
     * 
     * @return string
     */
    public function renderIcon(string $prefix, string $iconClass): string
    {
        return "<i class=\"$prefix$iconClass\"></i>";
    }

    /**
     * Возвращает все теги css классов шрифта с одним из параметров.
     * 
     * @param string $style Стиль шрифта.
     * @param string $key Имя параметра ("class", "name", "code") css класса шрифта.
     * 
     * @return array
     */
    public function getRenderItems(string $style = self::STYLE_REGULAR, string $key = 'name'): array
    {
        $result = [];
        foreach($this->map as $iconClass => $icon) {
            foreach($icon['style'] as $istyle) {
                $prefix = $this->getStylePrefix($istyle);
                $result[] = array($prefix . $iconClass, $icon[$key], $this->renderIcon($prefix, $iconClass), $istyle);
            }
        }
        return $result;
    }

    /**
     * Разбор каскадной таблицы стилей шрифта Font Awesome в 
     * карту {@see $map} css классов шрифта.
     * 
     * @param string $filename Имя файла каскадной таблицы стилей шрифта Font Awesome.
     * 
     * @return array|false
     */
    public function parseCssToArray(string $filename): array|false
    {
        $content = file_get_contents($filename);
        if ($content === false)
            return false;

        $from = array(' ', ':before', '{', '"\\', '"', ';', '.', 'content:');
        $to   = array('', '', '', '', '', '', '', '=');
        $content = str_replace($from, $to, $content);
        $items = explode('}', $content);
        $map = [];
        foreach ($items as $item) {
            $faClasses = false;
            if (strpos($item, ',') !== false) {
                $faClasses = explode(',', $item);
                $count = sizeof($faClasses);
                $item = $faClasses[$count - 1];
            } else
                $count = 0;
    
            $faClass = explode('=', $item);
            if (sizeof($faClass) == 1) continue;
            $className = trim($faClass[0]);
            $name = ucfirst(str_replace(['fa-', '-'], ['', ' '], $className));
            $code      = trim($faClass[1]);
            $map[$className] = [
                'class' => $className,
                'code'  => $code,
                'name'  => $name
            ];
            if ($faClasses) {
                $alias = [];
                for($i = 0; $i < $count - 1; $i++) {
                    $classNameS = trim($faClasses[$i]);
                    $nameS      = ltrim($classNameS, 'fa-');
                    if (empty($nameS)) continue;
                    $map[$nameS] = [
                        'class' => $className,
                        'code'  => $code,
                        'name'  => $name,
                    ];
                    $alias[] = $nameS;
                }
                $map[$className]['alias'] = $alias;
            }
        }
        return $map;
    }

    /**
     * Возвращает массив в виде строки.
     * 
     * @param array $array Массив значений.
     * 
     * @return string
     */
    protected function arrayToStr(array $array): string
    {
        $str = 'array(';
        $first = true;
        foreach ($array as $value) {
            if ($first) {
                $first = false;
                $str .= "'";
            } else
                $str .= ", '";
            $str .= trim($value) .  "'";
        }
        return $str . ')';
    }

    /**
     * Возвращает массив ccs классов шрифта в виде текста файла карты.
     * 
     * @param array $array Массив ccs классов шрифта.
     * 
     * @return void
     */
    protected function arrayToText(array $array): void
     {
        echo "<?php \r\nreturn array(\r\n";
        foreach ($array as $iconClass => $icon) {
            $count = 20 - strlen($iconClass);
            if ($count < 0 )
                $count = 0;
            echo "        '$iconClass' " , str_repeat(' ', $count),  "=> array(";
            echo "'class' => '$iconClass', ";
            echo "'name' => '{$icon['name']}', ";
            echo "'code' => '{$icon['code']}'";
            if (!empty($icon['alias']))
                 echo ", 'alias' => ", $this->arrayToStr($icon['alias']);
            if (!empty($icon['style']))
                 echo ", 'style' => ", $this->arrayToStr($icon['style']);
            echo "),\r\n";
        }
        echo ");\r\n?>\r\n";
     }

    /**
     * Возвращает разобранный массива ccs классов шрифта в текст файла карты.
     * 
     * @param array $array Массив ccs классов шрифта.
     * 
     * @return string
     */
    public function parseArrayToFileMap(array $array): string
    {
        ob_start();

        $this->arrayToText($array);

        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}