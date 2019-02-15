<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Component\Button\No_General_Buttons;
use ITRocks\Framework\Component\Button\No_Selection_Buttons;
use ITRocks\Framework\Feature\List_;

/**
 * RAD feature list controller
 */
class List_Controller extends List_\Controller
{
	use No_General_Buttons;
	use No_Selection_Buttons;

}
