<?php
namespace ITRocks\Framework\Component\Menu;

/**
 * The menu block is the main division of menus items
 */
class Block
{

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var Item[]
	 */
	public array $items = [];

	//--------------------------------------------------------------------------------------- $module
	/**
	 * The module name
	 *
	 * @var string
	 */
	public string $module = '';

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public string $title = '';

	//----------------------------------------------------------------------------------- $title_link
	/**
	 * link for the title
	 *
	 * @var string
	 */
	public string $title_link = '';

	//---------------------------------------------------------------------------- $title_link_target
	/**
	 * target of the title link
	 *
	 * @var string
	 */
	public string $title_link_target = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->title;
	}

}
