<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Associate elements that are strictly inside groups to these groups
 */
class Associate_Groups
{
	use Has_Structure;

	//------------------------------------------------------------------------------------------- run
	public function run() : void
	{
		// TODO once the user will be able to add groups to the structure himself
		// not needed once Generate_Groups is enough.
	}

}
