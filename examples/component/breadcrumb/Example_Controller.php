<?php
namespace ITRocks\Framework\Examples\Component\Breadcrumb;

use ITRocks\Framework\Component;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Class Example_Controller
 * URL : /ITRocks/Framework/Examples/Component/Breadcrumb/example
 *
 */
class Example_Controller extends Default_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$breadcrumb = new Component\Breadcrumb('Breadcrumb Example');
		$breadcrumb->setBackLink('/')
			->addButton('Examples', '', 'module')
			->addButton('Components', '', 'parent');
		return View::run(
			[Component\Breadcrumb::COMPONENT_NAME => $breadcrumb], [], [], $class_name, $feature_name
		);
	}

}
