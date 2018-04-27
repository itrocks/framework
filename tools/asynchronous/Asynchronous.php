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
	public $free_sessions = [];

	//-------------------------------------------------------------------------------- $max_processes
	/**
	 * @setter
	 * @var integer
	 */
	public $max_processes = 8;

	//---------------------------------------------------------------------------- $running_processes
	/**
	 * @var Process[]
	 */
	public $running_processes = [];

	//------------------------------------------------------------------------------ $session_counter
	/**
	 * @var integer
	 */
	public $session_counter = 0;

	//---------------------------------------------------------------------------- $waiting_processes
	/**
	 * @var Process[]
	 */
	public $waiting_processes = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs an asynchronous calls stack
	 *
	 * @param $max_processes integer
	 */
	public function __construct($max_processes = null)
	{
		if (isset($max_processes)) {
			$this->max_processes = $max_processes;
		}
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Calls an URI (controller) from the console asynchronously, using the same session handler than
	 * the caller
	 *
	 * @param $uri           string   Call this URI : link to a controller, including parameters
	 * @param $then          callable A callback called when the job is done
	 * @param $needs_session boolean  true to automatically clone current session (authenticated call)
	 */
	public function call($uri, callable $then = null, $needs_session = true)
	{
		if ($position = strpos($uri, '?')) {
			$uri[$position] = SP;
			while ($position = strpos($uri, '&', $position)) {
				$uri[$position] = SP;
			}
		}
		$position = strpos($uri, SP) ?: strlen($uri);
		$uri      = substr($uri, 0, $position)
			. SL . 's' . ++$this->session_counter
			. substr($uri, $position);
		$this->run('itrocks/framework/console.php' . SP . rawurldecode($uri), $then, $needs_session);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Calls a command line asynchronously
	 *
	 * @param $command       string   The command line to run
	 * @param $then          callable A callback called when the job is done
	 * @param $needs_session boolean  true to automatically clone current session (authenticated call)
	 * @return Process
	 */
	public function run($command, callable $then = null, $needs_session = false)
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
	 *
	 * @param $process Process
	 */
	protected function runProcess(Process $process)
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
	protected function runWaitingProcesses()
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
	 * $max_processes @setter : if increased, runs waiting processes immediately
	 *
	 * @param $max_processes string
	 */
	protected function setMaxProcesses($max_processes)
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
	 * @param $sleep  integer  The sleep between each wait control, in microseconds (default: 1ms)
	 */
	public function wait($reload = false, $sleep = 1000)
	{
		if ($reload && ($reload !== true) && (count($this->running_processes) < $this->max_processes)) {
			call_user_func($reload);
		}
		while ($this->running_processes || $this->waiting_processes) {
			$unset = false;
			foreach ($this->running_processes as $key => $process) {
				if (!$process->status()) {
					unset($this->running_processes[$key]);
					if ($process->session_id) {
						$this->free_sessions[] = $process->session_id;
					}
					$this->runWaitingProcesses();
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
			}
			if ($sleep && !$unset) {
				usleep($sleep);
			}
		}
	}

}
