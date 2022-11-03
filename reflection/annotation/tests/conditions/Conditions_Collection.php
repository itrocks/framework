<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

/**
 * Conditions in a collection
 */
class Conditions_Collection
{
	use Component;
	use Conditions_Trait;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * @composite
	 * @link Object
	 * @var Conditions
	 */
	public Conditions $conditions;

}
