<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Tests;

/**
 * Tests run controller
 *
 * @example
 * /ITRocks/Framework/Tests/run
 * /ITRocks/Framework/Tests/run?show=all
 * /ITRocks/Framework/Tests/run?show=none
 * /ITRocks/Framework/Tests/run/ITRocks/Framework/Sql/Join/Tests
 * /ITRocks/Framework/Tests/run/ITRocks/Framework/Sql/Join/Tests/testObject
 * /ITRocks/Framework/Tests/run/ITRocks/Framework/Sql/Join/Tests/testObject?show=all
 */
class Run_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Optional parameters :
	 *   /A/Test/Class/Path
	 *   /A/Test/Class/Path/And/testMethod
	 * and/or :
	 *   ?show=all|errors|none
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$class_name  = $parameters->shiftUnnamed();
		$method_name = $parameters->shiftUnnamed();
		$tests       = new Tests();
		if ($parameters->getRawParameter('show')) {
			$tests->show = $parameters->getRawParameter('show');
		}
		if (!$class_name) {
			$tests->run();
		}
		elseif (!$method_name) {
			$tests->runClass($class_name);
		}
		else {
			$tests->runClass($class_name, $method_name);
		}
		return '<h3>ALL DONE</h3>' . LF
			. $tests->tests_count . ' tests ran' . BR . LF
			. $tests->errors_count . ' errors' . BR . LF
			. $tests->successPercentage(true);
	}

}
