<?php
namespace SAF\Framework\Environment;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao;
use SAF\Framework\Environment;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Session;
use SAF\Framework\View;

/**
 * Environment value select controller
 */
class Select_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

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
		$environment = Session::current()->get(Environment::class, true);
		$objects = $parameters->getObjects();
		$set_value = isset($objects['']) ? $objects[''] : (isset($objects[1]) ? $objects[1] : null);
		$name  = $objects[0];
		$this->property = (new Reflection_Class($environment))->getProperty($name);

		if ($set_value) {
			$type = $this->property->getType();
			$environment->$name = $type->isClass()
				? Dao::read($set_value, $type->asString())
				: $set_value;
			$parameters->set('selected', true);
			return (new Output_Controller())->run($parameters, $form, $files);
		}
		else {
			$objects['controller'] = $this;
			$objects['name']       = $name;
			$objects = array_merge([get_class($environment) => $environment], $objects);
			return View::run($objects, $form, $files, get_class($environment), Feature::F_SELECT);
		}
	}

	//---------------------------------------------------------------------------------------- values
	/**
	 * Get current environment name possible values
	 *
	 * @return object[]|string[]|null
	 */
	public function values()
	{
		$type = $this->property->getType();
		if ($type->isClass()) {
			return Dao::readAll($this->property->getType()->asString(), Dao::sort());
		}
		elseif ($values = $this->property->getListAnnotation('values')->values()) {
			return array_combine($values, $values);
		}
		else {
			trigger_error(
				"Unable to get {$this->property->name} environment values from type " . $type->asString(),
				E_USER_ERROR
			);
			return null;
		}
	}

}
