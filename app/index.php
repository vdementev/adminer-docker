<?php

declare(strict_types=1);

function adminer_object()
{
    include_once "./plugins/plugin.php";

    include_once "./plugins/dump-date.php";
    include_once "./plugins/dump-zip.php";

    $plugins = array(
        // new AdminerDumpBz2(),
        new AdminerDumpDate(),
        // new AdminerDumpJson(),
        new AdminerDumpZip(),
    );

    return new AdminerPlugin($plugins);
}


// include original Adminer or Adminer Editor
include "./adminer.php";
