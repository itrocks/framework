<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\View;

/**
 * The list row class for Default_List_Data
 */
class Default_List_Row implements List_Row
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $object     object
	 * @param $values     string[]
	 */
	public function __construct($class_name, $object, array $values)
	{
		$this->class_name = $class_name;
		$this->object     = $object;
		$this->values     = $values;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count()
	{
		return count($this->values);
	}

	//---------------------------------------------------------------------------------- formatValues
	/**
	 * Return values ready for display
	 *
	 * @return string[]
	 */
	public function formatValues()
	{
		$values = [];
		static $cache = [];
		foreach ($this->values as $property_path => $value) {
			$property_view = isset($cache[$this->class_name][$property_path])
				? $cache[$this->class_name][$property_path]
				: (
					$cache[$this->class_name][$property_path] = new Reflection_Property_View(
						new Reflection_Property($this->class_name, $property_path)
					)
				);
			$values[$property_path] = $property_view->formatValue($value);
		}
		return $values;
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @return object
	 */
	public function getObject()
	{
		Getter::getObject($this->object, $this->class_name);
		return $this->object;
	}

	//--------------------------------------------------------------------------------- getOutputLink
	/**
	 * Returns link to the output feature for the object
	 *
	 * @return string
	 */
	public function getOutputLink()
	{
		return View::link(
			is_object($this->object) ? $this->object : [$this->class_name, $this->object]
		);
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @param string $property
	 * @return mixed
	 */
	public function getValue($property)
	{
		return $this->values[$property];
	}

	//------------------------------------------------------------------------------------- getValues
	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * @return mixed
	 */
	public function id()
	{
		return Dao::getObjectIdentifier($this->object);
	}

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * @param $property string the path of the property
	 * @param $value    mixed the new value
	 */
	public function setValue($property, $value)
	{
		$this->values[$property] = $value;
	}

}
