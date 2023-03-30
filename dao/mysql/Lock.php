<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;

#[Class_\Store]
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
	#[Store(false)]
	public int $count = 1;

	//----------------------------------------------------------------------------------- $identifier
	public int $identifier;

	//------------------------------------------------------------------------------ $mysql_thread_id
	/** Mysql thread identifier */
	public int $mysql_thread_id;

	//-------------------------------------------------------------------------------------- $options
	/** @var string[] */
	#[Values(self::class)]
	public array $options;

	//--------------------------------------------------------------------------- $process_identifier
	/**
	 * PHP running process identifier
	 * For information / debugging purpose
	 */
	public int $process_identifier;

	//----------------------------------------------------------------------------------- $table_name
	public string $table_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : initializes data
	 *
	 * @param $table_name string|null
	 * @param $identifier integer|null
	 * @param $options    string[]|null @values static::const
	 */
	public function __construct(
		string $table_name = null, int $identifier = null, array $options = null
	) {
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
	 */
	public static function get(
		string $table_name, int|string $record_identifier, Link $link = null
	) : ?Lock
	{
		if (!$link) {
			$link = Dao::current();
		}
		/** @var $locks Lock[] */
		$locks = $link->query(
			'SELECT * FROM `locks`' . LF
				. ' WHERE `table_name` = ' . DQ . $table_name . DQ
				. ' AND `identifier` = ' . $record_identifier,
			Lock::class
		);
		$lock = reset($locks);
		if (!$lock) {
			return $lock;
		}
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
			return null;
		}
		return $lock;
	}

}
