<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Objects\Counter;

/**
 * @extends Counter
 * @feature Random counters
 */
trait Random
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @conditions random=true
	 * @var string
	 */
	public string $property_name = 'number';

	//--------------------------------------------------------------------------------------- $random
	/**
	 * @var boolean
	 */
	public bool $random = false;

	//------------------------------------------------------------------------------------- decrement
	/**
	 * Random counters never decrement
	 *
	 * @param $class_name    string
	 * @param $property_name string
	 */
	protected function decrement(string $class_name, string $property_name)
	{
		if ($this->random) {
			return;
		}
		/** @noinspection PhpUndefinedClassInspection @extends Counter */
		/** @see Counter::decrement */
		parent::decrement($class_name, $property_name);
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
		/** @var $this Counter|static */
		if (!$this->random) {
			/** @noinspection PhpUndefinedClassInspection @extends Counter */
			/** @see Counter::next */
			return parent::next($object);
		}
		$length    = $this->numberLength();
		$max_value = intval(str_repeat(9, $length));
		if ($object) {
			$count = Dao::count([], get_class($object));
			if ($count >= ($max_value * 4 / 5)) {
				$length    = strlen($count);
				$max_value = intval(str_repeat(9, $length));
			}
		}
		do {
			$this->last_value = rand(1, $max_value);
			$formatted_value  = $this->formatLastValue();
		}
		while (Dao::searchOne([$this->property_name => $formatted_value], get_class($object)));
		return $formatted_value;
	}

}
