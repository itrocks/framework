<?php
namespace SAF\Framework\Tests;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tests;

/**
 * Tests run controller
 *
 * @example
 * /SAF/Framework/Tests/run
 * /SAF/Framework/Tests/run?show=all
 * /SAF/Framework/Tests/run?show=none
 * /SAF/Framework/Tests/run/SAF/Framework/Sql/Join/Tests
 * /SAF/Framework/Tests/run/SAF/Framework/Sql/Join/Tests/testObject
 * /SAF/Framework/Tests/run/SAF/Framework/Sql/Join/Tests/testObject?show=all
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
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
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
