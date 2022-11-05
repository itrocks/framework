<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Feature\Remove;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * The default property remove controller does nothing : we must remove a property from a context
 */
class Remove_Controller extends Remove\Controller
{

	//------------------------------------------------------------------------ removePropertyFromList
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function removePropertyFromList(string $class_name, string $property_path) : void
	{
		$list_settings = List_Setting\Set::current($class_name);
		$list_settings->removeProperty($property_path);
		$list_settings->save();
	}

	//---------------------------------------------------------------------- removePropertyFromOutput
	/**
	 * @param $class_name    string
	 * @param $feature_name  string
	 * @param $property_path string
	 */
	public function removePropertyFromOutput(
		string $class_name, string $feature_name, string $property_path
	) : void
	{
		$output_settings = Output_Setting\Set::current($class_name, $feature_name);
		$output_settings->removeProperty($property_path);
		$output_settings->save();
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call this to remove an element from a given class + feature context
	 *
	 * @param $parameters Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie 'output', 'list')
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form  array not used
	 * @param $files array[] not used
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$objects = $parameters->getObjects();
		$objects['class_name'] = $parameters->getRawParameter('class_name')
			?: array_shift($objects);
		$objects['feature_name'] = $parameters->getRawParameter('feature_name')
			?: array_shift($objects);
		$objects['property_path'] = $parameters->getRawParameter('property_path')
			?: array_shift($objects);
		array_unshift($objects, new Property());
		switch ($objects['feature_name']) {
			case Feature::F_LIST:
				$this->removePropertyFromList($objects['class_name'], $objects['property_path']);
				break;
			case Feature::F_EDIT:
			case Feature::F_OUTPUT:
				$this->removePropertyFromOutput(
					$objects['class_name'], $objects['feature_name'], $objects['property_path']
				);
				break;
		}
		$objects['removed']          = Loc::tr($objects['property_path']);
		$objects[Template::TEMPLATE] = 'removed';
		return View::run($objects, $form, $files, Property::class, Feature::F_REMOVE);
	}

}
