<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\I18n;

use Ge\Stdlib\Service;
use Ge\I18n\Source\BaseSource;

/**
 * Транслятор (локализатор сообщений) предназначен для вывода сообщений и текста 
 * в соответствии с шаблонами локализации.
 * 
 * Translator - это служба приложения, доступ к которой можно получить через `Ge::$app->translator`.
 * 
 * Добавления категорий и шаблонов локализации выполняется на этапе инициализация 
 * локализации приложения {@see \Ge\Mvc\Application::initLocalization()}.
 * 
 * После инициализации службы, будет добавлена категория "app" (с шаблонами локализации сообщений
 * приложения).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\I18n
 * @since 2.0
 */
class Translator extends Service
{
    /**
     * Категории сообщений.
     * 
     * По умолчанию будет добавлено: ['app' => BaseSource] для локализации всех 
     * сообщений приложения.
     *
     * @var array<string, BaseSource>
     */
    protected array $categories = [];

    /**
     * Ошибка последней локализации сообщения.
     * 
     * Ошибка возникает при вызове перевода в источнике сообщения {@see BaseSource::translate()} и 
     * имеет вид: ["код ошибки", "текст ошибки"].
     * 
     * @var array<int, int|string>
     */
    public array $error = [];

    /**
     * Добавляет категорию сообщений.
     * 
     * @param string $name Имя категории сообщений.
     * @param array $baseConfig Конфигурация источника сообщений.
     * @param string $sourceName Имя источника сообщений (по умолчанию "Message").
     * 
     * @return BaseSource
     */
    public function addCategory(string $name, array $baseConfig = [], string $sourceName = 'Message'): BaseSource
    {
        if (!isset($this->categories[$name])) {
            $this->categories[$name] = $this->getMessageSource($sourceName, $baseConfig);
        }
        return $this->categories[$name];
    }

    /**
     * Возвращает категорию сообщений.
     * 
     * @param string $name Имя категории сообщений.
     * 
     * @return false|BaseSource Возвращает значение `false`, если категория не существует.
     */
    public function getCategory(string $name): false|BaseSource
    {
        return $this->categories[$name] ?? false;
    }

    /**
     * Возвращает все категории сообщений.
     * 
     * @return array<string, BaseSource>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Проверяет, существует ли категория с указанным именем.
     * 
     * @return bool
     */
    public function categoryExists(string $name): bool
    {
        return isset($this->categories[$name]);
    }

    /**
     * Создает источник сообщений.
     * 
     * @param string $name Имя источника.
     * @param array $baseConfig Базовая конфигурация источника.
     * 
     * @return BaseSource
     */
    public function getMessageSource(string $name, array $baseConfig): BaseSource
    {
        $sourceClass = "\Ge\I18n\Source\\{$name}Source";
        return new $sourceClass($baseConfig);
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string $category Категория сообщений, например: 'app', '@date', '@message'.
     *    Где, указаны:
     *    - 'app', источник стообщений для всего приложения {@see \Ge\I18n\Source\MessageSource};
     *    - '@date', форматирование дат {@see \Ge\I18n\Source\DateSource};
     *    - '@message', форматирование сообщений {@see \Ge\I18n\Source\MessageSource}.
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function translate(string $category, string|array $message, array $params = [], string $locale = ''): string|array
    {
        $source = $this->getCategory($category);
        // если нет источника сообщений с указанной категорией
        if ($source === false) {
            $isSource = $category[0] === '@';
            if ($isSource) {
                $sourceName = ucfirst(ltrim($category, '@'));
                $source = $this->addCategory($category, [], $sourceName);
            } else
                throw new Exception\CategoryNotFoundException(
                    sprintf('"%s" category not found for translation.', $category)
                );
        }
        $message = $source->translate($message, $params, $locale);
        if ($source->hasError()) {
            $this->error = $source->getError();
        }
        return $message;
    }
}
