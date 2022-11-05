<?php
namespace ITRocks\Framework\Webservice\Api;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View\User_Error_Exception;

/**
 * A common API to access / alter any business object
 * - create : create an object.
 *   Initialize properties values into parameters and/or form.
 *   The created object identifier is returned to the caller.
 */
class Controller implements Default_Feature_Controller
{

	//---------------------------------------------------------------------------------------- CREATE
	const CREATE = 'create';

	//---------------------------------------------------------------------------------------- create
	/**
	 * Create an object
	 *
	 * @param $object object
	 * @param $form   array
	 * @return integer
	 * @throws User_Error_Exception
	 */
	private function create(object $object, array $form) : int
	{
		Dao::begin();
		$builder = new Object_Builder_Array();
		$builder->null_if_empty_sub_objects = true;
		$builder->build($form, $object);
		foreach ($builder->getBuiltObjects() as $built_object) {
			Dao::write($built_object->object, $built_object->write_options);
		}
		Dao::commit();
		return Dao::getObjectIdentifier($object);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 * @throws User_Error_Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$feature = $parameters->shiftUnnamed();
		$form = array_merge($parameters->getRawParameters(), $form);
		if (isset($form[Parameter::AS_WIDGET])) {
			unset($form[Parameter::AS_WIDGET]);
		}
		$object = $parameters->getMainObject($class_name);
		if ($feature === self::CREATE) {
			return $this->create($object, $form);
		}
		trigger_error('Not a valid API action ' . $feature, E_USER_ERROR);
		/** @noinspection PhpUnreachableStatementInspection May be if the error resumes */
		return null;
	}

}
