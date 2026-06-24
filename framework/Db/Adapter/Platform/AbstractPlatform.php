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
 * Абстрактный класса платформы адаптера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Adapter\Platform
 * @since 2.0
 */
abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * Строковый формат даты и времени.
     * 
     * @var string
     */
    public string $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * Строковый формат даты.
     * 
     * @var string
     */
    public string $dateFormat = 'Y-m-d';

    /**
     * Строковый формат времени.
     * 
     * @var string
     */
    public string $timeFormat = 'H:i:s';

    /**
     * Экранировать идентификатор.
     * 
     * @var bool
     */
    public bool $quoteIdentifiers = true;

    /**
     * Экран идентификатора.
     * 
     * @var array<int, string>
     */
    public array $quoteIdentifier = ['"', '"'];

    /**
     * Замена экрана на символ.
     * 
     * @see AbstractPlatform::quoteIdentifier()
     * 
     * @var string
     */
    public string $quoteIdentifierTo = '\'';

    /**
     * {@inheritdoc}
     */
    public function quoteIdentifierInFragment(string $identifier, array $safeWords = []): string
    {
        if (!$this->quoteIdentifiers) {
            return $identifier;
        }

        $safeWordsInt = ['*' => true, ' ' => true, '.' => true, 'as' => true];

        foreach ($safeWords as $sWord) {
            $safeWordsInt[strtolower($sWord)] = true;
        }

        $parts = preg_split(
            '/([^0-9,a-z,A-Z$_:])/i',
            $identifier,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        $identifier = '';

        foreach ($parts as $part) {
            $identifier .= isset($safeWordsInt[strtolower($part)])
                ? $part
                : $this->quoteIdentifier[0]
                . str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $part)
                . $this->quoteIdentifier[1];
        }
        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function quoteIdentifier(string $identifier): string
    {
        if (!$this->quoteIdentifiers) {
            return $identifier;
        }
        return $this->quoteIdentifier[0]
            . str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $identifier)
            . $this->quoteIdentifier[1];
    }

    /**
     * {@inheritdoc}
     */
    public function quoteIdentifierChain(string|array $identifierChain): string
    {
        return '"' . implode('"."', (array) str_replace('"', '\\"', $identifierChain)) . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteIdentifierSymbol(): string
    {
        return $this->quoteIdentifier[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteValueSymbol(): string
    {
        return '\'';
    }

    /**
     * {@inheritdoc}
     */
    public function quoteValue(string $value): string
    {
        trigger_error(
            'Attempting to quote a value in ' . get_class($this) .
            ' without extension/driver support can introduce security vulnerabilities in a production environment'
        );
        return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
    }

    /**
     * {@inheritdoc}
     */
    public function quoteTrustedValue(string $value): string
    {
        return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
    }

    /**
     * {@inheritdoc}
     */
    public function quoteValueList(string|array $valueList): string
    {
        return implode(', ', array_map([$this, 'quoteValue'], (array) $valueList));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierSeparator(): string
    {
        return '.';
    }
}
