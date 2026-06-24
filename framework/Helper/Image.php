<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use GdImage;

/**
 * Вспомогательный класс Image, обеспечивает вывод и формат изображения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Helper
 * @since 2.0
 */
class Image
{
    /**
     * Тип последнего изображения, которое было создано методом createFrom.
     * 
     * Например: `IMG_PNG`, `IMG_JPEG`, `IMG_JPG`, `IMG_GIF`...
     * 
     * @var int
     */
    public static $type = 0;

    /**
     * Расширение файла последнего изображения, которое было создано методом createFrom.
     * 
     * Например: 'jpg', 'bmp', 'png', 'gif', 'wbmp', 'webp', 'xbm', 'xpm', 'tga'.
     * 
     * @var int
     */
    public static $extension = '';

    /**
     * MIME-тип последнего изображения, которое было создано методом createFrom.
     * 
     * Например, 'image/jpeg'.
     * 
     * @var int
     */
    public static $mime = '';

    /**
     * Размер последнего изображения, которое было создано методом createFrom.
     * 
     * @var array<int, int>
     */
    public static $size = [];

    /**
     * Конвертация значения цвета из шестнад-й системы в RGB
     * 
     * @param string $hex Шестнад-е значение цвета.
     * 
     * @return array<int, int>
     */
    public static function hexToRgb(string $hex): array
    {
       $hex = str_replace("#", "", $hex);
       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
          $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
          $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
       } else {
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
       }
       return [$r, $g, $b];
    }

    /**
     * Конвертация значения цвета из RGB в шестнад-ю систему
     * 
     * @param array $rgb Значение цвета RGB.
     * 
     * @return string
     */
    public static function rgbToHex(array $rgb): string
    {
       $hex = "#";
       $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
       $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
       $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
       return $hex;
    }

    /**
     * Возвращает идентификатор изображения, представляющего изображение полученное 
     * из файла с заданным именем. 
     * 
     * @param string $filename Путь к файлу изображения. 
     * 
     * @return GdImage|false
     */
    public static function createFrom(string $filename): GdImage|false
    {
        /** @var array|false $result */
        $result = getimagesize($filename);
        if ($result === false) return false;

        static::$size = [$result[0], $result[1]];
        static::$type = $result[2];
        static::$mime = $result['mime'] ?: '';
        static::$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch (static::$type) {
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($filename);
            case IMAGETYPE_BMP : return @imagecreatefrombmp($filename);
            case IMAGETYPE_PNG : return @imagecreatefrompng($filename);
            case IMAGETYPE_GIF : return @imagecreatefrompng($filename);
            //case IMAGETYPE_TGA : return @imagecreatefromtga($filename);
            case IMAGETYPE_WBMP: return @imagecreatefromwbmp($filename);
            case IMAGETYPE_WEBP: return @imagecreatefromwebp($filename);
            case IMAGETYPE_XBM : return @imagecreatefromxbm($filename);
            //case IMAGETYPE_XPM : return @imagecreatefromxpm($filename);
             //case IMAGETYPE_AVIF: return @imagecreatefromavif($filename);
        }
        return false;
    }

    /**
     * Выводит изображение в браузер или пишет в файл
     * 
     * @param GdImage $image Объект GdImage, который возвращает одна из функций, 
     *     создающих изображения.
     * @param int|null $type Тип последнего изображения.
     * @param string|null $file Путь, или открытый потоковый ресурс (который 
     *     автоматически закрывается после завершения функции), для сохранения файла. 
     *     Если не установлен или равен null, изображение будет выведено в поток вывода 
     *     в бинарном виде. 
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function image(
        GdImage $image, 
        int|null $type = null, 
        ?string $file = null, 
        array $options = []
    ): bool
    {
        if ($type === null) {
            $type = static::$type;
        }
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $file, $options['quality'] ?? -1);
            case IMAGETYPE_BMP : 
                return imagebmp($image, $file, $options['compressed'] ?? true);
            case IMAGETYPE_PNG : 
                return imagepng($image, $file, $options['quality'] ?? -1, $options['filters'] ?? -1, );
            case IMAGETYPE_GIF :
                return imagegif($image, $file);
            case IMAGETYPE_WEBP:
                return imagewbmp($image, $file, $options['foregroundСolor'] ?? null);
            case IMAGETYPE_XBM :
                return imagexbm($image, $file, $options['foregroundСolor'] ?? null);
            /*
            case 'avif':
                return imageavif($image, $file, $options['quality'] ?? -1, $options['speed'] ?? -1);
            */
        }
        return false;
    }

    /**
     * Пишет изображение в файл.
     * 
     * @param GdImage $image Объект GdImage, который возвращает одна из функций, 
     *     создающих изображения.
     * @param string $filename Путь, для сохранения файла.
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function save(
        GdImage $image, 
        string $filename, 
        array $options = []
    ): bool
    {
        return static::image(
            $image,
            null,
            $filename,
            $options
        );
    }

    /**
     * Обрезать изображение до заданного прямоугольника.
     * 
     * @param GdImage|string $imageOrFile Объект GdImage, который возвращает одна из 
     *     функций, создающих изображения или имя файла изображения.
     * @param array $rectangle Обрезанный прямоугольник в виде массива (array) с 
     *     ключами x, y, width и height. 
     * @param string|null $newFile Путь, или открытый потоковый ресурс (который 
     *     автоматически закрывается после завершения функции), для сохранения файла. 
     *     Если не установлен или равен null, изображение будет выведено в поток вывода 
     *     в бинарном виде. 
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function crop(
        GdImage|string $imageOrFile, 
        array $rectangle, 
        ?string $newFile = null, 
        array $options = []
    ): bool
    {
        /** @var GdImage|false $image */
        $image = is_string($imageOrFile) ? static::createFrom($imageOrFile) : $imageOrFile;
        if ($image === false) {
            return false;
        }

        /** @var GdImage|false $crop */
        $crop = imagecrop($image, $rectangle);
        if ($crop === false) {
            return false;
        }

        if (is_string($newFile))
            return static::save($crop, $newFile, $options);
        else
            return static::image($crop, static::$type, null, $options);
    }

    /**
     * Автоматически обрезает изображение на основе заданного режима.
     * 
     * @link https://www.php.net/manual/ru/function.imagecropauto.php
     * 
     * @param GdImage|string $imageOrFile Объект GdImage, который возвращает одна из 
     *     функций, создающих изображения или имя файла изображения.
     * @param string|null $newFile Путь, или открытый потоковый ресурс (который 
     *     автоматически закрывается после завершения функции), для сохранения файла. 
     *     Если не установлен или равен null, изображение будет выведено в поток вывода 
     *     в бинарном виде. 
     * @param int $mode Одна из констант.
     * @param float $threshold Определяет допуск в процентах, который будет использован 
     *     при сравнении цвета изображения и цвета обрезки.
     * @param int $color Либо значение цвета RGB, либо индекс палитры.
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function cropAuto(
        GdImage|string $imageOrFile,
        ?string $newFile = null, 
        int $mode = IMG_CROP_DEFAULT,
        float $threshold = 0.5,
        int $color = -1,
        array $options = []
    ): bool 
    {
        /** @var GdImage|false $image */
        $image = is_string($imageOrFile) ? static::createFrom($imageOrFile) : $imageOrFile;
        if ($image === false) {
            return false;
        }

         /** @var GdImage|false $crop */
         $crop = imagecropauto($image, IMG_CROP_DEFAULT);
         if ($crop === false) {
            return false;
        }

        if (is_string($newFile))
            return static::save($crop, $newFile, $options);
        else
            return static::image($crop, static::$type, null, $options);
    }

    /**
     * Масштабирует изображение по заданной ширине и высоте.
     * 
     * @link https://www.php.net/manual/ru/function.imagescale.php
     * 
     * @param GdImage|string $imageOrFile Объект GdImage, который возвращает одна из 
     *     функций, создающих изображения или имя файла изображения.
     * @param resource|string|null $newFile Путь, или открытый потоковый ресурс (который 
     *     автоматически закрывается после завершения функции), для сохранения файла. 
     *     Если не установлен или равен null, изображение будет выведено в поток вывода 
     *     в бинарном виде. 
     * @param int $width Ширина для масштабирования. 
     * @param int $height Высота для масштабирования изображения. Если этот параметр 
     *     опущен или отрицателен, соотношение сторон будет сохранено. 
     * @param int $mode Алгоритм интерполяции.
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function scale(
        GdImage|string $imageOrFile,
        ?string $newFile, 
        int $width,
        int $height = -1,
        int $mode = IMG_BILINEAR_FIXED,
        array $options = []
    ): bool
    {
        /** @var GdImage|false $image */
        $image = is_string($imageOrFile) ? static::createFrom($imageOrFile) : $imageOrFile;
        if ($image === false) {
            return false;
        }

         /** @var GdImage|false $scale */
         $scale = imagescale($image, $width, $height, $mode);
         if ($scale === false) {
            return false;
        }

        if (is_string($newFile))
            return static::save($scale, $newFile, $options);
        else
            return static::image($scale, static::$type, null, $options);
    }

    /**
     * Создаёт превью изображения по заданной ширине и высоте.
     * 
     * @param GdImage|string $imageOrFile Объект GdImage, который возвращает одна из 
     *     функций, создающих изображения или имя файла изображения.
     * @param string|null $newFile Путь, или открытый потоковый ресурс (который 
     *     автоматически закрывается после завершения функции), для сохранения файла thumb. 
     *     Если не установлен или равен null, изображение thumb будет выведено в поток вывода 
     *     в бинарном виде. 
     * @param int $thumbWidth Ширина изображние thumb. 
     * @param int $thumbHeight Высота изображения thumb.
     * @param array $options Параметры вывода изображения в зависимости от его формата 
     *     (jpeg, png и т.д.), например для jpeg: `['quality' => 75]`.
     * 
     * @return bool
     */
    public static function thumb(
        GdImage|string $imageOrFile,
        ?string $newFile, 
        int $thumbWidth,
        int $thumbHeight,
        array $options = []
    ): bool
    {
        /** @var GdImage|false $image */
        $image = is_string($imageOrFile) ? static::createFrom($imageOrFile) : $imageOrFile;
        if ($image === false) {
            return false;
        }

        /** @var GdImage|false $thumb */
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // 1) положение изображения: горизонтальное, вертикальное, квадратное
        $imgWidth  = static::$size[0];
        $imgHeight = static::$size[1];
        $isSquareImg     = $imgWidth === $imgHeight;
        $isHorizontalImg = $imgWidth / $imgHeight > 1;
        $isVerticalImg  = !$isHorizontalImg;
        // 2) положение превью: горизонтальное, вертикальное, квадратное
        $isSquareTh     = $thumbWidth === $thumbHeight;
        $isHorizontalTh = $thumbWidth / $thumbHeight > 1;
        $isVerticalTh  = !$isHorizontalTh;

        if ($isHorizontalImg && $isHorizontalTh) {
            // если изображение больше чем превью
            if ($imgWidth > $thumbWidth) {
                /** @var float $k Коэффициент соотношения сторон  */
                $k = $imgWidth / $thumbWidth;

                $newThumbWidth  = $imgWidth;
                $newThumbHeight = $k * $thumbHeight;
                // т.к. вписываемое изобр-е в превью будет меньше по высоте, то
                // необходимо:
                if ($newThumbHeight > $imgHeight) {
                    // залить фон превью цветом и отцентрировать изобр-е в превью по высоте
                    if (0) {
                        $top = ($thumbHeight - ($imgHeight  / $k)) / 2;
                        imagecopyresized($thumb, $image, 0, $top, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight + ($newThumbHeight - $imgHeight));
                    // обрезать изображение 
                    } else {
                        /** @var float $k Коэффициент соотношения сторон  */
                        $k = $imgHeight / $thumbHeight;

                        $newThumbWidth  = $k * $thumbWidth;
                        $newThumbHeight  = $imgHeight;

                        imagecopyresized($thumb, $image, 0, 0, $imgWidth - $newThumbWidth, 0, $thumbWidth, $thumbHeight, $imgWidth - $newThumbWidth, $imgHeight);
                    }
                } else {

                }
            }
        }

        if (is_string($newFile))
            return static::save($thumb, $newFile, $options);
        else
            return static::image($thumb, static::$type, null, $options);
    }
}
