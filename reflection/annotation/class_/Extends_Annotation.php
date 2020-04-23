<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Options_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * This must be used for traits that are designed to extend a given class
 * Builder will use it to sort built classes
 */
class Extends_Annotation extends Template\List_Annotation implements Do_Not_Inherit
{
	use Options_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'extends';

	//---------------------------------------------------------------------------------------- STRICT
	/**
	 * strict option : if set, this annotation is used for Builder's extends information only, not
	 * for trait installation automatic feature build application
	 */
	const STRICT = 'strict';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		$this->build_class_name = false;
		$this->constructOptions($value);
		parent::__construct($value);
	}

	//-------------------------------------------------------------------------------------- allNotOf
	/**
	 * Get all annotations of a reflection object (for multiple annotations)
	 *
	 * @param $reflection_object Reflection
	 * @param $option            string filter to get only extends that use this option
	 * @return static[]
	 * @see Annoted::getAnnotation
	 */
	public static function allNotOf(Reflection $reflection_object, $option)
	{
		$annotations = [];
		foreach (parent::allOf($reflection_object) as $annotation) {
			if (!$annotation->hasOption($option)) {
				$annotations[] = $annotation;
			}
		}
		return $annotations;
	}

	//----------------------------------------------------------------------------------------- allOf
	/**
	 * Get all annotations of a reflection object (for multiple annotations)
	 *
	 * @param $reflection_object Reflection
	 * @param $option            string filter to get only extends that use this option
	 * @return static[]
	 * @see Annoted::getAnnotation
	 */
	public static function allOf(Reflection $reflection_object, $option = null)
	{
		$annotations = parent::allOf($reflection_object);
		if (!$annotations || !$option) {
			return $annotations;
		}
		$result = [];
		foreach ($annotations as $annotation) {
			if ($annotation->hasOption($option)) {
				$result[] = $annotation;
			}
		}
		return $result;
	}

	//----------------------------------------------------------------------------------------- notOf
	/**
	 * @param $reflection_object Reflection
	 * @param $option            string filter to get the first extends that uses this option
	 * @return static
	 * @see Annoted::getAnnotation
	 */
	public static function notOf(Reflection $reflection_object, $option)
	{
		$annotations = static::allNotOf($reflection_object, $option);
		return reset($annotations) ?: new static(null);
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $reflection_object Reflection
	 * @param $option            string filter to get the first extends that uses this option
	 * @return static
	 * @see Annoted::getAnnotation
	 */
	public static function of(Reflection $reflection_object, $option = null)
	{
		if ( !$option) {
			return parent::of($reflection_object);
		}
		$annotations = static::allOf($reflection_object, $option);
		return reset($annotations) ?: new static(null);
	}

}
