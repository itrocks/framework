<?php
namespace SAF\Framework;

/**
 * The default write controller will be called if no other write controller is defined
 */
class Default_Write_Controller implements Default_Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "write-typed" controller
	 *
	 * Save data from the posted form into the first parameter object using standard method.
	 * Create a new instance of this object if no identifier was given.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$objects = $parameters->getObjects();
		$object = reset($objects);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = Builder::create($class_name);
			$objects = array_merge(array($class_name => $object), $objects);
			$parameters->unshift($object);
		}
		$builder = new File_Builder_Post_Files();
		$form = $builder->appendToForm($form, $files);
		$builder = new Object_Builder_Array();
		$builder->build($form, $object, true);
		$write_objects = array();
		foreach ($builder->getBuiltObjects() as $write_object) {
			if (($write_object == $object) || Dao::getObjectIdentifier($write_object)) {
				$write_objects[] = $write_object;
			}
		}
		Dao::begin();
		foreach ($write_objects as $write_object) {
			Dao::write($write_object);
		}
		Dao::commit();
		if (isset($objects["fill_combo"]) && strpos($objects["fill_combo"], "[")) {
			$elements = explode(".", $objects["fill_combo"]);
			$objects["fill_combo"] = $elements[0] . '.elements["' . $elements[1] . '"]';
		}
		return View::run($objects, $form, $files, $class_name, "written");
	}

}
