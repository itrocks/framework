<?php
namespace SAF\Framework;

class Default_Remove_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call this to remove an element from a given class + feature context
	 *
	 * @param $parameters Controller_Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie "output", "list")
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array not used
	 * @param $class_name string the class name for the removed element (ie "SAF\Framework\Property")
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$objects = $parameters->getObjects();
		$objects["class_name"]   = array_shift($objects);
		$objects["feature_name"] = array_shift($objects);
		array_unshift($objects, Object_Builder::current()->newInstance($class_name));
		/**
		 * $objects for the view :
		 * - first : an empty class object (ie Property)
		 * - key "class_name" : the context class name (ie a business class)
		 * - key "feature_name" : the context feature name (ie "output", "list")
		 */
		View::run($objects, $form, $files, $class_name, "remove_unavailable");
	}

}
