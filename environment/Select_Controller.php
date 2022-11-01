<?php
namespace ITRocks\Framework\Environment;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Environment;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\View;

/**
 * Environment value select controller
 */
class Select_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private Reflection_Property $property;

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$environment = Session::current()->get(Environment::class, true);
		$objects     = $parameters->getObjects();
		$set_value   = $objects[''] ?? $objects[1] ?? null;
		$name        = $objects[0];
		/** @noinspection PhpUnhandledExceptionInspection valid $environment */
		$this->property = (new Reflection_Class($environment))->getProperty($name);

		if ($set_value) {
			$type               = $this->property->getType();
			$environment->$name = $type->isClass()
				? Dao::read($set_value, $type->asString())
				: $set_value;
			$parameters->set('selected', true);
			return (new Output_Controller)->run($parameters, $form, $files);
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
	public function values() : ?array
	{
		$type = $this->property->getType();
		if ($type->isClass()) {
			return Dao::readAll($this->property->getType()->asString(), Dao::sort());
		}
		elseif ($values = $this->property->getListAnnotation('values')->values()) {
			return array_combine($values, $values);
		}
		trigger_error(
			"Unable to get {$this->property->name} environment values from type " . $type->asString(),
			E_USER_ERROR
		);
		/** @noinspection PhpUnreachableStatementInspection in case of caught error */
		return null;
	}

}
