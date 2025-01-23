<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitccb37ceea0ff163cb9af1a0b8623878a
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CommerceCore\\EssentialPluginsInit\\' => 34,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CommerceCore\\EssentialPluginsInit\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitccb37ceea0ff163cb9af1a0b8623878a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitccb37ceea0ff163cb9af1a0b8623878a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitccb37ceea0ff163cb9af1a0b8623878a::$classMap;

        }, null, ClassLoader::class);
    }
}