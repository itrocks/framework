<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

/**
 * Filters property annotation
 */
class Filters_Annotation extends List_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $final_object object The referent object where to take values from (property path root)
	 * @return string[]
	 */
	public function parse($final_object)
	{
		$filters        = [];
		$filters_values = $this->values();
		if ($filters_values) {
			$property   = $this->property;
			$class_name = $property->getFinalClassName();
			foreach ($filters_values as $filter) {
				if (strpos($filter, '=')) {
					[$filter, $filter_value_name] = explode('=', $filter);
					$filter = trim($filter);
					$filter_value_name = trim($filter_value_name);
				} else {
					$filter_value_name = $filter;
				}
				if (
					is_numeric($filter_value_name)
					|| (
						in_array(substr($filter_value_name, 0, 1), [DQ, Q])
						&& (substr($filter_value_name, 0, 1) === substr($filter_value_name, -1))
					)
				) {
					$filters[$filter] = $filter_value_name;
				}
				elseif (property_exists($class_name, $filter_value_name)) {
					/** @noinspection PhpUnhandledExceptionInspection property_exists */
					$property = new Reflection\Reflection_Property($class_name, $filter_value_name);
					$filters[$filter] = $property->pathAsField(true);
				}
				elseif (
					method_exists($class_name, $filter_value_name)
					&& ($property instanceof Reflection_Property_Value)
				) {
					$filters[$filter] = $final_object->$filter_value_name();
				}
				else {
					user_error(
						'Not a method or property ' . $class_name . '::' . $filter_value_name, E_USER_ERROR
					);
				}
			}
		}
		return $filters;
	}

}
