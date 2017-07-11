<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View\Html\Template;

/**
 * The Counter class manages business-side counters : ie invoices numbers, etc.
 *
 * It deals with application-side locking in order that the next number has no jumps nor replicates
 *
 * @before_write updateLastUpdate
 * @business
 */
class Counter
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @example 'F{YEAR}{ITRocks\Framework\User.current.login.0.upper}%04s'
	 * @var string
	 */
	public $format = '{YEAR}%04s';

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	public $identifier;

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @default updateLastUpdate
	 * @link DateTime
	 * @var Date_Time
	 */
	public $last_update;

	//----------------------------------------------------------------------------------- $last_value
	/**
	 * @var integer
	 */
	public $last_value = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier string
	 */
	public function __construct($identifier = null)
	{
		if (isset($identifier)) {
			$this->identifier = $identifier;
		}
	}

	//------------------------------------------------------------------------------------- increment
	/**
	 * Load a counter linked to the class of an object from default data link and increment it
	 *
	 * @param $object     object The object to use to format the counter
	 * @param $identifier string The identifier of the counter ; default is get_class($object)
	 * @return string The new counter value
	 */
	public static function increment($object, $identifier = null)
	{
		/** @var $dao Link */
		$dao = Dao::current();
		echo date('I:s.u') . " begin...\n";
		$dao->begin();
		echo date('I:s.u') . " begun\n";
		if (empty($identifier)) {
			$identifier = Builder::current()->sourceClassName(get_class($object));
		}
		$table_name = $dao->storeNameOf(__CLASS__);
		echo date('I:s.u') . " lock record...\n";
		$lock       = $dao->lockRecord(
			$table_name,
			Dao::getObjectIdentifier(Dao::searchOne(['identifier' => $identifier], static::class)) ?: 1
		);
		echo date('I:s.u') . " record locked > sleep 1\n";
		sleep(1);
		echo date('I:s.u') . " search counter...\n";
		$counter = Dao::searchOne(['identifier' => $identifier], static::class)
			?: new Counter($identifier);
		echo date('I:s.u') . " counter found " . $counter->last_value . " > sleep 1\n";
		sleep(1);
		echo date('I:s.u') . " calculate next value...\n";
		$next_value = $counter->next($object);
		echo date('I:s.u') . " new value = $next_value > sleep 1\n";
		sleep(1);
		echo date('I:s.u') . " write counter...\n";
		$dao->write(
			$counter,
			Dao::getObjectIdentifier($counter) ? Dao::only('last_update', 'last_value') : null
		);
		echo date('I:s.u') . " counter written > sleep 1\n";
		sleep(1);
		echo date('I:s.u') . " unlock...\n";
		$dao->unlock($lock);
		echo date('I:s.u') . " unlocked\n";
		echo date('I:s.u') . " commit...\n";
		$dao->commit();
		echo date('I:s.u') . " committed\n";
		return $next_value;
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * Returns the next value for the counter, using format
	 * - This increments last_value
	 * - This resets the value if the day / month / year changed since the last_update date
	 * - This formats the value to get it "ready to print"
	 *
	 * @param $object object if set, use advanced formatting using object data ie {property.path}
	 * @return string
	 */
	public function next($object = null)
	{
		$next_value = ++$this->last_value;
		$format     = $this->format;
		if (strpos($format, '{') !== false) {
			if ($this->resetValue()) {
				$next_value = $this->last_value = 1;
			}
			$format = str_replace(
				['{YEAR4}', '{YEAR}', '{MONTH}', '{DAY}', '{HOUR}', '{MINUTE}', '{SECOND}'],
				[date('Y'), date('y'), date('m'), date('d'), date('H'), date('i'), date('s')],
				$format
			);
			if ($object && (strpos($format, '{') !== false)) {
				$format = (new Template($object))->parseVars($format);
			}
		}
		return sprintf($format, $next_value);
	}

	//------------------------------------------------------------------------------------ resetValue
	/**
	 * Checks if the value should be reset comparing today and the last_update date
	 * - if day changed and format contains {DAY}
	 * - if month changed and format contains {MONTH}
	 * - if year changed and format contains {YEAR4} or {YEAR}
	 *
	 * @return boolean true if the value should be reset
	 */
	public function resetValue()
	{
		$date   = date('Y-m-d');
		$format = $this->format;
		$last   = $this->last_update;
		return
			((strpos($format, '{DAY}') !== false) && ($date > $last->format('Y-m-d')))
			|| ((strpos($format, '{MONTH}') !== false) && (substr($date, 0, 7) > $last->format('Y-m')))
			|| ((strpos($format, '{YEAR') !== false) && (substr($date, 0, 4) > $last->format('Y')));
	}

	//------------------------------------------------------------------------------ updateLastUpdate
	public function updateLastUpdate()
	{
		$this->last_update = Date_Time::now();
	}

}
