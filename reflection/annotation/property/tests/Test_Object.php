<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * Simple object to test annotations
 *
 * @see User_Annotation used in tests : testGetterAnnotationSet crashes without the use clause
 */
#[Store]
class Test_Object
{

	//--------------------------------------------------------------------------------- KEEP_THIS_USE
	/**
	 * Keep using ITRocks\Framework\Reflection\Annotation\Annoted;
	 *
	 * @see Tests.php#testGetterAnnotationSet
	 */
	const KEEP_THIS_USE = Annoted::class;

	//-------------------------------------------------------------------------- $collection_property
	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @var Property_Test[]
	 */
	#[Component]
	public array $collection_property;

	//-------------------------------------------------------------------------------- $getter_simple
	#[Getter('getSimple')]
	public string $getter_simple;

	//-------------------------------------------------------------------------------- $getter_static
	#[Getter('getStatic')]
	public string $getter_static;

	//------------------------------------------------------------------------------------- $property
	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @default
	 *
	 * @default getDefaultPropertyValue
	 */
	public string $property;

	//-------------------------------------------------------------------------------- $setter_simple
	#[Setter('setSimple')]
	public string $setter_simple;

	//-------------------------------------------------------------------------------- $setter_static
	#[Setter('setStatic')]
	public string $setter_static;

	//---------------------------------------------------------------------------------- $with_values
	#[Values('a_value, another_value, third_value, fourth_value')]
	public string $with_values;

	//----------------------------------------------------------------------- getDefaultPropertyValue
	/**
	 * Get the default property value, for test of @default annotation
	 *
	 * @noinspection PhpUnused #Getter
	 */
	public function getDefaultPropertyValue(Interfaces\Reflection_Property $property) : string
	{
		return 'default value for ' . $property->getName();
	}

	//------------------------------------------------------------------------------------- getSimple
	/** @noinspection PhpUnused #Getter */
	public function getSimple() : string
	{
		return $this->getter_simple . ' with getter simple';
	}

	//------------------------------------------------------------------------------------- getStatic
	/** @noinspection PhpUnused #Getter */
	public static function getStatic(string $value) : string
	{
		return $value . ' with getter static';
	}

	//------------------------------------------------------------------------------------- setSimple
	/** @noinspection PhpUnused #Setter */
	public function setSimple(string $setter_simple) : void
	{
		$this->setter_simple = $setter_simple . ' with setter simple';
	}

	//------------------------------------------------------------------------------------- setStatic
	/** @noinspection PhpUnused #Setter */
	public static function setStatic(string $value) : string
	{
		return $value . ' with setter static';
	}

}
