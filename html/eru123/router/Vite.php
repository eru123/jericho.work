<?php

namespace eru123\router;

use Exception;
use eru123\helper\Format;

class Vite
{
    protected static $str_tpl_name = null;
    protected static $str_template = null;
    protected static $str_manifest = null;
    protected static $arr_head = [];
    protected static $arr_body = [];
    protected static $arr_data = [];
    protected static $dir_src = null;
    protected static $dir_pub = null;
    protected static $dir_dis = null;

    /**
     * Return the dev template for react
     * @return string
     */
    protected static function template_react(): string
    {
        return
            '<html lang="en">'
            . '<head>'
            . '<meta charset="UTF-8" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'
            . '${head}'
            . '</head>'
            . '<body>'
            . '<div id="${app_id}"></div>'
            . '${body}'
            . '<script type="module">'
            . 'import RefreshRuntime from \'${base_uri}/@react-refresh\''
            . 'RefreshRuntime.injectIntoGlobalHook(window)'
            . 'window.$RefreshReg$ = () => { }'
            . 'window.$RefreshSig$ = () => (type) => type'
            . 'window.__vite_plugin_react_preamble_installed__ = true'
            . '</script>'
            . '<script type="module" src="${base_uri}/@vite/client"></script>'
            . '<script type="module" src="${base_uri}/${entry}"></script>'
            . '</body>'
            . '</html>';
    }

    /**
     * Return the dev template for vite, this should work on all frameworks except react
     * @return string
     */
    protected static function template_dev()
    {
        return
            '<html lang="en">'
            . '<head>'
            . '<meta charset="UTF-8" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'
            . '${head}'
            . '</head>'
            . '<body>'
            . '<div id="${app_id}"></div>'
            . '${body}'
            . '<script type="module" src="${base_uri}/@vite/client"></script>'
            . '<script type="module" src="${base_uri}/${entry}"></script>'
            . '</body>'
            . '</html>';
    }

    /**
     * Return the production template for vite, this should work on all frameworks
     * @return string
     */
    protected static function template_vite()
    {
        return
            '<html lang="en">'
            . '<head>'
            . '<meta charset="UTF-8" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'
            . '${head}'
            . '</head>'
            . '<body>'
            . '<div id="${app_id}"></div>'
            . '${body}'
            . '<script type="module" src="${base_uri}/${entry}"></script>'
            . '</body>'
            . '</html>';
    }

    /**
     * Set the template to use
     * @param string $mode The template to use (vite, react, dev) vite for production, react for react dev, dev for vite dev
     * @return void
     */
    public static function template(string $mode = 'vite')
    {
        switch ($mode) {
            case 'react':
                static::$str_template = static::template_react();
                static::$str_tpl_name = 'react';
                break;
            case 'dev':
                static::$str_template = static::template_dev();
                static::$str_tpl_name = 'dev';
                break;
            case 'vite':
            default:
                static::$str_tpl_name = 'vite';
                static::$str_template = static::template_vite();
                break;
        }
    }

    /**
     * Set key value pair of data to be used in the template
     * @param string $key The key to be used in the template
     * @param string $value The value to be used in the template
     * @return void
     */
    public static function set(string $key, string $value)
    {
        static::$arr_data[$key] = $value;
    }

    /**
     * Add a raw html string to the head of the template
     * @param string $html The html string to be added to the head
     * @return void
     */
    public static function head(string $html)
    {
        static::$arr_head[] = $html;
    }

    /**
     * Add a raw html string to the body of the template
     * @param string $html The html string to be added to the body
     * @return void
     */
    public static function body(string $html)
    {
        static::$arr_body[] = $html;
    }

    /**
     * Set the path to the manifest.json file
     * @param string $path The path to the manifest.json file
     * @return void
     */
    public static function manifest(string $path)
    {
        static::$str_manifest = $path;
    }

    /**
     * Set the path to the src directory
     * @param string $path The path to the src directory
     * @return void
     */
    public static function src(string $path)
    {
        static::$dir_src = $path;
    }

    /**
     * Set the path to the public directory
     * @param string $path The path to the public directory
     * @return void
     */
    public static function public(string $path)
    {
        static::$dir_pub = $path;
    }

    /**
     * Set the path to the dist directory
     * @param string $path The path to the dist directory
     * @return void
     */
    public static function dist(string $path)
    {
        static::$dir_dis = $path;
    }

    /**
     * Render the template into html
     * @param array $data A key value pair of data to be used in the template
     * @param bool $minify Whether to minify the html or not
     * @return string The rendered html
     */
    public static function render(array $data = [], bool $minify = true): string
    {
        if (is_null(static::$str_template)) {
            static::template();
        }

        $tpl_data = $data + static::$arr_data + ['body' => implode('', static::$arr_body), 'head' => implode('', static::$arr_head)];
        $dev = in_array(static::$str_tpl_name, ['react', 'dev']);

        if (!isset($tpl_data['app_id'])) {
            switch (static::$str_tpl_name) {
                case 'react':
                    $tpl_data['app_id'] = 'root';
                    break;
                case 'dev':
                    $tpl_data['app_id'] = 'app';
                    break;
                default:
                    $tpl_data['app_id'] = 'app';
                    break;
            }
        }

        if (!isset($tpl_data['base_uri'])) {
            $tpl_data['base_uri'] = $dev ? 'http://localhost:5173' : '';
        }

        if (!isset($tpl_data['entry'])) {
            $tpl_data['entry'] = 'src/main.js';
        }

        if (!$dev) {
            $manifest_path = realpath(static::$str_manifest);
            if (!$manifest_path || !file_exists($manifest_path)) {
                throw new Exception('Manifest file not found');
            }

            $manifest = json_decode(file_get_contents($manifest_path), true);
            $entry = $tpl_data['entry'];
            if (!isset($manifest[$entry]) || !isset($manifest[$entry]['isEntry']) || !$manifest[$entry]['isEntry']) {
                throw new Exception('Invalid manifest file or entry file path');
            }

            $css = isset($manifest[$entry]['css']) ? $manifest[$entry]['css'] : [];
            $tpl_data['entry'] = $manifest[$entry]['file'];

            foreach ($css as $css_file) {
                $tpl_data['head'] .= '<link rel="stylesheet" href="' . $tpl_data['base_uri'] . '/' . $css_file . '">';
            }
        }

        $html = Format::template(
            static::$str_template,
            $tpl_data,
            FORMAT_TEMPLATE_DOLLAR_CURLY
        );

        $html = Format::template(
            $html,
            $tpl_data,
            FORMAT_TEMPLATE_DOLLAR_CURLY
        );

        return $minify ? preg_replace(['/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ', ''], $html) : $html;
    }

    /**
     * Inject the Vite handler into the app
     * @param Router $router The reference to the router object
     * @param array $data A key value pair of data to be used in the template
     * @return void
     */
    public static function inject(Router &$router, array $data = [], $minify = true)
    {
        $prod = !in_array(static::$str_tpl_name, ['dev', 'react']);
        $router->bootstrap(function (Context $ctx) use ($data, $minify) {
            $ctx->vite = function (array $data2 = []) use ($data, $minify) {
                return static::render($data2 + $data, $minify);
            };
        });

        if ($prod) {
            $router->static('/', static::$dir_dis);
        } else {
            $router->static('/', static::$dir_pub);
            $router->static('/src/', static::$dir_src);
        }
    }
}
