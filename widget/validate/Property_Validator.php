<?php
namespace SAF\Framework\Widget\Validate;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Annotation\Template;
use SAF\Framework\Widget\Validate\Property\Property_Validate_Annotation;

/**
 * The object validator links validation processes to objects properties values
 */
class Property_Validator
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//--------------------------------------------------------------------------------------- $report
	/**
	 * The report is made of validate annotations that have been validated or not
	 *
	 * @var Template\Property_Validator[]|Property_Validate_Annotation[]
	 */
	public $report = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 */
	public function __construct(Reflection_Property $property)
	{
		$this->property = $property;
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Returns true if the property value into the object is validated into the object context
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object)
	{
		$validated = true;
		foreach ($this->property->getAnnotations() as $annotation_name => $annotation) {
			if ($annotation instanceof Template\Validator) {
				/** @var $annotation Template\Property_Validator|Property_Validate_Annotation */
				$validated_annotation = $annotation->validate($object);
				if ($annotation->valid === true)  $annotation->valid = Validate::INFORMATION;
				if ($annotation->valid === false) $annotation->valid = Validate::ERROR;
				if (is_null($annotation->valid)) {
					return null;
				}
				else {
					if (!$validated_annotation) {
						$this->report[] = $annotation;
					}
					$validated = $validated && $validated_annotation;
				}
			}
		}
		return $validated;
	}

}
