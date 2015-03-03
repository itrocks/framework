<?php
namespace SAF\Framework\Mapper;

/**
 * Property path mapper methods for objects
 */
class Object_Property_Path extends Class_Property_Path
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object object
	 */
	public function __construct($object)
	{
		parent::__construct(get_class($object));
		$this->object = $object;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets the property value, following the given path
	 *
	 * @param $property_path string
	 * @return mixed
	 * @todo make this work with Class_Name->property_name reverse links
	 */
	public function getValue($property_path)
	{
		$object = $this->object;
		foreach (explode(DOT, $property_path) as $property_name) {
			if (is_object($object)) {
				$object = $object->$property_name;
			}
			else {
				return null;
			}
		}
		return $object;
	}

}
