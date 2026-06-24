<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session;

use Ge;
use SessionHandlerInterface;
use Ge\Stdlib\Service;
use Ge\Stdlib\CollectionTrait;

/**
 * Session обеспечивает управление данными сеанса пользователя и 
 * соответствующими настройками.
 * 
 * Session - это служба приложения, доступ к которой можно получить через `Ge::$app->session`.
 * 
 * Чтобы начать сеанс, вызовите {@see Session::open()};
 * Чтобы завершить и отправить данные сеанса, вызовите {@see Session::close()}; 
 * Чтобы уничтожить сеанс, вызовите {@see Session::destroy()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session
 * @since 2.0
 */
class Session extends Service implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use CollectionTrait;

    /**
     * {@inheritdoc}
     */
     protected bool $useUnifiedConfig = true;

   /**
     * Имя текущей сессии.
     * 
     * @var string
     */
    public string $name = 'PHPSID';

   /**
     * Автоматический старт сессии при инициализации службы.
     * 
     * @var bool
     */
    public bool $autoOpen = false;

   /**
     * Значение указывающее, следует ли использовать cookie для хранения 
     * идентификаторов сессии.
     * 
     * @see Session::setUseCookies()
     * 
     * @var bool
     */
    public bool $useCookies = true;

   /**
     * Устанавливает поддержку прозрачного sid.
     * 
     * @see Session::useTransparentSessionId()
     * 
     * @var bool
     */
    public bool $useTransparentSessionId = false;

   /**
     * Параметры устанавливаемые для cookie текущей сессии. 
     * 
     * Параметры могут иметь: 
     * - "lifetime" - время жизни cookie в секундах;
     * - "path" - путь, где размещена хранимая информация;
     * - "domain" - домен cookie;
     * - "secure" - cookie должны передаваться только через безопасные соединения;
     * - "httponly" - cookie могут быть доступны только по протоколу HTTP;
     * - "samesite" - управляет междоменной отправкой cookie.
     * 
     * @see https://www.php.net/manual/ru/function.session-set-cookie-params
     * 
     * @var array
     */
    public array $cookieParams = ['httponly' => true];

    /**
     * Объект, реализующий SessionHandlerInterface.
     * 
     * @var SessionHandlerInterface|string
     */
    public $handler = null;

   /**
     * Значение, показывающее, отправил ли текущий запрос идентификатор сессии.
     * 
     * @see Session::hasSessionId()
     * 
     * @var bool
     */
    protected bool $hasSessionId;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'session';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        // устанавливает поддержку прозрачного sid
        $this->useTransparentSessionId($this->useTransparentSessionId);
        // следует ли использовать cookie для хранения идентификаторов сессии
        $this->setUseCookies($this->useCookies);
        // автоматический старт сессии
        if ($this->autoOpen) {
            $this->open();
        }
        if ($this->isActive()) {
            $this->container = &$_SESSION;
        }
    }

    /**
     * Старт сессии.
     * 
     * @return void
     */
    public function open(): void
    {
        if ($this->isActive()) {
            return;
        }
        $this->setCookieParams();
        $this->registerHandler();

        session_name($this->name);
        $this->hasSessionId = true;
        GE_DEBUG ? session_start() : @session_start();
        $this->container = &$_SESSION;
    }

    /**
     * Записывает данные сессии и завершает её.
     * 
     * @see https://www.php.net/manual/ru/function.session-write-close
     * 
     * @return void
     */
    public function close(): void
    {
        if ($this->isActive()) {
            GE_DEBUG ? session_write_close() : @session_write_close();
        }
    }

    /**
     * Уничтожает сессию.
     * 
     * @return void
     */
    public function destroy(): void
    {
        if ($this->isActive()) {
            $this->close();
            GE_DEBUG ? session_start() : @session_start();
            $this->removeCookieParams();

            session_unset();
            session_destroy();
        }
    }

    /**
     * Проверяет, активна ли сессия.
     * 
     * @return bool Возвращает значение `true`, если сессия активна.
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Возвращает значение, показывающее, отправил ли текущий запрос идентификатор сессии.
     * 
     * Реализация по умолчанию будет проверять cookie и $ _GET, используя имя сесессиианса.
     * Если идентификатор сессии отправляется другим способом, необходимо переопределить этот
     * метод.
     * 
     * @return bool Отправил ли текущий запрос идентификатор сессии.
     */
    public function hasSessionId(): bool
    {
        if (!isset($this->hasSessionId)) {
            $name = $this->getSessionName();
            if (!empty($_COOKIE[$name]) && ini_get('session.use_cookies'))
                $this->hasSessionId = true;
            elseif (!ini_get('session.use_only_cookies') && ini_get('session.use_trans_sid'))
                $this->hasSessionId = Ge::$app->request->get($name) != '';
            else
                $this->hasSessionId = false;
        }
        return $this->hasSessionId;
    }

    /**
     * Возвращает идентификатор сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-id.php
     * 
     * @return string|false Возвращает идентификатор текущей сессии или пустую строку (''), 
     *     если нет текущей сессии (идентификатор текущей сессии не существует). В случае 
     *     неудачи возвращает `false`.
     */
    public function getId(): string|false
    {
        return session_id();
    }

    /**
     * Устанавилвает идентификатор сессии.
     * 
     * @see https://www.php.net/manual/en/function.session-id.php
     * 
     * @param string $value Идентификатор сессии.
     * 
     * @return void
     */
    public function setId(string $value): void
    {
        session_id($value);
    }

    /**
     * Генерирует и обновляет идентификатор текущей сессии.
     *
     * @see https://www.php.net/session_regenerate_id
     *
     * Этот метод не действует, если сессия не активна {@see Session::isActive}.
     * Обязательно вызовите {@see Session::open()} перед его вызовом.
     *
     * @param bool $deleteOldSession Определяет, удалять ли старый связанный файл с 
     *     сессией или нет. Не следует удалять старую сессию, если требуется избегать 
     *     состояния гонки из-за удаления или обнаруживать/избегать атак при перехвате сессии. 
     * 
     * @return void
     */
    public function regenerate(bool $deleteOldSession = false): void
    {
        if ($this->isActive()) {
            GE_DEBUG ? session_regenerate_id($deleteOldSession) : @session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Возвращает имя текущей сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-name.php
     * 
     * @return string|false Возвращает имя текущей сессии. Если задан параметр name, 
     *     имя текущей сессии поменяется и будет возвращено старое или `false` в случае 
     *     возникновения ошибки. 
     */
    public function getSessionName(): string|false
    {
        return session_name();
    }

    /**
     * Устанавливает имя для текущей сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-name.php
     * 
     * @param string $value Имя сессии для текущего сеанса должно быть буквенно-цифровой 
     *     строкой. По умолчанию это "PHPSESSID".
     * 
     * @return void
     */
    public function setName(string $value): void
    {
        session_name($value);
    }

    /**
     * Устанавливает имя текущего режима кеширования.
     *
     * @param string $cacheLimiter Режим кеширования ('public', 'private_no_expire', 
     *     'private', 'nocache').
     * 
     * @return void
     */
    public function setCacheLimiter(string $cacheLimiter): void
    {
        session_cache_limiter($cacheLimiter);
    }

    /**
     * Возвращает имя текущего режима кеширования.
     *
     * @return string|false Возвращает имя текущего режима кеширования ('public', 
     *    'private_no_expire', 'private', 'nocache'). В случае, если изменить 
     *    значение не удалось, возвращается `false`. 
     */
    public function getCacheLimiter(): string|false
    {
        return session_cache_limiter();
    }

    /**
     * Возвращает количество секунд, по истечении которых, данные будут считаться "мусором" 
     * и будут очищены.
     * 
     * Значение по умолчанию - 1440 секунд (или значение "session.gc_maxlifetime", 
     * установленное в php.ini).
     * 
     * @return int Количество секунд.
     */
    public function getTimeout(): int
    {
        return (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * Устанавливает количество секунд, по истечении которых, данные будут считаться "мусором" 
     * и будут очищены.
     * 
     * @param int $value Количество секунд.
     */
    public function setTimeout(int $value): void
    {
        ini_set('session.gc_maxlifetime', $value);
    }

    /**
     * Устанавливает или возвращает поддержку прозрачного sid.
     * 
     * Если значение указано, значит установит поддержку прозрачного sid или вернёт 
     * его значение.
     * 
     * @see https://www.php.net/manual/ru/session.configuration.php#ini.session.use-trans-sid
     * 
     * @param null|bool $value Включена ли поддержка прозрачного sid (по умолчачнию `null`).
     * 
     * @return bool
     */
    public function useTransparentSessionId(?bool $value = null): bool
    {
        if ($value === null) {
            return ini_get('session.use_trans_sid') == 1;
        }

        ini_set('session.use_trans_sid', $value ? '1' : '0');
        return true;
    }

    /**
     * Возвращает вероятность (процент) того, что будет запуск функции GC (сборщика мусора) 
     * при каждой инициализации сеанса.
     * 
     * @return float 
     */
    public function getGCProbability(): float
    {
        return (float) (ini_get('session.gc_probability') / ini_get('session.gc_divisor') * 100);
    }

    /**
     * Устанавливает вероятность (процент) того, что будет запуск функции GC (сборщика мусора) 
     * при каждой инициализации сеанса.
     * 
     * @param float $value Значение от 0 до 100.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Если значение не находится в диапазоне от 0 до 100.
     */
    public function setGCProbability(float $value): void
    {
        if ($value >= 0 && $value <= 100) {
            // percent * 21474837 / 2147483647 ≈ percent * 0.01
            ini_set('session.gc_probability', floor($value * 21474836.47));
            ini_set('session.gc_divisor', 2147483647);
        } else {
            throw new Exception\InvalidArgumentException('GCProbability must be a value between 0 and 100.');
        }
    }

    /**
     * Возвращает значение, указывающее, следует ли использовать cookie для хранения 
     * идентификаторов сессии.
     * 
     * @see Session::setUseCookies
     * 
     * @return bool|null Значение, указывающее, следует ли использовать cookie для 
     *     хранения идентификаторов сессии.
     */
    public function getUseCookies(): ?bool
    {
        if (ini_get('session.use_cookies') === '0')
            return false;
        else
        if (ini_get('session.use_only_cookies') === '1')
            return true;
        return null;
    }

    /**
     * Устанавливает значение, указывающее, следует ли использовать cookie для хранения 
     * идентификаторов сессии.
     *
     * Возможны три состояния:
     * - true: cookie и только cookie будут использоваться для хранения идентификаторов сессии.
     * - false: cookie не будут использоваться для хранения идентификаторов сессии.
     * - null: если возможно, cookie будут использоваться для хранения идентификаторов сессии; 
     * в противном случае будут использоваться другие механизмы (например, параметр GET)
     *
     * @param bool|null $value Значение, указывающее, следует ли использовать cookie для хранения 
     *     идентификаторов сессии.
     * 
     * @return void
     */
    public function setUseCookies(bool $value): void
    {
        if ($value === false) {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
        } else
        if ($value === true) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        } else {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '0');
        }
    }

    /**
     * Возвращает параметры cookie сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-get-cookie-params
     * 
     * @return array Параметры cookie сессии.
     */
    public function getCookieParams(): array
    {
        return array_merge(session_get_cookie_params(), array_change_key_case($this->cookieParams));
    }

    /**
     * Устанавливает параметры cookie сессии.
     * 
     * Этот метод вызывается, когда открывается {@see Session::open()} сессия.
     * 
     * @see https://www.php.net/manual/ru/function.session-set-cookie-params
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException Если параметры неполные.
     */
    protected function setCookieParams(): void
    {
        $data = $this->getCookieParams();
        if (isset($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly'])) {
            if (PHP_VERSION_ID >= 70300) {
                session_set_cookie_params($data);
            } else {
                if (!empty($data['samesite'])) {
                    $data['path'] .= '; samesite=' . $data['samesite'];
                }
                session_set_cookie_params($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);
            }
        } else {
            throw new Exception\InvalidArgumentException('СookieParams must contains elements: lifetime, path, domain, secure and httponly.');
        }
    }

    /**
     * Удаляет параметры cookie сессии.
     * 
     * Этот метод вызывается, когда уничтожается {@see Session::destroy()} сессия.
     * 
     * @return void
     */
    protected function removeCookieParams(): void
    {
        if ($this->getUseCookies()) {
            $data = $this->getCookieParams();
            setcookie($this->getSessionName(), '', time() - 42000, $data['path'], $data['domain'], $data['secure'], $data['httponly']);
        }
    }

    /**
     * Получает путь сохранения сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-save-path
     * 
     * @return string|false Возвращает путь текущей директории, используемой для хранения 
     *     данных сессии или `false` в случае возникновения ошибки. По умолчанию "/tmp".
     */
    public function getSavePath(): string|false
    {
        return session_save_path();
    }

    /**
     * Устанавливает путь сохранения сессии.
     * 
     * @see https://www.php.net/manual/ru/function.session-save-path
     * 
     * @param string $path Путь сохранения текущей сессии. Это может быть либо имя 
     *     каталога, либо псевдоним пути {@see Ge::getAlias()}.
     * 
     * @throws Exception\InvalidArgumentException Путь сохранения сеанса не является допустимым.
     */
    public function setSavePath(string $path): void
    {
        $path = Ge::getAlias($path);
        if (is_dir($path)) {
            session_save_path($path);
        } else {
            throw new Exception\InvalidArgumentException(sprintf('Session save path is not a valid directory: %s', $path));
        }
    }

   /**
     * Устанавливает CSRF токен сессии.
     * 
     * @param string $token
     * 
     * @return $this
     */
    public function setToken(string $token): static
    {
        $this->set(Ge::$app->request->csrfSessionName, $token);
        return $this;
    }

    /**
     * Возвращает CSRF токен сессии.
     * 
     * @return null|string Возвращает значение `null`, если CSRF токен отсутствует.
     */
    public function getToken(): ?string
    {
        return $this->get(Ge::$app->request->csrfSessionName);
    }

    /**
     * Возвращает имя CSRF токена сессии.
     * 
     * @return string Имя CSRF токена.
     */
    public function getTokenName(): string
    {
        return Ge::$app->request->csrfSessionName;
    }

    /**
     * Регистрирует обработчик сессии.
     * 
     * @return void
     */
    public function registerHandler(): void
    {
        if ($this->handler !== null) {
            if (!is_object($this->handler)) {
                $this->handler = Ge::createObject($this->handler);
            }

            if (!$this->handler instanceof SessionHandlerInterface) {
                throw new Exception\InvalidConfigException(
                    sprintf('%s::handler" must implement the SessionHandlerInterface.', get_class($this))
                );
            }
            GE_DEBUG ? session_set_save_handler($this->handler, true) : @session_set_save_handler($this->handler, true);
        }
    }
}
