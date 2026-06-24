<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\License;

use Ge;
use Ge\Stdlib\Service;
use Ge\Api\ServerCommand;

/**
 * License обеспечивает контроль над лицензионным ключом пользователя с помощью API 
 * сервера RosGear.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\License
 * @since 2.0
 */
class License extends Service
{
    /**
     * @var string Возможные статусы, получаемые в ответе от API сервера.
     */
    public const KEY_DISABLED      = 'LICENSE_KEY_DISABLED';
    public const KEY_ACTIVE        = 'LICENSE_KEY_ACTIVE';
    public const KEY_NOT_ACTIVE    = 'LICENSE_KEY_NOT_ACTIVE';
    public const KEY_NOT_AVAILABLE = 'LICENSE_KEY_NOT_AVAILABLE';
    public const KEY_NOT_FOUND     = 'LICENSE_KEY_NOT_FOUND';

    /**
     * Сообщения соответствующие статусам.
     * 
     * @var array
     */
    protected static array $statusMessages = [
        self::KEY_DISABLED      => 'License key is disabled',
        self::KEY_ACTIVE        => 'License key is activated',
        self::KEY_NOT_ACTIVE    => 'License key is not activated',
        self::KEY_NOT_AVAILABLE => 'License key is not available',
        self::KEY_NOT_FOUND     => 'License key not found'
    ];

    /**
     * Название файла лицензионного ключа.
     * 
     * @see License::setFilename()
     * @see License::getFilename()
     * 
     * @var string
     */
    protected string $filename;

    /**
     * Лицензионный ключ.
     * 
     * @see License::setKey()
     * @see License::getKey()
     * 
     * @var string
     */
    protected string $key;

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = false;

    /**
     * @see License::getApi()
     * 
     * @var ServerCommand
     */
    public ServerCommand $api;

    /**
     *  Возвращает сообщение соответствующие статусу.
     * 
     * @param string $status
     * 
     * @return string
     */
    public static function getStatusMessage(string $status): string
    {
        return self::$statusMessages[$status];
    }

    /**
     * Возвращает сообщения соответствующие полученному статусу API сервера.
     * 
     * @return array
     */
    public function getStatusMessages(): array
    {
        return [
            self::KEY_DISABLED      => 'License key is disabled',
            self::KEY_ACTIVE        => 'License key is activated',
            self::KEY_NOT_ACTIVE    => 'License key is not activated',
            self::KEY_NOT_AVAILABLE => 'License key is not available',
            self::KEY_NOT_FOUND     => 'License key not found'
        ];
    }

    /**
     * @param string $status
     * 
     * @return bool
     */
    public function hasStatus(string $status): bool
    {
        $messages = $this->getStatusMessages();
        return isset($messages[$status]);
    }

    /**
     * Возвращает сообщение соответствующие указанному статусу.
     * 
     * @param string $status Статус API сервера.
     * 
     * @return string|null Возвращает значение `null`, если указанный статус не 
     *     соответствует сообщению.
     */
    public function statusToMessage(string $status): ?string
    {
        $message = Ge::t('api', $status);
        return $message === $status ? $message : null;
    }

    /**
     * Возвращает объект выполнения запросов к API серверу.
     * 
     * @return ServerCommand
     */
    public function getApi(): ServerCommand
    {
        if (!isset($this->api)) {
            $this->api = new ServerCommand([
                'url' => rtrim(Ge::$app->config->apiMarketplaceServer['url'], '/') . '/license-key/'
            ]);
        }
        return $this->api;
    }

    /**
     * Возвращает информацию о лицензионном ключе.
     * 
     * @param string $key Лицензионном ключ.
     * 
     * @return false|array Возвращает значение `false`, если ошибка запроса.
     */
    public function getInfo(string $key): false|array
    {
        /** @var array|bool $response */
        $response = $this->getApi()->execute('info', [
            'post' => ['licenseKey' => $key]
        ]);
        if ($response !== false) {
            return array_merge(
                [
                    'key'             => '',
                    'name'            => '',
                    'created'         => '',
                    'periodFrom'      => '',
                    'periodTo'        => '',
                    'ipAddress'       => '',
                    'domain'          => '',
                    'edition'         => '',
                    'enabled'         => false,
                    'active'          => false,
                    'activeAfterDays' => 0,
                    'activeLeftDays'  => 0,
                ],
                $response['data'] ?? []
            );
        } else
            return false;
    }

    /**
     * Устанавливает имя файла конфигурации.
     * 
     * @param string $filename Имя файла конфигурации.
     * 
     * @return string
     */
    public function setFilename(string $filename)
    {
        return $this->filename = $filename;
    }

    /**
     * Возвращает имя файла конфигурации.
     * 
     * @return string
     */
    public function getFilename(): string
    {
        if (!isset($this->filename)) {
            $this->setFilename(Ge::getAlias('@config/.license.php'));
        }
        return $this->filename;
    }

    /**
     * Устанавливает лицензионный ключ.
     * 
     * @param string $key Лицензионный ключ.
     * 
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Возвращает лицензионный ключ.
     * 
     * @see License::load()
     * 
     * @return string
     */
    public function getKey()
    {
        if (!isset($this->key)) {
            $this->load();
        }
        return $this->key;
    }

    /**
     * Сохраняет текущий лицензионный ключ в файл.
     *
     * @return $this
     * 
     * @throws Exception\FileNotWriteException Нет записис в файл.
     */
    public function save(): static
    {
        $content = $this->getFilePattern($this->key);

        $filename = $this->getFilename();
        if (file_put_contents($filename, $content) === false) {
            throw new Exception\FileNotWriteException($filename);
        }
        return $this;
    }

    /**
     * Возвращает шаблон файла лицензионного ключа.
     *
     * @param null|string $key Лицензионный ключ.
     * 
     * @return string
     * 
     * @throws Exception\FileNotFoundException Файл лицензионного ключ не найден.
     * @throws Exception\FileNotReadException Файл лицензионного ключ не читается.
     */
    public function getFilePattern(?string $key = null): string
    {
        $filename = $this->getFilename();
        if (!file_exists($filename)) {
            throw new Exception\FileNotFoundException($filename);
        }

        $content = file_get_contents($filename, true);
        if ($content === false) {
            throw new Exception\FileNotReadException($filename);
        }

        if ($key !== null) {
            if (($start = mb_strpos($content, 'return')) !== false) {
                $replace = mb_substr($content, $start);
                return str_replace($replace, "return '$key';", $content);
            }
            return "<?php return '$key'; ?>";
        }
        return $content;
    }

    /**
     * Загрузка файла лицензии.
     * 
     * @see License::$key
     * 
     * @return $this
     * 
     * @throws Exception\FileNotFoundException Файл лицензионного ключ не найден.
     */
    public function load(): static
    {
        $filename = $this->getFilename();
        if (!file_exists($filename)) {
            throw new Exception\FileNotFoundException($filename);
        }

        $this->key = require($filename);
        return $this;
    }
}