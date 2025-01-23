<?php

if ( ! defined( 'APP_PROJECT_ROOT' ) ) {
    define( 'APP_PROJECT_ROOT', dirname(__FILE__, 2) . '/' );
}

if ( file_exists( APP_PROJECT_ROOT . '/adminer/adminer-main.php' ) ) {
    function adminer_object() {
        // required to run any plugin
        include_once APP_PROJECT_ROOT . '/adminer/plugin.php';

        // autoloader
        foreach (glob(APP_PROJECT_ROOT . '/adminer/plugins/*.php') as $filename) {
            include_once $filename;
        }

        $plugins = array(
            new AdminerLoginServers([
                'default' => [
                    'server' => 'localhost',
                    'driver' => 'server',
                ],
                'local' => [
                    'server' => 'mysql',
                    'driver' => 'server',
                ],
            ]),
        );

        return new AdminerPlugin($plugins);
    }

    include APP_PROJECT_ROOT . '/adminer/adminer-main.php';
}