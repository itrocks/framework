<?php
namespace SAF\Framework;

class Acls_Property_Remove_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * - first : the Acls_Properties class name
	 * - key 0 : context class name (ie a business object)
	 * - key 1 : context feature name (ie "output", "list")
	 * - keys 2 and next : the identifiers of the removed elements (ie property names)
	 * @param $form array
	 * @param $files array
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		/** @var $acls_properties Acls_Properties */
		$acls_properties = Object_Builder::current()->newInstance($parameters->shiftUnnamed());
		$acls_properties->context_class_name = $parameters->shiftUnnamed();
		$context_class_name = $acls_properties->context_class_name;
		$context_feature_name = $parameters->shiftUnnamed();
		$parameters->set("class_name", $context_class_name);
		$parameters->set("feature_name", $context_feature_name);
		$removed = array();
		foreach ($parameters->getUnnamedParameters() as $property_name) {
			$acls_properties->remove($context_feature_name, $property_name);
			$removed[$property_name] = $property_name;
		}
		$parameters->set("removed", $removed);
		$objects = $parameters->getObjects();
		$property = new Property();
		array_unshift($objects, $property);
		/**
		 * $objects for the view :
		 * - first : an empty Property
		 * - key "class_name" : the context class name (ie a business class)
		 * - key "feature_name" : the context feature name (ie "output", "list")
		 * - key "removed" : array of the removed properties names
		 */
		if ($removed) {
			View::run($objects, $form, $files, get_class($property), "removed");
		}
		else {
			View::run($objects, $form, $files, get_class($property), "remove_nothing_selected");
		}
	}

}
