<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\List_;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Mutex;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html\Template;
use ReflectionException;

/**
 * The Counter class manages business-side counters : ie invoices numbers, etc.
 *
 * It deals with application-side locking in order that the next number has no jumps nor replicates
 *
 * @feature Expert incremental counters configuration
 * @feature_menu Administration
 */
#[Display_Order('identifier', 'last_update', 'last_value', 'format')]
#[List_('identifier', 'last_value', 'last_update', 'format')]
#[Representative('identifier')]
#[Store]
class Counter
{

	//--------------------------------------------------------------------------------------- $format
	/** @example 'F{YEAR}{ITRocks\Framework\User.current.login.0.upper}%04s' */
	#[Mandatory]
	public string $format = '{YEAR}%04d';

	//----------------------------------------------------------------------------------- $identifier
	/** @user_getter showIdentifier */
	#[Mandatory, User(User::ADD_ONLY)]
	public string $identifier;

	//---------------------------------------------------------------------------------- $last_update
	#[User(User::READONLY)]
	public Date_Time|string $last_update;

	//----------------------------------------------------------------------------------- $last_value
	/**
	 * TODO output for the edit form, user_var for the list and the output... But isn't it the same ?
	 *
	 * @output string
	 * @user_getter formatLastValue
	 * @user_var string
	 */
	#[User(User::READONLY)]
	public int $last_value = 0;

	//----------------------------------------------------------------------------------- __construct
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
	/** @throws ReflectionException */
	public function __toString() : string
	{
		return $this->showIdentifier();
	}

	//--------------------------------------------------------------------------------- autoDecrement
	/**
	 * Decrement the counter value
	 *
	 * @noinspection PhpUnused often used on business objects' @after_delete with $number
	 * @param $object        object|string Object or class name
	 * @param $property_name string        The name of the property containing the counter value
	 */
	public static function autoDecrement(object|string $object, string $property_name = 'number')
		: void
	{
		$class_name = is_object($object) ? get_class($object) : $object;
		$class_name = Builder::current()->sourceClassName($class_name);
		$mutex      = static::lock($class_name);
		/** @var $counter Counter */
		$counter = Dao::searchOne(['identifier' => $class_name], Counter::class);
		$counter?->decrement($class_name, $property_name);
		static::unlock($mutex);
	}

	//------------------------------------------------------------------------------------- decrement
	protected function decrement(string $class_name, string $property_name) : void
	{
		$old_value = $this->last_value;
		while (
			($this->last_value > 0)
			&& !Dao::searchOne([$property_name => $this->formatLastValue()], $class_name)
		) {
			$this->last_value --;
		}
		if ($old_value !== $this->last_value) {
			Dao::write($this, Dao::only('last_value'));
		}
	}

	//------------------------------------------------------------------------------- formatLastValue
	/** Returns the last counter value, formatted */
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
					'{SECOND}' => $date->format('s'),
					'{RAND1}'  => rand(0, 9),
					'{RAND2}'  => sprintf('%02s', rand(0, 99)),
					'{RAND3}'  => sprintf('%03s', rand(0, 999)),
					'{RAND4}'  => sprintf('%04s', rand(0, 9999))
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object     object      The object to use to format the counter
	 * @param $identifier string|null The identifier of the counter ; default is get_class($object)
	 * @return string The new counter value
	 */
	public static function increment(object $object, string $identifier = null) : string
	{
		/** @var $dao Mysql\Link */
		$dao = Dao::current();
		$dao->begin();
		if (empty($identifier)) {
			$identifier = static::incrementIdentifier($object);
		}
		$mutex   = static::lock($identifier);
		/** @noinspection PhpUnhandledExceptionInspection class */
		$counter = Dao::searchOne(['identifier' => $identifier], static::class)
			?: Builder::create(Counter::class, [$identifier]);
		$next_value = $counter->next($object);
		$dao->write(
			$counter,
			Dao::getObjectIdentifier($counter) ? Dao::only('last_update', 'last_value') : []
		);
		static::unlock($mutex);
		$dao->commit();
		return $next_value;
	}

	//--------------------------------------------------------------------------- incrementIdentifier
	public static function incrementIdentifier(object $object) : string
	{
		return Builder::current()->sourceClassName(get_class($object));
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

	//---------------------------------------------------------------------------------- numberLength
	/**
	 * @example format = '{MONTH}%03d' => 3
	 * @example format = '{YEAR}%3d'   => 1
	 * @return integer The counter number initial length
	 */
	public function numberLength() : int
	{
		preg_match('/%(\d+)d/', $this->format, $match);
		return ($match && str_starts_with($match[1], '0')) ? $match[1] : 1;
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
	 * @throws ReflectionException
	 */
	public function showIdentifier() : string
	{
		$identifier = $this->identifier ?? '';
		if (!$identifier) {
			return '';
		}
		if (!str_contains($identifier, BS)) {
			return Loc::tr($identifier);
		}
		if (str_contains($identifier, '[')) {
			$identifier_parts = explode('[', $identifier);
			$class_name       = array_shift($identifier_parts);
		}
		else {
			$identifier_parts = [];
			$class_name       = lParse($identifier, '[');
		}
		$class_name = lParse($class_name, DOT);
		if (!class_exists($class_name)) {
			return $identifier;
		}
		$class_display = Loc::tr(Names::classToDisplay($class_name));
		if (!$identifier_parts) {
			return $class_display;
		}
		$identifiers = [];
		foreach ($identifier_parts as $identifier_part) {
			[$property_name, $property_identifier] = explode('=', $identifier_part);
			$property_identifier                   = substr($property_identifier, 0, -1);
			$property                              = new Reflection_Property($class_name, $property_name);
			$property_class_name                   = $property->getType()->asString();
			if (class_exists($property_class_name)) {
				$identifiers[$property_name] = Dao::read($property_identifier, $property_class_name);
			}
		}
		return $class_display . SP . join(SP, $identifiers);
	}

	//---------------------------------------------------------------------------------------- unlock
	protected static function unlock(Mutex $mutex) : void
	{
		$mutex->unlock();
	}

}
