<?php
namespace ITRocks\Framework\Examples\Component\Stepper;

use ITRocks\Framework\Component;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Class Example_Controller
 * /ITRocks/Framework/Examples/Component/Stepper/example
 */
class Example_Controller extends Default_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$stepper = new Component\Stepper();
		$stepper->addStep(1, 'First step', data_post: ['st1'])
			->addStep(2, 'Second step', data_post: ['st2'])
			->addStep(3, 'Third step', data_post: ['st3'], current: true)
			->addStep(4, 'Last step', data_post: ['st4']);
		return View::run(
			[Component\Stepper::COMPONENT_NAME => $stepper], [], [], $class_name, $feature_name
		);
	}

}
