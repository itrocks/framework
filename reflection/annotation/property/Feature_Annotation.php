<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;

/**
 * Property feature annotation : a feature based on changes on the property annotations
 *
 * Do not use $value directly : this may not have been parsed from a string to string[]
 * Please prefer the use of the values() method, always
 *
 * @example @feature featureName [A short description] @annotation1 new_value [@etc]
 * A property can embed several @feature annotations.
 */
class Feature_Annotation
	extends List_Annotation
	implements Multiple_Annotation, Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature';

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public string $path;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public string $title;

	//------------------------------------------------------------------------------ $value_as_string
	/**
	 * @var string
	 */
	private string $value_as_string;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Feature_Annotation constructor
	 *
	 * @noinspection PhpMissingParentConstructorInspection This does all the work itself
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		// path : The/Full/Class/Name/featureName
		$position   = strpos($value, SP);
		$this->path = substr($value, 0, $position);
		if (!strpos($this->path, SL)) {
			$this->path = str_replace(BS, SL, $property->getFinalClassName()) . SL . $this->path;
		}
		$value = trim(substr($value, $position + 1));

		// feature title (a text readable by a human)
		if ($position = strpos($value, AT)) {
			$this->title = trim(substr($value, 0, $position));
			$value       = substr($value, $position);
		}

		// property override is stored as a raw string (fastest constructor)
		// use values() to parse this and get $this->value as an array
		$this->value_as_string = str_replace(SP . AT, LF . '* @', substr($value, 1));
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Gets the name of the class associated with the feature
	 *
	 * @example Full\Class_Name
	 * @return string
	 */
	public function getClassName() : string
	{
		return Names::pathToClass(lLastParse($this->path, SL));
	}

	//-------------------------------------------------------------------------------- getFeatureName
	/**
	 * Gets the name of the feature
	 *
	 * @example featureName
	 * @return string
	 */
	public function getFeatureName() : string
	{
		return rLastParse($this->path, SL);
	}

	//---------------------------------------------------------------------------------------- values
	/**
	 * Return values : an array that associate annotation_name and its raw value as a string
	 *
	 * @return string[]
	 */
	public function values() : array
	{
		if (empty($this->value)) {
			$this->value = [];
			foreach (explode(LF . '* @', $this->value_as_string) as $override_annotation) {
				if (strpos($override_annotation, SP)) {
					list($annotation_name, $annotation_value) = explode(SP, $override_annotation, 2);
				}
				else {
					$annotation_name  = $override_annotation;
					$annotation_value = '';
				}
				$this->value[$annotation_name] = trim($annotation_value);
			}
		}
		return $this->value;
	}

}
