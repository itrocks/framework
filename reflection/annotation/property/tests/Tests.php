<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Tests\Default_Simple;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;

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

	//------------------------------------------------------------------------------------- $property
	/**
	 * A fictive local property, for unit tests use only
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @default getDefaultPropertyValue
	 * @link Collection
	 * @var Tests[]
	 */
	private $property;

	//----------------------------------------------------------------------- getDefaultPropertyValue
	/** @noinspection PhpMissingDocCommentInspection */
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

	//------------------------------------------------------------------------------------------ nope
	/**
	 * This makes PhpStorm inspector happy (a use of private $property is welcome)
	 */
	protected function nope()
	{
		$this->property;
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

	//----------------------------------------------------------------------------- testDefaultSimple
	/**
	 * Test @default annotation into the simpliest context : no AOP
	 */
	public function testDefaultSimple()
	{
		$robert = new Default_Simple();
		$this->assume('@default.simple', $robert->name, 'Robert');
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
			(new Getter_Annotation(BS . Annoted::class . '::has', $property))->value,
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
			$property->getAnnotation(Getter_Annotation::ANNOTATION)->value,
			Getter::class . '::getCollection'
		);
	}

	//------------------------------------------------------------------ testIntegratedAnnotationInit
	public function testIntegratedAnnotationInit()
	{
		$this->method(__METHOD__);
		$assume = ['__CLASS__' => Integrated_Annotation::class, 'display_properties' => []];

		// simple declarations

		$integrated = new Integrated_Annotation('');
		$assume ['value'] = ['full'];
		$this->assume('empty', $integrated, $assume);

		$integrated = new Integrated_Annotation('full');
		$assume['value'] = ['full'];
		$this->assume('full', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple');
		$assume['value'] = ['simple'];
		$this->assume('simple', $integrated, $assume);

		// options with implicit simple

		$integrated = new Integrated_Annotation('alias');
		$assume['value'] = ['alias', 'simple'];
		$this->assume('alias', $integrated, $assume);

		$integrated = new Integrated_Annotation('block');
		$assume['value'] = ['block', 'simple'];
		$this->assume('block', $integrated, $assume);

		// explicit options

		$integrated = new Integrated_Annotation('full block');
		$assume['value'] = ['full', 'block'];
		$this->assume('full block', $integrated, $assume);

		$integrated = new Integrated_Annotation('full alias');
		$assume['value'] = ['full', 'alias'];
		$this->assume('full alias', $integrated, $assume);

		$integrated = new Integrated_Annotation('full alias block');
		$assume['value'] = ['full', 'alias', 'block'];
		$this->assume('full alias block', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple block');
		$assume['value'] = ['simple', 'block'];
		$this->assume('simple block', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias');
		$assume['value'] = ['simple', 'alias'];
		$this->assume('simple alias', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block');
		$assume['value'] = ['simple', 'alias', 'block'];
		$this->assume('simple alias block', $integrated, $assume);

		// simple with properties

		$integrated = new Integrated_Annotation('simple property');
		$assume['display_properties'] = ['property'];
		$assume['value']              = ['simple'];
		$this->assume('simple property', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple property1, property2');
		$assume['display_properties'] = ['property1', 'property2'];
		$this->assume('simple properties', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple block,');
		$assume['display_properties'] = ['block'];
		$this->assume('simple reserved property', $integrated, $assume);

		// options and properties

		$integrated = new Integrated_Annotation('simple alias block property');
		$assume['display_properties'] = ['property'];
		$assume['value']              = ['simple', 'alias', 'block'];
		$this->assume('options property', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block property1, property2');
		$assume['display_properties'] = ['property1', 'property2'];
		$this->assume('options properties', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block block,');
		$assume['display_properties'] = ['block'];
		$this->assume('options reserved property', $integrated, $assume);

		// repeated and alone are properties

		$integrated = new Integrated_Annotation('simple alias block alias');
		$assume['display_properties'] = ['alias'];
		$this->assume('repeat alias', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block block');
		$assume['display_properties'] = ['block'];
		$this->assume('repeat block', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block simple');
		$assume['display_properties'] = ['simple'];
		$this->assume('repeat simple', $integrated, $assume);

		// excluded reserved words are properties

		$integrated = new Integrated_Annotation('full simple');
		$assume['display_properties'] = ['simple'];
		$assume['value']              = ['full'];
		$this->assume('full simple', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple full');
		$assume['display_properties'] = ['full'];
		$assume['value']              = ['simple'];
		$this->assume('simple full', $integrated, $assume);

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
