<?php
namespace SAF\Framework;

class Instantiator
{

	private static $substitutions = array();

	//-------------------------------------------------------------------------------------- getClass
	public static function getClass($object_class)
	{
		return isset(Instantiator::$substitutions[$object_class])
			? Instantiator::$substitutions[$object_class]
			: $object_class;
	}

	//----------------------------------------------------------------------------------- newInstance
	public static function newInstance($object_class)
	{
		$object_class = Instantiator::getClass($object_class);
		return new $object_class();
	}

	//-------------------------------------------------------------------------------------- register
	public static function register($object_class, $herited_class)
	{
		$reflection_class = new Reflection_Class($herited_class);
		if ($reflection_class->isSubClassOf($object_class)) {
			if (!Instantiator::$substitutions[$object_class]) {
				Instantiator::$substitutions[$object_class] = $herited_class;
			} else {
				user_error(
					"Can't substitute $object_class with $herited_class :"
					. " $object_class has already its substitution "
					. Instantiator::$substitutions[$object_class]
				);
			}
		} else {
			user_error("Can't substitute : $object_class is not superclass of $herited_class");
		}
	}

}
