<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

use CommerceCore\CcApiSwaggerui\CcApiSwaggerUI;
use CommerceCore\CcApiSwaggerui\SwaggerTemplate;

const CC_SWAGGERUI_PLUGIN_FILE = __FILE__;

new CcApiSwaggerUI();
new SwaggerTemplate();
