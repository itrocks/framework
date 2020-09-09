<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao\Mysql\Lock;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View\Html\Template;

/**
 * The Counter class manages business-side counters : ie invoices numbers, etc.
 *
 * It deals with application-side locking in order that the next number has no jumps nor replicates
 *
 * @business
 * @display_order identifier, last_update, last_value, format
 * @feature Expert incremental counters configuration
 * @feature_menu Administration
 * @list identifier, last_value, last_update, format
 * @representative identifier
 */
class Counter
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @example 'F{YEAR}{ITRocks\Framework\User.current.login.0.upper}%04s'
	 * @mandatory
	 * @var string
	 */
	public $format = '{YEAR}%04d';

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @mandatory
	 * @user readonly
	 * @user_getter showIdentifier
	 * @var string
	 */
	public $identifier;

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $last_update;

	//----------------------------------------------------------------------------------- $last_value
	/**
	 * TODO output for the edit form, user_var for the list and the output... But isn't it the same ?
	 *
	 * @output string
	 * @user readonly
	 * @user_getter formatLastValue
	 * @user_var string
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
			$this->last_update = Date_Time::now();
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->showIdentifier();
	}

	//--------------------------------------------------------------------------------- autoDecrement
	/**
	 * Decrement the counter value
	 *
	 * @noinspection PhpUnused often used on business objects' @after_delete with $number
	 * @param $object        object|string object or class name
	 * @param $property_name string The name of the property containing the counter value
	 */
	public static function autoDecrement($object, $property_name = 'number')
	{
		$class_name = is_object($object) ? get_class($object) : $object;
		$class_name = Builder::current()->sourceClassName($class_name);
		$lock       = static::lock($class_name);
		/** @var $counter Counter */
		$counter = Dao::searchOne(['identifier' => $class_name], Counter::class);
		if ($counter) {
			$old_value = $counter->last_value;
			while (
				($counter->last_value > 0)
				&& !Dao::searchOne([$property_name => $counter->formatLastValue()], $class_name)
			) {
				$counter->last_value --;
			}
			if ($old_value !== $counter->last_value) {
				Dao::write($counter, Dao::only('last_value'));
			}
		}
		static::unlock($lock);
	}

	//------------------------------------------------------------------------------- formatLastValue
	/**
	 * Returns the last counter value, formatted
	 *
	 * @param $object object|null
	 * @return string
	 */
	public function formatLastValue($object = null)
	{
		$format = $this->format;
		if (strpos($format, '{') !== false) {
			$format = str_replace(
				['{YEAR4}', '{YEAR}', '{MONTH}', '{DAY}', '{HOUR}', '{MINUTE}', '{SECOND}'],
				[date('Y'), date('y'), date('m'), date('d'), date('H'), date('i'), date('s')],
				$format
			);
			if (is_object($object) && (strpos($format, '{') !== false)) {
				$format = (new Template($object))->parseVars($format);
			}
		}
		$this->last_update = Date_Time::now();
		return sprintf($format, $this->last_value);
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
		/** @var $dao Mysql\Link */
		$dao = Dao::current();
		$dao->begin();
		if (empty($identifier)) {
			$identifier = Builder::current()->sourceClassName(get_class($object));
		}
		$lock = static::lock($identifier);
		$counter = Dao::searchOne(['identifier' => $identifier], static::class)
			?: new static($identifier);
		$next_value = $counter->next($object);
		$dao->write(
			$counter,
			Dao::getObjectIdentifier($counter) ? Dao::only('last_update', 'last_value') : null
		);
		static::unlock($lock);
		$dao->commit();
		return $next_value;
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Locks database access for only one simultaneous access to the counter
	 * Don't forget to call unlock when done !
	 *
	 * @param $identifier string The identifier of the counter ; default is get_class($object)
	 * @return Lock
	 */
	protected static function lock($identifier)
	{
		/** @var $dao Mysql\Link */
		$dao        = Dao::current();
		$table_name = $dao->storeNameOf(__CLASS__);
		return $dao->lockRecord(
			$table_name,
			Dao::getObjectIdentifier(Dao::searchOne(['identifier' => $identifier], static::class)) ?: 0
		);
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
		$this->last_value ++;
		if ($this->resetValue()) {
			$this->last_value = 1;
		}
		return $this->formatLastValue($object);
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

	//-------------------------------------------------------------------------------- showIdentifier
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function showIdentifier()
	{
		if (class_exists($this->identifier)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			return Loc::tr(Display_Annotation::of(new Reflection_Class($this->identifier))->value);
		}
		return Loc::tr($this->identifier);
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * @param $lock Lock
	 */
	protected static function unlock(Lock $lock)
	{
		/** @var $dao Mysql\Link */
		$dao = Dao::current();
		$dao->unlock($lock);

	}

}
