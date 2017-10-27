<?php
namespace ITRocks\Framework\Widget\Validate;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Validate;
use PHPUnit\Framework\Assert;

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
	 * @param array $report : An array of annotation objects.
	 * @return array
	 */
	private function buildAnnotationsInformation(array $report)
	{
		$info = [];

		foreach ($report as $annotation) {
			$info[] = [
				'message' => $annotation->reportMessage(),
				'value'   => $annotation->value,
				'valid'   => $annotation->valid,
				'class'   => get_class($annotation),
			];
		}

		return $info;
	}

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	protected function setUp()
	{
		$this->subject   = new Test_Object();
		$this->validator = new Validator();

		$property    = new Reflection_Property(Test_Object::class, 'property');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$annotations = Validate\Property\Validate_Annotation::allOf($property);
		$annotation  = new Validate\Property\Validate_Annotation(
			'__CLASS_NAME__::notValidDynamic', $property, Validate\Property\Validate_Annotation::ANNOTATION);
		$property->addAnnotation(Validate\Property\Validate_Annotation::ANNOTATION, $annotation);

		$class       = new Reflection_Class(Test_Object::class);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$annotations = Validate\Class_\Validate_Annotation::allOf($class);
		$annotation  = new Validate\Class_\Validate_Annotation(
			'__CLASS_NAME__::notValidDynamic', $class, Validate\Class_\Validate_Annotation::ANNOTATION
		);
		$class->addAnnotation(Validate\Class_\Validate_Annotation::ANNOTATION, $annotation);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * Reset after each test.
	 */
	public function tearDown()
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
		Assert::assertEquals('error', $this->validator->validate($this->subject));

		// Expected information about annotations & AOP.
		$expected = [
			// Property annotations.
			[
				'message' => null,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidFalse',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Property\Validate_Annotation',
			],
			[
				'message' => Test_Object::NOT_VALID_MESSAGE,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidMessage',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Property\Validate_Annotation',
			],
			[
				'message' => null,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::validTrue',
				'valid'   => 'information',
				'class'   => 'ITRocks\Framework\Widget\Validate\Property\Validate_Annotation',
			],
			[
				'message' => Test_Object::NOT_VALID_DYNAMIC,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidDynamic',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Property\Validate_Annotation',
			],
			// Class annotations.
			[
				'message' => null,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidFalse',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Class_\Validate_Annotation',
			],
			[
				'message' => Test_Object::NOT_VALID_MESSAGE,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidMessage',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Class_\Validate_Annotation',
			],
			[
				'message' => null,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::validTrue',
				'valid'   => 'information',
				'class'   => 'ITRocks\Framework\Widget\Validate\Class_\Validate_Annotation',
			],
			[
				'message' => Test_Object::NOT_VALID_DYNAMIC,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidDynamic',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Class_\Validate_Annotation',
			],
		];

		$actual = $this->buildAnnotationsInformation($this->validator->report);

		Assert::assertEquals($expected, $actual);
	}

}
