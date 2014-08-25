<?php
namespace SAF\Framework\Property;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Property;
use SAF\Framework\View;
use SAF\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * The default property add controller does nothing : we must add a property from a context
 */
class Add_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------- addPropertyToList
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function addPropertyToList($class_name, $property_path)
	{
		$list_settings = Data_List_Settings::current($class_name);
		$list_settings->addProperty($property_path);
		$list_settings->save();
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call this to add an element into a given class + feature context
	 *
	 * @param $parameters Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie 'output', 'list')
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array not used
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		$parameters['class_name']    = array_shift($parameters);
		$parameters['feature_name']  = array_shift($parameters);
		$parameters['property_path'] = array_shift($parameters);
		array_unshift($parameters, new Property());
		if ($parameters['feature_name'] == Feature::F_LIST) {
			$this->addPropertyToList($parameters['class_name'], $parameters['property_path']);
		}
		if ($parameters['feature_name'] == 'form') {
			// ...
		}
		$parameters['template'] = 'added';
		return View::run($parameters, $form, $files, Property::class, Feature::F_ADD);
	}

}
