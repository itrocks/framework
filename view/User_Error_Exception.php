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

	//------------------------------------------------------------------------------- $error_messages
	/**
	 * Additional error messages, added to view result
	 *
	 * @var string[]
	 */
	public array $error_messages = [];

	//------------------------------------------------------------------------------------ outputHtml
	/**
	 * @return string
	 */
	public function outputHtml() : string
	{
		$output = [new Item($this->view_result)];
		foreach ($this->error_messages as $error_message) {
			$output[] = new Item($error_message);
		}
		return Target::to(Target::QUERY, join(LF, $output));
	}
	
}
