<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Uploader;

use Ge;
use PHPThumb;

/**
 * Класс загружаемего файла изображения на сервер.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Uploader
 * @since 2.0
 */
class UploadedImageFile extends UploadedFile
{
    /**
     * @var int Ошибка загрузки (обработки) файла изображения.
     * 
     * @see UploadedFile::validate()
     */
    public const UPLOAD_ERR_IMG_FILE = 13;

    /**
     * Ширина миниатюры в пкс.
     * 
     * @var int
     */
    public int $thumbWidth = 100;

    /**
     * Высота миниатюры в пкс.
     * 
     * @var int
     */
    public int $thumbHeight = 100;

    /**
     * Слаг в имени файла миниатюры.
     * 
     * @var string
     */
    public string $thumbSlug = 'thumb';

    /**
     * Обрезать миниатюру по размеру.
     * 
     * Если значение `true`, то будет применяться адаптивное обрезание и вписывание 
     * миниатюры в указанные размеры, иначе масштабирование миниатюры.
     * 
     * @var bool
     */
    public bool $thumbCrop = false;

    /**
     * Добавить водяной знак в миниатюру.
     * 
     * @var bool
     */
    public bool $thumbWatermark = false;

    /**
     * Создать файл миниатюры.
     * 
     * @var bool
     */
    public bool $thumbCreate = false;

    /**
     * Ширина средней миниатюры в пкс.
     * 
     * @var int
     */
    public int $mediumWidth = 0;

    /**
     * Высота средней миниатюры в пкс.
     * 
     * @var int
     */
    public int $mediumHeight = 0;

    /**
     * Слаг в имени файла средней миниатюры.
     * 
     * @var string
     */
    public string $mediumSlug = 'medium';

    /**
     * Обрезать среднею миниатюру по размеру.
     * 
     * Если значение `true`, то будет применяться адаптивное обрезание и вписывание 
     * миниатюры в указанные размеры, иначе масштабирование миниатюры.
     * 
     * @var bool
     */
    public bool $mediumCrop = false;

    /**
     * Добавить водяной знак в среднею миниатюру.
     * 
     * @var bool
     */
    public bool $mediumWatermark = false;

    /**
     * Создать файл средней миниатюры.
     * 
     * @var bool
     */
    public bool $mediumCreate = false;

    /**
     * Ширина крупной миниатюры в пкс.
     * 
     * @var int
     */
    public int $largeWidth = 0;

    /**
     * Высота крупной миниатюры в пкс.
     * 
     * @var int
     */
    public int $largeHeight = 0;

    /**
     * Обрезать крупную миниатюру по размеру.
     * 
     * Если значение `true`, то будет применяться адаптивное обрезание и вписывание 
     * миниатюры в указанные размеры, иначе масштабирование миниатюры.
     * 
     * @var bool
     */
    public bool $largeCrop = false;

    /**
     * Слаг в имени файла крупной миниатюры.
     * 
     * @var string
     */
    public string $largeSlug = 'large';

    /**
     * Добавить водяной знак в крупную миниатюру.
     * 
     * @var bool
     */
    public bool $largeWatermark = false;

    /**
     * Создать файл крупной миниатюры.
     * 
     * @var bool
     */
    public bool $largeCreate = false;

    /**
     * Обрезать оригинальное изображение по размеру.
     * 
     * Если значение `true`, то будет применяться адаптивное обрезание и вписывание 
     * изображение в указанные размеры, иначе масштабирование изображения.
     * 
     * @var bool
     */
    public bool $originalCrop = false;

    /**
     * Слаг в имени файла оригинального изображения.
     * 
     * @var string
     */
    public string $originalSlug = '';

    /**
     * Ширина оригинального изображения в пкс.
     * 
     * @var int
     */
    public int $originalWidth = 0;

    /**
     * Высота оригинального изображения в пкс.
     * 
     * @var int
     */
    public int $originalHeight = 0;

    /**
     * Добавить водяной знак в оригинальное изображение.
     * 
     * @var bool
     */
    public bool $originalWatermark = false;

    /**
     * Изменить оригинальное изображение.
     * 
     * @var bool
     */
    public bool $originalApply = false;

    /**
     * Название файла водяного знака.
     * 
     * Например, '@published/uploads/img/watermark.png'.
     * 
     * @var string
     */
    public string $watermarkFile = '';

    /**
     * Положение водяного знака.
     * 
     * Например: 'center', 'right|east', 'left|west', 'bottom|lower|south', 
     *     'upper|top|north'.
     * 
     * @var string
     */
    public string $watermarkPosition = 'right|east';

    /**
     * Смещение водяного знака по вертикали.
     * 
     * @var int
     */
    public int $watermarkOffsetX = 0;

    /**
     * Смещение водяного знака по горизонтали.
     * 
     * @var int
     */
    public int $watermarkOffsetY = 0;

    /**
     * Прозрачность водяного знака.
     * 
     * От 0 до 100.
     * 
     * @var int
     */
    public int $watermarkOpacity = 50;

    /**
     * {@inheritdoc}
     */
    protected function uploaded(string $filename): bool
    {
        $this->result = ['thumb' => false, 'medium' => false, 'large' => false, 'original' => false];

        /** @var array $pluginsGD Подключаемые плагины */
        $pluginsGD = [];

        /** @var bool $checkGD Будет ли использоваться GD  */
        $checkGD = $this->originalApply || $this->largeCreate || $this->mediumCreate || $this->thumbCreate;
        if ($checkGD && !class_exists('PHPThumb\GD')) {
            $this->error = self::UPLOAD_ERR_IMG_FILE;
            Ge::error('Missing class or extension PHP: "PHPThumb"', ['line' => __LINE__, 'file' => __FILE__]);
            return false;
        }

        // если необходимо добавить водянной знак
        if ($this->originalWatermark || $this->largeWatermark || $this->mediumWatermark || $this->thumbWatermark) {
            if (empty($this->watermarkFile)) {
                $this->error = self::UPLOAD_ERR_IMG_FILE;
                Ge::error('Class property "watermarkFile" not set', ['line' => __LINE__, 'file' => __FILE__]);
                return false;
            }

            $watermarkFile = Ge::getAlias($this->watermarkFile);
            if (!is_file($watermarkFile)) {
                $this->error = self::UPLOAD_ERR_IMG_FILE;
                Ge::error('Watermark file "' . $watermarkFile . '" not exists', ['line' => __LINE__, 'file' => __FILE__]);
                return false;
            }

            /** @var PHPThumb\GD $watermark */
            $watermark = new PHPThumb\GD($watermarkFile);
            $pluginWatermark = new PHPThumb\Plugins\Watermark(
                $watermark, $this->watermarkPosition, $this->watermarkOpacity, $this->watermarkOffsetX, $this->watermarkOffsetY
            );
            $pluginsGD[] = &$pluginWatermark;
        }

        try {
            // создать мениатюру
            if ($this->thumbCreate) {
                if (empty($this->thumbSlug)) {
                    $this->error = self::UPLOAD_ERR_IMG_FILE;
                    Ge::error('Class property "thumbSlug" not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;
                }
                // если не установлены размеры
                if ($this->thumbWidth === 0 && $this->thumbHeight === 0) {
                    Ge::warning('Thumb sizes not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;
                }
                /** @var PHPThumb\GD $image */
                $image = new PHPThumb\GD($filename);
                // обрезать (вписать) изображение по размеру
                if ($this->thumbCrop)
                    $image->adaptiveResize($this->thumbWidth, $this->thumbHeight);
                else
                    $image->resize($this->thumbWidth, $this->thumbHeight);

                // если необходимо добавить водянной знак
                if ($this->thumbWatermark) {
                    // BUG: если не было преобразований, то и изображения нет
                    if ($image->getWorkingImage() === null) {
                        $image->resize();
                    }
                    $pluginWatermark->execute($image);
                }
                $size = $image->getCurrentDimensions();
                $thumbFilename = $this->addSlugToFilename($this->thumbSlug, $filename);
                $image->save($thumbFilename);
                $this->result['thumb'] = [
                    'width'     => $this->thumbWidth,
                    'height'    => $this->thumbHeight,
                    'crop'      => $this->thumbCrop,
                    'size'      => $size['width'] . 'x' . $size['height'],
                    'slug'      => $this->thumbSlug,
                    'watermark' => $this->thumbWatermark,
                    'filename'  => $thumbFilename
                ];
            }

            // создать среднею миниатюру
            if ($this->mediumCreate) {
                if (empty($this->mediumSlug)) {
                    $this->error = self::UPLOAD_ERR_IMG_FILE;
                    Ge::error('Class property "mediumSlug" not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;    
                }
                // если не установлены размеры
                if ($this->mediumWidth === 0 && $this->mediumHeight === 0) {
                    Ge::warning('Thumb medium sizes not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;
                }
                /** @var PHPThumb\GD $image */
                $image = new PHPThumb\GD($filename);
                // обрезать (вписать) изображение по размеру
                if ($this->mediumCrop)
                    $image->adaptiveResize($this->mediumWidth, $this->mediumHeight);
                else
                    $image->resize($this->mediumWidth, $this->mediumHeight);

                // если необходимо добавить водянной знак
                if ($this->mediumWatermark) {
                    // BUG: если не было преобразований, то и изображения нет
                    if ($image->getWorkingImage() === null) {
                        $image->resize();
                    }
                    $pluginWatermark->execute($image);
                }
                $size = $image->getCurrentDimensions();
                $mediumFilename = $this->addSlugToFilename($this->mediumSlug, $filename);
                $image->save($mediumFilename);
                $this->result['medium'] = [
                    'width'     => $this->mediumWidth,
                    'height'    => $this->mediumHeight,
                    'crop'      => $this->mediumCrop,
                    'size'      => $size['width'] . 'x' . $size['height'],
                    'slug'      => $this->mediumSlug,
                    'watermark' => $this->mediumWatermark,
                    'filename'  => $mediumFilename
                ];
            }

            // создать крупную миниатюру
            if ($this->largeCreate) {
                if (empty($this->largeSlug)) {
                    $this->error = self::UPLOAD_ERR_IMG_FILE;
                    Ge::error('Class property "largeSlug" not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;    
                }
                // если не установлены размеры
                if ($this->largeWidth === 0 && $this->largeHeight === 0) {
                    Ge::warning('Thumb large sizes not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;
                }
                /** @var PHPThumb\GD $image */
                $image = new PHPThumb\GD($filename);
                // обрезать (вписать) изображение по размеру
                if ($this->largeCrop)
                    $image->adaptiveResize($this->largeWidth, $this->largeHeight);
                else
                    $image->resize($this->largeWidth, $this->largeHeight);

                // если необходимо добавить водянной знак
                if ($this->largeWatermark) {
                    // BUG: если не было преобразований, то и изображения нет
                    if ($image->getWorkingImage() === null) {
                        $image->resize();
                    }
                    $pluginWatermark->execute($image);
                }
                $size = $image->getCurrentDimensions();
                $largeFilename = $this->addSlugToFilename($this->largeSlug, $filename);
                $image->save($largeFilename);
                $this->result['large'] = [
                    'width'     => $this->largeWidth,
                    'height'    => $this->largeHeight,
                    'crop'      => $this->largeCrop,
                    'size'      => $size['width'] . 'x' . $size['height'],
                    'slug'      => $this->largeSlug,
                    'watermark' => $this->largeWatermark,
                    'filename'  => $largeFilename
                ];
            }

            // изменить оригинал изображения
            if ($this->originalApply) {
                // если не установлены размеры
                if ($this->originalWidth === 0 && $this->originalHeight === 0) {
                    Ge::warning('Origial image sizes not set', ['line' => __LINE__, 'file' => __FILE__]);
                    return false;
                }
                /** @var PHPThumb\GD $image */
                $image = new PHPThumb\GD($filename, [], $pluginsGD);
                // обрезать (вписать) изображение по размеру
                if ($this->originalCrop)
                    $image->adaptiveResize($this->originalWidth, $this->originalHeight);
                else
                    $image->resize($this->originalWidth, $this->originalHeight);

                // если необходимо добавить водянной знак
                if ($this->originalWatermark) {
                    // BUG: если не было преобразований, то и изображения нет
                    if ($image->getWorkingImage() === null) {
                        $image->resize();
                    }
                    $pluginWatermark->execute($image);
                }
                $size = $image->getCurrentDimensions();
                $image->save($filename);
            } else {
                $imageSize = @getimagesize($filename);
                if ($imageSize !== false)
                    $size = ['width' => $imageSize[0] ?? 0, 'height' => $imageSize[1] ?? 0];
                else
                    $size = ['width' => 0, 'height' => 0];
            }
            $this->result['original'] = [
                'uploaded'  => $this->name,
                'apply'     => $this->originalApply,
                'width'     => $this->originalWidth,
                'height'    => $this->originalHeight,
                'size'      => $size['width'] . 'x' . $size['height'],
                'crop'      => $this->originalCrop,
                'slug'      => $this->originalSlug,
                'watermark' => $this->originalWatermark,
                'filename'  => $filename
            ];
        } catch (\Exception $e) {
            Ge::error(['message' => $e]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function codeToMessage(int $code): string
    {
        if ($code === self::UPLOAD_ERR_IMG_FILE) {
            return 'Error uploading image file';
        }
        return parent::codeToMessage($code);
    }
}
