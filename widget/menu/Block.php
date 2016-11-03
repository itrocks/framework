<?php
namespace ITRocks\Framework\Widget\Menu;

/**
 * The menu block is the main division of menus items
 */
class Block
{

	//--------------------------------------------------------------------------------------- $module
	/**
	 * The module name
	 *
	 * @var string
	 */
	public $module;

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var Item[]
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->title);
	}

}
