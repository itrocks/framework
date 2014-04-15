<?php
namespace SAF\Framework\Widget\Remove;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;
use stdClass;

/**
 * The default remove controller will be called if no other remove controller is defined
 */
class Remove_Controller implements Feature_Controller
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
	 * @param $files      array not used
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		$parameters['class_name']   = array_shift($objects);
		$parameters['feature_name'] = array_shift($objects);
		array_unshift($objects, new StdClass());
		/**
		 * $objects for the view :
		 * - first : an empty class object (ie Property)
		 * - key 'class_name' : the context class name (ie a business class)
		 * - key 'feature_name' : the context feature name (ie Feature::F_OUTPUT, Feature::F_LIST)
		 */
		$parameters['template'] = 'remove_unavailable';
		return View::run($objects, $form, $files, get_class(reset($objects)), Feature::F_REMOVE);
	}

}
