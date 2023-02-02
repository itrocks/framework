<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Tests\Conditions;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;

/**
 * Conditions in a collection
 */
class Conditions_Collection
{
	use Component;
	use Conditions_Trait;

	//----------------------------------------------------------------------------------- $conditions
	#[Composite]
	public Conditions $conditions;

}
