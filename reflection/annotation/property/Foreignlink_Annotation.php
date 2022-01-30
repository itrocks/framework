<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;

/**
 * The property name into the virtual link class that contains foreign object
 *
 * this is a virtual property name
 */
class Foreignlink_Annotation extends Documented_Type_Annotation
	implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'foreignlink';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link      = Link_Annotation::of($property);
			$possibles = null;
			if ($link->isCollection()) {
				$possibles = $this->defaultCollection($property);
			}
			elseif ($link->isMap()) {
				$possibles = $this->defaultMap($property);
			}
			elseif ($link->isObject()) {
				$possibles = $this->defaultObject($property);
			}
			if (is_array($possibles) && (count($possibles) === 1)) {
				$this->value = reset($possibles);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultCollection(Reflection_Property $property) : array
	{
		return [$property->getName()];
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultMap(Reflection_Property $property) : array
	{
		return [Names::setToSingle($property->getName())];
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultObject(Reflection_Property $property) : array
	{
		return [$property->getName()];
	}

}
