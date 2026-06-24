<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Encryption;

use RuntimeException;
use Ge\Stdlib\Service;
use Ge\Crypt\Password\Crypt as PasswordCrypt;

/**
 * Служба Шифрования предоставляют простой и удобный интерфейс для шифрования и дешифрования 
 * текста через OpenSSL с использованием шифрования AES-256 и AES-128.
 * 
 * Encrypter - это служба приложения, доступ к которой можно получить через `Ge::$app->encrypter`.
 * 
 * Все зашифрованные значения подписываются с использованием кода аутентификации сообщения (MAC), 
 * поэтому их базовое значение не может быть изменено или подделано после того, как зашифровано.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Taylor Otwell <taylor@laravel.com>
 * @package Ge\Encryption
 * @since 2.0
 */
class Encrypter extends Service
{
    /**
     * Ключ шифрования.
     *
     * @var string
     */
    public string $key = '';

    /**
     * Алгоритм, используемый для шифрования.
     *
     * @var string
     */
    public string $cipher = 'AES-128-CBC';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->key = (string) $this->key;
        if (!static::supported($this->key, $this->cipher)) {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Установка ключа и шифра.
     *
     * @param string $key Ключ.
     * @param string $cipher Шифр (AES-128-CBC, AES-256-CBC).
     * 
     * @return void
     * 
     * @throws RuntimeException 
     */
    public function set(string $key, string $cipher = 'AES-128-CBC'): void
    {
        $key = (string) $key;
        if (static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
    }

    /**
     * Определяет, действительна ли данная комбинация ключа и шифра.
     *
     * @param string $key Ключ.
     * @param string $cipher Шифр (AES-128-CBC, AES-256-CBC).
     * 
     * @return bool Если значение `true`, данная комбинация ключа и шифра действительна.
     */
    public static function supported(string $key, string $cipher): bool
    {
        $length = mb_strlen($key, '8bit');
        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Создаёт новый ключ шифрования для данного шифра.
     *
     * @param string $cipher Шифр.
     * 
     * @return string Ключ шифрования.
     */
    public static function generateKey(string $cipher): string
    {
        return random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Шифрует указанное значение.
     *
     * @param mixed $value Шифруемое значение.
     * @param bool $serialize Если значение `true`, сериализация значения.
     * @param string $key Ключ шифрования. Если значение `null`, используется {@see Encrypter::$key}.
     * 
     * @return string
     *
     * @throws Exception\EncryptException Не удалось зашифровать данные.
     */
    public function encrypt(mixed $value, bool $serialize = true, ?string $key = null): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        // Сначала шифруется значение с помощью OpenSSL. После, 
        // переходим к вычислению MAC для зашифрованного значения, 
        // чтобы это значение можно было проверить 
        // позже, так как оно не было изменено пользователями.
        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $key ?: $this->key, 0, $iv
        );

        if ($value === false) {
            throw new Exception\EncryptException('Could not encrypt the data.');
        }

        // Как только получим зашифрованное значение, в base64_encode 
        // кодируем вектор и получаем MAC для зашифрованного значения, чтобы затем мы 
        // могли проверить его подлинность. Затем поместим данные в массив 
        // «полезной нагрузки» в формате JSON.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception\EncryptException('Could not encrypt the data.');
        }
        return base64_encode($json);
    }

    /**
     * Шифрует строку без сериализации.
     *
     * @param string $value Шифруемое значение.
     * 
     * @return string
     *
     * @throws Exception\EncryptException Не удалось зашифровать данные.
     */
    public function encryptString(string $value): string
    {
        return $this->encrypt($value, false);
    }

    /**
     * Расшифрует указанное значение.
     *
     * @param string $payload Полезная нагрузка.
     * @param bool $unserialize Если значение `true`, выполняет десериализацию 
     *     расшифрованной строки.
     * @param string|null $key Ключ шифрования. Если значение `null`, используется {@see Encrypter::$key}.
     * 
     * @return mixed Расшифрованная строка или исключение в случае возникновения ошибки.
     *
     * @throws Exception\DecryptException Не удалось расшифровать данные.
     */
    public function decrypt(string $payload, bool $unserialize = true, ?string $key = null): mixed
    {
        $payload = $this->getJsonPayload($payload);
        $iv = base64_decode($payload['iv']);

        /** @var string|false $decrypted */
        $decrypted = \openssl_decrypt(
            $payload['value'], $this->cipher, $key ?: $this->key, 0, $iv
        );

        if ($decrypted === false) {
            throw new Exception\DecryptException('Could not decrypt the data.');
        }
        $value = $unserialize ? @unserialize($decrypted) : $decrypted;
        return $value;
    }

    /**
     * Расшифровует указанную строку без десериализации.
     *
     * @param string $payload Расшифруемая строка.
     * 
     * @return mixed
     *
     * @throws Exception\DecryptException Не удалось расшифровать данные.
     */
    public function decryptString(string $payload): mixed
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Генерируте хеш-кода на основе ключа, используя метод HMAC с алгоритмом 
     * хеширования sha256.
     *
     * @param string $iv Вектор шифра. 
     * @param mixed $value Сообщение для хеширования.
     * 
     * @return string Строка содержащая вычисленный хеш-код в шестнадцатеричной 
     *     кодировке в нижнем регистре.
     */
    protected function hash(string $iv, string $value): string
    {
        return hash_hmac('sha256', $iv . $value, $this->key);
    }

    /**
     * Возвращает массив JSON из заданной полезной нагрузки.
     *
     * @param string $payload Полезная нагрузка.
     * 
     * @return array
     *
     * @throws Exception\DecryptException Полезная нагрузка недействительна.
     * @throws Exception\DecryptException MAC недействителен.
     */
    protected function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);
        // проверяет, действительна ли полезная нагрузка шифрования
        if (!$this->validPayload($payload)) {
            throw new Exception\DecryptException('The payload is invalid.');
        }

        // проверяет, действителен ли MAC для данной полезной нагрузки
        if (!$this->validMac($payload)) {
            throw new Exception\DecryptException('The MAC is invalid.');
        }
        return $payload;
    }

    /**
     * Проверяет, действительна ли полезная нагрузка шифрования.
     *
     * @param mixed $payload Полезная нагрузка.
     * 
     * @return bool Возвращает значение `true`, если полезная нагрузка шифрования 
     *     действительна.
     */
    protected function validPayload(mixed $payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
               strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * Проверяет, действителен ли MAC для данной полезной нагрузки.
     *
     * @param array $payload Полезная нагрузка.
     * 
     * @return bool Возвращает значение `true`, если MAC действителен для данной 
     *     полезной нагрузки.
     */
    protected function validMac(array $payload): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value']), $payload['mac']
        );
    }

    /**
     * Возвращает ключ шифрования.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Шифрует пароль.
     *
     * @param string $password Пароль.
     * @param string $format Формат пароля (по умолчанию 'default').
     * 
     * @return string
     */
    public function encodePassword(string $password, string $format = 'default'): string
    {
        return (new PasswordCrypt(['format' => $format]))->create(md5($password));
    }

    /**
     * Проверяет пароль.
     *
     * @param string $password Пароль.
     * @param string $verify Проверяемое значение (пароль).
     * @param string $format Формат пароля (по умолчанию 'default').
     * 
     * @return bool Возвращает значение `true`, если проверяемое значение совпадает с паролем.
     */
    public function verifyPassword(string $password, string $verify, string $format = 'default'): bool
    {
        return (new PasswordCrypt(['format' => $format]))->verify(md5($verify), $password);
    }

    /**
     * Создаёт пароль.
     *
     * @param int $length Длина пароля.
     * @param bool $encode Кодировать пароль.
     * 
     * @return string Пароль.
     */
    public function generatePassword(int $length = 3, bool $encode = false): string
    {
        $str = bin2hex(openssl_random_pseudo_bytes($length));
        $str = substr($str, 0, $length);
        return $encode ? $this->encodePassword($str) : $str;
    }
}
