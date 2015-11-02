<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Mapper\Getter;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tests\Test;

/**
 * Property annotations unit tests
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------- $getter_simple
	/**
	 * @getter getSimple
	 * @var string
	 */
	private $getter_simple;

	//-------------------------------------------------------------------------------- $getter_static
	/**
	 * @getter getStatic
	 * @var string
	 */
	private $getter_static;

	//-------------------------------------------------------------------------------- $setter_simple
	/**
	 * @setter setSimple
	 * @var string
	 */
	private $setter_simple;

	//-------------------------------------------------------------------------------- $setter_static
	/**
	 * @setter static::setStatic
	 * @var string
	 */
	private $setter_static;

	//---------------------------------------------------------------------------------- $with_values
	/**
	 * @values a_value, another_value,
	 *         third_value,
	 *         fourth_value
	 * @var string
	 */
	private $with_values;

	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @default getDefaultPropertyValue
	 * @link Collection
	 * @var Tests[]
	 */
	private /* @noinspection PhpUnusedPrivateFieldInspection */ $property;

	/**
	 * Get the default property value, for test of @default annotation
	 *
	 * @param $property Interfaces\Reflection_Property
	 * @return string
	 */
	/* @noinspection PhpMissingDocCommentInspection */
	//----------------------------------------------------------------------- getDefaultPropertyValue
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

	//------------------------------------------------------------------------- testDefaultAnnotation
	/**
	 * Test default annotation
	 */
	public function testDefaultAnnotation()
	{
		$this->method('@default');
		$property = new Reflection_Property(self::class, 'property');

		// @default getDefaultPropertyValue
		$this->assume(
			'methodName',
			$property->getDefaultValue(),
			'default value for property'
		);
	}

	//--------------------------------------------------------------------- testGetterAnnotationCases
	/**
	 * Test property @getter : cases of uses
	 */
	public function testGetterAnnotationCases()
	{
		$this->method('@getter : cases of uses');
		$this->getter_simple = 'a value for simple';
		$this->assume(
			'simple, property name',
			$this->getter_simple,
			'a value for simple with getter simple'
		);
		$this->getter_static = 'a value for static';
		$this->assume(
			'static, $value',
			$this->getter_static,
			'a value for static with getter static'
		);
	}

	//----------------------------------------------------------------------- testGetterAnnotationSet
	/**
	 * Test property @getter : setting annotation value
	 */
	public function testGetterAnnotationSet()
	{
		$this->method('@getter : setting annotation value');
		$property = new Reflection_Property(self::class, 'property');

		// @getter methodName
		$this->assume(
			'methodName',
			(new Getter_Annotation('testGetterAnnotation', $property))->value,
			__CLASS__ . '::testGetterAnnotation'
		);
		// @getter Local_Class_Name::methodName
		$this->assume(
			'Local_Class_Name::methodName',
			(new Getter_Annotation('User_Annotation::has', $property))->value,
			User_Annotation::class . '::has'
		);
		// @getter Distant\Class\Full\Path::methodName
		$this->assume(
			'Distant\Class\Full\Path\Class_Name::methodName',
			(new Getter_Annotation(Annoted::class . '::has', $property))->value,
			Annoted::class . '::has'
		);
		// use Distant\Class\Full\Path\Class_Name
		// @getter Class_Name::methodName
		$this->assume(
			'use Class_Name::methodName',
			(new Getter_Annotation('Annoted::has', $property))->value,
			Annoted::class . '::has'
		);
		// default value for getter when there is a @link annotation
		$this->assume(
			'default value when @link',
			$property->getAnnotation('getter')->value,
			Getter::class . '::getCollection'
		);
	}

	//--------------------------------------------------------------------- testSetterAnnotationCases
	/**
	 * Test property @setter : cases of uses
	 */
	public function testSetterAnnotationCases()
	{
		$this->method('@setter : cases of uses');
		$this->setter_simple = 'a value for simple';
		$this->assume(
			'simple, property name, without return',
			$this->setter_simple,
			'a value for simple with setter simple'
		);
		$this->setter_static = 'a value for static';
		$this->assume(
			'static, $value, return value',
			$this->setter_static,
			'a value for static with setter static'
		);
	}

	//-------------------------------------------------------------------------------- testWithValues
	/**
	 * Test annotation with multi-lines values
	 */
	public function testWithValues()
	{
		$this->with_values = 'a_value';
		$this->assume(
			__METHOD__,
			(new Reflection_Property(get_class($this), 'with_values'))->getListAnnotation('values')->values(),
			['a_value', 'another_value', 'third_value', 'fourth_value']
		);
	}

}
