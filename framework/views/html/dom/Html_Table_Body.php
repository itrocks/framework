<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables bodies <tbody>
 */
class Html_Table_Body extends Html_Table_Section
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct("tbody");
	}

}
