<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http;

/**
 * Заголовки для формирования HTTP ответа или запроса
 * 
 * Загаловки используют {@see Response} и {@see Request}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http
 * @since 2.0
 */
class Headers implements \IteratorAggregate
{
    /**
     * Имена параметров и значений заголовков.
     * 
     * @var array
     */
    protected array $headers = [];

    /**
     * Формирование заголовков из getallheaders и http_get_request_headers.
     * При отсутствии методов, заголовки {@see $headers} формируются из $_SERVER.
     * 
     * @return $this
     */
    public function define(): static
    {
        if (function_exists('getallheaders')) {
            $this->addHeaders(getallheaders());
        } elseif (function_exists('http_get_request_headers')) {
            $this->addHeaders(\http_get_request_headers());
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $this->add($name, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Возвращает итератор для обхода заголовков в коллекции.
     * 
     * Этот метод используется для интерфейса SPL {@see \IteratorAggregate} и
     * может быть вызван для обхода коллекции с помощью "foreach".
     * 
     * @return \Traversable Итератор для обхода заголовков в коллекции.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->headers);
    }

    /**
     * Добавление коллекции заголовков.
     * 
     * @param array $headers Коллекция заголовков.
     * 
     * @return $this
     */
    public function addHeaders(array $headers): static
    {
        foreach($headers as $name => $value) {
            $this->add($name, $value);
        }
        return $this;
    }

    /**
     * Добавление параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * @param string $value Значение параметра.
     * 
     * @return $this
     */
    public function add(string $name, string $value, bool $lowercase = true): static
    {
        $this->headers[$lowercase ? strtolower($name) : $name] = $value;
        return $this;
    }

    /**
     * Установка параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * @param string $value Значение параметра.
     * 
     * @return $this
     */
    public function set(string $name, string $value, bool $lowercase = true): static
    {
        $this->headers[$lowercase ? strtolower($name) : $name] = $value;
        return $this;
    }

    /**
     * Устанавливает набор HTTP-заголовков по умолчанию для загрузки файлов.
     *
     * @param string $attachmentName Имя прикрепляемого файла.
     * @param null|string $mimeType MIME-тип ответа. Если значение `null`, заголовок 
     *     'Content-Type' не будет установлен (по умолчанию `null`).
     * @param bool $inline Устанавливает, должен ли браузер открывать файл в окне 
     *     браузера. Если значение `false`, то  появится диалоговое окно загрузки.
     *     (по умолчанию `false`).
     * @param null|int $contentLength Длина загружаемого файла в байтах. Если значение `null`, 
     *     то заголовок 'Content-Length' не будет установлен. (по умолчанию `null`).
     * 
     * @return $this
     */
    public function setDownload(string $attachmentName, ?string $mimeType = null, bool $inline = false, ?int $contentLength = null): static
    {
        $disposition = $this->getDispositionValue($inline ? 'inline' : 'attachment', $attachmentName);
        $this
            ->set('Pragma', 'public')
            ->set('Accept-Ranges', 'bytes')
            ->set('Expires', '0')
            ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->set('Content-Disposition', $disposition);

        if ($mimeType !== null) {
            $this->set('Content-Type', $mimeType);
        }

        if ($contentLength !== null) {
            $this->set('Content-Length', $contentLength);
        }
        return $this;
    }

    /**
     * Возвращает значение параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * @param null|string $default Значение по умолчнаию.
     * @param bool $сouple Если true, возвращает строку вида "параметр: значение", иначе значение параметра.
     * 
     * @return string|null
     */
    public function get(string $name, ?string $default = null, bool $сouple = false): ?string
    {
        $name = strtolower($name);
        if (isset($this->headers[$name])) {
            $value = $this->headers[$name];
            return $сouple ? $name . ': ' . $value : $value;
        }
        return $default;
    }

    /**
     * Возвращает заголовок, определеляющий вид ожидаемого контента, который будет 
     * отображаться в браузере.
     * 
     * Например: 'inline', 'attachment', 'attachment; filename="filename.jpg"'.
     * 
     * @param string $disposition Расположение:
     *     - 'inline', контент должен быть отображён внутри веб-страницы или как веб-страница;
     *     - 'attachment', указывает на скачиваемый контент.
     * @param null|string $attachmentName Имя скачиваемого контента (имя файла).
     */

    public function getDispositionValue(string $disposition, ?string $attachmentName): string
    {
        if ($disposition === 'attachment') {
            if ($attachmentName) {
                $encode = urlencode($attachmentName);
                $value = 'attachment; filename="' . $attachmentName . '"';
                if ($encode !== $attachmentName) {
                    $value .= "; filename*=UTF-8''" . $encode;
                }
                return $value;
            } else
                return 'attachment';
        }
        return $disposition;
    }

    /**
     * Возвращает оригинальное значение параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * @param null|string $default Значение по умолчнаию.
     * @param bool $сouple Если true, возвращает строку вида "параметр: значение", иначе значение параметра.
     * 
     * @return string|null
     */
    public function getOriginal(string $name, ?string $default = null, bool $сouple = false): ?string
    {
        if (isset($this->headers[$name])) {
            $value = $this->headers[$name];
            return $сouple ? $name . ': ' . $value : $value;
        }
        return $default;
    }

    /**
     * Проверка существования параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * 
     * @return bool
     */
    public function has(string $name, bool $lowercase = true): bool
    {
        return isset($this->headers[$lowercase ? strtolower($name) : $name]);
    }

    /**
     * Удаление параметра заголовка.
     * 
     * @param string $name Имя параметра.
     * 
     * @return $this
     */
    public function remove(string $name, bool $lowercase = true): static
    {
        $name = $lowercase ? strtolower($name) : $name;
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        return $this;
    }

    /**
     * Возвращает количества параметров заголовков.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->headers);
    }

    /**
     * Смещение указателя массива параметров заголовков {@see $headers} на один элемент вперед.
     * 
     * @return $this
     */
    public function next(): static
    {
        next($this->headers);
        return $this;
    }

    /**
     * Возвращает текущий элемента массива параметров заголовков {@see $headers}.
     * 
     * @return mixed
     */
    public function key(): string|int|null
    {
        return (key($this->headers));
    }

    /**
     * Сброс указателя на начало массива параметров заголовков {@see $headers}.
     * 
     * @return $this
     */
    public function rewind(): static
    {
        reset($this->headers);
        return $this;
    }

    /**
     * Удаление всех параметров заголовков.
     * 
     * @return $this
     */
    public function clear(): static
    {
        $this->headers = [];
        return $this;
    }

    /**
     * Возвращает массив параметров заголовков или массив строк имеющий
     * вид "параметр: значение".
     * 
     * @param bool $сouple Если true, возвращает массив строк имеющий вид "параметр: значение", иначе 
     * имена параметров с их значениями.
     * 
     * @return array
     */
    public function toArray(bool $сouple = false): array
    {
        if ($сouple) {
            $arr = array();
            foreach($this->headers as $name => $value) {
                $arr[] = $name . ': ' . $value;
            }
            return $arr;
        }
        return $this->headers;
    }

    /**
     * Возвращает имена параметров с их значениями в виде строки с разделителем.
     *
     * @return string
     */
    public function toString(): string
    {
        $headers = '';
        foreach ($this->toArray(true) as $line) {
            $headers .= $line . "\r\n";
        }
        return $headers;
    }

    /**
     * Проверяет, были ли отправлены заголовки.
     *
     * @return false|array Если false заголовки не были отправлены, иначе возвращает 
     * название файла сценария и номер строки откуда был отправлен заголовок.
     */
    public function sent(): false|array
    {
        if (!headers_sent($filename, $line)) {
            return false;
        }
        return [
            'filename' => $filename,
            'line'     => $line
        ];
    }

    /**
     * Отправка заголовков.
     *
     * @return $this
     */
    public function send(): static
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        return $this;
    }
}
