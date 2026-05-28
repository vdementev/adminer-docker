<?php

/** Inject a "Fast Restore" link into Adminer's navigation sidebar.
 *
 * Pairs with /app/restore.php (the standalone upload-and-restore page).
 * The link is pre-filled with the current host/port/user/db pulled from
 * Adminer state so the restore form opens with everything but the password
 * already populated.
 *
 * Hooks `navigation($missing)`. Adminer's `Plugins::__call` runs every
 * registered plugin's `navigation()` in order — each one echoes HTML and
 * returns null, then Adminer's default navigation renders below. So we can
 * append our link without replacing the sidebar.
 *
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
class AdminerFastRestoreLink extends Adminer\Plugin
{
    function navigation($missing)
    {
        // Hide the link on the login screen — no useful session data yet
        // and the restore page would just bounce back here.
        if ($missing === 'auth') {
            return null;
        }

        $params = $this->prefillParams();
        $url = 'restore.php' . ($params ? '?' . http_build_query($params) : '');

        // The badge is rendered above the database selector. CSS keeps it
        // compact and uses Adminer's existing color palette so it works in
        // both light and dark themes.
        ?>
<p style="margin:.5em 0 1em">
    <a href="<?php echo Adminer\h($url); ?>"
       title="Upload a .sql / .sql.gz / .sql.zst dump and pipe it through native mysql client"
       style="display:inline-flex;align-items:center;gap:.3em;padding:.35em .7em;
              background:#10b981;color:#fff;border-radius:4px;
              text-decoration:none;font-weight:500;font-size:.9em;">
        ⚡ Fast Restore
    </a>
</p>
        <?php
        return null;
    }

    /** Build the query-string parameters from Adminer's current connection
     *  state, so the restore form opens pre-filled.
     *
     *  @return array<string,string>
     */
    private function prefillParams(): array
    {
        $params = array();

        // Server (may be "host" or "host:port")
        if (defined('Adminer\\SERVER') && \Adminer\SERVER !== '') {
            $server = \Adminer\SERVER;
            if (strpos($server, ':') !== false) {
                list($host, $port) = explode(':', $server, 2);
                $params['host'] = $host;
                $params['port'] = $port;
            } else {
                $params['host'] = $server;
            }
        }

        if (defined('Adminer\\DB') && \Adminer\DB !== '') {
            $params['db'] = \Adminer\DB;
        }

        // Username: walk Adminer's session structure
        //   $_SESSION['pwds'][$driver][$server][$user] = password
        // and grab the first non-empty $user key.
        if (!empty($_SESSION['pwds']) && is_array($_SESSION['pwds'])) {
            foreach ($_SESSION['pwds'] as $serverMap) {
                if (!is_array($serverMap)) continue;
                foreach ($serverMap as $userMap) {
                    if (!is_array($userMap)) continue;
                    foreach (array_keys($userMap) as $user) {
                        if ($user !== '' && $user !== null) {
                            $params['user'] = (string)$user;
                            return $params;
                        }
                    }
                }
            }
        }

        return $params;
    }

    protected $translations = array(
        'cs' => array('⚡ Fast Restore' => '⚡ Rychlá obnova'),
        'de' => array('⚡ Fast Restore' => '⚡ Schnelle Wiederherstellung'),
        'fr' => array('⚡ Fast Restore' => '⚡ Restauration rapide'),
        'es' => array('⚡ Fast Restore' => '⚡ Restauración rápida'),
        'ru' => array('⚡ Fast Restore' => '⚡ Быстрое восстановление'),
        'ja' => array('⚡ Fast Restore' => '⚡ 高速復元'),
    );
}
