<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * Validate widget mandatory annotation test
 */
class Mandatory_Annotation_Test extends Test
{

	//------------------------------------------------------------------------------- $empty_property
	#[Property\Mandatory]
	public mixed $empty_property;

	//------------------------------------------------------------------------------ $filled_property
	#[Property\Mandatory(true)]
	public string $filled_property = 'foo';

	//---------------------------------------------------------------------------- $reflection_object
	private ?Reflection_Class $reflection_object;

	//----------------------------------------------------------------------------------------- setUp
	/** Before each test */
	public function setUp() : void
	{
		$this->reflection_object = new Reflection_Class(__CLASS__);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/** After each test */
	public function tearDown() : void
	{
		$this->reflection_object = null;
	}

	//--------------------------------------------------------------------------------- testIsEmptyKo
	/** Tests Mandatory_Annotation::isEmpty() with a none empty property */
	public function testIsEmptyKo() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Mandatory::of($this->reflection_object->getProperty('filled_property'));
		$actual     = $annotation->isEmpty($this);

		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------------- testIsEmptyOk
	/** Tests Mandatory_Annotation::isEmpty() with an empty property */
	public function testIsEmptyOk() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Mandatory::of($this->reflection_object->getProperty('empty_property'));
		$actual     = $annotation->isEmpty($this);

		static::assertTrue($actual);
	}

}
