<?php
namespace SAF\Framework;

class Default_Delete_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		echo "<pre>delete " . print_r($parameters->getObjects(), true) . "</pre>";
	}

}
