<?php

namespace CommerceCore\EssentialPluginsInit;

use CommerceCore\EssentialPluginsInit\Enum\CommerceCoreWpPlugins;

class PluginsManagement
{
    private GitHubClient $gitHubClient;
    private FileSystemOperationsHelper $fileSystemOperationsHelper;

    public function __construct()
    {
        $this->gitHubClient = new GitHubClient();
        $this->fileSystemOperationsHelper = new FileSystemOperationsHelper();
        WP_Filesystem();
    }

    public function installPlugin(
        CommerceCoreWpPlugins $plugin,
        ?string $version = null,
        bool $allowExistingPluginReplacement = false,
        bool $showWpCliLogs = false,
        bool $activatePlugin = true
    ): void
    {
        if (
            !$allowExistingPluginReplacement
            && file_exists($pluginFilePath = $this->getPluginFilePath($plugin))
        ) {
            throw new \Exception(sprintf('%s already exists', $pluginFilePath));
        }

        $zipContent = $this->gitHubClient->getPluginZipRequestBody(
            $plugin->getRepositoryUser(),
            $plugin->value,
            $version ? urlencode($version) : null
        );

        if (!$zipContent) {
            throw new \Exception(sprintf('Plugin: %s zip request was not is successful', $plugin->getName()));
        }

        $tempFile = wp_tempnam();
        file_put_contents($tempFile, $zipContent);

        if (is_wp_error(unzip_file($tempFile, WP_PLUGIN_DIR))) {
            throw new \Exception(sprintf('Error unzipping %s with %s, terminating', $tempFile, $plugin->value));
        }

        $pluginFolderPath = glob(WP_PLUGIN_DIR . '/' . $plugin->getRepositoryUser() . '-' . $plugin->value . '-*', GLOB_ONLYDIR);
        $desiredPluginPath = WP_PLUGIN_DIR . '/' . $plugin->value;

        if (file_exists($desiredPluginPath)) {
            $showWpCliLogs && \WP_CLI::warning(sprintf('Removing existing %s...', $desiredPluginPath));
            $this->fileSystemOperationsHelper->removeDirectory($desiredPluginPath);
        }

        $showWpCliLogs && \WP_CLI::log(sprintf('Renaming %s to %s...', $pluginFolderPath[0], $desiredPluginPath));
        rename($pluginFolderPath[0], $desiredPluginPath);

        if (!$activatePlugin || !$plugin->shouldBeActivated()) {
            return;
        }

        $pluginMainFile = sprintf('%s/%s/%s', WP_PLUGIN_DIR, $plugin->value, $plugin->getPluginFile());

        if (file_exists($pluginMainFile)) {
            $showWpCliLogs && \WP_CLI::log(sprintf('Activating %s...', $plugin->getName()));
            $activationResult = activate_plugin(plugin_basename($pluginMainFile));

            $showWpCliLogs && is_wp_error($activationResult)
                ? \WP_CLI::warning(sprintf('Error activating %s: %s', $plugin->getName(), $activationResult->get_error_message()))
                : \WP_CLI::success(sprintf('%s was successfully activated', $plugin->getName()));

        } else {
            $showWpCliLogs && \WP_CLI::warning(sprintf('%s main file not found', $plugin->getName()));
        }
    }

    function getPluginFilePath(CommerceCoreWpPlugins $plugin): string
    {
        $pluginFolderPath = glob(WP_PLUGIN_DIR . '/' . $plugin->value . '-*', GLOB_ONLYDIR);

        if (! empty($pluginFolderPath) && file_exists($pluginFolderPath[0] . '/' . $plugin->getPluginFile())) {
            return $pluginFolderPath[0] . '/' . $plugin->getPluginFile();
        }

        if (! empty($pluginFolderPath) && !file_exists($pluginFolderPath[0] . '/' . $plugin->getPluginFile())) {
            cleanDir($pluginFolderPath[0]);
            rmdir($pluginFolderPath[0]);
        }

        $dirTo =  WP_PLUGIN_DIR . '/' . $plugin->value . '/' . $plugin->getPluginFile();

        if(! empty($dirTo) && file_exists($dirTo)) {
            return $dirTo;
        }

        if(! empty($dirTo) && !file_exists($dirTo) && file_exists(dirname($dirTo))) {
            cleanDir(dirname($dirTo));
            rmdir(dirname($dirTo));
        }

        return $dirTo;
    }
}