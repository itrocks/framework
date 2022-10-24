<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Tests run controller
 *
 * @example
 * /ITRocks/Framework/Tests/run
 * /ITRocks/Framework/Tests/run?coverage-text
 * /ITRocks/Framework/Tests/run?verbose
 * /ITRocks/Framework/Tests/run?filter='TestNamespace\\TestCaseClass::testMethod'
 * /ITRocks/Framework/Tests/run?filter=TestCaseClass
 * @see https://phpunit.de/manual/current/en/textui.html for all options
 */
class Run_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Optional parameters :
	 *   /A/Test/Class/Path
	 *   /A/Test/Class/Path/And/testMethod
	 * and/or :
	 *   ?php_unit_option=option_value
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$query_params = $parameters->getRawParameters();

		// Exclude params when using from console
		unset($query_params['as_widget']);

		$tests = new Tests_Command();
		$tests->runTests($query_params);
		return null;
	}

}
