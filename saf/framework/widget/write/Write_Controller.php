<?php
namespace SAF\Framework\Widget\Write;

use Exception;
use SAF\Framework\Builder;
use SAF\Framework\Controller\Default_Class_Controller;
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
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$objects = $parameters->getObjects();
		$object = reset($objects);
		if (!$object || !is_object($object) || !is_a($object, $class_name, true)) {
			$object = Builder::create($class_name);
			$objects = array_merge([$class_name => $object], $objects);
			$parameters->unshift($object);
		}

		Dao::begin();
		try {
			$builder = new Post_Files();
			$form    = $builder->appendToForm($form, $files);
			$builder = new Object_Builder_Array();
			$builder->build($form, $object, true);
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

		if (isset($objects['fill_combo']) && strpos($objects['fill_combo'], '[')) {
			$elements = explode(DOT, $objects['fill_combo']);
			$objects['fill_combo'] = $elements[0] . '.elements["' . $elements[1] . '"]';
		}
		$objects[Template::TEMPLATE] = $write_error ? 'error' : 'written';
		return View::run($objects, $form, $files, $class_name, 'write');
	}

}
