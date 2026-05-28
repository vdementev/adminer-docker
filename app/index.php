<?php
/**
 * Adminer entrypoint — loads plugins, then delegates to compiled Adminer.
 *
 * The plugins under `adminer-plugins/` extend `Adminer\Plugin` but live in
 * the global namespace. They are included from inside `adminer_object()` so
 * that the `Adminer\Plugin` base class (defined in `adminer.php`) exists at
 * the moment each plugin class is declared.
 *
 * Adminer calls `adminer_object()` during its bootstrap; we return an
 * `Adminer\Plugins` instance enabling our customizations.
 *
 * Angie / nginx FastCGI maps `SCRIPT_FILENAME=/app/index.php`, so this file
 * remains the entry no matter what URL path is requested.
 */

declare(strict_types=1);

namespace Adminer;

/**
 * @return Plugins
 */
function adminer_object()
{
    // At this point `adminer.php` has already run and defined `Adminer\Plugin`,
    // so it's safe to declare classes that extend it.
    foreach (glob(__DIR__ . '/adminer-plugins/*.php') as $pluginFile) {
        require_once $pluginFile;
    }

    // Each plugin class lives in the GLOBAL namespace (no `namespace` decl in
    // its file), so reference with leading backslash. `::class` is a
    // compile-time constant, so listing a class here that wasn't loaded is
    // harmless — class_exists() guards instantiation.
    $candidates = [
        \AdminerDumpMysqldumpZstd::class, // fastest: shell out to mysqldump | zstd -T0
        \AdminerDumpPigz::class,          // parallel gzip via pigz CLI (all cores)
        \AdminerDumpPzst::class,          // parallel zstd via zstd CLI -T0 (all cores)
        \AdminerDumpZstd::class,          // single-core in-process Zstandard
        \AdminerFastRestoreLink::class,   // sidebar link to /adminer/restore.php
        \AdminerDumpDate::class,          // timestamp in dump filename
        \AdminerVersionNoverify::class,   // disable "new version available" check
        \AdminerDarkSwitcher::class,      // dark mode toggle
        \AdminerQueryLog::class,          // log executed queries
        // \AdminerMongo::class,          // enable if you actually use MongoDB
    ];

    $plugins = [];
    foreach ($candidates as $class) {
        if (class_exists($class, false)) {
            $plugins[] = new $class();
        }
    }
    return new Plugins($plugins);
}

require __DIR__ . '/adminer.php';
