<?php
namespace SAF\Framework;

/**
 * Session file default controller
 */
class Session_File_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $feature_name)
	{
		echo "doit ?<br>";
		echo "<pre>" . print_r($parameters, true) . "</pre>";
	}

}
