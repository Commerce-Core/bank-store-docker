<?php

namespace CommerceCore\EssentialPluginsInit\WpCli;

use CommerceCore\EssentialPluginsInit\Enum\CommerceCoreWpPlugins;
use CommerceCore\EssentialPluginsInit\FileSystemOperationsHelper;
use CommerceCore\EssentialPluginsInit\PluginsManagement;

// Include the necessary file to access plugin functions
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

class FixCcPlugins
{

    protected $environment;
    protected FileSystemOperationsHelper $fileSystemOperationsHelper;

    public function __construct()
    {
        if (!defined('WP_CLI')) {
            exit;
        }

        $this->environment = wp_get_environment_type();
        $this->fileSystemOperationsHelper = new FileSystemOperationsHelper();
    }

    public function __invoke( $args, $assocArgs ): void
    {
        if (getenv('CC_DEV')) {
            \WP_CLI::error( 'This command is not available in local development mode.' );
            exit;
        }

        try {
            $pluginProblems = $this->getPluginProblems();

            $pluginsToActivate = [];

            foreach ($pluginProblems as $ccPluginSlug => $pluginDirectories) {
                if (!$pluginDirectories) {
                    continue;
                }

                $pluginFile = CommerceCoreWpPlugins::fromValue($ccPluginSlug)?->getPluginFile();
                $pluginShouldBeActive = false;

                // step 1 found active plugin if exists
                $pluginToLeave = array_values(array_filter($pluginDirectories, function (string $pluginDirectory) use ($pluginFile) {
                    $pluginFilePathRelative = sprintf('%s/%s', $pluginDirectory, $pluginFile);

                    return is_plugin_active($pluginFilePathRelative);
                }))[0] ?? null;

                // step 2 if active plugin not found – take proper plugin slug first if found or first found otherwise
                if (!$pluginToLeave) {
                    $pluginToLeave = in_array($ccPluginSlug, $pluginDirectories, true)
                        ? $ccPluginSlug
                        : $pluginDirectories[0];
                } else {
                    $pluginShouldBeActive = true;
                }

                if (!$pluginToLeave) {
                    continue;
                }

                // Remove all other plugin installations
                foreach ($pluginDirectories as $pluginDirectory) {
                    if ($pluginDirectory === $pluginToLeave) {
                        continue;
                    }

                    $pluginDirectoryPath = sprintf('%s/%s', WP_PLUGIN_DIR, $pluginDirectory);
                    $this->fileSystemOperationsHelper->removeDirectory($pluginDirectoryPath);
                }


                // Rename plugin directory to proper if needed
                if ($pluginToLeave !== $ccPluginSlug) {
                    $pluginDirectoryPath = sprintf('%s/%s', WP_PLUGIN_DIR, $pluginToLeave);
                    $pluginDesiredDirectoryPath = sprintf('%s/%s', WP_PLUGIN_DIR, $ccPluginSlug);
                    $this->fileSystemOperationsHelper->renameDirectory($pluginDirectoryPath, $pluginDesiredDirectoryPath);
                }

                // Activate plugin if needed
                if ($pluginShouldBeActive) {
                    $pluginsToActivate[] = $ccPluginSlug;
                }
            }

            if ($pluginsToActivate) {
                \WP_CLI::success('Plugins to activate: ' . implode(' ', $pluginsToActivate));
            }
        } catch (\Exception $exception) {
            \WP_CLI::error( $exception->getMessage() );
        }
    }

    /**
     * @return array<string,string[]>
     */
    public function getPluginProblems(bool $includeNotInstalled = false): array
    {
        $plugins = scandir(WP_PLUGIN_DIR);
        $ccPluginsSlugs = array_map(
            fn(CommerceCoreWpPlugins $plugin) => $plugin->value,
            CommerceCoreWpPlugins::cases()
        );

        /**
         * @var array<string,string[]> $pluginProblems
         */
        $pluginProblems = [];

        foreach ($ccPluginsSlugs as $ccPluginSlug) {
            $pluginInstallations = $this->groupByStartingValue($plugins, $ccPluginSlug);

            if (
                count($pluginInstallations) > 1
                || (count($pluginInstallations) === 1 && $pluginInstallations[0] !== $ccPluginSlug)
                || ($includeNotInstalled && count($pluginInstallations) === 0)
            ) {
                $pluginProblems[$ccPluginSlug] = $pluginInstallations;
            }
        }

        return $pluginProblems;
    }

    /**
     * @param string[] $array
     * @param string $startingValue
     * @return string[]
     */
    private function groupByStartingValue(array $array, string $startingValue): array
    {
        $grouped = [];

        foreach ($array as $value) {
            // Check if the string starts with the specified value
            if (stripos($value, $startingValue) === 0) {
                $grouped[] = $value;
            }
        }

        return $grouped;
    }
}
