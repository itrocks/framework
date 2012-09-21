<?php
namespace SAF\Framework;
use \Reflector;

interface Reflection_Object_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/*
	 * @param string $value
	 * @param Reflector $reflection_object
	 */
	public function __construct($value, $reflection_object);

}
