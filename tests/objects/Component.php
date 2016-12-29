<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A component
 *
 * @set Test_Components
 */
class Component
{
	use Has_Name;
	use Mapper\Component;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * @composite
	 * @link Object
	 * @var Composite
	 */
	public $composite;

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
