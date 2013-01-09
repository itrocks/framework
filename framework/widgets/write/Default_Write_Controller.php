<?php
namespace SAF\Framework;

class Default_Write_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "write-typed" controller
	 *
	 * Save data from the posted form into the first parameter object using standard method.
	 * Create a new instance of this object if no identifier was given.
	 *
	 * @todo not implemented yet, please do something
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		$changed = false;
		foreach ($form as $name => $value) {
			if ((isset($value) && !isset($object->$name)) || ($object->$name !== $value)) {
				$object->$name = $value;
				$changed = true;
			}
		}
		if ($changed) {
			Dao::write($object);
			View::run($parameters, $form, $files, $class_name, "written");
		}
		else {
			View::run($parameters, $form, $files, $class_name, "unchanged");
		}
	}

}
