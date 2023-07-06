<?php

namespace App\Plugin;

class Upload
{
    static $instance = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function list()
    {
        $files = [];
        foreach ($_FILES as $value) {
            if (is_array($value['name'])) {
                foreach ($value['name'] as $k => $v) {
                    $e = array_combine(array_keys($value), array_column($value, $k));
                    $e['error_message'] = $this->error_message($e['error']);
                    $files[] = $e;
                }
            } else {
                $value['error_message'] = $this->error_message($value['error']);
                $files[] = $value;
            }
        }
        return $files;
    }

    public function error_message(int $errcode = 0)
    {
        switch ($errcode) {
            case UPLOAD_ERR_OK:
                return 'The file uploaded successfully';
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}
