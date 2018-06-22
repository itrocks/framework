<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure;

/**
 * Associate elements that are strictly inside groups to these groups
 */
class Associate_Groups
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

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		// TODO once the user will be able to add groups to the structure himself
		// not needed once Generate_Groups is enough.
	}

}
