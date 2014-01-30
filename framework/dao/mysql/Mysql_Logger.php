<?php
namespace SAF\Framework;

use mysqli;

/**
 * A logger for mysql queries
 */
class Mysql_Logger implements Plugin
{

	//------------------------------------------------------------------------------------- $continue
	/**
	 * If true, log will be displayed each time a query is executed.
	 * If false, will be display at script's end.
	 *
	 * @var boolean
	 */
	public $continue = false;

	//---------------------------------------------------------------------------------- $display_log
	/**
	 * Displays queries log. If false, only errors will be displayed
	 *
	 * @var boolean
	 */
	public $display_log = true;

	//----------------------------------------------------------------------------------- $errors_log
	/**
	 * The errors log
	 *
	 * Errors are full text looking like "errno: Error message [SQL Query]".
	 *
	 * @var string[]
	 */
	public $errors_log = array();

	//---------------------------------------------------------------------- $main_controller_counter
	/**
	 * Counts Main_Controller->run() recursion, to avoid logging after each sub-call
	 *
	 * @var integer
	 */
	public $main_controller_counter = 0;

	//---------------------------------------------------------------------------------- $queries_log
	/**
	 * The queries log
	 *
	 * All executed queries are logged here.
	 *
	 * @var string[]
	 */
	public $queries_log = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Mysql_Logger is a singleton, you only can get it's instance with getInstance()
	 */
	private function __construct() {}

	//------------------------------------------------------------------------ afterMainControllerRun
	/**
	 * After main controller run, display query log
	 */
	public function afterMainControllerRun()
	{
		$this->main_controller_counter--;
		if ($this->display_log && !$this->main_controller_counter) {
			$this->dumpLog();
		}
	}

	//--------------------------------------------------------------------------------------- dumpLog
	/**
	 * Display query log
	 */
	public function dumpLog()
	{
		echo "<h3>Mysql log</h3>";
		echo "<div class=\"Mysql logger query\">\n";
		echo "<pre>" . print_r($this->queries_log, true) . "</pre>\n";
		echo " </div>\n";
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * Called each time before a mysql_query() call is done : log the query
	 *
	 * @param $query string
	 */
	public function onQuery($query)
	{
		if ($this->continue && $this->display_log) {
			echo "<div class=\"Mysql logger query\">" . $query . "</div>\n";
		}
		$this->queries_log[] = $query;
	}

	//--------------------------------------------------------------------------------------- onError
	/**
	 * Called each time after a mysql_query() call is done : log the error (if some)
	 *
	 * @param $query  string
	 * @param $object mysqli
	 */
	public function onError($query, mysqli $object)
	{
		$mysqli = $object;
		if ($mysqli->errno) {
			$error = $mysqli->errno . ": " . $mysqli->error . "[" . $query . "]";
			echo "<div class=\"Mysql logger error\">" . $error . "</div>\n";
			$this->errors_log[] = $error;
		}
	}

	//--------------------------------------------------------------------------- onMainControllerRun
	public function onMainControllerRun()
	{
		$this->main_controller_counter++;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$parameters = $register->getConfiguration();
		if (isset($parameters)) {
			if (isset($parameters["continue"])) {
				$this->continue = $parameters["continue"];
			}
			if (isset($parameters["display_log"])) {
				$this->display_log = $parameters["display_log"];
			}
			foreach ($parameters as $key => $value) if (is_numeric($key)) {
				if (strpos("/" . $_SERVER["REQUEST_URI"] . "/", "/" . $value . "/")) {
					return;
				}
			}
		}
		$dealer->beforeMethodCall(
			array('SAF\Framework\Contextual_Mysqli', "query"), array($this, "onQuery")
		);
		$dealer->afterMethodCall(
			array('SAF\Framework\Contextual_Mysqli', "query"), array($this, "onError")
		);
		if (!$this->continue) {
			$dealer->beforeMethodCall(
				array('SAF\Framework\Main_Controller', "runController"),
				array($this, "onMainControllerRun")
			);
			$dealer->afterMethodCall(
				array('SAF\Framework\Main_Controller', "runController"),
				array($this, "afterMainControllerRun")
			);
		}
	}

}
