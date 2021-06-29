<?php
namespace ITRocks\Framework\Examples\Component\Accordion;

use ITRocks\Framework\Component;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Class Example_Controller
 * URL : /ITRocks/Framework/Examples/Component/Accordion/example
 */
class Example_Controller extends Default_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$accordion_g1 = new Component\Accordion(
			'accordion-example', 'example', 'e1', 'First example', [],
			[new View\Html\Dom\Title('First example', 3)]
		);
		$accordion_g2 = new Component\Accordion(
			'accordion-example2', 'example', 'e2', 'Second example', [],
			[new View\Html\Dom\Title('Second example', 3)]
		);
		$accordion_free = new Component\Accordion(
			'accordion-example3', 'example2', 'e1', 'Free example', [],
			[new View\Html\Dom\Title('Free example', 3)]
		);
		return View::run(
			[
				'accordion_group_1' => $accordion_g1,
				'accordion_group_2' => $accordion_g2,
				'accordion_free'    => $accordion_free,
			], [], [], $class_name, $feature_name
		);
	}

}
