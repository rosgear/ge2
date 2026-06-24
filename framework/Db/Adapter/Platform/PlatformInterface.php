<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Adapter\Platform;

/**
 * Интерфейс платформы адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Platform
 * @since 2.0
 */
interface PlatformInterface
{
    /**
     * Возвращает имя платформы.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает символ экрана (кавычку).
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol(): string;

    /**
     * Выполняет экранирование идентификатора кавычками.
     *
     * @param string $identifier 
     * 
     * @return string
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Выполняет экранирование массива строк c соединением их в одну строку.
     *
     * @param string|array<int, string> $identifierChain
     * 
     * @return string
     */
    public function quoteIdentifierChain(string|array $identifierChain): string;

    /**
     * Возвращает символ экрана.
     *
     * @return string
     */
    public function getQuoteValueSymbol(): string;

    /**
     * Выполняет экранирование строки одинарными кавычками.
     *
     * @param string $value Строка.
     * 
     * @return string
     */
    public function quoteValue(string $value): string;

    /**
     * Выполняет экранирование значения.
     * 
     * Возможность указывать значения без уведомлений
     *
     * @param $value значение
     * 
     * @return mixed
     */
    public function quoteTrustedValue(string $value): string;

    /**
     * Возвращает скленный массив экранированных значений.
     *
     * @param string|array<int, string> $valueList Массив значений.
     * 
     * @return string
     */
    public function quoteValueList(string|array $valueList): string;

    /**
     * Возвращает разделитель идентификатора.
     *
     * @return string
     */
    public function getIdentifierSeparator(): string;

    /**
     * Возвращает экранированный фрагмент.
     *
     * @param string $identifier Идентификатор.
     * @param array $additionalSafeWords Массив слов для безопасности.
     * 
     * @return string
     */
    public function quoteIdentifierInFragment(string $identifier, array $additionalSafeWords = []): string;
}
