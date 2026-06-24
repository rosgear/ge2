<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Import\Parser;

use Ge\Helper\Json;

/**
 * JSONParser класс разбора файла в формате JSON.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Import\Parser
 * @since 2.0
 */
class JsonParser extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $str): ?array
    {
        Json::$throwException = false;
        /** @var array|false $json */
        $json = Json::tryDecode($str, true);
        if ($error = Json::error()) {
            $this->addError($error);
            return null;
        }

        return [
            'title'       => $json['title'] ?? '',
            'description' => $json['description'] ?? '',
            'language'    => $json['language'] ?? '',
            'version'     => $json['version'] ?? '',
            'created'     => $json['created'] ?? '',
            'clear'       => ((int) $json['clear'] ?? 0) > 0,
            'items'       => []
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function parsePackage(string $str): ?array
    {
        Json::$throwException = false;
        /** @var array|false $json */
        $json = Json::tryDecode($str, true);
        if ($error = Json::error()) {
            $this->addError($error);
            return null;
        }

        return [
            'title'       => $json['title'] ?? '',
            'description' => $json['description'] ?? '',
            'language'    => $json['language'] ?? '',
            'version'     => $json['version'] ?? '',
            'created'     => $json['created'] ?? '',
            'properties'  => $json['properties'] ?? [],
            'components'  => $json['components'] ?? [],
            'files'       => $json['files'] ?? []
        ];
    }
}
