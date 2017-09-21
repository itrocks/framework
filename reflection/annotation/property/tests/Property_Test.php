<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Getter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;

/**
 * Property annotations unit tests
 */
class Property_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var Test_Object
	 */
	private $subject;

	//----------------------------------------------------------------------------------------- setUp
	protected function setUp()
	{
		$this->subject = new Test_Object();
	}

	//------------------------------------------------------------------------- testDefaultAnnotation
	/**
	 * Test default annotation
	 */
	public function testDefaultAnnotation()
	{
		$property = new Reflection_Property(Test_Object::class, 'property');
		$this->assertEquals('default value for property', $property->getDefaultValue());
	}

	//----------------------------------------------------------------------------- testDefaultSimple
	/**
	 * Test @default annotation into the simpliest context : no AOP
	 */
	public function testDefaultSimple()
	{
		$robert = new Default_Simple();
		// TODO LOW default for age should be 43, but this case does not work. Warning in documentation
		$this->assume('@default.override',      $robert->age,      18);
		$this->assume('@default.override_null', $robert->null_age, 43);
		$this->assume('@default.simple',        $robert->name,     'Robert');
		$this->assume('@default.very_simple',   $robert->surname,  'Mitchum');
		$this->assume('@default.reflection.override',
			(new Reflection_Property(Default_Simple::class, 'age'))->getDefaultValue(), 18
		);
		$this->assume('@default.reflection.override_null',
			(new Reflection_Property(Default_Simple::class, 'null_age'))->getDefaultValue(), 43
		);
		$this->assume('@default.reflection.simple',
			(new Reflection_Property(Default_Simple::class, 'name'))->getDefaultValue(), 'Robert'
		);
		$this->assume('@default.reflection.very_simple',
			(new Reflection_Property(Default_Simple::class, 'surname'))->getDefaultValue(), 'Mitchum'
		);
		$this->assume('@default.reflection.all',
			(new Reflection_Class(Default_Simple::class))->getDefaultProperties([T_EXTENDS]),
			['age' => 18, 'name' => 'Robert', 'null_age' => 43, 'surname' => 'Mitchum']
		);
	}

	//--------------------------------------------------------------------- testGetterAnnotationCases
	/**
	 * Test property @getter : cases of uses
	 */
	public function testGetterAnnotationCases()
	{
		$this->subject->getter_simple = 'a value for simple';
		$this->assertEquals('a value for simple with getter simple', $this->subject->getter_simple);

		$this->subject->getter_static = 'a value for static';
		$this->assertEquals('a value for static with getter static', $this->subject->getter_static);
	}

	//----------------------------------------------------------------------- testGetterAnnotationSet
	/**
	 * Test property @getter : setting annotation value
	 */
	public function testGetterAnnotationSet()
	{
		$this->method('@getter : setting annotation value');
		$property = new Reflection_Property(get_class($this->subject), 'property');

		// @getter methodName
		$this->assertEquals(
			get_class($this->subject) . '::testGetterAnnotation',
			(new Getter_Annotation('testGetterAnnotation', $property))->value,
			'methodName'
		);
		// @getter Local_Class_Name::methodName
		$this->assertEquals(
			User_Annotation::class . '::has',
			(new Getter_Annotation('User_Annotation::has', $property))->value,
			'Local_Class_Name::methodName'
		);
		// @getter Distant\Class\Full\Path::methodName
		$this->assertEquals(
			Annoted::class . '::has',
			(new Getter_Annotation(BS . Annoted::class . '::has', $property))->value,
			'Distant\Class\Full\Path\Class_Name::methodName'
		);
		// use Distant\Class\Full\Path\Class_Name
		// @getter Class_Name::methodName
		$this->assertEquals(
			Annoted::class . '::has',
			(new Getter_Annotation('Annoted::has', $property))->value,
			'use Class_Name::methodName'
		);
		// default value for getter when there is a @link annotation
		$this->assertEquals(
			Getter::class . '::getCollection',
			$property->getAnnotation(Getter_Annotation::ANNOTATION)->value,
			'default value when @link'
		);
	}

	//------------------------------------------------------------------ testIntegratedAnnotationInit
	public function testIntegratedAnnotationInit()
	{
		$this->method(__METHOD__);
		$assume = ['__CLASS__' => Integrated_Annotation::class, 'properties' => []];

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
		$assume['properties'] = ['property'];
		$assume['value']      = ['simple'];
		$this->assume('simple property', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple property1, property2');
		$assume['properties'] = ['property1', 'property2'];
		$this->assume('simple properties', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple block,');
		$assume['properties'] = ['block'];
		$this->assume('simple reserved property', $integrated, $assume);

		// options and properties

		$integrated = new Integrated_Annotation('simple alias block property');
		$assume['properties'] = ['property'];
		$assume['value']              = ['simple', 'alias', 'block'];
		$this->assume('options property', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block property1, property2');
		$assume['properties'] = ['property1', 'property2'];
		$this->assume('options properties', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block block,');
		$assume['properties'] = ['block'];
		$this->assume('options reserved property', $integrated, $assume);

		// repeated and alone are properties

		$integrated = new Integrated_Annotation('simple alias block alias');
		$assume['properties'] = ['alias'];
		$this->assume('repeat alias', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block block');
		$assume['properties'] = ['block'];
		$this->assume('repeat block', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple alias block simple');
		$assume['properties'] = ['simple'];
		$this->assume('repeat simple', $integrated, $assume);

		// excluded reserved words are properties

		$integrated = new Integrated_Annotation('full simple');
		$assume['properties'] = ['simple'];
		$assume['value']      = ['full'];
		$this->assume('full simple', $integrated, $assume);

		$integrated = new Integrated_Annotation('simple full');
		$assume['properties'] = ['full'];
		$assume['value']      = ['simple'];
		$this->assume('simple full', $integrated, $assume);

	}

	//--------------------------------------------------------------------- testSetterAnnotationCases
	/**
	 * Test property @setter : cases of uses
	 */
	public function testSetterAnnotationCases()
	{
		$this->subject->setter_simple = 'a value for simple';
		$this->assertEquals('a value for simple with setter simple', $this->subject->setter_simple);

		$this->subject->setter_static = 'a value for static';
		$this->assertEquals('a value for static with setter static', $this->subject->setter_static);
	}

	//-------------------------------------------------------------------------------- testWithValues
	/**
	 * Test annotation with multi-lines values
	 */
	public function testWithValues()
	{
		$this->subject->with_values = 'a_value';
		$this->assertEquals(
			['a_value', 'another_value', 'third_value', 'fourth_value'],
			(new Reflection_Property(get_class($this->subject), 'with_values'))->getListAnnotation('values')
				->values(),
			__METHOD__
		);
	}

}
