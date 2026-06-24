<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mail\Adapter;

use Ge;
use Ge\Mail\Mail;

/**
 * Абстрактный адаптер службы почты.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mail\Adapter
 * @since 2.0
 */
abstract class AbstractAdapter
{
    /**
     * Отправитель писем.
     *
     * @var mixed
     */
    protected mixed $mailer;

    /**
     * Имя класса отправителя писем.
     *
     * @var string
     */
    protected string $mailerName;

    /**
     * Служба почты.
     *
     * @var Mail
     */
    protected Mail $mail;

    /**
     * Шаги сборки письма.
     * 
     * Имеет вид: `[["message" => "...", "level" => "..."],...]`.
     *
     * @see AbstractAdapter::debug()
     * 
     * @var array
     */
    protected array $debug = [];

    /**
     * Конструктор класса.
     * 
     * @param Mail $mail Служба почты.
     * 
     * @return void
     */
    public function __construct(Mail $mail)
    {
        $this->mail = $mail;
        $this->getMailer();
    }

    /**
     * Инициализация адаптера службой почты.
     * 
     * @see \Ge\Stdlib\AdapterTrait::$adapterInitMethod
     * 
     * @param Mail $mail Служба почты.
     * 
     * @return void
     */
    public function init(Mail $mail): void
    {
    }

    /**
     * Установка параметров письма адаптера, перед его отправкой.
     * 
     * @param array $options Параметров письма адаптера.
     * @param bool $autocomplete Автозаполнение (по умолчанию `false`).
     * 
     * @return bool Если значение `false`, ошибка при установке параметров (см. текст ошибки {@see Mail::getError()}).
     */
    public function setOptions(array $options, bool $autocomplete = false)
    {
        if (empty($options)) return true;

        $noReply = $options['noReply'] ?? false;
        // добавление собственных заголовков
        if (isset($options['header'])) {
            foreach ($options['header'] as $header) {
                if ($this->addCustomHeader($header['name'], $header['value']) === false) {
                    $this->setError('Invalid header: ' . $header['name']);
                    return false;
                }
            }
        }
        // добавление адресов "BCC"
        if (isset($options['bcc'])) {
            foreach ($options['bcc'] as $bcc) {
                if ($this->addBCC($bcc['address'], $bcc['name'] ?? '') === false) {
                    $this->setError('Invalid address (BCC): ' . $bcc['address']);
                    return false;
                }
            }
        }
        // добавление адресов "CC"
        if (isset($options['cc'])) {
            foreach ($options['cc'] as $cc) {
                if ($this->addCC($cc['address'], $cc['name'] ?? '') === false) {
                    $this->setError('Invalid address (CC): ' . $cc['address']);
                    return false;
                }
            }
        }
        // добавление адресов "To"
        if (isset($options['to'])) {
            $options['to'] = (array) $options['to'];
            foreach ($options['to'] as $to) {
                if (is_string($to)) {
                    $address = $to;
                    $name    = '';
                } else {
                    $address = $to['address'];
                    $name    = $to['name'] ?? '';
                }
                if ($this->addAddress($address, $name) !== true) {
                    $this->setError('Invalid address (to): ' . $address);
                    return false;
                }
            }
        }
        // добавление адреса "Reply-To"
        if (isset($options['replyTo'])) {
            $replyTo = $options['replyTo'];
            if ($this->addReplyTo($replyTo['address'], $replyTo['name'] ?? '') === false) {
                $this->setError('Invalid address (reply-to): ' . $replyTo['address']);
                return false;
            }
        } else {
            if ($autocomplete) {
                if ($this->addReplyTo($this->mail->fromAddress, $this->mail->fromName) === false) {
                    $this->setError('Invalid address (reply-to): ' . $this->mail->fromAddress);
                    return false;
                }
            }
        }
        // добавление адреса отправителя
        if (isset($options['from'])) {
            $from = $options['from'];
            if ($this->setFrom($from['address'], $from['name'] ?? '') === false) return false;
        } else {
            if ($autocomplete) {
                if ($this->setFrom($noReply ? $this->mail->noreplyAddress : $this->mail->fromAddress, $noReply ? '' : $this->mail->fromName) === false) return false;
            }
        }
        // установка темы письма
        if (isset($options['subject'])) {
            $this->setSubject($options['subject']);
        }
        // установка текста письма
        if (isset($options['body'])) {
            $this->setBody($options['body']);
        }
        return true;
    }

    /**
     * Отладка каждого шага сборки письма.
     * 
     * Вызов будет в том случаи, если на каждом этапе сборки не будет ошибок.
     * Полученная информация добавляется к профилированию сборки письма {@see AbstractAdapter::profiling()}.
     * 
     * @param string $message Шаг сборки письма.
     * @param int $level Уровень.
     *
     * @return void
     */
    public function debug(string $message, int $level): void
    {
        $message = htmlentities(
            preg_replace('/[\r\n]+/', '', $message),
            ENT_QUOTES,
            'UTF-8'
        );
        $this->debug[] = ['message' => $message, 'level' => $level];
    }

    /**
     * Профилирование сборки письма.
     * 
     * Профилирование будет выполнено в том случаи, если будет:
     *    - отладка приложения `GE_DEBUG = true`;
     *    - включена служба логирования `\Ge\Log\Logger::$enabled`;
     *    - включено профилирование производительности `\Ge\Log\Logger::$enableProfiling`;
     *    - включено профилирование почты `\Ge\Log\Logger::enableProfilingMail`.
     * 
     * @param string $message Описание профиля.
     * 
     * @return void
     */
    public function profiling(string $message = ''): void
    {
        $logger = Ge::$app->logger;
        if (!$logger->enableProfilingMail) return;

        // шаги отладки
        $debugSteps = [];
        foreach ($this->debug as $index => $debug) {
            $debugSteps[] = ($index + 1) . '. ' . $debug['message'];
        }
        $addresses = $this->getAllAddresses(true, '<br>');
        $logger->mailProfiling(
            $message,
            [
                'error'      => $this->getError(),
                'to'         => $addresses['to'],
                'replyTo'    => $addresses['replyTo'],
                'bcc'        => $addresses['bcc'],
                'cc'         => $addresses['cc'],
                'from'       => $addresses['from'],
                'body'       => $this->getBody(),
                'subject'    => $this->getSubject(),
                'headers'    => $this->getAllHeaders(true, '<br>'),
                'messageId'  => htmlentities($this->getLastMessageId()),
                'service'    => $this->mail->getUnifiedConfig(),
                'debugSteps' => implode('<br>', $debugSteps)
            ]
        );
    }

    /**
     * Добавляет прикрепленные файлы из указанного пути файловой системы.
     * 
     * @param string $path Путь с прикрепленным файлом.
     * @param string $name Новое имя прикрепленного файла.
     *
     * @return bool Возвращает значение `false`, если файл не может быть найден или прочитан.
     */
    public function addAttachment(string $path, string $name = ''): bool
    {
        return false;
    }

    /**
     * Добавляет собственный заголовок.
     * 
     * @param string $name Имя собственного заголовка.
     *    Значение может быть перегружено, если имеет значение "name:value".
     * @param string|null $value Значение заголовка.
     * 
     * @return bool Если значение `true`, заголовок успешно добавлен.
     */
    public function addCustomHeader(string $name, ?string $value = null): bool
    {
        return false;
    }

    /**
     * Добавляет адрес "To".
     * 
     * Адрес "To" содержит имя и адрес получателя.
     *
     * @param string $address Электронный адрес для отправки.
     * @param string $name Имя отправителя.
     *
     * @return bool Если значение `false`, то адрес уже используется или 
     *     недействителен каким-либо образом.
     */
    public function addAddress(string $address, string $name = ''): bool
    {
        return $this->mailer->addAddress($address, $name);
    }

    /**
     *  Добавляет адрес "СС".
     * 
     * Адрес "СС" (Carbon Сopy) содержит имена и адреса вторичных получателей письма, 
     * к которым направляется копия.
     *
     * @param string $address Электронный адрес для отправки.
     * @param string $name Имя отправителя.
     *
     * @return bool Если значение `false`, то адрес уже используется или недействителен 
     *     каким-либо образом.
     */
    public function addCC(string $address, string $name = ''): bool
    {
        return $this->mailer->addCC($address, $name);
    }

    /**
     * Добавляет адрес "BСС".
     * 
     * Адрес "BСС" (Blind Carbon Сopy) содержит имена и адреса получателей письма, 
     * чьи адреса не следует показывать другим получателям.
     *
     * @param string $address Электронный адрес для отправки.
     * @param string $name Имя отправителя.
     *
     * @return bool Если значение `false`, то адрес уже используется или 
     *     недействителен каким-либо образом.
     */
    public function addBCC(string $address, string $name = '')
    {
        return $this->mailer->addBCC($address, $name);
    }

    /**
     * Добавляет адрес "Reply-To".
     * 
     * Адрес "Reply-To" содержит имя и адрес, куда следует адресовать ответы на это 
     * письмо. Если, например, письмо рассылается роботом, то в качестве Reply-To будет 
     * указан адрес почтового ящика, готового принять ответ на письмо.
     *
     * @param string $address Электронный адрес, на который нужно ответить.
     * @param string $name Имя кому надо ответить.
     *
     * @return bool Если значение `false`, то адрес уже используется или недействителен 
     *     каким-либо образом.
     */
    public function addReplyTo(string $address, string $name = '')
    {
        return $this->mailer->addReplyTo($address, $name);
    }

    /**
     * Возвращает массив прикрепленных файлов.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->mailer->getAttachments();
    }

    /**
     * Возвращает все адреса.
     * 
     * @param bool $toString Если значение `true`, все адреса буду "склеены" в строку.
     * @param string $glue Символ склеивания адресов.
     * 
     * @return array
     */
    public function getAllAddresses(bool $toString = false, string $glue = ';'): array
    {
        return [];
    }

    /**
     * Возвращает все заголовки.
     * 
     * @param bool $toString Если значение `true`, все заголовки буду "склеены" в строку.
     * @param string $glue Символ склеивания заголовков.
     * 
     * @return string|array
     */
    public function getAllHeaders(bool $toString = false, string $glue = ';'): string|array
    {
        return [];
    }

    /**
     * Возвращает все свои заголовки.
     *
     * @return array
     */
    public function getCustomHeaders(): array
    {
        return $this->mailer->getCustomHeaders();
    }

    /**
     * Проверяте, была ли ошибка в последнем запросе.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return false;
    }

    /**
     * Возвращает ошибку полученную в последнем запросе.
     *
     * @return string
     */
    public function getError(): string
    {
        return '';
    }

    /**
     * Устанавливает ошибку полученную в последнем запросе.
     * 
     * @param string $message Сообщение о полученной ошибке.
     * 
     * @return void
     */
    public function setError(string $message): void
    {
    }

    /**
     * Возвращает отправителя писем.
     *
     * @return mixed
     */
    public function getMailer(): mixed
    {
        if (!isset($this->mailer)) {
            $this->mailer = $this->crateMailer();
        }
        return $this->mailer;
    }

    /**
     * Создаёт отправителя писем.
     *
     * @return mixed
     */
    public function crateMailer(): mixed
    {
        return new $this->mailerName();
    }

    /**
     * Создаёт сообщение и отправляет его.
     *
     * @return bool Возвращает значение `false`, ошибка при создании или отправки 
     *     письма.
     */
    public function send(): bool
    {
        $isSand = $this->mailer->send();
        $this->profiling();
        return $isSand;
    }

    /**
     * Задаёт свойство "From" (от кого письмо).
     *
     * @param string $address Электронный адрес отправителя.
     * @param string $name Имя отправителя.
     *
     * @return bool Возвращает значение `true`, если свойство успешно установлено.
     */
    public function setFrom(string $address, string $name = ''): bool
    {
        return $this->mailer->setFrom($address, $name);
    }

    /**
     * Задаёт свойство "Subject" (тема письма).
     *
     * @param string $subject Тема письма.
     * 
     * @return $this
     */
    public function setSubject(string $subject): static
    {
        return $this;
    }

    /**
     * Возвращает свойство "Subject" (тема письма).
     *
     * @return string $subject Тема письма.
     */
    public function getSubject(): string
    {
        return '';
    }

    /**
     * Задаёт свойство "Body" (текст письма).
     *
     * @param string $body Текст письма.
     * 
     * @return $this
     */
    public function setBody(string $body): static
    {
        return $this;
    }

    /**
     * Возвращает свойство "Body" (текст письма).
     *
     * @return string $body Текст письма.
     */
    public function getBody(): string
    {
        return '';
    }

    /**
     * Удаляет все адреса получателей.
     * 
     * @return $this
     */
    public function clearAddresses(): static
    {
        $this->mailer->clearAddresses();
        return $this;
    }

    /**
     * Удаляет всех получателей "CC".
     * 
     * @return $this
     */
    public function clearCCs(): static
    {
        $this->mailer->clearCCs();
        return $this;
    }

    /**
     * Удаляет всех получателей "BCC".
     * 
     * @return $this
     */
    public function clearBCCs(): static
    {
        $this->mailer->clearBCCs();
        return $this;
    }

    /**
     * Удаляет всех получателей.
     * 
     * @return $this
     */
    public function clearAllRecipients(): static
    {
        $this->mailer->clearAllRecipients();
        return $this;
    }

    /**
     * Удаляет все прикрепленные файлы.
     * 
     * @return $this
     */
    public function clearAttachments(): static
    {
        $this->mailer->clearAttachments();
        return $this;
    }

    /**
     * Удаляет все собственные заголовки.
     * 
     * @return $this
     */
    public function clearCustomHeaders(): static
    {
        $this->mailer->clearCustomHeaders();
        return $this;
    }

    /**
     * Возвращает заголовок Message-ID последнего электронного письма.
     * 
     * Технически это значение с момента последнего создания заголовков, 
     * но это также идентификатор последнего отправленного сообщения, 
     * за исключением патологических случаев.
     *
     * @return string
     */
    public function getLastMessageId(): string
    {
        return '';
    }
}
