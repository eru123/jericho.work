<?php

namespace eru123\fs;

use Exception;

class File
{

    protected $path;
    protected $name;
    protected $ext;
    protected $mime;
    protected $size;

    public function __construct($path)
    {
        $this->path = realpath($path);

        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        $this->name = basename($this->path);
        
        $this->mime = Helper::get_mime($this->name);
        $this->size = filesize($this->path);
    }

    public function stream(int|string $bytes = '128kb', bool $render = true): void
    {
        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        if ($render) {
            $this->render();
        }

        $bytes = Helper::to_bytes((string) $bytes);
        if (empty($bytes)) {
            $bytes = 128 * 1024;
        }

        $fp = fopen($this->path, 'rb');
        if (!$fp) {
            return;
        }

        $size = $this->size;
        $offset = 0;

        // if (!headers_sent() && ob_get_level() === 0) {
        if (!headers_sent()) {
            header('Content-Type: ' . $this->mime);
            header('Content-Length: ' . $size);
            header('Accept-Ranges: bytes');
        }

        while ($offset < $size && !feof($fp) && !connection_aborted()) {
            $buffer = fread($fp, $bytes);
            echo $buffer;
            flush();
            $offset += $bytes;
        }

        fclose($fp);
        exit;
    }

    public function download(): void
    {
        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        $filename = basename($this->path);
        $size = $this->size;

        header('Content-Type: ' . $this->mime);
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Accept-Ranges: bytes');
        readfile($this->path);
        exit;
    }

    public function render(): void
    {
        if (strtolower($this->ext) == 'php' || in_array($this->mime, ['text/x-php', 'application/x-php'])) {
            $f = $this->path;
            (function () use ($f) {
                include $f; // temp fix for php files
                // TODO: make file inclusion safe by using a sandbox
            })();
            exit;
        }
    }
}
