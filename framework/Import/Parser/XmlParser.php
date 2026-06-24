<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Import\Parser;

use SimpleXMLElement;

/**
 * XmlParser класс разбора файла в формате XML с помощью \XMLParser.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Import\Parser
 * @since 2.0
 */
class XmlParser extends AbstractParser
{
    /**
     * Имя тега в обработчике.
     * 
     * @see XmlParser::tagOpen()
     *
     * @var string
     */
    protected string $tag = '';

    /**
     * Символьные данные полученные из обработчика.
     * 
     * @see XmlParser::characterData()
     *
     * @var string
     */
    protected string $cdata = '';

    /**
     * Путь полученный при разборе тегов на каждом шаге.
     * 
     * @see XmlParser::tagOpen()
     * 
     * @var array
     */
    protected array $tagPath = [];

    /**
     * Схема разбора тегов.
     * 
     * @see XmlParser::prepareScheme()
     * 
     * @var array
     */
    protected array $scheme = [];

    /**
     * Данные полученные при разбора тегов. 
     * 
     * @see XmlParser::tagClose()
     * 
     * @var array
     */
    protected array $dataTagPath = [];


    /**
     * Устанавливает и подготавливает схему разбора XML.
     * 
     * @param array $scheme Схема разбора XML.
     * 
     * @return void
     */
    protected function prepareScheme(array $scheme): void
    {
        $this->scheme = $scheme;
        foreach ($this->scheme as $params) {
            $this->dataTagPath[$params['path']] = [[]];
        }
    }

    /**
     * Возвращает данны согласно схеме XML.
     * 
     * @return array
     */
    protected function getDataScheme(): array
    {
        $data = [];
        foreach ($this->scheme as $params) {
            if (isset($this->dataTagPath[$params['path']])) {
                $toProperties = !empty($params['toProperties']);
                $array = $this->dataTagPath[$params['path']];
                // последний элемент - массив должен быть пустой
                if (sizeof($array) > 1) {
                    array_pop($array);
                }
                if ($toProperties)
                    $data[$params['key']] = $array[0];
                else
                    $data[$params['key']] = $array;
            }
        }
        return $data;
    }

    /**
     * Обработчик символьных данных.
     * 
     * @param \XMLParser $parser Парсер.
     * @param mixed $cdata
     * 
     * @return void
     */
    function characterData(\XMLParser $parser, $cdata): void
    {
        $this->cdata = $cdata;
    }

    /**
     * Обработчик начального элемента.
     * 
     * @param \XMLParser $parse Парсер.
     * @param string $tag Имя тега.
     * @param array $attr Атрибуты тега.
     * 
     * @return void
     */
    function tagOpen(\XMLParser $parse, string $tag, array $attr): void
    {
        $this->tag = $tag;

        $lastIndex = sizeof($this->tagPath);
        if ($lastIndex) {
            $path = $this->tagPath[$lastIndex - 1] . '/' . $tag;
            $this->tagPath[] = $path;
        } else
            $this->tagPath[] = $tag;
    }

    /**
     *  Обработчик конечного элемента.
     * 
     * @param \XMLParser $parser Парсер.
     * @param string $tag Имя тега.
     * 
     * @return void
     */
    public function tagClose(\XMLParser $parser, string $tag): void
    {
        if ($this->tag === $tag) {
            array_pop($this->tagPath);
        } else {

            $path = $this->tagPath[sizeof($this->tagPath) - 1];
            if (isset($this->dataTagPath[$path])) {
                $this->dataTagPath[$path][] = [];
            }

            array_pop($this->tagPath);
            return;
        }

        $lastIndex = sizeof($this->tagPath) - 1;
        $path = $this->tagPath[sizeof($this->tagPath) - 1];

        if (isset($this->dataTagPath[$path])) {
            $lastIndex = sizeof($this->dataTagPath[$path]) - 1;
            $this->dataTagPath[$path][$lastIndex][$tag] = $this->cdata;
        }
    }

    /**
     * Загружает строку XML для разбора.
     * 
     * @see XmlParser::parse()
     * @see XmlParser::parsePackage()
     * 
     * @param string $str Строка для разбора.
     * 
     * @return bool
     */
    public function loadXML(string $str): bool
    {
        /** @var \XMLParser $xml */
        $xml = xml_parser_create('UTF-8');
        xml_parser_set_option( $xml, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0);
        // deprecated PHP 8.4
        @xml_set_object($xml, $this);
        xml_set_character_data_handler($xml, 'characterData');
        xml_set_element_handler( $xml, 'tagOpen', 'tagClose');

        if (!xml_parse($xml, $str, true)) {
            $errorCode   = xml_get_error_code($xml);
            $errorString = xml_error_string( $errorCode );
            $this->addError('There was an error when reading this XML string (' . $errorCode . ': ' . $errorString .')');
            return false;

        }
        xml_parser_free($xml);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $str): ?array
    {
        $this->prepareScheme([
            [
                'path'  => 'data',
                'key'   => 'data',
                'toProperties' => true
            ],
            [
                'path' => 'data/items/item',
                'key'  => 'items'
            ]
        ]);

        if (!$this->loadXML($str)) {
            return null;
        }
        $data = $this->getDataScheme();
        return [
            'title'       => $data['data']['title'] ?? '',
            'description' => $data['data']['description'] ?? '',
            'language'    => $data['data']['language'] ?? '',
            'version'     => $data['data']['version'] ?? '',
            'created'     => $data['data']['created'] ?? '',
            'clear'       => ((int) $data['data']['clear'] ?? 0) > 0,
            'items'       => $data['items'] ?? [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function parsePackage(string $str): ?array
    {
        $this->prepareScheme([
            [
                'path'  => 'package',
                'key'   => 'package',
                'toProperties' => true
            ],
            [
                'path' => 'package/properties',
                'key'  => 'properties'
            ],
            [
                'path' => 'package/components/component',
                'key'  => 'components'
            ],
            [
                'path' => 'package/files/file',
                'key'  => 'files'
            ]
        ]);

        if (!$this->loadXML($str)) {
            return null;
        }
        $data = $this->getDataScheme();
        return [
            'title'       => $data['package']['title'] ?? '',
            'description' => $data['package']['description'] ?? '',
            'language'    => $data['package']['language'] ?? '',
            'version'     => $data['package']['version'] ?? '',
            'created'     => $data['package']['created'] ?? '',
            'properties'  => $data['properties'][0] ?: [],
            'components'  => $data['components'] ?? [],
            'files'       => $data['files'] ?? []
        ];
    }
}
