<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mail\Adapter;

use Ge\Mail\Mail;

/**
 * Адаптер службы почты "PHPMailer".
 * 
 * @see \PHPMailer\PHPMailer\PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mail\Adapter
 * @since 2.0
 */
class PHPMailerAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected string $mailerName = 'PHPMailer\PHPMailer\PHPMailer';

    /**
     * {@inheritdoc}
     */
    public function init(Mail $mail): void
    {
        $this->mailer->Encoding      = $mail->encoding;
        $this->mailer->CharSet       = $mail->charset;
        $this->mailer->Mailer        = $mail->mailer;
        $this->mailer->Host          = $mail->host;
        $this->mailer->Port          = $mail->port;
        $this->mailer->SMTPSecure    = $mail->SMTPSecure;
        $this->mailer->SMTPAutoTLS   = $mail->SMTPAutoTLS;
        $this->mailer->SMTPAuth      = $mail->SMTPAuth;
        $this->mailer->SMTPKeepAlive = $mail->SMTPKeepAlive;
        $this->mailer->SMTPDebug     = $mail->debug;
        $this->mailer->Username      = $mail->username;
        $this->mailer->Password      = $mail->password;
        $this->mailer->AuthType      = $mail->authType;
        $this->mailer->XMailer       = $mail->xmailer;
        $this->mailer->Debugoutput   = [$this, 'debug'];
        $this->mailer->isHtml($mail->isHtml);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomHeader(string $name, ?string $value = null): bool
    {
        return $this->mailer->addCustomHeader($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isError(): bool
    {
        return $this->mailer->isError();
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): string
    {
        return $this->mailer->ErrorInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastMessageId(): string
    {
        return $this->mailer->getLastMessageID();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllHeaders(bool $toString = false, string $glue = ';'): string|array
    {
        if ($toString) {
            return $this->collectHeaders($glue, $this->mailer->getCustomHeaders());
        } else {
            return $this->mailer->getCustomHeaders();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAddresses(bool $toString = false, string $glue = ';'): array
    {
        if ($toString)
            $addresses = ['replyTo' => '', 'bcc' => '', 'cc' => '', 'to' => '', 'from' => ''];
        else
            $addresses = ['replyTo' => [], 'bcc' => [], 'cc' => [], 'to' => [], 'from' => []];
        // адрес "REPLY-TO"
        $items = $this->mailer->getReplyToAddresses();
        if ($items) {
            $addresses['replyTo'] = array_values($items);
            if ($toString) {
                $addresses['replyTo'] = $this->collectAddresses($glue, $addresses['replyTo']);
            }
        }
        // адрес "BCC"
        $items = $this->mailer->getBccAddresses();
        if ($items) {
            $addresses['bcc'] = array_values($items);
            if ($toString) {
                $addresses['bcc'] = $this->collectAddresses($glue, $addresses['bcc']);
            }
        }
        // адрес "CC"
        $items = $this->mailer->getCcAddresses();
        if ($items) {
            $addresses['cc'] = array_values($items);
            if ($toString) {
                $addresses['cc'] = $this->collectAddresses($glue, $addresses['cc']);
            }
        }
        // адрес "TO"
        $items = $this->mailer->getToAddresses();
        if ($items) {
            $addresses['to'] = array_values($items);
            if ($toString) {
                $addresses['to'] = $this->collectAddresses($glue, $addresses['to']);
            }
        }
        // адрес "FROM"
        if ($this->mailer->From) {
            $addresses['from'] = [[$this->mailer->From, $this->mailer->FromName]];
            if ($toString) {
                $addresses['from'] = $this->collectAddresses($glue, $addresses['from']);
            }
        }
        return $addresses;
    }

    /**
     * Склеивает все e-mail адреса в строку с разделителем.
     * 
     * @param string $glue Разделитель адреса.
     * @param array $addresses Массив e-mail адресов (пример: `[["name", "address"],...]`).
     * 
     * @return string E-mail адреса в строке с разделителем.
     */
    protected function collectAddresses(string $glue, array $addresses): string
    {
        $result = [];
        foreach ($addresses as $address) {
            $result[] = ($address[1] ? '<' . $address[1] . '> ' : '') . $address[0];
        }
        return implode($glue, $result);
    }

    /**
     * Склеивает все заголовки письма в строку с разделителем.
     * 
     * @param string $glue Разделитель заголовков.
     * @param array $headers Массив заголовков (пример: `[["name", "value"],...]`).
     * 
     * @return string Заголовки письма в строке с разделителем.
     */
    protected function collectHeaders(string $glue, array $headers): string
    {
        $result = [];
        foreach ($headers as $header) {
            $result[] = $header[0] . ': '  . $header[1];
        }
        return implode($glue, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function setError(string $message): void
    {
        $this->mailer->setError($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject(string $subject): static
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): string
    {
        return $this->mailer->Subject;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody(string $body): static
    {
        $this->mailer->Body = $body;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        return $this->mailer->Body;
    }
}
