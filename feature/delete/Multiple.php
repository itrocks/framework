<?php
namespace ITRocks\Framework\Feature\Delete;

use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Displays_Annotation;
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
	public $objects;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects object[]
	 */
	public function __construct(array $objects)
	{
		$this->objects = $objects;
		// get @displays of $objects to set Multiple's @display value
		/** @noinspection PhpUnhandledExceptionInspection existing object */
		$display = Displays_Annotation::of(new Reflection_Class(reset($objects)))->value;
		/** @noinspection PhpUnhandledExceptionInspection $this */
		Display_Annotation::of(new Reflection_Class($this))->value = $display;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval(count($this->objects));
	}

}
