<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge\Stdlib\BaseObject;

/**
 * Переводчик установщика.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerTranslator extends BaseObject
{
    /**
     * Имя локали для загрузки сообщений перевода.
     * 
     * Например: 'ru-RU', 'en-GB'.
     * 
     * @var string
     */
    public string $locale;

    /**
     * Абсолютный путь к файлам перевода.
     * 
     * @var string
     */
    public string $path;

    /**
     * Автоматически загружать сообщения перевода при инициализации.
     * 
     * @var bool
     */
    public bool $autoload = true;

    /**
     * Сообщения перевода.
     * 
     * @see InstallerTranslator::addMessages()
     * 
     * @var array
     */
    protected array $messsages = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);

        $this->init();
    }

    /**
     * Инициализация переводчика.
     *
     * @return void
     */
    protected function init(): void
    {
        if ($this->autoload && $this->locale) {
            $this->addMessages($this->locale);
        }
    }

    /**
     * Возвращает название файла перевода.
     *
     * @param string $locale Имя локали, например: 'ru-RU', 'en-GB'.
     * 
     * @return string
     */
    public function getMessagesFile(string $locale): string
    {
        return $this->path . DS . $locale . '.php';
    }

    /**
     * Добавляет сообщения перевода.
     *
     * @param string $locale Имя локали, например: 'ru-RU', 'en-GB'.
     * 
     * @return void
     */
    public function addMessages(string $locale): void
    {
        $filename = $this->getMessagesFile($locale);
        if (file_exists($filename)) {
            $messages = include($filename);
            $this->messsages = array_merge($this->messsages, $messages);
        }
    }


    /**
     * Выполняет перевод указанного сообщения.
     * 
     * Пример:
     * ```php
     * t('Hi %s', ['Ivan']); // Hi Ivan
     * ```
     * 
     * @see Installer::getTranslator()
     * 
     * @param string $message Сообщение перевода.
     * @param array Параметры сообщения.
     * 
     * @return string
     */
    public function translate(string $message, array $args = []): string
    {
        $message = $this->messsages[$message] ?? $message;
        return $args ? vsprintf($message, $args) : $message;
    }

    /**
     * Возвращает все сообщения перевода.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messsages;
    }
}
