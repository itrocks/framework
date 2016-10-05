<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Tools\Names;

/**
 * The property name into the virtual link class that contains foreign object
 *
 * this is a virtual property name
 */
class Foreignlink_Annotation extends Documented_Type_Annotation
	implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link = $property->getAnnotation('link')->value;
			$possibles = null;
			if ($link == Link_Annotation::COLLECTION) {
				$possibles = $this->defaultCollection($property);
			}
			elseif ($link == Link_Annotation::MAP) {
				$possibles = $this->defaultMap($property);
			}
			elseif ($link == Link_Annotation::OBJECT) {
				$possibles = $this->defaultObject($property);
			}
			if (is_array($possibles) && count($possibles) == 1) {
				$this->value = reset($possibles);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultCollection(Reflection_Property $property)
	{
		return [$property->getName()];
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultMap(Reflection_Property $property)
	{
		return [Names::setToSingle($property->getName())];
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultObject(Reflection_Property $property)
	{
		return [$property->getName()];
	}

}
