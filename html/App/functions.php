<?php

function base_url($path = '')
{
    $base_url = env('BASE_URL', '/');
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

function cdn_stream(string $id, string $name = null)
{
    $cdn_url = env('BASE_URL') . '/cdn';
    return rtrim($cdn_url, '/') . "/stream/$id" . ($name ? "/$name" : '');
}

function cdn_download(string $id, string $name = null)
{
    $cdn_url = env('BASE_URL') . '/cdn';
    return rtrim($cdn_url, '/') . "/download/$id" . ($name ? "/$name" : '');
}

function get_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip ?? '0';
}

function is_dev()
{
    return in_array(trim(strtolower(env('APP_ENV', 'production'))), ['dev', 'development']);
}

function append_file($file, $content)
{
    $f = realpath($file);
    if (!$f || !file_exists($f)) {
        return false;
    }
    $handle = fopen($f, 'a');
    fwrite($handle, $content);
}

function prepend_file($file, $content)
{
    $f = realpath($file);
    if (!$f || !file_exists($f)) {
        return false;
    }
    $handle = fopen($f, 'r+');
    $len = strlen($content);
    $final_len = filesize($f) + $len;
    $cache_old = fread($handle, $len);
    rewind($handle);
    $i = 1;
    while (ftell($handle) < $final_len) {
        fwrite($handle, $content);
        $content = $cache_old;
        $cache_old = fread($handle, $len);
        fseek($handle, $i * $len);
        $i++;
    }
    return true;
}

function writelog_prefix($prefix)
{
    env_set('LOGFILE_PREFIX', $prefix);
}

function writelog($content, $append = true, $file = null)
{
    if (!is_dir(__LOGS__)) {
        mkdir(__LOGS__, 0777, true);
    }

    $logdir = realpath(__LOGS__);
    if (!$logdir) {
        return false;
    }

    $prefix = env('LOGFILE_PREFIX');
    $file = $file ?? $logdir . '/' . ($prefix ? $prefix : '') . date('Y-m-d') . '.log';
    if (!file_exists($file)) {
        touch($file);
    }

    if (is_array($content) || is_object($content)) {
        $content = json_encode($content, JSON_PRETTY_PRINT);
    }
    $msg = "[" . date("Y-m-d H:i:s") . "] {$content}" . PHP_EOL;
    if (!is_writable($file)) {
        return false;
    }

    if ($append) {
        return append_file($file, $msg);
    }

    return prepend_file($file, $msg);
}

function noshell_exec(string $cmd): string|false
{
    static $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
        $options = ['bypass_shell' => true];

    if (!$proc = proc_open($cmd, $descriptors, $pipes, null, null, $options)) {
        throw new \Error('Creating child process failed');
    }

    fclose($pipes[0]);
    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    proc_close($proc);
    return $result;
}

function parallel_exec(string $cmd): void
{
    if (substr(php_uname(), 0, 7) == "Windows") {
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        exec($cmd . " > /dev/null &");
    }
}

function escape_win32_argv(string $value): string
{
    static $expr = '(
        [\x00-\x20\x7F"] # control chars, whitespace or double quote
      | \\\\++ (?=("|$)) # backslashes followed by a quote or at the end
    )ux';

    if ($value === '') {
        return '""';
    }
    $quote = false;
    $replacer = function ($match) use ($value, &$quote) {
        switch ($match[0][0]) { // only inspect the first byte of the match
            case '"': // double quotes are escaped and must be quoted
                $match[0] = '\\"';
            case ' ':
            case "\t": // spaces and tabs are ok but must be quoted
                $quote = true;
                return $match[0];
            case '\\': // matching backslashes are escaped if quoted
                return $match[0] . $match[0];
            default:
                throw new InvalidArgumentException(sprintf(
                    "Invalid byte at offset %d: 0x%02X",
                    strpos($value, $match[0]),
                    ord($match[0])
                ));
        }
    };

    $escaped = preg_replace_callback($expr, $replacer, (string)$value);
    if ($escaped === null) {
        throw preg_last_error() === PREG_BAD_UTF8_ERROR
            ? new InvalidArgumentException("Invalid UTF-8 string")
            : new Error("PCRE error: " . preg_last_error());
    }

    return $quote // only quote when needed
        ? '"' . $escaped . '"'
        : $value;
}

function escape_win32_cmd(string $value): string
{
    return preg_replace('([()%!^"<>&|])', '^$0', $value);
}

function cmdp(string|array $cmd): string
{
    if (is_array($cmd) && count($cmd) && isset($cmd[0])) {
        $f = __SCRIPTS__ . DIRECTORY_SEPARATOR . $cmd[0] . '.php';
        if (file_exists($f)) {
            array_shift($cmd);
            array_unshift($cmd, 'php', $f);
        }
    }
    $cmd = is_array($cmd) ? implode(' ', array_map(PHP_OS_FAMILY === 'Windows' ? 'escape_win32_argv' : 'escapeshellarg', $cmd)) : $cmd;
    return $cmd;
}

function cmd(string|array $cmd, $parallel = false): string|false|null
{
    $cmd = cmdp($cmd);
    $cmd = PHP_OS_FAMILY === 'Windows' ? escape_win32_cmd($cmd) : $cmd;
    return $parallel ? parallel_exec($cmd) : noshell_exec($cmd);
}
