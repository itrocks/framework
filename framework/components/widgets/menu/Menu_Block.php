<?php
namespace SAF\Framework;

/**
 * The menu block is the main division of menus items
 */
class Menu_Block
{

	//---------------------------------------------------------------------------------------- $color
	/**
	 * @var string
	 * @values black, blue, cyan, gray, green, magenta, orange
	 */
	public $color;

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var Menu_Item[]
	 */
	public $items;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- $title_link
	/**
	 * link for the title
	 *
	 * @var string
	 */
	public $title_link;

	//---------------------------------------------------------------------------- $title_link_target
	/**
	 * target of the title link
	 *
	 * @var string
	 */
	public $title_link_target;

}
