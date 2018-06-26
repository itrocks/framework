<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure;

/**
 * A lot of generator need a $structure property
 */
trait Has_Structure
{

	//------------------------------------------------------------------------------------ $structure
	/**
	 * @var Structure
	 */
	protected $structure;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $structure Structure
	 */
	public function __construct(Structure $structure)
	{
		$this->structure = $structure;
	}

}
