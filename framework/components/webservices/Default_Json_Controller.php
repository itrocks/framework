<?php
namespace SAF\Framework;
use StdClass;

class Default_Json_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run the default json controller
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @return string
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		// read all objects corresponding to class name
		if (!$parameters) {
			return json_encode(Dao::readAll(Names::setToClass($class_name)));
		}
		// read object
		$first_parameter = reset($parameters);
		if (is_object($first_parameter)) {
			return json_encode($first_parameter);
		}
		// search objects
		if (isset($parameters["term"])) {
			$element_class_name = Namespaces::fullClassName(Names::setToClass($class_name));
			$search = (new Search_Array_Builder())->buildMultiple(
				Reflection_Class::getInstanceOf($element_class_name),
				$parameters["term"],
				"%"
			);
			$objects = array();
			foreach (Dao::search($search, $element_class_name) as $key => $source_object) {
				$object = new StdClass();
				$object->id = Dao::getObjectIdentifier($source_object);
				$object->value = "" . $source_object;
				$objects[$key] = $object;
			}
			return json_encode($objects);
		}
	}

}
