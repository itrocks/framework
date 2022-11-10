<?php
namespace ITRocks\Framework\Logger;

use ITRocks\Framework;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Logger\Entry\Data;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Log class stores logs information
 *
 * @business
 * @feature Consulting the software usage log
 * @feature
 * @feature fileExport
 * @feature_menu Administration
 * @list start, stop, duration, uri, data.arguments
 * @representative start, uri
 * @sort -start, uri
 * @store_name logs
 */
class Entry implements Validate\Except
{

	//---------------------------------------------------------------------------------- CONSOLE_USER
	const CONSOLE_USER = 2;

	//------------------------------------------------------------------------------------- CRON_USER
	const CRON_USER = 3;

	// TODO HIGH #71516 Fix Builder\Compiler as this should be replaced by dynamic call in config.php
	use Framework\Dao\Mysql\File_Logger\Entry;
	use Framework\View\Logger\Entry;

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @component
	 * @integrated alias
	 * @link Object
	 * @var ?Data
	 */
	public ?Data $data;

	//------------------------------------------------------------------------------------- $duration
	/**
	 * Until stop() is not called, $duration contains the start microtime.
	 * After stop() is called, it contains the number of seconds between start and stop, with a
	 * precision near to the microsecond.
	 *
	 * @var float
	 */
	public float $duration = .0;

	//------------------------------------------------------------------------------- $duration_start
	/**
	 * This is the microtime when the script starts.
	 * Used to calculate duration on resume() and stop()
	 *
	 * @store false
	 * @var float
	 */
	protected float $duration_start;

	//--------------------------------------------------------------------------------- $memory_usage
	/**
	 * Memory peak usage in MB
	 *
	 * @max_length 7
	 * @var integer
	 */
	public int $memory_usage;

	//------------------------------------------------------------------------------ $mysql_thread_id
	/**
	 * @var integer
	 */
	public int $mysql_thread_id;

	//----------------------------------------------------------------------------------- $process_id
	/**
	 * @var integer
	 */
	public int $process_id;

	//----------------------------------------------------------------------------------- $session_id
	/**
	 * @var string
	 */
	public string $session_id;

	//---------------------------------------------------------------------------------------- $start
	/**
	 * @link DateTime
	 * @show_seconds
	 * @var Date_Time|string
	 */
	public Date_Time|string $start;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * @link DateTime
	 * @show_seconds
	 * @var Date_Time|string
	 */
	public Date_Time|string $stop;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * @max_length 255
	 * @var string
	 */
	public string $uri;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var ?User
	 */
	public ?User $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor initialises logged information for a call on script beginning.
	 *
	 * @param $uri       ?string
	 * @param $arguments ?array
	 * @param $form      ?array
	 * @param $files     ?array[]
	 */
	public function __construct(
		string $uri = null, array $arguments = null, array $form = null, array $files = null
	) {
		if (isset($uri)) {
			/** @noinspection PhpTypedPropertyMightBeUninitializedInspection @link DateTime */
			if ($this->start->isEmpty()) {
				$this->duration_start = microtime(true);
				$this->start = new Date_Time();
			}
			if (!isset($this->process_id)) {
				$this->process_id = getmypid();
			}
			if (!isset($this->mysql_thread_id)) {
				$dao = Dao::current();
				if ($dao instanceof Link) {
					$this->mysql_thread_id = $dao->getConnection()->thread_id;
				}
			}
			if (!isset($this->session_id)) {
				$this->session_id = session_id();
			}
			if (!isset($this->uri)) {
				$this->uri = $uri;
			}
			if (
				($arguments || $form || $files || isset($_SERVER['HTTP_X_REQUEST_ID']))
				&& !isset($this->data)
			) {
				$this->data = new Data(
					$arguments,
					$form,
					$files,
					$_SERVER['HTTP_X_REQUEST_ID'] ?? null
				);
			}
			if (!isset($this->memory_usage)) {
				$this->memory_usage = ceil(memory_get_peak_usage(true) / 1024 / 1024);
			}

			$this->user = User::current();
			if (!Dao::getObjectIdentifier($this->user)) {
				$this->user = null;
			}
			if (!$this->user && ($_SERVER['REMOTE_ADDR'] === 'console')) {
				// check grandparent process is CRON (parent is a shell process)
				$process = explode(
					SP, exec('ps -p $(ps -o ppid= -p ' . posix_getppid() . ') -o command | tail -1')
				)[0];
				$this->user = Dao::read(
					strcasecmp($process, '/usr/sbin/CRON') ? self::CONSOLE_USER : self::CRON_USER,
					User::class
				);
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return trim(Loc::dateToLocale($this->start) . SP . $this->uri);
	}

	//----------------------------------------------------------------------------------- rawPostData
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $data mixed
	 */
	public function rawPostData(mixed $data) : void
	{
		if (!$data) {
			return;
		}
		if (!$this->data) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$this->data        = Builder::create(Data::class);
			$this->data->entry = $this;
		}
		$this->data->rawFormData($data);
		Dao::write($this->data, Dao::getObjectIdentifier($this->data) ? Dao::only('form') : []);
	}

	//---------------------------------------------------------------------------------------- resume
	public function resume() : void
	{
		$this->duration     = microtime(true) - $this->duration_start;
		$this->memory_usage = ceil(memory_get_peak_usage(true) / 1024 / 1024);
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop() : void
	{
		$this->duration     = microtime(true) - $this->duration_start;
		$this->memory_usage = ceil(memory_get_peak_usage(true) / 1024 / 1024);
		$this->stop = new Date_Time();
	}

}
