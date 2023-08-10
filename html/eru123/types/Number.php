<?php

declare(strict_types=1);

namespace eru123\types;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;

define('TYPES_NUMBER_PRECISION', 14);
define('TYPES_NUMBER_NOPRECISION', -1);
define('TYPES_NUMBER_DECIMAL', '/^([-+])?([0-9]+)((\.)([0-9]+))?$/');
define('TYPES_NUMBER_SCIENTIFIC', '/^([-+])?([0-9]+)((\.)([0-9]+))?([eE]([-+])?([0-9]+))?$/');

/**
 * Number
 * 
 * @method static bool isPrime(string $number) Check if a number is prime
 * @method static string round(string $number, int $precision = TYPES_NUMBER_PRECISION) Round a number
 * @method static string match_length(string $number, string $number2) Match length of two numbers
 * @method static string add(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Add two numbers
 * @method static string sub(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Subtract two numbers
 * @method static string mul(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Multiply two numbers
 * @method static string div(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide two numbers
 * @method static string mod(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Modulus of two numbers
 * @method static string pow(string $number, int $exp, int $precision = TYPES_NUMBER_PRECISION) Power of a number
 * @method static string div_single(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide a number by a single digit number
 * @method static string comp(string $number, string $number2) Compare two numbers
 * @method static string parse(string $number, int $precision = TYPES_NUMBER_PRECISION) Parse a number to a proper format
 * 
 * @method bool isPrime() Check if the number is prime
 * @method string round(int $precision = TYPES_NUMBER_PRECISION) Round the number
 * @method string match_length(string $number2) Match length of number to another number
 * @method string add(string $number2, int $precision = TYPES_NUMBER_PRECISION) Add number to another number
 * @method string sub(string $number2, int $precision = TYPES_NUMBER_PRECISION) Subtract to a number
 * @method string mul(string $number2, int $precision = TYPES_NUMBER_PRECISION) Multiply to a number
 * @method string div(string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide to a number
 * @method string mod(string $number2, int $precision = TYPES_NUMBER_PRECISION) Modulus of a number
 * @method string pow(int $exp, int $precision = TYPES_NUMBER_PRECISION) Get the power of a number
 * @method string div_single(string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide the number by a single digit number
 * @method string comp(string $number2) Compare the number to another number
 * @method string parse(int $precision = TYPES_NUMBER_PRECISION) Parse the number to a proper format
 */
class Number
{
    protected $number;
    static $use_gmp = true;
    static $use_bc = true;

    public function __construct(string $number, int $precision = TYPES_NUMBER_PRECISION)
    {
        $this->number = static::_parse($number, $precision);
    }

    public function __toString(): string
    {
        return $this->number;
    }

    public function __invoke(): string
    {
        return $this->number;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists(self::class, '_' . $name)) {
            return call_user_func_array([self::class, '_' . $name], array_merge([$this->number], $arguments));
        }

        throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $name . '()');
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (method_exists(self::class, '_' . $name)) {
            return call_user_func_array([self::class, '_' . $name], $arguments);
        }

        throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $name . '()');
    }

    public static function _gmp(bool $use_gmp = true): void
    {
        static::$use_gmp = $use_gmp;
    }

    public static function _bc(bool $use_bc = true): void
    {
        static::$use_bc = $use_bc;
    }

    public static function _isPrime(string $number): bool
    {
        $number = static::_parse($number, 0);

        if ($number == 1 || empty($number)) {
            return false;
        }

        if (static::$use_gmp && function_exists('gmp_prob_prime')) {
            return gmp_prob_prime($number) == 2;
        }

        $n = (int) $number;
        if ($n <= 3) {
            return $n > 1;
        } elseif ($n % 2 == 0 || $n % 3 == 0) {
            return false;
        }

        for ($i = 5; $i * $i <= $n; $i += 6) {
            if ($n % $i == 0 || $n % ($i + 2) == 0) {
                return false;
            }
        }

        return true;
    }

    public static function _round(string $number, int $precision = TYPES_NUMBER_NOPRECISION): string
    {
        if (!preg_match(TYPES_NUMBER_DECIMAL, $number, $matches)) {
            return static::_parse($number, $precision);
        }

        if ($precision === TYPES_NUMBER_NOPRECISION) {
            $precision = !empty($matches[5]) ? strlen($matches[5]) : 0;
        }

        $sign = $matches[1] ?? '';
        $sign = $sign == '-' ? '-' : '';
        $int = ltrim($matches[2], '0') ?? '0';
        $dec = rtrim($matches[5] ?? '', '0') ?? '0';
        $dec = str_pad($dec, $precision, '0', STR_PAD_RIGHT);
        $dec = substr($dec, 0, $precision);

        return $precision >= 1 ? $sign . $int . '.' . $dec : $sign . $int;
    }

    public static function _floor(string $number): string
    {
        return static::_round($number, 0);
    }

    public static function _ceil(string $number): string
    {
        $number = static::_parse($number, TYPES_NUMBER_NOPRECISION);
        if (!preg_match(TYPES_NUMBER_DECIMAL, $number, $matches)) {
            return $number;
        }

        if (!empty(trim($matches[5] ?? '', '0'))) {
            return static::_add($matches[1] . $matches[2], '1', 0);
        }

        return static::_round($number, 0);
    }

    public static function _match_length(string $a, string $b): array
    {
        preg_match(TYPES_NUMBER_DECIMAL, $a, $amts);
        preg_match(TYPES_NUMBER_DECIMAL, $b, $bmts);

        $as = $amts[1] ?? '+';
        $bs = $bmts[1] ?? '+';
        $ss = $as == $bs ? true : false;
        $sc = static::_comp($a, $b);
        $ms = $sc == 1 ? $amts[1] : $bmts[1];

        $ai = $amts[2] ?? '0';
        $bi = $bmts[2] ?? '0';
        $mi = max(strlen($ai), strlen($bi));

        $ai = str_pad($ai, $mi, '0', STR_PAD_LEFT);
        $bi = str_pad($bi, $mi, '0', STR_PAD_LEFT);

        $af = $amts[5] ?? '';
        $bf = $bmts[5] ?? '';
        $mf = max(strlen($af), strlen($bf));

        $af = str_pad($af, $mf, '0', STR_PAD_RIGHT);
        $bf = str_pad($bf, $mf, '0', STR_PAD_RIGHT);

        $a = $ai . ($af ? '.' . $af : '');
        $b = $bi . ($bf ? '.' . $bf : '');
        return [$a, $b, $ss, $ms];
    }

    public static function _add(string $a, string $b, int $precision = TYPES_NUMBER_NOPRECISION): string
    {
        $a = static::_parse($a, $precision);
        $b = static::_parse($b, $precision);

        if (static::$use_gmp && function_exists('gmp_add')) {
            return static::_round(gmp_strval(gmp_add($a, $b)), $precision);
        }

        if (static::$use_bc && static::$use_bc && function_exists('bcadd')) {
            return bcadd($a, $b, $precision);
        }

        list($a, $b, $ss, $ms) = static::_match_length($a, $b);
        if ($ss) {
            $carry = 0;
            $result = '';
            for ($i = strlen($a) - 1; $i >= 0; $i--) {
                if ($a[$i] == '.') {
                    $result = '.' . $result;
                    continue;
                }

                $sum = $a[$i] + $b[$i] + $carry;
                $carry = 0;
                if ($sum >= 10) {
                    $carry = 1;
                    $sum -= 10;
                }
                $result = $sum . $result;
            }

            if ($carry) {
                $result = $carry . $result;
            }

            return $ms . $result;
        }

        $carry = 0;
        $result = '';
        for ($i = strlen($a) - 1; $i >= 0; $i--) {
            if ($a[$i] == '.') {
                $result = '.' . $result;
                continue;
            }

            $sum = (int) $a[$i] - (int) $b[$i] - $carry;
            $carry = 0;
            if ($sum < 0) {
                $carry = 1;
                $sum += 10;
            }
            $result = $sum . $result;
        }

        if ($carry) {
            $result = $carry . $result;
        }

        return $ms . $result;
    }

    public static function _sub(string $a, string $b, int $precision = TYPES_NUMBER_NOPRECISION): string
    {
        $a = static::_parse($a, $precision);
        $b = static::_parse($b, $precision);

        if (static::$use_gmp && function_exists('gmp_sub')) {
            return static::_round(gmp_strval(gmp_sub($a, $b)), $precision);
        }

        if (static::$use_bc && function_exists('bcsub')) {
            return bcsub($a, $b, $precision);
        }

        $s = strlen($b) > 0 && $b[0] == '-' ? '' : '-';
        $b = strlen($b) > 0 && in_array($b[0], ['-', '+']) ? $s . substr($b, 1) : $s . $b;
        return static::_add($a, $b, $precision);
    }

    public static function _mul(string $a, string $b, int $precision = TYPES_NUMBER_NOPRECISION): string
    {
        $a = static::_parse($a, $precision);
        $b = static::_parse($b, $precision);
        if (static::$use_gmp && function_exists('gmp_mul')) {
            return static::_round(gmp_strval(gmp_mul($a, $b)), $precision);
        }

        if (static::$use_bc && function_exists('bcmul')) {
            return bcmul($a, $b, $precision);
        }

        throw new Exception('Not implemented. Please use bc or gmp instead.');
    }

    public static function _div(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        $a = static::_parse($a);
        $b = static::_parse($b);

        if ($a == '0' || $b == '0') {
            return '0';
        }

        if (static::$use_gmp && function_exists('gmp_div')) {
            return static::_round(gmp_strval(gmp_div($a, $b)), $precision);
        }

        if (static::$use_bc && function_exists('bcdiv')) {
            return bcdiv($a, $b, $precision);
        }

        throw new Exception('Not implemented. Please use bc or gmp instead.');
    }

    public static function _div_single(string $a, string $b): string
    {
        throw new Exception('Not implemented. Please use bc or gmp instead.');
    }

    public static function _mod(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (static::$use_gmp && function_exists('gmp_mod')) {
            return static::_round(gmp_strval(gmp_mod($a, $b)), $precision);
        }

        if (static::$use_bc && function_exists('bcmod')) {
            return bcmod($a, $b, $precision);
        }

        throw new Exception('Not implemented. Please use bc or gmp instead.');
    }

    public static function _comp(string $a, string $b): int
    {
        if (!preg_match(TYPES_NUMBER_DECIMAL, $a, $am) || !preg_match(TYPES_NUMBER_DECIMAL, $b, $bm)) {
            throw new InvalidArgumentException("Invalid number format for '$a' or '$b'");
        }

        if (static::$use_gmp && function_exists('gmp_cmp')) {
            return gmp_cmp($a, $b);
        }

        if (static::$use_bc && function_exists('bccomp')) {
            return bccomp($a, $b);
        }

        $as = isset($am[1]) && !empty($am[1]) && $am[1] == '-' ? '-' : '+';
        $bs = isset($bm[1]) && !empty($bm[1]) && $bm[1] == '-' ? '-' : '+';
        $ai = isset($am[2]) && !empty($am[2]) ? $am[2] : '0';
        $ai = ltrim($ai, '0');
        $bi = isset($bm[2]) && !empty($bm[2]) ? $bm[2] : '0';
        $bi = ltrim($bi, '0');
        $af = isset($am[5]) && !empty($am[5]) ? $am[5] : '';
        $af = rtrim($af, '0');
        $bf = isset($bm[5]) && !empty($bm[5]) ? $bm[5] : '';
        $bf = rtrim($bf, '0');

        $ac = $as . $ai . '.' . ($af ? $af : '0');
        $bc = $bs . $bi . '.' . ($bf ? $bf : '0');

        if ($ac == $bc) {
            return 0;
        }
        if ($as != $bs) {
            return $as == '-' ? -1 : 1;
        }

        if ($as == '+') {
            if (strlen($ai) > strlen($bi)) {
                return 1;
            } elseif (strlen($ai) < strlen($bi)) {
                return -1;
            } else if ($ai == $bi) {
                if (strlen($af) > strlen($bf)) {
                    return 1;
                } elseif (strlen($af) < strlen($bf)) {
                    return -1;
                } else if ($af == $bf) {
                    return 0;
                }

                for ($i = 0; $i < strlen($af); $i++) {
                    if ($af[$i] > $bf[$i]) {
                        return 1;
                    } elseif ($af[$i] < $bf[$i]) {
                        return -1;
                    }
                }
            } else {
                for ($i = 0; $i < strlen($ai); $i++) {
                    if ($ai[$i] > $bi[$i]) {
                        return 1;
                    } elseif ($ai[$i] < $bi[$i]) {
                        return -1;
                    }
                }
            }
        }

        if ($as == '-') {
            if (strlen($ai) > strlen($bi)) {
                return -1;
            } elseif (strlen($ai) < strlen($bi)) {
                return 1;
            } else if ($ai == $bi) {
                if (strlen($af) > strlen($bf)) {
                    return -1;
                } elseif (strlen($af) < strlen($bf)) {
                    return 1;
                } else if ($af == $bf) {
                    return 0;
                }

                for ($i = 0; $i < strlen($af); $i++) {
                    if ($af[$i] > $bf[$i]) {
                        return -1;
                    } elseif ($af[$i] < $bf[$i]) {
                        return 1;
                    }
                }
            } else {
                for ($i = 0; $i < strlen($ai); $i++) {
                    if ($ai[$i] > $bi[$i]) {
                        return -1;
                    } elseif ($ai[$i] < $bi[$i]) {
                        return 1;
                    }
                }
            }
        }

        throw new Exception("Invalid comparison for '$a' and '$b'");
    }

    public static function _pow(string $a, string $b): string
    {
        if (static::$use_gmp && function_exists('gmp_pow')) {
            return static::_round(gmp_strval(gmp_pow($a, (int) $b)), TYPES_NUMBER_NOPRECISION);
        }

        if (static::$use_bc && function_exists('bcpow')) {
            return static::_round(bcpow($a, $b), TYPES_NUMBER_NOPRECISION);
        }

        $result = '1';
        $b = static::_parse($b);
        while (static::_comp($b, '0') > 0) {
            $result = static::_mul($result, $a);
            $b = static::_sub($b, '1');
        }

        return static::_round($result, TYPES_NUMBER_NOPRECISION);
    }

    public static function _parse(string $number, int $precision = TYPES_NUMBER_NOPRECISION): string
    {
        if (preg_match(TYPES_NUMBER_DECIMAL, $number, $matches)) {
            return static::_round($number, $precision);
        }

        if (preg_match(TYPES_NUMBER_SCIENTIFIC, $number, $matches)) {
            $precision = 0;
            out($matches, PHP_EOL);
            return static::_mul($matches[1] . $matches[2] . $matches[3], static::_pow('10', $matches[8]), $precision);
        }

        throw new InvalidArgumentException("Invalid number format for '$number'");
    }
}
