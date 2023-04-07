<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Constant_Or_Callable;

/**
 * Identifies a method that gets the default value for properties which type is this class
 * The Class will be sent as an argument to this callable
 */
#[Attribute(Attribute::TARGET_CLASS), Inheritable]
class Default_
{
	use Common;
	use Has_Constant_Or_Callable { __construct as private parentConstruct; }

	//----------------------------------------------------------------------------------- __construct
	public function __construct(mixed $value = 'getDefault')
	{
		$this->parentConstruct($value);
	}

}
