<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Asynchronous\Process;

/**
 * Asynchronous controller execution
 */
class Asynchronous
{

	//--------------------------------------------------------------------------------- NEEDS_SESSION
	const NEEDS_SESSION = 'needs_session';

	//-------------------------------------------------------------------------------- $free_sessions
	/**
	 * Free session identifiers : set by done called URIs, consumed by new called URIs
	 *
	 * @var string[]
	 */
	public array $free_sessions = [];

	//-------------------------------------------------------------------------------- $max_processes
	/**
	 * #Setter
	 */
	public int $max_processes = 8;

	//---------------------------------------------------------------------------- $running_processes
	/**
	 * @var Process[]
	 */
	public array $running_processes = [];

	//------------------------------------------------------------------------------ $session_counter
	public int $session_counter = 0;

	//---------------------------------------------------------------------------- $waiting_processes
	/**
	 * @var Process[]
	 */
	public array $waiting_processes = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs an asynchronous calls stack
	 */
	public function __construct(int $max_processes = null)
	{
		if (isset($max_processes)) {
			$this->max_processes = $max_processes;
		}
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Calls a URI (controller) from the console asynchronously, using the same session handler than
	 * the caller
	 *
	 * @param $uri              string Call this URI : link to a controller, including parameters
	 * @param $then             array A callback for when the job is done (class + method + arguments)
	 * @param $needs_session    boolean True automatically clones current session (authenticated call)
	 * @param $needs_identifier boolean True generates an unique identifier for X-Request-ID
	 * @return Process
	 */
	public function call(
		string $uri, array $then = [], bool $needs_session = true, bool $needs_identifier = false
	) : Process
	{
		if ($position = strpos($uri, '?')) {
			$uri[$position] = SP;
			while ($position = strpos($uri, '&', $position)) {
				$uri[$position] = SP;
			}
		}
		if ($needs_identifier) {
			$unique_identifier = uniqid('async-', true);
			$parameters        = ' -h X-Request-ID=' . $unique_identifier;
		}
		else {
			$parameters = '';
		}

		$process = $this->run(
			getcwd() . '/itrocks/framework/console' . SP . rawurldecode($uri) . $parameters,
			$then,
			$needs_session
		);
		if (isset($unique_identifier)) {
			$process->unique_identifier = $unique_identifier;
		}
		return $process;
	}

	//----------------------------------------------------------------------------------------- flush
	/**
	 * Flush asynchronous process running events : done processes, callbacks, run waiting processes
	 */
	public function flush() : void
	{
		$done_processes = 0;
		foreach ($this->running_processes as $key => $process) {
			if (!$process->status()) {
				$done_processes ++;
				unset($this->running_processes[$key]);
				if ($process->session_id) {
					$this->free_sessions[] = $process->session_id;
				}
			}
		}
		if ($done_processes) {
			$this->runWaitingProcesses();
		}
	}

	//-------------------------------------------------------------------------------- processesCount
	/**
	 * Counts how many processes are running or waiting (total).
	 * When 0: all processes are done.
	 */
	public function processesCount() : int
	{
		return count($this->running_processes) + count($this->waiting_processes);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Calls a command line asynchronously
	 *
	 * @param $command       string The command line to run
	 * @param $then          array A callback called when the job is done (class + method + arguments)
	 * @param $needs_session boolean true to automatically clone current session (authenticated call)
	 * @return Process
	 */
	public function run(string $command, array $then = [], bool $needs_session = false) : Process
	{
		$process = new Process($command, $then);
		if ($needs_session) {
			$process->session_id = static::NEEDS_SESSION;
		}
		if (count($this->running_processes) < $this->max_processes) {
			$this->running_processes[] = $process;
			$this->runProcess($process);
		}
		else {
			$this->waiting_processes[] = $process;
		}
		return $process;
	}

	//------------------------------------------------------------------------------------ runProcess
	/**
	 * Runs the process. Appends the session id to the command if needed
	 */
	protected function runProcess(Process $process) : void
	{
		if ($process->session_id === static::NEEDS_SESSION) {
			$process->session_id = $this->free_sessions
				? array_pop($this->free_sessions)
				: Session::cloneSessionId();
			$process->command .= SP . session_name() . '=' . $process->session_id;
		}
		$process->run();
	}

	//--------------------------------------------------------------------------- runWaitingProcesses
	/**
	 * Runs waiting processes, up to $max_processes simultaneously running processes
	 */
	protected function runWaitingProcesses() : void
	{
		while (
			(count($this->running_processes) < $this->max_processes)
			&& ($process = array_shift($this->waiting_processes))
		) {
			$this->running_processes[] = $process;
			$this->runProcess($process);
		}
	}

	//------------------------------------------------------------------------------- setMaxProcesses
	/**
	 * $max_processes #Setter : if increased, runs waiting processes immediately
	 */
	protected function setMaxProcesses(int $max_processes) : void
	{
		$this->max_processes = $max_processes;
		$this->runWaitingProcesses();
	}

	//------------------------------------------------------------------------------------------ wait
	/**
	 * Wait for all called controllers to have done their job
	 *
	 * @param $reload boolean|callable If false (default), returns once all processes are done.
	 *                If true, returns once less than $max_processes are still running.
	 *                If a callable, this callback is called each time less than
	 *                $max_processes are still running.
	 * @param $sleep  float The sleep between each wait control, in seconds (default: .1s)
	 */
	public function wait(bool|callable $reload = false, float $sleep = .1) : void
	{
		$sleep = floor($sleep * 1000000);
		if (is_callable($reload) && (count($this->running_processes) < $this->max_processes)) {
			call_user_func($reload);
		}
		while ($processes_count = $this->processesCount()) {
			$unset = false;
			$this->flush();
			if ($this->processesCount() < $processes_count) {
				if ($reload && (count($this->running_processes) < $this->max_processes)) {
					if ($reload === true) {
						return;
					}
					else {
						call_user_func($reload);
					}
				}
				$unset = true;
			}
			if ($sleep && !$unset) {
				usleep($sleep);
			}
		}
	}

}
