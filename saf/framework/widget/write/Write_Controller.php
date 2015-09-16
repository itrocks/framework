<?php
namespace SAF\Framework\Widget\Write;

use Exception;
use SAF\Framework\Builder;
use SAF\Framework\Controller;
use SAF\Framework\Controller\Default_Class_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao\File\Builder\Post_Files;
use SAF\Framework\Dao;
use SAF\Framework\Mapper\Object_Builder_Array;
use SAF\Framework\View;
use SAF\Framework\View\Html\Template;

/**
 * The default write controller will be called if no other write controller is defined
 */
class Write_Controller implements Default_Class_Controller
{

	//-------------------------------------------------------------------- write controller constants
	const ERROR      = 'error';
	const FILL_COMBO = 'fill_combo';
	const REDIRECT   = 'redirect_after_write';
	const WRITTEN    = 'written';

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters  Parameters
	 * @param $class_name  string
	 * @param $write_error boolean
	 * @return array
	 */
	protected function getViewParameters(Parameters $parameters, $class_name, $write_error)
	{
		$parameters->getMainObject($class_name);
		$parameters = $parameters->getObjects();

		if (isset($parameters[self::FILL_COMBO]) && strpos($parameters[self::FILL_COMBO], '[')) {
			$elements = explode(DOT, $parameters[self::FILL_COMBO]);
			$parameters[self::FILL_COMBO] = $elements[0] . '.elements[' . DQ . $elements[1] . DQ . ']';
		}

		$parameters[Template::TEMPLATE] = $write_error ? self::ERROR : self::WRITTEN;

		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default 'write-typed' controller
	 *
	 * Save data from the posted form into the first parameter object using standard method.
	 * Create a new instance of this object if no identifier was given.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return string
	 * @throws Exception
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$object = $parameters->getMainObject($class_name);
		$new_object = !Dao::getObjectIdentifier($object);

		Dao::begin();
		try {
			$builder = new Post_Files();
			$form    = $builder->appendToForm($form, $files);
			$builder = new Object_Builder_Array();
			$builder->null_if_empty_sub_objects = true;
			$builder->build($form, $object);
			$write_objects = [];
			foreach ($builder->getBuiltObjects() as $write_object) {
				if (($write_object == $object) || Dao::getObjectIdentifier($write_object)) {
					$write_objects[] = $write_object;
				}
			}

			$write_error = false;
			foreach ($write_objects as $write_object) {
				if (!Dao::write($write_object)) {
					$write_error = true;
					break;
				}
			}
			$write_error ? Dao::rollback() : Dao::commit();
		}
		catch (Exception $exception) {
			Dao::rollback();
			throw $exception;
		}

		$parameters = $this->getViewParameters($parameters, $class_name, $write_error);
		$parameters['new_object'] = $new_object;
		return View::run($parameters, $form, $files, $class_name, Feature::F_WRITE);
	}

}
