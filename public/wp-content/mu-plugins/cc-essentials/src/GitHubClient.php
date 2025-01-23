<?php

namespace CommerceCore\EssentialPluginsInit;

class GitHubClient
{
    private const DEFAULT_TIMEOUT = 60;

    private const API_URL_BASE = 'https://api.github.com/repos';

    private ?string $gitHubToken;

    public function __construct()
    {
        if (!defined('CC_GITHUB_TOKEN')) {
            throw new \Exception('CC_GITHUB_TOKEN is not defined.');
        }

        $this->gitHubToken = CC_GITHUB_TOKEN;
    }

    /**
     * @throws \Exception
     */
    public function getZipBallRepositoryUrl(string $user, string $repository, ?string $version = null): string
    {
        if ($version) {
            return sprintf('%s/%s/%s/zipball/%s', self::API_URL_BASE, $user, $repository, $version);
        }

        if ($latestReleaseZipBallRepositoryUrl = $this->getLatestReleaseZipBallRepositoryUrl($user, $repository)) {
            return $latestReleaseZipBallRepositoryUrl;
        }

        throw new \Exception('Could not find latest release zip ball repository.');
    }

    public function getLatestReleaseZipBallRepositoryUrl(string $user, string $repository): ?string
    {
        try {
            $latestRelease = $this->getRequestBody(
                $this->getLatestReleaseUrl($user, $repository)
            );

            $zipBallUrl = json_decode($latestRelease, true, 512, JSON_THROW_ON_ERROR)['zipball_url'] ?? '';

            return filter_var($zipBallUrl, FILTER_VALIDATE_URL) ? $zipBallUrl : null;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return null;
    }

    public function getReleasesRepositoryUrl(string $user, string $repository, int $perPage = 1): string
    {
        return sprintf('%s/%s/%s/releases?per_page=%d', self::API_URL_BASE, $user, $repository, $perPage);
    }

    public function getLatestReleaseUrl(string $user, string $repository): string
    {
        return sprintf('%s/%s/%s/releases/latest', self::API_URL_BASE, $user, $repository);
    }

    public function getRequestResponse(string $url): ?array
    {
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 120,
                'headers' => [
                    'Authorization' => 'token ' . $this->gitHubToken,
                ]
            ]
        );

        if (!is_wp_error($response)) {
            return $response;
        }

        return null;
    }

    public function getRequestBody(string $url): ?string
    {
        $response = $this->getRequestResponse($url);

        return $response ? wp_remote_retrieve_body($response) : null;
    }


    /**
     * @throws \Exception
     */
    public function getPluginZipRequestBody(string $user, string $repository, ?string $version = null): ?string
    {
        return $this->getRequestBody(
            $this->getZipBallRepositoryUrl($user, $repository, $version)
        );
    }

    public function getThemeReleases(string $user, string $repository): ?array
    {
        if (
            $releasesRequestResponse = $this->getRequestResponse(
                $this->getReleasesRepositoryUrl($user, $repository)
            )
        ) {
            return json_decode(
                wp_remote_retrieve_body($releasesRequestResponse),
                true
            );
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    public function getThemeLatestZipRequestBody(string $user, string $repository): ?string
    {
        $zipBallRequestUrl = $this->getZipBallRepositoryUrl($user, $repository);

        if (!$zipBallRequestUrl) {
            return null;
        }

        return $this->getRequestBody(
            $zipBallRequestUrl
        );
    }

    /**
     * @throws \Exception
     */
    public function getThemeVersionZipRequestBody(string $user, string $repository, string $version): ?string
    {
        $zipBallRequestUrl = $this->getZipBallRepositoryUrl($user, $repository, urlencode($version));

        return $this->getRequestBody(
            $zipBallRequestUrl
        );
    }
}