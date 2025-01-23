<?php

namespace CommerceCore\EssentialPluginsInit\WpCli;

use CommerceCore\EssentialPluginsInit\Enum\CommerceCoreWpPlugins;
use CommerceCore\EssentialPluginsInit\PluginsManagement;

class InstallPlugins
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
        // Usage: wp cc-install-plugins [--replace=<1|0>]
        if (getenv('CC_DEV')) {
            \WP_CLI::error( 'This command is not available in local development mode.' );
            exit;
        }

        try {
            $replaceExisting = !!($assocArgs['replace'] ?? null);
            $activatePlugins = !!($assocArgs['activate'] ?? true);

            if (!$activatePlugins) {
                \WP_CLI::log('Plugins will be installed but not activated...');
            }

            $this->downloadPlugins($replaceExisting, $activatePlugins);
        } catch (\Exception $exception) {
            \WP_CLI::error( $exception->getMessage() );
        }
    }

    public function downloadPlugins(bool $replaceExisting = false, bool $activatePlugins = true): void
    {
        \WP_CLI::log('Downloading plugins...');

        foreach (CommerceCoreWpPlugins::cases() as $plugin) {
            if (!$plugin->shouldBeInstalled()) {
                continue;
            }

            \WP_CLI::log(sprintf('Downloading plugin: %s', $plugin->getName()));

            try {
                $this->pluginsManagement->installPlugin(
                    $plugin,
                    null,
                    $replaceExisting,
                    true,
                    $activatePlugins
                );
            } catch (\Exception $exception) {
                \WP_CLI::warning( sprintf('%s, skipping...', $exception->getMessage()) );
                continue;
            }
        }
    }
}
