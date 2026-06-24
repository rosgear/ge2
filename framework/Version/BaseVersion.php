<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Version;

use Ge\Stdlib\BaseObject;

/**
 * Базовый класс Версии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Version
 * @since 2.0
 */
class BaseVersion extends BaseObject
{
    /**
     * Название версии.
     * 
     * @var string
     */
    public string $name = '';

    /**
     * Исходное название версии.
     * 
     * @var string
     */
    public string $originalName = '';

    /**
     * Номер версии.
     * 
     * @var string
     */
    public string $number = '';

    /**
     * URL-адрес информации о версии.
     * 
     * @var string
     */
    public string $resource = '';

    /**
     * URL-адрес документации.
     * 
     * @var string
     */
    public string $docsResource = '';

    /**
     * Дата выпуска версии.
     * 
     * @var string
     */
    public string $date = '';

  /**
     * Возвращает дату выпуска версии в указанном формате.
     * 
     * @param null|string $format Формат даты {@link https://www.php.net/manual/ru/datetime.format.php}.
     *    Если значение `null`, форматирование даты не будет.
     * 
     * @return string
     */
    public function getDate(?string $format = null): string
    {
        if ($format === null) {
            return $this->date;
        }
        return $this->date ? date($format, strtotime($this->date)) : '';
    }

    /**
     * Возвращает имя версии в формате HTML.
     *
     * @return string
     */
    public function nameToHtml(): string
    {
        if ($this->name) {
            $words = explode(' ', $this->name);
            $html = '';
            for ($i = 0; $i < sizeof($words); $i++) {
                $html .= '<span class="name-part-' . ($i + 1) . '">' . $words[$i] . '</span>';
            }
            return $html;
        }
        return '';
    }

    /**
     * Возвращает номер версии в формате HTML.
     *
     * @return string
     */
    public function numberToHtml(): string
    {
        return $this->number ? '<span class="version-part>' . $this->number . '</span>' : '';
    }

    /**
     * Возвращает версию в формате HTML.
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->nameToHtml() . ' ' . $this->numberToHtml();
    }

    /**
     * Возвращает название выпуска версии (содержит имя и номер версии).
     *
     * @param bool $originalName Значение `true` если оригинальное название (по умолчанию `true`).
     * 
     * @return string
     */
    public function getReleaseName(bool $originalName = true): string
    {
        if ($originalName)
            return $this->originalName . ($this->number ? ' ' . $this->number : '');
        else
            return $this->name . ($this->number ? ' ' . $this->number : '');
    }

    /**
     * Сравнение текущего номера версии с указанной.
     * 
     * @param string $version  строка версии (вида "0.1.2").
     * 
     * @return int -1 если $version старше текущей,
     *              0 если версия такая же,
     *             +1 если $version новее
     */
    public function compareNumber(string $version): int
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower($this->number));
    }

    /**
     * Сравнивает название и номер версии с текущей.
     * 
     * @param string $name Название.
     * @param null|string $number Номер версии, например '4.3.2RC1' (по умолчанию `null`).
     * 
     * @return bool Если значение `false`, указанный номер или название версии не 
     *     соответствует текущей.
     */
    public function compare(string $name, ?string $number = null): bool
    {
        if ($name !== $this->name) {
            return false;
        }
        return $number ? version_compare($number, $this->number, '<=') : false;
    }
}
