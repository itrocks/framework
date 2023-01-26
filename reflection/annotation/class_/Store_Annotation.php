<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * Store annotation : @store [false]
 *
 * Identifies a class that may be stored using data links
 * When this annotation is set, this enables simplified / implicit use of @link
 * ie "@link Object" and @link DateTime" will be set automatically
 * ie "@link All", "@link Collection", "@link Map" will be distinguished using @all and @component
 *
 * The default value for @store is false : you should set it for all your stored object classes
 * If @store_annotation is set and @store is not defined, @store will be true
 */
class Store_Annotation extends Boolean_Annotation implements Class_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'store';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value bool|null|string
	 * @param $class Reflection_Class
	 */
	public function __construct(bool|null|string $value, Reflection_Class $class)
	{
		if (is_null($value) && !Store_Name_Annotation::of($class)->calculated) {
			$value = true;
		}
		parent::__construct($value);
	}

}
