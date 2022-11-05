<?php
namespace ITRocks\Framework\Tools\OS;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Tools\OS\Asynchronous_Task\Linux_Cron;

/**
 * OS asynchronous task scheduler
 *
 * - linux : crontab (user files stored into /var/spool/cron/crontabs or /var/spool/cron)
 */
class Asynchronous_Task
{

	//-------------------------------------------------------------------------------------- $command
	/**
	 * @var string
	 */
	public string $command;

	//--------------------------------------------------------------------------------- $day_of_month
	/**
	 * @var string
	 */
	public string $day_of_month = '*';

	//---------------------------------------------------------------------------------- $day_of_week
	/**
	 * @var string
	 */
	public string $day_of_week = '*';

	//----------------------------------------------------------------------------------------- $hour
	/**
	 * @var string
	 */
	public string $hour = '*';

	//--------------------------------------------------------------------------------------- $minute
	/**
	 * @var string
	 */
	public string $minute = '*';

	//---------------------------------------------------------------------------------------- $month
	/**
	 * @var string
	 */
	public string $month = '*';

	//-------------------------------------------------------------------------------------- $wrapper
	/**
	 * @var ?object
	 */
	public ?object $wrapper;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $command string|null
	 */
	public function __construct(string $command = null)
	{
		if ($command) {
			$this->command = $command;
		}
		if (PHP_OS_FAMILY === 'Linux') {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$this->wrapper = Builder::create(Linux_Cron::class, [$this]);
		}
	}

	//------------------------------------------------------------------------------------------- add
	public function add() : void
	{
		if ($this->wrapper) {
			$this->wrapper->add();
		}
	}

	//---------------------------------------------------------------------------------------- remove
	public function remove() : void
	{
		if ($this->wrapper) {
			$this->wrapper->remove();
		}
	}

}
