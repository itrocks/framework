<?php
namespace ITRocks\Framework\Tools\Asynchronous;

/**
 * An asynchronous process
 *
 * @example
 * $process = new Process('echo hello');
 * $process->run();
 * while ($process->status());
 * echo $process->output;
 * // will display 'hello'
 */
class Process
{

	//------------------------------------------------------------------------------------- $callback
	/**
	 * If set, this will be called once status() returns false (done)
	 *
	 * The first two elements are the callable
	 * Next optional elements are parameters that will be transmitted to the callable
	 *
	 * @var callable|array
	 */
	public $callback;

	//-------------------------------------------------------------------------------------- $command
	/**
	 * The running command
	 *
	 * @var string
	 */
	public $command;

	//--------------------------------------------------------------------------------------- $errors
	/**
	 * The errors output stream :
	 * - if status() returns true (running) : a resource containing the errors stream
	 * - if status() returns false (done) : the full errors stream text
	 *
	 * @var string|resource
	 */
	public $errors;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * This is the process identifier on the system where it is launched
	 *
	 * @null
	 * @var integer
	 */
	public $identifier;

	//--------------------------------------------------------------------------------------- $output
	/**
	 * The standard output stream :
	 * - if status() returns true (running) : a resource containing the output stream
	 * - if status() returns false (done) : the full output stream text
	 *
	 * @var string|resource
	 */
	public $output;

	//-------------------------------------------------------------------------------------- $process
	/**
	 * The process resource
	 * - set by run()
	 * - if status() returns true (running), the resource is still opened
	 * - once status() returns false (done), the resource is freed and this becomes null
	 *
	 * @var resource
	 */
	public $process;

	//----------------------------------------------------------------------------------- $session_id
	/**
	 * If set : the PHP session identifier for a called URI
	 *
	 * @var string
	 */
	public $session_id;

	//---------------------------------------------------------------------------- $unique_identifier
	/**
	 * You can associate an unique identifier, eg using uniqid('prefix_', true), to your process
	 *
	 * @var string
	 */
	public $unique_identifier;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $command  string
	 * @param $callback callable|array
	 */
	public function __construct($command = null, array $callback = null)
	{
		if (isset($command)) {
			$this->command = $command;
		}
		if (isset($callback)) {
			$this->callback = $callback;
		}
	}

	//------------------------------------------------------------------------------------------ done
	/**
	 * Called by state() when the process is done :
	 * - gets errors
	 * - gets output
	 * - closes the process
	 * - calls the callback with callback parameters, then the Process as last parameter
	 */
	protected function done()
	{
		$this->errors = is_resource($this->errors)
			? trim(stream_get_contents($this->errors))
			: 'no-resource';
		$this->output = is_resource($this->output)
			? trim(stream_get_contents($this->output))
			: 'no-resource';
		proc_close($this->process);
		$this->process = null;
		if (isset($this->callback)) {
			call_user_func_array(
				array_slice($this->callback, 0, 2),
				array_merge(array_slice($this->callback, 2), [$this])
			);
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Runs the command
	 */
	public function run()
	{
		$this->process = proc_open(
			$this->command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes
		);
		$this->output = $pipes[1];
		$this->errors = $pipes[2];
		if ($status = proc_get_status($this->process)) {
			$this->identifier = $status['pid'];
		}
	}

	//---------------------------------------------------------------------------------------- status
	/**
	 * Returns the process running status
	 *
	 * @return boolean true if the process is running, false if it's done
	 */
	public function status()
	{
		if ($this->process) {
			$status = proc_get_status($this->process);
			if ($status['running']) {
				return true;
			}
			$this->done();
		}
		return false;
	}

}
