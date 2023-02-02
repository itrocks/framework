<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Attribute\Class_;

/**
 * This must be used for traits that are designed to implement a given interface.
 * Builder will use it to sort built classes.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Implement extends Class_
{

	//----------------------------------------------------------------------------------- $implements
	/**
	 * @var class-string[]
	 */
	public array $implements = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $implements class-string[]
	 */
	public function __construct(string... $implements)
	{
		$this->implements = $implements;
		foreach ($this->implements as &$implements) {
			$implements = Builder::className($implements);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		sort($this->implements);
		return join(LF, $this->implements);
	}

}
