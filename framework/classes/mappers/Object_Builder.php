<?php
namespace SAF\Framework;
use ReflectionClass;

class Object_Builder
{

	//----------------------------------------------------------------------------------- newInstance
	public static function newInstance($class_name)
	{
		return new $class_name();
	}

	//------------------------------------------------------------------------------- newInstanceArgs
	public static function newInstanceArgs($class_name, $args)
	{
		return (new ReflectionClass($class_name))->newInstanceArgs($args);
	}

}
