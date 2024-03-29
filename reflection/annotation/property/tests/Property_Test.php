<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;
use ReflectionException;

/**
 * Property annotations unit tests
 */
class Property_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
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
	/** Test default annotation */
	public function testDefaultAnnotation() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constants */
		$property = new Reflection_Property(Test_Object::class, 'property');
		self::assertEquals('default value for property', $property->getDefaultValue());
	}

	//----------------------------------------------------------------------------- testDefaultSimple
	/** Test #Default attribute into the simplest context : no AOP */
	public function testDefaultSimple() : void
	{
		$robert = new Default_Simple();
		self::assertEquals(18, $robert->age,      '#Default.override');
		self::assertEquals(43, $robert->null_age, '#Default.override_null');
		/** @noinspection PhpTypedPropertyMightBeUninitializedInspection #Default */
		self::assertEquals('Robert',  $robert->name,    '#Default.simple');
		self::assertEquals('Mitchum', $robert->surname, '#Default.very_simple');
		/** @noinspection PhpUnhandledExceptionInspection constants */
		self::assertEquals(
			43,
			(new Reflection_Property(Default_Simple::class, 'age'))->getDefaultValue(),
			'#Default.reflection.override'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		self::assertEquals(
			43,
			(new Reflection_Property(Default_Simple::class, 'null_age'))->getDefaultValue(),
			'#Default.reflection.override_null'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		self::assertEquals(
			'Robert',
			(new Reflection_Property(Default_Simple::class, 'name'))->getDefaultValue(),
			'#Default.reflection.simple'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		self::assertEquals(
			'Mitchum',
			(new Reflection_Property(Default_Simple::class, 'surname'))->getDefaultValue(),
			'#Default.reflection.very_simple'
		);
		/** @noinspection PhpUnhandledExceptionInspection constants */
		self::assertEquals(
			[
				'age'         => 43,
				'alive_until' => Date_Time::max(),
				'name'        => 'Robert',
				'null_age'    => 43,
				'surname'     => 'Mitchum'
			],
			(new Reflection_Class(Default_Simple::class))->getDefaultProperties([T_EXTENDS]),
			'#Default.reflection.all'
		);
	}

	//--------------------------------------------------------------------- testGetterAnnotationCases
	/** Test property #Getter cases of uses */
	public function testGetterAnnotationCases() : void
	{
		$this->subject->getter_simple = 'a value for simple';
		self::assertEquals('a value for simple with getter simple', $this->subject->getter_simple);

		$this->subject->getter_static = 'a value for static';
		self::assertEquals('a value for static with getter static', $this->subject->getter_static);
	}

	//----------------------------------------------------------------------- testGetterAnnotationSet
	/**
	 * Test property #Getter : setting annotation value
	 *
	 * @throws ReflectionException
	 */
	public function testGetterAnnotationSet() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid object and constant */
		$property = new Reflection_Property($this->subject, 'collection_property');

		// #Getter methodName
		$getter = new Getter('testGetterAnnotation');
		$getter->setFinal($property);
		self::assertEquals(
			get_class($this->subject) . '::testGetterAnnotation',
			$getter->callable,
			'methodName'
		);
		// #Getter Local_Class_Name::methodName
		$getter = new Getter([User::class, 'has']);
		$getter->setFinal($property);
		self::assertEquals(
			User::class . '::has',
			$getter->callable,
			'Local_Class_Name::methodName'
		);
		// #Getter Distant\Class\Full\Path::methodName
		$getter = new Getter([Annoted::class, 'has']);
		$getter->setFinal($property);
		self::assertEquals(
			Annoted::class . '::has',
			$getter->callable,
			'Distant\Class\Full\Path\Class_Name::methodName'
		);
		// default value for getter when there is a @link annotation
		self::assertEquals(
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
		self::assertEquals($expected_value, $integrated->value);
		self::assertEquals($expected_properties, $integrated->properties);
	}

	//--------------------------------------------------------------------- testSetterAnnotationCases
	/** Test property #Setter cases of uses */
	public function testSetterAnnotationCases() : void
	{
		$this->subject->setter_simple = 'a value for simple';
		self::assertEquals('a value for simple with setter simple', $this->subject->setter_simple);

		$this->subject->setter_static = 'a value for static';
		self::assertEquals('a value for static with setter static', $this->subject->setter_static);
	}

	//-------------------------------------------------------------------------------- testWithValues
	/** Test annotation with multi-lines values */
	public function testWithValues() : void
	{
		$this->subject->with_values = 'a_value';
		/** @noinspection PhpUnhandledExceptionInspection valid object and constant property */
		self::assertEquals(
			['a_value', 'another_value', 'third_value', 'fourth_value'],
			Values::of(new Reflection_Property($this->subject, 'with_values'))?->values
		);
	}

}
