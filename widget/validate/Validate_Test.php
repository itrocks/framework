<?php
namespace ITRocks\Framework\Widget\Validate;

use ITRocks\Framework\Tests\Test;

/**
 * Validate widget testing
 */
class Validate_Test extends Test
{
	/**
	 * @var Test_Object
	 */
	private $subject;

	//------------------------------------------------------------------------------------ $validator
	/**
	 * @var Validator
	 */
	private $validator;

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	protected function setUp()
	{
		$this->subject   = new Test_Object();
		$this->validator = new Validator();
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
		$this->assertEquals('error', $this->validator->validate($this->subject));

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
				'message' => 'This is not value',
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
			// Class annotations.
			[
				'message' => null,
				'value'   => 'ITRocks\Framework\Widget\Validate\Test_Object::notValidFalse',
				'valid'   => 'error',
				'class'   => 'ITRocks\Framework\Widget\Validate\Class_\Validate_Annotation',
			],
			[
				'message' => 'This is not value',
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
		];

		$actual = $this->buildAnnotationsInformation($this->validator->report);

		$this->assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------- buildAnnotationsInformation
	/**
	 * Extract annotations' information in an associative array.
	 *
	 * @param array $report : An array of annotation objects.
	 *
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

}
