<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Mutex;
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
	 * @user add_only
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
	 * @param $identifier string|null
	 */
	public function __construct(string $identifier = null)
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
	public function __toString() : string
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
	public static function autoDecrement(object|string $object, string $property_name = 'number')
	{
		$class_name = is_object($object) ? get_class($object) : $object;
		$class_name = Builder::current()->sourceClassName($class_name);
		$mutex      = static::lock($class_name);
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
		static::unlock($mutex);
	}

	//------------------------------------------------------------------------------- formatLastValue
	/**
	 * Returns the last counter value, formatted
	 *
	 * @param $object object|null
	 * @return string
	 */
	public function formatLastValue(object $object = null) : string
	{
		$format = $this->format;
		$date   = ($object && property_exists($object, 'date')) ? $object->date : Date_Time::now();
		$date   = $date->latest($this->last_update);
		if (str_contains($format, '{')) {
			$format = strReplace(
				[
					'{YEAR4}'  => $date->format('Y'),
					'{YEAR}'   => $date->format('y'),
					'{MONTH}'  => $date->format('m'),
					'{DAY}'    => $date->format('d'),
					'{HOUR}'   => $date->format('H'),
					'{MINUTE}' => $date->format('i'),
					'{SECOND}' => $date->format('s')
				],
				$format
			);
			if ($object && str_contains($format, '{')) {
				$format = (new Template($object))->parseVars($format);
			}
		}
		$this->last_update = $date;
		return sprintf($format, $this->last_value);
	}

	//------------------------------------------------------------------------------------- increment
	/**
	 * Load a counter linked to the class of an object from default data link and increment it
	 *
	 * @param $object     object The object to use to format the counter
	 * @param $identifier string|null The identifier of the counter ; default is get_class($object)
	 * @return string The new counter value
	 */
	public static function increment(object $object, string $identifier = null) : string
	{
		/** @var $dao Mysql\Link */
		$dao = Dao::current();
		$dao->begin();
		if (empty($identifier)) {
			$identifier = Builder::current()->sourceClassName(get_class($object));
		}
		$mutex   = static::lock($identifier);
		$counter = Dao::searchOne(['identifier' => $identifier], static::class)
			?: new static($identifier);
		$next_value = $counter->next($object);
		$dao->write(
			$counter,
			Dao::getObjectIdentifier($counter) ? Dao::only('last_update', 'last_value') : null
		);
		static::unlock($mutex);
		$dao->commit();
		return $next_value;
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Locks database access for only one simultaneous access to the counter
	 * Don't forget to call unlock when done !
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $identifier string The identifier of the counter ; default is get_class($object)
	 * @return Mutex
	 */
	protected static function lock(string $identifier) : Mutex
	{
		$identifier = strUri($identifier);
		$table_name = Dao::storeNameOf(__CLASS__);
		/** @noinspection PhpUnhandledExceptionInspection class */
		$mutex = Builder::create(Mutex::class, [$table_name . DOT . $identifier]);
		$mutex->lock();
		return $mutex;
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * Returns the next value for the counter, using format
	 * - This increments last_value
	 * - This resets the value if the day / month / year changed since the last_update date
	 * - This formats the value to get it "ready to print"
	 *
	 * @param $object object|null if set, use advanced formatting using object data ie {property.path}
	 * @return string
	 */
	public function next(object $object = null) : string
	{
		$this->last_value ++;
		if ($this->resetValue($object)) {
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
	 * @param $object object|null An object that can have a reference date (instead of now)
	 * @return boolean true if the value should be reset
	 */
	public function resetValue(object $object = null) : bool
	{
		$date   = ($object && property_exists($object, 'date')) ? $object->date : Date_Time::now();
		$date   = $date->latest($this->last_update);
		$date   = $date->format('Y-m-d');
		$format = $this->format;
		$last   = $this->last_update;
		return
			(str_contains($format, '{DAY}') && ($date > $last->format('Y-m-d')))
			|| (str_contains($format, '{MONTH}') && (substr($date, 0, 7) > $last->format('Y-m')))
			|| (str_contains($format, '{YEAR') && (substr($date, 0, 4) > $last->format('Y')));
	}

	//-------------------------------------------------------------------------------- showIdentifier
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function showIdentifier() : string
	{
		if (!$this->identifier) {
			return '';
		}
		if (class_exists($this->identifier)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			return Loc::tr(Display_Annotation::of(new Reflection_Class($this->identifier))->value);
		}
		return Loc::tr($this->identifier);
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * @param $mutex Mutex
	 */
	protected static function unlock(Mutex $mutex)
	{
		$mutex->unlock();

	}

}
