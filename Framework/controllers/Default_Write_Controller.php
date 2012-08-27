<?php

class Default_Write_Controller implements Controller
{

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param array $params
	 * @param array $form
	 * @param array $files
	 */
	public function call($params, $form, $files)
	{
		echo "write " . print_r($params, true) . " data " . print_r($form, true) . " files " . print_r($files, true) . "<br>";
	}

}
