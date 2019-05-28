<?php
namespace ITRocks\Framework\Component\Menu;

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

}
