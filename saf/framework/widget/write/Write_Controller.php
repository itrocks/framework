<?php
namespace SAF\Framework\Widget\Write;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Default_Class_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao\File\Builder\Post_Files;
use SAF\Framework\Dao;
use SAF\Framework\Mapper\Object_Builder_Array;
use SAF\Framework\View;

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
		$builder = new Post_Files();
		$form = $builder->appendToForm($form, $files);
		$builder = new Object_Builder_Array();
		$builder->build($form, $object, true);
		$write_objects = [];
		foreach ($builder->getBuiltObjects() as $write_object) {
			if (($write_object == $object) || Dao::getObjectIdentifier($write_object)) {
				$write_objects[] = $write_object;
			}
		}

		foreach ($write_objects as $write_object) {
			Dao::write($write_object);
		}
		Dao::commit();

		if (isset($objects['fill_combo']) && strpos($objects['fill_combo'], '[')) {
			$elements = explode(DOT, $objects['fill_combo']);
			$objects['fill_combo'] = $elements[0] . '.elements["' . $elements[1] . '"]';
		}
		$objects['template'] = 'written';
		return View::run($objects, $form, $files, $class_name, 'write');
	}

}
