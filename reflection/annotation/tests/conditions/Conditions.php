<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests;

use ITRocks\Framework\Reflection\Annotation\Tests\Conditions\Conditions_Collection;
use ITRocks\Framework\Reflection\Annotation\Tests\Conditions\Conditions_Trait;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * Example business class for @conditions
 *
 * To test it :
 * http://localhost/itrocks/ITRocks/Framework/Reflection/Annotation/Tests/Conditions/add
 */
#[Store('tests_conditions')]
class Conditions
{
	use Conditions_Trait;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var Conditions_Collection[]
	 */
	public array $lines;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return 'conditions';
	}

}
