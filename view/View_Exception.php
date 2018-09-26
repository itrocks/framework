<?php
namespace ITRocks\Framework\View;

use Exception;

/**
 * The View exception stops the current controller-view execution chain and enabled displaying
 * the result of an alternative view result
 */
class View_Exception extends Exception
{

	//---------------------------------------------------------------------------------- $view_result
	/**
	 * @var string
	 */
	public $view_result;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $result string The alternative view result (if set)
	 */
	public function __construct($result = null)
	{
		parent::__construct();
		if (isset($result)) $this->view_result = $result;
	}

}
