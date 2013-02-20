<?php
namespace SAF\Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Main_Controller;

class Application_Home_Controller implements \SAF\Framework\Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		return Main_Controller::getInstance()->runController("/Tests/run");
	}

}
