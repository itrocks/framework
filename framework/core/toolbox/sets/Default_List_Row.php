<?php
namespace SAF\Framework;

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
	 * @var multitype:string
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($class_name, $object, $values)
	{
		$this->class_name = $class_name;
		$this->object = $object;
		$this->values = $values;
	}

	//----------------------------------------------------------------------------------------- count
	public function count()
	{
		return count($this->values);
	}

	//---------------------------------------------------------------------------------- formatValues
	/**
	 * Return values ready for display
	 *
	 * @return multitype:string
	 */
	public function formatValues()
	{
		$values = array();
		static $cache = array();
		foreach ($this->values as $property_path => $value) {
			$property_view = isset($cache[$this->class_name][$property_path])
				? $cache[$this->class_name][$property_path]
				: (
					$cache[$this->class_name][$property_path] = new Reflection_Property_View(
						Reflection_Property::getInstanceOf($this->class_name, $property_path)
					)
				);
			$values[$property_path] = $property_view->formatValue($value);
		}
		return $values;
	}

	//---------------------------------------------------------------------------------- getClassName
	public function getClassName()
	{
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------- getObject
	public function getObject()
	{
		return Getter::getObject($this->object, $this->class_name);
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue($property)
	{
		return $this->values[$property];
	}

	//-------------------------------------------------------------------------------------------- id
	public function id()
	{
		return $this->object;
	}

}
