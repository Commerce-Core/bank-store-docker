<?php

namespace CommerceCore\EssentialPluginsInit\Enum;

enum CommerceCoreWpPlugins: string
{
    case WP_GUTENBERG_BLOCKS_PLUGIN = 'wp-gutenberg-blocks-plugin';
    case WP_CHECKOUT_PLUGIN = 'wp-checkout-plugin';
    case POST_TRANSLATOR_PLUGIN = 'post-translator-plugin';
    case WP_ECOM_EXPERIMENTS = 'wp-ecom-experiments';
    case WP_COMMERCECORE_MONITORING = 'wp-commercecore-monitoring';
    case WP_EMAILS_PLUGIN = 'wp-emails-plugin';
    case CC_POSTS_RESAVE = 'cc-posts-resave';
    case CC_CURRENCY_FORMATTER = 'cc-currency-formatter';
    case WP_EMAIL_MARKETING = 'wp-email-marketing';
    case WP_ECOM_CACHE = 'wp-ecom-cache';
    case WP_DOMAIN_REPLACER = 'wp-domain-replacer';
    case CC_CODE_OBFUSCATOR = 'cc-code-obfuscator';
    case CC_IMAGE_OPTIMIZER = 'cc-image-optimizer';
    case SENTRY_PLUGIN = 'sentry-plugin';
    case CUSTOM_CSS_PLUGIN = 'custom-css-plugin';
    case CC_LOGGER = 'cc-logger';
    case CC_LOCAL_GTM = 'cc-local-gtm';
    case CC_MAINTENANCE = 'cc-maintenance';
    case CC_BRANDS_PLUGIN = 'brands-ecom';
    case CC_LEGAL_PAGES_PLUGIN = 'cc-legal-pages';
    case CC_LANDERS_PLUGIN = 'wp-landers';

    public function getName(): string
    {
        return match ($this) {
            self::WP_GUTENBERG_BLOCKS_PLUGIN => 'Core Blocks',
            self::WP_CHECKOUT_PLUGIN => 'Commerce Core Checkout',
            self::POST_TRANSLATOR_PLUGIN => 'Post Translator CC',
            self::WP_ECOM_EXPERIMENTS => 'CC Rotate Plugin',
            self::WP_COMMERCECORE_MONITORING => 'Wp Commerce Monitoring',
            self::WP_EMAILS_PLUGIN => 'CommerceCore Emails Plugin',
            self::CC_POSTS_RESAVE => 'CommerceCore Posts Resave',
            self::CC_CURRENCY_FORMATTER => 'Core Currency Formatter',
            self::WP_EMAIL_MARKETING => 'Email marketing plugin',
            self::WP_ECOM_CACHE => 'WP Ecom Cache plugin',
            self::WP_DOMAIN_REPLACER => 'Domain Replacer Plugin',
            self::CC_CODE_OBFUSCATOR => 'Commerce Core Obfuscation Tool',
            self::CC_IMAGE_OPTIMIZER => 'Commerce Core Images Optimizer',
            self::SENTRY_PLUGIN => 'Sentry plugin',
            self::CUSTOM_CSS_PLUGIN => 'CC Custom CSS Plugin',
            self::CC_LOGGER => 'CC Logger Plugin',
            self::CC_LOCAL_GTM => 'CC Local GTM Plugin',
            self::CC_MAINTENANCE => 'CC Maintenance Plugin',
            self::CC_BRANDS_PLUGIN => 'CC Brands Plugin',
            self::CC_LEGAL_PAGES_PLUGIN => 'CC Legal pages Plugin',
            self::CC_LANDERS_PLUGIN => 'CC Landers Plugin',
        };
    }

    public function getPluginFile(): string
    {
        return match ($this) {
            self::WP_GUTENBERG_BLOCKS_PLUGIN => 'cc-gutenberg-blocks.php',
            self::WP_CHECKOUT_PLUGIN => 'cc-checkout.php',
            self::POST_TRANSLATOR_PLUGIN => 'post-translator.php',
            self::WP_ECOM_EXPERIMENTS => 'cc-rotate.php',
            self::WP_COMMERCECORE_MONITORING => 'wp-commerce-monitoring.php',
            self::WP_EMAILS_PLUGIN => 'emails-plugin.php',
            self::CC_POSTS_RESAVE => 'cc-posts-resave.php',
            self::CC_CURRENCY_FORMATTER => 'cc-currency-formatter.php',
            self::WP_EMAIL_MARKETING => 'wp-email-marketing.php',
            self::WP_ECOM_CACHE => 'wp-ecom-cache.php',
            self::WP_DOMAIN_REPLACER => 'domain-replacer.php',
            self::CC_CODE_OBFUSCATOR => 'cc-code-obfuscator.php',
            self::CC_IMAGE_OPTIMIZER => 'cc-image-optimizer.php',
            self::SENTRY_PLUGIN => 'sentry-plugin.php',
            self::CUSTOM_CSS_PLUGIN => 'custom-css-plugin.php',
            self::CC_LOGGER => 'cc-logger.php',
            self::CC_LOCAL_GTM => 'cc-local-gtm.php',
            self::CC_MAINTENANCE => 'cc-maintenance.php',
            self::CC_BRANDS_PLUGIN => 'cc-brands.php',
            self::CC_LEGAL_PAGES_PLUGIN => 'cc-legal-pages.php',
            self::CC_LANDERS_PLUGIN => 'cc-landers.php',
        };
    }

    public function getRepositoryUser(): string
    {
        return match ($this) {
            self::POST_TRANSLATOR_PLUGIN => 'WhaMedia-Acc',
            default => 'Commerce-Core',
        };
    }

    public function shouldBeInstalled(): bool
    {
        return match ($this) {
            self::CUSTOM_CSS_PLUGIN,
            self::CC_MAINTENANCE,
            self::CC_LOCAL_GTM,
            self::CC_LANDERS_PLUGIN,
            self::CC_BRANDS_PLUGIN => false,
            default => true,
        };
    }

    public function shouldBeActivated(): bool
    {
        return match ($this) {
            self::CC_CODE_OBFUSCATOR,
            self::SENTRY_PLUGIN,
            self::CUSTOM_CSS_PLUGIN,
            self::CC_MAINTENANCE,
            self::CC_LOCAL_GTM,
            self::CC_LANDERS_PLUGIN,
            self::CC_BRANDS_PLUGIN => false,
            default => true,
        };
    }

    public static function fromValue(string $value): ?self
    {
        return array_values(
            array_filter(self::cases(), fn (self $plugin) => $plugin->value === $value)
        )[0] ?? null;
    }
}
