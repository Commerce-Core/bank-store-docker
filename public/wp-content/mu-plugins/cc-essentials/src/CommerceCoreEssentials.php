<?php

namespace CommerceCore\EssentialPluginsInit;

use CommerceCore\EssentialPluginsInit\WpCli\InstallTheme;
use CommerceCore\EssentialPluginsInit\WpCli\InstallPlugins;
use CommerceCore\EssentialPluginsInit\WpCli\InstallPluginVersion;
use CommerceCore\EssentialPluginsInit\WpCli\FixCcPlugins;

class CommerceCoreEssentials
{
    private static ?self $instance = null;

    const CC_WP_LOGIN = '/cc-adm1n.php';

    public function __construct()
    {
        $this->addCustomWpCliCommands();

        // Remove the EditURI/RSD xmlrpc.php from head
        remove_action ('wp_head', 'rsd_link');

        // Allow ACF fields edit only in dev environment
        ( defined( 'WP_CC_DEV' ) && \WP_CC_DEV )
            || add_filter( 'acf/settings/show_admin', '__return_false' );

        // Replace wp-login.php to custom login url
        $disableWpLoginReplacement = defined( 'DISABLE_CC_REPLACE_WP_LOGIN' ) && \DISABLE_CC_REPLACE_WP_LOGIN;

        if ( ! $disableWpLoginReplacement ) {
            $this->replaceWpLogin();
        }
    }

    public function addCustomWpCliCommands()
    {
        if ( defined( 'WP_CLI' ) && \WP_CLI ) {
            // Increase timeout for HTTP requests for WP-CLI commands
            add_filter( 'http_request_timeout', fn () => 120 );
            \WP_CLI::add_command( 'cc-install-theme', new InstallTheme() );
            \WP_CLI::add_command( 'cc-install-plugins', new InstallPlugins() );
            \WP_CLI::add_command( 'cc-install-plugin-version', new InstallPluginVersion() );
            \WP_CLI::add_command( 'cc-plugins-fix', new FixCcPlugins() );
        }
    }

    public function replaceWpLogin(): void
    {
        if (file_exists(ABSPATH . self::CC_WP_LOGIN)) {
            $ccReplaceLoginUrl = function (string $url): string {
                return str_replace('/wp-login.php',self::CC_WP_LOGIN, $url);
            };

            add_filter( 'login_url', $ccReplaceLoginUrl, PHP_INT_MAX );
            add_filter( 'logout_url', $ccReplaceLoginUrl, PHP_INT_MAX );
            add_filter( 'wp_redirect', $ccReplaceLoginUrl, PHP_INT_MAX );
            add_filter( 'site_url', $ccReplaceLoginUrl, PHP_INT_MAX );
        }
    }

    public static function factory(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}