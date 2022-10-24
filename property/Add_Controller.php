<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

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
		$list_settings = List_Setting\Set::current($class_name);
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
	 * @param $files      array[] not used
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$parameters = $parameters->getObjects();
		$parameters['class_name']    = array_shift($parameters);
		$parameters['feature_name']  = array_shift($parameters);
		$parameters['property_path'] = array_shift($parameters);
		array_unshift($parameters, new Property());
		if ($parameters['feature_name'] == Feature::F_LIST) {
			$this->addPropertyToList($parameters['class_name'], $parameters['property_path']);
		}
		$parameters[Template::TEMPLATE] = 'added';
		return View::run($parameters, $form, $files, Property::class, Feature::F_ADD);
	}

}
