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
	public Date_Time $date;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The cached object
	 *
	 * @var object
	 */
	public object $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object object|null
	 */
	public function __construct(object $object = null)
	{
		if (isset($object)) {
			$this->object = $object;
		}
		if (!isset($this->date)) {
			$this->date = new Date_Time();
		}
	}

	//---------------------------------------------------------------------------------------- access
	/**
	 * Call this each time the object is accessed : its access date-time will be updated
	 */
	public function access()
	{
		$this->date = new Date_Time();
	}

}
