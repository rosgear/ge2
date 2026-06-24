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
use Ge\Helper\Str;
use Ge\Stdlib\BaseObject;

/**
 * Класс создания хеш паролей с помощью алгоритмов шифрования Apache.
 *
 * @see http://httpd.apache.org/docs/2.2/misc/password_encryptions.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Crypt\Password
 * @since 2.0
 */
class Apache extends BaseObject implements PasswordInterface
{
    /**
     * @var string Набор символов для Base64.
     */
    public const BASE64  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

    /**
     * @var string Набор символов для Alpha64.
     */
    public const ALPHA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * Поддерживаемые алгоритмы шифрования.
     * 
     * @var array<int, string>
     */
    protected array $supportedFormat = ['crypt', 'sha1', 'md5', 'digest'];

    /**
     * Алгоритм шифрования.
     * 
     * @var string
     */
    public string $format;

    /**
     * Имя аутентификации (realm) для проверки подлинности дайджеста.
     * 
     * @var string
     */
    public string $authName = '';

    /**
     * Имя пользователя.
     * 
     * @var string
     */
    public string $userName = '';

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
            case 'crypt':
                return crypt($password, rand(0, strlen(self::ALPHA64)));

            case 'sha1':
                return '{SHA}' . base64_encode(sha1($password, true));

            case 'md5':
                return $this->apr1Md5($password);

            case 'digest':
                if (empty($this->userName) || empty($this->authName)) {
                    throw new Exception\RuntimeException(
                        Ge::t('app', 'You must specify UserName and AuthName (realm) to generate the digest')
                    );
                }
                return md5($this->userName . ':' . $this->authName . ':' .$password);
        }
        return false;
    }

    /**
     * Проверяет правильность пароля относительно хеш-значения.
     * 
     * @param string $password Пароль.
     * @param string $hash Хеш-значение.
     * 
     * @return bool
     */
    public function verify(string $password, string $hash): bool
    {
        if (substr($hash, 0, 5) === '{SHA}') {
            $hash2 = '{SHA}' . base64_encode(sha1($password, true));
            return Str::compareStrings($hash, $hash2);
        }

        if (substr($hash, 0, 6) === '$apr1$') {
            $token = explode('$', $hash);
            if (empty($token[2])) {
                throw new Exception\InvalidArgumentException(
                    Ge::t('app', 'The APR1 password format is not valid')
                );
            }
            $hash2 = $this->apr1Md5($password, $token[2]);
            return Str::compareStrings($hash, $hash2);
        }

        $bcryptPattern = '/\$2[ay]?\$[0-9]{2}\$[' . addcslashes(static::BASE64, '+/') . '\.]{53}/';

        if (strlen($hash) > 13 && ! preg_match($bcryptPattern, $hash)) { // digest
            if (empty($this->userName) || empty($this->authName)) {
                throw new Exception\RuntimeException(
                    Ge::t('app', 'You must specify UserName and AuthName (realm) to verify the digest')
                );
            }
            $hash2 = md5($this->userName . ':' . $this->authName . ':' .$password);
            return Str::compareStrings($hash, $hash2);
        }
        return Str::compareStrings($hash, crypt($password, $hash));
    }

    /**
     * Устанавливет алгоритм шифрования.
     *
     * @param string $format Алгоритм шифрования, например: 'crypt', 'sha1', 'md5', 'digest'.
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

    /**
     * Преобразование двоичной строки с использованием алфавита "./0-9A-Za-z"
     *
     * @param string $value Строка.
     * 
     * @return string
     */
    protected function toAlphabet64(string $value): string
    {
        return strtr(strrev(substr(base64_encode($value), 2)), self::BASE64, self::ALPHA64);
    }

    /**
     * APR1 MD5 алгоритм.
     *
     * @param string $password Пароль.
     * @param null|string $salt Соль.
     * 
     * @return string
     */
    protected function apr1Md5(string $password, ?string $salt = null): string
    {
        if (null === $salt) {
            $salt = Str::randomChars(8, self::ALPHA64);
        } else {
            if (strlen($salt) !== 8) {
                throw new Exception\InvalidArgumentException(
                    Ge::t('app', 'The salt value for APR1 algorithm must be 8 characters long')
                );
            }
            for ($i = 0; $i < 8; $i++) {
                if (strpos(self::ALPHA64, $salt[$i]) === false) {
                    throw new Exception\InvalidArgumentException(
                        Ge::t('app', 'The salt value must be a string in the alphabet') . './0-9A-Za-z'
                    );
                }
            }
        }

        $len  = strlen($password);
        $text = $password . '$apr1$' . $salt;
        $bin  = pack("H32", md5($password . $salt . $password));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $password[0];
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $password : $bin;
            if ($i % 3) {
                $new .= $salt;
            }
            if ($i % 7) {
                $new .= $password;
            }
            $new .= ($i & 1) ? $bin : $password;
            $bin = pack("H32", md5($new));
        }
        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) {
                $j = 5;
            }
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;

        return '$apr1$' . $salt . '$' . $this->toAlphabet64($tmp);
    }
}
