<?php
namespace ITRocks\Framework\Widget\Cards;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Widget\Button\Has_Selection_Buttons;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;

/**
 * Cards controller
 */
class Controller extends Data_List_Controller implements Has_Selection_Buttons
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = Feature::F_CARDS;

}
