<?php
namespace ITRocks\Framework\Feature\Import\Settings;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Import property
 */
class Import_Property
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public string $class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class string|null
	 * @param $name  string|null
	 */
	public function __construct(string $class = null, string $name = null)
	{
		if (isset($class)) $this->class = $class;
		if (isset($name))  $this->name  = $name;
	}

	//------------------------------------------------------------------------------------ toProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property
	 */
	public function toProperty() : Reflection_Property
	{
		/** @noinspection PhpUnhandledExceptionInspection property must be valid */
		return new Reflection_Property($this->class, $this->name);
	}

}
