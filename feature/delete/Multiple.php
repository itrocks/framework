<?php
namespace ITRocks\Framework\Feature\Delete;

use ITRocks\Framework\Reflection\Attribute\Class_\Display;
use ITRocks\Framework\Reflection\Attribute\Class_\Displays;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Multiple elements to delete, helper for display
 */
class Multiple
{

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var object[]
	 */
	public array $objects;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects object[]
	 */
	public function __construct(array $objects)
	{
		$this->objects = $objects;
		// get #Displays of $objects to set Multiple's #Display value
		/** @noinspection PhpUnhandledExceptionInspection existing object */
		$display = Displays::of(new Reflection_Class(reset($objects)))->value;
		/** @noinspection PhpUnhandledExceptionInspection $this */
		Display::of(new Reflection_Class($this))->value = $display;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return count($this->objects);
	}

}
