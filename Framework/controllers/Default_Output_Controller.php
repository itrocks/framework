<?php

class Default_Output_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	public function call($params, $form, $files)
	{
		echo "default output controller with arguments " . print_r($params, true) . "<br>";
	}

}
