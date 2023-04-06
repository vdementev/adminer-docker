<?php

declare(strict_types=1);

function adminer_object()
{
    include_once "./plugins/plugin.php";

    $pluginFiles = [
        'dump-date',
        'dump-zip',
    ];

    $plugins = [];
    foreach ($pluginFiles as $pluginFile) {
        include_once "./plugins/{$pluginFile}.php";
        $className = 'Adminer' . str_replace('-', '', ucwords($pluginFile, '-'));
        $plugins[] = new $className();
    }

    return new AdminerPlugin($plugins);
}

include "./adminer.php";
