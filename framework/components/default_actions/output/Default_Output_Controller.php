<?php
namespace SAF\Framework;

class Default_Output_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($object)
	{
		return array(
			new Button(
				"Duplicate", View::link($object, "duplicate"), "duplicate"
			)
		);
	}

}
