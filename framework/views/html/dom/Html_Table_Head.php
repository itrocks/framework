<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables heads <thead>
 */
class Html_Table_Head extends Html_Table_Section
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct("thead");
	}

}
