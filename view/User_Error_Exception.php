<?php
namespace ITRocks\Framework\View;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View\Html\Dom\List_\Item;

/**
 * User error exception
 *
 * Throw this to stop the execution and display a formatted error message to the user
 * ... or your code can catch this exception
 */
class User_Error_Exception extends View_Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $result string|null The alternative view result (if set)
	 */
	public function __construct(string $result = null)
	{
		parent::__construct($result);
		$this->view_result = Target::to(Target::QUERY, new Item($this->view_result));
	}

}
