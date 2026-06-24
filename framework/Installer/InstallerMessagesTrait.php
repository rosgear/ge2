<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

/**
 * Трейт обработки сообщений установщика.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
trait InstallerMessagesTrait
{
    /**
     * Массив сообщений - ошибок.
     * 
     * @var array
     */
    protected array $messages = [];

    /**
     * Возвращает значение, указывающее на присутствие сообщений.
     * 
     * @return bool
     */
    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }
 
    /**
     * Возвращает значение, указывающее на отсутствие сообщений.
     * 
     * @return bool
     */
    public function noMessages(): bool
    {
        return empty($this->messages);
    }

    /**
     * Возвращает значение, указывающее, было ли сообщение.
     * 
     * @return bool
     */
    public function hasMessage(): bool
    {
        return isset($this->messages[0]);
    }

    /**
     * Возвращает значение, указывающее, была ли ошибка.
     * 
     * @return bool
     */
    public function hasError(): bool
    {
        return isset($this->messages[0]);
    }

    /**
     * Удаляет все сообщения из очереди.
     * 
     * @return $this
     */
    public function clearMessages(): static
    {
        $this->messages = [];
        return $this;
    }

    /**
     * Добавляет сообщение в очередь.
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок сообщения (по умолчанию '').
     * @param string $type Тип сообщения: 'error', 'warning', 'info' (по умолчанию 'error').
     * 
     * @return $this
     */
    public function addMessage(string $message, string $title = '', string $type = 'error'): static
    {
        $this->messages[] = [
            'message' => $message,
            'title'   => $title,
            'type'    => $type
        ];
        return $this;
    }

    /**
     * Добавляет предупреждение в очередь.
     * 
     * @see addMessage()
     * 
     * @param string $message Предупреждение.
     * @param string $title Заголовок предупреждения (по умолчанию '').
     * 
     * @return $this
     */
    public function addWarning(string $message, string $title = ''): static
    {
        $this->addMessage($message, $title, 'warning');
        return $this;
    }

    /**
     * Добавляет информирование в очередь.
     * 
     * @see addMessage()
     * 
     * @param string $message Информация.
     * @param string $title Заголовок (по умолчанию '').
     * 
     * @return $this
     */
    public function addInfo(string $message, string $title = ''): static
    {
        $this->addMessage($message, $title, 'info');
        return $this;
    }

    /**
     * Добавляет ошибку в очередь.
     * 
     * @see addMessage()
     * 
     * @param string $message Ошибка.
     * @param string $title Заголовок (по умолчанию '').
     * 
     * @return $this
     */
    public function addError(string $message, string $title = ''): static
    {
        $this->addMessage($message, $title, 'error');
        return $this;
    }

    /**
     * Устанавливает сообщение в очереди.
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок сообщения (по умолчанию '').
     * @param string $type Тип сообщения: 'error', 'warning', 'info' (по умолчанию '').
     * 
     * @return $this
     */
    public function setMessage(string $message, string $title = '', string $type = ''): static
    {
        $this->messages[0] = [
            'message' => $message,
            'title'   => $title,
            'type'    => $type
        ];
        return $this;
    }

    /**
     * Возвращает первое сообщение из очереди.
     * 
     * @return array|null Сообщение.
     */
    public function getMessage(): ?array
    {
        return $this->getMessages(0);
    }

    /**
     * Возвращает сообщени(е)я из очереди.
     * 
     * @param null|int $index Порядковый номер сообщения в очереди. Если значение 
     *     `null`, возвращает все сообщения (по умолчанию `null`).
     * 
     * @return array|null Если значение `null`, сообщение отсутствует по указанному 
     *     порядковому номеру.
     */
    public function getMessages(?int $index = null): ?array
    {
        if ($index === null) {
            return $this->messages;
        }
        return $this->messages[$index] ?? null;
    }
}
