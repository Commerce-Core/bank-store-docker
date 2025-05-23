<?php

namespace CommerceCore\CcApiSwaggerui;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class SwaggerTemplate
{
    public function __construct()
    {
        add_action('template_include', [$this, 'view'], 99);
        add_action('wp_enqueue_scripts', [$this, 'removeQueuedScripts'], 99);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 99);
    }

    public function view(string $template): string
    {
        if (get_query_var('swagger_api') === 'docs') {
            $template = CcApiSwaggerUI::pluginPath('template/single.php');
        }

        return $template;
    }

    public function removeQueuedScripts(): void
    {
        if (get_query_var('swagger_api') === 'docs') {
            // Remove all default styles.
            global $wp_styles;
            $style_whitelist = ['admin-bar', 'dashicons'];

            if (isset($wp_styles->registered)) {
                foreach ($wp_styles->registered as $handle => $data) {
                    if (! in_array($handle, $style_whitelist)) {
                        wp_deregister_style($handle);
                        wp_dequeue_style($handle);
                    }
                }
            }

            // Remove all default scripts;
            global $wp_scripts;
            $script_whitelist = ['admin-bar'];

            if (isset($wp_scripts->registered)) {
                foreach ($wp_scripts->registered as $handle => $data) {
                    if (! in_array($handle, $script_whitelist)) {
                        wp_dequeue_script($handle);
                    }
                }
            }
        }
    }

    public function enqueueScripts(): void
    {
        if (get_query_var('swagger_api') === 'docs') {

            $info_css = $this->getAssetInfo('assets/css/app');
            wp_enqueue_style('swagger-ui', CcApiSwaggerUI::pluginUrl('assets/css/app.css'), [], $info_css['version']);

            $info_js = $this->getAssetInfo('assets/js/app');
            wp_enqueue_script('swagger-ui', CcApiSwaggerUI::pluginUrl('assets/js/app.js'), $info_js['dependencies'], $info_js['version'], true);

            $l10n = [
                'schema_url' => home_url(CcApiSwaggerUI::rewriteBaseApi().'/schema'),
            ];
            wp_localize_script('swagger-ui', 'swagger_ui_app', $l10n);
        }
    }

    public function getAssetInfo(string $name = ''): ?array
    {
        global $wp_version;
        $info = ['dependencies' => [], 'version' => $wp_version];

        $file = CcApiSwaggerUI::pluginPath($name.'.asset.php');
        if (is_readable($file)) {
            $info = include $file;
        }

        return $info;
    }
}
