<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\User;

use Ge;
use DateTimeZone;
use DateTimeInterface;
use Ge\Exception;
use Ge\Helper\Url;
use Ge\Stdlib\Service;
use Ge\I18n\Formatter;
use Ge\Session\Storage\StorageInterface;

/**
 * 
 * User - класс службы приложения, который управляет аутентификацией пользователя.
 * 
 * Обратите внимание, что класс определяет процесс аутентификации пользователя. 
 * Он не обрабатывает, как аутентифицировать пользователя. Логика аутентификации 
 * пользователя должна выполняться в классе, реализующий интерфейс {@see UserIdentityInterface}.
 * Также необходимо установить {@see User::$identityClass} с именем этого класса.
 * 
 * Доступ к службе можно получить через `Ge::$app->user`.
 * 
 * Вы можете изменить конфигурацию службы, добавив следующие параметры в раздел `user` 
 * файла конфигурации менеджера служб, как показано на примере:
 * ```php
 * // ...
 * 'user' => [
 *      'identityClass' => '\Web\User\UserIdentity', // класс должен реализовать UserIdentityInterface
 *      'storageClass'  => '\Ge\Session\Storage\SessionStorage', // класс должен реализовать StorageInterface
 *     // ...
 * ],
 * // ...
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\User
 * @since 2.0
 */
class User extends Service
{
    /**
     * @var string События возникает перед авторизацией пользователя.
     */
    public const EVENT_BEFORE_LOGIN = 'beforeLogin';

    /**
     * @var string События возникает после авторизации пользователя.
     */
    public const EVENT_AFTER_LOGIN = 'afterLogin';

    /**
     * @var string События возникает перед выходом пользователя.
     */
    public const EVENT_BEFORE_LOGOUT = 'beforeLogout';

    /**
     * @var string События возникает после выхода пользователя.
     */
    public const EVENT_AFTER_LOGOUT = 'afterLogout';

    /**
     * @var int Статус пользователя "активный".
     */
    public const STATUS_ACTIVE = 0;

    /**
     * @var int Статус пользователя "заблокирован".
     */
    public const STATUS_DISABLED = 1;

    /**
     * @var int Статус пользователя "временно заблокирован".
     */
    public const STATUS_TEMPORARILY_DISABLED = 2;

    /**
     * @var int Статус пользователя "ожидает проверки".
     */
    public const STATUS_WAITING = 3;

    /**
     * Форматтер.
     * 
     * @var Formatter
     */
    public Formatter $formatter;

    /**
     * Cтатусы пользователя.
     *
     * @var array
     */
    public array $statuses = [
        self::STATUS_ACTIVE               => 'Active',
        self::STATUS_DISABLED             => 'Disabled',
        self::STATUS_TEMPORARILY_DISABLED => 'Temporarily disabled',
        self::STATUS_WAITING              => 'Waiting'
    ];

    /**
     * Хранилища аутентификации пользователя.
     * 
     * @see User::setStorage()
     * @see User::getStorage()
     * 
     * @var StorageInterface
     */
    protected StorageInterface $_storage;

    /**
     * Прошёл ли пользователь идентификацию.
     * 
     * @see User::hasIdentity()
     * 
     * @var bool
     */
    protected bool $_hasIdentity;

    /**
     * Объект идентификации пользователя.
     * 
     * @see User::setIdentity()
     * @see User::getIdentity()
     * 
     * @var false|UserIdentityInterface
     */
    protected false|UserIdentityInterface $_identity;

    /**
     * Имя класс предоставляющий информацию о идентификации пользователя.
     * 
     * @var string
     */
    public string $identityClass;

    /**
     * Имя класс хранилище аутентификации пользователя.
     * 
     * @var string
     */
    public string $storageClass;

    /**
     * Имя идентификатора пользователя в хранилище.
     * 
     * @var string
     */
    public string $userIdParam = 'id';

    /**
     * URL-адрес страницы авторизации пользователя.
     * 
     * Используется для переадресации на страницу авторизации пользователя в {@see User::loginRequired()}.
     * Если указан массив, будет вызван {@see \Ge\Url\UrlManager::createUrl()} для 
     * создания соответствующего URL-адреса. Первый элемент массива должен быть маршрутом 
     * к странице авторизации, а остальные пары "имя => значение" являются параметрами GET, 
     * используемыми для создания URL-адреса входа. Например, 
     * ```php
     * ['page/login', '?' => ['param1' => 'value1'], '#' => 'name']
     * ```
     *
     * Если это свойство имеет значение `null`, то при вызове {@see User::loginRequired()} возникает 
     * исключение 403 HTTP.
     * 
     * @var string|array 
     */
    public $loginUrl = ['page/login'];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (!isset($this->identityClass)) {
            throw new Exception\InvalidConfigException('User::identityClass property must be set.');
        }
        if (!isset($this->storageClass)) {
            throw new Exception\InvalidConfigException('User::storageClass property must be set.');
        }
    }

    /**
     * Создаёт объект идентификации пользователя.
     * 
     * @return UserIdentityInterface
     */
    public function createIdentity(): UserIdentityInterface
    {
        return new $this->identityClass();
    }

    /**
     * Устанавливает объект идентификации пользователя.
     * 
     * @param false|UserIdentityInterface $identity Объект идентификации, связанный с 
     *     текущим вошедшим в систему пользователем. Если `false`, значит, что 
     *     текущий пользователь будет гостем без каких-либо связанных идентификаторов.
     * 
     * @return void
     */
    public function setIdentity(false|UserIdentityInterface $identity): void
    {
        $this->_identity = $identity;
    }

    /**
     * Возвращает объект идентификации пользователя связанный с текущим вошедшим в 
     * систему пользователем.
     * 
     * @see User::restoreIdentity()
     * 
     * @param bool $restore Если значение `true`, создаёт объект идентификации пользователя и 
     *     восстанавливает ему информацию аутентификации из хранилища.
     * 
     * @return false|UserIdentity|UserIdentityInterface Если `false`, хранилище аутентификации пользователя 
     *     не создано (пользователь авторизацию ранее не проходил) или оно не имеет информации 
     *     о пользователе. Иначе, создаёт объект идентификации пользователя.
     */
    public function getIdentity(bool $restore = true): false|UserIdentityInterface 
    {
        if (!isset($this->_identity)) {
            if ($restore)
                $this->_identity = $this->restoreIdentity();
            else
                $this->_identity = false;
        }
        return $this->_identity;
    }

    /**
     * Восстанавливает из хранилища информацию для созданного объекта идентификации 
     * пользователя.
     * 
     * @return false|UserIdentityInterface Если значение `false`, хранилище аутентификации 
     *     пользователя не создано (пользователь авторизацию ранее не проходил) или оно не 
     *     имеет информации о пользователе. Иначе, создаёт объект идентификации пользователя.
     */
    public function restoreIdentity(): false|UserIdentityInterface 
    {
        if ($this->hasIdentity()) {
            return new $this->identityClass($this->getStorage());
        }
        return false;
    }

    /**
     * Проверяет, прошел ли пользователь идентификацию.
     * 
     * @return bool Если значение `true`, пользователь успешно прошел идентификацию, т.к. 
     *     хранилище было успешно создано.
     */
    public function hasIdentity(): bool
    {
        if (!isset($this->_hasIdentity)) {
            if ($this->isStorageInit()) {
                $id = $this->getStorage()->get($this->userIdParam);
                $this->_hasIdentity = $id !== null;
            } else
                $this->_hasIdentity = false;
        }
        return $this->_hasIdentity;
    }

    /**
     * Возвращает идентификатор пользователя.
     * 
     * @return int|null Если значение `null`, пользователя не прошел идентификацию.
     */
    public function getId(): ?int
    {
        return $this->hasIdentity() ? $this->getIdentity()->getId() : null;

    }

    /**
     * Возвращает часовой пояс пользователя.
     * 
     * Если пользователь не имеет часовой пояс, то возвратит часовой пояс приложения.
     * 
     * @return DateTimeZone
     */
    public function getTimeZone(): DateTimeZone
    {
        static $timeZone;

        if ($timeZone === null) {
            $timeZone = new DateTimeZone(date_default_timezone_get());
        }
        return $timeZone;
    }

    /**
     * Возвращает имя пользователя.
     * 
     * @return string Если имя пользователя отсутствует или пользователь не прошел 
     *     идентификация, тогда вернёт пустую строку.
     */
    public function getUsername(): string
    {
        return $this->hasIdentity() ? $this->getIdentity()->getUsername() : '';
    }

    /**
     * Возвращает хранилище аутентификации пользователя.
     *
     * @return StorageInterface Хранилище аутентификации пользователя.
     */
    public function getStorage(): StorageInterface
    {
        if (!isset($this->_storage)) {
            $this->setStorage(new $this->storageClass());
        }
        return $this->_storage;
    }

    /**
     * Устанавливает хранилище аутентификации пользователя.
     *
     * @param StorageInterface $storage Хранилище аутентификации пользователя.
     * 
     * @return $this
     */
    public function setStorage(StorageInterface $storage): static
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Проверяет инициализацию хранилище аутентификации пользователя.
     * 
     * Инициализация выполнится, если хотя бы раз, был вызов хранилища.
     *
     * @return bool Если значение `false`, хранилище не было инициализировано.
     */
    public function isStorageInit(): bool
    {
        return $this->storageClass::isInit();
    }

    /**
     * Определяет, предоставлять ли доступ, проверяя роль и дочерние роли для разрешения.
     * 
     * @param string $permission Разрешение.
     * @param bool $extension Если значение `false`, проверяет разрешение модуля. 
     *     Иначе, расширение модуля (по умолчанию `false`).
     * 
     * @return boolean Если значение `true`, разрешение доступно, иначе нет.
     */
    public function isGranted(string $permission, bool $extension = false): bool
    {
        if ($this->hasIdentity()) {
            return $this->getIdentity()->isGranted($permission, $extension);
        }
        return false;
    }

    /**
     * Проверяет, является ли пользователь гостём.
     * 
     * В дальнейшем можно переопределить метод для определения конктретной стороны 
     * frontend или backend.
     * 
     * @see User::hasIdentity()
     *
     * @return bool Если значение `true`, пользователь является гостём.
     */
    public function isGuest(): bool
    {
        return !$this->hasIdentity();
    }

    /**
     * Возвращает все доступные разрешения пользователя.
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        if ($this->hasIdentity()) {
            return $this->getIdentity()->getPermissions();
        }
        return [];
    }

    /**
     * Возвращает доступные пользователю модули для указанного разрешения.
     * 
     * @param string|null $permission Разрешение. Если значение `null`, все доступные модули.
     * 
     * @return string Доступные пользователю модули через разделитель "," для 
     *     указанного разрешения.
     */
    public function getModules(?string $permission): string
    {
        if ($this->hasIdentity()) {
            return $this->getIdentity()->getModules(false, $permission);
        }
        return '';
    }

    /**
     * Возвращает идентификаторы модулей доступных (с разрешениями: "any", "view") для просмотра.
     * 
     * @param bool $toArray Если значение `false`, то результатом будут идентификаторы 
     *     через разделитель ",". Иначе, массив идентификаторов со значением `true` 
     *     (по умолчанию `false`).
     * 
     * @return string|array
     */
    public function getViewableModules(bool $toArray = false): string|array
    {
        if ($this->hasIdentity()) {
            return $this->getIdentity()->getViewableModules($toArray);
        }
        return $toArray ? [] : '';
    }

    /**
     * Этот метод вызывается перед входом пользователя в систему.
     * 
     * По умолчанию будет задействован триггер события {@see User::EVENT_BEFORE_LOGIN}}.
     * 
     * Если метод переопределён, убедитесь, что вызывается parent для 
     * инициализации события.
     * 
     * @param UserIdentityInterface $identity Информация о идентификации пользователя.
     * 
     * @return bool Следует ли пользователю продолжать вход в систему.
     */
    protected function beforeLogin(UserIdentityInterface $identity): bool
    {
        $error = false;
        $this->trigger(self::EVENT_BEFORE_LOGIN, ['identity' => $identity, 'error' => $error]);
        return $error === false;
    }

    /**
     * Этот метод вызывается после успешного входа пользователя в систему.
     * 
     * По умолчанию будет задействован триггер события {@see User::EVENT_AFTER_LOGIN}}.
     * 
     * Если метод переопределён, убедитесь, что вызывается parent для 
     * инициализации события.
     * 
     * @param UserIdentityInterface $identity Информация идентификации пользователя.
     * 
     * @return void
     */
    protected function afterLogin(UserIdentityInterface $identity): void
    {
        $this->trigger(self::EVENT_AFTER_LOGIN, ['identity' => $identity]);
    }

    /**
     * Этот метод вызывается при выходе пользователя из системы.
     * 
     * Выход пользователя выполняется через {@see User::Logout()}.
     * По умолчанию будет задействован триггер события {@see User::EVENT_BEFORE_LOGOUT}}.
     * 
     * Если метод переопределён, убедитесь, что вызывается parent для 
     * инициализации события.
     * 
     * @param UserIdentityInterface $identity Информация идентификации пользователя.
     */
    protected function beforeLogout(UserIdentityInterface $identity) :void
    {
        $this->trigger(self::EVENT_BEFORE_LOGOUT, ['identity' => $identity]);
    }

    /**
     * Этот метод вызывается сразу после выхода пользователя из системы.
     * 
     * Выход пользователя выполняется через {@see User::Logout()}.
     * По умолчанию будет задействован триггер события {@see User::EVENT_AFTER_LOGOUT}}.
     * 
     * Если метод переопределён, убедитесь, что вызывается parent для 
     * инициализации события.
     * 
     * @param UserIdentityInterface $identity Информация идентификации пользователя.
     * 
     * @return void
     */
    protected function afterLogout(UserIdentityInterface $identity): void
    {
        $this->trigger(self::EVENT_AFTER_LOGOUT, ['identity' => $identity]);
    }

    /**
     * Вход пользователя в систему.
     * 
     * @param UserIdentityInterface $identity Информация идентификации пользователя.
     * 
     * @return void
     */
    public function login(UserIdentityInterface $identity): void
    {
        if (!$this->beforeLogin($identity)) {
            return;
        }

        // TODO: 

        $this->afterLogin($identity);
    }

    /**
     * Перенаправляет браузер на страницу авторизации пользователя.
     * 
     * Убедитесь, что установлен {@see User::$loginUrl} так, чтобы браузер мог 
     * перенаправить на указанный URL-адрес авторизации после вызова этого метода.
     * 
     * Обратите внимание, если установлен {@see User::$loginUrl}, то вызов этого метода 
     * не завершает выполнение приложения.
     * 
     * @return void
     * 
     * @throws Exception\ForbiddenHttpException Исключение HTTP "Доступ запрещен", если {@see User::$loginUrl} 
     *     не установлен или перенаправление не возможно.
     */
    public function loginRequired(): void
    {
        $loginUrl = $this->loginUrl();
        if ($loginUrl !== Url::toRoute()) {
            if (isset(Ge::$app->controller)) {
                Ge::$app->controller
                    ->getResponse()
                        ->redirect($loginUrl);
                return;
            }
            if (isset(Ge::$app->response)) {
                Ge::$app->response
                    ->redirect($loginUrl);
                return;
            }
        }
        throw new Exception\ForbiddenHttpException(Ge::t('app', 'Login required'));
    }

    /**
     * Возвращает абсолютный URL-адрес страницы авторизации пользователя.
     * 
     * Если значение {@see User::$loginUrl} - массив, то будет вызван {@see \Ge\Url\UrlManager::createUrl()} 
     * для создания соответствующего URL-адреса.
     * 
     * @see User::$loginUrl
     * 
     * @return string|null Если значение `null`, маршрут к странице авторизации не указан. Иначе, 
     *     абсолютный URL-адрес страницы авторизации пользователя.
     */
    public function loginUrl(): ?string
    {
        if (is_array($this->loginUrl))
            return Ge::$services->getAs('urlManager')->createUrl($this->loginUrl);
        else
        if (is_string($this->loginUrl))
            return $this->loginUrl;
        else
            return null;
    }

    /**
     * Выход пользователя из системы.
     * 
     * @return void
     */
    public function logout(): void
    {
        /**
         * $this->beforeLogout($identity);
         * $this->afterLogout($identity);
         */
    }

    /**
     * Возвращает значение даты и времени, полученное в результате преобразования часового пояса UTC в часовой 
     * пояс пользователя.
     * 
     * @param DateTimeInterface|string|int $value Значение даты и времени в часовом поясе UTC.
     * @param DateTimeZone|string|null $fromTimeZone Часовой пояс форматируемого значения. 
     *    Если значение `null`, часовой пояс соответствует {@see \Ge\I18n\Formatter::$timeZoneUTC}
     *    (по умолчанию `null`).
     * @param string $format Формат преобразования значения (по умолчанию `null`). Если 
     *    формат `null`, используется текущий формат {@see \Ge\I18n\Formatter::$dateFormat}.  
     *    Формат может иметь значения 'short', 'medium', 'long' или 'full'.  
     *    Также, может быть пользовательский формат, указанный в ICU {@link http://userguide.icu-project.org/formatparse/datetime}.  
     *    В качестве альтернативы может быть строка с префиксом 'php:', представляющая 
     *    формат даты PHP {@link https://www.php.net/manual/ru/datetime.formats.date.php}.
     * 
     * @return mixed Значение даты и времени в часовом поясе пользователя.
     */
    public function formatDateTime(
        DateTimeInterface|string|int $value, 
        DateTimeZone|string|null $fromTimeZone,
        ?string $format = null
    ): mixed
    {
        static $userTZ;

        if (!isset($this->formatter)) {
            $this->formatter = Ge::$services->get('formatter');
        }

        if ($userTZ === null) $userTZ = $this->getTimeZone();
        return empty($value) ? $value : $this->formatter->toDateTimeZone($value, $format, false, $fromTimeZone, $userTZ);
    }
}
