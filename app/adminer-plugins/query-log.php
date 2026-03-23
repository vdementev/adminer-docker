<?php

/** Log all SQL queries except dump recovery (file imports)
 * Logs to file specified by ADMINER_QUERY_LOG env var, defaults to /tmp/adminer-queries.log
 * @link https://www.adminer.org/plugins/#use
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerQueryLog extends Adminer\Plugin
{
	private $logFile;

	function __construct($logFile = null)
	{
		$this->logFile = $logFile ?? getenv('ADMINER_QUERY_LOG') ?: '/tmp/adminer-queries.log';
	}

	private function log($query, $time = '', $failed = false)
	{
		$entry = date('Y-m-d H:i:s')
			. ' [' . Adminer\SERVER . ']'
			. ' [' . ($_GET['db'] ?? '-') . ']'
			. ' [' . ($_GET['username'] ?? '-') . ']'
			. ($failed ? ' ERROR' : '')
			. ($time ? " ($time)" : '')
			. ' ' . preg_replace('~\s+~', ' ', trim($query))
			. "\n";

		@file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
	}

	function sqlCommandQuery($query)
	{
		if (!isset($_GET['import'])) {
			$this->log($query);
		}
		return null;
	}

	function messageQuery($query, $time, $failed = false)
	{
		$this->log($query, $time, $failed);
		return null;
	}

	protected $translations = array(
		'cs' => array('' => 'Logování SQL dotazů kromě importu souborů'),
		'de' => array('' => 'SQL-Abfragen protokollieren (außer Dateiimport)'),
		'pl' => array('' => 'Logowanie zapytań SQL z wyjątkiem importu plików'),
		'ru' => array('' => 'Логирование SQL-запросов, кроме восстановления дампов'),
		'ja' => array('' => 'ファイルインポートを除くSQLクエリのログ'),
	);
}
