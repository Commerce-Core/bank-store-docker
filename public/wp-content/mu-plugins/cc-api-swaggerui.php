<?php

/*
* Plugin Name: CommerceCore REST API Documentation Plugin
* Author: Commerce Core
* Author URI: https://www.commercecore.com
* Description: CommerceCore REST API Documentation Plugin (works on local environment only)
* Text Domain: cc-api-swaggerui
* Version: 1.0.0
*/

if (getenv('CC_DEV')) { // load api docs only on local environment
    require WPMU_PLUGIN_DIR.'/cc-api-swaggerui/cc-api-swaggerui.php';
}
