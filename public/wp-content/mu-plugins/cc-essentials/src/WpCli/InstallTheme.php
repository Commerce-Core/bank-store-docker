<?php

namespace CommerceCore\EssentialPluginsInit\WpCli;

use CommerceCore\EssentialPluginsInit\FileSystemOperationsHelper;
use CommerceCore\EssentialPluginsInit\GitHubClient;

class InstallTheme
{
    public const CC_THEME = [
        'user' => 'Commerce-Core',
        'repo' => 'commercecore-ecom',
        'directory' => 'commercecore-ecom',
        'name' => 'Commerce Core E-com'
    ];

    protected $environment;
    private GitHubClient $gitHubClient;
    private FileSystemOperationsHelper $fileSystemOperationsHelper;

    public function __construct()
    {
        if (!defined('WP_CLI')) {
            exit;
        }

        $this->gitHubClient = new GitHubClient();
        $this->fileSystemOperationsHelper = new FileSystemOperationsHelper();
        $this->environment = wp_get_environment_type();
    }

    public function __invoke( $args ): void
    {
        if (getenv('CC_DEV')) {
            \WP_CLI::error( 'This command is not available in local development mode.' );
            exit;
        }

        try {
            $this->downloadTheme();
        } catch (\Exception $exception) {
            \WP_CLI::error( $exception->getMessage() );
        }
    }

    /**
     * @throws \Exception
     */
    public function downloadTheme(): void
    {
        $particularThemeVersion = function_exists('get_env_with_fallback')
            ? get_env_with_fallback('CC_THEME_VERSION')
            : null;

        \WP_CLI::log(sprintf('Downloading theme %s...', $particularThemeVersion ?: ''));

        if (
            $zip_content = $particularThemeVersion
                ? $this->gitHubClient->getThemeVersionZipRequestBody(self::CC_THEME['user'], self::CC_THEME['repo'], $particularThemeVersion)
                : $this->gitHubClient->getThemeLatestZipRequestBody(self::CC_THEME['user'], self::CC_THEME['repo'])
        ) {
            $tempFile = \wp_tempnam();
            file_put_contents($tempFile, $zip_content);
            WP_Filesystem();

            $themesDirPath = WP_CONTENT_DIR . '/themes/';
            $extractedThemeFolderPattern = sprintf('%s/%s-%s-*', $themesDirPath, self::CC_THEME['user'], self::CC_THEME['repo']);
            $desiredThemeFolder = $themesDirPath . self::CC_THEME['directory'];

            $this->fileSystemOperationsHelper->removeSymlink($desiredThemeFolder);
            $this->fileSystemOperationsHelper->removeDirectory($desiredThemeFolder);

            if (
                !is_wp_error(unzip_file($tempFile, $themesDirPath))
                && $extractedThemeFolder  = (glob($extractedThemeFolderPattern, GLOB_ONLYDIR) ?: [])[0] ?? null
            ) {
                rename($extractedThemeFolder, $desiredThemeFolder);

                \WP_CLI::success( 'Done' );
            }
        }
    }
}
