<?php
namespace SAF\Framework;

/**
 * The property name into the virtual link class that contains foreign object
 *
 * this is a virtual property name
 */
class Foreignlink_Annotation extends Documented_Type_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link = $reflection_property->getAnnotation("link")->value;
			$possibles = null;
			if ($link == "Collection") {
				$possibles = $this->defaultCollection($reflection_property);
			}
			elseif ($link == "Map") {
				$possibles = $this->defaultMap($reflection_property);
			}
			elseif ($link == "Object") {
				$possibles = $this->defaultObject($reflection_property);
			}
			if (is_array($possibles) && count($possibles) == 1) {
				$this->value = reset($possibles);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $reflection_property Reflection_Property
	 * @return string[]
	 */
	private function defaultCollection(Reflection_Property $reflection_property)
	{
		return array($reflection_property->name);
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $reflection_property Reflection_Property
	 * @return string[]
	 */
	private function defaultMap(Reflection_Property $reflection_property)
	{
		return array(Names::ClassToProperty(Names::setToClass(
				Names::propertyToClass($reflection_property->name), false
		)));
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param Reflection_Property $reflection_property
	 * @return string[]
	 */
	private function defaultObject(Reflection_Property $reflection_property)
	{
		return array($reflection_property->name);
	}

}
