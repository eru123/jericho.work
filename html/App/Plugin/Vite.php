<?php

namespace App\Plugin;

use eru123\Helper\ArrayUtil as A;
use eru123\helper\Format;
use Exception;

class Vite
{
    static $instance = null;
    private $headers = [];
    private $seo = [];
    public $data = [];
    private $template_dir = __DIR__ . '/../../client/template';
    private $template = null;
    private $entry = 'src/main.js';
    private $manifest = 'manifest.json';
    private $dist = null;

    public static function instance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function useTemplate(string $template)
    {
        $f = realpath($this->template_dir . '/' . $template . '.html');
        if ($f && file_exists($f)) {
            $this->template = file_get_contents($f);
            return;
        }

        throw new Exception("Template $template not found: " . $f);
    }

    public function setDist(string $dist)
    {
        $this->dist = rtrim($dist, '/');
    }

    public function setEntry(string $entry)
    {
        $this->entry = $entry;
    }

    public function setManifest(string $manifest)
    {
        $this->manifest = ltrim($manifest, '/');
    }

    public function setAppId(string $app_id)
    {
        $this->data['app_id'] = $app_id;
    }

    public function footer(string $footer)
    {
        if (isset($this->data['footers'])) {
            $this->data['footers'] .= $footer;
        } else {
            $this->data['footers'] = $footer;
        }
    }

    public function extend(string $extend)
    {
        if (isset($this->data['extends'])) {
            $this->data['extends'] .= $extend;
        } else {
            $this->data['extends'] = $extend;
        }
    }

    public function data(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function header(string $header)
    {
        $this->headers[] = $header;
    }

    public function headers(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function header_string()
    {
        return implode("\n", $this->headers) . $this->html_seo();
    }

    public function template(string $template)
    {
        $this->template = $template;
    }

    public function build($minify = false)
    {
        $res = Format::template(
            $this->template,
            array_merge(
                $this->data,
                [
                    'headers' => $this->header_string(),
                ]
            ),
            FORMAT_TEMPLATE_DOLLAR_CURLY
        );

        if ($minify) {
            $res = preg_replace(
                array(
                    '/ {2,}/',
                    '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s',
                ),
                array(
                    ' ',
                    '',
                ),
                $res
            );
        }

        return $res;
    }

    public function render($base = '', $minify = true)
    {
        $manifest = json_decode(file_get_contents($this->dist . '/' . $this->manifest), true);
        $entry = isset($manifest[$this->entry]) ? $manifest[$this->entry]['file'] : null;
        $css = isset($manifest[$this->entry]) ? $manifest[$this->entry]['css'] : [];
        $base = rtrim($base, '/');
        foreach ($css as $c) {
            $path = $base . '/' . $c;
            $this->header("<link rel=\"stylesheet\" href=\"$path\">");
        }
        $this->data([
            'base' => $base,
            'entry' => $entry,
        ]);
        return $this->build($minify);
    }

    public function seo(array $data)
    {
        $seo = $this->seo;
        $this->seo = [
            'url' => A::get($data, 'url', A::get($seo, 'url')),
            'type' => A::get($data, 'type', A::get($seo, 'type')),
            'title' => A::get($data, 'title', A::get($seo, 'title')),
            'description' => A::get($data, 'description', A::get($seo, 'description')),
            'image' => A::get($data, 'image', A::get($seo, 'image')),
            'app_id' => A::get($data, 'app_id', A::get($seo, 'app_id')),
            'locale' => A::get($data, 'locale', A::get($seo, 'locale')),
            'author' => A::get($data, 'author', A::get($seo, 'author')),
            'publisher' => A::get($data, 'publisher', A::get($seo, 'publisher')),
            'keywords' => A::get($data, 'keywords', A::get($seo, 'keywords')),
        ];
    }

    public function html_seo()
    {
        $meta = [];
        !isset($this->seo['url']) || $meta[] = '<meta property="og:url" content="' . $this->seo['url'] . '">';
        !isset($this->seo['type']) || $meta[] = '<meta property="og:type" content="' . $this->seo['type'] . '">';
        !isset($this->seo['title']) || $meta[] = '<meta property="og:title" content="' . $this->seo['title'] . '">';
        !isset($this->seo['description']) || $meta[] = '<meta property="og:description" content="' . $this->seo['description'] . '">';
        !isset($this->seo['image']) || $meta[] = '<meta property="og:image" content="' . $this->seo['image'] . '">';
        !isset($this->seo['app_id']) || $meta[] = '<meta property="fb:app_id" content="' . $this->seo['app_id'] . '">';
        !isset($this->seo['locale']) || $meta[] = '<meta property="og:locale" content="' . $this->seo['locale'] . '">';

        !isset($this->seo['type']) || $meta[] = '<meta name="twitter:card" content="' . $this->seo['type'] . '">';

        !isset($this->seo['title']) || $meta[] = '<meta name="title" content="' . $this->seo['title'] . '">';
        !isset($this->seo['description']) || $meta[] = '<meta name="description" content="' . $this->seo['description'] . '">';
        !isset($this->seo['author']) || $meta[] = '<meta name="author" content="' . $this->seo['author'] . '">';
        !isset($this->seo['publisher']) || $meta[] = '<meta name="publisher" content="' . $this->seo['publisher'] . '">';
        !isset($this->seo['image']) || $meta[] = '<meta name="image" content="' . $this->seo['image'] . '">';
        !isset($this->seo['locale']) || $meta[] = '<meta name="locale" content="' . $this->seo['locale'] . '">';
        !isset($this->seo['keywords']) || $meta[] = '<meta name="keywords" content="' . (is_array($this->seo['keywords']) ? implode(',', $this->seo['keywords']) : '') . '">';

        return implode("", $meta);
    }
}
