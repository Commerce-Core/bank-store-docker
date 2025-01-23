<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use CommerceCore\EssentialPluginsInit\CommerceCoreEssentials;

add_action( 'init', function () {
    CommerceCoreEssentials::factory();
} );
