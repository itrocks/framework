<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;

/**
 * @business
 */
class Lock
{
	use Has_Creation_Date_Time;

	//------------------------------------------------------------------------------------------ READ
	const READ = 'read';

	//------------------------------------------------------------------------------------------ WAIT
	/**
	 * When calling Dao::lock(), the caller process will wait until it is unlocked.
	 * - With this is option, it will wait until lock and will always return true.
	 * - Without this option, it will always return a result : true if locked, false if it could not.
	 */
	const WAIT = 'wait';

	//----------------------------------------------------------------------------------------- WRITE
	/**
	 * The hardest lock : other processes will not be enabled to read nor write this record
	 */
	const WRITE = 'write';

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @store false
	 * @var integer
	 */
	public $count = 1;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var integer
	 */
	public $identifier;

	//------------------------------------------------------------------------------ $mysql_thread_id
	/**
	 * Mysql thread identifier
	 *
	 * @var integer
	 */
	public $mysql_thread_id;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @values static::const
	 * @var string[]
	 */
	public $options;

	//--------------------------------------------------------------------------- $process_identifier
	/**
	 * PHP running process identifier
	 * For information / debugging purpose
	 *
	 * @var integer
	 */
	public $process_identifier;

	//----------------------------------------------------------------------------------- $table_name
	/**
	 * @var string
	 */
	public $table_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : initializes data
	 *
	 * @param $table_name string
	 * @param $identifier integer
	 * @param $options    string[] @values static::const
	 */
	public function __construct($table_name = null, $identifier = null, $options = null)
	{
		if (!isset($this->creation)) {
			$this->creation = Date_Time::now();
		}
		if (!isset($this->mysql_thread_id)) {
			$link = Dao::current();
			if ($link instanceof Link) {
				$this->mysql_thread_id = $link->getConnection()->thread_id;
			}
		}
		if (!isset($this->process_identifier)) {
			$this->process_identifier = getmypid();
		}
		if (isset($identifier)) {
			$this->identifier = $identifier;
		}
		if (isset($options)) {
			$this->options = $options;
		}
		if (isset($table_name)) {
			$this->table_name = $table_name;
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the existing active lock matching this table name and record identifier
	 *
	 * If a lock entry exists but is not associated to an alive mysql thread id : it is purged and
	 * considered as non-existent
	 *
	 * @param $table_name        string
	 * @param $record_identifier integer
	 * @param $link              Link
	 * @return Lock
	 */
	public static function get($table_name, $record_identifier, Link $link = null)
	{
		if (!$link) {
			$link = Dao::current();
		}
		/** @var $lock Lock[] */
		$lock = $link->query(
			'SELECT * FROM `locks`' . LF
			. ' WHERE `table_name` = ' . DQ . $table_name . DQ
			. ' AND `identifier` = ' . $record_identifier,
			Lock::class
		);
		$lock = reset($lock);
		/** @var $lock Lock */
		if ($lock) {
			// is the lock still alive ?
			$alive = false;
			foreach ($link->query('SHOW PROCESSLIST', Process::class) as $process) {
				/** @var $process Process */
				if ($process->getMysqlThreadId() === $lock->mysql_thread_id) {
					$alive = true;
					break;
				}
			}
			if (!$alive) {
				$link->query('DELETE FROM `locks` WHERE id = ' . $link->getObjectIdentifier($lock));
				$lock = null;
			}
		}
		return $lock;
	}

}
