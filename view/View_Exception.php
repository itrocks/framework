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
	public string $view_result;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $result string|null The alternative view result (if set)
	 */
	public function __construct(string $result = null)
	{
		parent::__construct();
		if (isset($result)) $this->view_result = $result;
	}

	//------------------------------------------------------------------------------------ outputHtml
	/**
	 * Output the messages as HTML
	 *
	 * @return string
	 */
	public function outputHtml() : string
	{
		return $this->view_result;
	}
	
}
