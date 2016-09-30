<?php
namespace SAF\Framework\Widget\Edit\Tests;

use SAF\Framework\Mapper\Component;
use SAF\Framework\Traits\Has_Code;
use SAF\Framework\Traits\Has_Name;

/**
 * A simple test object
 */
class Simple_Component
{
	use Component;
	use Has_Code;
	use Has_Name;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * @composite
	 * @link Object
	 * @var Has_Collection
	 */
	public $composite;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @param $code string
	 * @param $name string
	 */
	public function __construct($code = null, $name = null)
	{
		if (isset($code)) $this->code = $code;
		if (isset($name)) $this->name = $name;
	}

}
