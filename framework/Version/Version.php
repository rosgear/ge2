<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Version;

use Ge;

/**
 * Класс Версии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Version
 * @since 2.0
 */
class Version extends BaseVersion
{
    /**
     * Название версии релиза.
     * 
     * @var string
     */
    public string $releaseName = '';

    /**
     * Номер версии релиза.
     * 
     * @var string
     */
    public string $releaseNumber = '';

    /**
     * Версия редакции приложения.
     * 
     * @see Version::getEdition()
     * 
     * @var Edition|array|null
     */
    protected mixed $edition = null;

    /**
     * Сравнение.
     * 
     * @var Compare
     */
    protected Compare $compare;

    /**
     * Возвращает сравнение.
     * 
     * @return Compare
     */
    public function getCompare()
    {
        if (!isset($this->compare)) {
            $this->compare = new Compare(['version' => $this]);
        }
        return $this->compare;
    }

    /**
     * Возвращает версию редакции приложения.
     * 
     * @return Edition|null
     */
    public function getEdition(): ?Edition
    {
        if (is_array($this->edition)) {
            $this->edition = Ge::createObject('\Ge\Version\Edition', $this->edition);
        }
        return $this->edition;
    }

    /**
     * Возвращает контент заголовка "X-Powered-By" в HTTP-ответе.
     *
     * @return string
     */
    public function getPoweredBy(): string
    {
        /**
         * $release = $this->name;
         * if ($edition = $this->getEdition())
         *     $release .= ' (' . $edition->name . ')';
         * return $release; 
         */
        return $this->name;
    }

    /**
     * Возвращает контент метатега "generator".
     * 
     * @param bool $originalName Значение `true` если оригинальное название (по умолчанию `true`).
     * 
     * @return string
     */
    public function getGenerator(bool $originalName = true): string
    {
        /**
         * return $this->getReleaseName($originalName, false) . ' (' . $this->resource . ')';
         */
        return $this->name;
    }

    /**
     * Возвращает название выпуска версии (содержит имя и номер версии).
     * 
     * @param bool $originalName Значение `true` если оригинальное название (по умолчанию `true`).
     * @param bool $withEdition Если значение `true`, будет добавлено наазвание выпуска 
     *     версии редакции приложения.
     * 
     * @return string
     */
    public function getReleaseName(bool $originalName = true, bool $withEdition = false): string
    {
        $release = $this->name . ($this->number ? ' ' . $this->number : '');
        if ($withEdition && ($edition = $this->getEdition())) {
            $release .= ': ' . $edition->getReleaseName($originalName);
        }
        return $release;
    }

    /**
     * Возвращает версию пакета обновлений приложения.
     * 
     * Используется для формирования имени файла пакета обновлений.
     * 
     * Пример:
     *    - gear.cms_4.0 (не включает релиз);
     *    - gear.cms_4.0_base_1.0 (включает релиз).
     * 
     * @param bool $useRelease Добавить версию релиза (редакции) приложения.
     * 
     * @return string
     */
    public function getPackageVersion(bool $useRelease = true): string
    {
        $name = strtolower($this->name);
        $name = str_replace(' ', '.', $name);
        if ($this->number) {
            $name .= '_' . $this->number;
        }
        if ($useRelease) {
            $name .= '_' . str_replace(' ', ' ', strtolower($this->releaseName));
            if ($this->number) {
                $name .= '_' . $this->releaseNumber;
            }
        }
        return $name;
    }
}
