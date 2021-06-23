<?php
namespace ITRocks\Framework\Examples\View\Html\Dom\Content_Title;

use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Content_Title;

/**
 * Class Example_Controller
 * /ITRocks/Framework/Examples/View/Html/Dom/Content_Title/example
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
		$content_title = new Content_Title('Hello');
		return View::run(
			[Content_Title::ELEMENT_NAME => $content_title], [], [], $class_name, $feature_name
		);
	}

}
