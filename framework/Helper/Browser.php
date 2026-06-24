<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

/**
 *  Вспомогательный класс Browser, определяет версию браузера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Browser
{
    /**
     * Версия пользовательского агента.
     * 
     * Заголовок запроса в виде строки с характеристиками, по которым сервера и 
     * сетевые узлы могут определить тип приложения, операционную систему, производителя.
     * 
     * Шаблон: '<product> / <product-version> <comment>'.
     * 
     * @see Browser::userAgent()
     * 
     * @var string
     */
    public static string $userAgent;

    /**
     * Определённый бразуер из версии пользовательского агента.
     * 
     * @see Browser::browserDetect()
     * 
     * @var false|array{family:string, version:string}
     */
    protected static $browser;

    /**
     * Определённая ОС из версии пользовательского агента.
     * 
     * @see Browser::platformDetect()
     * 
     * @var false|array{version:string, family:string, short:string, pattern:string}
     */
    protected static $platform;

    /**
     * Определённый бот из версии пользовательского агента.
     * 
     * @see Browser::botDetect()
     * 
     * @var false|array{name:string, version:string, family:string, icon:string, pattern:string}
     */
    protected static $bot;

    /**
     * Логотипы семейств браузеров.
     *
     * @var array
     */
    public static array $browserLogos = [
        'Android Browser'    => 'android',
        'BlackBerry Browser' => 'blackberry',
        'Baidu'              => 'baidu',
        'Chrome'             => 'chrome',
        'Firefox'            => 'firefox',
        'Internet Explorer'  => 'ie',
        'Nokia Browser'      => 'nokia',
        'Opera'              => 'opera',
        'Safari'             => 'safari',
        'Yandex'             => 'yandex'
    ];

    /**
     * Семейства браузеров.
     *
     * @var array
     */
    public static array $browserFamilies = [
        'Android Browser',
        'BlackBerry Browser',
        'Baidu',
        'Amiga',
        'Chrome',
        'Firefox',
        'Internet Explorer',
        'Konqueror',
        'NetFront',
        'NetSurf',
        'Nokia Browser',
        'Opera',
        'Safari',
        'Sailfish Browser',
        'Yandex'
    ];

    /**
     * Логотипы семейств операционных систем.
     *
     * @var array
     */
    public static array $osLogos = [
        'Android'        => 'android',
        'Apple TV'       => 'apple',
        'BlackBerry'     => 'blackberry',
        'Brew'           => 'brew',
        'BeOS'           => 'be',
        'Chrome OS'      => 'chrome',
        'Firefox OS'     => 'firefox',
        'Gaming Console' => 'console',
        'iOS'            => 'apple',
        'GNU/Linux'      => 'linux',
        'Mac'            => 'mac',
        'Mobile Gaming Console' => 'console',
        'Symbian'        => 'symbian',
        'Unix'           => 'unix',
        'Windows'        => 'windows'
    ];

    /**
     * Семейства операционных систем, сопоставляемые с короткими кодами.
     *
     * @var array
     */
    public static array $osFamilies = [
        'Android',
        'AmigaOS',
        'Apple TV',
        'BlackBerry',
        'Brew',
        'BeOS',
        'Chrome OS',
        'Firefox OS',
        'Gaming Console',
        'Google TV',
        'IBM',
        'iOS',
        'RISC OS',
        'GNU/Linux',
        'Mac',
        'Mobile Gaming Console',
        'Real-time OS',
        'Other Mobile',
        'Symbian',
        'Unix',
        'WebTV',
        'Windows',
        'Windows Mobile',
        'Other Smart TV'
    ];

    /**
     * Устанавливает версию пользовательского агента.
     *
     * @param string $userAgent Версия пользовательского агента.
     * 
     * @return string
     */
    public static function setUserAgent(string $userAgent): string
    {
        return static::$userAgent = $userAgent;
    }

    /**
     * Возвращает версию пользовательского агента.
     *
     * @return string
     */
    public static function userAgent(): string
    {
        if (!isset(static::$userAgent)) {
            static::$userAgent =  empty($_SERVER['HTTP_USER_AGENT']) ? '' : (string) $_SERVER['HTTP_USER_AGENT'];
        }
        return static::$userAgent;
    }

    /**
     * Определяет версию и семейство браузера, ОС и бота по указанной версии пользовательского 
     * агента.
     * 
     * @see Browser::browserDetect()
     * @see Browser::platformDetect()
     * @see Browser::botDetect()
     * 
     * @param string $userAgent
     * 
     * @return void
     */
    public static function detect(string $userAgent = ''): void
    {
        static::browserDetect($userAgent);
        static::platformDetect($userAgent);
        static::botDetect($userAgent);
    }

    /**
     * Определяет версию и семейство браузера по указанной версии пользовательского 
     * агента.
     * 
     * @param string $userAgent Версия пользовательского агента.
     * 
     * @return false|array{family:string, version:string} Возвращает значение `false`, 
     *     если невозможно определить версию браузера.
     */
    public static function browserDetect(string $userAgent = ''): false|array
    {
        $notSpecified  = $userAgent === '';
        if ($notSpecified) {
            if (static::$browser) {
                return static::$browser;
            }
            $userAgent = static::userAgent();
        } else {
            static::setUserAgent($userAgent);
        }

        $browser = static::browserParse($userAgent);

        if ($browser === false) {
            return false;
        }
        $version = join(
            '.',
            array_filter((array) $browser['version'],
                function ($x) {
                    return !is_null($x);
                }
            )
        );
        $result = ['family' => $browser['family'], 'version' => $version];
        if ($notSpecified) {
            static::$browser = $result;
        }
        return $result;
    }

   /**
     * Определяет версию и семейство ОС по указанной версии пользовательского 
     * агента.
     * 
     * @param string $userAgent Версия пользовательского агента.
     * 
     * @return false|array{version:string, family:string, short:string, pattern:string} 
     *     Возвращает значение `false`, если невозможно определить версию браузера.
     */
    public static function platformDetect(string $userAgent = ''): false|array
    {
        $notSpecified  = $userAgent === '';
        if ($notSpecified) {
            if (isset(static::$platform)) {
                return static::$platform;
            }
            $userAgent = static::userAgent();
        } else
            static::setUserAgent($userAgent);

        $result = false;
        $versions = static::getPlatformFilter();
        foreach ($versions as $attr) {
            if (preg_match('/' . $attr['pattern'] . '/', $userAgent)) {
                $result = $attr;
                break;
            }
        }
        if ($notSpecified) {
            static::$platform = $result;
        }
        return $result;
    }

   /**
     * Определяет Бота по указанной версии пользовательского агента.
     * 
     * @param string $userAgent Версия пользовательского агента.
     * 
     * @return false|array{name:string, version:string, family:string, icon:string, pattern:string} 
     *     Возвращает значение `false`, если невозможно определить версию браузера.
     */
    public static function botDetect(string $userAgent = ''): false|array
    {
        $notSpecified = $userAgent === '';
        if ($notSpecified) {
            if (isset(static::$bot)) {
                return static::$bot;
            }
            $userAgent = static::userAgent();
        } else
            static::setUserAgent($userAgent);

        $result = false;
        $versions = static::getBotFilter();
        foreach ($versions as $attr) {
            if (preg_match('/' . $attr['pattern'] . '/', $userAgent)) {
                $result = $attr;
                break;
            }
        }
        if ($notSpecified) {
            static::$bot = $result;
        }
        return $result;
    }

    /**
     * Определяет версию и семейство браузера по указанной версии пользовательского 
     * агента.
     * 
     * @param string $userAgent Версия пользовательского агента.
     * 
     * @return false|array{family:string, version:string} Возвращает значение `false`, 
     *     если невозможно определить версию браузера.
     */
    public static function browserParse(string $userAgent): false|array
    {
        $filter = static::getBrowserFilter();
        foreach ($filter as $browser) {
            $version = [];
            if (strpos($userAgent, $browser['needle']) !== false) {
                preg_match($browser['pattern'], $userAgent, $versionMatch);
                foreach ($browser['match'] as $index => $matchIndex) {
                    isset($versionMatch[$matchIndex]) && $version[$index] = (int) $versionMatch[$matchIndex];
                }
                return ['family' => $browser['family'], 'version' => $version];
            }
        }
        return false;
    }

    /**
     * Определяет имя браузера.
     * 
     * Имя браузера: '<family> <version>'.
     * 
     * @see Browser::browserDetect()
     * 
     * @return string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserName(): string
    {
        $browser = static::browserDetect();
        return $browser ? $browser['family'] . ($browser['version'] ? ' ' . $browser['version'] : '') : '';
    }

    /**
     * Определяет семейтсво браузера.
     * 
     * @see Browser::browserDetect()
     * 
     * @return string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserFamily(): string
    {
        $browser = static::browserDetect();
        return $browser ? $browser['family'] : '';
    }

    /**
     * Определяет версию браузера.
     * 
     * @see Browser::browserDetect()
     * 
     * @return string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserVersion(): string
    {
        $browser = static::browserDetect();
        return $browser && $browser['version'] ? $browser['version'] : '';
    }

    /**
     * Определяет основную версию браузера.
     * 
     * @see Browser::browserDetect()
     * 
     * @return int|string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserVersionMajor(): int|string
    {
        $browser = static::browserDetect();
        if ($browser && $browser['version']) {
            $version = explode('.', $browser['version']);
            return (int) ($version[0] ?? 0);
        } else
            return '';
    }

    /**
     * Определяет второстепенную версию браузера.
     * 
     * @see Browser::browserDetect()
     * 
     * @return int|string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserVersionMinor(): int|string
    {
        $browser = static::browserDetect();
        if ($browser && $browser['version']) {
            $version = explode('.', $browser['version']);
            return (int) ($version[1] ?? 0);
        } else
            return '';
    }

    /**
     * Определяет версию патча браузера.
     * 
     * @see Browser::browserDetect()
     * 
     * @return int|string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function browserVersionPatch(): int|string
    {
        $browser = static::browserDetect();
        if ($browser && $browser['version']) {
            $version = explode('.', $browser['version']);
            return (int) ($version[2] ?? 0);
        } else
            return '';
    }

    /**
     * Определяет, принадлежит ли браузер указанному семейству.
     *
     * @param string $family Семейство браузера.
     * 
     * @return bool
     */
    public static function isBrowser(string $family): bool
    {
        return static::browserFamily() === $family;
    }

    /**
     * Определяет, принадлежит ли браузер семейству Firefox.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isFirefox(): bool
    {
        return static::isBrowser('Firefox');
    }

    /**
     * Определяет, принадлежит ли браузер семейству Opera.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isOpera(): bool
    {
        return static::isBrowser('Opera');
    }

    /**
     * Определяет, принадлежит ли браузер семейству Chrome.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isChrome(): bool
    {
        return static::isBrowser('Chrome');
    }

    /**
     * Определяет, принадлежит ли браузер семейству Safari.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isSafari(): bool
    {
        return static::isBrowser('Safari');
    }

    /**
     * Определяет, принадлежит ли браузер семейству Internet Explorer.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isIE(): bool
    {
        return static::isBrowser('Internet Explorer');
    }

    /**
     * Определяет, принадлежит ли ОС указанному семейству.
     *
     * @param string $family Семейство ОС.
     * 
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isPlatform(string $family): bool
    {
        return static::platformFamily() === $family;
    }

    /**
     * Определяет, принадлежит ли ОС семейству Windows.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isWindows(): bool
    {
        return static::isPlatform('Windows');
    }

    /**
     * Определяет, принадлежит ли ОС семейству Linux.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isLinux(): bool
    {
        return static::isPlatform('Linux');
    }

    /**
     * Определяет, принадлежит ли ОС семейству Mac OS.
     *
     * @return bool Если значение `true`, то принадлежит.
     */
    public static function isMac(): bool
    {
        return static::isPlatform('Mac OS');
    }

    /**
     * Определяет, был ли сделан запрос ботом.
     *
     * @return bool
     */
    public static function isBot(): bool
    {
        $bot = static::botDetect();
        return $bot !== false;
    }

    /**
     * Определяет имя ОС.
     * 
     * Имя ОС: '<family> <version>'.
     * 
     * @see Browser::platformDetect()
     * 
     * @return string Если ОС не удалось определить, то возвратит значение ''.
     */
    public static function platformName(): string
    {
        $platform = static::platformDetect();
        if ($platform) {
            if (isset($platform['name']))
                return $platform['name'];
            else {
                return  $platform['family'] . (empty($platform['version']) ? '' : ' ' . $platform['version']);
            }
        } else
            return '';
    }

    /**
     * Определяет семейтсво ОС.
     * 
     * @see Browser::platformDetect()
     * 
     * @return string Если ОС не удалось определить, то возвратит значение ''.
     */
    public static function platformFamily(): string
    {
        $platform = static::platformDetect();
        return $platform ? $platform['family'] : '';
    }

    /**
     * Определяет версию ОС.
     * 
     * @see Browser::platformDetect()
     * 
     * @return string Если ОС не удалось определить, то возвратит значение ''.
     */
    public static function platformVersion(): string
    {
        $platform = static::platformDetect();
        return $platform['version'] ?? '';
    }

    /**
     * Определяет основную версию ОС.
     * 
     * @see Browser::platformDetect()
     * 
     * @return int|string Если браузер не удалось определить, то возвратит значение ''.
     */
    public static function platformVersionMajor(): string
    {
        $platform = static::platformDetect();
        if ($platform && $platform['version']) {
            $version = explode('.', $platform['version']);
            return $version[0] ?? '';
        } else
            return '';
    }

    /**
     * Определяет второстепенную версию ОС.
     * 
     * @see Browser::platformDetect()
     * 
     * @return int|string Если ОС не удалось определить, то возвратит значение ''.
     */
    public static function platformVersionMinor(): string
    {
        $platform = static::platformDetect();
        if ($platform && $platform['version']) {
            $version = explode('.', $platform['version']);
            return $version[1] ?? '';
        } else
            return '';
    }

    /**
     * Определяет версию патча ОС.
     * 
     * @see Browser::browserDetect()
     * 
     * @return int|string Если ОС не удалось определить, то возвратит значение ''.
     */
    public static function platformVersionPatch(): string
    {
        $platform = static::platformDetect();
        if ($platform && $platform['version']) {
            $version = explode('.', $platform['version']);
            return $version[2] ?? '';
        } else
            return '';
    }

    /**
     * Возвращает семейства браузеров.
     * 
     * @see Browser::getBrowserFilter()
     * 
     * @return array<string, true>
     */
    public static function getBrowserFamilies(): array
    {
        $families = [];
        $filter = static::getBrowserFilter();
        foreach ($filter as $browser) {
            $families[$browser['family']] = true;
        }
        return $families;
    }

    /**
     * Возвращает семейства ОС.
     * 
     * @see Browser::getPlatformFilter()
     * 
     * @return array<string, true>
     */
    public static function getOsFamilies(): array
    {
        $families = [];
        $filter = static::getPlatformFilter();
        foreach ($filter as $os) {
            $families[$os['family']] = true;
        }
        return $families;
    }

    /**
     * Возвращает параметры фильтрации браузеров.
     * 
     * @return array<string, array{family:string, needle:string, pattern:string, match:array}>
     */
    public static function getBrowserFilter(): array
    {
        return [
            //http://www.useragentstring.com/pages/Opera/
            [
                'family'  => 'Opera',
                'needle'  => 'Opera/',
                'pattern' => '#Version/(\d{1,3})\.(\d{1,2})#i',
                'match'   => [1, 2]
            ],
            // http://dev.opera.com/articles/view/opera-ua-string-changes/
            [
                'family'  => 'Opera',
                'needle'  => 'Opera/',
                'pattern' => '#Opera/(\d{1,3})\.(\d{1,2})#i',
                'match'   => [1, 2]
            ],
            // http://www.useragentstring.com/pages/Opera/
            [
                'family'  => 'Opera',
                'needle'  => 'Opera ',
                'pattern' => '#Opera (\d{1,3})\.(\d{1,2})#i',
                'match'   => [1, 2]
            ],
            // http://www.useragentstring.com/pages/Firefox/
            [
                'family'  => 'Firefox',
                'needle'  => 'Firefox/',
                'pattern' => '#Firefox/(\d{1,3})\.(\d{1,2})(\.(\d{1,2})(\.(\d{1,2}))?)?#i',
                'match'   => [1, 2, 4, 6]
            ],
            // http://www.useragentstring.com/pages/Internet%20Explorer/
            [
                'family'  => 'Internet Explorer',
                'needle'  => 'MSIE ',
                'pattern' => '#MSIE (\d{1,3})\.(\d{1,2})#i',
                'match'   => [1, 2]
            ],
            // http://www.useragentstring.com/pages/Iceweasel/
            [
                'family'  => 'Firefox',
                'needle'  => 'Iceweasel/',
                'pattern' => '#Iceweasel/(\d{1,3})\.(\d{1,2})(\.(\d{1,2})(\.(\d{1,2}))?)?#i',
                'match'   => [1, 2, 4, 6]
            ],
            // http://www.useragentstring.com/pages/Chrome/
            [
                'family'  => 'Chrome',
                'needle'  => 'Chrome/',
                'pattern' => '#Chrome/(\d{1,3})\.(\d{1,4})\.(\d{1,4}).(\d{1,4})#i',
                'match'   => [1, 2, 3, 4]
            ],
            // http://www.useragentstring.com/pages/Safari/
            [
                'family'  => 'Safari',
                'needle'  => 'Safari/',
                'pattern' => '#Safari/(\d{1,3})\.(\d{1,2})(\.(\d{1,2}))?#i',
                'match'   => [1, 2, 4]
            ]
        ];
    }

    /**
     * Возвращает параметры фильтрации ОС.
     * 
     * @return array<string, array{version:string, family:string, short:string, pattern:string}>
     */
    public static function getPlatformFilter(): array
    {
        return [
            'Windows 3.11' => [
                'version' => '3.11',
                'family'  => 'Windows',
                'short'   => 'win-3-11',
                'pattern' => 'Win16'
            ],
            'Windows 95' => [
                'version' => '95',
                'family'  => 'Windows',
                'short'   => 'win-95',
                'pattern' => '(Windows 95)|(Win95)|(Windows_95)'
            ],
            'Windows 98' => [
                'version' => '98',
                'family'  => 'Windows',
                'short'   => 'win-98',
                'pattern' => '(Windows 98)|(Win98)'
            ],
            'Windows 2000' => [
                'version' => '2000',
                'family'  => 'Windows',
                'short'   => 'win-2000',
                'pattern' => '(Windows NT 5.0)|(Windows 2000)'
            ],
            'Windows XP' => [
                'version' => 'XP',
                'family'  => 'Windows',
                'short'   => 'win-xp',
                'pattern' => '(Windows NT 5.1)|(Windows XP)'
            ],
            'Windows 2003' => [
                'version' => '2003',
                'family'  => 'Windows',
                'short'   => 'win-2003',
                'pattern' => '(Windows NT 5.2)'
            ],
            'Windows 7' => [
                'version' => '7',
                'family'  => 'Windows',
                'short'   => 'win-7',
                'pattern' => 'Windows NT 6.1'
            ],
            'Windows 8.1' => [
                'version' => '8.1',
                'family'  => 'Windows',
                'short'   => 'win-8',
                'pattern' => 'Windows NT 6.3'
            ],
            'Windows 8' => [
                'version' => '8',
                'family'  => 'Windows',
                'short'   => 'win-8',
                'pattern' => 'Windows NT 6.2'
            ],
            'Windows 10' => [
                'version' => '10',
                'family'  => 'Windows',
                'short'   => 'win-8',
                'pattern' => 'Windows NT 10.0'
            ],
            'Windows Vista' => [
                'version' => 'Vista',
                'family'  => 'Windows',
                'short'   => 'win-vista',
                'pattern' => 'Windows NT 6.0'
            ],
            'Windows NT 4.0' => [
                'version' => 'NT 4.0',
                'family'  => 'Windows',
                'short'   => 'win-nt-4',
                'pattern' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)'
            ],
            'Windows ME' => [
                'version' => 'ME',
                'family'  => 'Windows',
                'short'   => 'win-me',
                'pattern' => 'Windows ME'
            ],
            'OpenBSD' => [
                'name'    => 'OpenBSD',
                'family'  => 'Unix',
                'short'   => 'openbsd',
                'pattern' => 'OpenBSD'
            ],
            'SunOS' => [
                'name'    => 'SunOS',
                'family'  => 'Unix',
                'short'   => 'sun-os',
                'pattern' => 'SunOS'
            ],
            'Linux' => [
                'family'  => 'Linux',
                'short'   => 'linux',
                'pattern' => '(Linux)|(X11)'
            ],
            'Macintosh' => [
                'name'    => 'Macintosh',
                'family'  => 'Mac OS',
                'short'   => 'mac-os',
                'pattern' => '(Mac_PowerPC)|(Macintosh)'
            ],
            'QNX' => [
                'family'  => 'Posix',
                'short'   => 'qnx',
                'pattern' => 'QNX'
            ],
            'BeOS' => [
                'family'  => 'BeOS',
                'short'   => 'be-os',
                'pattern' => 'BeOS'
            ],
            'OS/2' => [
                'family'  => 'OS/2',
                'short'   => 'os-2',
                'pattern' => 'OS\/2'
            ],
            'Mac OS' => [
                'family'  => 'Mac OS',
                'short'   => 'mac-os',
                'pattern' => 'Mac OS'
            ]
        ];
    }

    /**
     * Возвращает параметры фильтрации ботов.
     * 
     * @return array<string, array{name:string, version:string, family:string, icon:string, pattern:string}>
     */
    public static function getBotFilter(): array
    {
        return [
            'Search Bot' => [
                'name'    => 'Search Bot',
                'version' => '',
                'family'  => 'Bot',
                'icon'    => 'bot',
                'pattern' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)'
            ]
        ];
    }
}