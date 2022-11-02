<?php
namespace ITRocks\Framework\Feature\Remove;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use stdClass;

/**
 * The default remove controller will be called if no other remove controller is defined
 */
class Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call this to remove an element from a given class + feature context
	 *
	 * @param $parameters Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie Feature::F_OUTPUT, Feature::F_LIST)
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array[] not used
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$objects = $parameters->getObjects();
		$parameters['class_name']   = array_shift($objects);
		$parameters['feature_name'] = array_shift($objects);
		array_unshift($objects, new stdClass());
		/**
		 * $objects for the view :
		 * - first : an empty class object (ie Property)
		 * - key 'class_name' : the context class name (ie a business class)
		 * - key 'feature_name' : the context feature name (ie Feature::F_OUTPUT, Feature::F_LIST)
		 */
		$parameters[Template::TEMPLATE] = 'remove_unavailable';
		return View::run($objects, $form, $files, get_class(reset($objects)), Feature::F_REMOVE);
	}

}
