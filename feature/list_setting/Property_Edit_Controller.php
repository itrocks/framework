<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * Output setting widget property edit controller
 */
class Property_Edit_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------ customSettingsProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    string The name of the class
	 * @param $property_path string The property
	 * @return Property
	 */
	private function customSettingsProperty($class_name, $property_path)
	{
		$list_settings = Set::current($class_name);
		$list_settings->cleanup();
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$property = isset($list_settings->properties[$property_path])
			? $list_settings->properties[$property_path]
			: Builder::create(Property::class, [$class_name, $property_path]);
		return $property;
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
	public function run(Parameters $parameters, array $form, array $files)
	{
		if ($parameters->getMainObject(Property::class)->isEmpty()) {
			[$class_name, $property_path] = $parameters->getRawParameters();
			$class_name = Names::setToClass($class_name);
			$property   = $this->customSettingsProperty($class_name, $property_path);
			$parameters->unshift($property);
		}
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, Property::class, Feature::F_EDIT);
	}

}
