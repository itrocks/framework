<?php
namespace SAF\Framework\Objects;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\View\Html\Template;

/**
 * The Counter class manages business-side counters : ie invoices numbers, etc.
 *
 * It deals with application-side locking in order that the next number has no jumps nor replicates
 *
 * @business
 */
class Counter
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @example 'F{YEAR}{SAF\Framework\User.current.login.0.upper}%04s'
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
		if (!isset($this->last_update)) {
			$this->last_update = new Date_Time();
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
		Dao::begin();
		if (empty($identifier)) {
			$identifier = Builder::current()->sourceClassName(get_class($object));
		}
		$counter = Dao::searchOne(['identifier' => $identifier], get_called_class())
			?: new Counter($identifier);
		$next_value = $counter->next($object);
		Dao::write($counter);
		Dao::commit();
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
		$format = $this->format;
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

}
