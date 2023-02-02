<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Property annotations unit tests
 */
class Property_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var Test_Object
	 */
	private Test_Object $subject;

	//--------------------------------------------------------- providerIntegratedAnnotationConstruct
	/**
	 * @return array[] string[][]|string[][][]
	 * @see testIntegratedAnnotationConstruct
	 */
	public function providerIntegratedAnnotationConstruct() : array
	{
		return [
			// simple declarations
			'empty'                     => ['',                                        ['full'],                     []],
			'full'                      => ['full',                                    ['full'],                     []],
			'simple'                    => ['simple',                                  ['simple'],                   []],
			// options with implicit simple
			'alias'                     => ['alias',                                   ['alias', 'simple'],          []],
			'block'                     => ['block',                                   ['block', 'simple'],          []],
			// explicit options
			'full block'                => ['full block',                              ['full', 'block'],            []],
			'full alias'                => ['full alias',                              ['full', 'alias'],            []],
			'full alias block'          => ['full alias block',                        ['full', 'alias', 'block'],   []],
			'simple block'              => ['simple block',                            ['simple', 'block'],          []],
			'simple alias'              => ['simple alias',                            ['simple', 'alias'],          []],
			'simple alias block'        => ['simple alias block',                      ['simple', 'alias', 'block'], []],
			// simple with properties
			'simple property'           => ['simple property',                         ['simple'],                   ['property']],
			'simple properties'         => ['simple property1, property2',             ['simple'],                   ['property1', 'property2']],
			'simple reserved property'  => ['simple block,',                           ['simple'],                   ['block']],
			// options and properties
			'options property'          => ['simple alias block property',             ['simple', 'alias', 'block'], ['property']],
			'options properties'        => ['simple alias block property1, property2', ['simple', 'alias', 'block'], ['property1', 'property2']],
			'options reserved property' => ['simple alias block block,',               ['simple', 'alias', 'block'], ['block']],
			// repeated and alone are properties
			'repeat alias'              => ['simple alias block alias',                ['simple', 'alias', 'block'], ['alias']],
			'repeat block'              => ['simple alias block block',                ['simple', 'alias', 'block'], ['block']],
			'repeat simple'             => ['simple alias block simple',               ['simple', 'alias', 'block'], ['simple']],
			// excluded reserved words are properties
			'full simple'               => ['full simple',                             ['full'],                     ['simple']],
			'simple full'               => ['simple full',                             ['simple'],                   ['full']]
		];
	}

	//----------------------------------------------------------------------------------------- setUp
	protected function setUp() : void
	{
		$this->subject = new Test_Object();
	}

	//------------------------------------------------------------------------- testDefaultAnnotation
	/**
	 * Test default annotation
	 */
	public function testDefaultAnnotation() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constants */
		$property = new Reflection_Property(Test_Object::class, 'property');
		static::assertEquals('default value for property', $property->getDefaultValue());
	}

	//----------------------------------------------------------------------------- testDefaultSimple
	/**
	 * Test @default annotation into the simplest context : no AOP
	 */
	public function testDefaultSimple() : void
	{
		$robert = new Default_Simple();
		static::assertEquals(18, $robert->age, '@default.override');
		static::assertEquals(43, $robert->null_age, '@default.override_null');
		/** @noinspection PhpTypedPropertyMightBeUninitializedInspection @default */
		static::assertEquals('Robert', $robert->name, '@default.simple');
		static::assertEquals('Mitchum', $robert->surname, '@default.very_simple');
		/** @noinspection PhpUnhandledExceptionInspection constants */
		static::assertEquals(
			43,
			(new Reflection_Property(Default_Simple::class, 'age'))->getDefaultValue(),
			'@default.reflection.override'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		static::assertEquals(
			43,
			(new Reflection_Property(Default_Simple::class, 'null_age'))->getDefaultValue(),
			'@default.reflection.override_null'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		static::assertEquals(
			'Robert',
			(new Reflection_Property(Default_Simple::class, 'name'))->getDefaultValue(),
			'@default.reflection.simple'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		static::assertEquals(
			'Mitchum',
			(new Reflection_Property(Default_Simple::class, 'surname'))->getDefaultValue(),
			'@default.reflection.very_simple'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		static::assertEquals(
			[
				'age'         => 43,
				'alive_until' => Date_Time::max(),
				'name'        => 'Robert',
				'null_age'    => 43,
				'surname'     => 'Mitchum'
			],
			(new Reflection_Class(Default_Simple::class))->getDefaultProperties([T_EXTENDS]),
			'@default.reflection.all'
		);
	}

	//--------------------------------------------------------------------- testGetterAnnotationCases
	/**
	 * Test property #Getter cases of uses
	 */
	public function testGetterAnnotationCases() : void
	{
		$this->subject->getter_simple = 'a value for simple';
		static::assertEquals('a value for simple with getter simple', $this->subject->getter_simple);

		$this->subject->getter_static = 'a value for static';
		static::assertEquals('a value for static with getter static', $this->subject->getter_static);
	}

	//----------------------------------------------------------------------- testGetterAnnotationSet
	/**
	 * Test property #Getter : setting annotation value
	 */
	public function testGetterAnnotationSet() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid object and constant */
		$property = new Reflection_Property($this->subject, 'collection_property');

		// #Getter methodName
		static::assertEquals(
			get_class($this->subject) . '::testGetterAnnotation',
			(new Getter('testGetterAnnotation', $property))->callable,
			'methodName'
		);
		// #Getter Local_Class_Name::methodName
		static::assertEquals(
			User_Annotation::class . '::has',
			(new Getter('User_Annotation::has', $property))->callable,
			'Local_Class_Name::methodName'
		);
		// #Getter Distant\Class\Full\Path::methodName
		static::assertEquals(
			Annoted::class . '::has',
			(new Getter(BS . Annoted::class . '::has', $property))->callable,
			'Distant\Class\Full\Path\Class_Name::methodName'
		);
		// use Distant\Class\Full\Path\Class_Name
		// #Getter Class_Name::methodName
		static::assertEquals(
			Annoted::class . '::has',
			(new Getter('Annoted::has', $property))->callable,
			'use Class_Name::methodName'
		);
		// default value for getter when there is a @link annotation
		static::assertEquals(
			Mapper\Getter::class . '::getCollection',
			Getter::of($property)->callable,
			'default value when @link'
		);
	}

	//------------------------------------------------------------- testIntegratedAnnotationConstruct
	/**
	 * @dataProvider providerIntegratedAnnotationConstruct
	 * @param $init                 string
	 * @param $expected_value       string[]
	 * @param $expected_properties  string[]
	 */
	public function testIntegratedAnnotationConstruct(
		string $init, array $expected_value, array $expected_properties
	) : void
	{
		$integrated = new Integrated_Annotation($init);
		static::assertEquals($expected_value, $integrated->value);
		static::assertEquals($expected_properties, $integrated->properties);
	}

	//--------------------------------------------------------------------- testSetterAnnotationCases
	/**
	 * Test property #Setter cases of uses
	 */
	public function testSetterAnnotationCases() : void
	{
		$this->subject->setter_simple = 'a value for simple';
		static::assertEquals('a value for simple with setter simple', $this->subject->setter_simple);

		$this->subject->setter_static = 'a value for static';
		static::assertEquals('a value for static with setter static', $this->subject->setter_static);
	}

	//-------------------------------------------------------------------------------- testWithValues
	/**
	 * Test annotation with multi-lines values
	 */
	public function testWithValues() : void
	{
		$this->subject->with_values = 'a_value';
		/** @noinspection PhpUnhandledExceptionInspection valid object and constant property */
		static::assertEquals(
			['a_value', 'another_value', 'third_value', 'fourth_value'],
			(new Reflection_Property($this->subject, 'with_values'))
				->getListAnnotation('values')->values()
		);
	}

}
