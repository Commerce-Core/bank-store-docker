<?php

if ($siteOriginalUrl = get_env_with_fallback('WORDPRESS_URL', false)) {
    $siteOriginalUrlScheme = parse_url($siteOriginalUrl, PHP_URL_SCHEME);
    $siteDomain = parse_url($siteOriginalUrl, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? null;

    if (
        $siteDomain
        && $siteOriginalUrlScheme
        && $currentHost
        && $currentHost !== $siteDomain
    ) {
        $siteAliasUrl = sprintf('%s://%s', $siteOriginalUrlScheme, $currentHost);

        define('WP_HOME',  $siteAliasUrl);
        define('WP_SITEURL', $siteAliasUrl);
        define('SITE_ALIAS_VISIT', 1);
    }
}
