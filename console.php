#!/usr/bin/php
<?php
namespace ITRocks\Framework;

/**
 * Call with php itrocks/framework/console.php
 *
 * Call this script from command line / scheduled tasks to call features from the software
 * - Will be considered like running under HTTPS
 * - Remote address will be 'console'
 */
error_reporting(E_ALL);

/**
 * The ITRocks\Framework\Console class represents information about the console
 */
class Console
{

	//-------------------------------------------------------------------------------------- PHP_PATH
	/**
	 * The path for the php executable on your system
	 */
	const PHP_PATH = '/usr/bin/php';

	//--------------------------------------------------------------------------- TEMPORARY_DIRECTORY
	/**
	 * The path of the preferred temporary directory.
	 * Will be used only if exists : if it does not, a temporary directory named 'tmp' will be created
	 * into your project root folder.
	 */
	const TEMPORARY_DIRECTORY = '/home/tmp';

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * The arguments passed to the URI (get / post vars)
	 * -g argument switches to get vars (default)
	 * -p argument switches to post vars
	 *
	 * @var string[]
	 */
	public $arguments;

	//------------------------------------------------------------------------------ $already_running
	/**
	 * Already running grep result lines
	 *
	 * @var string[]
	 */
	private $already_running;

	//-------------------------------------------------------------------------------------- $current
	/**
	 * The current running command console object
	 *
	 * @var self
	 */
	public static $current;

	//--------------------------------------------------------------------------------- $running_file
	/**
	 * The name of the running file (set once runningFileName() is called)
	 *
	 * @var string
	 */
	private $running_file;

	//--------------------------------------------------------------------------------------- $script
	/**
	 * The script initially called (matches $argv[0])
	 *
	 * @var string
	 */
	public $script;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The called URI (without arguments. Always start with '/')
	 *
	 * @var string
	 */
	public $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments string[] command line arguments
	 */
	public function __construct(array $arguments)
	{
		$this->script = $arguments[0];
		if (empty($arguments[1]) || (substr($arguments[1], 0, 1) !== '/')) {
			$this->uri = '/';
			$this->arguments = array_slice($arguments, 1);
		}
		else {
			$this->uri = $arguments[1];
			$this->arguments = array_slice($arguments, 2);
		}
	}

	//------------------------------------------------------------------------- alreadyRunningMessage
	/**
	 * @return string
	 */
	private function alreadyRunningMessage()
	{
		return 'Already running ' . $this->uri . "\n" . print_r($this->already_running, true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Called after execution ends : remove running file
	 */
	public function end()
	{
		/** @noinspection PhpUsageOfSilenceOperatorInspection No warning if removed by someone else */
		@unlink($this->runningFileName());
	}

	//------------------------------------------------------------------------------ isAlreadyRunning
	/**
	 * Checks if the feature is already running (must have been launched from the command line)
	 *
	 * @return boolean
	 */
	private function isAlreadyRunning()
	{
		$count = 0;
		$this->already_running = [];
		exec("ps -aux | grep $this->uri | grep -v grep", $outputs);
		foreach ($outputs as $output) {
			if (($pos = strpos($output, $this->uri)) && strpos($output, self::PHP_PATH)) {
				if (in_array(substr($output, $pos + strlen($this->uri), 1), ['', ' ', "\n", "\r", "\t"])) {
					$this->already_running[] = $output;
					$count++;
				}
			}
		}
		return $count > 1;
	}

	//-------------------------------------------------------------------------------- parseArguments
	/**
	 * Parse arguments and change them to $_COOKIE / $_FILES / $_GET / $_POST / $_REQUEST / $_SERVER
	 * values
	 */
	private function parseArguments()
	{
		$_GET = ['as_widget' => true];
		$var  = '-g';
		foreach ($this->arguments as $argument) {
			if (substr($argument, 0, 1) === '-') {
				$var = $argument;
			}
			else {
				list($name, $value) = strpos($argument, '=')
					? explode('=', $argument, 2)
					: [$argument, false];
				switch ($var) {
					case '-c': $_COOKIE [$name] = $value; break;
					case '-f': $_FILES  [$name] = $value; break;
					case '-g': $_GET    [$name] = $value; break;
					case '-p': $_POST   [$name] = $value; break;
					case '-r': $_REQUEST[$name] = $value; break;
					case '-s': $_SERVER [$name] = $value; break;
					default: trigger_error('Unknown option ' . $var, E_USER_ERROR);
				}
				if (in_array($var, ['-g', '-p'])) {
					$_REQUEST[$name] = $value;
				}
			}
		}
	}

	//--------------------------------------------------------------------------------------- prepare
	/**
	 * Prepare console for script execution
	 * Returns true if is ready for execution
	 *
	 * Execution is a require(__DIR__ . '/index.php') and must be called once from the global context
	 *
	 * @return boolean true if prepared and can execute. If false, please don't execute.
	 */
	public function prepare()
	{
		if ($this->isAlreadyRunning()) {
			echo $this->alreadyRunningMessage();
		}
		else {
			$this->storeRunningFile();
			if (empty($_GET)) {
				$this->prepareExecutionContext();
				$this->parseArguments();
				$this->waitForUnlock();
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------- prepareExecutionContext
	/**
	 * Prepare execution context
	 */
	private function prepareExecutionContext()
	{
		$_SERVER['HTTPS']       = true;
		$_SERVER['PATH_INFO']   = $this->uri;
		$_SERVER['REMOTE_ADDR'] = 'console';
		$_SERVER['SCRIPT_NAME'] = '/console.php';
	}

	//------------------------------------------------------------------------------- runningFileName
	/**
	 * @return string
	 */
	private function runningFileName()
	{
		if (!isset($this->running_file)) {
			$this->running_file = $this->temporaryDirectory() . '/'
				. (str_replace('/', '_', substr($this->uri, 1)) ?: 'index');
		}
		return $this->running_file;
	}

	//------------------------------------------------------------------------------ storeRunningFile
	private function storeRunningFile()
	{
		touch($this->runningFileName());
	}

	//---------------------------------------------------------------------------- temporaryDirectory
	/**
	 * Gets the temporary directory path
	 * Creates the directory into the project directory if it does not exist
	 *
	 * @return string
	 */
	private function temporaryDirectory()
	{
		$directory = (file_exists(self::TEMPORARY_DIRECTORY) && is_dir(self::TEMPORARY_DIRECTORY))
			? self::TEMPORARY_DIRECTORY
			: (__DIR__ . '/../../tmp');
		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
			chmod($directory, 0777);
		}
		return $directory;
	}

	//--------------------------------------------------------------------------------- waitForUnlock
	private function waitForUnlock()
	{
		while (is_file('lock-console')) {
			usleep(100000);
			clearstatcache(true, 'lock-console');
		}
	}

}

Console::$current = new Console(isset($argv) ? $argv : null);
if (Console::$current->prepare()) {
	include_once __DIR__ . '/index.php';
	Console::$current->end();
}
