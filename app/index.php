<?php

declare(strict_types=1);


define('ADMINER_ENV_SERVER', 'ADMINER_DEFAULT_SERVER');
define('ADMINER_ENV_USERNAME', 'ADMINER_DEFAULT_USERNAME');
define('ADMINER_ENV_PASSWORD', 'ADMINER_DEFAULT_PASSWORD');
// Optional: Define an environment variable for the default database.
define('ADMINER_ENV_DB', 'ADMINER_DEFAULT_DB');

// Define the path to the plugins directory (relative to this index.php file).
define('ADMINER_PLUGINS_DIR', './plugins');

// Define the path to the main Adminer core file.
define('ADMINER_CORE_FILE', './adminer.php');
// --- End Configuration ---


// Check for critical files
if (!is_dir(ADMINER_PLUGINS_DIR)) {
    die("Error: Adminer plugins directory not found at '" . ADMINER_PLUGINS_DIR . "'.");
}
if (!file_exists(ADMINER_PLUGINS_DIR . '/plugin.php')) {
    die("Error: Base plugin file not found at '" . ADMINER_PLUGINS_DIR . '/plugin.php' . "'. Please download it from Adminer website.");
}
if (!file_exists(ADMINER_CORE_FILE)) {
    die("Error: Main Adminer core file not found at '" . ADMINER_CORE_FILE . "'. Please download it from Adminer website.");
}

// Include the base plugin class required by Adminer plugins
include_once ADMINER_PLUGINS_DIR . "/plugin.php";

/**
 * Custom Adminer class to handle autologin via environment variables
 * and potentially other customizations.
 */
class AdminerCustomization extends AdminerPlugin
{
    /**
     * Returns database credentials retrieved from environment variables.
     * If environment variables are not set, returns null to show the login form.
     *
     * @return array|null An array containing [server, username, password, database|null] or null.
     */
    function credentials(): ?array
    {
        $server   = getenv(ADMINER_ENV_SERVER);
        $username = getenv(ADMINER_ENV_USERNAME);
        $password = getenv(ADMINER_ENV_PASSWORD); // Check against false explicitly
        $db       = getenv(ADMINER_ENV_DB) ?: null; // Use null if DB env var is not set or empty

        // Check if essential variables are set and not empty/false
        if ($server && $username && $password !== false) {
            // Return credentials in the format Adminer expects
            // [server, username, password, optional_database]
            return [$server, $username, $password, $db];
        }

        // Fallback to Adminer's default behavior (show login form)
        return null;
    }
}


/**
 * Adminer object factory function.
 * This function is called by Adminer to get plugins and customizations.
 * It automatically loads plugins from the defined directory and applies customizations.
 *
 * @return AdminerCustomization Instance of our customization class with loaded plugins.
 */
function adminer_object(): AdminerCustomization
{
    $plugins = [];
    $pluginDir = rtrim(ADMINER_PLUGINS_DIR, '/\\'); // Ensure no trailing slash (cross-platform)

    $pluginFiles = glob("{$pluginDir}/*.php");

    if ($pluginFiles === false) {
        error_log("Adminer: Failed to scan plugin directory '{$pluginDir}'. No plugins loaded.");
        $pluginFiles = [];
    }

    foreach ($pluginFiles as $pluginFile) {
        $baseName = basename($pluginFile, '.php');

        if ($baseName === 'plugin') {
            continue;
        }

        $className = 'Adminer' . str_replace('-', '', ucwords($baseName, '-'));

        include_once $pluginFile;

        if (class_exists($className)) {
            try {
                $plugins[] = new $className();
            } catch (Throwable $e) {
                error_log("Adminer: Failed to instantiate plugin class '{$className}' from file '{$pluginFile}': " . $e->getMessage());
            }
        } else {
            error_log("Adminer: Plugin file '{$pluginFile}' included, but expected class '{$className}' not found.");
        }
    }

    return new AdminerCustomization($plugins);
}

include ADMINER_CORE_FILE;
