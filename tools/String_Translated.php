<?php
namespace SAF\Framework\Tools;

use SAF\Framework\Locale\Loc;

/**
 * A String_Class with translation of display()
 */
class String_Translated extends String_Class
{

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return Loc::tr(parent::display());
	}

}
