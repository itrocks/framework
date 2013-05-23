<?php
namespace SAF\Framework;
use ReflectionClass, ReflectionProperty;

/**
 * Enables field names for best personalization of properties displays into HTML templates
 */
class Reflection_Property_Value_For_Html extends Reflection_Property_Value
{

	//---------------------------------------------------------------------------------------- $field
	/**
	 * Html field name
	 *
	 * Modify it and user name={field} into templates to personalize your field names into html forms
	 *
	 * @var string
	 */
	public $field;

	//------------------------------------------------------------------------------------- $tab_path
	/**
	 * @var string
	 */
	public $tab_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class       string|ReflectionClass|Reflection_Class|ReflectionProperty|Reflection_Property|object
	 * @param $name        string|ReflectionProperty|Reflection_Property
	 * @param $object      object|mixed the object containing the value, or the value itself (in this case set $final_value tu true)
	 * @param $final_value boolean set to true if $object is a final value instead of the object containing the valued property
	 * @see Reflection_Property_Value::__construct()
	 */
	public function __construct($class, $name = null, $object = null, $final_value = false)
	{
		parent::__construct($class, $name, $object, $final_value);
		$this->field = $this->name;
	}

}
