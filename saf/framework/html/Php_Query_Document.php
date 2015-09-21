<?php
namespace SAF\Framework\Html;

/**
 * Php query document
 */
class Php_Query_Document
{

	//----------------------------------------------------------------------------------------- $html
	/**
	 * @var string
	 */
	private $html;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $html string
	 */
	public function __construct($html)
	{
		$this->html = $html;
	}

}
