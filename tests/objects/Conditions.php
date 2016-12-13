<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * Example business class for @conditions
 *
 * To test it :
 * http://it.rocks/project/ITRocks/Framework/Tests/Objects/Conditions/add
 *
 * @set Tests_Conditions
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
	public function __toString()
	{
		return 'conditions';
	}

}
