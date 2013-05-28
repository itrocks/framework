<?php
namespace SAF\Framework;

/**
 * A standard menu for your application
 */
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
	 * @param $set_current Menu
	 * @return Menu
	 */
	public static function current(Menu $set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
