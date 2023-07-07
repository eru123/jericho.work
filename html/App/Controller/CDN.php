<?php

namespace App\Controller;

use App\Plugin\Upload;
use App\Plugin\R2;
use App\Plugin\DB;
use App\Plugin\Rate;
use eru123\router\Context;

class CDN extends Controller
{
    public function upload(Context $c)
    {
        $rate = Rate::instance()->ip('i', 1, 'cdn_map');
        $reset_time = strtotime('+1 minute');
        $reset_at = date('Y-m-d H:i:00', $reset_time);
        $reset_ts = strtotime($reset_at);
        if (!headers_sent()) {
            header('X-RateLimit-Limit: ' . $rate['limit']);
            header('X-RateLimit-Remaining: ' . $rate['remaining']);
            header('X-RateLimit-Reset: ' . $reset_at);
        }

        if ($rate['limited']) {
            if (!headers_sent()) {
                http_response_code(429);
                $time_diff = $reset_ts - time();
                header('Retry-After: ' . $time_diff);
            }
            return [
                'status' => false,
                'error' => "Rate limit exceeded. Try again in 1 minute",
            ];
        }

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

                if (!preg_match('/(7Z|CSV|GIF|MIDI|PNG|TIF|ZIP|AVI|DOC|GZ|MKV|PPT|TIFF|ZST|AVIF|DOCX|ICO|MP3|PPTX|TTF|APK|DMG|ISO|MP4|PS|WEBM|BIN|EJS|JAR|OGG|RAR|WEBP|BMP|EOT|JPG|OTF|SVG|WOFF|BZ2|EPS|JPEG|PDF|SVGZ|WOFF2|CLASS|EXE|JS|PICT|SWF|XLS|CSS|FLAC|MID|PLS|TAR|XLSX)$/i', $f['name'])) {
                    $fr['uploaded'] = false;
                    $fr['message'] = "File type not allowed";
                }

                if ($f['size'] > 5242880) {
                    $fr['uploaded'] = false;
                    $fr['message'] = "File size too large. Allowed size is 5MB.";
                }

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
                    } else {
                        $fr['message'] = "Failed to save to database";
                        $fr['uploaded'] = false;
                    }
                }

                if ($fr['uploaded']) {
                    $ao = [];

                    if (in_array($fo['mime'], ['text/css'])) {
                        $ao['type'] = 'css';
                        $ao['tag'] = '<link rel="stylesheet" href="' . $fr['stream'] . '" integrity="' . $fo['sha256'] . '" crossorigin="anonymous">';
                    }

                    if (in_array($fo['mime'], ['application/javascript', 'text/javascript'])) {
                        $ao['type'] = 'js';
                        $ao['tag'] = '<script src="' . $fr['stream'] . '" integrity="' . $fo['sha256'] . '" crossorigin="anonymous"></script>';
                    }

                    if (preg_match('/^image\//', $fo['mime'])) {
                        $ao['type'] = 'image';
                        $ao['tag'] = '<img src="' . $fr['stream'] . '" alt="' . $fo['name'] . '" title="' . $fo['name'] . '">';
                    }

                    $fr['html'] = $ao;
                }
            }

            $result[] = $fr;
        }

        return [
            'status' => true,
            'files' => $result,
        ];
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
