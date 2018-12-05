<?php
namespace ITRocks\Framework\Dao\Cache;

use ITRocks\Framework\Tools\Date_Time;

/**
 * An object stored into the cache
 */
class Cached
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * When has it been accessed the last time
	 *
	 * @var Date_Time
	 */
	public $date;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The cached object
	 *
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 */
	public function __construct($object = null)
	{
		if (isset($object)) {
			$this->object = $object;
		}
		if (!isset($this->date)) {
			/** @noinspection PhpUnhandledExceptionInspection valid */
			$this->date = new Date_Time();
		}
	}

	//---------------------------------------------------------------------------------------- access
	/**
	 * Call this each time the object is accessed : its access date-time will be updated
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function access()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$this->date = new Date_Time();
	}

}
