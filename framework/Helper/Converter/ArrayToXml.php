<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper\Converter;

use DOMDocument;
use DOMElement;
use Exception;
use DOMException;

/**
 * Класс преобразования массива в XML формат.
 * 
 * @link https://github.com/spatie/array-to-xml
 * 
 * @author Spatie bvba <info@spatie.be>
 * @package Ge\Helper\Converter
 * @since 2.0
 */
class ArrayToXml
{
    /**
     * Представляет весь XML-документ; корень дерева документа. 
     *
     * @var DOMDocument
     */
    protected DOMDocument $document;

    /**
     * Заменить пробелы на символы подчеркивания в именах ключей.
     *
     * @var bool
     */
    protected $replaceSpacesByUnderScoresInKeyNames = true;

    /**
     * Добавить XML объявление в документ.
     *
     * @var bool
     */
    protected $addXmlDeclaration = true;

    /**
     * Добавляет приставку к именам тегов.
     *
     * @var string
     */
    protected $numericTagNamePrefix = 'numeric_';

    /**
     * Конструктор класса.
     *
     * @param array $array Массив преобразования.
     * @param string $rootElement (по умолчанию '').
     * @param bool $replaceSpacesByUnderScoresInKeyNames Заменить пробелы на символы подчеркивания 
     *     в именах ключей (по умолчанию `true`).
     * @param string $xmlEncoding Кодировка документа как часть объявления XML (по умолчанию ''). 
     * @param string $xmlVersion Номер версии документа как часть объявления XML (по умолчанию '1.0'). 
     * @param array $domProperties
     * @param bool $xmlStandalone Указывает на то, что документ автономный. Принимает 
     *     значение `false`, если не указан. Автономный документ — документ, в котором 
     *     отсутствуют объявления внешней разметки (по умолчанию `false`).
     */
    public function __construct(
        array $array,
        string|array $rootElement = '',
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        string $xmlEncoding = '',
        string $xmlVersion = '1.0',
        array $domProperties = [],
        bool $xmlStandalone = false
    ) {
        $this->document = new DOMDocument($xmlVersion, $xmlEncoding);

        if (!is_null($xmlStandalone)) {
            $this->document->xmlStandalone = $xmlStandalone;
        }

        if (!empty($domProperties)) {
            $this->setDomProperties($domProperties);
        }

        $this->replaceSpacesByUnderScoresInKeyNames = $replaceSpacesByUnderScoresInKeyNames;

        if ($this->isArrayAllKeySequential($array) && ! empty($array)) {
            throw new DOMException('Invalid Character Error');
        }

        $root = $this->createRootElement($rootElement);
        $this->document->appendChild($root);

        $this->convertElement($root, $array);
    }

    /**
     * Устанавливает числовой префикс имени тега.
     *
     * @param string $prefix Префикс.
     * 
     * @return void
     */
    public function setNumericTagNamePrefix(string $prefix): void
    {
        $this->numericTagNamePrefix = $prefix;
    }

    /**
     * Ковертирует документ в XML.
     * 
     * @param array $array Массив преобразования.
     * @param string $rootElement (по умолчанию ''). 
     * @param bool $replaceSpacesByUnderScoresInKeyNames Заменить пробелы на символы подчеркивания 
     *     в именах ключей (по умолчанию `true`).
     * @param string $xmlEncoding Кодировка документа как часть объявления XML (по умолчанию ''). 
     * @param string $xmlVersion Номер версии документа как часть объявления XML (по умолчанию '1.0'). 
     * @param array $domProperties
     * @param bool $xmlStandalone Указывает на то, что документ автономный. Принимает 
     *     значение `false`, если не указан. Автономный документ — документ, в котором 
     *     отсутствуют объявления внешней разметки (по умолчанию `false`).
     * 
     * @return string
     */
    public static function convert(
        array $array,
        string|array $rootElement = '',
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        string $xmlEncoding = '',
        string $xmlVersion = '1.0',
        array $domProperties = [],
        bool $xmlStandalone = false
    ): string {
        $converter = new static(
            $array,
            $rootElement,
            $replaceSpacesByUnderScoresInKeyNames,
            $xmlEncoding,
            $xmlVersion,
            $domProperties,
            $xmlStandalone
        );
        return $converter->toXml();
    }

    /**
     * Возвращает XML.
     *
     * @return string
     */
    public function toXml(): string
    {
        if ($this->addXmlDeclaration === false) {
            return $this->document->saveXml($this->document->documentElement);
        }
        return $this->document->saveXML();
    }

    /**
     * Возвращает документ.
     *
     * @return DOMDocument
     */
    public function toDom(): DOMDocument
    {
        return $this->document;
    }

    /**
     * Выполняет проверку свойствам документа.
     *
     * @param array $domProperties Проверяемые свойства.
     * 
     * @return void
     * 
     * @throws Exception Cвойства не допустимые.
     */
    protected function ensureValidDomProperties(array $domProperties): void
    {
        foreach ($domProperties as $key => $value) {
            if (!property_exists($this->document, $key)) {
                throw new Exception($key.' is not a valid property of DOMDocument');
            }
        }
    }

    /**
     * Устанавливает документу свойства.
     *
     * @param array $domProperties Свойства документа.
     * 
     * @return $this
     */
    public function setDomProperties(array $domProperties): static
    {
        $this->ensureValidDomProperties($domProperties);

        foreach ($domProperties as $key => $value) {
            $this->document->{$key} = $value;
        }
        return $this;
    }

    /**
     * Добавить форматирование в XML.
     *
     * @return $this
     */
    public function prettify(): static
    {
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;

        return $this;
    }

    /**
     * Устанавливает удаление XML объявления.
     *
     * @return $this
     */
    public function dropXmlDeclaration(): static
    {
        $this->addXmlDeclaration = false;
        return $this;
    }

    /**
     * Конвертирует значение элемента.
     * 
     * @param DOMElement $element Элемент DOM.
     * @param mixed $value Значение элемента.
     * 
     * @return void
     */
    private function convertElement(DOMElement $element, mixed $value): void
    {
        $sequential = $this->isArrayAllKeySequential($value);

        if (!is_array($value)) {
            $value = htmlspecialchars($value);
            $value = $this->removeControlCharacters($value);
            $element->nodeValue = $value;
            return;
        }

        foreach ($value as $key => $data) {
            if (! $sequential) {
                if (($key === '_attributes') || ($key === '@attributes')) {
                    $this->addAttributes($element, $data);
                } elseif ((($key === '_value') || ($key === '@value')) && is_string($data)) {
                    $element->nodeValue = htmlspecialchars($data);
                } elseif ((($key === '_cdata') || ($key === '@cdata')) && is_string($data)) {
                    $element->appendChild($this->document->createCDATASection($data));
                } elseif ((($key === '_mixed') || ($key === '@mixed')) && is_string($data)) {
                    $fragment = $this->document->createDocumentFragment();
                    $fragment->appendXML($data);
                    $element->appendChild($fragment);
                } elseif ($key === '__numeric') {
                    $this->addNumericNode($element, $data);
                } elseif (substr($key, 0, 9) === '__custom:') {
                    $this->addNode($element, str_replace('\:', ':', preg_split('/(?<!\\\):/', $key)[1]), $data);
                } else {
                    $this->addNode($element, $key, $data);
                }
            } elseif (is_array($data)) {
                $this->addCollectionNode($element, $data);
            } else {
                $this->addSequentialNode($element, $data);
            }
        }
    }

    /**
     * Добавляет нумерованный элемента.
     * 
     * @param DOMElement $element Элемент DOM.
     * @param mixed $value Значение элемента.
     * 
     * @return void
     */
    protected function addNumericNode(DOMElement $element, mixed $value): void
    {
        foreach ($value as $key => $item) {
            $this->convertElement($element, [$this->numericTagNamePrefix.$key => $item]);
        }
    }

    /**
     * Добавляет узел.
     *
     * @param DOMElement $element Элемент DOM.
     * @param string $key Название ключевого элемента, который будет добавлен в $element.
     * @param string $value Значеие элемента.
     * 
     * @return void
     */
    protected function addNode(DOMElement $element, string $key, mixed $value): void
    {
        if ($this->replaceSpacesByUnderScoresInKeyNames) {
            $key = str_replace(' ', '_', $key);
        }

        $child = $this->document->createElement($key);
        $element->appendChild($child);
        $this->convertElement($child, $value);
    }

    /**
     * Добавляет коллекцию узлов.
     *
     * @param DOMElement $element Элемент DOM.
     * @param string $value Значение элемента.
     * 
     * @return void
     */
    protected function addCollectionNode(DOMElement $element, $value): void
    {
        if ($element->childNodes->length === 0 && $element->attributes->length === 0) {
            $this->convertElement($element, $value);
            return;
        }

        $child = $this->document->createElement($element->tagName);
        $element->parentNode->appendChild($child);
        $this->convertElement($child, $value);
    }

    /**
     * Добавляет последовательный узел.
     *
     * @param DOMElement $element Элемент DOM.
     * @param string $value Значение элемента.
     * 
     * @return void
     */
    protected function addSequentialNode(DOMElement $element, string $value): void
    {
        if (empty($element->nodeValue) && ! is_numeric($element->nodeValue)) {
            $element->nodeValue = htmlspecialchars($value);
            return;
        }

        $child = new DOMElement($element->tagName);
        $child->nodeValue = htmlspecialchars($value);
        $element->parentNode->appendChild($child);
    }

    /**
     * Проверяет, является ли последовательный массивом всех ключей.
     * 
     * @param array $value
     * 
     * @return bool
     */
    protected function isArrayAllKeySequential(array $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if (count($value) <= 0) {
            return true;
        }

        if (\key($value) === '__numeric') {
            return false;
        }
        return array_unique(array_map('is_int', array_keys($value))) === [true];
    }

    /**
     * Добавляет атриубты элементу.
     *
     * @param DOMElement $element Элемент DOM.
     * @param array<string, string> $data Атрибуты элемента.
     * 
     * @return void
     */
    protected function addAttributes(DOMElement $element, array $data): void
    {
        foreach ($data as $attrKey => $attrVal) {
            $element->setAttribute($attrKey, $attrVal);
        }
    }

    /**
     * Создаёт корневой элемент.
     * 
     * @link https://www.php.net/manual/ru/domdocument.createelement.php
     * 
     * @param string|array<string, array> $rootElement Название корневого элемента или его атрибуты.
     * 
     * @return DOMElement
     */
    protected function createRootElement(string|array $rootElement): DOMElement
    {
        if (is_string($rootElement)) {
            $rootElementName = $rootElement ?: 'root';
            return $this->document->createElement($rootElementName);
        }

        /** @var string $rootElementName */
        $rootElementName = $rootElement['rootElementName'] ?? 'root';
        $element = $this->document->createElement($rootElementName);

        foreach ($rootElement as $key => $value) {
            if ($key !== '_attributes' && $key !== '@attributes') {
                continue;
            }
            $this->addAttributes($element, $rootElement[$key]);
        }
        return $element;
    }

    /**
     * Удаляет управляющие символы.
     *
     * @param string $value
     * 
     * @return string
     */
    protected function removeControlCharacters(string $value): string
    {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    }
}
