<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests;

use ITRocks\Framework\Reflection\Annotation\Tests\Conditions\Conditions_Collection;
use ITRocks\Framework\Reflection\Annotation\Tests\Conditions\Conditions_Trait;

/**
 * Example business class for @conditions
 *
 * To test it :
 * http://localhost/itrocks/ITRocks/Framework/Reflection/Annotation/Tests/Conditions/add
 *
 * @store_name tests_conditions
 */
class Conditions
{
	use Conditions_Trait;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link Collection
	 * @var Conditions_Collection[]
	 */
	public $lines;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return 'conditions';
	}

}
