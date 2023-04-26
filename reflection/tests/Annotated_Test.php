<?php
namespace ITRocks\Framework\Reflection\Tests;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;

/**
 * Annotated unit tests
 */
class Annotated_Test extends Test
{

	//------------------------------------------------------------------------ testSetAnnotationLocal
	public function testSetAnnotationLocal() : void
	{
		$property1 = new Reflection_Property(Framework\User::class, 'login');
		User::of($property1)->add(User::INVISIBLE);
		self::assertTrue(User::of($property1)->has(User::INVISIBLE), 'modifiedProperty');
		$property2 = new Reflection_Property(Framework\User::class, 'login');
		self::assertFalse(User::of($property2)->has(User::INVISIBLE), 'newProperty');
	}

}
