<?php
namespace SAF\Framework\Html;

/**
 * Php query : a portage to jQuery for easy manipulation of DOM
 */
class Php_Query
{

	//------------------------------------------------------------------------------------- $document
	/**
	 * @var Php_Query_Document
	 */
	private $document;

	//----------------------------------------------------------------------------------- $selections
	/**
	 * @var Php_Query_Selection[]
	 */
	private $selections = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $html string
	 */
	public function __construct($html)
	{
		$this->document = new Php_Query_Document($html);
	}

	//------------------------------------------------------------------------------------------ find
	/**
	 * @param $selector string
	 */
	public function find($selector)
	{

	}

}
