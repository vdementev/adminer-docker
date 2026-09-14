<?php
/**
 * Adminer entrypoint — loads drivers and plugins, then delegates to compiled Adminer.
 *
 * `adminer_object()` is declared in the GLOBAL namespace on purpose: Adminer
 * bootstraps with `function_exists('adminer_object')`, and function_exists()
 * doesn't resolve against the calling namespace. Declared under `namespace
 * Adminer` the hook is silently skipped and Adminer falls back to scanning
 * `adminer-plugins/` by itself — which loads the plugins but never the
 * drivers, and ignores the order below.
 *
 * The plugins under `adminer-plugins/` extend `Adminer\Plugin` but live in
 * the global namespace. They are included from inside `adminer_object()` so
 * that the `Adminer\Plugin` base class (defined in `adminer.php`) exists at
 * the moment each plugin class is declared.
 *
 * The files under `adminer-drivers/` are upstream driver plugins (ClickHouse,
 * Elasticsearch, MongoDB, Redis, SimpleDB) for the databases that aren't
 * compiled into `adminer.php`. They are not classes — each one calls
 * `Adminer\add_driver()` at include time, which is why they also have to be
 * included from here: `adminer_object()` runs before Adminer resolves the
 * `DRIVER` constant, so a driver registered now shows up in the login form.
 *
 * Adminer calls `adminer_object()` during its bootstrap; we return an
 * `Adminer\Plugins` instance enabling our customizations.
 *
 * Angie / nginx FastCGI maps `SCRIPT_FILENAME=/app/index.php`, so this file
 * remains the entry no matter what URL path is requested.
 */

declare(strict_types=1);

/**
 * @return \Adminer\Plugins
 */
function adminer_object()
{
    // At this point `adminer.php` has already run and defined `Adminer\Plugin`,
    // so it's safe to declare classes that extend it.
    foreach (glob(__DIR__ . '/adminer-plugins/*.php') as $pluginFile) {
        require_once $pluginFile;
    }

    // Drivers, tracked the way Adminer's own auto-discovery does it: diff
    // `SqlDriver::$drivers` around each include so the databases page can
    // list which file contributed which driver.
    $loadedDrivers = array();
    $driverFiles = array();
    foreach (glob(__DIR__ . '/adminer-drivers/*.php') as $driverFile) {
        $before = \Adminer\SqlDriver::$drivers;
        require_once $driverFile;
        $added = array_diff_key(\Adminer\SqlDriver::$drivers, $before);
        $loadedDrivers += $added;
        foreach (array_keys($added) as $driver) {
            $driverFiles[$driver] = $driverFile;
        }
    }

    // Order matters — it's the order the dump options appear in. `::class` is
    // a compile-time constant, so listing a class here that wasn't loaded is
    // harmless: class_exists() guards instantiation.
    $candidates = [
        \AdminerDumpMysqldumpZstd::class, // fastest MySQL path: mysqldump | zstd -T0
        \AdminerDumpPgdumpZstd::class,    // fastest PostgreSQL path: pg_dump | zstd -T0
        \AdminerDumpPigz::class,          // parallel gzip via pigz CLI (all cores)
        \AdminerDumpPzst::class,          // parallel zstd via zstd CLI -T0 (all cores)
        \AdminerDumpZstd::class,          // single-core in-process Zstandard
        \AdminerFastRestoreLink::class,   // sidebar link to /adminer/restore.php
        \AdminerDumpDate::class,          // timestamp in dump filename
        \AdminerVersionNoverify::class,   // disable "new version available" check
        \AdminerDarkSwitcher::class,      // dark mode toggle
        \AdminerQueryLog::class,          // log executed queries
    ];

    $plugins = [];
    foreach ($candidates as $class) {
        if (class_exists($class, false)) {
            $plugins[] = new $class();
        }
    }

    $instance = new \Adminer\Plugins($plugins);
    // Constructed after the includes above, so its own driver diff came up
    // empty — fill in what we loaded. Both properties matter: `drivers` lists
    // them on the databases page, `driverFiles` lets Adminer checksum each
    // file against the table baked into adminer.php, which is how a driver
    // left behind by an Adminer upgrade gets flagged.
    $instance->drivers = $loadedDrivers;
    $instance->driverFiles = $driverFiles;
    return $instance;
}

require __DIR__ . '/adminer.php';
