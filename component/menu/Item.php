<?php
namespace ITRocks\Framework\Component\Menu;

use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Tools\Names;

/**
 * A menu item is a displayed text and a link to apply when the user clicks on it
 */
class Item
{

	//--------------------------------------------------------------------------------- $cancel_label
	/**
	 * @var string
	 */
	public $cancel_label;

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//-------------------------------------------------------------------------------- $confirm_label
	/**
	 * @var string
	 */
	public $confirm_label;

	//------------------------------------------------------------------------------ $confirm_message
	/**
	 * @var string
	 */
	public $confirm_message;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var string
	 */
	public $link;

	//---------------------------------------------------------------------------------- $link_target
	/**
	 * @var string
	 */
	public $link_target;

	//------------------------------------------------------------------------------------- linkClass
	/**
	 * @return string
	 */
	public function linkClass()
	{
		$uri = new Uri($this->link);
		return Names::setToClass($uri->controller_name, false);
	}

}
