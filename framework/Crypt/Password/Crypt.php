<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Crypt\Password;

use Ge;
use Ge\Stdlib\BaseObject;

/**
 * Класс создания хеш паролей с помощью алгоритмов шифрования.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Crypt\Password
 * @since 2.0
 */
class Crypt extends BaseObject implements PasswordInterface
{
    /**
     * Поддерживаемые алгоритмы шифрования.
     * 
     * @var array<int, string>
     */
    protected array $supportedFormat = [
        'default', 'blowfish', 'oldBlowfish', 'argon2i', 'argon2id', 'bcrypt'
    ];

    /**
     * Алгоритм шифрования.
     * 
     * @var string
     */
    public string $format;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        if (isset($config['format'])) {
            $this->setFormat($config['format']);
        }

        parent::configure($config);
    }

    /**
     *  Создаёт хеш пароля.
     *
     * @param string $password Пароль.
     * 
     * @return false|string Возвращает хеш пароля. Если значение `false`, то невозможно 
     *     создать хеш.
     */
    public function create(string $password): false|string
    {
        switch ($this->format) {
            case 'default':  return password_hash($password, PASSWORD_DEFAULT);
            case 'argon2i':  return password_hash($password, PASSWORD_ARGON2I);
            case 'argon2id': return password_hash($password, PASSWORD_ARGON2ID );
            case 'blowfish':
            case 'bcrypt':   return password_hash($password, PASSWORD_BCRYPT);

            case 'oldBlowfish':
                $salt = md5(uniqid('some_prefix', true));
                $salt = substr(strtr(base64_encode($salt), '+', '.'), 0, 22);
                return crypt($password, '$2a$08$' . $salt);
        }
        return false;
    }

    /**
     * Проверяет правильность пароля относительно хеш-значения.
     * 
     * @link https://php.net/manual/ru/function.crypt.php
     * 
     * @param string $password Пароль.
     * @param string $hash Хеш-значение.
     * 
     * @return bool
     */
    public function verify(string $password, string $hash): bool
    {
        return crypt($password, $hash) === $hash;
    }

    /**
     * Устанавливет алгоритм шифрования.
     *
     * @param string $format Алгоритм шифрования, например: 'bcrypt', 'blowfish', 'argon2i', 'argon2id'.
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException Не поддерживается алгоритм шифрования.
     */
    public function setFormat(string $format): static
    {
        if (!in_array($format, $this->supportedFormat)) {
            throw new Exception\InvalidArgumentException(
                Ge::t(
                    'app', 
                    'The format {0} specified is not valid. The supported formats are: {1}', 
                    [$format, implode(',', $this->supportedFormat)]
                )
            );
        }
        $this->format = $format;
        return $this;
    }
}
