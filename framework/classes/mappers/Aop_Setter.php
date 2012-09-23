<?php
namespace SAF\Framework;

require_once "framework/classes/toolbox/Aop.php";
require_once "framework/classes/reflection/Reflection_Property.php";

abstract class Aop_Setter extends Aop
{

	//---------------------------------------------------------------------- registerPropertiesSetter
	/**
	 * Auto-register properties setters for a given class name
	 *
	 * Call this each time a class is declared (ie at end of Autoloader->autoload()) to automatically register AOP special getters for object properties.
	 * This uses the property @setter annotation to know what getter to use.
	 * Specific Aop::getMethod() getters are allowed shortcuts for SAF\Framework\Aop_Getter::getMethod().
	 *
	 * @todo check phpdoc
	 * @param string $class_name
	 */
	public static function registerPropertiesSetters($class_name)
	{
		parent::registerProperties($class_name, "setter", "write");
	}

}
