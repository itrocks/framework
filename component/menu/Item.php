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
	public string $cancel_label = '';

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public string $caption = '';

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public string $class = '';

	//-------------------------------------------------------------------------------- $confirm_label
	/**
	 * @var string
	 */
	public string $confirm_label = '';

	//------------------------------------------------------------------------------ $confirm_message
	/**
	 * @var string
	 */
	public string $confirm_message = '';

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var string
	 */
	public string $link = '';

	//---------------------------------------------------------------------------------- $link_target
	/**
	 * @var string
	 */
	public string $link_target = '';

	//------------------------------------------------------------------------------------- linkClass
	/**
	 * @return string
	 */
	public function linkClass() : string
	{
		$uri = new Uri($this->link);
		return Names::setToClass($uri->controller_name, false);
	}

}
