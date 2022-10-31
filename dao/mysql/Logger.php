<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Contextual_Mysqli;

/**
 * A logger for mysql queries
 */
class Logger implements Configurable, Registerable
{

	//----------------------------------------------------- Logger configuration array keys constants
	const CONTINUE_LOG = 'continue';
	const DISPLAY_LOG  = 'display_log';
	const EXCLUDE      = 'exclude';

	//------------------------------------------------------------------------------------- $continue
	/**
	 * If true, log will be displayed each time a query is executed.
	 * If false, will be display at script's end.
	 *
	 * @var boolean
	 */
	public bool $continue = false;

	//---------------------------------------------------------------------------------- $display_log
	/**
	 * Displays queries log. If false, only errors will be displayed
	 *
	 * @var boolean
	 */
	public bool $display_log = true;

	//----------------------------------------------------------------------------------- $errors_log
	/**
	 * The errors log
	 *
	 * Errors are full text looking like 'errno: Error message [SQL Query]'.
	 *
	 * @var string[]
	 */
	public array $errors_log = [];

	//---------------------------------------------------------------------- $main_controller_counter
	/**
	 * Counts Main_Controller->run() recursion, to avoid logging after each sub-call
	 *
	 * @var integer
	 */
	public int $main_controller_counter = 0;

	//---------------------------------------------------------------------------------- $queries_log
	/**
	 * The queries log
	 *
	 * All executed queries are logged here.
	 *
	 * @var string[]
	 */
	public array $queries_log = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration = [])
	{
		if (isset($configuration[self::CONTINUE_LOG])) {
			$this->continue = $configuration[self::CONTINUE_LOG];
			if (isset($configuration[self::EXCLUDE])) {
				foreach ($configuration[self::EXCLUDE] as $exclude) {
					if (str_contains(SL . $_SERVER['REQUEST_URI'] . SL, SL . $exclude . SL)) {
						$this->continue = false;
						break;
					}
				}
			}
		}
		if (isset($configuration[self::DISPLAY_LOG])) {
			$this->display_log = $configuration[self::DISPLAY_LOG];
		}
	}

	//------------------------------------------------------------------------ afterMainControllerRun
	/**
	 * After main controller run, display query log
	 */
	public function afterMainControllerRun()
	{
		$this->main_controller_counter --;
		if ($this->display_log && !$this->main_controller_counter) {
			$this->dumpLog();
		}
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public function beforeMainControllerRun()
	{
		$this->main_controller_counter ++;
	}

	//--------------------------------------------------------------------------------------- dumpLog
	/**
	 * Display query log
	 */
	public function dumpLog()
	{
		echo '<h3>Mysql log</h3>';
		echo '<div class="Mysql logger query">' . LF;
		echo '<pre>' . print_r($this->queries_log, true) . '</pre>' . LF;
		echo ' </div>' . LF;
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * Called each time before a mysql_query() call is done : log the query
	 *
	 * @param $query string
	 */
	public function onQuery(string $query)
	{
		if ($this->continue && $this->display_log) {
			echo '<div class="Mysql logger query">' . $query . '</div>' . LF;
		}
		$this->queries_log[] = $query;
	}

	//---------------------------------------------------------------------------------- onQueryError
	/**
	 * Called each time after a mysql_query() call is done : log the error (if some)
	 *
	 * @param $object Contextual_Mysqli
	 * @param $query  string
	 */
	public function onQueryError(Contextual_Mysqli $object, string $query)
	{
		$mysqli = $object;

		$error = $mysqli->last_errno . ': ' . $mysqli->last_error . ' [' . LF . trim($query) . LF . ']';

		$this->errors_log[]  = $error;
		$this->queries_log[] = '# ERROR ' . str_replace(LF, LF . '# ', $error);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Contextual_Mysqli::class, 'query'],      [$this, 'onQuery']);
		$aop->beforeMethod([Contextual_Mysqli::class, 'queryError'], [$this, 'onQueryError']);
		if (!$this->continue) {
			$aop->afterMethod([Main::class, 'runController'], [$this, 'afterMainControllerRun']);
			$aop->beforeMethod([Main::class, 'runController'], [$this, 'beforeMainControllerRun']);
		}
	}

}
