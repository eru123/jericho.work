<?php

namespace eru123\fs;

class Helper
{
    public static function to_bytes(string $size): int
    {
        $size = trim($size);
        $rgx = '/^(?P<value>[0-9]+)(\s+)?(?P<unit>[a-zA-Z]+)$/';
        preg_match($rgx, $size, $matches);
        if (empty($matches)) {
            return 0;
        }

        $values = [
            'B' => 1,
            'KB' => 1024,
            'MB' => 1048576,
            'GB' => 1073741824,
            'TB' => 1099511627776,
            'PB' => 1125899906842624,
            'EB' => 1152921504606846976,
            'ZB' => 1180591620717411303424,
            'YB' => 1208925819614629174706176,
        ];

        $value = (float) $matches['value'];
        $unit = strtoupper($matches['unit']);

        if (!isset($values[$unit])) {
            return $value;
        }

        return (float) ($value * $values[$unit]);
    }
}
