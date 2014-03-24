<?php
namespace SAF\Framework;

/**
 * The default remove controller will be called if no other remove controller is defined
 */
class Default_Remove_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call this to remove an element from a given class + feature context
	 *
	 * @param $parameters Controller_Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie 'output', 'list')
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array not used
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$objects = $parameters->getObjects();
		$objects['class_name']   = array_shift($objects);
		$objects['feature_name'] = array_shift($objects);
		array_unshift($objects, new Property());
		/**
		 * $objects for the view :
		 * - first : an empty class object (ie Property)
		 * - key 'class_name' : the context class name (ie a business class)
		 * - key 'feature_name' : the context feature name (ie 'output', 'list')
		 */
		return View::run($objects, $form, $files, get_class(reset($objects)), 'remove_unavailable');
	}

}
