<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\View;

/**
 * Output setting widget property edit controller
 */
class Property_Edit_Controller implements Feature_Controller
{

	//----------------------------------------------------------------- applyCustomSettingsToProperty
	/**
	 * @param $property   Property The property
	 * @param $class_name string The name of the class
	 */
	private function applyCustomSettingsToProperty(Property $property, $class_name)
	{
		$output_settings = Output_Settings::current($class_name);
		$output_settings->cleanup();
		if (isset($output_settings->properties_read_only[$property->path])) {
			$property->read_only = $output_settings->properties_read_only[$property->path];
		}
		if (isset($output_settings->properties_title[$property->path])) {
			$property->display = $output_settings->properties_title[$property->path];
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		if ($parameters->getMainObject(Property::class)->isEmpty()) {
			list($class_name, $property_name) = $parameters->getRawParameters();
			$property = new Property(new Reflection_Property_Value($class_name, $property_name));
			$this->applyCustomSettingsToProperty($property, $class_name);
			$parameters->unshift($property);
		}
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, Property::class, Feature::F_EDIT);
	}

}
