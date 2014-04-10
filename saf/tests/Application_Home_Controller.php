<?php
namespace SAF\Tests;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameters;

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
		return (new Main())->runController('/Tests/run');
	}

}
