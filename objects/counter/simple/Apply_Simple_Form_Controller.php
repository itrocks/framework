<?php
namespace ITRocks\Framework\Objects\Counter\Simple;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Apply counter simple form controller
 */
class Apply_Simple_Form_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'APPLY_SIMPLE_FORM';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$counter = $parameters->getMainObject();
		(new Object_Builder_Array())->build($form, $counter);
		$counter->simpleToFormat();
		$parameters->set(Template::TEMPLATE, 'applySimpleForm');
		return View::run(
			$parameters->getObjects(), $form, $files, get_class($counter), static::FEATURE
		);
	}

}
