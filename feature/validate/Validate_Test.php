<?php
namespace ITRocks\Framework\Feature\Validate;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\Assert;
use ReflectionException;

/**
 * Validate widget testing
 */
class Validate_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var Test_Object
	 */
	private $subject;

	//------------------------------------------------------------------------------------ $validator
	/**
	 * @var Validator
	 */
	private $validator;

	//------------------------------------------------------------------- buildAnnotationsInformation
	/**
	 * Extract annotations' information in an associative array.
	 *
	 * @param $report Reflection\Annotation[]|Annotation[] of annotation objects
	 * @return array
	 */
	private function buildAnnotationsInformation(array $report)
	{
		$info = [];

		foreach ($report as $annotation) {
			$info[] = [
				'class'   => get_class($annotation),
				'message' => $annotation->reportMessage(),
				'valid'   => $annotation->valid,
				'value'   => $annotation->value,
			];
		}

		return $info;
	}

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test
	 *
	 * @throws ReflectionException
	 */
	protected function setUp() : void
	{
		$this->subject   = new Test_Object();
		$this->validator = new Validator();

		$property = new Reflection_Property(Test_Object::class, 'property');
		Property\Validate_Annotation::allOf($property);
		$annotation = new Property\Validate_Annotation(
			'__CLASS_NAME__::notValidDynamic', $property, Property\Validate_Annotation::ANNOTATION
		);
		$property->addAnnotation(Property\Validate_Annotation::ANNOTATION, $annotation);

		$class = new Reflection_Class(Test_Object::class);
		Class_\Validate_Annotation::allOf($class);
		$annotation = new Class_\Validate_Annotation(
			'__CLASS_NAME__::notValidDynamic', $class, Class_\Validate_Annotation::ANNOTATION
		);
		$class->addAnnotation(Class_\Validate_Annotation::ANNOTATION, $annotation);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * Reset after each test
	 */
	public function tearDown() : void
	{
		$this->subject   = null;
		$this->validator = null;
	}

	//----------------------------------------------------------------------- testValidateAnnotations
	/**
	 * Launches 3 class validators that returns different results.
	 */
	public function testValidateAnnotations()
	{
		// Expected information about annotations & AOP.
		$expected = [
			// Property annotations.
			[
				'class'   => Property\Validate_Annotation::class,
				'message' => null,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidFalse',
			],
			[
				'class'   => Property\Validate_Annotation::class,
				'message' => Test_Object::NOT_VALID_MESSAGE,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidMessage',
			],
			[
				'class'   => Property\Validate_Annotation::class,
				'message' => null,
				'valid'   => 'information',
				'value'   => Test_Object::class . '::validTrue',
			],
			[
				'class'   => Property\Validate_Annotation::class,
				'message' => Test_Object::NOT_VALID_DYNAMIC,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidDynamic',
			],
			// Class annotations.
			[
				'class'   => Class_\Validate_Annotation::class,
				'message' => null,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidFalse',
			],
			[
				'class'   => Class_\Validate_Annotation::class,
				'message' => Test_Object::NOT_VALID_MESSAGE,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidMessage',
			],
			[
				'class'   => Class_\Validate_Annotation::class,
				'message' => null,
				'valid'   => 'information',
				'value'   => Test_Object::class . '::validTrue',
			],
			[
				'class'   => Class_\Validate_Annotation::class,
				'message' => Test_Object::NOT_VALID_DYNAMIC,
				'valid'   => 'error',
				'value'   => Test_Object::class . '::notValidDynamic',
			],
		];

		Assert::assertEquals('error', $this->validator->validate($this->subject));
		$actual = $this->buildAnnotationsInformation($this->validator->report);

		Assert::assertEquals($expected, $actual);
	}

}
