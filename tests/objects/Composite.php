<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A sample composite class
 *
 * @set Test_Composites
 */
class Composite
{
	use Has_Name;

	//------------------------------------------------------------------------------------ $component
	/**
	 * @component
	 * @link Object
	 * @var Component
	 */
	public $component;

	//----------------------------------------------------------------------------------- $components
	/**
	 * @link Collection
	 * @var Component[]
	 */
	public $components;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Object constructor
	 *
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) $this->name = $name;
	}

}
