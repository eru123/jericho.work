<?php

namespace App\Controller;

use App\Plugin\Upload;
use App\Plugin\R2; 
use App\Plugin\DB;
use eru123\router\Context;

class CDN extends Controller
{
    public function upload(Context $c)
    {
        $files = Upload::instance()->list();
        $r2 = R2::instance();
        $db = DB::instance();
        $result = [];
        foreach ($files as $f) {
            $fr = [
                'uploaded' => false,
                'message' => $f['error_message'],
            ];

            if ($f['error'] == 0) {
                $fo = [];

                $fr['uploaded'] = true;
                $fr['message'] = 'File uploaded successfully';

                $fo['sha256'] = hash_file('sha256', $f['tmp_name']);
                $fo['size'] = $f['size'];
                $fo['mime'] = $f['type'];
                $fo['name'] = $f['name'];

                $key = date('YmdHis') . '-' . $fo['sha256'] . '-' . $fo['name'];
                $fo['key'] = $key;

                $ff = fopen($f['tmp_name'], 'r');
                $r2s = $r2->put([
                    'Key' => $fo['key'],
                    'ContentType' => $fo['mime'],
                ], $ff);
                fclose($ff);

                if ($r2s) {
                    $fr['message'] = 'File uploaded successfully';
                    $fo['url'] = $r2s['ObjectURL'];
                } else {
                    $fr['message'] = "Failed to upload is R2 Server";
                    $fr['uploaded'] = false;
                }

                if ($fr['uploaded']) {
                    $db->insert('cdn', [
                        'r2key' => $fo['key'],
                        'name' => $fo['name'],
                        'mime' => $fo['mime'],
                        'size' => $fo['size'],
                        'sha256' => $fo['sha256'],
                        'url' => $fo['url'],
                    ]);

                    $id = $db->last_insert_id();
                    if ($id) {
                        $fr['id'] = $id;
                        $fr['stream'] = cdn_stream($id, $fo['name']);
                        $fr['download'] = cdn_download($id, $fo['name']);
                        unset($fo['key']);
                        unset($fo['url']);
                        $fr['file'] = $fo;
                    }
                }
            }

            $result[] = $fr;
        }

        return $result;
    }

    public function stream(Context $c)
    {
        $db = DB::instance();
        $id = $c->params['id'];
        $rec = $db->query('SELECT * FROM `cdn` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetch();
        if (!$rec) {
            return false;
        }

        $name = $c->params['name'] ?? $rec['name'];
        $key = $rec['r2key'];

        $r2 = R2::instance();
        $r2s = $r2->get([
            'Key' => $key,
        ]);

        header('Content-Type: ' . $r2s['ContentType']);
        header('Content-Length: ' . $r2s['ContentLength']);
        header('Content-Disposition: inline; filename="' . $name . '"');
        echo $r2s['Body'];
        exit;
    }

    public function download(Context $c)
    {
        $db = DB::instance();
        $id = $c->params['id'];
        $rec = $db->query('SELECT * FROM `cdn` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetch();
        if (!$rec) {
            return false;
        }

        $name = $c->params['name'] ?? $rec['name'];
        $key = $rec['r2key'];

        $r2 = R2::instance();
        $r2s = $r2->get([
            'Key' => $key,
        ]);

        header('Content-Type: ' . $r2s['ContentType']);
        header('Content-Length: ' . $r2s['ContentLength']);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo $r2s['Body'];
        exit;
    }
}
