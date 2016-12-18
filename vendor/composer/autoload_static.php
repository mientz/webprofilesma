<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3395cd736a09978f5e65a8517b556d01
{
    public static $files = array (
        '253c157292f75eb38082b5acb06f3f01' => __DIR__ . '/..' . '/nikic/fast-route/src/functions.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' =>
        array (
            'Slim\\Views\\' => 11,
            'Slim\\Flash\\' => 11,
            'Slim\\' => 5,
        ),
        'P' =>
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'I' =>
        array (
            'Intervention\\Image\\' => 19,
            'Interop\\Container\\' => 18,
        ),
        'G' =>
        array (
            'GuzzleHttp\\Psr7\\' => 16,
        ),
        'F' =>
        array (
            'FastRoute\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Slim\\Views\\' =>
        array (
            0 => __DIR__ . '/..' . '/slim/twig-view/src',
        ),
        'Slim\\Flash\\' =>
        array (
            0 => __DIR__ . '/..' . '/slim/flash/src',
        ),
        'Slim\\' =>
        array (
            0 => __DIR__ . '/..' . '/slim/slim/Slim',
        ),
        'Psr\\Http\\Message\\' =>
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Intervention\\Image\\' =>
        array (
            0 => __DIR__ . '/..' . '/intervention/image/src/Intervention/Image',
        ),
        'Interop\\Container\\' =>
        array (
            0 => __DIR__ . '/..' . '/container-interop/container-interop/src/Interop/Container',
        ),
        'GuzzleHttp\\Psr7\\' =>
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'FastRoute\\' =>
        array (
            0 => __DIR__ . '/..' . '/nikic/fast-route/src',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/bryanjhv/slim-session/src',
    );

    public static $prefixesPsr0 = array (
        'T' =>
        array (
            'Twig_Extensions_' =>
            array (
                0 => __DIR__ . '/..' . '/twig/extensions/lib',
            ),
            'Twig_' =>
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
        'P' =>
        array (
            'Pimple' =>
            array (
                0 => __DIR__ . '/..' . '/pimple/pimple/src',
            ),
        ),
    );

    public static $classMap = array (
        'Qazd\\TextDiff' => __DIR__ . '/..' . '/qazd/text-diff/src/TextDiff.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3395cd736a09978f5e65a8517b556d01::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3395cd736a09978f5e65a8517b556d01::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInit3395cd736a09978f5e65a8517b556d01::$fallbackDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit3395cd736a09978f5e65a8517b556d01::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit3395cd736a09978f5e65a8517b556d01::$classMap;

        }, null, ClassLoader::class);
    }
}
