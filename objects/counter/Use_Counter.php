<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Objects\Counter;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Number\Automatic;
use ITRocks\Framework\View\View_Exception;

/**
 * Apply this trait to classes that use a counter
 *
 * You have to set two annotations :
 * - @counter_property property_name (only needed if property_name is not 'number')
 * - @override property_name @calculated @mandatory false @user readonly too
 *
 * @after_write incrementCounterPropertyValue
 * @example Has_Number\Automatic is a great example about how to use this
 * @see Automatic
 */
trait Use_Counter
{

	//----------------------------------------------------------------- incrementCounterPropertyValue
	/**
	 * This calculates $counter_property if it is empty, using the Counter identified by class name
	 * and counter property name (if not empty)
	 *
	 * The job is done after the document has been written : if any problem occurs, we should not
	 * have incremented the counter, and there is more luck to have problem before than after write
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnused @after_write
	 * @param $link Data_Link
	 * @throws View_Exception
	 */
	public function incrementCounterPropertyValue(Data_Link $link)
	{
		static $increments = [];
		/** @noinspection PhpUnhandledExceptionInspection object */
		$property_name = $this->incrementPropertyName();
		if ($this->$property_name) {
			return;
		}
		$identifier = $link->getObjectIdentifier($this);
		if (isset($increments[get_class($this)][$identifier][$property_name])) {
			$this->$property_name = $increments[get_class($this)][$identifier][$property_name];
			return;
		}
		if (($link instanceof Identifier_Map) && $identifier) {
			/** @var $counter_class_name Counter|string */
			$counter_class_name = Builder::className(Counter::class);
			$counter_value      = $counter_class_name::increment(
				$this, $this->incrementIdentifier($property_name)
			);
			$increments[get_class($this)][$identifier][$property_name] = $counter_value;
			$this->$property_name = $counter_value;
			$link->write($this, Dao::only($property_name));
		}
		else {
			throw new View_Exception(Loc::tr($property_name) . ' : ' . Loc::tr('mandatory'));
		}
	}

	//--------------------------------------------------------------------------- incrementIdentifier
	/**
	 * @param $property_name string|null internally used for optimisation purpose
	 * @return ?string
	 */
	protected function incrementIdentifier(string $property_name = null) : ?string
	{
		if (!isset($property_name)) {
			$property_name = $this->incrementPropertyName();
		}
		return ($property_name === 'number')
			? Counter::incrementIdentifier($this)
			: Builder::current()->sourceClassName(get_class($this)) . DOT . $property_name;
	}

	//------------------------------------------------------------------------- incrementPropertyName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	protected function incrementPropertyName() : string
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		return (new Reflection_Class($this))->getAnnotation('counter_property')->value ?: 'number';
	}
	
}
