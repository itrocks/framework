<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Display;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\Traits\Is_Immutable;

/**
 * Feature class
 *
 * @override name @translate common
 */
#[Store]
class Feature_Class
{
	use Has_Name;
	use Is_Immutable;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string|null the name of the source class
	 * @param $name       string|null the displayed name (matches #Display of the built class)
	 */
	public function __construct(string $class_name = null, string $name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (isset($name)) {
			$this->name = $name;
		}
		if ($this->class_name && !$this->name) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$this->name = Display::of(new Reflection_Class($this->class_name))->value;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->name ? Loc::tr($this->name) : '';
	}

}
