<?php
namespace SAF\Framework\Widget\Validate;

use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tests\Test;
use SAF\Framework\Widget\Validate\Class_;

/**
 * Validate widget testing
 *
 * @validate notValidFalse
 * @validate notValidMessage
 * @validate validTrue
 */
class Tests extends Test
{

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
		return 'This is not valid';
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
		$validator = new Validator();
		$result = $validator->validate($this);
		// assume
		$class = (new Reflection_Class(get_class($this)));
		$property = $class->getProperty('property');
		/** @var $annotations Class_\Validate_Annotation[] */
		$annotations = array_merge(
			$property->getAnnotations(Property\Validate_Annotation::ANNOTATION),
			$class->getAnnotations(Class_\Validate_Annotation::ANNOTATION)
		);
		foreach ($annotations as $annotation) {
			$annotation->object = $this;
			$annotation->valid = strpos($annotation->value, '::not')
				? Result::ERROR
				: Result::INFORMATION;
			if (isA($annotation, Property\Annotation::class)) {
				/** @var $annotation Property\Annotation::class */
				$annotation->property = $property;
			}
		}
		// report
		$this->assume(__METHOD__, [$result, $validator->report], [Result::ERROR, $annotations]);
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
