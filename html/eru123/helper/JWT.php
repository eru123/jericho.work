<?php

namespace eru123\helper;

use DateTime;
use Exception;

class JWT
{
    public $leeway = 0;
    public $timestamp = null;
    public $algs = [
        'HS256' => 'SHA256',
        'HS384' => 'SHA384',
        'HS512' => 'SHA512'
    ];
    public $key = null;
    public $alg = null;

    public function __construct(string $key = null, string $alg = 'HS256', int $leeway = 0)
    {
        $this->key = $key;
        $this->alg = $alg;
        $this->leeway = $leeway;
    }

    public function encode(array $payload, string $key = null, string $alg = null): string
    {
        $key = $key ?? $this->key;
        $alg = $alg ?? $this->alg;

        if (empty($key)) {
            throw new Exception('Invalid secret key', 400);
        }

        if (empty($this->algs[$this->alg])) {
            throw new Exception('Algorithm not supported', 400);
        }

        $tmc_keys = ['iat', 'nbf', 'exp', 'jti'];
        foreach ($tmc_keys as $tk) {
            if (isset($payload[$tk]) && !is_numeric($payload[$tk]) && is_string($payload[$tk])) {
                $payload[$tk] = Date::parse($payload[$tk], DATE_UNIT_SECOND, DATE_TYPE_UNIT);
            }

            if (isset($payload[$tk]) && !is_numeric($payload[$tk])) {
                throw new Exception("Invalid value for $tk", 400);
            }
        }

        if (empty($payload['iat'])) {
            $payload['iat'] = Date::parse('now', DATE_UNIT_SECOND, DATE_TYPE_UNIT);
        } else {
            $payload['iat'] = Date::parse($payload['iat'], DATE_UNIT_SECOND, DATE_TYPE_UNIT);
        }

        if (empty($payload['exp'])) {
            $payload['exp'] = Date::parse('now + 1day', DATE_UNIT_SECOND, DATE_TYPE_UNIT);
        } else {
            $payload['exp'] = Date::parse($payload['exp'], DATE_UNIT_SECOND, DATE_TYPE_UNIT);
        }

        $header = ['typ' => 'JWT', 'alg' => $alg];
        $segments = [];
        $segments[] = static::urlsafeB64Encode((string) static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode((string) static::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = $this->sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public function decode(string $jwt, string $key = null, string $alg = null)
    {
        $key = $key ?? $this->key;
        $alg = $alg ?? $this->alg;

        if (empty($key)) {
            throw new Exception('Invalid secret key', 400);
        }

        if (empty($alg)) {
            throw new Exception('Algorithm not supported', 400);
        }

        $timestamp = is_null($this->timestamp) ? Date::now() : $this->timestamp;

        $tks = explode('.', $jwt);
        if (count($tks) !== 3) {
            throw new Exception('Wrong number of segments', 401);
        }

        list($headb64, $bodyb64, $cryptob64) = $tks;

        $headerRaw = static::urlsafeB64Decode($headb64);
        if (null === ($header = static::jsonDecode($headerRaw))) {
            throw new Exception('Invalid header encoding', 401);
        }

        $payloadRaw = static::urlsafeB64Decode($bodyb64);
        if (null === ($payload = static::jsonDecode($payloadRaw))) {
            throw new Exception('Invalid claims encoding', 401);
        }

        $sig = static::urlsafeB64Decode($cryptob64);

        if (!is_array($header)) {
            throw new Exception('Invalid header encoding', 401);
        }

        if (empty($header['alg'])) {
            throw new Exception('Empty algorithm', 401);
        }

        if (empty($this->algs[$header['alg']])) {
            throw new Exception('Algorithm not supported', 401);
        }

        if (!static::constantTimeEquals($alg, $header['alg'])) {
            throw new Exception('Algorithm not allowed', 401);
        }

        if (!$this->verify("{$headb64}.{$bodyb64}", $sig, $key)) {
            throw new Exception('Signature verification failed', 401);
        }

        if (isset($payload['nbf']) && $payload['nbf'] > ($timestamp + $this->leeway)) {
            throw new Exception(
                'Cannot handle token prior to ' . date(DateTime::ATOM, $payload['nbf']),
                401
            );
        }

        if (isset($payload['iat']) && $payload['iat'] > ($timestamp + $this->leeway)) {
            throw new Exception(
                'Cannot handle token prior to ' . date(DateTime::ATOM, $payload['iat']),
                401
            );
        }

        if (isset($payload['exp']) && ($timestamp - $this->leeway) >= $payload['exp']) {
            throw new Exception('Expired token', 401);
        }

        return $payload;
    }

    public function sign(string $msg, string $key, string $alg): string
    {
        $alg = $alg ?? $this->alg;
        if (empty($this->algs[$alg])) {
            throw new Exception('Algorithm not supported', 400);
        }
        $algorithm = $this->algs[$alg];
        return hash_hmac($algorithm, $msg, $key, true);
    }

    private function verify(string $msg, string $signature, string $key = null, string $alg = null): bool
    {
        $key = $key ?? $this->key;
        $alg = $alg ?? $this->alg;

        if (empty($key)) {
            throw new Exception('Invalid secret key', 400);
        }

        if (empty($this->algs[$alg])) {
            throw new Exception('Algorithm not supported');
        }

        $algorithm = $this->algs[$alg];
        $hash = hash_hmac($algorithm, $msg, $key, true);
        return static::constantTimeEquals($hash, $signature);
    }

    public static function jsonDecode(string $input)
    {
        $obj = json_decode($input, true, 512, JSON_BIGINT_AS_STRING);

        if ($errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new Exception('Null result with non-null input');
        }
        return $obj;
    }

    public static function jsonEncode(array $input): string
    {
        if (PHP_VERSION_ID >= 50400) {
            $json = json_encode($input, JSON_UNESCAPED_SLASHES);
        } else {
            $json = json_encode($input);
        }
        if ($errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new Exception('Null result with non-null input');
        }
        if ($json === false) {
            throw new Exception('Failed to encode JSON');
        }
        return $json;
    }

    public static function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    public static function constantTimeEquals(string $left, string $right): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($left, $right);
        }
        $len = min(static::safeStrlen($left), static::safeStrlen($right));

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= (ord($left[$i]) ^ ord($right[$i]));
        }
        $status |= (static::safeStrlen($left) ^ static::safeStrlen($right));

        return ($status === 0);
    }

    private static function handleJsonError(int $errno): void
    {
        $messages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters'
        ];

        throw new Exception(
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Unknown JSON error: ' . $errno,
            $errno
        );
    }

    private static function safeStrlen(string $str): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}
