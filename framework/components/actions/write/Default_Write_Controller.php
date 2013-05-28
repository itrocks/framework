<?php
namespace SAF\Framework;

/**
 * The default write controller will be called if no other write controller is defined
 */
class Default_Write_Controller implements Default_Class_Controller
{

	//-------------------------------------------------------------------- formElementToPropertyValue
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return mixed
	 */
	private function formElementToPropertyValue(Reflection_Property $property, $value)
	{
		if ($property->getType()->isBoolean()) {
			$value = !(empty($value) || ($value === "false"));
		}
		elseif (is_array($value)) {
			if ($property->getAnnotation("link")->value == "Object") {
				$value = arrayToObject($value, $property->getType()->getElementTypeAsString(), false);
			}
			elseif ($property->getAnnotation("link")->value == "Collection") {
				$value = arrayToCollection($value, $property->getType()->getElementTypeAsString(), false);
			}
		}
		return $value;
	}

	//--------------------------------------------------------------------------------- formToObjects
	/**
	 * Returns the object that form data represents
	 *
	 * @param $object object The object or class name to fill-in
	 * @param $form array    The form data
	 * @return object[] The result objects (same as $object if it was an object)
	 */
	public function formToObjects($object, $form)
	{
		if (is_string($object)) {
			$object = Builder::create($object);
		}
		$objects = array($object);
		$class = Reflection_Class::getInstanceOf($object);
		$properties = $class->accessProperties();
		if (isset($form["id"]) && empty($form["id"])) {
			unset($form["id"]);
		}
		foreach ($form as $name => $value) {
			if (isset($properties[$name])) {
				$object->$name = $this->formElementToPropertyValue($properties[$name], $value);
				if ($properties[$name]->getAnnotation("link")->value == "Object") {
					foreach ($this->formToObjects($object->$name, $value) as $sub_object) {
						if (!Empty_Object::isEmpty($sub_object)) {
							$objects[] = $sub_object;
						}
					}
				}
			}
			else {
				$object->$name = $value;
				if ((substr($name, 0, 3) == "id_")) {
					$name = substr($name, 3);
					if (isset($object->$name)) {
						unset($object->$name);
					}
				}
			}
		}
		$class->accessPropertiesDone();
		return $objects;
	}

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
		$write_objects = $this->formToObjects($object, $form);
		$write_objects = array_reverse($write_objects);
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
