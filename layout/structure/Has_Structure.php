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
	public Structure $structure;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $structure Structure|null
	 */
	public function __construct(Structure $structure = null)
	{
		if (isset($structure)) {
			$this->structure = $structure;
		}
	}

}
