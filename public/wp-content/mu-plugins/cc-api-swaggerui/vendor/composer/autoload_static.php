<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2691c4a3fdf37e5451dd750aedf7010b
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CommerceCore\\CcApiSwaggerui\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CommerceCore\\CcApiSwaggerui\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2691c4a3fdf37e5451dd750aedf7010b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2691c4a3fdf37e5451dd750aedf7010b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2691c4a3fdf37e5451dd750aedf7010b::$classMap;

        }, null, ClassLoader::class);
    }
}
