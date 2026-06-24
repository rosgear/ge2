<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mail;

use Ge;
use Ge\Stdlib\Service;
use Ge\Stdlib\AdapterTrait;

/**
 * Электронная почта.
 * 
 * Mail - это служба приложения, доступ к которой можно получить через `Ge::$app->mail`.
 * 
 * Служба отправляет почту с помощью указанного адаптера. Каждый адаптер службы унаследован от расширения 
 * установленного в директорию (VENDOR_PATH) сторонних библиотек.
 * 
 * Каждому адаптеру передаются параметры конфигурации службы.
 * 
 * Доступ к экземпляру класса Mail можно получить через "Ge::$app->mail".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log
 * @since 2.0
 */
class Mail extends Service
{
    use AdapterTrait;

    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Псевдонимы имён классов адаптеров.
     * Значение устанавливается параметров "adapterClasses" в файле конфигурации 
     * менеджера служб (т.к. является свойством AdapterTrait) в виде пары "псевдоним => имя класса".
     * 
     * @var array
     * 
     * public array $adapterClasses = [];
     */

    /**
     * Адаптер по умолчанию.
     * Значение устанавливается параметров "defaultAdapter" в файле конфигурации 
     * менеджера служб (т.к. является свойством AdapterTrait).
     * 
     * @var string
     * 
     * public string $defaultAdapter = '';
     */

    /**
     * Набор символов сообщения.
     * Параметры:  "us-ascii", "iso-8859-1", "utf-8".
     * 
     * @var string
     */
    public string $charset = 'iso-8859-1';

    /**
     * Тип кодирования письма.
     * Параметры: "8bit", "7bit", "binary", "base64" и "quoted-printable".
     *
     * @var string
     */
    public string $encoding = '8bit';

    /**
     * Метод используемый для отправки почты.
     * Параметры: "mail", "sendmail" или "smtp".
     *
     * @var string
     */
    public string $mailer = 'mail';

    /**
     * Либо одно имя хоста, либо несколько имен хостов, разделенных точкой с запятой.
     * Вы также можете указать другой порт для каждого хоста, используя следующий формат: "имя хоста[:порт],имя хоста[:порт]"
     * Вы также можете указать тип шифрования, например: "tls://smtp1.example.com:587,ssl://smtp2.example.com:465".
     * 
     * @var string
     */
    public string $host = 'localhost';

    /**
     * Порт SMTP-сервера по умолчанию.
     *
     * @var int
     */
    public int $port = 25;

    /**
     * Шифрование SMTP-соединения.
     * Параметры: "tls", "ssl".
     *
     * @var string
     */
    public string $SMTPSecure = '';

    /**
     * Автоматическое шифрование TLS, в том случаи, если сервер поддерживает его.
     * Игнорирует свойство {@see Mail::$SMTPSecure}.
     *
     * @var bool
     */
    public bool $SMTPAutoTLS = true;

    /**
     * Использовать SMTP-аутентификацию.
     * Использует свойства имени пользователя и пароля.
     *
     * @see Mail::$username
     * @see Mail::$password
     *
     * @var bool
     */
    public bool $SMTPAuth = false;

    /**
     * Сохранять SMTP-соединение открытым после каждого сообщения.
     *
     * @var bool
     */
    public bool $SMTPKeepAlive = false;

    /**
     * Включить отладку.
     *
     * @var bool
     */
    public bool $debug = false;

    /**
     * Имя пользователя для SMTP-соединения.
     *
     * @var string
     */
    public string $username = '';

    /**
     * Пароль для SMTP-соединения.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Механизм SMTP-аутентификации.
     * Параметры: "CRAM-MD5", "LOGIN", "PLAIN", "XOAUTH2".
     *
     * @var string
     */
    public string $authType = '';

    /**
     * Заголовок X-Mailer.
     *
     * @var string
     */
    public string $xmailer = '';

    /**
     * E-mail адрес отправителя, который выполняет рассылку уведомлений, 
     * но принимать письма не может.
     *
     * @var string
     */
    public string $noreplyAddress = '';

    /**
     * E-mail адрес ответчика принимает входящие письма (уведомления) и выполняет 
     * функции оффициального почтового адресата.
     *
     * @var string
     */
    public string $fromAddress = '';

    /**
     * Имя ответчика, принимающий входящие письма (уведомления).
     *
     * @var string
     */
    public string $fromName = '';

    /**
     * Определяет, является ли текст письма в формате HTML.
     *
     * @var bool
     */
    public bool $isHtml = false;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'mail';
    }

    /**
     * Возвращает ошибку полученную в последнем запросе.
     *
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->getAdapter()->getError();
    }

    /**
     * Проверяте, была ли ошибка в последнем запросе.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->getAdapter()->isError();
    }

    /**
     * Возвращает заголовок X-Mailer по умолчанию.
     *
     * @return string
     */
    public function getDefaultXMailer(): string
    {
        return Ge::$app->version->name;
    }

    /**
     * Отправляет письмо.
     * 
     * @return mixed Если значение `true`, то письмо успешно отправлено.
     */
    public function send(): mixed
    {
        return $this->getAdapter()->send();
    }
}
