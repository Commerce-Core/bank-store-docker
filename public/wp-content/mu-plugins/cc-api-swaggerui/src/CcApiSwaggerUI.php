<?php

namespace CommerceCore\CcApiSwaggerui;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class CcApiSwaggerUI
{
    public function __construct()
    {
        global $wp_version;

        if (version_compare(PHP_VERSION, '5.4', '<') || version_compare($wp_version, '4.7', '<')) {
            return;
        }

        add_action('init', [$this, 'routes']);
        add_action('wp', [$this, 'swagger']);
    }

    public function routes(): void
    {
        $base = self::rewriteBaseApi();
        add_rewrite_tag('%swagger_api%', '([^&]+)');
        add_rewrite_rule('^'.$base.'/docs/?', 'index.php?swagger_api=docs', 'top');
        add_rewrite_rule('^'.$base.'/schema/?', 'index.php?swagger_api=schema', 'top');
    }

    public static function rewriteBaseApi(): string
    {
        return apply_filters('swagger_api_rewrite_api_base', 'rest-api');
    }

    public static function pluginUrl(string $path = null): string
    {
        return plugin_dir_url(CC_SWAGGERUI_PLUGIN_FILE).$path;
    }

    public static function pluginPath(string $path): string
    {
        return plugin_dir_path(CC_SWAGGERUI_PLUGIN_FILE).$path;
    }

    public function swagger(): void
    {
        if (get_query_var('swagger_api') !== 'schema') {
            return;
        }

        $response = [
            'swagger' => '2.0',
            'info' => [
                'title' => get_option('blogname').' API',
                'description' => get_option('blogdescription'),
                'version' => get_plugin_data(WPMU_PLUGIN_DIR.'/cc-api-swaggerui.php')['Version'],
            ],
            'host' => $this->getHost(),
            'basePath' => $this->getBasePath(),
            'tags' => [],
            'schemes' => $this->getSchemes(),
            'paths' => $this->getPaths(),
        ];

        wp_send_json($response);
    }

    public function getHost(): string
    {
        $host = parse_url(home_url(), PHP_URL_HOST);
        $port = parse_url(home_url(), PHP_URL_PORT);

        if ($port) {
            if ($port != 80 && $port != 443) {
                $host = $host.':'.$port;
            }
        }

        return $host;
    }

    public function getBasePath(): string
    {
        $path = parse_url(home_url(), PHP_URL_PATH);

        return rtrim($path, '/').'/'.ltrim(rest_get_url_prefix(), '/');
    }

    public function getSchemes(): array
    {
        $schemes = [];
        if (is_ssl()) {
            $schemes[] = 'https';
        }
        $schemes[] = 'http';

        return $schemes;
    }

    public function getRawPaths(): array
    {
        $routes = rest_get_server()->get_routes();

        $raw_paths = [];
        foreach ($routes as $route => $value) {
            if (! str_contains($route, 'wp') && ! str_contains($route, 'oembed') && ! str_contains($route, 'batch') && $route !== '/' && ! preg_match('/v[0-9]+$/', $route)) {
                $raw_paths[$route] = $value;
            }
        }

        return $raw_paths;
    }

    public function getPaths(): array
    {
        $raw = $this->getRawPaths();

        $paths = [];

        foreach ($raw as $endpoint => $args) {
            $ep = $this->convertEndpoint($endpoint);
            $paths[$ep] = $this->getMethodsFromArgs($ep, $endpoint, $args);
        }

        return $paths;
    }

    public function convertEndpoint(string $endpoint): string
    {

        if (mb_strpos($endpoint, '(?P<') !== false) {
            $endpoint = preg_replace_callback('/\(\?P\<(.*?)>(.*)\)+/', function ($match) {
                return '{'.$match[1].'}';
            }, $endpoint);
        }

        return $endpoint;
    }

    public function getDefaultTagsFromEndpoint(string $endpoint): array
    {
        $parts = explode('/', trim($endpoint, '/'));

        return isset($parts[0]) ? [$parts[0]] : [];
    }

    public function getMethodsFromArgs(array|string $ep, string $endpoint, array $args): array
    {

        $path_parameters = $this->getParametersFromEndpoint($endpoint);
        $methods = [];

        $tags = $this->getDefaultTagsFromEndpoint($endpoint);

        foreach ($args as $arg) {

            $all_parameters = $this->getParametersFromArgs(
                $ep,
                isset($arg['args']) ? $arg['args'] : [],
                isset($arg['methods']) ? $arg['methods'] : []
            );

            foreach ($arg['methods'] as $method => $bool) {
                $mtd = mb_strtolower($method);
                $methodEndpoint = $mtd.str_replace('/', '_', $ep);
                $parameters = isset($all_parameters[$mtd]) ? $all_parameters[$mtd] : [];

                // Building parameters.
                $existing_names = array_map(function ($param) {
                    return $param['name'];
                }, $parameters);
                foreach ($path_parameters as $path_params) {
                    if (! in_array($path_params['name'], $existing_names, true)) {
                        $parameters[] = $path_params;
                    }
                }

                $produces = ['application/json'];
                if (isset($arg['produces'])) {
                    $produces = (array) $arg['produces'];
                }

                $consumes = [
                    'application/x-www-form-urlencoded',
                    'multipart/form-data',
                ];

                if (isset($arg['consumes'])) {
                    $consumes = (array) $arg['consumes'];
                }

                if ($arg['accept_json']) {
                    $consumes[] = ['application/json'];
                }

                if (isset($args['tags']) && is_array($args['tags'])) {
                    $tags = $args['tags'];
                }

                $responses = $this->getResponses($methodEndpoint);
                if (isset($arg['responses'])) {
                    $responses = $arg['responses'];
                }

                $conf = [
                    'tags' => $tags,
                    'summary' => isset($arg['summary']) ? $arg['summary'] : '',
                    'description' => isset($arg['description']) ? $arg['description'] : '',
                    'consumes' => $consumes,
                    'produces' => $produces,
                    'parameters' => $parameters,
                    'responses' => $responses,
                ];

                $methods[$mtd] = $conf;
            }
        }

        return $methods;
    }

    public function getParametersFromEndpoint(string $endpoint): array
    {
        $path_params = [];

        if (mb_strpos($endpoint, '(?P<') !== false && (preg_match_all('/\(\?P\<(.*?)>(.*)\)/', $endpoint, $matches))) {
            foreach ($matches[1] as $order => $match) {
                $type = strpos(mb_strtolower($matches[2][$order]), '\d') !== false ? 'integer' : 'string';
                $params = [
                    'name' => $match,
                    'in' => 'path',
                    'description' => '',
                    'required' => true,
                    'type' => $type,
                ];
                if ($type === 'integer') {
                    $params['format'] = 'int64';
                }
                $path_params[$match] = $params;
            }
        }

        return $path_params;
    }

    public function detectIn(string $param, string $mtd, string $endpoint, ?array $detail): string
    {
        if (isset($detail['in'])) {
            return $detail['in'];
        }

        switch ($mtd) {
            case strpos($endpoint, '{'.$param.'}') !== false:
                $in = 'path';
                break;
            case 'post':
                $in = 'formData';
                break;
            default:
                $in = 'query';
                break;
        }

        return $in;
    }

    public function buildParams(string $param, string $mtd, string $endpoint, array $detail): array
    {
        $type = $detail['type'];

        if (is_array($type) && isset($type[0])) {
            $type = $type[0];
        }

        if (empty($type)) {

            if (strpos($param, '_id') !== false) {
                $type = 'integer';
            } elseif (strtolower($param) === 'id') {
                $type = 'integer';
            } else {
                $type = 'string';
            }
        }

        $in = $this->detectIn($param, $mtd, $endpoint, $detail);
        $required = ! empty($detail['required']);

        if ($in === 'path') {
            $required = true;
        }

        $params = [
            'name' => $param,
            'in' => $in,
            'description' => isset($detail['description']) ? $detail['description'] : '',
            'required' => $required,
            'type' => $type,
        ];

        if (isset($detail['items'])) {
            $params['items'] = [
                'type' => isset($detail['items']['type']) ? $detail['items']['type'] : 'string',
            ];
        } elseif (isset($detail['enum'])) {
            $params['type'] = 'array';
            $items = [
                'type' => $detail['type'],
                'enum' => $detail['enum'],
            ];
            if (isset($detail['default'])) {
                $items['default'] = $detail['default'];
            }
            $params['items'] = $items;
            $params['collectionFormat'] = 'multi';
        }

        if (isset($detail['maximum'])) {
            $params['maximum'] = $detail['maximum'];
        }

        if (isset($detail['minimum'])) {
            $params['minimum'] = $detail['minimum'];
        }

        if (isset($detail['format'])) {
            $params['format'] = $detail['format'];
        } elseif ($detail['type'] === 'integer') {
            $params['format'] = 'int64';
        }

        if (isset($detail['schema'])) {
            $params['schema'] = $detail['schema'];
        }

        return $params;
    }

    public function getParametersFromArgs(string $endpoint = '', array $args = [], array $methods = []): array
    {
        $parameters = [];

        foreach ($args as $param => $detail) {
            foreach ($methods as $method => $bool) {
                $mtd = mb_strtolower($method);

                if (! isset($parameters[$mtd])) {
                    $parameters[$mtd] = [];
                }

                $parameters[$mtd][] = $this->buildParams($param, $mtd, $endpoint, $detail + ['type' => 'string']);
            }
        }

        return $parameters;
    }

    public function getResponses(string $methodEndpoint): array
    {
        return apply_filters('swagger_api_responses_'.$methodEndpoint, [
            '200' => ['description' => 'OK'],
            '404' => ['description' => 'Not Found'],
            '422' => ['description' => 'Unprocessable Entity'],
            '400' => ['description' => 'Bad Request'],
            '500' => ['description' => 'Iternal Server Error'],
        ]);
    }
}
