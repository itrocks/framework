<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * Simple object to test annotations
 */
class Test_Object
{

	//--------------------------------------------------------------------------------- KEEP_THIS_USE
	/**
	 * Keep use ITRocks\Framework\Reflection\Annotation\Annoted;
	 *
	 * @see Tests.php#testGetterAnnotationSet
	 */
	const KEEP_THIS_USE = Annoted::class;

	//-------------------------------------------------------------------------------- $getter_simple
	/**
	 * @getter getSimple
	 * @var string
	 */
	public $getter_simple;

	//-------------------------------------------------------------------------------- $getter_static
	/**
	 * @getter getStatic
	 * @var string
	 */
	public $getter_static;

	//------------------------------------------------------------------------------------- $property
	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @default getDefaultPropertyValue
	 * @link Collection
	 * @var Property_Test[]
	 */
	public $property;

	//-------------------------------------------------------------------------------- $setter_simple
	/**
	 * @setter setSimple
	 * @var string
	 */
	public $setter_simple;

	//-------------------------------------------------------------------------------- $setter_static
	/**
	 * @setter static::setStatic
	 * @var string
	 */
	public $setter_static;

	//---------------------------------------------------------------------------------- $with_values
	/**
	 * @values a_value, another_value,
	 *         third_value,
	 *         fourth_value
	 * @var string
	 */
	public $with_values;

	//----------------------------------------------------------------------- getDefaultPropertyValue
	/**
	 * Get the default property value, for test of @default annotation
	 *
	 * @param $property Interfaces\Reflection_Property
	 * @return string
	 */
	public static function getDefaultPropertyValue(Interfaces\Reflection_Property $property)
	{
		return 'default value for ' . $property->getName();
	}

	//------------------------------------------------------------------------------------- getSimple
	/**
	 * @return string
	 */
	public function getSimple()
	{
		return $this->getter_simple . ' with getter simple';
	}

	//------------------------------------------------------------------------------------- getStatic
	/**
	 * @param $value string
	 * @return string
	 */
	public static function getStatic($value)
	{
		return $value . ' with getter static';
	}

	//------------------------------------------------------------------------------------- setSimple
	/**
	 * @param $setter_simple string
	 */
	public function setSimple($setter_simple)
	{
		$this->setter_simple = $setter_simple . ' with setter simple';
	}

	//------------------------------------------------------------------------------------- setStatic
	/**
	 * @param $value string
	 * @return string
	 */
	public static function setStatic($value)
	{
		return $value . ' with setter static';
	}

}
