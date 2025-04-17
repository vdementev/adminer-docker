<?php

/** Allow switching light and dark mode
 * @link https://www.adminer.org/plugins/#use
 * @author Jakub Vrana, https://www.vrana.cz/ updated by Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDarkSwitcher extends Adminer\Plugin
{

	function head($dark = null)
	{
?>
		<script <?php echo Adminer\nonce(); ?>>
			// 1. Default to light mode
			let adminerDark = false;

			// 2. Read saved preference, override default only if cookie exists
			const savedMatch = document.cookie.match(/adminer_dark=(\d)/);
			adminerDark = savedMatch ? Boolean(Number(savedMatch[1])) : false;

			// 3. Set mode: enable/disable dark.css, update meta, write cookie
			function adminerDarkSet() {
				document.querySelectorAll('link[href*="dark.css"]')
					.forEach(link => link.media = adminerDark ? '' : 'never');

				document.querySelector('meta[name="color-scheme"]').content = adminerDark ? 'dark' : 'light';

				// persist choice for 30 days
				document.cookie = `adminer_dark=${adminerDark ? 1 : 0};max-age=${30*24*60*60};path=/`;
			}

			// 4. Toggle handler
			function adminerDarkSwitch() {
				adminerDark = !adminerDark;
				adminerDarkSet();
			}

			// 5. Apply on load
			adminerDarkSet();
		</script>
<?php
	}

	function navigation($missing)
	{
		// a little sun icon in the corner to toggle
		echo "<big style='position:fixed;bottom:.5em;right:.5em;cursor:pointer;'>☀</big>"
			. Adminer\script("
                // ensure the setter is in place before toggling
                if (typeof adminerDark !== 'undefined') adminerDarkSet();
                document.querySelector('big').onclick = adminerDarkSwitch;
             ")
			. "\n";
	}

	function screenshot()
	{
		return "https://www.adminer.org/static/plugins/dark-switcher.gif";
	}

	protected $translations = [
		'cs' => ['' => 'Dovoluje přepínání světlého a tmavého vzhledu'],
		'de' => ['' => 'Umschalten zwischen hellem und dunklem Design erlauben'],
		'ja' => ['' => 'ダークモードへの切替え'],
		'pl' => ['' => 'Zezwalaj na przełączanie trybu jasnego i ciemnego'],
	];
}
