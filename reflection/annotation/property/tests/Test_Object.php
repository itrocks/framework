<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * Simple object to test annotations
 *
 * @see User_Annotation used in tests : testGetterAnnotationSet crashes without the use clause
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
	public string $getter_simple;

	//-------------------------------------------------------------------------------- $getter_static
	/**
	 * @getter getStatic
	 * @var string
	 */
	public string $getter_static;

	//------------------------------------------------------------------------------------- $property
	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @default getDefaultPropertyValue
	 * @link Collection
	 * @var Property_Test[]
	 */
	public array $property;

	//-------------------------------------------------------------------------------- $setter_simple
	/**
	 * @setter setSimple
	 * @var string
	 */
	public string $setter_simple;

	//-------------------------------------------------------------------------------- $setter_static
	/**
	 * @setter static::setStatic
	 * @var string
	 */
	public string $setter_static;

	//---------------------------------------------------------------------------------- $with_values
	/**
	 * @values a_value, another_value,
	 *         third_value,
	 *         fourth_value
	 * @var string
	 */
	public string $with_values;

	//----------------------------------------------------------------------- getDefaultPropertyValue
	/**
	 * Get the default property value, for test of @default annotation
	 *
	 * @noinspection PhpUnused @getter
	 * @param $property Interfaces\Reflection_Property
	 * @return string
	 */
	public function getDefaultPropertyValue(Interfaces\Reflection_Property $property) : string
	{
		return 'default value for ' . $property->getName();
	}

	//------------------------------------------------------------------------------------- getSimple
	/**
	 * @noinspection PhpUnused @getter
	 * @return string
	 */
	public function getSimple() : string
	{
		return $this->getter_simple . ' with getter simple';
	}

	//------------------------------------------------------------------------------------- getStatic
	/**
	 * @noinspection PhpUnused @getter
	 * @param $value string
	 * @return string
	 */
	public static function getStatic(string $value) : string
	{
		return $value . ' with getter static';
	}

	//------------------------------------------------------------------------------------- setSimple
	/**
	 * @noinspection PhpUnused @setter
	 * @param $setter_simple string
	 */
	public function setSimple(string $setter_simple) : void
	{
		$this->setter_simple = $setter_simple . ' with setter simple';
	}

	//------------------------------------------------------------------------------------- setStatic
	/**
	 * @noinspection PhpUnused @setter
	 * @param $value string
	 * @return string
	 */
	public static function setStatic(string $value) : string
	{
		return $value . ' with setter static';
	}

}
