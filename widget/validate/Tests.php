<?php
namespace ITRocks\Framework\Widget\Validate;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Validate\Class_;

/**
 * Validate widget testing
 *
 * @validate notValidFalse
 * @validate notValidMessage
 * @validate validTrue
 */
class Tests extends Test
{

	//--------------------------------------------------------------------------------------- MESSAGE
	const MESSAGE = 'This is not value';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @validate notValidFalse
	 * @validate notValidMessage
	 * @validate validTrue
	 * @var string
	 */
	protected $property = 'its-value';

	//--------------------------------------------------------------------------------- notValidFalse
	/**
	 * A validation function that returns false
	 *
	 * @return boolean
	 */
	public function notValidFalse()
	{
		return false;
	}

	//------------------------------------------------------------------------------- notValidMessage
	/**
	 * A validation function that returns an error message
	 *
	 * @return string
	 */
	public function notValidMessage()
	{
		return self::MESSAGE;
	}

	//--------------------------------------------------------------------- testClassPropertyValidate
	/**
	 * This test launches the validation of the current test object :
	 * - launch 3 class validators that return different results
	 * - launch 3 property validators that return different results
	 */
	public function testClassPropertyValidate()
	{
		// test
		$annotations = [];
		$messages    = [];
		$validator   = new Validator();
		$result      = $validator->validate($this);
		foreach ($validator->report as $annotation) {
			if ($annotation instanceof Annotation\Validate_Annotation) {
				$annotations[] = $annotation;
				$messages[]    = $annotation->reportMessage();
			}
		}
		$check = [
			'annotations' => $annotations,
			'error'       => $result,
			'messages'    => $messages
		];

		// assume
		$class    = (new Reflection_Class(get_class($this)));
		$property = $class->getProperty('property');
		/** @var $annotations Class_\Validate_Annotation[] */
		$annotations = array_merge(
			$property->getAnnotations(Property\Validate_Annotation::ANNOTATION),
			$class->getAnnotations(Class_\Validate_Annotation::ANNOTATION)
		);
		$assume_messages = [];
		foreach ($annotations as $annotation) {
			$annotation->object = $this;
			$annotation->valid  = strpos($annotation->value, '::not')
				? Result::ERROR
				: Result::INFORMATION;
			if (isA($annotation, Property\Annotation::class)) {
				/** @var $annotation Property\Annotation::class */
				$annotation->property = $property;
			}
			$assume_messages[] = strpos($annotation->value, '::notValidMessage') ? self::MESSAGE : null;
		}
		$assume = [
			'annotations' => $annotations,
			'error'       => Result::ERROR,
			'messages'    => $assume_messages
		];

		// report
		$this->assume(__METHOD__, $check, $assume);
	}

	//------------------------------------------------------------------------------------- validTrue
	/**
	 * A validation function that returns true
	 *
	 * @return boolean
	 */
	public function validTrue()
	{
		return true;
	}

}
