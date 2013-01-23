<?php
namespace SAF\Framework;

class Menu
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Menu_Block[]
	 */
	public $blocks;

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

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Menu $set_current
	 * @return Menu
	 */
	public static function current(Menu $set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
