<?php
namespace ITRocks\Framework\Reflection\Tests;

use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\User;

/**
 * Annotated unit tests
 */
class Annotated_Test extends Test
{

	//----------------------------------------------------------------------------- testSetAnnotation
	public function testSetAnnotation() : void
	{
		$property1       = new Reflection_Property(User::class, 'login');
		$user_annotation = new User_Annotation(User_Annotation::INVISIBLE);
		$property1->setAnnotation($user_annotation);

		$property2 = new reflection_Property(User::class, 'login');

		static::assertTrue(
			User_Annotation::of($property1)->has(User_Annotation::INVISIBLE), 'modifiedProperty'
		);

		static::assertTrue(
			User_Annotation::of($property2)->has(User_Annotation::INVISIBLE), '.newProperty'
		);

		// reset for future tests
		$property1->removeAnnotation(User_Annotation::ANNOTATION);
	}

	//------------------------------------------------------------------------ testSetAnnotationLocal
	public function testSetAnnotationLocal() : void
	{
		$property1       = new Reflection_Property(User::class, 'login');
		$user_annotation = User_Annotation::local($property1);
		$user_annotation->add(User_Annotation::INVISIBLE);

		$property2 = new reflection_Property(User::class, 'login');
		static::assertTrue(
			User_Annotation::of($property1)->has(User_Annotation::INVISIBLE), 'modifiedProperty'
		);

		static::assertFalse(
			User_Annotation::of($property2)->has(User_Annotation::INVISIBLE), 'newProperty'
		);
	}

}
