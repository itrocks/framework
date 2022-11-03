<?php
namespace ITRocks\Framework\Mapper;

/**
 * Property path mapper methods for objects
 */
class Object_Property_Path extends Class_Property_Path
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private object $object;

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
	public function getValue(string $property_path) : mixed
	{
		$object = $this->object;
		foreach (explode(DOT, $property_path) as $property_name) {
			if (!is_object($object)) {
				return null;
			}
			$object = $object->$property_name;
		}
		return $object;
	}

}
