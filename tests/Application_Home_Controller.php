<?php
namespace SAF\Tests;

use SAF\Framework\Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Main_Controller;

/**
 * The home page runs the tests
 */
class Application_Home_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		return (new Main_Controller())->runController('/Tests/run');
	}

}
