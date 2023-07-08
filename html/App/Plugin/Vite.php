<?php

namespace App\Plugin;

use eru123\Helper\ArrayUtil as A;
use eru123\helper\Format;

class Vite
{
    static $instance = null;
    private $headers = [];
    private $seo = [];
    private $data = [];

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
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
        return $this->html_seo() . implode("\n", $this->headers);
    }

    public function template(string $template)
    {
        $this->data['template'] = $template;
    }

    public function build() {
        return Format::template(
            $this->data['template'],
            array_merge(
                $this->data,
                [
                    'headers' => $this->header_string(),
                ]
            )
        );
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
        !$this->seo['url'] || $meta[] = '<meta property="og:url" content="' . $this->seo['url'] . '">';
        !$this->seo['type'] || $meta[] = '<meta property="og:type" content="' . $this->seo['type'] . '">';
        !$this->seo['title'] || $meta[] = '<meta property="og:title" content="' . $this->seo['title'] . '">';
        !$this->seo['description'] || $meta[] = '<meta property="og:description" content="' . $this->seo['description'] . '">';
        !$this->seo['image'] || $meta[] = '<meta property="og:image" content="' . $this->seo['image'] . '">';
        !$this->seo['app_id'] || $meta[] = '<meta property="fb:app_id" content="' . $this->seo['app_id'] . '">';
        !$this->seo['locale'] || $meta[] = '<meta property="og:locale" content="' . $this->seo['locale'] . '">';

        !$this->seo['type'] || $meta[] = '<meta name="twitter:card" content="' . $this->seo['type'] . '">';

        !$this->seo['title'] || $meta[] = '<meta name="title" content="' . $this->seo['title'] . '">';
        !$this->seo['description'] || $meta[] = '<meta name="description" content="' . $this->seo['description'] . '">';
        !$this->seo['author'] || $meta[] = '<meta name="author" content="' . $this->seo['author'] . '">';
        !$this->seo['publisher'] || $meta[] = '<meta name="publisher" content="' . $this->seo['publisher'] . '">';
        !$this->seo['image'] || $meta[] = '<meta name="image" content="' . $this->seo['image'] . '">';
        !$this->seo['locale'] || $meta[] = '<meta name="locale" content="' . $this->seo['locale'] . '">';
        !$this->seo['keywords'] || $meta[] = '<meta name="keywords" content="' . (is_array($this->seo['keywords']) ? implode(',', $this->seo['keywords']) : '') . '">';

        return implode("\n", $meta);
    }
}
