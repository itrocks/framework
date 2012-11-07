<?php
namespace SAF\Framework;

class Button
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * Button caption
	 * @var string
	 */
	public $caption;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Button feature
	 * @var string
	 */
	public $feature;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Button link
	 * @var string
	 */
	public $link;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($caption, $link, $feature = null)
	{
		$this->caption = $caption;
		$this->feature = $feature;
		$this->link    = $link;
	}

}
