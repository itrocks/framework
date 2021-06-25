<?php
namespace ITRocks\Framework\Examples\Component\Button_List;

use ITRocks\Framework\Component\Button_List;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View;

/**
 * Class Example_Controller
 * /ITRocks/Framework/Examples/Component/Button_List/example
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
		$button_list = new Button_List();
		$button_list->setButtons(
			[
				new Button_List\Button(
					'First button', 'Return to home', '/', Target::MAIN, [], Button_List\Button::COLOR_PRIMARY
				),
				new Button_List\Button(
					'Second button', 'Return to home', '/', Target::MAIN, [],
					Button_List\Button::COLOR_SECONDARY
				),
			]
		);
		return View::run(
			[Button_List::COMPONENT_NAME => $button_list], [], [], $class_name, $feature_name
		);
	}

}
