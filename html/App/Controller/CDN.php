<?php

namespace App\Controller;

use App\Plugin\Upload;
use App\Plugin\R2;
use App\Plugin\DB;
use App\Plugin\Rate;
use App\Plugin\Vite;
use eru123\router\Context;

class CDN extends Controller
{
    public function index()
    {
        Vite::instance()->data([
            'app_title' => 'OpenCDN',
        ]);

        Vite::instance()->seo([
            'title' => 'OpenCDN',
            'description' => 'Fast and Free Content Delivery Network (CDN) Alternative',
            'keywords' => [
                'cdn',
                'opencdn',
                'fastcdn',
                'cdnjs',
                'jsdelivr',
                'unpkg',
                'cloudflare',
            ],
            'image' => 'https://cdn.jericho.work/stream/1/opencdn.png',
            'url' => base_url(),
            'type' => 'website'
        ]);
    }

    public function upload(Context $c)
    {
        if (Rate::ip('d', 1000, 'cdn_map')) {
            return [
                'status' => false,
                'error' => "Rate limit exceeded, please try again tomorrow.",
            ];
        } 
        // else if (Rate::ip('i', 2, 'cdn_map')) {
        //     return [
        //         'status' => false,
        //         'error' => "Rate limit exceeded, please try again after 1 minute.",
        //     ];
        // }

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

                // if (!preg_match('/(7Z|CSV|GIF|MIDI|PNG|TIF|ZIP|AVI|DOC|GZ|MKV|PPT|TIFF|ZST|AVIF|DOCX|ICO|MP3|PPTX|TTF|APK|DMG|ISO|MP4|PS|WEBM|BIN|EJS|JAR|OGG|RAR|WEBP|BMP|EOT|JPG|OTF|SVG|WOFF|BZ2|EPS|JPEG|PDF|SVGZ|WOFF2|CLASS|EXE|JS|PICT|SWF|XLS|CSS|FLAC|MID|PLS|TAR|XLSX)$/i', $f['name'])) {
                //     $fr['uploaded'] = false;
                //     $fr['message'] = "File type not allowed";
                //     $fr['file'] = [
                //         'name' => $f['name'],
                //         'mime' => $f['type'],
                //         'size' => $f['size'],
                //     ];
                // }

                if ($fr['uploaded'] && $f['size'] > 26214400) {
                    $fr['uploaded'] = false;
                    $fr['message'] = "File size exceeded 25MB limit";
                    $fr['file'] = [
                        'name' => $f['name'],
                        'mime' => $f['type'],
                        'size' => $f['size'],
                    ];
                }

                if ($fr['uploaded']) {
                    $srif = fopen($f['tmp_name'], 'r');
                    $srib = fread($srif, $f['size']);
                    fclose($srif);
                    $fo['sri'] = 'sha256-' . base64_encode(hash('sha256', $srib, true));
                    $fo['size'] = $f['size'];
                    $fo['mime'] = $f['type'];
                    $fo['name'] = $f['name'];

                    $key = date('YmdHis') . '-' . $fo['sri'] . '-' . $fo['name'];
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
                }

                if ($fr['uploaded']) {
                    $db->insert('cdn', [
                        'r2key' => $fo['key'],
                        'name' => $fo['name'],
                        'mime' => $fo['mime'],
                        'size' => $fo['size'],
                        'sri' => $fo['sri'],
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
                        $ao['tag'] = '<link rel="stylesheet" href="' . $fr['stream'] . '" integrity="' . $fo['sri'] . '" crossorigin="anonymous">';
                    }

                    if (in_array($fo['mime'], ['application/javascript', 'text/javascript'])) {
                        $ao['type'] = 'js';
                        $ao['tag'] = '<script src="' . $fr['stream'] . '" integrity="' . $fo['sri'] . '" crossorigin="anonymous"></script>';
                    }

                    if (preg_match('/^image\//', $fo['mime'])) {
                        $ao['type'] = 'image';
                        $ao['tag'] = '<img src="' . $fr['stream'] . '" alt="' . $fo['name'] . '" title="' . $fo['name'] . '">';
                    }

                    if (preg_match('/^video\//', $fo['mime'])) {
                        $ao['type'] = 'video';
                        $ao['tag'] = '<video src="' . $fr['stream'] . '" controls></video>';
                    }

                    if (preg_match('/^audio\//', $fo['mime'])) {
                        $ao['type'] = 'audio';
                        $ao['tag'] = '<audio src="' . $fr['stream'] . '" controls></audio>';
                    }

                    if (preg_match('/^font\//', $fo['mime'])) {
                        $ao['type'] = 'font';
                        $ao['tag'] = '<link rel="stylesheet" href="' . $fr['stream'] . '" integrity="' . $fo['sri'] . '" crossorigin="anonymous">';
                    }

                    if (in_array($fo['mime'], ['application/pdf'])) {
                        $ao['type'] = 'pdf';
                        $ao['tag'] = '<iframe src="' . $fr['stream'] . '" title="' . $fo['name'] . '" style="width: 100%; height: 100%; border: none;"></iframe>';
                    }

                    $fr['html'] = $ao;
                }
            }

            $result[] = $fr;
        }

        $total_uploaded = count(array_filter($result, function ($r) {
            return $r['uploaded'];
        }));

        $error_message = false;

        if (!$total_uploaded) {
            foreach ($result as $r) {
                if ($r['message']) {
                    $error_message = $r['message'];
                    break;
                }
            }

            if (!$error_message) {
                $error_message = 'No file uploaded';
            }
        }
        
        return [
            'status' => $total_uploaded > 0,
            'message' => $total_uploaded . ' file(s) uploaded successfully',
            'files' => $result,
            'error' => $error_message
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

        header('Access-Control-Allow-Origin: *');
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

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . $r2s['ContentType']);
        header('Content-Length: ' . $r2s['ContentLength']);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo $r2s['Body'];
        exit;
    }
}
