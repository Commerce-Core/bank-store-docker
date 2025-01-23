<?php

namespace CommerceCore\EssentialPluginsInit\WpCli;

use CommerceCore\EssentialPluginsInit\Enum\CommerceCoreWpPlugins;
use CommerceCore\EssentialPluginsInit\PluginsManagement;

class InstallPluginVersion
{

    private PluginsManagement $pluginsManagement;
    protected $environment;

    public function __construct()
    {
        if (!defined('WP_CLI')) {
            exit;
        }

        $this->pluginsManagement = new PluginsManagement();
        $this->environment = wp_get_environment_type();
    }

    public function __invoke( $args, $assocArgs ): void
    {
        if (getenv('CC_DEV')) {
            \WP_CLI::error( 'This command is not available in local development mode.' );
            exit;
        }

        try {
            $plugin          = CommerceCoreWpPlugins::fromValue($args[0] ?? '');
            $version         = $assocArgs['version'] ?? null;
            $replaceExisting = !!($assocArgs['replace'] ?? null);

            if (!$plugin) {
                \WP_CLI::error( 'Usage: wp cc-install-plugin-version <plugin-slug> [--version=<version-number> --replace=<1|0>]' );
            }

            \WP_CLI::log(
                $version
                    ? sprintf('Installing %s version %s...',  $plugin->getName(), $version)
                    : sprintf('Installing %s latest release version...',  $plugin->getName())
            );
            $this->pluginsManagement->installPlugin($plugin, $version, $replaceExisting, true);
        } catch (\Exception $exception) {
            \WP_CLI::error( $exception->getMessage() );
        }
    }
}
