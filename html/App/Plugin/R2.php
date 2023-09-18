<?php

namespace App\Plugin;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception;

class R2
{
    private $r2_endpoint = null;
    private $r2_access_id = null;
    private $r2_api_key = null;
    private $r2_bucket = null;
    private $s3 = null;
    static $instance = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->r2_endpoint = env('CF_R2_HOST');
        if (empty($this->r2_endpoint)) {
            throw new Exception('CF_R2_HOST is not set', 500);
        }

        $this->r2_access_id = env('CF_R2_ACCESS_KEY_ID');
        if (empty($this->r2_access_id)) {
            throw new Exception('CF_R2_ACCESS_KEY_ID is not set', 500);
        }

        $this->r2_api_key = env('CF_R2_API_KEY');
        if (empty($this->r2_api_key)) {
            throw new Exception('CF_R2_API_KEY is not set', 500);
        }

        $this->r2_bucket = env('CF_R2_BUCKET');
        if (empty($this->r2_bucket)) {
            throw new Exception('CF_R2_BUCKET is not set', 500);
        }

        $this->s3 = new S3Client([
            'endpoint' => $this->r2_endpoint,
            'credentials' => [
                'key' => $this->r2_access_id,
                'secret' => $this->r2_api_key,
            ],
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    public function put(array $data = [], $fdata = null)
    {
        $defaults = [
            'Bucket' => $this->r2_bucket,
            'Body' => $fdata, // fopen($file, 'r'),
            'ACL' => 'public-read',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        if (empty($data['Key'])) {
            throw new Exception('Key is not set', 500);
        }

        try {
            $result = $this->s3->putObject($data);
            return $result->toArray();
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function delete(array $data = [])
    {
        $defaults = [
            'Bucket' => $this->r2_bucket,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        if (empty($data['Key'])) {
            throw new Exception('Key is not set', 500);
        }

        try {
            $result = $this->s3->deleteObject($data);
            return $result->toArray();
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function get(array $data = [])
    {
        $defaults = [
            'Bucket' => $this->r2_bucket,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        if (empty($data['Key'])) {
            throw new Exception('Key is not set', 500);
        }

        try {
            $result = $this->s3->getObject($data);
            return $result->toArray();
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function list(array $data = [])
    {
        $defaults = [
            'Bucket' => $this->r2_bucket,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        try {
            $result = $this->s3->listObjects($data);
            return $result->toArray();
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function upload(callable $action)
    {
        $files = [];
        foreach ($_FILES as $key => $value) {
            if (is_array($value['name'])) {
                foreach ($value['name'] as $k => $v) {
                    $files[$k] = [
                        'name' => $v,
                        'type' => $value['type'][$k],
                        'tmp_name' => $value['tmp_name'][$k],
                        'error' => $value['error'][$k],
                        'size' => $value['size'][$k],
                    ];
                }
            } else {
                $files[] = $value;
            }
        }

        $results = [];
        foreach ($files as $file) {
            $results[] = $action($file);
        }

        return $results;
    }
}
