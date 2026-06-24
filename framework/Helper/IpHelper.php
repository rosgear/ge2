<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Helper;

use Ge\Exception;

/**
 * Вспомогательный класс IP-адреса, обеспечивает проверку и преобразование IP-адресов 
 * для версий протокола IPv4, IPv6.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Paul Gregg <pgregg@pgregg.com>
 * @package Ge\Helper
 * @since 2.0
 */
class IpHelper
{
    /**
     * @var int Номер версии IPv4-адреса.
     */
    public const IPV4 = 4;

    /**
     * @var int Номер версии IPv6-адреса.
     */
    public const IPV6 = 6;

    /**
     * @var int Длина IPv6-адреса в битах.
     */
    public const IPV6_ADDRESS_LENGTH = 128;

    /**
     * @var int Длина IPv4-адреса в битах.
     */
    public const IPV4_ADDRESS_LENGTH = 32;

    /**
     * Проверяет IP-адрес на корректность.
     * 
     * @param string $ip IP-адрес.
     * 
     * @return bool Возвращает значение `false`, если IP-адрес имеет неправильный формат.
     */
    public static function validIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) === false ? false : true;
    }

    /**
     * Проверяет IPv4-адрес на корректность.
     * 
     * @param string $ip IPv4-адрес.
     * 
     * @return bool Возвращает значение `false`, если IPv4-адрес имеет неправильный формат.
     */
    public static function validIPv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false ? false : true;
    }

    /**
     * Проверяет IPv6-адрес на корректность.
     * 
     * @param string $ip IPv6-адрес.
     * 
     * @return bool Возвращает значение `false`, если IPv6-адрес имеет неправильный формат.
     */
    public static function validIPv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false ? false : true;
    }

    /**
     * Проверяет, является ли IP-адрес частным (внутренним, локальным).
     * 
     * @param string $ip IP-адрес.
     * 
     * @return bool Возвращает значение `false`, если IP-адрес частный (внутренний, локальный).
     */
    public static function validPrivateRange(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false ? false : true;
    }

    /**
     * Возвращает номер версии указанного IP-адреса.
     *
     * @param string $ip IPv4-адрес или IPv6-адрес.
     * 
     * @return int Возвратит {@see IpHelper::IPV4} или {@see IpHelper::IPV6}.
     */
    public static function getIpVersion(string $ip): int
    {
        return strpos($ip, ':') === false ? self::IPV4 : self::IPV6;
    }

    /**
     * Расширяет IP-адрес протокола IPv6 до его полной записи.
     *
     * Например "2001:db8::1" будет "2001:0db8:0000:0000:0000:0000:0000:0001".
     *
     * @param string $ip Действующий IP-адрес, протокола IPv6.
     * 
     * @return string Расширенный IP-адрес протокола IPv6.
     */
    public static function expandIPv6(string $ip): string
    {
        $hex = unpack('H*hex', inet_pton($ip));
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * Конвертирует строку, содержащую IPv6-адрес в целое число.
     *
     * @param string $ip Действующий IPv6-адрес.
     * 
     * @return string Целое число из IPv6-адреса.
     */
    public static function ip2long6(string $ip): string
    {
        if (substr_count($ip, '::')) { 
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip); 
        } 
        $ip = explode(':', $ip);
        $r_ip = ''; 
        foreach ($ip as $v) {
            $r_ip .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT); 
        } 
        return base_convert($r_ip, 2, 10); 
    } 

    /**
     * Конвертирует строку, содержащую IPv4 или IPv6-адрес в целое число.
     *
     * @param string $ip Действующий IPv4 или IPv6-адрес.
     * 
     * @return int|false Возвращает значение `false`, неправильный формат IPv4 или 
     *     IPv6-адреса, иначе целое число.
     */
    public static function ip2long(string $ip): int|false
    {
        if (static::getIpVersion($ip) === static::IPV6)
            return static::ip2long6($ip);
        else
            return ip2long($ip);
    } 

    /**
     * Конвертирует IPv6-адрес в десятичное число.
     *
     * @param string $ip Действующий IPv6-адрес.
     * 
     * @return string Десятичное число из IPv6-адреса.
     */
    public static function getIPv6Full(string $ip): string
    {
        $pieces = explode ('/', $ip, 2);
        $left_piece = $pieces[0];
        $right_piece = $pieces[1];
        // Извлеките основные части IP
        $ip_pieces = explode('::', $left_piece, 2);
        $main_ip_piece = $ip_pieces[0];
        $last_ip_piece = $ip_pieces[1];
        // Дополняет части
        $main_ip_pieces = explode(':', $main_ip_piece);
        foreach($main_ip_pieces as $key=>$val) {
            $main_ip_pieces[$key] = str_pad($main_ip_pieces[$key], 4, "0", STR_PAD_LEFT);
        }
        // Проверяет, установлен ли последний IP-блок (часть после ::)
        $last_piece = '';
        $size = count($main_ip_pieces);
        if (trim($last_ip_piece) != '') {
            $last_piece = str_pad($last_ip_piece, 4, '0', STR_PAD_LEFT);
            // Создаёт полную форму IPV6-адреса с учетом последнего набора IP-блоков
            for ($i = $size; $i < 7; $i++) {
                $main_ip_pieces[$i] = '0000';
            }
            $main_ip_pieces[7] = $last_piece;
        }
        else {
            // Создаёт полную форму адреса IPV6
            for ($i = $size; $i < 8; $i++) {
                $main_ip_pieces[$i] = '0000';
            }
        }
        // Восстановите окончательный IPV6-адрес в полной форме
        $final_ip = implode(':', $main_ip_pieces);
        return static::ip2long6($final_ip);
    }

    /**
     * Преобразует IP-адрес в битовое представление.
     *
     * @param string $ip Действующий IPv4-адрес или IPv6-адрес.
     * 
     * @return string Биты как строка.
     * 
     * @throws Exception\NotSupportedException
     */
    public static function ip2bin(string $ip): string
    {
        $ipBinary = null;
        if (static::getIpVersion($ip) === static::IPV4) {
            $ipBinary = pack('N', ip2long($ip));
        } elseif (@inet_pton('::1') === false) {
            throw new Exception\NotSupportedException('IPv6 is not supported by inet_pton()!');
        } else {
            $ipBinary = inet_pton($ip);
        }
        $result = '';
        for ($i = 0, $iMax = strlen($ipBinary); $i < $iMax; $i += 4) {
            $result .= str_pad(decbin(unpack('N', substr($ipBinary, $i, 4))[1]), 32, '0', STR_PAD_LEFT);
        }
        return $result;
    }

    /**
     * Чтобы упростить работу с IPv4-адресами (в двоичном формате) и их 
     * сетевыми масками, их необходимо заполнить "0" до 32 символов, 
     * т.к. IPv4-адрес представляют собой 32-битныое число.
     *
     * @param string $dec Десятичное число.
     * 
     * @return string
     */
    public static function decbin32 (string $dec): string
    {
        return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
    }

    /**
     * Проверяет, находится ли IPv4-адрес в пределах допустимого диапазона.
     * 
     * @param string $ip Действующий IPv4-адрес.
     * @param string $range Диапазон IP-адреса.
     *    Диапазон может иметь формат:
     *    - "1.2.3.*" (формат подстановочного знака);
     *    - "1.2.3/24" или "1.2.3.4/255.255.255.0" (CIDR формат);
     *    - "1.2.3.0-1.2.3.255" (формат начала и конца IP-адреса);
     * 
     * @return bool Если значение `true`, указанный IPv4-адрес находится в пределах 
     *     диапазона.
     */
    public static function ipV4inRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') !== false) {
            // $range в формате IP/NETMASK
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                // $netmask в формате 255.255.0.0
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);
                return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
            } else {
                // $netmask это размер блока CIDR фиксирующий диапазон
                $x = explode('.', $range);
                while(count($x)<4) $x[] = '0';
                list($a,$b,$c,$d) = $x;
                $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
                $range_dec = ip2long($range);
                $ip_dec = ip2long($ip);

                $wildcard_dec = pow(2, (32-$netmask)) - 1;
                $netmask_dec = ~ $wildcard_dec;
                
                return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
            }
        } else {
            // диапазон может быть 255.255.*.* или 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !== false) { // a.b.*.* формат
                // конвертировать в формат A-B установкой * от 0 для A и 255 для B
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }
            if (strpos($range, '-')!== false) { // A-B формат
                list($lower, $upper) = explode('-', $range, 2);
                $lower_dec = (float)sprintf("%u",ip2long($lower));
                $upper_dec = (float)sprintf("%u",ip2long($upper));
                $ip_dec = (float)sprintf("%u",ip2long($ip));
                return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
            }
            return false;
        } 
    }

    /**
     * Проверяет, находится ли IPv6-адрес в пределах допустимого диапазона.
     * 
     * @param string $ip Действующий IPv6-адрес.
     * @param string $range Диапазон IP-адреса. Диапазон конвертируется в полный формат 
     *     IPv6-адреса.
     * 
     * @return bool Возвращает значение `true`, если указанный IPv6-адрес находится в 
     *     пределах диапазона.
     */
    public static function ipV6inRange(string $ip, string $range): bool
    {
        $pieces = explode('/', $range, 2);
        $left_piece = $pieces[0];
        $right_piece = $pieces[1];
        // Извлеките основные части IP
        $ip_pieces = explode("::", $left_piece, 2);
        $main_ip_piece = $ip_pieces[0];
        $last_ip_piece = $ip_pieces[1];
        // Дополняет вхождением
        $main_ip_pieces = explode(':', $main_ip_piece);
        foreach($main_ip_pieces as $key=>$val) {
            $main_ip_pieces[$key] = str_pad($main_ip_pieces[$key], 4, "0", STR_PAD_LEFT);
        }
        // Создайте первую и последнюю части, которые будут обозначать диапазон IPv6
        $first = $main_ip_pieces;
        $last = $main_ip_pieces;
        // Проверяет, установлен ли последний IP-блок (часть после ::)
        $last_piece = '';
        $size = count($main_ip_pieces);
        if (trim($last_ip_piece) != '') {
            $last_piece = str_pad($last_ip_piece, 4, "0", STR_PAD_LEFT);
            // Создаёт полную форму IPv6-адреса с учетом последнего набора IP-блоков
            for ($i = $size; $i < 7; $i++) {
                $first[$i] = "0000";
                $last[$i] = "ffff";
            }
            $main_ip_pieces[7] = $last_piece;
        }
        else {
            // Создаёт полную форму адреса IPv6
            for ($i = $size; $i < 8; $i++) {
                $first[$i] = "0000";
                $last[$i] = "ffff";
            }
        }
        // Восстановите окончательный IPv6-адрес в полной форме
        $first = static::ip2long6(implode(":", $first));
        $last = static::ip2long6(implode(":", $last));
        $in_range = ($ip >= $first && $ip <= $last);
        return $in_range;
    }

    /**
     * Проверяет, находится ли IP-адрес в списке допустимых диапазонов.
     * 
     * @param string $ip Действующий IPv4-адрес или IPv6-адрес.
     * @param array $list Список диапазонов IP-адресов.
     *    Диапазон может иметь формат для IPv4-адреса:
     *    - "1.2.3.*" (формат подстановочного знака);
     *    - "1.2.3/24" или "1.2.3.4/255.255.255.0" (CIDR формат);
     *    - "1.2.3.0-1.2.3.255" (формат начала и конца IP-адреса);
     * 
     * @return bool Возвращает значение `true`, если указанный IP-адрес находится 
     *     в списке.
     */
    public static function inList(string $ip, array $list): bool
    {
        foreach ($list as $ipRange) {
            if (static::inRange($ip, $ipRange)) return true;
        }
        return false;
    }

    /**
     * Проверяет, находится ли IP-адрес в пределах допустимого диапазона.
     * 
     * @param string $ip Действующий IPv4-адрес или IPv6-адрес.
     * @param string $range Диапазон IP-адреса.
     *    Диапазон может иметь формат для IPv4-адреса:
     *    - "1.2.3.*" (формат подстановочного знака);
     *    - "1.2.3/24" или "1.2.3.4/255.255.255.0" (CIDR формат);
     *    - "1.2.3.0-1.2.3.255" (формат начала и конца IP-адреса);
     * 
     * @return bool Возвращает значение `true`, если указанный IP-адрес находится в 
     *     пределах диапазона.
     */
    public static function inRange(string $ip, string $range): bool
    {
        if (static::validIPv4($ip))
            return static::ipV4inRange($ip, $range);
        else
            return static::ipV6inRange($ip, $range);
    }

    /**
     * Возвращает диапазон начального и конечного IP-адресов для указанного IPv4-адреса 
     * и его маски подсети.
     * 
     * @param string $subnet Действующий IPv4-адрес (например: "192.168.0.1").
     * @param int $mask Количество единичных разрядов в маске подсети.
     * @param bool $rangeInt Если `true`, возвращает диапазон IP-адресов в формате int.
     * 
     * @return array Диапазон IP-адресов.
     */
    public static function cidr2rangeIpV4(string $subnet, int $mask, bool $rangeInt = false): array
    {
        if ($rangeInt)
            return [ip2long($subnet), ip2long($subnet) | (pow(2,( 32 - $mask ))-1)]; 
        else 
            return [$subnet, long2ip(ip2long($subnet) | (pow(2,( 32 - $mask ))-1))];
    }

    /**
     * Возвращает диапазон начального и конечного IP-адресов для указанного IPv6-адреса 
     * и его маски подсети.
     * 
     * @param string $subnet Действующий IPv6-адрес (например: "2001:0db8:85a3:0000:0000:8a2e:0370:7334").
     * @param int $mask Количество единичных разрядов в маске подсети.
     * @param bool $rangeInt Если `true`, возвращает диапазон IP-адресов в формате int.
     * 
     * @return array Диапазон IP-адресов.
     */
    public static function cidr2rangeIpV6(string $subnet, int $mask, bool $rangeInt = false): array
    {
        return [];
    }

    /**
     * Конвертирует строку, содержащую IPv4-адрес (CIDR формат) в массив диапазона начального 
     * и конечного IP-адресов.
     * 
     * @param string $cidr Действующий IPv4-адрес или IPv6-адрес в CIDR формате 
     *     (например: "192.168.0.0/24") .
     * @param bool $rangeInt Если true, возвращает диапазон IP-адресов в формате int.
     * 
     * @return array Диапазон IP-адресов в формате IPv4, IPv6 или int.
     */
    public static function cidr2range(string $cidr, bool $rangeInt = false): array
    {
        list($subnet, $mask) = explode('/', $cidr);
        if (static::validIPv4($subnet))
            return static::cidr2rangeIpV4($subnet, $mask, $rangeInt);
        else
            return static::cidr2rangeIpV6($subnet, $mask, $rangeInt);
    }

    /**
     * Конвертирует строку, содержащую IPv4-адрес в массив диапазона начального и 
     * конечного IP-адресов.
     * 
     * @param string $ip Действующий IPv4-адрес (с указанным диапазоном).
     *    Диапазон может иметь формат для IPv4-адреса:
     *    - "1.2.3.4" (формат IPv4-адреса);
     *    - "1.2.3.*" (формат подстановочного знака);
     *    - "1.2.3.4/24" или "1.2.3.4/255.255.255.0" (CIDR формат);
     *    - "1.2.3.0-1.2.3.255" (формат начала и конца IP-адреса).

     * @return mixed Количество бит (разрядов) в указанной маске.
     */
    public static function ip2range4(string $ip, bool $rangeInt = false): mixed
    {
        // IP-адрес в формате "IP/MASK"
        if (strpos($ip, '/') !== false) {
            list($ip, $mask) = explode('/', $ip, 2);
            // если формат IP-адреса "IP/a.b.c.d" или "IP/a.b.c.*"
            if (strpos($mask, '.') !== false) {
                $cidr = static::mask2cidr(str_replace('*', '0', $mask));
                return static::cidr2rangeIpV4($ip, $cidr, $rangeInt);
            // если формат IP-адреса "IP/x"
            } else {
                return static::cidr2rangeIpV4($ip, $mask, $rangeInt);
            }
        } else
        // IP-адрес в формате "IP-IP"
        if (strpos($ip, '-') !== false) {
            $range = explode('-', $ip, 2);
            if ($rangeInt)
                return [sprintf("%u", ip2long($range[0])), sprintf("%u", ip2long($range[1]))];
            else
                return $range;
        } else
        // IP-адрес в формате "a.b.c.*"
        if (strpos($ip, '*') !== false) {
            $lower = str_replace('*', '0', $ip);
            $upper = str_replace('*', '255', $ip);
            return [ip2long($lower), ip2long($upper)];
        }
        return $rangeInt ? ip2long($ip) : $ip;
    }

    /**
     * Конвертирует строку, содержащую IPv6-адрес в массив диапазона начального и конечного IP-адресов.
     * 
     * @param string $ip Действующий IPv6-адрес (с указанным диапазоном).

     * @return int Количество бит (разрядов) в указанной маске.
     */
    public static function ip2range6(string $ip, bool $rangeInt = false): mixed
    {
        return 0;
    }

    /**
     * Конвертирует строку, содержащую IPv4 или IPv6-адрес в массив, диапазона начального и конечного IP-адресов.
     * 
     * @param string $ip Действующий IPv4 или IPv6-адрес (с указанным диапазоном).
     *    Диапазон может иметь формат для IPv4-адреса:
     *    - "1.2.3.4" (формат IPv4-адреса);
     *    - "1.2.3.*" (формат подстановочного знака);
     *    - "1.2.3.4/24" или "1.2.3.4/255.255.255.0" (CIDR формат);
     *    - "1.2.3.0-1.2.3.255" (формат начала и конца IP-адреса).

     * @return mixed Количество бит (разрядов) в указанной маске.
     */
    public static function ip2range(string $ip, bool $rangeInt = false): mixed
    {
        if (static::getIpVersion($ip) === static::IPV6)
            return static::ip2range6($ip, $rangeInt);
        else
            return static::ip2range4($ip, $rangeInt);
    }

    /**
     * Возвращает количество бит (разрядов) в маске подсети формата IPv4 или IPv6.
     * 
     * @param string $mask Маска подсети в формате IPv4 или IPv6.
     * 
     * @return int Количество бит в указанной маске.
     */
    public static function mask2cidr(string $mask): int
    {
        if (static::validIPv4($mask))
            return static::ipV4mask2cidr($mask);
        else
            return static::ipV6mask2cidr($mask);
    }

    /**
     * Возвращает количество бит (разрядов) в маске подсети формата IPv4.
     * 
     * @param string $mask Маска подсети в формате IPv4-адреса.
     * 
     * @return int Количество бит в указанной маске.
     */
    public static function ipV4mask2cidr(string $mask): int
    {
      $long = ip2long($mask);
      $base = ip2long('255.255.255.255');
      return 32 - log(($long ^ $base) + 1,2);
    }

    /**
     * Возвращает количество бит (разрядов) в маске подсети формата IPv6.
     * 
     * @param string $mask Маска подсети в формате IPv6-адреса.
     * 
     * @return int Количество бит в указанной маске.
     */
    public static function ipV6mask2cidr(string $mask): int
    {
        return 0;
    }
}
