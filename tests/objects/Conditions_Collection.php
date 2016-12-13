<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper\Component;

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
	 * @var Conditions
	 */
	public $conditions;

}
