<?php
namespace ITRocks\Framework\Builder\Compiler\Configuration;

use ITRocks\Framework\Builder\Compiler\Configuration;
use ITRocks\Framework\Component;
use ITRocks\Framework\PHP\Reflection_Source;

/**
 * menu.php configuration file compiler (menu hot update)
 */
class Menu extends Configuration
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Reflection_Source
	 */
	public function compile(Reflection_Source $source)
	{
		Component\Menu::get()->refresh();
	}

}
