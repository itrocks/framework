<?php
namespace ITRocks\Framework;

use Exception;

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

	//------------------------------------------------------------------------------ $already_running
	/**
	 * Already running grep result lines
	 *
	 * @var string[]
	 */
	private array $already_running;

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * The arguments passed to the URI (get / post vars)
	 * -g argument switches to get vars (default)
	 * -p argument switches to post vars
	 *
	 * @var string[]
	 */
	public array $arguments;

	//-------------------------------------------------------------------------------------- $current
	/**
	 * The current running command console object
	 *
	 * @var ?Console
	 */
	public static ?Console $current = null;

	//--------------------------------------------------------------------------------- $running_file
	/**
	 * The name of the running file (set once runningFileName() is called)
	 *
	 * @var string
	 */
	private string $running_file;

	//--------------------------------------------------------------------------------------- $script
	/**
	 * The script initially called (matches $argv[0])
	 *
	 * @var string
	 */
	public string $script;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The called URI (without arguments. Always start with '/')
	 *
	 * @var string
	 */
	public string $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments string[] command line arguments
	 */
	public function __construct(array $arguments = [])
	{
		if ($arguments) {
			$this->script = $arguments[0];

			if (empty($arguments[1]) || !str_starts_with($arguments[1], '/')) {
				$this->uri       = '/';
				$this->arguments = array_slice($arguments, 1);
			}
			else {
				$this->uri       = $arguments[1];
				$this->arguments = array_slice($arguments, 2);
			}
		}
		else {
			$this->uri       = '/';
			$this->arguments = [];
		}
	}

	//------------------------------------------------------------------------- alreadyRunningMessage
	/**
	 * @return string
	 */
	private function alreadyRunningMessage() : string
	{
		return 'Already running ' . $this->uri . "\n" . print_r($this->already_running, true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Called after execution ends : remove running file and process info
	 */
	public function end() : void
	{
		/** @noinspection PhpUsageOfSilenceOperatorInspection No warning if removed by someone else */
		@unlink($this->runningFileName());
		$this->procInfoPurgeProc();
	}

	//------------------------------------------------------------------------------ isAlreadyRunning
	/**
	 * Checks if the feature is already running (must have been launched from the command line)
	 *
	 * @return boolean
	 */
	private function isAlreadyRunning() : bool
	{
		$count = 0;
		$cwd   = getcwd();
		$this->already_running = [];
		exec("ps -aux | grep $this->uri | grep -v grep", $outputs);
		foreach ($outputs as $output) {
			if (($pos = strpos($output, $this->uri)) && str_contains($output, self::PHP_PATH)) {
				$pid = intval(trim(substr($output, strpos($output, ' '))));
				if (
					in_array(substr($output, $pos + strlen($this->uri), 1), ['', ' ', "\n", "\r", "\t"])
					&& ($this->procInfoGetCwd($pid) === $cwd)
				) {
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
	private function parseArguments() : void
	{
		/** @noinspection PhpArrayWriteIsNotUsedInspection Of course it is */
		$_GET = ['as_widget' => true];
		$var  = '-g';
		foreach ($this->arguments as $argument) {
			if (str_starts_with($argument, '-')) {
				$var = $argument;
			}
			else {
				[$name, $value] = str_contains($argument, '=')
					? explode('=', $argument, 2)
					: [$argument, false];
				if ($var === '-h') {
					$_SERVER ['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
				}
				else switch ($var) {
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
	public function prepare() : bool
	{
		$this->procInfoPurge();
		$this->procInfoWrite();
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
		$this->procInfoPurgeProc();
		return false;
	}

	//----------------------------------------------------------------------- prepareExecutionContext
	/**
	 * Prepare execution context
	 */
	private function prepareExecutionContext() : void
	{
		$_SERVER['HTTPS']       = true;
		$_SERVER['PATH_INFO']   = $this->uri;
		$_SERVER['REMOTE_ADDR'] = 'console';
		$_SERVER['REQUEST_URI'] = $this->uri;
		$_SERVER['SCRIPT_NAME'] = '/console';
	}

	//-------------------------------------------------------------------------------- procInfoGetCwd
	/**
	 * @param $pid integer|null null for 'current pid'
	 * @return string '' if pid is not running
	 */
	private function procInfoGetCwd(int $pid = null) : string
	{
		if (!$pid) {
			$pid = getmypid();
		}
		$path = $this->procPath();
		$file = "$path/$pid/cwd";
		return file_exists($file) ? readlink($file) : '';
	}

	//--------------------------------------------------------------------------------- procInfoPurge
	/**
	 * Purge proc information about pid that are not running anymore
	 */
	private function procInfoPurge() : void
	{
		$path = $this->procPath();
		exec('ps -aux | awk \'{print $2}\'', $running_processes);
		foreach (scandir($path) as $pid) if ($pid[0] !== '.') {
			if (!in_array($pid, $running_processes)) {
				$this->procInfoPurgeProc($pid, $path);
			}
		}
	}

	//----------------------------------------------------------------------------- procInfoPurgeProc
	/**
	 * @param $pid  integer|null
	 * @param $path string|null
	 */
	private function procInfoPurgeProc(int $pid = null, string $path = null) : void
	{
		if (!$path) {
			$path = $this->procPath();
		}
		if (!$pid) {
			$pid = getmypid();
		}
		if (is_dir("$path/$pid")) {
			try {
				foreach (scandir("$path/$pid") as $file) {
					if ($file[0] !== '.') {
						@unlink("$path/$pid/$file");
					}
				}
				@rmdir("$path/$pid");
			}
			catch (Exception) {
				// ignore files created by another user : this user will purge them
			}
		}
	}

	//--------------------------------------------------------------------------------- procInfoWrite
	private function procInfoWrite() : void
	{
		$cwd  = getcwd();
		$path = $this->procPath();
		$pid  = getmypid();
		if (!file_exists("$path/$pid")) {
			mkdir("$path/$pid");
			chmod("$path/$pid", 0777);
		}
		symlink($cwd, "$path/$pid/cwd");
	}

	//-------------------------------------------------------------------------------------- procPath
	/**
	 * @return string
	 */
	private function procPath() : string
	{
		exec('hostname', $hostname);
		$hostname = reset($hostname);
		$path     = '../proc';
		if (!file_exists($path)) {
			mkdir($path);
			chmod($path, 0777);
		}
		if (!file_exists("$path/$hostname")) {
			mkdir("$path/$hostname");
			chmod("$path/$hostname", 0777);
		}
		return "$path/$hostname";
	}

	//------------------------------------------------------------------------------- runningFileName
	/**
	 * Calculates the current running file name (complete path)
	 *
	 * This is a little bit hard-coded, sorry :)
	 * This complies to the default way itrocks/deploy / itrocks/platform works
	 *
	 * You will find almost the same code into console_script.php's Console::runningFileName()
	 *
	 * @return string
	 * @see Environment::runningFileName
	 */
	private function runningFileName() : string
	{
		if (!isset($this->running_file)) {
			if (substr_count(__DIR__, '/') > 4) {
				[,, $vendor, $project, $environment] = explode('/', __DIR__);
				$prepend = $vendor . '-' . $project . '-' . $environment . '-';
			}
			else {
				$prepend = '';
			}
			$this->running_file = $this->temporaryDirectory() . '/'
				. $prepend . (str_replace('/', '_', substr($this->uri, 1)) ?: 'index');
		}
		return $this->running_file;
	}

	//------------------------------------------------------------------------------ storeRunningFile
	private function storeRunningFile() : void
	{
		$running_filename = $this->runningFileName();
		touch($running_filename);
		chmod($running_filename, 0777);
	}

	//---------------------------------------------------------------------------- temporaryDirectory
	/**
	 * Gets the temporary directory path
	 * Creates the directory into the project directory if it does not exist
	 *
	 * @return string
	 */
	private function temporaryDirectory() : string
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
	private function waitForUnlock() : void
	{
		while (is_file('lock-console')) {
			usleep(100000);
			clearstatcache(true, 'lock-console');
		}
	}

}
