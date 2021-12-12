<?php
namespace ITRocks\Framework\Feature\Import\Settings;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Import class
 */
class Import_Class
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------ $constants
	/**
	 * @var Reflection_Property_Value[] key is the name of the property
	 */
	public $constants = [];

	//-------------------------------------------------------------------------- $identify_properties
	/**
	 * @var Import_Property[] key is the name of the property
	 */
	public $identify_properties = [];

	//---------------------------------------------------------------------------- $ignore_properties
	/**
	 * @var Import_Property[] key is the name of the property
	 */
	public $ignore_properties = [];

	//------------------------------------------------------------------- $object_not_found_behaviour
	/**
	 * @values create_new_value, do_nothing, tell_it_and_stop_import
	 * @var string
	 */
	public $object_not_found_behaviour = 'do_nothing';

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Reflection_Property cache : the properties object matching write_properties
	 *
	 * @see $class_name
	 * @see $write_properties
	 * @var Reflection_Property[] key is the property path, starting from $this->class_name
	 */
	public $properties = [];

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * @var string[] key is the name of the property
	 */
	public $property_path;

	//--------------------------------------------------------------------------- $unknown_properties
	/**
	 * @var Import_Property[] key is the name of the property
	 */
	public $unknown_properties = [];

	//----------------------------------------------------------------------------- $write_properties
	/**
	 * @var Import_Property[] key is the name of the property
	 */
	public $write_properties = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name                 string
	 * @param $property_path              string[]
	 * @param $object_not_found_behaviour string create_new_value, do_nothing, tell_it_and_stop_import
	 */
	public function __construct(
		$class_name = null, array $property_path = null, $object_not_found_behaviour = null
	) {
		if (isset($class_name)) {
			$this->class_name = $class_name;
			$this->name       = Names::classToDisplay($class_name);
		}
		if (isset($object_not_found_behaviour)) {
			$this->object_not_found_behaviour = $object_not_found_behaviour;
		}
		if (isset($property_path)) {
			$this->property_path = $property_path;
		}
	}

	//----------------------------------------------------------------------------------- __serialize
	/**
	 * @return array
	 */
	public function __serialize() : array
	{
		$serialize = get_object_vars($this);
		if (isset($serialize['constants']) && is_array($serialize['constants'])) {
			foreach ($serialize['constants'] as $key => $value) {
				/** @var $value Reflection_Property_Value */
				$serialize['constants'][$key] = [
					'class'        => $value->class,
					'name'         => $value->name,
					'value'        => $value->value(),
					'final_object' => true
				];
			}
		}
		return $serialize;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getPropertyPathValue();
	}

	//--------------------------------------------------------------------------------- __unserialize
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $serialized array
	 */
	public function __unserialize(array $serialized)
	{
		foreach ($serialized as $key => $value) {
			if ($key === 'constants') {
				foreach ($value as $constant_key => $constant_value) {
					/** @noinspection PhpUnhandledExceptionInspection constants must be valid */
					$this->constants[$constant_key] = new Reflection_Property_Value(
						$constant_value['class'],
						$constant_value['name'],
						$constant_value['value'],
						$constant_value['final_object']
					);
				}
			}
			else {
				$this->$key = $value;
			}
		}
	}

	//----------------------------------------------------------------------------------- addConstant
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * Adds a new constant to the list : default value is empty and default name is random
	 */
	public function addConstant()
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class_name must be valid */
		foreach (
			(new Reflection_Class($this->class_name))->getProperties([T_EXTENDS, T_USE]) as $property
		) {
			if (!$property->isStatic() && !isset($this->constants[$property->name])) {
				/** @noinspection PhpUnhandledExceptionInspection $property is valid */
				$property              = new Reflection_Property_Value($property->class, $property->name);
				$property->final_class = $this->class_name;
				$this->constants[$property->name] = $property;
				break;
			}
		}
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * This cleanup method is called after loading and getting the current value
	 * in order to avoid crashes when some components of the setting disappeared in the meantime.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup()
	{
		$changes_count = 0;
		foreach (array_keys($this->identify_properties) as $property_name) {
			if (!property_exists($this->class_name, $property_name)) {
				$this->unknown_properties[$property_name] = $this->identify_properties[$property_name];
				unset($this->identify_properties[$property_name]);
				$changes_count ++;
			}
		}
		foreach (array_keys($this->ignore_properties) as $property_name) {
			if (!property_exists($this->class_name, $property_name)) {
				$this->unknown_properties[$property_name] = $this->ignore_properties[$property_name];
				unset($this->ignore_properties[$property_name]);
				$changes_count ++;
			}
		}
		foreach (array_keys($this->write_properties) as $property_name) {
			if (!property_exists($this->class_name, $property_name)) {
				$this->unknown_properties[$property_name] = $this->write_properties[$property_name];
				unset($this->write_properties[$property_name]);
				$changes_count ++;
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection $this->class_name must be valid */
		foreach (
			(new Reflection_Class($this->class_name))->getProperties([T_EXTENDS, T_USE]) as $property
		) {
			if (
				!isset($this->identify_properties  [$property->name])
				&& !isset($this->ignore_properties [$property->name])
				&& !isset($this->unknown_properties[$property->name])
				&& !isset($this->write_properties  [$property->name])
			) {
				$this->ignore_properties[$property->name] = new Import_Property(
					$this->class_name, $property->name
				);
				$changes_count ++;
			}
		}
		return $changes_count;
	}

	//------------------------------------------------------------------------------ getIdentifyValue
	/**
	 * @return string
	 */
	public function getIdentifyValue()
	{
		$properties = [];
		foreach ($this->identify_properties as $property) {
			$properties[$property->name] = $property->name;
		}
		return join(',', $properties);
	}

	//-------------------------------------------------------------------------------- getIgnoreValue
	/**
	 * @return string
	 */
	public function getIgnoreValue()
	{
		$properties = [];
		foreach ($this->ignore_properties as $property) {
			$properties[$property->name] = $property->name;
		}
		return join(',', $properties);
	}

	//-------------------------------------------------------------------------- getPropertyPathValue
	/**
	 * @return string
	 */
	public function getPropertyPathValue()
	{
		return $this->property_path ? (join(DOT, $this->property_path)) : $this->class_name;
	}

	//--------------------------------------------------------------------------------- getWriteValue
	/**
	 * @return string
	 */
	public function getWriteValue()
	{
		$properties = [];
		foreach ($this->write_properties as $property) {
			$properties[$property->name] = $property->name;
		}
		return join(',', $properties);
	}

	//-------------------------------------------------------------------------------- removeConstant
	/**
	 * Removes a constant from the list
	 *
	 * @param $property_name string
	 */
	public function removeConstant($property_name)
	{
		if (isset($this->constants[$property_name])) {
			unset($this->constants[$property_name]);
		}
	}

}
